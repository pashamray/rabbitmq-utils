<?php

namespace App\Transport;

class TransportProvider
{
    private array $transports;

    public function __construct(array $transports)
    {
        foreach ($transports as $transport) {
            $this->addTransport($transport);
        }
    }

    private function addTransport(TransportInterface $transport): void
    {
        $this->transports[$transport->getName()] = $transport;
    }

    public function getTransport(string $name): TransportInterface
    {
        return $this->transports[$name];
    }

    public function getTransports(): array
    {
        return $this->transports;
    }
}
