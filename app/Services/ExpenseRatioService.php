<?php

namespace App\Services;

use App\Models\ExpenseRatio;

class ExpenseRatioService
{
    public function all()
    {
        return ExpenseRatio::select('id', 'month', 'underwriting_expenses', 'written_premiums')
            ->get()
            ->map(function($item) {
                $item->expense_ratio = $item->written_premiums > 0 
                    ? ($item->underwriting_expenses / $item->written_premiums) *100
                    : 0;
                return $item;
            });
    }

    public function find($id)
    {
        return ExpenseRatio::findOrFail($id);
    }

    public function getAverageRatio()
    {
        $data = $this->all();
        $count = $data->count();
        
        if ($count === 0) {
            return 0;
        }
        
        $total = $data->sum('expense_ratio');
        return $total / $count;
    }
}
