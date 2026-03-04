<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\NEPService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class NEPController extends Controller
{
    protected $nepService;

    public function __construct(NEPService $nepService)
    {
        $this->nepService = $nepService;
        $this->middleware('auth:api');
    }

  
    public function index(Request $request)
    {
        // Since NEP is calculated data from glmasterinfo, not a table, 
        // we'll return empty response for now
        $items = [
            'data' => [],
            'current_page' => 1,
            'per_page' => 15,
            'total' => 0
        ];

        return response()->json($items);
    }

    
    public function show($id)
    {
        // NEP data is calculated from glmasterinfo, not a table
        // Return empty response for now
        return response()->json(['message' => 'NEP data is calculated, not stored as individual records']);
    }

    
    public function store(Request $request)
    {
        // NEP data is calculated from glmasterinfo, not stored as individual records
        return response()->json(['message' => 'NEP data is calculated from financial data, not manually created'], 405);
    }

    
    public function update(Request $request, $id)
    {
        // NEP data is calculated from glmasterinfo, not stored as individual records
        return response()->json(['message' => 'NEP data is calculated from financial data, not manually updated'], 405);
    }
    
    
    public function destroy($id)
    {
        // NEP data is calculated from glmasterinfo, not stored as individual records
        return response()->json(['message' => 'NEP data is calculated from financial data, not manually deleted'], 405);
    }
    private function generateAssetNumber()
    {
        return 'NEP-' . date('Y') . '-' . str_pad(DB::table('nep')->count() + 1, 4, '0', STR_PAD_LEFT);
    }

    public function getNEPSummary(Request $request)
    {
        $year = $request->input('year');   
        $month = $request->input('month'); 

        $nepData = $this->nepService->getNEP($year, $month);

        $monthlyData = $nepData['monthly'] ?? [];
        $yearlyData = $nepData['yearly'] ?? [];
        
        $totalGross = 0;
        $totalReinsurance = 0;
        $totalNEP = 0;
        
        $formattedMonthlyData = [];
        
        if (is_array($monthlyData)) {
            foreach ($monthlyData as $item) {
                $gross = $item['gross_premium'] ?? 0;
                $reinsurance = $item['reinsurance_ceded'] ?? 0;
                $nep = $gross - $reinsurance;
                
                $totalGross += $gross;
                $totalReinsurance += $reinsurance;
                $totalNEP += $nep;
                
                $formattedMonthlyData[] = [
                    'year' => $item['account_year'] ?? 0,
                    'month' => $item['account_month'] ?? 0,
                    'month_name' => $item['account_month'] ? date('F', mktime(0, 0, 0, $item['account_month'], 1)) : '',
                    'gross_premium' => $gross,
                    'reinsurance_ceded' => $reinsurance,
                    'nep' => $nep
                ];
            }
        }

        $nepStats = [
            'total_items' => 0,
            'total_original_cost' => 0,
            'total_current_value' => 0,
            'by_category' => [],
            'by_status' => []
        ];

        $response = [
            'period' => [
                'year' => $year,
                'month' => $month ?: 'All'
            ],
            'totals' => [
                'gross_premium' => $totalGross,
                'reinsurance_ceded' => $totalReinsurance,
                'nep' => $totalNEP
            ],
            'monthly_data' => $formattedMonthlyData,
            'yearly_summary' => $yearlyData,
            'nep_statistics' => $nepStats
        ];

        return response()->json($response);
    }
}
