<?php

namespace App\Client\Amqp;

readonly class AmqpClient implements AmqpClientInterface
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

    public function getAmqpUri(bool $local = false): string
    {
        return $this->buildUri($local);
    }

    private function buildUri(bool $local): string
    {
        $schema = $this->tls ? 'amqps' : 'amqp';

        if ($local) {
            return sprintf('%s:///%s', $schema, urlencode($this->vhost));
        }

        $credentials = implode(':', [
            $this->login,
            $this->password,
        ]);

        $endpoint = implode(':', [
            $this->host,
            $this->port,
        ]);

        $uri = implode('@', [$credentials, $endpoint]);

        $verify = $this->tlsVerify ? 'verify_peer' : 'verify_none';

        return sprintf(
            '%s://%s/%s?verify=%s',
            $schema,
            $uri,
            urlencode($this->vhost),
            $verify
        );
    }
}
