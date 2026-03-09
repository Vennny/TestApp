<?php

declare(strict_types=1);

namespace App\Ship\Values\Enums;

enum MimeTypesEnum: string
{
    case TEXT_XML = 'text/xml';

    case APPLICATION_XML = 'application/xml';
}
