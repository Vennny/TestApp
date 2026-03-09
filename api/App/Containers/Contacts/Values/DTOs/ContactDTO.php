<?php

declare(strict_types=1);

namespace App\Containers\Contacts\Values\DTOs;

use App\Containers\Contacts\Models\Contact;
use App\Ship\Parents\DataTransferObjects\DataTransferObject;

final class ContactDTO extends DataTransferObject
{
    /**
     * @param string $value
     */
    public function setFirstName(string $value): void
    {
        $this->attributes[Contact::ATTR_FIRST_NAME] = $value;
    }

    /**
     * @param string $value
     */
    public function setLastName(string $value): void
    {
        $this->attributes[Contact::ATTR_LAST_NAME] = $value;
    }

    /**
     * @param string $value
     */
    public function setEmail(string $value): void
    {
        $this->attributes[Contact::ATTR_EMAIL] = $value;
    }

    /**
     * @param string|null $value
     */
    public function setPhone(?string $value): void
    {
        $this->attributes[Contact::ATTR_PHONE] = $value;
    }

    /**
     * @param string|null $value
     */
    public function setNote(?string $value): void
    {
        $this->attributes[Contact::ATTR_NOTE] = $value;
    }
}
