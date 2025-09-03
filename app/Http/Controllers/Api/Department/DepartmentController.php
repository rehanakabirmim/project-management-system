<?php

namespace App\Http\Controllers\Api\Department;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department;

class DepartmentController extends Controller
{
    // List all departments
    public function index()
    {
        $departments = Department::all();
        return response()->json([
            'status' => 'success',
            'data' => $departments
        ]);
    }

    // Create a new department
    public function store(Request $request)
    {
        $request->validate([
            'dept_name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $department = Department::create($request->only('dept_name', 'description'));

        return response()->json([
            'status' => 'success',
            'message' => 'Department created successfully',
            'data' => $department
        ], 201);
    }

    // Show a single department
    public function show($id)
    {
        $department = Department::findOrFail($id);
        return response()->json([
            'status' => 'success',
            'data' => $department
        ]);
    }

    // Update a department
    public function update(Request $request, $id)
    {
        $department = Department::findOrFail($id);

        $request->validate([
            'dept_name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $department->update($request->only('dept_name', 'description'));

        return response()->json([
            'status' => 'success',
            'message' => 'Department updated successfully',
            'data' => $department
        ]);
    }

    // Delete a department
    public function destroy($id)
    {
        $department = Department::findOrFail($id);
        $department->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Department deleted successfully'
        ]);
    }
}
