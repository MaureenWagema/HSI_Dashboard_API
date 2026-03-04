<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class AuthController extends Controller
{
    
    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        try {
            $tokenResult = $user->createToken('API Token');
            $token       = $tokenResult->accessToken;
        } catch (\Exception $e) {
            Log::error('Token generation failed during registration: ' . $e->getMessage());

            return response()->json([
                'isOk'    => false,
                'message' => 'User created but token generation failed',
                'error'   => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'isOk'    => true,
            'message' => 'User registered successfully',
            'user'    => $user,
            'token'   => $token,
        ], 201);
    }

    
    public function login(Request $request)
    {
        $request->validate([
            'login'    => 'required|string',
            'password' => 'required',
        ]);

        $loginField = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';
        
        $user = User::where($loginField, $request->login)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'isOk'    => false,
                'message' => 'Invalid login credentials',
            ], 401);
        }

        if (!$user->is_superadmin) {
            return response()->json([
                'isOk'    => false,
                'message' => 'User is not a superadmin',
            ], 403);
        }

        $user->last_login_at = now();
        $user->save();

        try {
            $tokenResult = $user->createToken('API Token');
            $token       = $tokenResult->accessToken;
        } catch (\Exception $e) {
            Log::error('Token generation failed: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'isOk'    => false,
                'message' => 'Could not generate token',
                'error'   => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'isOk'    => true,
            'message' => 'Login successful',
            'user'    => $user,
            'token'   => $token,
        ], 200);
    }

    
    public function user(Request $request)
    {
        $authUser = $request->user();

        if (!$authUser) {
            return response()->json([
                'isOk'    => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        return response()->json([
            'isOk' => true,
            'user' => $authUser,
        ], 200);
    }

   
    public function logout(Request $request)
    {
        try {
            $request->user()->token()->revoke();

            return response()->json([
                'isOk'    => true,
                'message' => 'Logged out successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'isOk'    => false,
                'message' => 'Logout failed',
            ], 500);
        }
    }
}