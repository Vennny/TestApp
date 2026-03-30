<?php

declare(strict_types=1);

namespace App\Containers\Contacts\Tests;

use App\Containers\Contacts\Jobs\ProcessContactImportJob;
use App\Containers\Contacts\Models\Contact;
use App\Containers\Contacts\Requests\ContactImportRequestFilter;
use App\Ship\Values\Enums\MimeTypesEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class ContactImportApiTest extends TestCase
{
    use RefreshDatabase;

    public function testImportDispatchesJob(): void
    {
        Queue::fake();
        Storage::fake('local');

        $xmlContent = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<contacts>
    <item>
        <first_name>John</first_name>
        <last_name>Doe</last_name>
        <email>john.doe@example.com</email>
    </item>
</contacts>
XML;

        $file = UploadedFile::fake()
            ->createWithContent('contacts.xml', $xmlContent)
            ->mimeType(MimeTypesEnum::TEXT_XML->value);

        $response = $this->postJson('/api/contacts/import', [
            ContactImportRequestFilter::FIELD_FILE => $file,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['import_id', 'status_url']);

        Queue::assertPushed(ProcessContactImportJob::class);
    }

    public function testImportProcessesCorrectly(): void
    {
        Storage::fake('local');

        $xmlContent = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<contacts>
    <item>
        <first_name>John</first_name>
        <last_name>Doe</last_name>
        <email>john.doe@example.com</email>
    </item>
    <item>
        <first_name>Jane</first_name>
        <last_name>Smith</last_name>
        <email>jane.smith@example.com</email>
    </item>
    <item>
        <first_name>Duplicate</first_name>
        <last_name>User</last_name>
        <email>john.doe@example.com</email>
    </item>
    <item>
        <first_name></first_name>
        <last_name>Invalid</last_name>
        <email>invalid-email</email>
    </item>
</contacts>
XML;

        $file = UploadedFile::fake()
            ->createWithContent('contacts.xml', $xmlContent)
            ->mimeType(MimeTypesEnum::TEXT_XML->value);

        $response = $this->postJson('/api/contacts/import', [
            ContactImportRequestFilter::FIELD_FILE => $file,
        ]);

        $importId = $response->json('import_id');

        $this->assertDatabaseHas('contact', [
            Contact::ATTR_EMAIL => 'john.doe@example.com',
            Contact::ATTR_FIRST_NAME => 'John',
        ]);
        $this->assertDatabaseHas('contact', [
            Contact::ATTR_EMAIL => 'jane.smith@example.com',
            Contact::ATTR_FIRST_NAME => 'Jane',
        ]);
        $this->assertDatabaseCount('contact', 2);

        $response = $this->getJson("/api/contacts/import/$importId/failures");
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'duplicates');
        $response->assertJsonCount(1, 'invalid');
    }
}
