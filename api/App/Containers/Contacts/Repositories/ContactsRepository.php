<?php

declare(strict_types=1);

namespace App\Containers\Contacts\Repositories;

use App\Containers\Contacts\Contracts\ContactsQueryInterface;
use App\Containers\Contacts\Contracts\ContactsRepositoryInterface;
use App\Containers\Contacts\Models\Contact;
use App\Containers\Contacts\Queries\ContactsQueryBuilder;
use Illuminate\Support\Collection;

final readonly class ContactsRepository implements ContactsRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function get(int $id): Contact
    {
        /** @var \App\Containers\Contacts\Models\Contact $contact */
        $contact = $this->query()->findOrFail($id);
        return $contact;
    }

    /**
     * @inheritDoc
     */
    public function getAll(): Collection
    {
        return $this->query()->get();
    }

    /**
     * @inheritDoc
     */
    public function create(array $data): Contact
    {
        $contact = new Contact();
        $contact->compactFill($data);
        $this->save($contact);

        return $contact;
    }

    /**
     * @inheritDoc
     */
    public function insert(array $data): bool
    {
        return $this->query()->insertMany($data);
    }

    /**
     * @inheritDoc
     */
    public function update(Contact $contact, array $data): Contact
    {
        $contact->compactFill($data);
        $this->save($contact);

        return $contact;
    }

    /**
     * @inheritDoc
     */
    public function save(Contact $contact): void
    {
        $contact->save();
    }

    /**
     * @inheritDoc
     */
    public function delete(Contact $contact): void
    {
        $contact->delete();
    }

    /**
     * @inheritDoc
     */
    public function query(): ContactsQueryInterface
    {
        return new ContactsQueryBuilder(new Contact());
    }
}
