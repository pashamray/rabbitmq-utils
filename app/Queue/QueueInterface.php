<?php

namespace App\Queue;

interface QueueInterface
{
    public function queueList(string $vhost): array;

    public function queueCreate(Queue $queue): bool;

    public function queueMessages(string $vhost, string $queue, int $count = 10): array;
}
