<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Hash;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        $admins = User::role('admin')->with('roles', 'permissions')->get();

        return response()->json([
            'admins' => $admins
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole('admin');

        if ($request->has('roles')) {
            $user->syncRoles(array_merge(['admin'], $request->roles));
        }

        return response()->json([
            'message' => 'Thêm mới thành công.',
            'admin' => $user->load('roles', 'permissions')
        ], 201);
    }

    public function show($id)
    {
        $admin = User::role('admin')->with('roles', 'permissions')->findOrFail($id);

        return response()->json([
            'admin' => $admin
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id'
        ]);

        $user = User::findOrFail($id);

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $user->update($userData);

        if ($request->has('roles')) {
            $user->syncRoles(array_merge(['admin'], $request->roles));
        }

        return response()->json([
            'message' => 'Cập nhật thành công.',
            'admin' => $user->load('roles', 'permissions')
        ]);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if (auth()->id() == $user->id) {
            return response()->json([
                'message' => 'Không thể xóa tài khoản đang đăng nhập.'
            ], 403);
        }

        $user->delete();

        return response()->json([
            'message' => 'Xóa admin thành công'
        ]);
    }
}
