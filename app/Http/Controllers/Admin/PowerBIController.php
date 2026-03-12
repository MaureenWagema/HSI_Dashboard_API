<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PowerBIService;
use Illuminate\Support\Facades\Log;

class PowerBIController extends Controller
{
    protected $powerBI;

    public function __construct(PowerBIService $powerBI)
    {
        $this->powerBI = $powerBI;
    }

    
    
    public function getEmbedConfig(Request $request)
    {
        try {
            $embedConfig = $this->powerBI->getMainDashboardEmbedConfig();
            
            Log::info('Power BI main dashboard embed config served', [
                'cached' => isset($embedConfig['generatedAt']),
                'expires' => $embedConfig['expires'] ?? null,
            ]);

            return response()->json($embedConfig);

        } catch (\Exception $e) {
            Log::error('Power BI main dashboard embed failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'error' => 'Failed to load Power BI dashboard',
                'details' => config('app.debug') ? $e->getMessage() : 'Please try again later'
            ], 500);
        }
    }

   

    public function getTatEmbedConfig(Request $request)
    {
        try {
            $embedConfig = $this->powerBI->getTatReportEmbedConfig();
            
            Log::info('Power BI TAT report embed config served', [
                'cached' => isset($embedConfig['generatedAt']),
                'expires' => $embedConfig['expires'] ?? null,
            ]);

            return response()->json($embedConfig);

        } catch (\Exception $e) {
            Log::error('Power BI TAT report embed failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'error' => 'Failed to load Power BI TAT report',
                'details' => config('app.debug') ? $e->getMessage() : 'Please try again later'
            ], 500);
        }
    }


    public function clearCache(Request $request)
    {
        try {
            $this->powerBI->clearCache();
            
            Log::info('Power BI cache cleared by admin');
            
            return response()->json([
                'message' => 'Power BI cache cleared successfully',
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to clear Power BI cache', [
                'message' => $e->getMessage()
            ]);
            
            return response()->json([
                'error' => 'Failed to clear cache',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    
    public function getCacheStatus(Request $request)
    {
        try {
            $status = $this->powerBI->getCacheStatus();
            
            return response()->json([
                'cache_status' => $status,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get Power BI cache status', [
                'message' => $e->getMessage()
            ]);
            
            return response()->json([
                'error' => 'Failed to get cache status',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Refresh Power BI tokens if needed (admin endpoint)
     */
    public function refreshTokens(Request $request)
    {
        try {
            $results = $this->powerBI->refreshTokensIfNeeded();
            
            Log::info('Power BI token refresh completed', [
                'results' => $results
            ]);
            
            return response()->json([
                'message' => 'Power BI token refresh completed',
                'results' => $results,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to refresh Power BI tokens', [
                'message' => $e->getMessage()
            ]);
            
            return response()->json([
                'error' => 'Failed to refresh tokens',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}