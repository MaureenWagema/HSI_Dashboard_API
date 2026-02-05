<?php

namespace App\Http\Controllers\Admin;

use App\Models\Pdepartment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PdepartmentController extends Controller
{
    public function index()
    {
        return response()->json(Pdepartment::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'department_name' => 'required|string|unique:pdepartment,department_name',
        ]);

        $department = Pdepartment::create($validated);

        return response()->json($department, 201);
    }

    public function show($id)
    {
        return response()->json(Pdepartment::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $department = Pdepartment::findOrFail($id);
        $department->update($request->all());

        return response()->json($department);
    }

    public function destroy($id)
    {
        Pdepartment::findOrFail($id)->delete();
        return response()->json(['message' => 'Department deleted']);
    }
}
