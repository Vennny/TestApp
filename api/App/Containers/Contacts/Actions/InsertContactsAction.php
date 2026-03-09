<?php

declare(strict_types=1);

namespace App\Containers\Contacts\Actions;

use App\Containers\Contacts\Contracts\ContactsRepositoryInterface;
use App\Containers\Contacts\Models\Contact;
use App\Containers\Contacts\Values\DTOs\ContactDTO;
use Illuminate\Database\DatabaseManager;

final readonly class InsertContactsAction
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
     * @param array $items
     *
     * @return bool
     *
     * @throws \Throwable
     */
    public function run(array $items): bool
    {
        return $this->databaseManager->transaction(function () use ($items): bool {
            return $this->contactsRepository->insert($items);
        });
    }
}
