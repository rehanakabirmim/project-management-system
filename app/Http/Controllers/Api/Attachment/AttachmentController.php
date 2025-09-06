<?php

namespace App\Http\Controllers\Api\Attachment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attachment;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    // List all attachments (optionally filter by project_id or task_id)
    public function index(Request $request)
    {
        $query = Attachment::query();

        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->has('task_id')) {
            $query->where('task_id', $request->task_id);
        }

        $attachments = $query->with('uploader')->get();

        return response()->json($attachments, 200);
    }

    // Upload attachment
    public function store(Request $request)
    {
        $data = $request->validate([
            'project_id'  => 'nullable|exists:projects,id',
            'task_id'     => 'nullable|exists:tasks,id',
            'uploaded_by' => 'required|exists:users,id',
            'file'        => 'required|file|max:10240', // max 10 MB
        ]);

        $file = $request->file('file');
        $path = $file->store('attachments', 'public');

        $attachment = Attachment::create([
            'project_id'  => $data['project_id'] ?? null,
            'task_id'     => $data['task_id'] ?? null,
            'uploaded_by' => $data['uploaded_by'],
            'file_path'   => $path,
            'file_type'   => $file->getClientOriginalExtension(),
        ]);

        return response()->json([
            'message'    => 'Attachment uploaded successfully',
            'attachment' => $attachment,
        ], 201);
    }

    // Download attachment
    public function download($id)
    {
        $attachment = Attachment::findOrFail($id);

        if (!Storage::disk('public')->exists($attachment->file_path)) {
            return response()->json(['message' => 'File not found'], 404);
        }

        return Storage::disk('public')->download($attachment->file_path);
    }

    // Delete attachment
    public function destroy($id)
    {
        $attachment = Attachment::findOrFail($id);

        if (Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $attachment->delete();

        return response()->json(['message' => 'Attachment deleted successfully'], 200);
    }
}
