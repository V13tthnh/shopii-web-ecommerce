<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Hash;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request){
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'nullable|string'
        ]);

        $user = User::where('email', $request->email)->first();

        if(!$user || !Hash::check($request->password, $user->password)){
            throw ValidationException::withMessages([
                'message' => ['Thông tin đăng nhập không chính xác.']
            ]);
        }

        if(!$user->hasRole('admin')){
            return response()->json([
                'message' => 'Bạn không có quyền truy cập vào hệ thống.'
            ], 403);
        }

        $deviceName = $request->device_name ?? $request->ip();
        $token = $user->createToken($deviceName, ['admin'])->plainTextToken;

        return response()->json([
            'isLoggedIn' => true,
            'user' => $user->load('roles', 'permissions'),
            'token' => $token,
            'token_type' => "Bearer"
        ]);
    }

    public function me(Request $request){
        return response()->json([
            'user' => $request->user()->load('roles', 'permissions')
        ]);
    }

    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Đăng xuất thành công.'
        ]);
    }
}
