<?php

declare(strict_types=1);

namespace App\Containers\Contacts\Contracts;

use App\Containers\Contacts\Models\Contact;
use App\Containers\Contacts\Queries\ContactsQueryBuilder;
use Illuminate\Support\Collection;

interface ContactsRepositoryInterface
{
    /**
     * @param int $id
     *
     * @return \App\Containers\Contacts\Models\Contact
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function get(int $id): Contact;

    /**
     * @return \Illuminate\Support\Collection<\App\Containers\Contacts\Models\Contact>
     */
    public function getAll(): Collection;

    /**
     * @param array<string,mixed> $data
     * @return \App\Containers\Contacts\Models\Contact
     */
    public function create(array $data): Contact;

    /**
     * @param array<array<string, mixed>> $data
     *
     * @return bool
     */
    public function insert(array $data): bool;

    /**
     * @param \App\Containers\Contacts\Models\Contact $contact
     * @param array<string,mixed> $data
     *
     * @return \App\Containers\Contacts\Models\Contact
     */
    public function update(Contact $contact, array $data): Contact;

    /**
     * @param \App\Containers\Contacts\Models\Contact $contact
     */
    public function save(Contact $contact): void;

    /**
     * @param \App\Containers\Contacts\Models\Contact $contact
     */
    public function delete(Contact $contact): void;

    /**
     * @return \App\Containers\Contacts\Contracts\ContactsQueryInterface
     */
    public function query(): ContactsQueryInterface;
}
