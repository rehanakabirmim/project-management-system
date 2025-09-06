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
        $projects = Project::with('creator', 'members', 'phases')->get()
            ->map(fn($project) => $this->formatProjectResponse($project));

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
            'phase_ids'           => 'nullable|array',
            'phase_ids.*'         => 'exists:project_phases,id',
            'order_sheet_link'    => 'nullable|string|url',
            'order_sheet_file'    => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);

        // Handle file upload
        if ($request->hasFile('order_sheet_file')) {
            $file = $request->file('order_sheet_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('order_sheets', $filename, 'public');
            $data['order_sheet_link'] = $path;
        }

        $project = Project::create($data);
        $notifications = [];

        // Attach members & send notifications
        if (!empty($data['member_ids'])) {
            $project->members()->sync($data['member_ids']);
            foreach ($project->members as $user) {
                $notifications[] = NotificationService::create(
                    $user->id,
                    'New Project Assigned',
                    "Project {$project->project_name} has been assigned to you.",
                    'project'
                );
            }
        }

        // Attach phases
        if (!empty($data['phase_ids'])) {
            $project->phases()->sync($data['phase_ids']);
        }

        return response()->json([
            'message' => 'Project created successfully',
            'project' => $project->load('creator', 'members', 'phases'),
            'notifications' => $notifications
        ], 201);
    }

    // Show single project
    public function show($id)
    {
        $project = Project::with('creator', 'members', 'phases')->findOrFail($id);
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
            'phase_ids'           => 'nullable|array',
            'phase_ids.*'         => 'exists:project_phases,id',
            'order_sheet_link'    => 'nullable|string|url',
            'order_sheet_file'    => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);

        // Handle file upload and delete old file
        if ($request->hasFile('order_sheet_file')) {
            if ($project->order_sheet_link && Storage::disk('public')->exists($project->order_sheet_link)) {
                Storage::disk('public')->delete($project->order_sheet_link);
            }
            $file = $request->file('order_sheet_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('order_sheets', $filename, 'public');
            $data['order_sheet_link'] = $path;
        } elseif (!empty($data['order_sheet_link'])) {
            $data['order_sheet_link'] = $data['order_sheet_link'];
        }

        $project->update($data);
        $notifications = [];

        // Sync members and send notifications
        if (isset($data['member_ids'])) {
            $project->members()->sync($data['member_ids']);
            foreach ($project->members as $user) {
                $notifications[] = NotificationService::create(
                    $user->id,
                    'Project Updated',
                    "Project {$project->project_name} has been updated.",
                    'project'
                );
            }
        }

        // Sync phases
        if (isset($data['phase_ids'])) {
            $project->phases()->sync($data['phase_ids']);
        }

        return response()->json([
            'message' => 'Project updated successfully',
            'project' => $project->load('creator', 'members', 'phases'),
            'notifications' => $notifications
        ], 200);
    }

    // Delete project
    public function destroy($id)
    {
        $project = Project::findOrFail($id);

        $members = $project->members ?? collect([]);
        $notifications = [];

        // Detach members & phases
        $project->members()->detach();
        $project->phases()->detach();

        // Delete physical file if exists
        if ($project->order_sheet_link && Storage::disk('public')->exists($project->order_sheet_link)) {
            Storage::disk('public')->delete($project->order_sheet_link);
        }

        $project->delete();

        // Send notifications to previous members
        foreach ($members as $user) {
            $notifications[] = NotificationService::create(
                $user->id,
                'Project Deleted',
                "Project {$project->project_name} has been deleted.",
                'project'
            );
        }

        return response()->json([
            'message' => 'Project deleted successfully',
            'notifications' => $notifications
        ], 200);
    }

    // Get deadlines / remaining days for all projects
    public function deadlines()
    {
        $projects = Project::all()->map(function($project) {
            $project->deadline_days = now()->diffInDays($project->end_date, false);
            return $this->formatProjectResponse($project);
        });

        return response()->json($projects, 200);
    }

    // Format project response with auto-calculated fields
    private function formatProjectResponse(Project $project)
    {
        return [
            'id'                 => $project->id,
            'project_name'       => $project->project_name,
            'client_id'          => $project->client_id,
            'profile_name'       => $project->profile_name,
            'status'             => $project->status,
            'start_date'         => $project->start_date,
            'end_date'           => $project->end_date,
            'total_amount'       => $project->total_amount,
            'running_state'      => $project->running_state,
            'is_delivered'       => $project->is_delivered,
            'post_delivery_state'=> $project->post_delivery_state,
            'client_mood'        => $project->client_mood,
            'issue'              => $project->issue,
            'created_by'         => $project->created_by,
            'order_sheet_link'   => $project->order_sheet_link,
            'members'            => $project->members,
            'phases'             => $project->phases,
            'creator'            => $project->creator,
            'deadline_days'      => $project->deadline_days,
            'remaining_time'     => $project->remaining_time,
            'color_code'         => $project->color_code,
        ];
    }



}
