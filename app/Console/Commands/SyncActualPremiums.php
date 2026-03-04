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
            $updatedCount = 0;
            $insertedCount = 0;
            $offset = 0;
            $batchSize = 1000;
            $batchCount = 0;

            $this->info('Syncing actual premiums to SQL Server (updates only)...');

            do {
                $this->info("Processing batch " . ($batchCount + 1) . " (offset: {$offset})");
                
                $query = "
                    SELECT 
                        d.Debit_ID,
                        e.commdept_name AS department, 
                        c.description AS sub_department,
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
                        'Debit_ID' => $row->Debit_ID,
                        'department' => $row->department,
                        'sub_department' => $row->sub_department,
                        'account_year' => $row->account_year,
                        'account_month' => $row->account_month,
                        'business' => $row->business,
                        'actual_premium' => $row->actual_premium,
                        'policy_no' => $row->policy_no,
                        'Name' => $row->Name,
                        'GrossAmount' => $row->GrossAmount ?? 0,
                        'NetAmount' => $row->NetAmount ?? 0,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }

                // Process each record with update-first logic
                foreach ($insertData as $record) {
                    try {
                        // Try to update existing record first
                        $updated = \DB::connection('sqlsrv')->update(
                            "UPDATE actual_premium WITH (ROWLOCK) 
                             SET department = ?, 
                                 sub_department = ?, 
                                 business = ?, 
                                 actual_premium = ?, 
                                 policy_no = ?, 
                                 Name = ?, 
                                 GrossAmount = ?, 
                                 NetAmount = ?, 
                                 updated_at = ?
                             WHERE Debit_ID = ?",
                            [
                                $record['department'],
                                $record['sub_department'],
                                $record['business'],
                                $record['actual_premium'],
                                $record['policy_no'],
                                $record['Name'],
                                $record['GrossAmount'],
                                $record['NetAmount'],
                                now()->toDateTimeString(),
                                $record['Debit_ID']
                            ]
                        );

                        if ($updated > 0) {
                            $updatedCount++;
                            $totalSynced++;
                        } else {
                            // Insert new record if not found
                            try {
                                \DB::connection('sqlsrv')->table('actual_premium')->insert($record);
                                $insertedCount++;
                                $totalSynced++;
                            } catch (\Exception $e) {
                                // Skip duplicates
                                if (strpos($e->getMessage(), 'duplicate key') !== false) {
                                    continue;
                                }
                                throw $e;
                            }
                        }
                    } catch (\Exception $e) {
                        $this->error("Error processing record {$record['Debit_ID']}: " . $e->getMessage());
                        continue;
                    }
                }

                $offset += $batchSize;
                $batchCount++;
                
                // Clear memory
                unset($mysqlData, $insertData, $chunks);

            } while (true);

            $this->info("Sync completed successfully!");
            $this->info("Total records synced: {$totalSynced}");
            $this->info("Records updated: {$updatedCount}");
            $this->info("Records inserted: {$insertedCount}");
            $this->info("Total batches processed: {$batchCount}");
            
        } catch (\Exception $e) {
            $this->error(" Sync failed with exception: " . $e->getMessage());
            $this->error(" Error occurred at batch: " . ($batchCount + 1));
            return 1;
        }
        
        return 0;
    }
}
