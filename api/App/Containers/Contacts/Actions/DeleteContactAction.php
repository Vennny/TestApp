<?php

declare(strict_types=1);

namespace App\Containers\Contacts\Actions;

use App\Containers\Contacts\Contracts\ContactsRepositoryInterface;
use App\Containers\Contacts\Models\Contact;
use Illuminate\Database\DatabaseManager;

final readonly class DeleteContactAction
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
     * @throws \Throwable
     */
    public function run(Contact $contact): void
    {
        $this->databaseManager->transaction(function () use ($contact): void {
            $this->contactsRepository->delete($contact);
        });
    }
}
