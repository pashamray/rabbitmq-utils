<?php

namespace App\Commands\Queue;

use App\Message\Message;
use App\Transport\TransportProvider;
use Illuminate\Console\Scheduling\Schedule;
use JsonException;
use LaravelZero\Framework\Commands\Command;

class QueueMessageList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:message:list {queue} {--count=10} {--payload-only} {--vhost=/} {--transport=default} {--format=table}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Queues message list';

    /**
     * Execute the console command.
     *
     * @throws JsonException
     */
    public function handle(TransportProvider $transportProvider): int
    {
        $transport = $transportProvider->getTransport($this->option('transport'));

        $messages = $transport->queueMessages(
            $this->option('vhost'),
            $this->argument('queue'),
            $this->option('count'),
        );

        if ($messages === []) {
            return self::SUCCESS;
        }

        $payloadOnly = $this->option('payload-only');

        $messages = array_map(
            static fn (Message $message) => $payloadOnly ? [
                'payload' => $message->payload,
            ] : [
                'count' => $message->count,
                'headers' => json_encode($message->headers, JSON_THROW_ON_ERROR),
                'payload' => $message->payload,
            ],
            $messages
        );

        if ($this->option('format') === 'raw') {
            foreach ($messages as $message) {
                $this->line($message);
            }

            return self::SUCCESS;
        }

        $this->table(
            $payloadOnly ? [
                'payload',
            ] : [
                'count',
                'headers',
                'payload',
            ],
            $messages
        );

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
