<?php

namespace App\Shovel;

enum ShovelResourceType: string
{
    case QUEUE = 'queue';
    case EXCHANGE = 'exchange';
}
