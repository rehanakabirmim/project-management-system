<?php

namespace App\Http\Controllers\Api\Project;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;


class ProjectController extends Controller
{
    // Get all projects
    public function index()
    {
        return response()->json(Project::with('creator', 'members')->get(), 200);
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
            'end_date'            => 'required|date|after:start_date',
            'deadline'            => 'required|date|after_or_equal:start_date',
            'remaining_hours'     => 'required|numeric',
            'total_amount'        => 'required|numeric',
            'running_state'       => 'required|in:UI/UX,Frontend,Backend,Flutter',
            'delivery_date'       => 'required|date|after_or_equal:end_date',
            'is_delivered'        => 'required|in:delivered,ongoing,requested',
            'post_delivery_state' => 'required|in:bug fixing,redesigning,deployment,new feature,all clear',
            'client_mood'         => 'required|in:cool,hyper,happy,normal',
            'issue'               => 'nullable|string',
            'color_code'          => 'required|in:light_black,light_violet,green,yellow',
            'created_by'          => 'required|exists:users,id',
            'member_ids'          => 'nullable|array',
            'member_ids.*'        => 'exists:users,id',
            'order_sheet_link'    => 'nullable|string|url',
            'order_sheet_file'    => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);

        // Handle file upload
        if ($request->hasFile('order_sheet_file')) {
            $file = $request->file('order_sheet_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('order_sheets', $filename, 'public');
            $data['order_sheet_link'] = $path; // store path if file uploaded
        }

        // If user provided an external link, just store it as-is
        // No need to fetch content

        $project = Project::create($data);
        $notifications = [];

        // Assign members and send notifications
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

        return response()->json([
            'message' => 'Project created successfully',
            'project' => $project->load('creator', 'members'),
            'notifications' => $notifications
        ], 201);
    }

    // Show single project
    public function show($id)
    {
        $project = Project::with('creator', 'members')->findOrFail($id);
        return response()->json($project, 200);
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
            'end_date'            => 'sometimes|required|date|after:start_date',
            'deadline'            => 'sometimes|required|date|after_or_equal:start_date',
            'remaining_hours'     => 'sometimes|required|numeric',
            'total_amount'        => 'sometimes|required|numeric',
            'running_state'       => 'sometimes|required|in:UI/UX,Frontend,Backend,Flutter',
            'delivery_date'       => 'sometimes|required|date|after_or_equal:end_date',
            'is_delivered'        => 'sometimes|required|in:delivered,ongoing,requested',
            'post_delivery_state' => 'sometimes|required|in:bug fixing,redesigning,deployment,new feature,all clear',
            'client_mood'         => 'sometimes|required|in:cool,hyper,happy,normal',
            'issue'               => 'nullable|string',
            'color_code'          => 'sometimes|required|in:light_black,light_violet,green,yellow',
            'created_by'          => 'sometimes|required|exists:users,id',
            'member_ids'          => 'nullable|array',
            'member_ids.*'        => 'exists:users,id',
            'order_sheet_link'    => 'nullable|string|url',
            'order_sheet_file'    => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);

        // Handle file upload (delete old file if exists)
        if ($request->hasFile('order_sheet_file')) {
            if ($project->order_sheet_link && Storage::disk('public')->exists($project->order_sheet_link)) {
                Storage::disk('public')->delete($project->order_sheet_link);
            }
            $file = $request->file('order_sheet_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('order_sheets', $filename, 'public');
            $data['order_sheet_link'] = $path;
        }

        // External link just overwrite if provided
        elseif (!empty($data['order_sheet_link'])) {
            $data['order_sheet_link'] = $data['order_sheet_link'];
        }

        $project->update($data);
        $notifications = [];

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

        return response()->json([
            'message' => 'Project updated successfully',
            'project' => $project->load('creator', 'members'),
            'notifications' => $notifications
        ], 200);
    }

    // Delete project
    public function destroy($id)
    {
        $project = Project::findOrFail($id);
        $members = $project->members ?? collect([]);
        $notifications = [];

        // Delete physical file if exists
        if ($project->order_sheet_link && Storage::disk('public')->exists($project->order_sheet_link)) {
            Storage::disk('public')->delete($project->order_sheet_link);
        }

        $project->delete();

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





public function deadlines()
{
    $projects = Project::with('creator')->get()
        ->map(function ($project) {
            return [
                'id' => $project->id,
                'project_name' => $project->project_name,
                'deadline' => $project->deadline_days,      // integer days
                'remaining_time' => $project->remaining_time, // hours + minutes
                'status' => $project->status,
                'color' => $project->color_code,
                'creator_id' => $project->creator ? $project->creator->id : null,
            ];
        });

    return response()->json([
        'status' => 'success',
        'data' => $projects
    ]);
}








   
}
