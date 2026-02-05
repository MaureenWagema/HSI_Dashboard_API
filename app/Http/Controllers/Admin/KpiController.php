<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\KpiItem;
use App\Models\Employee;
use App\Models\KpiScore;
use App\Models\EmployeeSummary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class KpiController extends Controller
{
    
    public function getDepartments()
    {
        try {
            $departments = Department::all();
            return response()->json([
                'success' => true,
                'data' => $departments
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch departments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

   
    public function getDepartment($id)
    {
        try {
            $department = Department::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $department
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Department not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

  
    public function createDepartment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'Department' => 'required|string|max:100|unique:sqlsrv.department,Department',
            'CostCentre' => 'required|string|max:50',
            'HeadOfDepartment' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $department = Department::create($request->all());
            return response()->json([
                'success' => true,
                'message' => 'Department created successfully',
                'data' => $department
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create department',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    
    public function updateDepartment(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'Department' => 'sometimes|string|max:100|unique:sqlsrv.department,Department,' . $id . ',DepartmentID',
            'CostCentre' => 'sometimes|string|max:50',
            'HeadOfDepartment' => 'sometimes|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $department = Department::findOrFail($id);
            $department->update($request->all());
            return response()->json([
                'success' => true,
                'message' => 'Department updated successfully',
                'data' => $department
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update department',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    
    public function deleteDepartment($id)
    {
        try {
            $department = Department::findOrFail($id);
            $department->delete();
            return response()->json([
                'success' => true,
                'message' => 'Department deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete department',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ==================== KPI ITEMS METHODS ====================
    
    /**
     * Get all KPI items
     */
    public function getKpiItems(Request $request)
    {
        try {
            $query = KpiItem::with('department');
            
            if ($request->has('department')) {
                $query->where('Department', $request->department);
            }
            
            $kpiItems = $query->get();
            return response()->json([
                'success' => true,
                'data' => $kpiItems
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch KPI items',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getKpiItem($id)
    {
        try {
            $kpiItem = KpiItem::with('department')->findOrFail($id);
            return response()->json(['success' => true, 'data' => $kpiItem], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'KPI item not found'], 404);
        }
    }

    public function createKpiItem(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'Department' => 'required|string|max:100|exists:sqlsrv.department,Department',
            'KPIItem' => 'required|string|max:100',
            'Weight' => 'required|integer|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $kpiItem = KpiItem::create($request->all());
            return response()->json(['success' => true, 'data' => $kpiItem], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function updateKpiItem(Request $request, $id)
    {
        try {
            $kpiItem = KpiItem::findOrFail($id);
            $kpiItem->update($request->all());
            return response()->json(['success' => true, 'data' => $kpiItem], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function deleteKpiItem($id)
    {
        try {
            KpiItem::findOrFail($id)->delete();
            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // ==================== EMPLOYEES METHODS ====================
    
    public function getEmployees(Request $request)
    {
        try {
            $query = Employee::with('department');
            if ($request->has('department')) {
                $query->where('Department', $request->department);
            }
            return response()->json(['success' => true, 'data' => $query->get()], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function getEmployee($id)
    {
        try {
            $employee = Employee::with(['department', 'kpiScores'])->findOrFail($id);
            return response()->json(['success' => true, 'data' => $employee], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 404);
        }
    }

    public function createEmployee(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'EmployeeID' => 'required|integer|unique:sqlsrv.employees,EmployeeID',
            'EmployeeName' => 'required|string|max:100',
            'Department' => 'required|string|exists:sqlsrv.department,Department',
            'JobTitle' => 'required|string|max:100',
            'Email' => 'required|email|unique:sqlsrv.employees,Email',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            return response()->json(['success' => true, 'data' => Employee::create($request->all())], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function updateEmployee(Request $request, $id)
    {
        try {
            $employee = Employee::findOrFail($id);
            $employee->update($request->all());
            return response()->json(['success' => true, 'data' => $employee], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function deleteEmployee($id)
    {
        try {
            Employee::findOrFail($id)->delete();
            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // ==================== KPI SCORES METHODS ====================
    
    public function getKpiScores(Request $request)
    {
        try {
            $query = KpiScore::with(['employee', 'department']);
            
            if ($request->has('employee_id')) {
                $query->where('EmployeeID', $request->employee_id);
            }
            if ($request->has('period')) {
                $query->where('Period', $request->period);
            }
            
            return response()->json(['success' => true, 'data' => $query->get()], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function getKpiScore($id)
    {
        try {
            return response()->json(['success' => true, 'data' => KpiScore::findOrFail($id)], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 404);
        }
    }

    public function createKpiScore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'EmployeeID' => 'required|exists:sqlsrv.employees,EmployeeID',
            'Department' => 'required|exists:sqlsrv.department,Department',
            'KPIItem' => 'required|string',
            'Weight' => 'required|integer',
            'Score' => 'required|integer',
            'WeightedScore' => 'required|numeric',
            'Period' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            return response()->json(['success' => true, 'data' => KpiScore::create($request->all())], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function updateKpiScore(Request $request, $id)
    {
        try {
            $score = KpiScore::findOrFail($id);
            $score->update($request->all());
            return response()->json(['success' => true, 'data' => $score], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function deleteKpiScore($id)
    {
        try {
            KpiScore::findOrFail($id)->delete();
            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // ==================== EMPLOYEE SUMMARIES METHODS ====================
    
    public function getEmployeeSummaries(Request $request)
    {
        try {
            $query = EmployeeSummary::with(['employee', 'department']);
            
            if ($request->has('period')) {
                $query->where('Period', $request->period);
            }
            
            return response()->json(['success' => true, 'data' => $query->get()], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function getEmployeeSummary($id)
    {
        try {
            return response()->json(['success' => true, 'data' => EmployeeSummary::findOrFail($id)], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 404);
        }
    }

    public function createEmployeeSummary(Request $request)
    {
        try {
            return response()->json(['success' => true, 'data' => EmployeeSummary::create($request->all())], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function updateEmployeeSummary(Request $request, $id)
    {
        try {
            $summary = EmployeeSummary::findOrFail($id);
            $summary->update($request->all());
            return response()->json(['success' => true, 'data' => $summary], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function deleteEmployeeSummary($id)
    {
        try {
            EmployeeSummary::findOrFail($id)->delete();
            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // ==================== UTILITY METHODS ====================
    
    public function calculateEmployeeSummary(Request $request)
    {
        try {
            $employeeId = $request->employee_id;
            $period = $request->period;

            $kpiScores = KpiScore::where('EmployeeID', $employeeId)
                ->where('Period', $period)
                ->get();

            if ($kpiScores->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'No KPI scores found'], 404);
            }

            $totalWeightedScore = $kpiScores->sum('WeightedScore');
            $employee = Employee::findOrFail($employeeId);

            $summary = EmployeeSummary::updateOrCreate(
                ['EmployeeID' => $employeeId, 'Period' => $period],
                [
                    'EmployeeName' => $employee->EmployeeName,
                    'Department' => $employee->Department,
                    'TotalWeightedScore' => $totalWeightedScore
                ]
            );

            return response()->json(['success' => true, 'data' => $summary], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function getDashboardData(Request $request)
    {
        try {
            $period = $request->input('period');
            $department = $request->input('department');

            $query = EmployeeSummary::with(['employee', 'department']);

            if ($period) {
                $query->where('Period', $period);
            }
            if ($department) {
                $query->where('Department', $department);
            }

            $summaries = $query->orderBy('TotalWeightedScore', 'desc')->get();

            $departmentStats = EmployeeSummary::select('Department', DB::raw('AVG(TotalWeightedScore) as avg_score'))
                ->when($period, fn($q) => $q->where('Period', $period))
                ->groupBy('Department')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'employee_summaries' => $summaries,
                    'department_statistics' => $departmentStats
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
