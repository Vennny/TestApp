<?php

declare(strict_types=1);

namespace App\Containers\Contacts\Contracts;

use App\Ship\Contracts\QueryBuilderInterface;
use App\Ship\Values\Enums\WhereBooleanEnum;

interface ContactsQueryInterface extends QueryBuilderInterface
{
   /**
     * @param string $value
     * @param \App\Ship\Values\Enums\WhereBooleanEnum|null $boolean
     *
     * @return \App\Containers\Contacts\Contracts\ContactsQueryInterface
     */
    public function whereFirstName(string $value, ?WhereBooleanEnum $boolean = null): self;

    /**
     * @param string $value
     * @param \App\Ship\Values\Enums\WhereBooleanEnum|null $boolean
     *
     * @return \App\Containers\Contacts\Contracts\ContactsQueryInterface
     */
    public function whereLastName(string $value, ?WhereBooleanEnum $boolean = null): self;

    /**
     * @param string $value
     * @param \App\Ship\Values\Enums\WhereBooleanEnum|null $boolean
     *
     * @return \App\Containers\Contacts\Contracts\ContactsQueryInterface
     */
    public function whereEmail(string $value, ?WhereBooleanEnum $boolean = null): self;

}
