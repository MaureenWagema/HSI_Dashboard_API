<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AccountMappingService
{
    protected $db;
    
   
    protected $accountMappings = [
        'claims_paid' => ['060-06-001', '060-06-002', '060-06-003', '060-06-004', '060-06-005', '060-06-006'],
        'change_in_reserves' => ['060-02-001', '060-02-002', '060-02-003', '060-02-004', '060-02-005', '060-02-006'],
        'salvage_subrogation' => ['070-08-001', '070-08-002', '070-08-003', '070-08-004', '070-08-005', '070-08-006'],
        'commissions' => ['080-01-001', '080-01-002', '080-01-003', '080-01-004', '080-01-005', '080-01-006'],
        'premium_collection' => ['060-12-002'],
        'policy_acquisition' => ['060-09-001', '060-09-002'],
        'gross_premium' => ['040-01-001','040-01-002','040-01-003','040-01-004','040-01-005','040-01-006','040-02-001','040-02-002','040-02-003','040-02-004','040-02-005','040-02-006','040-05-001'],
        'Reinsurance_Premiums_Ceded' => ['050-03-001','050-03-002','050-03-003','050-03-004','050-03-005','050-03-006','050-04-001','050-04-002','050-04-003','050-04-004','050-04-005','050-04-006','050-02-002']
    ];

    protected function getAccountToCategoryMapping()
    {
        $accountToCategory = [];
        foreach ($this->accountMappings as $category => $accounts) {
            foreach ($accounts as $account) {
                $accountToCategory[$account] = $category;
            }
        }
        return $accountToCategory;
    }

    
    public function __construct($connection = 'mysql')
    {
        $this->db = DB::connection($connection);
    }

    
    public function setConnection($connection)
    {
        $this->db = DB::connection($connection);
        return $this;
    }

    protected function getAccountMappingsQuery($year = null, $month = null)
    {
        $accountToCategory = $this->getAccountToCategoryMapping();
        
        $categoryCase = 'CASE ';
        foreach ($accountToCategory as $account => $category) {
            $categoryCase .= "WHEN displayAccountNo = '{$account}' THEN '{$category}' ";
        }
        $categoryCase .= "ELSE 'unknown' END as category";

        $query = $this->db
            ->table('glmasterinfo')
            ->select(
                'displayAccountNo as account_number',
                'account_name',
                'account_year',
                'account_month',
                DB::raw('CAST(monthly_credit AS DECIMAL(15,2)) as monthly_credit'),
                DB::raw('CAST(monthly_debit AS DECIMAL(15,2)) as monthly_debit'),
                DB::raw($categoryCase)
            )
            ->whereIn('displayAccountNo', array_keys($accountToCategory))
            ->orderBy('account_year')
            ->orderBy('account_month')
            ->orderBy('displayAccountNo');

        if ($year) {
            $query->where('account_year', $year);
        }
        if ($month) {
            $query->where('account_month', $month);
        }

        return $query;
    }

    public function getAccountMappings($year = null, $month = null)
    {
        $query = $this->getAccountMappingsQuery($year, $month);
        
        return $query->get()->map(function ($item) {
            return (object)[
                'account_number' => $item->account_number,
                'account_name' => $item->account_name,
                'account_year' => (int)$item->account_year,
                'account_month' => (int)$item->account_month,
                'monthly_credit' => (float)$item->monthly_credit,
                'monthly_debit' => (float)$item->monthly_debit,
                'category' => $item->category
            ];
        });
    }

     
    protected function tableExists()
    {
        try {
            $result = DB::connection('sqlsrv')->select(
                "SELECT 1 FROM sys.tables WHERE name = 'ratio_data'"
            );
            return !empty($result);
        } catch (\Exception $e) {
            Log::warning('Error checking if table exists: ' . $e->getMessage());
            return false;
        }
    }

    protected function createTable()
    {
        try {
            try {
                $result = DB::connection('sqlsrv')->select(
                    "SELECT TOP 1 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'ratio_data'"
                );
            } catch (\Exception $e) {
                Log::warning('Error checking table existence (INFORMATION_SCHEMA): ' . $e->getMessage());
            }
            
            try {
                $result = DB::connection('sqlsrv')->select(
                    "SELECT TOP 1 1 FROM sys.tables WHERE name = 'ratio_data'"
                );
            } catch (\Exception $e) {
                Log::warning('Error checking table existence (sys.tables): ' . $e->getMessage());
            }

            $tableName = 'ratio_data';
            $createTableSQL = "
            BEGIN TRY
                IF NOT EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = '{$tableName}')
                BEGIN
                    PRINT 'Creating table {$tableName}...';
                    CREATE TABLE {$tableName} (
                        id INT IDENTITY(1,1) PRIMARY KEY,
                        display_account_number VARCHAR(50) NOT NULL,
                        account_name NVARCHAR(255),
                        account_year INT NOT NULL,
                        account_month INT NOT NULL,
                        monthly_credit DECIMAL(15,2) DEFAULT 0,
                        monthly_debit DECIMAL(15,2) DEFAULT 0,
                        category VARCHAR(100),
                        created_at DATETIME2,
                        updated_at DATETIME2
                    )
                    PRINT 'Table {$tableName} created successfully';
                    SELECT 1 as result;
                END
                ELSE
                BEGIN
                    PRINT 'Table {$tableName} already exists';
                    SELECT 1 as result;
                END
            END TRY
            BEGIN CATCH
                PRINT 'Error creating table: ' + ERROR_MESSAGE();
                SELECT 0 as result, ERROR_MESSAGE() as error_message, ERROR_NUMBER() as error_number;
            END CATCH";

            
            return $this->executeWithRetry(function() use ($tableName) {
                try {
                    $result = DB::connection('sqlsrv')->select(
                        "SELECT * FROM OPENROWSET('SQLNCLI', 'Server=(local);Trusted_Connection=yes;', '
                            IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = ''{$tableName}'')
                            BEGIN
                                CREATE TABLE {$tableName} (
                                    id INT IDENTITY(1,1) PRIMARY KEY,
                                    display_account_number VARCHAR(50) NOT NULL,
                                    account_name NVARCHAR(255),
                                    account_year INT NOT NULL,
                                    account_month INT NOT NULL,
                                    monthly_credit DECIMAL(15,2) DEFAULT 0,
                                    monthly_debit DECIMAL(15,2) DEFAULT 0,
                                    category VARCHAR(100),
                                    created_at DATETIME2,
                                    updated_at DATETIME2
                                )
                                SELECT 1 as result, ''Table created successfully'' as message
                            END
                            ELSE
                            BEGIN
                                SELECT 1 as result, ''Table already exists'' as message
                            END
                        ')"
                    );
                    
                    if (empty($result)) {
                        throw new \Exception('No result returned from table creation query');
                    }
                    
                    
                    $verificationMethods = [
                        'INFORMATION_SCHEMA' => "SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = '{$tableName}'",
                        'sys.tables' => "SELECT 1 FROM sys.tables WHERE name = '{$tableName}'",
                        'sys.objects' => "SELECT 1 FROM sys.objects WHERE type = 'U' AND name = '{$tableName}'"
                    ];
                    
                    $verified = false;
                    $lastError = null;
                    
                    foreach ($verificationMethods as $method => $query) {
                        try {
                            $check = DB::connection('sqlsrv')->select($query);
                        } catch (\Exception $e) {
                            $lastError = $e->getMessage();
                            Log::warning("Verification using {$method} failed: {$lastError}");
                        }
                    }
                    
                    if (!$verified) {
                        throw new \Exception('Table creation verification failed using all methods. Last error: ' . $lastError);
                    }
                    
                    return true;
                    
                } catch (\Exception $e) {
                    try {
                        $errorInfo = DB::connection('sqlsrv')->select(
                            "SELECT ERROR_NUMBER() as error_number, ERROR_MESSAGE() as error_message"
                        );
                        if (!empty($errorInfo)) {
                            throw new \Exception(sprintf(
                                'SQL Error %s: %s',
                                $errorInfo[0]->error_number ?? 'unknown',
                                $errorInfo[0]->error_message ?? 'no details'
                            ));
                        }
                    } catch (\Exception $innerE) {
                        throw $e;
                    }
                    throw $e;
                }
            }, 3, 2000); // 3 retries, 2s delay
            
        } catch (\Exception $e) {
            $errorMsg = 'Error creating table: ' . $e->getMessage();
            Log::error($errorMsg);
            
            try {
                $errorInfo = DB::connection('sqlsrv')->select(
                    "SELECT ERROR_NUMBER() as error_number, ERROR_MESSAGE() as error_message"
                );
                if (!empty($errorInfo)) {
                    $errorMsg .= sprintf(" (SQL Error %s: %s)", 
                        $errorInfo[0]->error_number ?? 'unknown', 
                        $errorInfo[0]->error_message ?? 'no details');
                }
            } catch (\Exception $innerE) {
                $errorMsg .= ' (Additional error details unavailable: ' . $innerE->getMessage() . ')';
            }
            
            Log::error($errorMsg);
            return false;
        }
    }

    protected function ensureTableExists()
    {
        try {
            if (!$this->createTable()) {
                throw new \Exception('Failed to create or verify table existence');
            }
            
            $indexes = [
                'UQ_ratio_data_unique' => [
                    'sql' => "ALTER TABLE ratio_data ADD CONSTRAINT UQ_ratio_data_unique 
                             UNIQUE (display_account_number, account_year, account_month)",
                    'isConstraint' => true
                ],
                'idx_ratio_data_account' => [
                    'sql' => 'CREATE INDEX idx_ratio_data_account ON ratio_data (display_account_number)',
                    'isConstraint' => false
                ],
                'idx_ratio_data_period' => [
                    'sql' => 'CREATE INDEX idx_ratio_data_period ON ratio_data (account_year, account_month)',
                    'isConstraint' => false
                ],
                'idx_ratio_data_category' => [
                    'sql' => 'CREATE INDEX idx_ratio_data_category ON ratio_data (category)',
                    'isConstraint' => false
                ]
            ];
            
            foreach ($indexes as $name => $index) {
                try {
                    $this->executeWithRetry(function() use ($name, $index) {
                        $checkSql = $index['isConstraint'] 
                            ? "SELECT 1 FROM sys.indexes i INNER JOIN sys.objects o ON i.object_id = o.object_id WHERE i.name = ? AND o.name = 'ratio_data'"
                            : "SELECT 1 FROM sys.indexes WHERE name = ? AND object_id = OBJECT_ID('ratio_data')";
                            
                        $exists = DB::connection('sqlsrv')->select($checkSql, [$name]);
                        
                        if (empty($exists)) {
                            DB::connection('sqlsrv')->statement($index['sql']);
                        }
                        return true;
                    }, 3, 2000); 
                    
                    usleep(100000); 
                } catch (\Exception $e) {
                    Log::warning("Failed to create index/constraint $name: " . $e->getMessage());
                }
            }
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Error in ensureTableExists: ' . $e->getMessage());
            throw $e;
        }
    }
    
    protected function addIndexIfNotExists($indexName, $createStatement)
    {
        try {
            $exists = DB::connection('sqlsrv')->select("
                SELECT 1 
                FROM sys.indexes 
                WHERE name = ? AND object_id = OBJECT_ID('ratio_data')
            ", [$indexName]);
            
            if (empty($exists)) {
                DB::connection('sqlsrv')->statement($createStatement);
            }
        } catch (\Exception $e) {
            Log::warning("Error adding index {$indexName}: " . $e->getMessage());
        }
    }

    protected function executeWithRetry($callback, $maxRetries = 3, $delayMs = 1000)
    {
        $retries = 0;
        $lastException = null;
        
        while ($retries < $maxRetries) {
            try {
                return $callback();
            } catch (\Exception $e) {
                $lastException = $e;
                $retries++;
                if ($retries < $maxRetries) {
                    $waitTime = $delayMs * $retries; 
                    usleep($waitTime * 1000); 
                    Log::warning("Retry $retries after error: " . $e->getMessage());
                }
            }
        }
        
        throw $lastException;
    }

    public function syncToSqlServer($year = null, $month = null)
    {
        
        try {
            $data = $this->getAccountMappings($year, $month);
            
            if ($data->isEmpty()) {
                return ['success' => false, 'message' => 'No data found to sync'];
            }

            $this->ensureTableExists();
            
            $chunkSize = 5; 
            $totalSynced = 0;
            $failed = 0;
            $processed = 0;
            $totalRecords = count($data);
            
            
            $chunks = $data->chunk($chunkSize);
            $totalChunks = $chunks->count();
            
            foreach ($chunks as $chunkIndex => $chunk) {
                
                foreach ($chunk as $item) {
                    $processed++;
                    
                    if ($processed % 50 === 0) {
                        // Progress tracking every 50 records instead of 10
                    }
                    
                    $itemArray = is_object($item) ? (array)$item : $item;
                    
                    $recordId = sprintf('%s (%d/%d)', 
                        $itemArray['account_number'] ?? $item->account_number ?? 'unknown', 
                        $itemArray['account_year'] ?? $item->account_year ?? 0, 
                        $itemArray['account_month'] ?? $item->account_month ?? 0
                    );
                    
                    try {
                        $updateSuccess = $this->executeWithRetry(function() use ($item, $itemArray) {
                            $updated = DB::connection('sqlsrv')->update(
                                "UPDATE ratio_data WITH (ROWLOCK) 
                                 SET account_name = ?, 
                                     monthly_credit = ?, 
                                     monthly_debit = ?, 
                                     category = ?, 
                                     updated_at = ?
                                 WHERE display_account_number = ? 
                                 AND account_year = ? 
                                 AND account_month = ?",
                                [
                                    $itemArray['account_name'] ?? $item->account_name ?? null,
                                    $itemArray['monthly_credit'] ?? $item->monthly_credit ?? 0,
                                    $itemArray['monthly_debit'] ?? $item->monthly_debit ?? 0,
                                    $itemArray['category'] ?? $item->category ?? 'unknown',
                                    now()->toDateTimeString(),
                                    $itemArray['account_number'] ?? $item->account_number ?? null,
                                    $itemArray['account_year'] ?? $item->account_year ?? 0,
                                    $itemArray['account_month'] ?? $item->account_month ?? 0
                                ]
                            );
                            return $updated > 0;
                        }, 3, 1000); // 3 retries, 1s delay

                        if (!$updateSuccess) {
                            $inserted = $this->executeWithRetry(function() use ($item, $itemArray) {
                                try {
                                    return DB::connection('sqlsrv')->insert(
                                        "INSERT INTO ratio_data (
                                            display_account_number, account_name, account_year, account_month, 
                                            monthly_credit, monthly_debit, category, created_at, updated_at
                                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                                        [
                                            $itemArray['account_number'] ?? $item->account_number ?? null,
                                            $itemArray['account_name'] ?? $item->account_name ?? null,
                                            $itemArray['account_year'] ?? $item->account_year ?? 0,
                                            $itemArray['account_month'] ?? $item->account_month ?? 0,
                                            $itemArray['monthly_credit'] ?? $item->monthly_credit ?? 0,
                                            $itemArray['monthly_debit'] ?? $item->monthly_debit ?? 0,
                                            $itemArray['category'] ?? $item->category ?? 'unknown',
                                            now()->toDateTimeString(),
                                            now()->toDateTimeString()
                                        ]
                                    );
                                } catch (\Exception $insertEx) {
                                    if (strpos($insertEx->getMessage(), '2601') === false) {
                                        throw $insertEx;
                                    }
                                    return true;
                                }
                            }, 3, 1000); // 3 retries, 1s delay
                            
                            if ($inserted) {
                                $totalSynced++;
                            }
                        } else {
                            $totalSynced++;
                        }
                    } catch (\Exception $e) {
                        $failed++;
                        Log::error(sprintf(
                            'Error processing record %s: %s',
                            $recordId,
                            $e->getMessage()
                        ));
                    }
                    
                    usleep(50000); 
                }
                
                usleep(100000); // 100ms
                
                usleep(50000); // 50ms
            }
            
            if ($failed > 0) {
                throw new \Exception("Completed with $failed errors. Check the logs for details.");
            }

            return [
                'success' => true, 
                'message' => "Successfully synced {$totalSynced} records",
                'synced_count' => $totalSynced
            ];

        } catch (\Exception $e) {
            Log::error('Error syncing data to SQL Server: ' . $e->getMessage());
            return [
                'success' => false, 
                'message' => 'Error syncing data: ' . $e->getMessage()
            ];
        }
    }

    public function getAvailablePeriods()
    {
        $years = DB::connection('mysql')
            ->table('glmasterinfo')
            ->select('account_year')
            ->distinct()
            ->orderBy('account_year')
            ->pluck('account_year');

        $months = DB::connection('mysql')
            ->table('glmasterinfo')
            ->select('account_month')
            ->distinct()
            ->orderBy('account_month')
            ->pluck('account_month');

        return [
            'years' => $years,
            'months' => $months
        ];
    }
}
