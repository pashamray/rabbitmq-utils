<?php

namespace App\Commands\Queue;

use App\Queue\Queue;
use App\Transport\TransportProvider;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class QueueCreate extends Command
{
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:create {queue} {--durable=true} {--type=classic} {--auto-delete=false} {--vhost=default} {--transport=default}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Queue create';

    /**
     * Execute the console command.
     */
    public function handle(TransportProvider $transportProvider): int
    {
        if ($this->confirmToProceed('Queue create') === false) {
            return self::FAILURE;
        }

        $transport = $transportProvider->getTransport($this->option('transport'));

        $queue = $this->argument('queue');
        $queueDurable = filter_var($this->option('durable'), FILTER_VALIDATE_BOOL);
        $queueType = $this->option('type');
        $queueAutoDelete = filter_var($this->option('auto-delete'), FILTER_VALIDATE_BOOL);

        $result = $transport->queueCreate(
            Queue::createFromArray([
                'vhost' => (string) $this->option('vhost'),
                'name' => $queue,
                'type' => $queueType,
                'durable' => $queueDurable,
                'auto_delete' => $queueAutoDelete,
            ])
        );

        if ($result === false) {
            $this->error(sprintf('Unable to create queue %s.', $queue));

            return self::FAILURE;
        }

        $this->info(sprintf('Queue %s created', $queue));

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
