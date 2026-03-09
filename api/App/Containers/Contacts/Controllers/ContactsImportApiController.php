<?php

declare(strict_types=1);

namespace App\Containers\Contacts\Controllers;

use App\Containers\Contacts\Jobs\ProcessContactImportJob;
use App\Containers\Contacts\Requests\ContactImportRequestFilter;
use App\Containers\Contacts\Services\ContactXmlCachedImportManager;
use App\Containers\Imports\Services\CachedImportManager;
use App\Ship\Parents\Controllers\ApiController;
use App\Ship\Responses\ApiResponse;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ContactsImportApiController extends ApiController
{
    /**
     * @param \App\Containers\Contacts\Requests\ContactImportRequestFilter $requestFilter
     * @param \Illuminate\Http\Request $request
     *
     * @return \App\Ship\Responses\ApiResponse
     */
    public function store(
        ContactImportRequestFilter $requestFilter,
        Request $request
    ): ApiResponse {
        $path = $requestFilter->getValidatedFile($request)->store('imports');
        $importId = (string) Str::uuid();

        CachedImportManager::putPendingJobStatusToCache($importId);

        ProcessContactImportJob::dispatch($importId, $path);

        return $this->arrayResponse([
            'import_id' => $importId,
            'status_url' => route('imports.show', $importId),
        ]);
    }

    /**
     * @param string $importId
     *
     * @return \App\Ship\Responses\ApiResponse
     */
    public function failures(string $importId): ApiResponse
    {
        if (! Cache::has(CachedImportManager::getCacheKey($importId))) {
            throw new NotFoundHttpException(\trans('imports.errors.not_found'));
        }

        return $this->arrayResponse([
            'import_id' => $importId,
            'duplicates' => Cache::pull(ContactXmlCachedImportManager::getCacheKeyForDuplicates($importId)) ?? [],
            'invalid' => Cache::pull(ContactXmlCachedImportManager::getCacheKeyForInvalid($importId)) ?? [],
        ]);
    }
}
