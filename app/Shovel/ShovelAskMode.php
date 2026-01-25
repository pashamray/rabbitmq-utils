<?php

namespace App\Shovel;

enum ShovelAskMode: string
{
    case ON_PUBLISH = 'on-publish';
    case ON_CONFIRM = 'on-confirm';
    case NO_ASK = 'no-ask';
}
