<?php

declare(strict_types=1);

namespace App\Containers\Contacts\Actions;

use App\Containers\Contacts\Contracts\ContactsRepositoryInterface;
use App\Containers\Contacts\Models\Contact;
use App\Containers\Contacts\Values\DTOs\ContactDTO;
use Illuminate\Database\DatabaseManager;

final readonly class UpdateContactAction
{
    /**
     * @param \App\Containers\Contacts\Contracts\ContactsRepositoryInterface $contactsRepository
     * @param \Illuminate\Database\DatabaseManager $databaseManager
     */
    public function __construct(
        private ContactsRepositoryInterface $contactsRepository,
        private DatabaseManager $databaseManager
    ) {
    }

    /**
     * @param \App\Containers\Contacts\Models\Contact $contact
     * @param \App\Containers\Contacts\Values\DTOs\ContactDTO $dto
     *
     * @return \App\Containers\Contacts\Models\Contact
     *
     * @throws \Throwable
     */
    public function run(Contact $contact, ContactDTO $dto): Contact
    {
        return $this->databaseManager->transaction(function () use ($contact, $dto): Contact {
            $data = $dto->getAttributes();

            $this->contactsRepository->update($contact, $data);

            return $contact;
        });
    }
}
