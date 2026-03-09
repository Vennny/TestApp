<?php

declare(strict_types=1);

namespace App\Ship\Exceptions;

final class InvalidJsonException extends \Exception
{
    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return 400;
    }

    /**
     * @return string
     */
    public function getResponseType(): string
    {
        return "InvalidSyntax";
    }
}
