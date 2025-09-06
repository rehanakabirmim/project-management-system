<?php

namespace App\Http\Controllers\Api\Summary;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\ActivityLog;

class ActivityLogController extends Controller
{
    /**
     * Project summary with activity logs (Project + Tasks)
     */
    public function projectSummary($projectId)
    {
        $project = Project::with('tasks')->findOrFail($projectId);

        // Project activity logs
        $projectLogs = ActivityLog::with('user')
            ->where('model_type', 'Project')
            ->where('model_id', $project->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Tasks activity logs
        $taskLogs = ActivityLog::with('user')
            ->where('model_type', 'Task')
            ->whereIn('model_id', $project->tasks->pluck('id'))
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'project'       => $project,
            'project_logs'  => $projectLogs,
            'task_logs'     => $taskLogs,
        ]);
    }
}
