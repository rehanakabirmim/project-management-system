<?php

namespace App\Http\Controllers\Api\RolePermission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index() {
        return response()->json(Permission::all());
    }

    public function store(Request $request) {
        $request->validate(['name'=>'required|string|unique:permissions,name']);
        $permission = Permission::create(['name'=>$request->name, 'guard_name'=>'web']);
        return response()->json($permission);
    }
}
