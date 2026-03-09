<?php

declare(strict_types=1);

namespace App\Containers\Contacts\Actions;

use App\Containers\Contacts\Contracts\ContactsRepositoryInterface;
use App\Containers\Contacts\Models\Contact;
use App\Containers\Contacts\Values\DTOs\ContactDTO;
use Illuminate\Database\DatabaseManager;

final readonly class CreateContactAction
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
     * @param \App\Containers\Contacts\Values\DTOs\ContactDTO $dto
     *
     * @return \App\Containers\Contacts\Models\Contact
     *
     * @throws \Throwable
     */
    public function run(ContactDTO $dto): Contact
    {
        return $this->databaseManager->transaction(function () use ($dto): Contact {
            return $this->contactsRepository->create( $dto->getAttributes());
        });
    }
}
