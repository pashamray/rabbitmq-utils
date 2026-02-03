<?php

namespace App\Shovel;

interface ShovelInterface
{
    public function shovelList(string $vhost): array;

    public function shovelRemove(string $vhost, string $name): bool;

    public function shovelCreate(Shovel $shovel): bool;
}
