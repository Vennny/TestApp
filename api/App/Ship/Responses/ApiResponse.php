<?php

declare(strict_types=1);

namespace App\Ship\Responses;

use Illuminate\Http\JsonResponse;

final class ApiResponse extends JsonResponse
{
    final public const string COLLECTION_DATA_KEY = 'items';

    final public const string PAGINATION_META_KEY = '_meta';

    final public const string PAGINATION_PAGE_KEY = 'page';

    final public const string PAGINATION_TOTAL_PAGES_KEY = 'total_pages';

    final public const string PAGINATION_RECORDS_KEY = 'records';

    final public const string PAGINATION_PER_PAGE_KEY = 'per_page';
}
