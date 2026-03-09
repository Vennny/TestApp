<?php

declare(strict_types=1);

namespace App\Ship\Services\DataHandler\Exceptions;

final class InputsNotSupportedException extends \RuntimeException
{
    /**
     * @param string $input
     * @param int $code
     */
    public function __construct(
        private readonly string $input,
        int $code = 422
    ) {
        parent::__construct($message ?? 'Input fields are not supported.', $code);
    }

    /**
     * @return string
     */
    public function getInput(): string
    {
        return $this->input;
    }
}
