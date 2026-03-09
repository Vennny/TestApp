<?php

declare(strict_types=1);

namespace App\Ship\Services\DataHandler\Filtering;

final readonly class FilterRule
{
    /**
     * @param string $column
     * @param mixed $value
     * @param \App\Ship\Services\DataHandler\Filtering\FilterOperatorEnum $operator
     */
    public function __construct(
        private string $column,
        private mixed $value,
        private FilterOperatorEnum $operator
    ) {
    }

    /**
     * @return string
     */
    public function getColumn(): string
    {
        return $this->column;
    }

    /**
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * @return \App\Ship\Services\DataHandler\Filtering\FilterOperatorEnum
     */
    public function getOperator(): FilterOperatorEnum
    {
        return $this->operator;
    }
}
