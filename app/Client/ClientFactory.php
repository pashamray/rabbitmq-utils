<?php

namespace App\Client;

use App\Client\Amqp\AmqpClient;
use App\Client\Manager\ManagerClient;
use App\Client\Manager\ManagerClientConfig;
use RuntimeException;

class ClientFactory
{
    public static function createFromArray(string $type, array $config): ClientInterface
    {
        return match ($type) {
            'amqp' => AmqpClient::createFromArray($config),
            'manager' => ManagerClient::create(
                ManagerClientConfig::createFromArray($config)
            ),
            default => throw new RuntimeException(sprintf('Unknown client type: %s', $type))
        };
    }
}
