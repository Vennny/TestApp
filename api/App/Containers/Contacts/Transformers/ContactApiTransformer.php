<?php

declare(strict_types=1);

namespace App\Containers\Contacts\Transformers;

use App\Containers\Contacts\Models\Contact;
use App\Ship\Parents\Transformers\ApiTransformer;

final class ContactApiTransformer extends ApiTransformer
{
    final public const string PROP_ID = Contact::ATTR_ID;

    final public const string PROP_FIRST_NAME = Contact::ATTR_FIRST_NAME;

    final public const string PROP_LAST_NAME = Contact::ATTR_LAST_NAME;

    final public const string PROP_EMAIL = Contact::ATTR_EMAIL;

    final public const string PROP_CREATED_AT = Contact::ATTR_CREATED_AT;

    final public const string PROP_UPDATED_AT = Contact::ATTR_UPDATED_AT;

    /**
     * @param \App\Containers\Contacts\Models\Contact $contact
     *
     * @return mixed[]
     */
    public function transform(Contact $contact): array
    {
        return [
            self::PROP_ID => $contact->getKey(),
            self::PROP_FIRST_NAME => $contact->getFirstName(),
            self::PROP_LAST_NAME => $contact->getLastName(),
            self::PROP_EMAIL => $contact->getEmail(),
            self::PROP_CREATED_AT => $this->formatDateTime($contact->getCreatedAt()),
            self::PROP_UPDATED_AT => $this->formatDateTime($contact->getUpdatedAt()),
        ];
    }

    /**
     * @inheritDoc
     */
    public function filters(): array
    {
        return [
            self::PROP_ID,
            self::PROP_FIRST_NAME,
            self::PROP_LAST_NAME,
            self::PROP_EMAIL,
            self::PROP_CREATED_AT,
            self::PROP_UPDATED_AT,
        ];
    }
}
