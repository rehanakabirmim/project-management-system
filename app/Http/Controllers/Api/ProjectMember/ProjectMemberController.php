<?php

namespace App\Http\Controllers\Api\ProjectMember;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProjectMember;

class ProjectMemberController extends Controller
{
    // List all project members
    public function index()
    {
        $members = ProjectMember::with(['project','user','role'])->get();
        return response()->json($members, 200);
    }

    // Add single member
    public function store(Request $request)
    {
        $data = $request->validate([
            'project_id'       => 'required|exists:projects,id',
            'user_id'          => 'required|exists:users,id',
            'assigned_role_id' => 'nullable|exists:roles,id',
        ]);

        $member = ProjectMember::create($data);

        return response()->json([
            'message' => 'Project member added successfully',
            'data' => $member
        ], 201);
    }

    // Bulk add multiple members
    public function storeMultiple(Request $request)
    {
        $data = $request->validate([
            'members' => 'required|array',
            'members.*.project_id'       => 'required|exists:projects,id',
            'members.*.user_id'          => 'required|exists:users,id',
            'members.*.assigned_role_id' => 'nullable|exists:roles,id',
        ]);

        $inserted = [];
        foreach ($data['members'] as $memberData) {
            $inserted[] = ProjectMember::create($memberData);
        }

        return response()->json([
            'message' => 'Project members added successfully',
            'data' => $inserted
        ], 201);
    }

    // Show single member
    public function show($id)
    {
        $member = ProjectMember::with(['project','user','role'])->findOrFail($id);
        return response()->json($member, 200);
    }

    // Update member
    public function update(Request $request, $id)
    {
        $member = ProjectMember::findOrFail($id);

        $data = $request->validate([
            'project_id'       => 'sometimes|required|exists:projects,id',
            'user_id'          => 'sometimes|required|exists:users,id',
            'assigned_role_id' => 'nullable|exists:roles,id',
        ]);

        $member->update($data);

        return response()->json([
            'message' => 'Project member updated successfully',
            'data' => $member
        ], 200);
    }

    // Delete member
    public function destroy($id)
    {
        $member = ProjectMember::findOrFail($id);
        $member->delete();

        return response()->json([
            'message' => 'Project member deleted successfully'
        ], 200);
    }
}
