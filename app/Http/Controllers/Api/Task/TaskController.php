<?php

namespace App\Http\Controllers\API\Task;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'project.tasks.projectMembers'
        ])->paginate(10);

        $tasksData = $tasks->map(function ($task) {
            $totalMembers = $task->projectMembers->count();
            $completed = 0; // manual logic, no column
            $progressPercent = $totalMembers > 0 ? round(($completed / $totalMembers) * 100, 2) : 0;

            $totalProgressPercent = 0;
            if ($task->project && $task->project->tasks->count() > 0) {
                $sum = 0;
                foreach ($task->project->tasks as $t) {
                    $totalM = $t->projectMembers->count();
                    $sum += 0; // manual completed
                }
                $totalProgressPercent = round($sum / $task->project->tasks->count(), 2);
            }

            return [
                'task' => $task,
                'assigned_member_count' => $task->projectMembers->count(),
                'progress_percent' => $progressPercent,
                'total_progress_percent' => $totalProgressPercent
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
        ]);

        DB::transaction(function () use ($validated, &$task) {
            $task = Task::create([
                'project_id' => $validated['project_id'],
                'phase_id' => $validated['phase_id'] ?? null,
                'task_name' => $validated['task_name'],
                'description' => $validated['description'] ?? null,
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
                'assigned_to' => $validated['assigned_to'] ?? null,
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
        ]);

        DB::transaction(function () use ($validated, $task) {
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
            $task->projectMembers()->detach();
            $task->delete();
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Task deleted successfully.'
        ]);
    }
}
