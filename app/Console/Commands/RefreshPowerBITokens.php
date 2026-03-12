<?php

namespace App\Console\Commands;

use App\Services\PowerBIService;
use Illuminate\Console\Command;

class RefreshPowerBITokens extends Command
{
    protected $signature = 'powerbi:refresh-tokens {--force : Force refresh even if tokens are valid}';
    protected $description = 'Refresh Power BI embed tokens if they are expired or force refresh';

    protected $powerBI;

    public function __construct(PowerBIService $powerBI)
    {
        parent::__construct();
        $this->powerBI = $powerBI;
    }

    public function handle()
    {
        $this->info('Power BI Token Refresh - Starting...');
        $this->line('Timestamp: ' . now()->toDateTimeString());
        $this->line('');


        $status = $this->powerBI->getCacheStatus();
        $this->displayStatus($status);

        if ($this->option('force')) {
            $this->line('Force refresh option detected - clearing cache first...');
            $this->powerBI->clearCache();
            $this->line('Cache cleared.');
            $this->line('');
        }


        $this->line('Checking and refreshing tokens...');
        $results = $this->powerBI->refreshTokensIfNeeded();


        $this->displayResults($results);


        $this->line('');
        $this->line('Final status after refresh:');
        $finalStatus = $this->powerBI->getCacheStatus();
        $this->displayStatus($finalStatus);

        $this->info('Power BI Token Refresh - Completed!');
        return 0;
    }

    protected function displayStatus($status)
    {
        $this->table(
            ['Report', 'Cached', 'Status', 'Expires At', 'Minutes Remaining'],
            [
                [
                    'Main Dashboard',
                    $status['main_dashboard']['cached'] ? 'Yes' : 'No',
                    $status['main_dashboard']['status'] ?? 'N/A',
                    $status['main_dashboard']['expires_at'] ?? 'N/A',
                    $status['main_dashboard']['minutes_remaining'] ?? 'N/A'
                ],
                [
                    'TAT Report',
                    $status['tat_report']['cached'] ? 'Yes' : 'No',
                    $status['tat_report']['status'] ?? 'N/A',
                    $status['tat_report']['expires_at'] ?? 'N/A',
                    $status['tat_report']['minutes_remaining'] ?? 'N/A'
                ]
            ]
        );
    }

    protected function displayResults($results)
    {
        foreach ($results as $report => $result) {
            $status = $result === 'refreshed' ? 'Refreshed' : 'Valid';
            $reportName = $report === 'main_dashboard' ? 'Main Dashboard' : 'TAT Report';
            $this->line("{$reportName}: {$status}");
        }
    }
}
