<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NEP;
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
        $query = NEP::query();

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }

        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%");
            });
        }

        $items = $query->orderBy('purchase_date', 'desc')
                      ->paginate($request->input('per_page', 15));

        return response()->json($items);
    }

    
    public function show($id)
    {
        $item = NEP::with(['assignedTo', 'location', 'depreciation'])->findOrFail($id);
        return response()->json($item);
    }

    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|max:100',
            'serial_number' => 'required|string|max:100|unique:nep,serial_number',
            'model_number' => 'nullable|string|max:100',
            'purchase_date' => 'required|date',
            'purchase_cost' => 'required|numeric|min:0',
            'useful_life_years' => 'required|integer|min:1|max:100',
            'salvage_value' => 'nullable|numeric|min:0',
            'location_id' => 'required|exists:locations,id',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'required|in:active,disposed,transferred',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        
        try {
            $data = $validator->validated();
            $data['asset_number'] = $this->generateAssetNumber();
            $data['current_value'] = $data['purchase_cost'];
            
            $nep = NEP::create($data);
            
            // Create initial depreciation record
            $nep->depreciation()->create([
                'depreciation_date' => $data['purchase_date'],
                'amount' => 0,
                'current_value' => $data['purchase_cost'],
                'notes' => 'Initial purchase'
            ]);
            
            DB::commit();
            
            return response()->json($nep, 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to create NEP item: ' . $e->getMessage()], 500);
        }
    }

    
    public function update(Request $request, $id)
    {
        $nep = NEP::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'sometimes|required|string|max:100',
            'serial_number' => 'sometimes|required|string|max:100|unique:nep,serial_number,' . $id,
            'model_number' => 'nullable|string|max:100',
            'purchase_date' => 'sometimes|required|date',
            'purchase_cost' => 'sometimes|required|numeric|min:0',
            'useful_life_years' => 'sometimes|required|integer|min:1|max:100',
            'salvage_value' => 'nullable|numeric|min:0',
            'location_id' => 'sometimes|required|exists:locations,id',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'sometimes|required|in:active,disposed,transferred',
            'notes' => 'nullable|string',
            'disposal_date' => 'nullable|date|after_or_equal:purchase_date',
            'disposal_amount' => 'nullable|numeric|min:0',
            'disposal_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        
        try {
            $data = $validator->validated();
            
            if (isset($data['status']) && $data['status'] === 'disposed' && !isset($data['disposal_date'])) {
                $data['disposal_date'] = now()->format('Y-m-d');
            }
            
            $nep->update($data);
            
            if (isset($data['status']) || isset($data['assigned_to']) || isset($data['location_id'])) {
                $this->logNEPChange($nep, [
                    'status' => $data['status'] ?? null,
                    'assigned_to' => $data['assigned_to'] ?? null,
                    'location_id' => $data['location_id'] ?? null,
                    'notes' => $data['notes'] ?? null
                ]);
            }
            
            DB::commit();
            
            return response()->json($nep);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to update NEP item: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $nep = NEP::findOrFail($id);
        
        DB::beginTransaction();
        
        try {
            // Log the deletion
            \App\Models\NEPAuditLog::create([
                'nep_id' => $nep->id,
                'action' => 'deleted',
                'changed_by' => auth()->id(),
                'old_values' => $nep->toArray(),
                'new_values' => null,
                'notes' => 'NEP item deleted'
            ]);
            
            $nep->delete();
            
            DB::commit();
            
            return response()->json(null, 204);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to delete NEP item: ' . $e->getMessage()], 500);
        }
    }

    public function recordDepreciation(Request $request, $id)
    {
        $nep = NEP::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'depreciation_date' => 'required|date|after_or_equal:' . $nep->purchase_date,
            'amount' => 'required|numeric|min:0.01|max:' . ($nep->current_value - $nep->salvage_value),
            'current_value' => 'required|numeric|min:' . $nep->salvage_value . '|max:' . $nep->current_value,
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        
        try {
            $data = $validator->validated();
            
            $depreciation = $nep->depreciation()->create([
                'depreciation_date' => $data['depreciation_date'],
                'amount' => $data['amount'],
                'current_value' => $data['current_value'],
                'notes' => $data['notes'] ?? null,
                'recorded_by' => auth()->id()
            ]);
            
            $nep->update([
                'current_value' => $data['current_value'],
                'last_depreciation_date' => $data['depreciation_date']
            ]);
            
            DB::commit();
            
            return response()->json($depreciation);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to record depreciation: ' . $e->getMessage()], 500);
        }
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
            'total_items' => NEP::count(),
            'total_original_cost' => NEP::sum('purchase_cost'),
            'total_current_value' => NEP::sum('current_value'),
            'by_category' => NEP::select('category', 
                                       DB::raw('count(*) as count'),
                                       DB::raw('sum(purchase_cost) as total_cost'),
                                       DB::raw('sum(current_value) as current_value'))
                               ->groupBy('category')
                               ->get(),
            'by_status' => NEP::select('status', 
                                     DB::raw('count(*) as count'),
                                     DB::raw('sum(purchase_cost) as total_cost'))
                             ->groupBy('status')
                             ->get()
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

    protected function generateAssetNumber()
    {
        $prefix = 'AST' . date('y');
        $lastAsset = NEP::where('asset_number', 'like', $prefix . '%')
                       ->orderBy('asset_number', 'desc')
                       ->first();
        
        if ($lastAsset) {
            $lastNumber = (int)substr($lastAsset->asset_number, 5);
            return $prefix . str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        }
        
        return $prefix . '00001';
    }
    
    
    protected function logNEPChange($nep, $changes)
    {
        $oldValues = [];
        $newValues = [];
        
        foreach ($changes as $field => $newValue) {
            if ($nep->$field != $newValue) {
                $oldValues[$field] = $nep->$field;
                $newValues[$field] = $newValue;
            }
        }
        
        if (!empty($newValues)) {
            \App\Models\NEPAuditLog::create([
                'nep_id' => $nep->id,
                'action' => 'updated',
                'changed_by' => auth()->id(),
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'notes' => $changes['notes'] ?? null
            ]);
        }
    }
}
