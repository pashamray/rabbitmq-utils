<?php

namespace App\Commands\Shovel;

use App\Shovel\Shovel;
use App\Transport\TransportProvider;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class ShovelCreate extends Command
{
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shovel:create {resource} {--transport-src=source} {--transport-dst=destination} {--vhost=/} {--transport=default} {--prefix=shovel} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Shovel create';

    /**
     * Execute the console command.
     */
    public function handle(TransportProvider $transportProvider): int
    {
        if ($this->confirmToProceed('Shovel create') === false) {
            return self::FAILURE;
        }

        $vhost = $this->option('vhost');
        $resource = $this->argument('resource');
        $shovelName = $this->option('prefix') ? sprintf('%s-%s', $this->option('prefix'), $resource) : $resource;

        $transport = $transportProvider->getTransport($this->option('transport'));
        $transportSrc = $transportProvider->getTransport($this->option('transport-src'));
        $transportDst = $transportProvider->getTransport($this->option('transport-dst'));

        $shovel = Shovel::createFromArray([
            'vhost' => $vhost,
            'name' => $shovelName,
            'src' => [
                'uri' => $transportSrc->getAmqpUri($transport === $transportSrc),
                'name' => $resource,
            ],
            'dst' => [
                'uri' => $transportDst->getAmqpUri($transport === $transportDst),
                'name' => $resource,
            ],
        ]);

        $result = $transport->shovelCreate($shovel);

        if ($result === false) {
            $this->error(sprintf('Unable to create shovel %s.', $shovelName));

            return self::FAILURE;
        }

        $this->info(sprintf('Shovel %s created', $shovelName));

        return self::SUCCESS;
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
