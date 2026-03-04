<?php

//batch file for automatic

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
            $jobId = uniqid('sync_', true);
            
            
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
        try {
            set_time_limit(60); // 1 minute limit for web requests
            
            // Use transaction with retry logic for better reliability
            DB::connection('sqlsrv')->transaction(function () {
                // Truncate table instead of delete for better performance
                DB::connection('sqlsrv')->statement('TRUNCATE TABLE actual_premium');
            });

            $totalSynced = 0;
            $offset = 0;
            $batchSize = 100; // Reduced batch size for stability
            $maxBatches = 50; // Process max 50 batches (5,000 records) per request
            $batchCount = 0;

            do {
                
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

                $mysqlData = DB::connection('mysql')->select($query);

                if (empty($mysqlData)) {
                    break;
                }

                // Prepare data for insertion
                $insertData = [];
                $nullDeptCount = 0;
                $validDeptCount = 0;
                $deptStats = [];
                
                foreach ($mysqlData as $row) {
                    // Track department statistics
                    if ($row->department === null || $row->department === '') {
                        $nullDeptCount++;
                    } else {
                        $validDeptCount++;
                        $deptStats[$row->department] = ($deptStats[$row->department] ?? 0) + 1;
                    }
                    
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
                

                // Use smaller chunks and transaction for each batch
                if (!empty($insertData)) {
                    DB::connection('sqlsrv')->transaction(function () use ($insertData) {
                        $chunks = array_chunk($insertData, 25); // Smaller chunks
                        foreach ($chunks as $chunk) {
                            DB::connection('sqlsrv')->table('actual_premium')->insert($chunk);
                            $totalSynced += count($chunk);
                        }
                    });
                }

                $offset += $batchSize;
                $batchCount++;
                
                // Clear memory
                unset($mysqlData, $insertData, $chunks);

                // Safety check to prevent infinite loops
                if ($batchCount >= $maxBatches) {
                    break;
                }

            } while (true);

            
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
    
    public function testTruncate()
    {
        try {
            // Test TRUNCATE operation
            DB::connection('sqlsrv')->transaction(function () {
                DB::connection('sqlsrv')->statement('TRUNCATE TABLE actual_premium');
            });
            
            // Check if table is empty
            $count = DB::connection('sqlsrv')->table('actual_premium')->count();
            
            return response()->json([
                'success' => true,
                'message' => 'TRUNCATE completed successfully',
                'remaining_records' => $count
            ]);
            
        } catch (\Exception $e) {
            Log::error('TRUNCATE test failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'TRUNCATE failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function debugDepartmentMapping()
    {
        try {
            $debugInfo = [];
            
            // Debug 1: Check commdeptinfo table
            $commdeptQuery = "SELECT COUNT(*) as total, COUNT(DISTINCT commdept_code) as unique_codes FROM commdeptinfo";
            $commdeptCount = DB::connection('mysql')->select($commdeptQuery)[0];
            $debugInfo['commdeptinfo'] = $commdeptCount;
            
            // Debug 2: Check classinfo table for commdept_code
            $classinfoQuery = "
                SELECT 
                    COUNT(*) as total_classes,
                    COUNT(commdept_code) as classes_with_dept_code,
                    COUNT(DISTINCT commdept_code) as unique_dept_codes
                FROM classinfo
            ";
            $classinfoCount = DB::connection('mysql')->select($classinfoQuery)[0];
            $debugInfo['classinfo'] = $classinfoCount;
            
            // Debug 3: Check debitmastinfo table
            $debitQuery = "
                SELECT 
                    COUNT(*) as total_records,
                    COUNT(DISTINCT class_code) as unique_class_codes
                FROM debitmastinfo
            ";
            $debitCount = DB::connection('mysql')->select($debitQuery)[0];
            $debugInfo['debitmastinfo'] = $debitCount;
            
            // Debug 4: Check join chain integrity
            $joinTestQuery = "
                SELECT 
                    COUNT(*) as total_joined,
                    COUNT(e.commdept_name) as with_department_name,
                    COUNT(DISTINCT e.commdept_name) as unique_departments
                FROM classinfo c
                RIGHT JOIN debitmastinfo d ON c.class_code = d.class_code
                RIGHT JOIN bustypeinfo b ON b.bustype = d.bustype
                RIGHT JOIN commdeptinfo e ON e.commdept_code = c.commdept_code
            ";
            $joinTest = DB::connection('mysql')->select($joinTestQuery)[0];
            $debugInfo['join_test'] = $joinTest;
            
            // Debug 5: Find records with missing departments
            $missingDeptQuery = "
                SELECT COUNT(*) as missing_dept_count
                FROM classinfo c
                RIGHT JOIN debitmastinfo d ON c.class_code = d.class_code
                RIGHT JOIN bustypeinfo b ON b.bustype = d.bustype
                RIGHT JOIN commdeptinfo e ON e.commdept_code = c.commdept_code
                WHERE e.commdept_name IS NULL OR e.commdept_name = ''
            ";
            $missingDept = DB::connection('mysql')->select($missingDeptQuery)[0];
            $debugInfo['missing_departments'] = $missingDept;
            
            // Debug 6: Sample of problematic records
            $problemSampleQuery = "
                SELECT 
                    d.policy_no,
                    d.class_code,
                    c.commdept_code,
                    e.commdept_name
                FROM classinfo c
                RIGHT JOIN debitmastinfo d ON c.class_code = d.class_code
                RIGHT JOIN bustypeinfo b ON b.bustype = d.bustype
                RIGHT JOIN commdeptinfo e ON e.commdept_code = c.commdept_code
                WHERE e.commdept_name IS NULL OR e.commdept_name = ''
                LIMIT 5
            ";
            $problemSample = DB::connection('mysql')->select($problemSampleQuery);
            $debugInfo['problematic_records'] = $problemSample;
            
            // Debug 7: Department distribution in source
            $deptDistQuery = "
                SELECT 
                    e.commdept_name,
                    COUNT(*) as count
                FROM classinfo c
                RIGHT JOIN debitmastinfo d ON c.class_code = d.class_code
                RIGHT JOIN bustypeinfo b ON b.bustype = d.bustype
                RIGHT JOIN commdeptinfo e ON e.commdept_code = c.commdept_code
                WHERE e.commdept_name IS NOT NULL AND e.commdept_name != ''
                GROUP BY e.commdept_name
                ORDER BY count DESC
                LIMIT 10
            ";
            $deptDistribution = DB::connection('mysql')->select($deptDistQuery);
            $debugInfo['department_distribution'] = $deptDistribution;
            
            // Debug 8: Compare with SQL Server
            $sqlServerQuery = "
                SELECT TOP 10
                    department,
                    COUNT(*) as count
                FROM actual_premium
                WHERE department IS NOT NULL AND department != ''
                GROUP BY department
                ORDER BY count DESC
            ";
            $sqlServerDepts = DB::connection('sqlsrv')->select($sqlServerQuery);
            $debugInfo['sql_server_departments'] = $sqlServerDepts;

            return response()->json([
                'success' => true,
                'data' => $debugInfo
            ]);

        } catch (\Exception $e) {
            Log::error('Error in department mapping debug: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to debug department mapping',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function testDepartments()
    {
        try {
            // Test 1: Get departments from source (MySQL)
            $sourceQuery = "
                SELECT DISTINCT 
                    e.commdept_code,
                    e.commdept_name AS department_name
                FROM commdeptinfo e
                WHERE e.commdept_name IS NOT NULL 
                AND e.commdept_name != ''
                ORDER BY e.commdept_name ASC
            ";
            $sourceDepartments = DB::connection('mysql')->select($sourceQuery);

            // Test 2: Get departments from actual_premium table (SQL Server)
            $targetQuery = "
                SELECT DISTINCT 
                    department,
                    COUNT(*) as record_count
                FROM actual_premium
                WHERE department IS NOT NULL
                GROUP BY department
                ORDER BY department ASC
            ";
            $targetDepartments = DB::connection('sqlsrv')->select($targetQuery);

            // Test 3: Sample of joined data from sync query
            $sampleQuery = "
                SELECT 
                    e.commdept_name AS department, 
                    d.account_year, 
                    d.account_month, 
                    b.bustype_description AS business, 
                    d.balanceamount AS actual_premium,
                    d.policy_no AS policy_no
                FROM classinfo c
                RIGHT JOIN debitmastinfo d ON c.class_code = d.class_code
                RIGHT JOIN bustypeinfo b ON b.bustype = d.bustype
                RIGHT JOIN commdeptinfo e ON e.commdept_code = c.commdept_code
                ORDER BY d.account_year DESC, d.account_month DESC
                LIMIT 10
            ";
            $sampleData = DB::connection('mysql')->select($sampleQuery);

            return response()->json([
                'success' => true,
                'data' => [
                    'source_departments' => $sourceDepartments,
                    'target_departments' => $targetDepartments,
                    'sample_sync_data' => $sampleData,
                    'source_count' => count($sourceDepartments),
                    'target_count' => count($targetDepartments)
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error in department test: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to test departments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getDepartments()
    {
        try {
            $query = "
                SELECT DISTINCT 
                    e.commdept_code,
                    e.commdept_name AS department_name
                FROM commdeptinfo e
                WHERE e.commdept_name IS NOT NULL 
                AND e.commdept_name != ''
                ORDER BY e.commdept_name ASC
            ";

            $departments = DB::connection('mysql')->select($query);

            return response()->json([
                'success' => true,
                'data' => $departments,
                'count' => count($departments)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching departments: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch departments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

   
    public function getActualPremiums()
    {
        try {
            $query = "
                SELECT TOP 100
                    Debit_ID,
                    department, 
                    sub_department,
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
                $query .= " AND e.commdept_name LIKE ?";
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
