<?php

namespace App\Shovel;

readonly class Shovel
{
    public function __construct(
        public string $name,
        public string $vhost,
        public ?ShovelResource $source = null,
        public ?ShovelResource $destination = null,
        public bool $addForwardHeaders = false,
        public ShovelSourceAutoDelete $autoDelete = ShovelSourceAutoDelete::NEVER,
        public ShovelAskMode $askMode = ShovelAskMode::ON_CONFIRM,
        public int $prefetchCount = 1,
    ) {}

    public static function createFromArray(array $shovel): self
    {
        return new self(
            $shovel['name'],
            $shovel['vhost'],
            isset($shovel['src']) ? new ShovelResource(
                $shovel['src']['uri'],
                $shovel['src']['name'],
            ) : null,
            isset($shovel['dst']) ? new ShovelResource(
                $shovel['dst']['uri'],
                $shovel['dst']['name'],
            ) : null,
        );
    }
}
