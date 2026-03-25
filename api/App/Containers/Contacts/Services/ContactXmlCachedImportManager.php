<?php

declare(strict_types=1);

namespace App\Containers\Contacts\Services;

use App\Containers\Contacts\Actions\GetAllContactsAction;
use App\Containers\Contacts\Actions\InsertContactsAction;
use App\Containers\Contacts\Models\Contact;
use App\Containers\Contacts\Values\DTOs\ContactDTO;
use App\Containers\Imports\Enums\ImportStatusesEnum;
use App\Containers\Imports\Services\CachedImportManager;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class ContactXmlCachedImportManager extends CachedImportManager
{
    public const string CACHE_DUPLICATES_SUFFIX = ':duplicates';

    public const string CACHE_INVALID_SUFFIX = ':invalid';

    private const int ITEMS_IN_INSERT_CHUNK = 500;

    private const string IMPORT_DONE_ATTRIBUTE_TOTAL = 'total';

    private const string IMPORT_DONE_ATTRIBUTE_IMPORTED = 'imported';

    private const string IMPORT_DONE_ATTRIBUTE_DUPLICATES = 'duplicates';

    private const string IMPORT_DONE_ATTRIBUTE_INVALID = 'invalid';

    private const string IMPORT_DONE_ATTRIBUTE_DURATION = 'duration';

    /**
     * @var \Carbon\CarbonImmutable $importStartCarbon
     */
    private CarbonImmutable $importStartCarbon {
        get {
            return $this->importStartCarbon ??= CarbonImmutable::now();
        }
    }

    /**
     * @param \App\Containers\Contacts\Actions\GetAllContactsAction $getAllContactsAction
     * @param \App\Containers\Contacts\Actions\InsertContactsAction $insertContactsAction
     */
    public function __construct(
        private readonly GetAllContactsAction $getAllContactsAction,
        private readonly InsertContactsAction $insertContactsAction
    ) {
    }

    /**
     * @param string $importId
     * @param string $path
     *
     * @throws \Throwable
     */
    public function run(string $importId, string $path): void
    {
        $startedAt = \microtime(true);

        Cache::put(
            self::getCacheKey($importId),
            [self::CACHE_ATTR_STATUS => ImportStatusesEnum::PROCESSING],
            $this->importStartCarbon->addHours(24)
        );

        $xml = \simplexml_load_file(Storage::path($path));
        $total = \count($xml->item);
        $existingEmails = $this->getAllContactsAction->query()
            ->pluck(Contact::ATTR_EMAIL)
            ->flip()
            ->all();

        $imported = 0;
        $duplicates = 0;
        $invalid = 0;
        $duplicateItems = [];
        $invalidItems = [];

        foreach (\array_chunk(\iterator_to_array($xml->item, false), self::ITEMS_IN_INSERT_CHUNK) as $chunk) {
            $toInsert = [];

            foreach ($chunk as $item) {
                $firstName = \trim((string) $item->first_name);
                $lastName = \trim((string) $item->last_name);
                $email = Str::lower(\trim((string) $item->email));

                $raw = \compact('firstName', 'lastName', 'email');

                if (
                    empty($email)
                    || empty($firstName)
                    || empty($lastName)
                    || ! $this->isEmailValid($email)
                ) {
                    $invalid++;
                    $invalidItems[] = $raw;
                    continue;
                }

                if (isset($existingEmails[$email])) {
                    $duplicates++;
                    $duplicateItems[] = $raw;
                    continue;
                }

                $existingEmails[$email] = true;

                $dto = new ContactDTO();
                $dto->setEmail($email);
                $dto->setFirstName($firstName);
                $dto->setLastName($lastName);

                $createdAt = CarbonImmutable::now();

                $toInsert[] =  new Contact()
                    ->compactFill($dto->getAttributes())
                    ->setCreatedAt($createdAt)
                    ->setUpdatedAt($createdAt)
                    ->getAttributes();;
            }

            if (! empty($toInsert)) {
                $this->insertContactsAction->run($toInsert);
                $imported += \count($toInsert);
            }
        }

        Storage::delete($path);

        Cache::put(self::getCacheKey($importId), [
            self::CACHE_ATTR_STATUS => ImportStatusesEnum::DONE->value,
            self::IMPORT_DONE_ATTRIBUTE_TOTAL => $total,
            self::IMPORT_DONE_ATTRIBUTE_IMPORTED => $imported,
            self::IMPORT_DONE_ATTRIBUTE_DUPLICATES => $duplicates,
            self::IMPORT_DONE_ATTRIBUTE_INVALID => $invalid,
            self::IMPORT_DONE_ATTRIBUTE_DURATION => \round(\microtime(true) - $startedAt, 2),
        ], $this->importStartCarbon->addHours(24));

        if (!empty($invalidItems)) {
            Cache::put(self::getCacheKeyForInvalid($importId), $invalidItems, $this->importStartCarbon->addHours(24));
        }

        if (!empty($duplicateItems)) {
            Cache::put(self::getCacheKeyForDuplicates($importId), $duplicateItems, $this->importStartCarbon->addHours(24));
        }
    }

    /**
     * @param string $importId
     */
    public function fail(string $importId): void
    {
        Cache::put(
            self::getCacheKey($importId),
            [self::CACHE_ATTR_STATUS => ImportStatusesEnum::FAILED->value],
            $this->importStartCarbon->addHours(24)
        );
    }

    /**
     * @param string $importId
     *
     * @return string
     */
    public static function getCacheKeyForDuplicates(string $importId): string
    {
        return self::getCacheKey($importId) . self::CACHE_DUPLICATES_SUFFIX;
    }

    /**
     * @param string $importId
     *
     * @return string
     */
    public static function getCacheKeyForInvalid(string $importId): string
    {
        return self::getCacheKey($importId) . self::CACHE_INVALID_SUFFIX;
    }

    /**
     * @param string $email
     *
     * @return bool
     */
    private function isEmailValid(string $email): bool
    {
        return (bool) filter_var($email, FILTER_VALIDATE_EMAIL, FILTER_FLAG_EMAIL_UNICODE);
    }
}
