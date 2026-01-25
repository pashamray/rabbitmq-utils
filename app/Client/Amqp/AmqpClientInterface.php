<?php

namespace App\Client\Amqp;

use App\Client\ClientInterface;

interface AmqpClientInterface extends ClientInterface
{
    public function getAmqpUri(bool $local = false): string;
}
