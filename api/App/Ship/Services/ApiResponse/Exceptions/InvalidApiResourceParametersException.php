<?php

declare(strict_types=1);

namespace App\Ship\Services\ApiResponse\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

final class InvalidApiResourceParametersException extends HttpException
{
}
