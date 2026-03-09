<?php

declare(strict_types=1);

namespace App\Ship\Services\DataHandler\Exceptions;

final class UnexpectedFilterOperationException extends \RuntimeException
{
    /**
     * @param string $operation
     * @param string $field
     * @param string|null $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(
        private readonly string $operation,
        private readonly string $field,
        ?string $message = null,
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message ?? 'Filter operator is invalid.', $code, $previous);
    }

    /**
     * @return string
     */
    public function getOperation(): string
    {
        return $this->operation;
    }

    /**
     * @return string
     */
    public function getField(): string
    {
        return $this->field;
    }
}
