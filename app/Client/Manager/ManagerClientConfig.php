<?php

namespace App\Client\Manager;

readonly class ManagerClientConfig
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
            $config['tls_verify'],
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
}
