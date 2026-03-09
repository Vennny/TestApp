<?php

declare(strict_types=1);

namespace App\Containers\Imports\Services;

use App\Containers\Imports\Enums\ImportStatusesEnum;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;

abstract class CachedImportManager
{
    const string CACHE_ATTR_STATUS = 'status';

    /**
     * @param string $importId
     *
     * @return string
     */
    public static function getCacheKey(string $importId): string
    {
        return "import:$importId";
    }

    /**
     * @param string $importId
     */
    public static function putPendingJobStatusToCache(string $importId): void
    {
        Cache::put(
            self::getCacheKey($importId),
            [self::CACHE_ATTR_STATUS => ImportStatusesEnum::PENDING->value],
            CarbonImmutable::now()->addHours(24)
        );
    }
}
