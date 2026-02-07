<?php

namespace App\Commands\Shovel;

use App\Transport\TransportProvider;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class ShovelRemove extends Command
{
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shovel:remove {shovel} {--vhost=default} {--transport=default} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Shovel remove';

    /**
     * Execute the console command.
     */
    public function handle(TransportProvider $transportProvider): int
    {
        if ($this->confirmToProceed('Shovel remove') === false) {
            return self::FAILURE;
        }

        $transport = $transportProvider->getTransport($this->option('transport'));

        $name = $this->argument('shovel');
        $result = $transport->shovelRemove(
            (string) $this->option('vhost'),
            $name,
        );

        if ($result === false) {
            $this->error(sprintf('Unable to remove shovel %s.', $name));

            return self::FAILURE;
        }

        $this->info(sprintf('Shovel %s removed', $name));

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
