<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Claim;
use App\Models\Policy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ClaimsController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/claims",
     *     summary="Get all claims",
     *     tags={"Claims"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Start date (Y-m-d)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="End date (Y-m-d)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of claims"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Claim::with(['policy']);

        // Apply filters
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($startDate = $request->input('start_date')) {
            $query->where('LossDate', '>=', $startDate);
        }

        if ($endDate = $request->input('end_date')) {
            $query->where('LossDate', '<=', $endDate);
        }

        $claims = $query->orderBy('LossDate', 'desc')->paginate(15);
        return response()->json($claims);
    }

    /**
     * @OA\Get(
     *     path="/api/claims/{id}",
     *     summary="Get claim by ID",
     *     tags={"Claims"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Claim ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Claim details"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Claim not found"
     *     )
     * )
     */
    public function show($id)
    {
        $claim = Claim::with(['policy'])->findOrFail($id);
        return response()->json($claim);
    }

    /**
     * @OA\Post(
     *     path="/api/claims",
     *     summary="Create a new claim",
     *     tags={"Claims"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"PolicyID", "LossDate", "ClaimPaid"},
     *             @OA\Property(property="PolicyID", type="string", example="POL123456"),
     *             @OA\Property(property="ProductLine", type="string", example="Motor"),
     *             @OA\Property(property="AgentName", type="string", example="John Doe"),
     *             @OA\Property(property="BrokerName", type="string", example="ABC Brokerage"),
     *             @OA\Property(property="LossDate", type="string", format="date", example="2025-01-15"),
     *             @OA\Property(property="ReportedDate", type="string", format="date", example="2025-01-16"),
     *             @OA\Property(property="PaidDate", type="string", format="date", example="2025-02-01"),
     *             @OA\Property(property="ClaimPaid", type="number", format="float", example=1500.00),
     *             @OA\Property(property="RecoveriesRI", type="number", format="float", example=500.00),
     *             @OA\Property(property="SalvageSubrogation", type="number", format="float", example=200.00)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Claim created successfully"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'PolicyID' => 'required|string|exists:policies,PolicyID',
            'ProductLine' => 'required|string|max:100',
            'AgentName' => 'nullable|string|max:255',
            'BrokerName' => 'nullable|string|max:255',
            'LossDate' => 'required|date',
            'ReportedDate' => 'nullable|date|after_or_equal:LossDate',
            'PaidDate' => 'nullable|date|after_or_equal:LossDate',
            'ClaimPaid' => 'required|numeric|min:0',
            'RecoveriesRI' => 'nullable|numeric|min:0',
            'SalvageSubrogation' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        
        try {
            // Generate a unique ClaimID
            $lastClaim = Claim::orderBy('ClaimID', 'desc')->first();
            $claimId = 'CLM' . str_pad((int)substr(($lastClaim->ClaimID ?? 'CLM0'), 3) + 1, 6, '0', STR_PAD_LEFT);
            
            $claim = new Claim($validator->validated());
            $claim->ClaimID = $claimId;
            $claim->save();
            
            // Update policy claims information if needed
            $this->updatePolicyClaimsInfo($claim->PolicyID);
            
            DB::commit();
            
            return response()->json($claim, 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to create claim: ' . $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/claims/{id}",
     *     summary="Update a claim",
     *     tags={"Claims"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Claim ID"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="ProductLine", type="string", example="Motor"),
     *             @OA\Property(property="AgentName", type="string", example="John Doe"),
     *             @OA\Property(property="BrokerName", type="string", example="ABC Brokerage"),
     *             @OA\Property(property="LossDate", type="string", format="date", example="2025-01-15"),
     *             @OA\Property(property="ReportedDate", type="string", format="date", example="2025-01-16"),
     *             @OA\Property(property="PaidDate", type="string", format="date", example="2025-02-01"),
     *             @OA\Property(property="ClaimPaid", type="number", format="float", example=1500.00),
     *             @OA\Property(property="RecoveriesRI", type="number", format="float", example=500.00),
     *             @OA\Property(property="SalvageSubrogation", type="number", format="float", example=200.00)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Claim updated successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Claim not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $claim = Claim::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'ProductLine' => 'sometimes|required|string|max:100',
            'AgentName' => 'nullable|string|max:255',
            'BrokerName' => 'nullable|string|max:255',
            'LossDate' => 'sometimes|required|date',
            'ReportedDate' => 'nullable|date|after_or_equal:LossDate',
            'PaidDate' => 'nullable|date|after_or_equal:LossDate',
            'ClaimPaid' => 'sometimes|required|numeric|min:0',
            'RecoveriesRI' => 'nullable|numeric|min:0',
            'SalvageSubrogation' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        
        try {
            $originalPolicyId = $claim->PolicyID;
            
            $claim->update($validator->validated());
            
            // If PolicyID was changed, update both old and new policies
            if ($request->has('PolicyID') && $request->PolicyID !== $originalPolicyId) {
                $this->updatePolicyClaimsInfo($originalPolicyId);
                $this->updatePolicyClaimsInfo($claim->PolicyID);
            } else {
                $this->updatePolicyClaimsInfo($claim->PolicyID);
            }
            
            DB::commit();
            
            return response()->json($claim);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to update claim: ' . $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/claims/{id}",
     *     summary="Delete a claim",
     *     tags={"Claims"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Claim ID"
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Claim deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Claim not found"
     *     )
     * )
     */
    public function destroy($id)
    {
        $claim = Claim::findOrFail($id);
        $policyId = $claim->PolicyID;
        
        DB::beginTransaction();
        
        try {
            $claim->delete();
            $this->updatePolicyClaimsInfo($policyId);
            
            DB::commit();
            
            return response()->json(null, 204);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to delete claim: ' . $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/claims/summary",
     *     summary="Get claims summary",
     *     tags={"Claims"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Start date (Y-m-d)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="End date (Y-m-d)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Claims summary"
     *     )
     * )
     */
    public function summary(Request $request)
    {
        $query = Claim::query();
        
        if ($startDate = $request->input('start_date')) {
            $query->where('LossDate', '>=', $startDate);
        }
        
        if ($endDate = $request->input('end_date')) {
            $query->where('LossDate', '<=', $endDate);
        }
        
        $totalClaims = $query->count();
        $totalPaid = $query->sum('ClaimPaid');
        $totalRecovered = $query->sum(DB::raw('COALESCE(RecoveriesRI, 0) + COALESCE(SalvageSubrogation, 0)'));
        
        $byProduct = $query->clone()
            ->select('ProductLine', 
                    DB::raw('COUNT(*) as claim_count'),
                    DB::raw('SUM(ClaimPaid) as total_paid'))
            ->groupBy('ProductLine')
            ->get();
            
        $byMonth = $query->clone()
            ->select(DB::raw('YEAR(LossDate) as year'),
                    DB::raw('MONTH(LossDate) as month'),
                    DB::raw('COUNT(*) as claim_count'),
                    DB::raw('SUM(ClaimPaid) as total_paid'))
            ->groupBy(DB::raw('YEAR(LossDate), MONTH(LossDate)'))
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();
        
        return response()->json([
            'total_claims' => $totalClaims,
            'total_paid' => $totalPaid,
            'total_recovered' => $totalRecovered,
            'net_paid' => $totalPaid - $totalRecovered,
            'by_product' => $byProduct,
            'by_month' => $byMonth
        ]);
    }
    
    /**
     * Update policy claims information
     */
    protected function updatePolicyClaimsInfo($policyId)
    {
        $policy = Policy::find($policyId);
        if ($policy) {
            $claims = Claim::where('PolicyID', $policyId)->get();
            
            $policy->update([
                'TotalClaims' => $claims->count(),
                'TotalClaimAmount' => $claims->sum('ClaimPaid'),
                'LastClaimDate' => $claims->max('LossDate')
            ]);
        }
    }
    
    /**
     * Get claims totals (legacy method)
     */
    public function getClaimsTotals(Request $request)
    {
        // Optional filters
        $year = $request->input('year');   // e.g., 2025
        $month = $request->input('month'); // e.g., 3

        $query = DB::table('glmasterinfo')
            ->select(
                'account_year',
                'account_month',
                DB::raw('SUM(monthly_debit - monthly_credit) as claims_paid')
            )
            ->whereIn('displayAccountNo', [
                '070-08-001','070-08-002','070-08-003','070-08-004','070-08-005','070-08-006',
                '070-09-001','070-09-002','070-09-003','070-09-004','070-09-005','070-09-006',
                '070-07-002'
            ]);

        // Apply filters if provided
        if ($year) {
            $query->where('account_year', $year);
        }

        if ($month) {
            $query->where('account_month', $month);
        }

        // Group by year & month for monthly totals
        $monthlyTotals = $query->clone()
            ->groupBy('account_year', 'account_month')
            ->orderBy('account_year')
            ->orderBy('account_month')
            ->get();

        // Yearly totals
        $yearlyTotals = $query->clone()
            ->groupBy('account_year')
            ->select(
                'account_year',
                DB::raw('SUM(monthly_debit - monthly_credit) as claims_paid')
            )
            ->get();

        // General total
        $generalTotal = $query->clone()
            ->select(DB::raw('SUM(monthly_debit - monthly_credit) as claims_paid'))
            ->first();

        return response()->json([
            'monthly_totals' => $monthlyTotals,
            'yearly_totals'  => $yearlyTotals,
            'general_total'  => $generalTotal->claims_paid ?? 0,
        ]);
    }
}
