<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ActualPremiumSyncService
{
    protected SyncTrackerService $tracker;
    protected int $batchSize = 500;
    protected int $maxRetries = 3;
    
    public function __construct(SyncTrackerService $tracker)
    {
        $this->tracker = $tracker;
    }
    
    public function sync(string $jobId = null): string
    {
        $jobId = $jobId ?? $this->tracker->createSync('actual_premiums');
        
        try {
            $this->tracker->startSync($jobId);
            $this->tracker->log('actual_premiums', $jobId, 'info', 'Starting incremental sync');
            
            $lastSync = $this->tracker->getLastSyncInfo('actual_premiums');
            $lastSyncTimestamp = $lastSync?->last_sync_timestamp;
            $lastSyncId = $lastSync?->last_sync_id;
            
            $totalRecords = $this->getTotalChangedRecords($lastSyncTimestamp);
            $this->tracker->updateProgress($jobId, 0, $totalRecords);
            
            if ($totalRecords === 0) {
                $this->tracker->log('actual_premiums', $jobId, 'info', 'No new records to sync');
                $this->tracker->completeSync($jobId, $lastSyncId);
                return $jobId;
            }
              
            $processed = 0;
            $offset = 0;
            $currentSyncId = uniqid('sync_', true);
            
            while ($processed < $totalRecords) {
                $batch = $this->getChangedRecordsBatch($lastSyncTimestamp, $offset, $this->batchSize);
                
                if (empty($batch)) {
                    break;
                }
                
                $this->processBatch($batch, $jobId, $currentSyncId);
                
                $processed += count($batch);
                $offset += $this->batchSize;
                
                $this->tracker->updateProgress($jobId, $processed, $totalRecords);
                $this->tracker->log('actual_premiums', $jobId, 'info', "Processed {$processed}/{$totalRecords} records");
                
                // Clear memory
                unset($batch);
            }
            
            $this->tracker->completeSync($jobId, $currentSyncId);
            $this->tracker->log('actual_premiums', $jobId, 'info', "Sync completed. Total records: {$processed}");
            
        } catch (\Exception $e) {
            $this->tracker->failSync($jobId, $e->getMessage());
            throw $e;
        }
        
        return $jobId;
    }
    
    protected function getTotalChangedRecords(?Carbon $lastSyncTimestamp): int
    {
        $query = DB::connection('mysql')
            ->table('debitmastinfo d')
            ->join('classinfo c', 'c.class_code', '=', 'd.class_code')
            ->join('bustypeinfo b', 'b.bustype', '=', 'd.bustype')
            ->join('commdeptinfo e', 'e.commdept_code', '=', 'c.commdept_code');
            
        if ($lastSyncTimestamp) {
            $query->where('d.last_modified', '>', $lastSyncTimestamp);
        }
        
        return $query->count();
    }
    
    protected function getChangedRecordsBatch(?Carbon $lastSyncTimestamp, int $offset, int $limit): array
    {
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
                d.NetAmount AS NetAmount,
                d.last_modified,
                ? as sync_id
            FROM classinfo c
            RIGHT JOIN debitmastinfo d ON c.class_code = d.class_code
            RIGHT JOIN bustypeinfo b ON b.bustype = d.bustype
            RIGHT JOIN commdeptinfo e ON e.commdept_code = c.commdept_code
        ";
        
        $bindings = [];
        
        if ($lastSyncTimestamp) {
            $query .= " WHERE d.last_modified > ?";
            $bindings[] = $lastSyncTimestamp;
        }
        
        $query .= " ORDER BY d.last_modified ASC LIMIT {$limit} OFFSET {$offset}";
        $bindings[] = uniqid('sync_', true);
        
        return DB::connection('mysql')->select($query, $bindings);
    }
    
    protected function processBatch(array $batch, string $jobId, string $syncId): void
    {
        DB::connection('sqlsrv')->transaction(function () use ($batch, $syncId) {
            foreach ($batch as $record) {
                $this->upsertRecord($record, $syncId);
            }
        });
    }
    
    protected function upsertRecord(object $record, string $syncId): void
    {
        $data = [
            'Debit_ID' => $record->Debit_ID,
            'department' => $record->department,
            'sub_department' => $record->sub_department,
            'account_year' => $record->account_year,
            'account_month' => $record->account_month,
            'business' => $record->business,
            'actual_premium' => $record->actual_premium,
            'policy_no' => $record->policy_no,
            'Name' => $record->Name,
            'GrossAmount' => $record->GrossAmount ?? 0,
            'NetAmount' => $record->NetAmount ?? 0,
            'sync_id' => $syncId,
            'updated_at' => now()
        ];
        
        // Use updateOrInsert to prevent duplicates and handle updates
        DB::connection('sqlsrv')->table('actual_premium')
            ->updateOrInsert(
                [
                    'policy_no' => $record->policy_no,
                    'account_year' => $record->account_year,
                    'account_month' => $record->account_month
                ],
                array_merge($data, ['created_at' => now()])
            );
    }
    
    public function getSyncStatus(string $jobId): array
    {
        return $this->tracker->getSyncStatus($jobId);
    }
    
    public function getRecentLogs(string $jobId, int $limit = 50): array
    {
        return $this->tracker->getRecentLogs($jobId, $limit);
    }
    
    public function getLastSyncInfo(): ?object
    {
        return $this->tracker->getLastSyncInfo('actual_premiums');
    }
    
    public function fullSync(): string
    {
        // For full sync, we can set last_sync_timestamp to null
        // This will sync all records
        return $this->sync();
    }
    
    public function repairSync(): string
    {
        // Find failed or incomplete syncs and retry them
        $failedSyncs = DB::table('sync_trackers')
            ->where('sync_type', 'actual_premiums')
            ->where('status', 'failed')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
            
        foreach ($failedSyncs as $failed) {
            $this->tracker->log('actual_premiums', $failed->job_id, 'info', 'Attempting to repair failed sync');
            $this->sync($failed->job_id);
        }
        
        return 'repair_completed';
    }
}
