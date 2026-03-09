<?php

declare(strict_types=1);

namespace App\Ship\Values\Enums;

enum CastTypesEnum: string
{
    case STRING = 'string';

    case BOOL = 'bool';

    case INT = 'int';

    case DATE = 'date';

    case DATETIME = 'datetime';

    case ARRAY = 'array';
}
