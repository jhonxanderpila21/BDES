<?php

namespace App\Console\Commands;

use App\Models\MaintenanceSchedule;
use Illuminate\Console\Command;

class UpdateMaintenanceStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bdes:update-preventive-maintenance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Daily job to recalculate and update the status of preventive maintenance schedules.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting update of preventive maintenance schedules...');
        
        $schedules = MaintenanceSchedule::all();
        $updatedCount = 0;

        foreach ($schedules as $schedule) {
            if ($schedule->refreshStatus()) {
                $updatedCount++;
            }
        }

        $this->info("Completed. Updated {$updatedCount} schedules.");
    }
}
