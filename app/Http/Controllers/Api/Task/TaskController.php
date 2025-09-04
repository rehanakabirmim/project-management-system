<?php

namespace App\Http\Controllers\API\Task;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    // ----------------------
    // List all tasks
    // ----------------------
    public function index()
    {
        $tasks = Task::with([
            'assignedUser',
            'project',
            'phase',
            'projectMembers.user',
            'projectMembers.role',
        ])->paginate(10);

        $tasksData = $tasks->map(function ($task) {
            $totalMembers = $task->projectMembers->count();
            $completed = 0; // manual logic
            $progressPercent = $totalMembers > 0 ? round(($completed / $totalMembers) * 100, 2) : 0;

            return [
                'task' => $task,
                'assigned_member_count' => $totalMembers,
                'progress_percent' => $progressPercent,
                'source_code_url' => $task->source_code ? asset('storage/'.$task->source_code) : $task->source_code,
                'live_demo_url' => $task->live_demo ? asset('storage/'.$task->live_demo) : $task->live_demo,
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Tasks retrieved successfully.',
            'data' => [
                'tasks' => $tasksData,
                'total' => $tasks->total(),
                'per_page' => $tasks->perPage(),
                'current_page' => $tasks->currentPage(),
                'last_page' => $tasks->lastPage(),
            ]
        ]);
    }

    // ----------------------
    // Create a new task
    // ----------------------
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'phase_id' => 'nullable|exists:project_phases,id',
            'task_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'assigned_to' => 'nullable|exists:users,id',
            'project_member_ids' => 'nullable|array',
            'project_member_ids.*' => 'exists:project_members,id',
            'source_code' => 'nullable|string',
            'live_demo' => 'nullable|string',
            'source_code_file' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'live_demo_file' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);

        DB::transaction(function () use ($validated, &$task) {

            // Handle source_code file upload
            if (!empty($validated['source_code_file'])) {
                $file = $validated['source_code_file'];
                $filename = time().'_'.$file->getClientOriginalName();
                $path = $file->storeAs('tasks/source_code', $filename, 'public');
                $validated['source_code'] = $path;
            }

            // Handle live_demo file upload
            if (!empty($validated['live_demo_file'])) {
                $file = $validated['live_demo_file'];
                $filename = time().'_'.$file->getClientOriginalName();
                $path = $file->storeAs('tasks/live_demo', $filename, 'public');
                $validated['live_demo'] = $path;
            }

            $task = Task::create([
                'project_id' => $validated['project_id'],
                'phase_id' => $validated['phase_id'] ?? null,
                'task_name' => $validated['task_name'],
                'description' => $validated['description'] ?? null,
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
                'assigned_to' => $validated['assigned_to'] ?? null,
                'source_code' => $validated['source_code'] ?? null,
                'live_demo' => $validated['live_demo'] ?? null,
            ]);

            if (!empty($validated['project_member_ids'])) {
                $task->projectMembers()->sync($validated['project_member_ids']);
            }
        });

        $task->load('projectMembers.user', 'projectMembers.role');

        return response()->json([
            'status' => 'success',
            'message' => 'Task created successfully.',
            'data' => [
                'task' => $task,
                'assigned_member_count' => $task->projectMembers->count(),
                'source_code_url' => $task->source_code ? asset('storage/'.$task->source_code) : $task->source_code,
                'live_demo_url' => $task->live_demo ? asset('storage/'.$task->live_demo) : $task->live_demo,
            ]
        ], 201);
    }

    // ----------------------
    // Show single task
    // ----------------------
    public function show(Task $task)
    {
        $task->load('assignedUser', 'project', 'phase', 'projectMembers.user', 'projectMembers.role');

        return response()->json([
            'status' => 'success',
            'message' => 'Task retrieved successfully.',
            'data' => [
                'task' => $task,
                'assigned_member_count' => $task->projectMembers->count(),
                'source_code_url' => $task->source_code ? asset('storage/'.$task->source_code) : $task->source_code,
                'live_demo_url' => $task->live_demo ? asset('storage/'.$task->live_demo) : $task->live_demo,
            ]
        ]);
    }

    // ----------------------
    // Update task
    // ----------------------
    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'project_id' => 'sometimes|exists:projects,id',
            'phase_id' => 'sometimes|exists:project_phases,id',
            'task_name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'assigned_to' => 'nullable|exists:users,id',
            'project_member_ids' => 'nullable|array',
            'project_member_ids.*' => 'exists:project_members,id',
            'source_code' => 'nullable|string',
            'live_demo' => 'nullable|string',
            'source_code_file' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'live_demo_file' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);

        DB::transaction(function () use ($validated, $task) {

            // Delete old source_code if new uploaded
            if (!empty($validated['source_code_file']) && $task->source_code && Storage::disk('public')->exists($task->source_code)) {
                Storage::disk('public')->delete($task->source_code);
            }
            if (!empty($validated['source_code_file'])) {
                $file = $validated['source_code_file'];
                $filename = time().'_'.$file->getClientOriginalName();
                $path = $file->storeAs('tasks/source_code', $filename, 'public');
                $validated['source_code'] = $path;
            }

            // Delete old live_demo if new uploaded
            if (!empty($validated['live_demo_file']) && $task->live_demo && Storage::disk('public')->exists($task->live_demo)) {
                Storage::disk('public')->delete($task->live_demo);
            }
            if (!empty($validated['live_demo_file'])) {
                $file = $validated['live_demo_file'];
                $filename = time().'_'.$file->getClientOriginalName();
                $path = $file->storeAs('tasks/live_demo', $filename, 'public');
                $validated['live_demo'] = $path;
            }

            $task->update($validated);

            if (!empty($validated['project_member_ids'])) {
                $task->projectMembers()->sync($validated['project_member_ids']);
            }
        });

        $task->load('projectMembers.user', 'projectMembers.role');

        return response()->json([
            'status' => 'success',
            'message' => 'Task updated successfully.',
            'data' => [
                'task' => $task,
                'assigned_member_count' => $task->projectMembers->count(),
                'source_code_url' => $task->source_code ? asset('storage/'.$task->source_code) : $task->source_code,
                'live_demo_url' => $task->live_demo ? asset('storage/'.$task->live_demo) : $task->live_demo,
            ]
        ]);
    }

    // ----------------------
    // Delete task
    // ----------------------
    public function destroy($id)
    {
        $task = Task::findOrFail($id);

        DB::transaction(function () use ($task) {

            // Delete uploaded files
            if ($task->source_code && Storage::disk('public')->exists($task->source_code)) {
                Storage::disk('public')->delete($task->source_code);
            }
            if ($task->live_demo && Storage::disk('public')->exists($task->live_demo)) {
                Storage::disk('public')->delete($task->live_demo);
            }

            $task->projectMembers()->detach();
            $task->delete();
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Task deleted successfully.'
        ]);
    }
}
