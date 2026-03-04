<?php

//command to run php artisan sync:actual-premiums

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BudgetController extends Controller
{
    
    public function index()
    {
        $budgets = Budget::on('sqlsrv')->orderBy('year', 'desc')->orderBy('month', 'desc')->get();
        return response()->json($budgets);
    }

   
    public function create()
    {
        //
    }

    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:2100',
            'budget' => 'required|numeric|min:0',
            'month' => [
                'required',
                'integer',
                'min:1',
                'max:12',
                Rule::unique('budgets')->where(function ($query) use ($request) {
                    return $query->where('year', $request->year);
                })
            ]
        ]);

        $budget = Budget::on('sqlsrv')->create($validated);
        return response()->json($budget, 201);
    }

    
    public function show(string $id)
    {
        $budget = Budget::on('sqlsrv')->findOrFail($id);
        return response()->json($budget);
    }

    public function edit(string $id)
    {
    }

   
    public function update(Request $request, string $id)
    {
        $budget = Budget::on('sqlsrv')->findOrFail($id);
        
        $validated = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:2100',
            'budget' => 'required|numeric|min:0',
            'month' => [
                'required',
                'integer',
                'min:1',
                'max:12',
                Rule::unique('budgets')->where(function ($query) use ($request) {
                    return $query->where('year', $request->year);
                })->ignore($budget->id)
            ]
        ]);

        $budget->update($validated);
        return response()->json($budget);
    }

   
    public function destroy(string $id)
    {
        $budget = Budget::on('sqlsrv')->findOrFail($id);
        $budget->delete();
        return response()->json(null, 204);
    }
}
