<?php

declare(strict_types=1);

namespace App\Containers\Contacts\Models;

use App\Containers\Contacts\Contracts\ContactsQueryInterface;
use App\Containers\Contacts\Queries\ContactsQueryBuilder;
use App\Ship\Values\Enums\CastTypesEnum;
use Carbon\CarbonImmutable;
use Database\Factories\ContactFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class Contact extends Model
{
    use HasFactory;

    /**
     * Attributes of the model.
     */
    final public const string ATTR_ID = 'id';

    final public const string ATTR_FIRST_NAME = 'first_name';

    final public const string ATTR_LAST_NAME = 'last_name';

    final public const string ATTR_EMAIL = 'email';

    final public const string ATTR_CREATED_AT = self::CREATED_AT;

    final public const string ATTR_UPDATED_AT = self::UPDATED_AT;

    /**
     * Model limits.
     */
    final public const int LIMIT_FIRST_NAME = 50;

    final public const int LIMIT_LAST_NAME = 50;

    final public const int LIMIT_EMAIL = 255;

    /**
     * @inheritDoc
     * @var string
     */
    protected $table = 'contact';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        self::ATTR_FIRST_NAME,
        self::ATTR_LAST_NAME,
        self::ATTR_EMAIL,
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var string[]
     */
    protected $casts = [
        self::ATTR_FIRST_NAME => CastTypesEnum::STRING->value,
        self::ATTR_LAST_NAME => CastTypesEnum::STRING->value,
        self::ATTR_EMAIL => CastTypesEnum::STRING->value,
    ];

    /**
     * @inheritDoc
     */
    protected static function newFactory(): ContactFactory
    {
        return ContactFactory::new();
    }

    /**
     * Create new model query.
     *
     * @return \App\Containers\Contacts\Contracts\ContactsQueryInterface
     */
    public function newModelQuery(): ContactsQueryInterface
    {
        return new ContactsQueryBuilder($this)->withoutGlobalScopes();
    }

    /**
     * Fill model with compact data.
     *
     * @param array<string,mixed> $data
     *
     * @return \App\Containers\Contacts\Models\Contact
     */
    public function compactFill(array $data): self
    {
        //default data
        $this->fill($data);

        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->getAttributeValue(self::ATTR_FIRST_NAME);
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->getAttributeValue(self::ATTR_LAST_NAME);
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->getAttributeValue(self::ATTR_EMAIL);
    }

    /**
     * @return \Carbon\CarbonImmutable
     */
    public function getCreatedAt(): CarbonImmutable
    {
        return $this->getAttributeValue(self::ATTR_CREATED_AT);
    }

    /**
     * @return \Carbon\CarbonImmutable
     */
    public function getUpdatedAt(): CarbonImmutable
    {
        return $this->getAttributeValue(self::ATTR_UPDATED_AT);
    }
}
