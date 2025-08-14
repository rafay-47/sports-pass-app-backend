<?php

namespace App\Console\Commands;

use App\Models\Sport;
use Illuminate\Console\Command;

class SyncSportServiceCounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sports:sync-service-counts {--sport-id= : Sync for a specific sport ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync the number_of_services count for all sports based on actual service records';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sportId = $this->option('sport-id');

        if ($sportId) {
            // Sync for specific sport
            $sport = Sport::find($sportId);
            if (!$sport) {
                $this->error("Sport with ID {$sportId} not found.");
                return 1;
            }
            
            $this->syncSportServiceCount($sport);
            $this->info("Service count synced for sport: {$sport->name}");
        } else {
            // Sync for all sports
            $sports = Sport::all();
            $progressBar = $this->output->createProgressBar($sports->count());
            $progressBar->start();

            foreach ($sports as $sport) {
                $this->syncSportServiceCount($sport);
                $progressBar->advance();
            }

            $progressBar->finish();
            $this->newLine();
            $this->info("Service counts synced for all {$sports->count()} sports.");
        }

        return 0;
    }

    /**
     * Sync service count for a specific sport.
     */
    private function syncSportServiceCount(Sport $sport): void
    {
        $actualCount = $sport->services()->count();
        $currentCount = $sport->number_of_services;

        if ($actualCount !== $currentCount) {
            $sport->update(['number_of_services' => $actualCount]);
            $this->line("  - {$sport->name}: {$currentCount} → {$actualCount}");
        }
    }
}
