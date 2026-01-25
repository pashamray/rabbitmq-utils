<?php

namespace App\Providers;

use App\Client\ClientFactory;
use App\Transport\Transport;
use App\Transport\TransportProvider;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class TransportServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $transports = config('transports');

        foreach ($transports as $name => $transportConfig) {
            $transportId = sprintf('transport.%s', $name);
            $transportClientTag = sprintf('transport.%s.client', $name);

            foreach ($transportConfig['clients'] as $type => $clientConfig) {
                $clientId = sprintf('%s.%s.client', $transportId, $type);

                $this->app->singleton($clientId, static fn () => ClientFactory::createFromArray($type, $clientConfig));
                $this->app->tag($clientId, $transportClientTag);
            }

            $this->app->singleton(
                $transportId,
                static fn(Application $app) => new Transport(
                    $name,
                    ...$app->tagged($transportClientTag)
                )
            );
            $this->app->tag($transportId, 'transport');
        }

        $this->app->singleton(
            TransportProvider::class,
            static fn (Application $app) => new TransportProvider([...$app->tagged('transport')])
        );
    }
}
