<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException; 
use Illuminate\Support\Facades\Log;

class PowerBIController extends Controller
{
    
    
    public function getEmbedConfig(Request $request)
    {

        
        try {
        \Log::info('Starting Power BI embed token generation', [
            'user_id' => $request->user()?->id,
            'tenant_id' => env('POWERBI_TENANT_ID'),
        ]);
        
        $tenantId     = env('POWERBI_TENANT_ID');
        $clientId     = env('POWERBI_CLIENT_ID');
        $clientSecret = env('POWERBI_CLIENT_SECRET');
        $reportId     = env('POWERBI_REPORT_ID');
        $groupId      = env('POWERBI_GROUP_ID');

        Log::info('Power BI Config', [
    'tenantId'     => $tenantId     ?: 'MISSING',
    'clientId'     => $clientId     ?: 'MISSING',
    'clientSecret' => $clientSecret ? 'SET (length: ' . strlen($clientSecret) . ')' : 'MISSING',
    'reportId'     => $reportId     ?: 'MISSING',
    'groupId'      => $groupId      ?: 'MISSING',
]);

       

        $client = new \GuzzleHttp\Client();

            $tokenResponse = $client->post("https://login.microsoftonline.com/{$tenantId}/oauth2/v2.0/token", [
                'form_params' => [
                    'grant_type'    => 'client_credentials',
                    'client_id'     => $clientId,
                    'client_secret' => $clientSecret,
                    'scope'         => 'https://analysis.windows.net/powerbi/api/.default',
                ], 
            ]);

$aadData = json_decode($tokenResponse->getBody()->getContents(), true);
        $aadToken = $aadData['access_token'] ?? throw new \Exception('No access_token from Azure');
            
            \Log::info('Azure token received successfully');
        $reportResponse = $client->get("https://api.powerbi.com/v1.0/myorg/groups/{$groupId}/reports/{$reportId}", [
            'headers' => [
                'Authorization' => "Bearer {$aadToken}",
                'Content-Type'  => 'application/json',
            ],
        ]);

        $reportData = json_decode($reportResponse->getBody()->getContents(), true);
        $datasetId = $reportData['datasetId'] ?? throw new \Exception('No datasetId found in report response');

        \Log::info('Report details retrieved', ['datasetId' => $datasetId]);

            $embedResponse = $client->post('https://api.powerbi.com/v1.0/myorg/GenerateToken', [
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
                    'lifetimeInMinutes' => 60,          // max 60 in many cases
                    'accessLevel'       => 'View',
                   
                ],
            ]);

            $embedData = json_decode($embedResponse->getBody()->getContents(), true);

            $embedUrl = "https://app.powerbi.com/reportEmbed?reportId={$reportId}&groupId={$groupId}";

            return response()->json([
                'accessToken' => $embedData['token'],
                'embedUrl'    => $embedUrl,
                'reportId'    => $reportId,
                'expires'     => $embedData['expiration'], // ISO string
            ]);

        } catch (\GuzzleHttp\Exception\RequestException $e) {
        \Log::error('Guzzle error in Power BI', [
            'message' => $e->getMessage(),
            'response' => $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null,
        ]);
        return response()->json(['error' => 'External API error', 'details' => $e->getMessage()], 500);
    } catch (\Exception $e) {
        \Log::error('General error in Power BI embed', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        return response()->json(['error' => $e->getMessage()], 500);
    }
}}