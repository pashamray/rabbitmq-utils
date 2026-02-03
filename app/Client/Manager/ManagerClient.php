<?php

namespace App\Client\Manager;

use App\Message\Message;
use App\Queue\Queue;
use App\Queue\QueueInterface;
use App\Shovel\Shovel;
use App\Shovel\ShovelInterface;
use Illuminate\Support\Facades\Http;

readonly class ManagerClient implements ManagerClientInterface, QueueInterface, ShovelInterface
{
    public static function createFromArray(array $config): self
    {
        return new static(
            $config['host'],
            $config['vhost'],
            $config['port'],
            $config['login'],
            $config['password'],
            $config['tls'],
            $config['tls_verify']
        );
    }

    public function __construct(
        public string $host,
        public string $vhost,
        public int $port,
        public string $login,
        public string $password,
        public bool $tls = false,
        public bool $tlsVerify = false,
    ) {}

    public function queueList(string $vhost = '/'): array
    {
        // GET /api/queues/{vhost}

        $queues = Http::baseUrl($this->buildUrl())
            ->withOptions(['verify' => $this->tlsVerify])
            ->get(sprintf('/api/queues/%s', urlencode($vhost)))
            ->json();

        if (isset($queues['error'])) {
            throw new \RuntimeException(sprintf('Client error: %s', $queues['error']));
        }

        return array_map(static fn (array $queue) => Queue::createFromArray($queue), $queues);
    }

    public function queueCreate(Queue $queue): bool
    {
        // PUT /api/queues/{vhost}/{name}

        $result = Http::baseUrl($this->buildUrl())
            ->withOptions(['verify' => $this->tlsVerify])
            ->put(
                sprintf('/api/queues/%s/%s', urlencode($queue->vhost), $queue->name),
                [
                    'auto_delete' => $queue->autoDelete,
                    'durable' => $queue->durable,
                    'arguments' => array_merge([
                        'x-queue-type' => $queue->type,
                    ], $queue->arguments),
                ]
            )
            ->json();

        if (isset($result['error'])) {
            throw new \RuntimeException(
                sprintf('Client error: %s, reason: %s', $result['error'], $result['reason'])
            );
        }

        return true;
    }

    public function queueMessages(string $vhost, string $queue, int $count = 10): array
    {
        // POST /api/queues/{vhost}/{queue}/get

        $messages = Http::baseUrl($this->buildUrl())
            ->withOptions(['verify' => $this->tlsVerify])
            ->post(
                sprintf('/api/queues/%s/%s/get', urlencode($vhost), $queue),
                [
                    'count' => $count,
                    'ackmode' => 'ack_requeue_true',
                    'encoding' => 'auto',
                ]
            )
            ->json();

        if (isset($messages['error'])) {
            throw new \RuntimeException(sprintf('Client error: %s', $messages['error']));
        }

        return array_map(static fn (array $message) => Message::createFromArray($message), $messages);
    }

    public function shovelList(string $vhost): array
    {
        // GET /api/shovels/{vhost}

        $shovels = Http::baseUrl($this->buildUrl())
            ->withOptions(['verify' => $this->tlsVerify])
            ->get(sprintf('/api/shovels/%s', urlencode($vhost)))
            ->json();

        if (isset($shovels['error'])) {
            throw new \RuntimeException(sprintf('Client error: %s', $shovels['error']));
        }

        return array_map(static fn (array $shovel) => Shovel::createFromArray($shovel), $shovels);
    }

    public function shovelCreate(Shovel $shovel): bool
    {
        // PUT /api/parameters/shovel/{vhost}/{name}

        $result = Http::baseUrl($this->buildUrl())
            ->withOptions(['verify' => $this->tlsVerify])
            ->put(
                sprintf('/api/parameters/shovel/%s/%s', urlencode($shovel->vhost), $shovel->name),
                [
                    'component' => 'shovel',
                    'name' => $shovel->name,
                    'value' => [
                        'src-queue' => $shovel->source->name,
                        'src-uri' => $shovel->source->uri,
                        //                        'dest-exchange' => null,
                        'dest-queue' => $shovel->destination->name,
                        'dest-uri' => $shovel->destination->uri,
                        'ack-mode' => $shovel->askMode,
                        'delete-after' => $shovel->autoDelete,
                        'add-forward-headers' => $shovel->addForwardHeaders,
                        'prefetch-count' => $shovel->prefetchCount,
                        //                        'reconnect-delay' => 30,
                    ],
                    'vhost' => $shovel->vhost,
                ]
            )
            ->json();

        if (isset($result['error'])) {
            throw new \RuntimeException(sprintf('Client error: %s', $result['error']));
        }

        return true;
    }

    public function shovelRemove(string $vhost, string $name): bool
    {
        // DELETE /api/parameters/shovel/{vhost}/{name}

        $result = Http::baseUrl($this->buildUrl())
            ->withOptions(['verify' => $this->tlsVerify])
            ->delete(sprintf('/api/parameters/shovel/%s/%s', urlencode($vhost), $name))
            ->json();

        if (isset($result['error'])) {
            throw new \RuntimeException(sprintf('Client error: %s', $result['error']));
        }

        return true;
    }

    private function buildUrl(): string
    {
        $credentials = implode(':', [
            $this->login,
            $this->password,
        ]);

        $endpoint = implode(':', [
            $this->host,
            $this->port,
        ]);

        $schema = $this->tls ? 'https' : 'http';

        return sprintf('%s://%s', $schema, implode('@', [$credentials, $endpoint]));
    }
}
