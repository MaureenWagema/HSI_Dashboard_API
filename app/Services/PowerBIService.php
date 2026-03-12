<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PowerBIService
{
    protected $client;
    protected $tenantId;
    protected $clientId;
    protected $clientSecret;

    public function __construct()
    {
        $this->client = new Client();
        $this->tenantId = env('POWERBI_TENANT_ID');
        $this->clientId = env('POWERBI_CLIENT_ID');
        $this->clientSecret = env('POWERBI_CLIENT_SECRET');
    }

    


    public function getEmbedConfig($reportId = null, $groupId = null, $cacheKey = 'powerbi_embed_token')
    {
        $reportId = $reportId ?? env('POWERBI_REPORT_ID');
        $groupId = $groupId ?? env('POWERBI_GROUP_ID');

        if (Cache::has($cacheKey)) {
            $cachedToken = Cache::get($cacheKey);
            
            $expiresAt = \Carbon\Carbon::parse($cachedToken['expires']);
            $now = now();
            $bufferMinutes = 5;
            
            if ($now->lt($expiresAt->subMinutes($bufferMinutes))) {
                Log::info("Power BI: Returning valid cached token for {$cacheKey}", [
                    'expires_at' => $cachedToken['expires'],
                    'minutes_remaining' => $now->diffInMinutes($expiresAt)
                ]);
                return $cachedToken;
            } else {
                Log::info("Power BI: Cached token expired for {$cacheKey}, refreshing...", [
                    'expired_at' => $cachedToken['expires'],
                    'current_time' => $now->toISOString()
                ]);
                Cache::forget($cacheKey);
            }
        }

        Log::info("Power BI: Generating new token for {$cacheKey}");

        try {
            $aadToken = $this->getAzureToken();

            $reportData = $this->getReportDetails($aadToken, $groupId, $reportId);
            $datasetId = $reportData['datasetId'];

            $embedData = $this->generateEmbedToken($aadToken, $reportId, $datasetId, $groupId);

            $response = [
                'accessToken' => $embedData['token'],
                'embedUrl'    => "https://app.powerbi.com/reportEmbed?reportId={$reportId}&groupId={$groupId}",
                'reportId'    => $reportId,
                'expires'     => $embedData['expiration'],
                'generatedAt' => now()->toISOString(),
            ];

            Cache::put($cacheKey, $response, now()->addMinutes(50));

            Log::info("Power BI: Token cached successfully for {$cacheKey}, expires: {$response['expires']}");

            return $response;

        } catch (\Exception $e) {
            Log::error("Power BI Service error for {$cacheKey}", [
                'message' => $e->getMessage(),
                'reportId' => $reportId,
                'groupId' => $groupId,
            ]);
            throw $e;
        }
    }

    
    public function getMainDashboardEmbedConfig()
    {
        return $this->getEmbedConfig(
            env('POWERBI_REPORT_ID'),
            env('POWERBI_GROUP_ID'),
            'powerbi_embed_token_main'
        );
    }

    
    public function getTatReportEmbedConfig()
    {
        return $this->getEmbedConfig(
            env('POWERBI_REPORT_ID_TAT'),
            env('POWERBI_GROUP_ID_TAT'),
            'powerbi_embed_token_tat'
        );
    }

    
    public function refreshTokensIfNeeded()
    {
        $results = [];
        
        // Check main dashboard token
        $mainStatus = $this->getTokenStatus('powerbi_embed_token_main');
        if ($mainStatus['status'] === 'expired' || !$mainStatus['cached']) {
            Log::info("Power BI: Proactively refreshing main dashboard token");
            $this->getMainDashboardEmbedConfig();
            $results['main_dashboard'] = 'refreshed';
        } else {
            $results['main_dashboard'] = 'valid';
        }
        
        // Check TAT report token
        $tatStatus = $this->getTokenStatus('powerbi_embed_token_tat');
        if ($tatStatus['status'] === 'expired' || !$tatStatus['cached']) {
            Log::info("Power BI: Proactively refreshing TAT report token");
            $this->getTatReportEmbedConfig();
            $results['tat_report'] = 'refreshed';
        } else {
            $results['tat_report'] = 'valid';
        }
        
        return $results;
    }

    


    public function clearCache($cacheKey = null)
    {
        if ($cacheKey) {
            Cache::forget($cacheKey);
            Log::info("Power BI: Cleared cache for {$cacheKey}");
        } else {
            Cache::forget('powerbi_embed_token_main');
            Cache::forget('powerbi_embed_token_tat');
            Log::info("Power BI: Cleared all caches");
        }
    }

  

    public function getCacheStatus()
    {
        $mainStatus = $this->getTokenStatus('powerbi_embed_token_main');
        $tatStatus = $this->getTokenStatus('powerbi_embed_token_tat');
        
        return [
            'main_dashboard' => $mainStatus,
            'tat_report' => $tatStatus,
        ];
    }

 

    protected function getTokenStatus($cacheKey)
    {
        if (!Cache::has($cacheKey)) {
            return [
                'cached' => false,
                'expires_at' => null,
                'status' => 'not_cached',
                'minutes_remaining' => null
            ];
        }

        $cachedToken = Cache::get($cacheKey);
        $expiresAt = \Carbon\Carbon::parse($cachedToken['expires']);
        $now = now();
        $bufferMinutes = 5;

        if ($now->lt($expiresAt->subMinutes($bufferMinutes))) {
            return [
                'cached' => true,
                'expires_at' => $cachedToken['expires'],
                'status' => 'valid',
                'minutes_remaining' => $now->diffInMinutes($expiresAt)
            ];
        } else {
            return [
                'cached' => true,
                'expires_at' => $cachedToken['expires'],
                'status' => 'expired',
                'minutes_remaining' => 0
            ];
        }
    }

   

    protected function getAzureToken()
    {
        $tokenResponse = $this->client->post("https://login.microsoftonline.com/{$this->tenantId}/oauth2/v2.0/token", [
            'form_params' => [
                'grant_type'    => 'client_credentials',
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
                'scope'         => 'https://analysis.windows.net/powerbi/api/.default',
            ],
            'timeout' => 30,
        ]);

        $aadData = json_decode($tokenResponse->getBody()->getContents(), true);
        return $aadData['access_token'] ?? throw new \Exception('No access_token from Azure');
    }

    

    protected function getReportDetails($aadToken, $groupId, $reportId)
    {
        $reportResponse = $this->client->get("https://api.powerbi.com/v1.0/myorg/groups/{$groupId}/reports/{$reportId}", [
            'headers' => [
                'Authorization' => "Bearer {$aadToken}",
                'Content-Type'  => 'application/json',
            ],
            'timeout' => 30,
        ]);

        $reportData = json_decode($reportResponse->getBody()->getContents(), true);
        return [
            'datasetId' => $reportData['datasetId'] ?? throw new \Exception('No datasetId found in report response'),
        ];
    }

    
    
    protected function generateEmbedToken($aadToken, $reportId, $datasetId, $groupId)
    {
        $embedResponse = $this->client->post('https://api.powerbi.com/v1.0/myorg/GenerateToken', [
            'headers' => [
                'Authorization' => "Bearer {$aadToken}",
                'Content-Type'  => 'application/json',
            ],
            'json' => [
                'reports' => [
                    ['id' => $reportId]
                ],
                'datasets' => [
                    ['id' => $datasetId]
                ],
                'targetWorkspaces' => [
                    ['id' => $groupId]
                ],
                'lifetimeInMinutes' => 60,
                'accessLevel'       => 'View',
            ],
            'timeout' => 30,
        ]);

        $embedData = json_decode($embedResponse->getBody()->getContents(), true);
        return [
            'token' => $embedData['token'] ?? throw new \Exception('No embed token generated'),
            'expiration' => $embedData['expiration'] ?? throw new \Exception('No expiration in embed token'),
        ];
    }
}
