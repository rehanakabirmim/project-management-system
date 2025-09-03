<?php

namespace App\Http\Controllers\Api\RolePermission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserRoleController extends Controller
{
    public function assignRole(Request $request, User $user) {
        $request->validate(['role'=>'required|string']);
        $user->assignRole($request->role);
        return response()->json(['message'=>"Role assigned to user"]);
    }

    public function assignPermission(Request $request, User $user) {
        $request->validate(['permission'=>'required|string']);
        $user->givePermissionTo($request->permission);
        return response()->json(['message'=>"Permission assigned to user"]);
    }

    public function getUserRoles(User $user) {
        return response()->json($user->getRoleNames());
    }

    public function getUserPermissions(User $user) {
        return response()->json($user->getAllPermissions());
    }
}