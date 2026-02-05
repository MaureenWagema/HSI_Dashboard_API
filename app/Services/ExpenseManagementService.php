<?php

namespace App\Services;

use App\Models\Expense;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ExpenseManagementService
{
    public function getAllExpenses($filters = [])
    {
        $query = Expense::query();

        // Apply filters
        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }
        
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (!empty($filters['start_date'])) {
            $query->where('expense_date', '>=', $filters['start_date']);
        }
        
        if (!empty($filters['end_date'])) {
            $query->where('expense_date', '<=', $filters['end_date']);
        }

        return $query->orderBy('expense_date', 'desc')
                    ->with(['createdBy', 'approvedBy'])
                    ->paginate($filters['per_page'] ?? 15);
    }

    public function getExpenseById($id)
    {
        return Expense::with(['createdBy', 'approvedBy'])->findOrFail($id);
    }

    public function createExpense(array $data)
    {
        DB::beginTransaction();
        
        try {
            $expense = new Expense($data);
            $expense->created_by = Auth::id();
            
            // Auto-approve if amount is below threshold
            if ($data['amount'] <= config('expenses.auto_approve_threshold', 1000)) {
                $expense->status = 'approved';
                $expense->approved_by = Auth::id();
                $expense->approved_at = now();
            } else {
                $expense->status = 'pending';
            }
            
            $expense->save();
            
            DB::commit();
            return $expense;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating expense: ' . $e->getMessage());
            throw $e;
        }
    }

    public function updateExpense($id, array $data)
    {
        $expense = Expense::findOrFail($id);
        
        DB::beginTransaction();
        
        try {
            // Only allow updating certain fields
            $updatable = ['description', 'amount', 'expense_date', 'category'];
            $updateData = array_intersect_key($data, array_flip($updatable));
            
            // If amount is being updated, we might need to re-approve
            if (isset($data['amount']) && $data['amount'] > $expense->amount) {
                $expense->status = 'pending';
                $expense->approved_by = null;
                $expense->approved_at = null;
            }
            
            $expense->update($updateData);
            
            DB::commit();
            return $expense->fresh();
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error updating expense {$id}: " . $e->getMessage());
            throw $e;
        }
    }

    public function deleteExpense($id)
    {
        $expense = Expense::findOrFail($id);
        
        // Add any business rules for deletion
        if ($expense->status === 'approved') {
            throw new \Exception('Cannot delete an approved expense');
        }
        
        return $expense->delete();
    }

    public function approveExpense($id, $approverId)
    {
        $expense = Expense::findOrFail($id);
        
        $expense->update([
            'status' => 'approved',
            'approved_by' => $approverId,
            'approved_at' => now()
        ]);
        
        return $expense;
    }

    public function rejectExpense($id, $reason)
    {
        $expense = Expense::findOrFail($id);
        
        $expense->update([
            'status' => 'rejected',
            'rejection_reason' => $reason
        ]);
        
        return $expense;
    }

    public function getExpenseSummary($startDate = null, $endDate = null)
    {
        $query = Expense::query();
        
        if ($startDate) {
            $query->where('expense_date', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('expense_date', '<=', $endDate);
        }
        
        return [
            'total' => $query->sum('amount'),
            'count' => $query->count(),
            'by_category' => $query->select('category', DB::raw('SUM(amount) as total'))
                                 ->groupBy('category')
                                 ->pluck('total', 'category')
        ];
    }
}
