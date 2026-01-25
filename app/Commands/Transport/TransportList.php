<?php

namespace App\Commands\Transport;

use App\Transport\TransportInterface;
use App\Transport\TransportProvider;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class TransportList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transport:list {--name-only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transport list';

    /**
     * Execute the console command.
     */
    public function handle(TransportProvider $transportProvider): int
    {
        $transports = $transportProvider->getTransports();

        if ($this->option('name-only')) {
            foreach ($transports as $transport) {
                $this->line($transport->getName());
            }

            return self::SUCCESS;
        }

        $this->table([
            'name',
        ], array_map(
            static fn(TransportInterface $transport) => [
                $transport->getName(),
            ],
            $transports
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
