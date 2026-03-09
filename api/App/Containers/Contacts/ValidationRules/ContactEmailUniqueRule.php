<?php

declare(strict_types=1);

namespace App\Containers\Contacts\ValidationRules;

use App\Containers\Contacts\Contracts\ContactsRepositoryInterface;
use App\Containers\Contacts\Models\Contact;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final readonly class ContactEmailUniqueRule implements ValidationRule
{
    /**
     * @param \App\Containers\Contacts\Contracts\ContactsRepositoryInterface $contactsRepository
     * @param \App\Containers\Contacts\Models\Contact|null $contact
     */
    public function __construct(
        private ContactsRepositoryInterface $contactsRepository,
        private ?Contact $contact = null,
    ) {
    }

    /**
     * @param string $attribute
     * @param mixed $value
     * @param \Closure $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $query = $this->contactsRepository->query()
            ->whereEmail($value);

        if ($this->contact) {
            $query->whereKeyNot($this->contact->getKey());
        }

        if ($query->toBaseBuilder()->exists()) {
            $fail('contacts.validation_errors.email_not_unique')->translate();
        }
    }
}
