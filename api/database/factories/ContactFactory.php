<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Containers\Contacts\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Containers\Contacts\Models\Contact>
 */
class ContactFactory extends Factory
{
    /**
     * @inheritdoc
     */
    protected $model = Contact::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            Contact::ATTR_FIRST_NAME => $this->faker->firstName,
            Contact::ATTR_LAST_NAME => $this->faker->firstName,
            Contact::ATTR_EMAIL => $this->faker->userName . '@gmail.com',
        ];
    }
}
