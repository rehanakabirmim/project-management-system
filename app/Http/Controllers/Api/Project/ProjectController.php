<?php

namespace App\Http\Controllers\Api\Project;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Storage;

class ProjectController extends Controller
{
    // List all projects
    public function index()
    {
        $projects = Project::with('creator', 'members')->get()->map(function($project) {
            return $this->formatProjectResponse($project);
        });

        return response()->json($projects, 200);
    }

    // Store a new project
    public function store(Request $request)
    {
        $data = $request->validate([
            'project_name'        => 'required|string|max:255',
            'client_id'           => 'required|exists:clients,id',
            'profile_name'        => 'required|string|max:255',
            'status'              => 'required|in:not_started,in_progress,hold,completed,cancelled',
            'start_date'          => 'required|date',
            'end_date'            => 'required|date|after_or_equal:start_date',
            'total_amount'        => 'required|numeric',
            'running_state'       => 'required|in:UI/UX,Frontend,Backend,Flutter',
            'is_delivered'        => 'required|in:delivered,ongoing,requested',
            'post_delivery_state' => 'required|in:bug fixing,redesigning,deployment,new feature,all clear',
            'client_mood'         => 'required|in:cool,hyper,happy,normal',
            'issue'               => 'nullable|string',
            'created_by'          => 'required|exists:users,id',
            'member_ids'          => 'nullable|array',
            'member_ids.*'        => 'exists:users,id',
            'order_sheet_link'    => 'nullable|string|url',
        ]);

        $project = Project::create($data);

        if (!empty($data['member_ids'])) {
            $project->members()->sync($data['member_ids']);
        }

        return response()->json([
            'message' => 'Project created successfully',
            'project' => $this->formatProjectResponse($project->load('creator', 'members'))
        ], 201);
    }

    // Show single project
    public function show($id)
    {
        $project = Project::with('creator', 'members')->findOrFail($id);
        return response()->json($this->formatProjectResponse($project), 200);
    }

    // Update project
    public function update(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        $data = $request->validate([
            'project_name'        => 'sometimes|required|string|max:255',
            'client_id'           => 'sometimes|required|exists:clients,id',
            'profile_name'        => 'sometimes|required|string|max:255',
            'status'              => 'sometimes|required|in:not_started,in_progress,hold,completed,cancelled',
            'start_date'          => 'sometimes|required|date',
            'end_date'            => 'sometimes|required|date|after_or_equal:start_date',
            'total_amount'        => 'sometimes|required|numeric',
            'running_state'       => 'sometimes|required|in:UI/UX,Frontend,Backend,Flutter',
            'is_delivered'        => 'sometimes|required|in:delivered,ongoing,requested',
            'post_delivery_state' => 'sometimes|required|in:bug fixing,redesigning,deployment,new feature,all clear',
            'client_mood'         => 'sometimes|required|in:cool,hyper,happy,normal',
            'issue'               => 'nullable|string',
            'member_ids'          => 'nullable|array',
            'member_ids.*'        => 'exists:users,id',
            'order_sheet_link'    => 'nullable|string|url',
        ]);

        $project->update($data);

        if (isset($data['member_ids'])) {
            $project->members()->sync($data['member_ids']);
        }

        return response()->json([
            'message' => 'Project updated successfully',
            'project' => $this->formatProjectResponse($project->load('creator', 'members'))
        ], 200);
    }

    // Format project response with auto-calculated fields
    private function formatProjectResponse(Project $project)
    {
        return [
            'id' => $project->id,
            'project_name' => $project->project_name,
            'client_id' => $project->client_id,
            'profile_name' => $project->profile_name,
            'status' => $project->status,
            'start_date' => $project->start_date,
            'end_date' => $project->end_date,
            'total_amount' => $project->total_amount,
            'running_state' => $project->running_state,
            'is_delivered' => $project->is_delivered,
            'post_delivery_state' => $project->post_delivery_state,
            'client_mood' => $project->client_mood,
            'issue' => $project->issue,
            'created_by' => $project->created_by,
            'order_sheet_link' => $project->order_sheet_link,
            'members' => $project->members,
            'creator' => $project->creator,
            'deadline_days' => $project->deadline_days,
            'remaining_time' => $project->remaining_time,
            'color_code' => $project->color_code,
        ];
    }
}
