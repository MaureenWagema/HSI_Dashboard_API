<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
  
    public function index()
    {
        // $users = User::with('department')->get();
        // return response()->json($users);
        $users = User::all()->makeHidden(['is_active']);
        return response()->json($users);
    
    
    
    }public function countUsers()
    {
        $count = User::count();
        return response()->json(['count' => $count]);
    }



    // public function index()
    // {
    //     $users = User::all()->map(function ($user) {
    //         return [
    //             'id' => $user->id,
    //             'name' => $user->name,
    //             'email' => $user->email,
    //             'department' => $user->department,
    //             'status' => $user->status, // accessor applied
    //         ];
    //     });

    //     return response()->json($users);
    // }

    
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name'          => 'required|string|max:255',
                'email'         => 'required|email|max:255|unique:sqlsrv.user,email',
                'password'      => 'required|string|min:4|confirmed',
                'is_superadmin' => 'required|boolean',
                'department'    => 'required|string|exists:sqlsrv.departments,department_name',
            ]);

            $user = User::create([
                'name'              => $validated['name'],
                'email'             => $validated['email'],
                'password'          => Hash::make($validated['password']),
                'is_superadmin'     => $validated['is_superadmin'],
                'is_active'         => true,
                'department'        => $validated['department'],
                'last_login_at'     => null,
                'last_logout_at'    => null,
                'password_changed_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data'    => $user
            ], 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

   
    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);
        $user->is_active = !$user->is_active;
        $user->save();

        return response()->json([
            'message' => 'User status updated successfully',
            'user'    => $user->fresh()
        ]);
    }


   
    public function changePassword(Request $request, $id)
    {
        try {
            $request->validate([
                'password' => 'required|string|min:5|confirmed'
            ]);

            $user = User::findOrFail($id);
            $user->password = Hash::make($request->password);
            $user->password_changed_at = now();
            $user->save();

            return response()->json([
                'message' => 'Password updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
    


    public function deleteUsers($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully'
        ], 200);
    }
}
