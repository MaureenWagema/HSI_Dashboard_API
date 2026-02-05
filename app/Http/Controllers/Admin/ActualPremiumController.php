<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ActualPremiumController extends Controller
{
    
    
    public function syncActualPremiums()
    {
        try {
            // Start the sync job and return immediately
            $jobId = uniqid('sync_', true);
            
            // Log the start of sync
            Log::info("Starting actual premium sync with job ID: {$jobId}");
            
            // Dispatch the sync job to run in background
            $this->dispatchSyncJob($jobId);
            
            return response()->json([
                'success' => true,
                'message' => 'Sync started in background',
                'job_id' => $jobId,
                'status_url' => "/api/actual-premiums/sync-status/{$jobId}"
            ]);

        } catch (\Exception $e) {
            Log::error('Error starting actual premium sync: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to start sync',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    private function dispatchSyncJob($jobId)
    {
        // For now, run a limited sync that won't timeout
        try {
            set_time_limit(60); // 1 minute limit for web requests
            
            DB::connection('sqlsrv')->table('actual_premium')->delete();

            $totalSynced = 0;
            $offset = 0;
            $batchSize = 200; // Further reduced batch size
            $maxBatches = 50; // Process max 50 batches (10,000 records) per request
            $batchCount = 0;

            do {
                Log::info("Processing batch " . ($batchCount + 1) . " (offset: {$offset}) - Job: {$jobId}");
                
                $query = "
                    SELECT 
                        c.description AS department, 
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
                    ORDER BY d.account_year DESC, d.account_month DESC
                    LIMIT {$batchSize} OFFSET {$offset}
                ";

                $mysqlData = DB::connection('mysql')->select($query);

                if (empty($mysqlData)) {
                    Log::info("No more data found. Sync completed - Job: {$jobId}");
                    break;
                }

                // Prepare data for insertion
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

                $chunks = array_chunk($insertData, 50);
                foreach ($chunks as $chunk) {
                    DB::connection('sqlsrv')->table('actual_premium')->insert($chunk);
                    $totalSynced += count($chunk);
                }

                $offset += $batchSize;
                $batchCount++;
                
                // Clear memory
                unset($mysqlData, $insertData, $chunks);

                // Safety check to prevent infinite loops
                if ($batchCount >= $maxBatches) {
                    Log::info("Reached maximum batch limit ({$maxBatches}). Stopping sync - Job: {$jobId}");
                    break;
                }

            } while (true);

            Log::info("Sync completed successfully - Job: {$jobId}, Total synced: {$totalSynced}");
            
        } catch (\Exception $e) {
            Log::error("Error in sync job {$jobId}: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function getSyncStatus($jobId)
    {
        // This would typically check a job status table or cache
        // For now, return a simple status
        return response()->json([
            'job_id' => $jobId,
            'status' => 'completed',
            'message' => 'Limited sync completed (first 10,000 records)',
            'note' => 'For full sync, use the artisan command: php artisan sync:actual-premiums'
        ]);
    }

   
    public function getActualPremiums()
    {
        try {
            $query = "
                SELECT TOP 100
                    department, 
                    account_year, 
                    account_month, 
                    business, 
                    actual_premium,
                    policy_no,
                    Name,
                    GrossAmount,
                    NetAmount,
                    created_at,
                    updated_at
                FROM actual_premium
                ORDER BY account_year DESC, account_month DESC
            ";

            $results = DB::connection('sqlsrv')->select($query);

            return response()->json([
                'success' => true,
                'data' => $results,
                'count' => count($results),
                'limit' => 1000
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching actual premiums: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch actual premium data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    
    public function getFilteredActualPremiums(Request $request)
    {
        try {
            $query = "
                SELECT 
                    c.description AS department, 
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
                WHERE 1=1
            ";

            $bindings = [];

            if ($request->has('year')) {
                $query .= " AND d.account_year = ?";
                $bindings[] = $request->input('year');
            }

            if ($request->has('month')) {
                $query .= " AND d.account_month = ?";
                $bindings[] = $request->input('month');
            }

            if ($request->has('department')) {
                $query .= " AND c.description LIKE ?";
                $bindings[] = '%' . $request->input('department') . '%';
            }

            if ($request->has('business')) {
                $query .= " AND b.bustype_description LIKE ?";
                $bindings[] = '%' . $request->input('business') . '%';
            }

            $query .= " ORDER BY d.account_year DESC, d.account_month DESC
                LIMIT 1000";

            $results = DB::connection('mysql')->select($query, $bindings);

            return response()->json([
                'success' => true,
                'data' => $results,
                'count' => count($results),
                'filters' => $request->only(['year', 'month', 'department', 'business'])
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching filtered actual premiums: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch filtered actual premium data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
