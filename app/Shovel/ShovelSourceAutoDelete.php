<?php

namespace App\Shovel;

enum ShovelSourceAutoDelete: string
{
    case NEVER = 'never';
    case QUEUE_LENGTH = 'queue-length';
}
