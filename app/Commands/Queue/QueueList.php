<?php

namespace App\Commands\Queue;

use App\Queue\Queue;
use App\Transport\TransportProvider;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class QueueList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:list {--name-only} {--vhost=/} {--transport=default}';

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

        $queues = $transport->queueList(
            (string) $this->option('vhost')
        );

        if ($this->option('name-only')) {
            foreach ($queues as $queue) {
                $this->line($queue->name);
            }

            return self::SUCCESS;
        }

        $this->table([
            'vhost',
            'name',
            'messages',
        ], array_map(
            static fn(Queue $queue) => [
                $queue->vhost,
                $queue->name,
                $queue->messages,
            ],
            $queues
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
