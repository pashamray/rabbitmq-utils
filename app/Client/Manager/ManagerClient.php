<?php

namespace App\Client\Manager;

use App\Message\Message;
use App\Queue\Queue;
use App\Queue\QueueInterface;
use App\Shovel\Shovel;
use App\Shovel\ShovelInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

readonly class ManagerClient implements ManagerClientInterface, QueueInterface, ShovelInterface
{
    private PendingRequest $client;

    static public function create(ManagerClientConfig $config): self
    {
        return new static($config);
    }

    public function __construct(
        public ManagerClientConfig $config,
    ) {
        $this->client = $this->buildClient();
    }

    public function queueList(string $vhost): array
    {
        // GET /api/queues/{vhost}

        $result = $this
            ->client
            ->get(sprintf('/api/queues/%s', $this->resolveVhost($vhost)))
            ->json();

        $result = $this->handleResponse($result);

        return array_map(static fn (array $queue) => Queue::createFromArray($queue), $result);
    }

    public function queueRemove(string $vhost, string $name): bool
    {
        // DELETE /api/queues/{vhost}/{name}

        $result = $this
            ->client
            ->delete(
                sprintf('/api/queues/%s/%s', $this->resolveVhost($vhost), $name),
            )
            ->json();

        $this->handleResponse($result);

        return true;
    }


    public function queueCreate(Queue $queue): bool
    {
        // PUT /api/queues/{vhost}/{name}

        $result = $this
            ->client
            ->put(
                sprintf('/api/queues/%s/%s', $this->resolveVhost($queue->vhost), $queue->name),
                [
                    'auto_delete' => $queue->autoDelete,
                    'durable' => $queue->durable,
                    'arguments' => array_merge([
                        'x-queue-type' => $queue->type,
                    ], $queue->arguments),
                ],
            )
            ->json();

        $this->handleResponse($result);

        return true;
    }

    public function queueMessages(string $vhost, string $queue, int $count = 10): array
    {
        // POST /api/queues/{vhost}/{queue}/get

        $result = $this
            ->client
            ->post(
                sprintf('/api/queues/%s/%s/get', $this->resolveVhost($vhost), $queue),
                [
                    'count' => $count,
                    'ackmode' => 'ack_requeue_true',
                    'encoding' => 'auto',
                ],
            )
            ->json();

        $result = $this->handleResponse($result);

        return array_map(static fn (array $message) => Message::createFromArray($message), $result);
    }

    public function shovelList(string $vhost): array
    {
        // GET /api/shovels/{vhost}

        $result = $this
            ->client
            ->get(sprintf('/api/shovels/%s', $this->resolveVhost($vhost)))
            ->json();

        $result = $this->handleResponse($result);

        return array_map(static fn (array $shovel) => Shovel::createFromArray($shovel), $result);
    }

    public function shovelCreate(Shovel $shovel): bool
    {
        // PUT /api/parameters/shovel/{vhost}/{name}

        $result = $this
            ->client
            ->put(
                sprintf('/api/parameters/shovel/%s/%s', $this->resolveVhost($shovel->vhost), $shovel->name),
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
                ],
            )
            ->json();

        $this->handleResponse($result);

        return true;
    }

    public function shovelRemove(string $vhost, string $name): bool
    {
        // DELETE /api/parameters/shovel/{vhost}/{name}

        $result = $this
            ->client
            ->delete(sprintf('/api/parameters/shovel/%s/%s', $this->resolveVhost($vhost), $name))
            ->json();

        $this->handleResponse($result);

        return true;
    }

    private function handleResponse(?array $result): ?array
    {
        if (isset($result['error'])) {
            throw new \RuntimeException(
                sprintf('Client error: %s, reason: %s', $result['error'], $result['reason']),
            );
        }

        return $result;
    }

    private function buildClient(): PendingRequest
    {
        return Http::baseUrl($this->buildUrl())->withOptions(['verify' => $this->config->tlsVerify]);
    }

    private function buildUrl(): string
    {
        $credentials = implode(':', [
            $this->config->login,
            $this->config->password,
        ]);

        $endpoint = implode(':', [
            $this->config->host,
            $this->config->port,
        ]);

        $schema = $this->config->tls ? 'https' : 'http';

        return sprintf('%s://%s', $schema, implode('@', [$credentials, $endpoint]));
    }

    /**
     * @param string $vhost
     * @return string
     */
    public function resolveVhost(string $vhost): string
    {
        return urlencode($vhost === 'default' ? $this->config->vhost : $vhost);
    }
}
