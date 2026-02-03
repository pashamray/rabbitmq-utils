<?php

namespace App\Queue;

readonly class Queue
{
    public function __construct(
        public string $name,
        public string $vhost,
        public int $messages = 0,
        public int $consumers = 0,
        public string $type = 'classic',
        public bool $autoDelete = false,
        public bool $durable = true,
        public bool $exclusive = false,
        public array $arguments = [],
    ) {}

    public static function createFromArray(array $queue): self
    {
        return new self(
            $queue['name'],
            $queue['vhost'],
            $queue['messages'] ?? 0,
            $queue['consumers'] ?? 0,
            $queue['type'] ?? 'undefined',
            $queue['auto_delete'] ?? false,
            $queue['durable'] ?? true,
            $queue['exclusive'] ?? false,
            $queue['arguments'] ?? [],
        );
    }
}
