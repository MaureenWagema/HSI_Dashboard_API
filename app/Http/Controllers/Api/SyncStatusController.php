<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class SyncStatusController extends Controller
{
    public function getStatus()
    {
        $logPath = storage_path('logs/data-sync.log');
        $status = [
            'last_sync' => null,
            'last_success' => null,
            'last_error' => null,
            'sync_count_today' => 0,
            'errors_today' => 0
        ];

        try {
            if (File::exists($logPath)) {
                $logs = File::get($logPath);
                $lines = array_reverse(explode("\n", $logs));
                
                $today = now()->format('Y-m-d');
                
                foreach ($lines as $line) {
                    if (empty($line)) continue;
                    
                    // Extract timestamp and message
                    if (preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/', $line, $matches)) {
                        $timestamp = $matches[1];
                        $date = substr($timestamp, 0, 10);
                        
                        if (strpos($line, 'completed successfully') !== false) {
                            if (!$status['last_success']) {
                                $status['last_success'] = $timestamp;
                            }
                            if ($date === $today) {
                                $status['sync_count_today']++;
                            }
                        }
                        
                        if (strpos($line, 'failed') !== false || strpos($line, 'ERROR') !== false) {
                            if (!$status['last_error']) {
                                $status['last_error'] = $timestamp . ' - ' . $line;
                            }
                            if ($date === $today) {
                                $status['errors_today']++;
                            }
                        }
                        
                        if (!$status['last_sync']) {
                            $status['last_sync'] = $timestamp;
                        }
                    }
                }
            }
            
            return response()->json([
                'status' => 'success',
                'data' => $status
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting sync status: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get sync status'
            ], 500);
        }
    }
}
