<?php

declare(strict_types=1);

namespace App\Ship\Values\Enums;

enum WhereBooleanEnum : string
{
    case AND = 'and';

    case OR = 'or';
}
