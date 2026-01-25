<?php

namespace App\Commands\Shovel;

use App\Queue\Queue;
use App\Shovel\Shovel;
use App\Transport\TransportProvider;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class ShovelList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shovel:list {--name-only} {--vhost=} {--transport=default}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Queues list';

    /**
     * Execute the console command.
     */
    public function handle(TransportProvider $transportProvider): int
    {
        $transport = $transportProvider->getTransport($this->option('transport'));

        $shovels = $transport->shovelList((string) $this->option('vhost'));

        if ($shovels === []) {
            return self::SUCCESS;
        }

        if ($this->option('name-only')) {
            foreach ($shovels as $shovel) {
                $this->line($shovel->name);
            }

            return self::SUCCESS;
        }

        $this->table([
            'vhost',
            'name',
        ], array_map(
            static fn(Shovel $shovel) => [
                $shovel->vhost,
                $shovel->name,
            ],
            $shovels
        ));

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
