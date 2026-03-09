<?php

declare(strict_types=1);

namespace App\Ship\Services\DataHandler\Exceptions;

final class InvalidFilterFormatException extends \RuntimeException
{
    /**
     * @param string|null $message
     * @param int $code
     */
    public function __construct(
        ?string $message = null,
        int $code = 422
    ) {
        parent::__construct($message ?? 'Input filter item format is invalid.', $code);
    }
}
