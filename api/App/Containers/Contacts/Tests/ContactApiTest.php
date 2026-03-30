<?php

declare(strict_types=1);

namespace App\Containers\Contacts\Tests;

use App\Containers\Contacts\Models\Contact;
use App\Containers\Contacts\Requests\ContactRequestFilter;
use App\Containers\Contacts\Transformers\ContactApiTransformer;
use App\Ship\Responses\ApiResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

final class ContactApiTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    /**
     * @var string
     */
    protected string $uri = '/contacts';

    /**
     * @var string[]
     */
    private static array $structure = [
        ContactApiTransformer::PROP_ID,
        ContactApiTransformer::PROP_FIRST_NAME,
        ContactApiTransformer::PROP_LAST_NAME,
        ContactApiTransformer::PROP_EMAIL,
        ContactApiTransformer::PROP_CREATED_AT,
        ContactApiTransformer::PROP_UPDATED_AT,
    ];

    public function testIndex(): void
    {
        Contact::factory()->count(3)->create();

        $this->getJson('/api/contacts')
            ->assertStatus(200)
            ->assertJsonCount(3, ApiResponse::COLLECTION_DATA_KEY)
            ->assertJsonStructure([
                ApiResponse::COLLECTION_DATA_KEY => [
                    '*' => self::$structure
                ]
            ]);
    }

    public function testShow(): void
    {
        $item = Contact::factory()->create();

        $response = $this->getJson('/api/contacts/ ' . $item->getKey());

        $response->assertStatus(200);
        $response->assertJsonStructure(self::$structure);
    }

    public function testStoreOk(): void
    {
        $firstName = $this->faker->firstName;
        $lastName = $this->faker->lastName;
        $email = $this->faker->userName . '@gmail.com';

        $this->postJson('/api/contacts', [
            ContactRequestFilter::FIELD_FIRST_NAME => $firstName,
            ContactRequestFilter::FIELD_LAST_NAME => $lastName,
            ContactRequestFilter::FIELD_EMAIL => $email,
        ])
            ->assertStatus(201)
            ->assertJsonStructure(self::$structure);

        $this->assertDatabaseHas('contact', [
            Contact::ATTR_FIRST_NAME => $firstName,
            Contact::ATTR_LAST_NAME => $lastName,
            Contact::ATTR_EMAIL => $email,
        ]);
    }

    public function testUpdatePutOk(): void
    {
        $item = Contact::factory()->create();

        $firstName = $this->faker->firstName;
        $lastName = $this->faker->lastName;
        $email = $this->faker->userName . '@gmail.com';

        $this->putJson('/api/contacts/' . $item->getKey(), [
            ContactRequestFilter::FIELD_FIRST_NAME => $firstName,
            ContactRequestFilter::FIELD_LAST_NAME => $lastName,
            ContactRequestFilter::FIELD_EMAIL => $email,
        ])
            ->assertStatus(200)
            ->assertJsonStructure(self::$structure);

        $this->assertDatabaseHas('contact', [
            Contact::ATTR_FIRST_NAME => $firstName,
            Contact::ATTR_LAST_NAME => $lastName,
            Contact::ATTR_EMAIL => $email,
        ]);
    }

    public function testUpdatePatchOk(): void
    {
        $item = Contact::factory()->create();

        $firstName = $this->faker->firstName;

        $this->patchJson('/api/contacts/' . $item->getKey(), [
            ContactRequestFilter::FIELD_FIRST_NAME => $firstName,
        ])
            ->assertStatus(200)
            ->assertJsonStructure(self::$structure);

        $this->assertDatabaseHas('contact', [
            Contact::ATTR_FIRST_NAME => $firstName,
        ]);
    }

    public function testDeleteOk(): void
    {
        $item = Contact::factory()->create();

        $response = $this->deleteJson('/api/contacts/' . $item->getKey());

        $response->assertStatus(204);

        $this->assertDatabaseEmpty('contact');
    }

    public function testStoreValidationFails(): void
    {
        $firstName = $this->faker->firstName;
        $lastName = $this->faker->lastName;
        $email = 'notvalid';

        $this->postJson('/api/contacts', [
            ContactRequestFilter::FIELD_FIRST_NAME => $firstName,
            ContactRequestFilter::FIELD_LAST_NAME => $lastName,
            ContactRequestFilter::FIELD_EMAIL => $email,
        ])
            ->assertStatus(422);
    }
}
