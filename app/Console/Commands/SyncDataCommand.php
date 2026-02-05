<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AccountMappingService;
use Illuminate\Support\Facades\Log;

class SyncDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:sync 
                            {--year= : Specific year to sync}
                            {--month= : Specific month to sync}
                            {--auto : Run in automatic mode}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync data from MySQL to SQL Server';

    protected $accountMappingService;

    public function __construct(AccountMappingService $accountMappingService)
    {
        parent::__construct();
        $this->accountMappingService = $accountMappingService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $year = $this->option('year');
        $month = $this->option('month');
        $auto = $this->option('auto');

        $this->info('Starting data sync...');

        try {
            if ($auto) {
                // Auto mode: sync current month data
                $year = date('Y');
                $month = date('n');
                $this->info("Auto-syncing year: {$year}, month: {$month}");
            }

            $result = $this->accountMappingService->syncToSqlServer($year, $month);

            if ($result['success']) {
                $this->info("✅ Sync completed successfully!");
                $this->info("Records synced: " . ($result['synced_count'] ?? 'Unknown'));
                
                // Log success
                Log::info('Data sync completed successfully', [
                    'year' => $year,
                    'month' => $month,
                    'synced_count' => $result['synced_count'] ?? 0
                ]);
            } else {
                $this->error("❌ Sync failed: " . $result['message']);
                
                // Log failure
                Log::error('Data sync failed', [
                    'year' => $year,
                    'month' => $month,
                    'message' => $result['message']
                ]);
                
                return 1;
            }

        } catch (\Exception $e) {
            $this->error("❌ Exception occurred: " . $e->getMessage());
            Log::error('Data sync exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return 1;
        }

        return 0;
    }
}
