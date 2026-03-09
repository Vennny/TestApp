<?php

declare(strict_types=1);

namespace App\Containers\Contacts\Actions;

use App\Containers\Contacts\Contracts\ContactsQueryInterface;
use App\Containers\Contacts\Contracts\ContactsRepositoryInterface;
use Illuminate\Support\Collection;

final readonly class GetAllContactsAction
{
    /**
     * @param \App\Containers\Contacts\Contracts\ContactsRepositoryInterface $contactsRepository
     */
    public function __construct(
        private ContactsRepositoryInterface $contactsRepository
    ) {
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getAll(): Collection
    {
        return $this->query()->get();
    }

    /**
     * @return \App\Containers\Contacts\Contracts\ContactsQueryInterface
     */
    public function query(): ContactsQueryInterface
    {
        return $this->contactsRepository->query();
    }
}
