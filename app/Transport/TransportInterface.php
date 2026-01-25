<?php

namespace App\Transport;

use App\Client\Amqp\AmqpClientInterface;
use App\Client\Manager\ManagerClientInterface;
use App\Queue\QueueInterface;
use App\Shovel\ShovelInterface;

interface TransportInterface extends AmqpClientInterface, ManagerClientInterface, QueueInterface, ShovelInterface
{
    public function getName(): string;
}
