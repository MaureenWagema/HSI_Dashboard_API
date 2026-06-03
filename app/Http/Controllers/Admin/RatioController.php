<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\ClaimsRatioService;
use App\Services\ExpenseRatioService;
use App\Services\CombinedRatioService;

class RatioController extends Controller
{
    protected $claimsService;
    protected $expenseService;
    protected $combinedService;

    public function __construct(
        ClaimsRatioService $claimsService,
        ExpenseRatioService $expenseService,
        CombinedRatioService $combinedService
    ) {
        $this->claimsService = $claimsService;
        $this->expenseService = $expenseService;
        $this->combinedService = $combinedService;
    }

    // GET /api/ratios/claims
    public function claims()
    {
        $data = $this->claimsService->all();
        return response()->json($data);
    }

    // GET /api/ratios/expenses
    public function expenses()
    {
        $data = $this->expenseService->all();
        return response()->json($data);
    }

    // GET /api/ratios/combined
    public function combined()
    {
        $data = $this->combinedService->all();
        return response()->json($data);
    }

    // Optional: Fetch single record
    public function show($type, $id)
    {
        switch ($type) {
            case 'claims':
                $data = $this->claimsService->find($id);
                break;
            case 'expenses':
                $data = $this->expenseService->find($id);
                break;
            case 'combined':
                $data = $this->combinedService->find($id);
                break;
            default:
                return response()->json(['message' => 'Invalid type'], 400);
        }

        return response()->json($data);
    }

    // GET /api/ratios/claims/average
    public function claimsAverage()
    {
        $average = $this->claimsService->getAverageRatio();
        return response()->json([
            'average_claims_ratio' => round($average, 2)
        ]);
    }

    // GET /api/ratios/expenses/average
    public function expensesAverage()
    {
        $average = $this->expenseService->getAverageRatio();
        return response()->json([
            'average_expense_ratio' => round($average, 2)
        ]);
    }

    // GET /api/ratios/combined/average
    public function combinedAverage()
    {
        $average = $this->combinedService->getAverageRatio();
        return response()->json([
            'average_combined_ratio' => round($average, 2)
        ]);
    }
}
