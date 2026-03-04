<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Tat;

class SyncTatData extends Command
{
    
    protected $signature = 'sync:tat-data';

    
    protected $description = 'Sync TAT data from MySQL to SQL Server';

  
    public function handle()
    {
        $this->info('Starting TAT data sync from MySQL to SQL Server...');

        try {
            $tatData = DB::connection('mysql')->select("
                SELECT 
                    c.claim_no AS Claim_No,
                    d.policy_no AS Policy_No,
                    d.name AS Name,
                    f.company_dept_name AS Dept,
                    DATE(c.datereported) AS Date_Reported,
                    DATE(t.StatusEffectiveDate) AS Offer_Date,
                    t.statusdescription,
                    DATEDIFF(t.StatusEffectiveDate, c.datereported) AS Time_to_Make_Offer 
                FROM claimsinfo c
                INNER JOIN claimstatusinfo t ON t.claim_no = c.claim_no
                INNER JOIN claimsstatuscodes o ON o.StatusCode_ID = t.StatusCode_ID
                INNER JOIN claimsregistryinfo d ON d.claim_no = c.claim_no
                INNER JOIN classinfo e ON e.class_code = c.class_code
                INNER JOIN companydeptinfo f ON f.company_dept_code = e.company_dept_code
                WHERE o.statusdescription LIKE '%FORMAL OFFER MADE%'
                ORDER BY t.StatusEffectiveDate DESC
            ");

            $this->info('Found ' . count($tatData) . ' records in MySQL');

            $this->info('Syncing TAT data to SQL Server (updates only)...');
            
            $batchSize = 100;
            $batches = array_chunk($tatData, $batchSize);
            $processedCount = 0;
            $updatedCount = 0;
            $insertedCount = 0;
            $skippedCount = 0;
            
            foreach ($batches as $index => $batch) {
                $this->info("Processing batch " . ($index + 1) . " of " . count($batches));
                
                foreach ($batch as $record) {
                    try {
                        $processedCount++;
                        
                        // Try to update existing record first
                        $updated = DB::connection('sqlsrv')->update(
                            "UPDATE tat WITH (ROWLOCK) 
                             SET Policy_No = ?, 
                                 Name = ?, 
                                 Dept = ?, 
                                 Date_Reported = ?, 
                                 Offer_Date = ?, 
                                 statusdescription = ?, 
                                 Time_to_Make_Offer = ?, 
                                 updated_at = ?
                             WHERE Claim_No = ?",
                            [
                                $record->Policy_No,
                                $record->Name,
                                $record->Dept,
                                $record->Date_Reported,
                                $record->Offer_Date,
                                $record->statusdescription,
                                $record->Time_to_Make_Offer,
                                now()->toDateTimeString(),
                                $record->Claim_No
                            ]
                        );

                        if ($updated > 0) {
                            $updatedCount++;
                        } else {
                            // Insert new record if not found
                            try {
                                Tat::create([
                                    'Claim_No' => $record->Claim_No,
                                    'Policy_No' => $record->Policy_No,
                                    'Name' => $record->Name,
                                    'Dept' => $record->Dept,
                                    'Date_Reported' => $record->Date_Reported,
                                    'Offer_Date' => $record->Offer_Date,
                                    'statusdescription' => $record->statusdescription,
                                    'Time_to_Make_Offer' => $record->Time_to_Make_Offer
                                ]);
                                $insertedCount++;
                            } catch (\Exception $e) {
                                // Skip duplicates
                                if (strpos($e->getMessage(), 'duplicate key') !== false) {
                                    $skippedCount++;
                                    continue;
                                }
                                throw $e;
                            }
                        }
                    } catch (\Exception $e) {
                        $this->error("Error processing record {$record->Claim_No}: " . $e->getMessage());
                        continue;
                    }
                }
            }

            $this->info('TAT data sync completed successfully!');
            $this->info('Total records found in MySQL: ' . count($tatData));
            $this->info('Records processed: ' . $processedCount);
            $this->info('Records updated: ' . $updatedCount);
            $this->info('Records inserted: ' . $insertedCount);
            $this->info('Duplicates skipped: ' . $skippedCount);

            $totalRecords = Tat::count();
            $avgTime = Tat::avg('Time_to_Make_Offer');
            $deptCount = Tat::distinct('Dept')->count('Dept');

            $this->info('SQL Server Statistics:');
            $this->info('- Total Records: ' . $totalRecords);
            $this->info('- Average Time to Make Offer: ' . round($avgTime, 2) . ' days');
            $this->info('- Number of Departments: ' . $deptCount);

        } catch (\Exception $e) {
            $this->error('Error during sync: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
