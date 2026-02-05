<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AccountMappingService;

class SyncAccountMappings extends Command
{
    protected $signature = 'sync:account-mappings 
                            {--year= : The year to sync (optional)} 
                            {--month= : The month to sync (optional)}';

    protected $description = 'Sync account mappings from MySQL to SQL Server';

    protected $accountMappingService;

    public function __construct(AccountMappingService $accountMappingService)
    {
        parent::__construct();
        $this->accountMappingService = $accountMappingService;
    }

    public function handle()
    {
        $year = $this->option('year');
        $month = $this->option('month');

        $this->info('Starting account mappings sync...');
        
        $result = $this->accountMappingService->syncToSqlServer($year, $month);

        if ($result['success']) {
            $this->info('Sync completed successfully!');
            $this->info("Synced {$result['synced_count']} records.");
            return 0;
        } else {
            $this->error('Sync failed: ' . $result['message']);
            return 1;
        }
    }
}
