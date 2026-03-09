<?php

declare(strict_types=1);

namespace App\Containers\Contacts\Requests;

use App\Containers\Contacts\Contracts\ContactsRepositoryInterface;
use App\Containers\Contacts\Models\Contact;
use App\Containers\Contacts\ValidationRules\ContactEmailUniqueRule;
use App\Containers\Contacts\Values\DTOs\ContactDTO;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory as ValidatorFactory;
use Illuminate\Validation\Rules\Email;
use Illuminate\Validation\ValidationException;

final class ContactRequestFilter
{
    /**
     * Fields.
     */
    final public const string FIELD_FIRST_NAME = Contact::ATTR_FIRST_NAME;

    final public const string FIELD_LAST_NAME = Contact::ATTR_LAST_NAME;

    final public const string FIELD_EMAIL = Contact::ATTR_EMAIL;

    /**
     * Limits.
     */
    final public const int LIMIT_FIRST_NAME = Contact::LIMIT_FIRST_NAME;

    final public const int LIMIT_LAST_NAME = Contact::LIMIT_LAST_NAME;

    final public const int LIMIT_EMAIL = Contact::LIMIT_EMAIL;

    /**
     * @param \Illuminate\Validation\Factory $validatorFactory
     * @param \App\Containers\Contacts\Contracts\ContactsRepositoryInterface $contactsRepository
     */
    public function __construct(
        private readonly ValidatorFactory $validatorFactory,
        private readonly ContactsRepositoryInterface $contactsRepository
    ) {
    }

    /**
     * Get values for model.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Containers\Contacts\Models\Contact|null $contact
     *
     * @return \App\Containers\Contacts\Values\DTOs\ContactDTO
     */
    public function getValidatedData(Request $request, ?Contact $contact = null): ContactDTO {
        $fields = $this->validate($request, $contact);
        $rawData = $request->only($fields);
        $dto = new ContactDTO();

        if (\array_key_exists(self::FIELD_FIRST_NAME, $rawData)) {
            $dto->setFirstName($rawData[self::FIELD_FIRST_NAME]);
        }

        if (\array_key_exists(self::FIELD_LAST_NAME, $rawData)) {
            $dto->setLastName($rawData[self::FIELD_LAST_NAME]);
        }

        if (\array_key_exists(self::FIELD_EMAIL, $rawData)) {
            $dto->setEmail($rawData[self::FIELD_EMAIL]);
        }

        return $dto;
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \App\Containers\Contacts\Models\Contact|null $contact
     *
     * @return string[]
     */
    public function validate(Request $request, ?Contact $contact = null): array
    {
        $rules = $this->getRules($request, $contact);
        $validator = $this->validatorFactory->make($request->all(), $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return \array_keys($rules);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \App\Containers\Contacts\Models\Contact|null $contact
     *
     * @return mixed[]
     */
    private function getRules(Request $request, ?Contact $contact = null): array
    {
        $sometimesRequired = $request->isMethod(Request::METHOD_PATCH) ? 'sometimes' : 'required';

        return [
            self::FIELD_FIRST_NAME => [
                $sometimesRequired,
                'string',
                'max:' . self::LIMIT_FIRST_NAME,
            ],
            self::FIELD_LAST_NAME => [
                $sometimesRequired,
                'string',
                'max:' . self::LIMIT_LAST_NAME,
            ],
            self::FIELD_EMAIL => [
                $sometimesRequired,
                Email::default()->rfcCompliant(),
                'max:' . self::LIMIT_EMAIL,
                new ContactEmailUniqueRule($this->contactsRepository, $contact),
            ],
        ];
    }
}
