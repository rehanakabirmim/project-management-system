<?php

namespace App\Http\Controllers\Api\RolePermission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index() {
        return response()->json(Role::all());
    }

    public function store(Request $request) {
        $request->validate(['name'=>'required|string|unique:roles,name']);
        $role = Role::create(['name'=>$request->name, 'guard_name'=>'web']);
        return response()->json($role);
    }

    public function update(Request $request, Role $role) {
        $role->update($request->only('name'));
        return response()->json($role);
    }

    public function destroy(Role $role) {
        $role->delete();
        return response()->json(['message'=>'Role deleted']);
    }
}