<?php

declare(strict_types=1);

namespace App\Ship\Contracts;

use App\Ship\Services\DataHandler\Filtering\FilterRule;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Query\Builder;

interface QueryBuilderInterface
{
    /**
     * @param \App\Ship\Services\DataHandler\Filtering\FilterRule $filterRule
     */
    public function applyFilterRule(FilterRule $filterRule): void;

    /**
     * @param int $perPage
     * @param int $page
     *
     * @return \Illuminate\Contracts\Pagination\Paginator
     */
    public function applyPagination(int $perPage, int $page): Paginator;

    /**
     * @param array<\Illuminate\Database\Eloquent\Model> $items
     *
     * @return bool
     */
    public function insertMany(array $items): bool;

    /**
     * @return \Illuminate\Database\Query\Builder
     */
    public function toBaseBuilder(): Builder;
}
