<?php

namespace App\Http\Controllers\Admin;

use App\Models\Department;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class DepartmentController extends Controller
{
    /**
     * Get total count of departments
     */
    public function countDepartments()
    {
        $count = Department::count();
        return response()->json(['count' => $count]);
    }
}
