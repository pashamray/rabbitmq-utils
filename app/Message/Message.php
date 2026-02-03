<?php

namespace App\Message;

readonly class Message
{
    public function __construct(
        public int $count,
        public array $headers,
        public string $payload,
    ) {}

    public static function createFromArray(array $message): self
    {
        return new self(
            $message['message_count'],
            $message['properties']['headers'],
            $message['payload'],
        );
    }
}
