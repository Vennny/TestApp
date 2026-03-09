<?php

declare(strict_types=1);

namespace App\Containers\Imports\Enums;

enum ImportStatusesEnum: string
{
    case PENDING = 'pending';

    case PROCESSING = 'processing';

    case DONE = 'done';

    case FAILED = 'failed';
}
