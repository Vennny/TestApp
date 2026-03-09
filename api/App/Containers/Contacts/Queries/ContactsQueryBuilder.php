<?php

declare(strict_types=1);

namespace App\Containers\Contacts\Queries;

use App\Containers\Contacts\Contracts\ContactsQueryInterface;
use App\Containers\Contacts\Models\Contact;
use App\Ship\Parents\Queries\QueryBuilder;
use App\Ship\Values\Enums\WhereBooleanEnum;

/**
 * @property \App\Containers\Contacts\Models\Contact $model
 */
final class ContactsQueryBuilder extends QueryBuilder implements ContactsQueryInterface
{
    /**
     * @inheritDoc
     */
    public function whereFirstName(string $value, ?WhereBooleanEnum $boolean = null): self
    {
        return $this->where(Contact::ATTR_FIRST_NAME, '=', $value, $boolean);
    }


    /**
     * @inheritDoc
     */
    public function whereLastName(string $value, ?WhereBooleanEnum $boolean = null): self
    {
        return $this->where(Contact::ATTR_LAST_NAME, '=', $value, $boolean);
    }

    /**
     * @inheritDoc
     */
    public function whereEmail(string $value, ?WhereBooleanEnum $boolean = null): self
    {
        return $this->where(Contact::ATTR_EMAIL, '=', $value, $boolean);
    }
}
