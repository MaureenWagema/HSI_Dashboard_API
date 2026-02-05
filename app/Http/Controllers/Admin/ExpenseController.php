<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ExpenseManagementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ExpenseController extends Controller
{
    protected $expenseService;

    public function __construct(ExpenseManagementService $expenseService)
    {
        $this->expenseService = $expenseService;
        $this->middleware('auth:api');
    }

    /**
     * @OA\Get(
     *     path="/api/expenses",
     *     summary="Get all expenses",
     *     tags={"Expenses"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="Filter by category",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pending", "approved", "rejected"})
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
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of expenses",
     *         @OA\JsonContent(type="object")
     *     )
     * )
     */
    public function index(Request $request)
    {
        $filters = $request->only(['category', 'status', 'start_date', 'end_date', 'per_page']);
        $expenses = $this->expenseService->getAllExpenses($filters);
        return response()->json($expenses);
    }

    /**
     * @OA\Get(
     *     path="/api/expenses/{id}",
     *     summary="Get expense by ID",
     *     tags={"Expenses"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Expense ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Expense details",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Expense not found"
     *     )
     * )
     */
    public function show($id)
    {
        $expense = $this->expenseService->getExpenseById($id);
        return response()->json($expense);
    }

    /**
     * @OA\Post(
     *     path="/api/expenses",
     *     summary="Create a new expense",
     *     tags={"Expenses"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"account_number", "description", "amount", "expense_date", "category"},
     *             @OA\Property(property="account_number", type="string", example="123-45-678"),
     *             @OA\Property(property="description", type="string", example="Office supplies"),
     *             @OA\Property(property="amount", type="number", format="float", example=150.75),
     *             @OA\Property(property="expense_date", type="string", format="date", example="2025-01-15"),
     *             @OA\Property(property="category", type="string", example="Office Expenses")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Expense created successfully"
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
            'account_number' => 'required|string|max:20',
            'description' => 'required|string|max:1000',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'category' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $expense = $this->expenseService->createExpense($validator->validated());
        return response()->json($expense, 201);
    }

    /**
     * @OA\Put(
     *     path="/api/expenses/{id}",
     *     summary="Update an existing expense",
     *     tags={"Expenses"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Expense ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="description", type="string", example="Updated description"),
     *             @OA\Property(property="amount", type="number", format="float", example=200.00),
     *             @OA\Property(property="expense_date", type="string", format="date", example="2025-01-20"),
     *             @OA\Property(property="category", type="string", example="Updated Category")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Expense updated successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Expense not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'sometimes|required|string|max:1000',
            'amount' => 'sometimes|required|numeric|min:0.01',
            'expense_date' => 'sometimes|required|date',
            'category' => 'sometimes|required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $expense = $this->expenseService->updateExpense($id, $validator->validated());
        return response()->json($expense);
    }

    /**
     * @OA\Delete(
     *     path="/api/expenses/{id}",
     *     summary="Delete an expense",
     *     tags={"Expenses"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Expense ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Expense deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Expense not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Cannot delete approved expense"
     *     )
     * )
     */
    public function destroy($id)
    {
        try {
            $this->expenseService->deleteExpense($id);
            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/expenses/{id}/approve",
     *     summary="Approve an expense",
     *     tags={"Expenses"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Expense ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Expense approved successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Expense not found"
     *     )
     * )
     */
    public function approve($id)
    {
        $expense = $this->expenseService->approveExpense($id, auth()->id());
        return response()->json($expense);
    }

    /**
     * @OA\Post(
     *     path="/api/expenses/{id}/reject",
     *     summary="Reject an expense",
     *     tags={"Expenses"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Expense ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"reason"},
     *             @OA\Property(property="reason", type="string", example="Insufficient documentation")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Expense rejected successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Expense not found"
     *     )
     * )
     */
    public function reject(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string|max:1000']);
        $expense = $this->expenseService->rejectExpense($id, $request->reason);
        return response()->json($expense);
    }

    /**
     * @OA\Get(
     *     path="/api/expenses/summary",
     *     summary="Get expense summary",
     *     tags={"Expenses"},
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
     *         description="Expense summary",
     *         @OA\JsonContent(type="object")
     *     )
     * )
     */
    public function summary(Request $request)
    {
        $summary = $this->expenseService->getExpenseSummary(
            $request->input('start_date'),
            $request->input('end_date')
        );
        
        return response()->json($summary);
    }
}
