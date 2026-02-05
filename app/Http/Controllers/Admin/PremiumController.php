<?php

namespace App\Http\Controllers\Admin;

use App\Models\Premium;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PremiumController extends Controller
{
    public function index()
    {
        $premiums = Premium::with('pdepartment')->get();
        return response()->json($premiums);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'department_name' => 'required|exists:pdepartment,department_name',
            'month' => 'required',
            'year' => 'required',
            'annual_budget' => 'required|numeric',
            'premium_actuals' => 'required|numeric',
            'erm_loading' => 'required|numeric',
            'vat_amount' => 'required|numeric',
        ]);

        $premium = Premium::create($validated);

        // Return the newly created premium with its related department info
        return response()->json($premium->load('pdepartment'), 201);
    }

    public function show($id)
    {
        $premium = Premium::with('pdepartment')->findOrFail($id);
        return response()->json($premium);
    }

    public function update(Request $request, $id)
    {
        $premium = Premium::findOrFail($id);
        $premium->update($request->all());

        return response()->json($premium->load('pdepartment'));
    }

    public function destroy($id)
    {
        Premium::findOrFail($id)->delete();
        return response()->json(['message' => 'Premium deleted']);
    }

    public function getYtd(Request $request)
    {
        $validated = $request->validate([
            'year' => 'required|integer',
            'month' => 'required|integer|min:1|max:12',
            'department_name' => 'nullable|exists:pdepartment,department_name',
        ]);

        $year = $validated['year'];
        $month = $validated['month'];
        $departmentName = $validated['department_name'] ?? null;

        // Build query for YTD calculation (from January to the specified month)
        $query = Premium::where('year', $year)
            ->where('month', '<=', $month);

        if ($departmentName) {
            $query->where('department_name', $departmentName);
        }

        // Calculate YTD totals
        $ytdData = $query->selectRaw('
            SUM(annual_budget) as ytd_annual_budget,
            SUM(premium_actuals) as ytd_premium_actuals,
            SUM(erm_loading) as ytd_erm_loading,
            SUM(vat_amount) as ytd_vat_amount,
            COUNT(*) as record_count
        ')->first();

        // Get monthly breakdown
        $monthlyBreakdown = Premium::where('year', $year)
            ->where('month', '<=', $month)
            ->when($departmentName, function ($q) use ($departmentName) {
                return $q->where('department_name', $departmentName);
            })
            ->with('pdepartment')
            ->orderBy('month')
            ->get();

        return response()->json([
            'year' => $year,
            'month' => $month,
            'department_name' => $departmentName,
            'ytd_totals' => [
                'annual_budget' => $ytdData->ytd_annual_budget ?? 0,
                'premium_actuals' => $ytdData->ytd_premium_actuals ?? 0,
                'erm_loading' => $ytdData->ytd_erm_loading ?? 0,
                'vat_amount' => $ytdData->ytd_vat_amount ?? 0,
                'record_count' => $ytdData->record_count ?? 0,
            ],
            'monthly_breakdown' => $monthlyBreakdown,
        ]);
    }
}
