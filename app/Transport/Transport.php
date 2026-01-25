<?php

namespace App\Transport;

use App\Client\Amqp\AmqpClientInterface;
use App\Client\ClientInterface;
use App\Queue\Queue;
use App\Queue\QueueInterface;
use App\Shovel\Shovel;
use App\Shovel\ShovelInterface;

readonly class Transport implements TransportInterface
{
    /** @var ClientInterface[] $clients */
    private array $clients;

    public function __construct(private string $name, ClientInterface ...$clients)
    {
        $this->clients = $clients;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAmqpUri(bool $local = false): string
    {
        foreach ($this->clients as $client) {
            if (($client instanceof AmqpClientInterface) === false) {
                continue;
            }

            return $client->getAmqpUri($local);
        }

        throw new \RuntimeException(sprintf('Not found client with interface: %s', AmqpClientInterface::class));
    }

    public function queueList(string $vhost): array
    {
        foreach ($this->clients as $client) {
            if (($client instanceof QueueInterface) === false) {
                continue;
            }

            return $client->queueList($vhost);
        }

        throw new \RuntimeException(sprintf('Not found client with interface: %s', QueueInterface::class));
    }

    public function queueCreate(Queue $queue): bool
    {
        foreach ($this->clients as $client) {
            if (($client instanceof QueueInterface) === false) {
                continue;
            }

            return $client->queueCreate($queue);
        }

        throw new \RuntimeException(sprintf('Not found client with interface: %s', QueueInterface::class));
    }

    public function queueMessages(string $vhost, string $queue, int $count = 10): array
    {
        foreach ($this->clients as $client) {
            if (($client instanceof QueueInterface) === false) {
                continue;
            }

            return $client->queueMessages($vhost, $queue, $count);
        }

        throw new \RuntimeException(sprintf('Not found client with interface: %s', QueueInterface::class));
    }

    public function shovelList(string $vhost): array
    {
        foreach ($this->clients as $client) {
            if (($client instanceof ShovelInterface) === false) {
                continue;
            }

            return $client->shovelList($vhost);
        }

        throw new \RuntimeException(sprintf('Not found client with interface: %s', ShovelInterface::class));
    }

    public function shovelCreate(Shovel $shovel): bool
    {
        foreach ($this->clients as $client) {
            if (($client instanceof ShovelInterface) === false) {
                continue;
            }

            return $client->shovelCreate($shovel);
        }

        throw new \RuntimeException(sprintf('Not found client with interface: %s', ShovelInterface::class));    }

    public function shovelRemove(string $vhost, string $name): bool
    {
        foreach ($this->clients as $client) {
            if (($client instanceof ShovelInterface) === false) {
                continue;
            }

            return $client->shovelRemove($vhost, $name);
        }

        throw new \RuntimeException(sprintf('Not found client with interface: %s', ShovelInterface::class));
    }
}
