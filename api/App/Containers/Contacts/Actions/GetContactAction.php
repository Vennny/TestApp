<?php

declare(strict_types=1);

namespace App\Containers\Contacts\Actions;

use App\Containers\Contacts\Contracts\ContactsRepositoryInterface;
use App\Containers\Contacts\Models\Contact;

final readonly class GetContactAction
{
    /**
     * @param \App\Containers\Contacts\Contracts\ContactsRepositoryInterface $contactsRepository
     */
    public function __construct(
        private ContactsRepositoryInterface $contactsRepository
    ) {
    }

    /**
     * @param int $id
     *
     * @return \App\Containers\Contacts\Models\Contact
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function run(int $id): Contact
    {
        return $this->contactsRepository->get($id);
    }
}
