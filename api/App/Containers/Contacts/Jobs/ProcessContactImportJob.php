<?php

declare(strict_types=1);

namespace App\Containers\Contacts\Jobs;

use App\Containers\Contacts\Services\ContactXmlCachedImportManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class ProcessContactImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries = 1;

    /**
     * @param string $importId
     * @param string $path
     */
    public function __construct(
        private readonly string $importId,
        private readonly string $path,
    ) {}

    /**
     * @param \App\Containers\Contacts\Services\ContactXmlCachedImportManager $contactXmlImportManager
     *
     * @throws \Throwable
     */
    public function handle(
        ContactXmlCachedImportManager $contactXmlImportManager
    ): void {
        $contactXmlImportManager->run($this->importId, $this->path);
    }

    /**
     * @param \Throwable $e
     */
    public function failed(\Throwable $e): void
    {
        app(ContactXmlCachedImportManager::class)->fail($this->importId);
    }
}
