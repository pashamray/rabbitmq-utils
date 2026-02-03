<?php

namespace App\Shovel;

readonly class ShovelResource
{
    public function __construct(
        public string $uri,
        public string $name,
        public ShovelResourceType $resourceType = ShovelResourceType::QUEUE,
    ) {}
}
