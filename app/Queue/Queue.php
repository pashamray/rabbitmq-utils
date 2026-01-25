<?php

namespace App\Queue;

readonly class Queue
{
    public function __construct(
        public string $name,
        public string $vhost,
        public int $messages = 0,
    ) {
    }

    public static function createFromArray(array $queue): self
    {
        return new self(
            $queue['name'],
            $queue['vhost'],
            $queue['messages'] ?? 0,
        );
    }
}
