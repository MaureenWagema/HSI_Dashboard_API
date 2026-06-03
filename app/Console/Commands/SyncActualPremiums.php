<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SyncActualPremiums extends Command
{
    
    protected $signature = 'sync:actual-premiums';

    
    protected $description = 'Sync actual premiums from MySQL to SQL Server';

    
    public function handle()
    {
        $this->info('Starting sync of actual premiums...');
        
        try {
            $totalSynced = 0;
            $offset = 0;
            $batchSize = 1000;
            $batchCount = 0;

            $this->info('Clearing existing data from SQL Server...');
            \DB::connection('sqlsrv')->statement('TRUNCATE TABLE actual_premium');

            $this->info('Starting data sync from MySQL to SQL Server...');

            do {
                $this->info("Processing batch " . ($batchCount + 1) . " (offset: {$offset})");
                
                $query = "
                    SELECT 
                        e.commdept_name AS department, 
                        d.account_year, 
                        d.account_month, 
                        b.bustype_description AS business, 
                        d.balanceamount AS actual_premium,
                        d.policy_no AS policy_no,
                        d.GrossAmount AS GrossAmount,
                        d.Name AS Name,
                        d.NetAmount AS NetAmount
                    FROM classinfo c
                    RIGHT JOIN debitmastinfo d ON c.class_code = d.class_code
                    RIGHT JOIN bustypeinfo b ON b.bustype = d.bustype
                    RIGHT JOIN commdeptinfo e ON e.commdept_code = c.commdept_code
                    ORDER BY d.account_year DESC, d.account_month DESC
                    LIMIT {$batchSize} OFFSET {$offset}
                ";

                $mysqlData = \DB::connection('mysql')->select($query);

                if (empty($mysqlData)) {
                    $this->info("No more data found. Sync completed.");
                    break;
                }

                $insertData = [];
                foreach ($mysqlData as $row) {
                    $insertData[] = [
                        'department' => $row->department,
                        'account_year' => $row->account_year,
                        'account_month' => $row->account_month,
                        'business' => $row->business,
                        'actual_premium' => $row->actual_premium,
                        'policy_no' => $row->policy_no,
                        'Name' => $row->Name,
                        'GrossAmount' => $row->GrossAmount,
                        'NetAmount' => $row->NetAmount,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }

                // Insert data in chunks
                $chunks = array_chunk($insertData, 100);
                foreach ($chunks as $chunk) {
                    \DB::connection('sqlsrv')->table('actual_premium')->insert($chunk);
                    $totalSynced += count($chunk);
                }

                $offset += $batchSize;
                $batchCount++;
                
                // Clear memory
                unset($mysqlData, $insertData, $chunks);

            } while (true);

            $this->info("Sync completed successfully!");
            $this->info("Total records synced: {$totalSynced}");
            $this->info("Total batches processed: {$batchCount}");
            
        } catch (\Exception $e) {
            $this->error(" Sync failed with exception: " . $e->getMessage());
            $this->error(" Error occurred at batch: " . ($batchCount + 1));
            return 1;
        }
        
        return 0;
    }
}
