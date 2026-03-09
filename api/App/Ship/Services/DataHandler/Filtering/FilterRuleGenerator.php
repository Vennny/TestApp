<?php

declare(strict_types=1);

namespace App\Ship\Services\DataHandler\Filtering;

use App\Ship\Exceptions\InvalidJsonException;
use App\Ship\Services\DataHandler\Exceptions\InputsNotSupportedException;
use App\Ship\Services\DataHandler\Exceptions\InvalidFilterFormatException;
use App\Ship\Services\DataHandler\Exceptions\UnexpectedFilterOperationException;

final class FilterRuleGenerator
{
    final public const string FIELD_OPERATION = 'o';

    final public const string FIELD_VALUE = 'v';

    final public const string FIELD_FIELD = 'f';

    /**
     * Create filter data from given string.
     *
     * @param string $input
     * @param string[] $fields
     *
     * @return \App\Ship\Services\DataHandler\Filtering\FilterRule[]
     *
     * @throws \App\Ship\Exceptions\InvalidJsonException
     * @throws \App\Ship\Services\DataHandler\Exceptions\InvalidFilterFormatException
     * @throws \App\Ship\Services\DataHandler\Exceptions\UnexpectedFilterOperationException
     */
    public static function fromInput(string $input, array $fields): array
    {
        $input = \base64_decode(\str_replace(' ', '+', $input), true);
        if ($input === false) {
            throw new \InvalidArgumentException('Invalid input in base64.');
        }

        $filterItems = \json_decode($input, true);

        if (\json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidJsonException(\json_last_error_msg());
        }

        $filterRules = [];
        foreach ($filterItems as $filterItem) {
            $filterRules[] = self::parseFilterItemToRule($filterItem, $fields);
        }

        return $filterRules;
    }

    /**
     * @param mixed[] $item
     * @param string[] $transformerFilterFields
     *
     * @return \App\Ship\Services\DataHandler\Filtering\FilterRule
     *
     * @throws \App\Ship\Services\DataHandler\Exceptions\InvalidFilterFormatException
     * @throws \App\Ship\Services\DataHandler\Exceptions\UnexpectedFilterOperationException
     */
    public static function parseFilterItemToRule(array $item, array $transformerFilterFields): FilterRule
    {
        $field = $item[self::FIELD_FIELD] ?? null;
        $value = $item[self::FIELD_VALUE] ?? null;
        $operator = $item[self::FIELD_OPERATION] ?? null;

        if (! \is_string($field)) {
            throw new InvalidFilterFormatException();
        }

        if (! \array_key_exists(self::FIELD_VALUE, $item)) {
            throw new InvalidFilterFormatException();
        }

        $field = \is_string($field) ? \trim($field) : '';
        if ($field === '') {
            throw new InvalidFilterFormatException();
        }

        if (! \in_array($field, $transformerFilterFields, true)) {
            throw new InputsNotSupportedException($field);
        }

        $operatorInstance = FilterOperatorEnum::tryFrom($operator);
        if (! $operatorInstance) {
            throw new UnexpectedFilterOperationException($operator, $field);
        }

        return new FilterRule($field, $value, $operatorInstance);
    }
}
