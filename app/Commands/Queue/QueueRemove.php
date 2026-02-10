<?php

namespace App\Commands\Queue;

use App\Transport\TransportProvider;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class QueueRemove extends Command
{
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:remove {queue} {--vhost=default} {--transport=default} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Queue remove';

    /**
     * Execute the console command.
     */
    public function handle(TransportProvider $transportProvider): int
    {
        if ($this->confirmToProceed('Queue remove') === false) {
            return self::FAILURE;
        }

        $transport = $transportProvider->getTransport($this->option('transport'));

        $name = $this->argument('queue');
        $result = $transport->queueRemove(
            (string) $this->option('vhost'),
            $name,
        );

        if ($result === false) {
            $this->error(sprintf('Unable to remove queue %s.', $name));

            return self::FAILURE;
        }

        $this->info(sprintf('Queue %s removed', $name));

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
