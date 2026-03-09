<?php

declare(strict_types=1);

namespace App\Ship\Parents\Queries;

use App\Ship\Contracts\QueryBuilderInterface;
use App\Ship\Services\DataHandler\Filtering\FilterOperatorEnum;
use App\Ship\Services\DataHandler\Filtering\FilterRule;
use App\Ship\Values\Enums\WhereBooleanEnum;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as FrameworkQueryBuilder;

class QueryBuilder extends Builder implements QueryBuilderInterface
{
    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    final public function __construct(Model $model)
    {
        $connection = $model->getConnection();

        $query = new FrameworkQueryBuilder(
            $connection,
            $connection->getQueryGrammar(),
            $connection->getPostProcessor()
        );

        parent::__construct($query);

        $model->registerGlobalScopes($this);
        $this->setModel($model);
    }

    /**
     * @inheritDoc
     *
     * @param \Closure|string|string[]|\Illuminate\Database\Query\Expression $column
     * @param string|\App\Ship\Values\Enums\WhereBooleanEnum $boolean
     */
    public function where($column, $operator = null, $value = null, $boolean = null): Builder | QueryBuilder | static
    {
        $boolean = $this->getBoolean($boolean);
        return parent::where($column, $operator, $value, $boolean);
    }

    /**
     * @param \App\Ship\Values\Enums\WhereBooleanEnum|string|null $boolean
     *
     * @return \App\Ship\Values\Enums\WhereBooleanEnum|mixed|string
     */
    private function getBoolean(WhereBooleanEnum | string | null $boolean): mixed
    {
        if ($boolean === null) {
            $boolean = WhereBooleanEnum::AND->value;
        }

        if ($boolean instanceof WhereBooleanEnum) {
            $boolean = $boolean->value;
        }

        return $boolean;
    }

    /**
     * @inheritDoc
     */
    final public function applyFilterRule(FilterRule $filterRule): void
    {
        $column = $filterRule->getColumn();
        $value = $filterRule->getValue();

        switch ($filterRule->getOperator()) {
            case FilterOperatorEnum::CONTAINS:
                $this->where($column, 'LIKE', '%' . $value . '%');
                break;
        }
    }

    /**
     * @inheritDoc
     */
    public function applyPagination(int $perPage, int $page): Paginator
    {
        return $this->paginate($perPage, page: $page);
    }

    /**
     * @inheritDoc
     */
    public function insertMany(array $items): bool
    {
        return $this->insert($items);
    }

    /**
     * @inheritDoc
     */
    public function toBaseBuilder(): \Illuminate\Database\Query\Builder
    {
        return $this->toBase();
    }
}
