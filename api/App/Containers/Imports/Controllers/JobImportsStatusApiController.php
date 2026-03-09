<?php

declare(strict_types=1);

namespace App\Containers\Imports\Controllers;

use App\Containers\Imports\Services\CachedImportManager;
use App\Ship\Parents\Controllers\ApiController;
use App\Ship\Responses\ApiResponse;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class JobImportsStatusApiController extends ApiController
{
    /**
     * @param string $importId
     *
     * @return \App\Ship\Responses\ApiResponse
     */
    public function show(string $importId): ApiResponse
    {
        //cant take cache from manager instance, has to be from parent
        $data = Cache::get(CachedImportManager::getCacheKey($importId));

        if (! $data) {
            throw new NotFoundHttpException(\trans('imports.errors.not_found'));
        }

        return $this->arrayResponse($data);
    }
}
