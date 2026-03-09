<?php

declare(strict_types=1);

namespace App\Ship\Services\DataHandler\Filtering;

use App\Ship\Contracts\QueryBuilderInterface;

final class DataFilteringHandler
{
    /**
     * @var array<\App\Ship\Services\DataHandler\Filtering\FilterRule|\App\Ship\Services\DataHandler\Filtering\FilterRule[]>
     */
    private array $ruleSet = [];

    /**
     * @param string $input
     * @param string[] $fields
     *
     * @throws \App\Ship\Exceptions\InvalidJsonException
     * @throws \App\Ship\Services\DataHandler\Exceptions\InvalidFilterFormatException
     * @throws \App\Ship\Services\DataHandler\Exceptions\UnexpectedFilterOperationException
     */
    public function setupFromInput(string $input, array $fields): void
    {
        foreach (FilterRuleGenerator::fromInput($input, $fields) as $ruleSet) {
            $this->ruleSet[] = $ruleSet;
        }
    }

    /**
     * @param \App\Ship\Contracts\QueryBuilderInterface $query
     */
    public function applyFiltersOnQuery(QueryBuilderInterface $query): void
    {
        foreach ($this->ruleSet as $ruleSet) {
            if ($ruleSet instanceof FilterRule) {
                $query->applyFilterRule($ruleSet);
            }
        }
    }
}
