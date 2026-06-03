<?php

namespace App\Services;

use App\Models\CombinedRatio;

class CombinedRatioService
{
    public function all()
    {
        return CombinedRatio::select('id', 'month', 'claims_ratio', 'expense_ratio', 'combined_ratio')->get();
    }

    public function find($id)
    {
        return CombinedRatio::findOrFail($id);
    }

    public function getAverageRatio()
    {
        $data = $this->all();
        $count = $data->count();
        
        if ($count === 0) {
            return 0;
        }
        
        $total = $data->sum('combined_ratio');
        return $total / $count;
    }
}
