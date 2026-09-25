<?php

namespace App\Console\Commands;

use App\Services\NewsFetchService;
use Illuminate\Console\Command;

class FetchNewsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'news:fetch
                            {--source= : Specific source ID to fetch}
                            {--force : Force fetch regardless of frequency timing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch news from configured active RSS and JSON API sources';

    /**
     * Execute the console command.
     */
    public function handle(NewsFetchService $service): int
    {
        $this->info('Starting automated news fetch...');

        $sourceId = $this->option('source') ? (int) $this->option('source') : null;
        $force = (bool) $this->option('force');

        $stats = $service->fetchActiveSources($sourceId, $force);

        $this->line("Sources Processed: {$stats['sources_processed']}");
        $this->line("Items Found:       {$stats['items_found']}");
        $this->line("Items Imported:    {$stats['items_imported']}");
        $this->line("Duplicates Skipped:{$stats['items_skipped_duplicate']}");

        if (! empty($stats['errors'])) {
            $this->warn('Errors encountered:');
            foreach ($stats['errors'] as $error) {
                $this->error(" - {$error}");
            }
        }

        $this->info('News fetch completed successfully.');

        return Command::SUCCESS;
    }
}
