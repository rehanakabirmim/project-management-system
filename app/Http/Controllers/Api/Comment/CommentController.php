<?php

namespace App\Http\Controllers\Api\Comment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Comment;

class CommentController extends Controller
{
    // List all parent comments with nested replies
    public function index(Request $request)
    {
        $query = Comment::whereNull('parent_id'); // only parent comments

        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->has('task_id')) {
            $query->where('task_id', $request->task_id);
        }

        $comments = $query->with('user', 'replies')->get();

        return response()->json($comments, 200);
    }

    // Add new comment or reply
    public function store(Request $request)
    {
        $data = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'task_id'    => 'nullable|exists:tasks,id',
            'user_id'    => 'required|exists:users,id',
            'parent_id'  => 'nullable|exists:comments,id', // reply হলে parent_id দিতে হবে
            'comment'    => 'required|string',
        ]);

        $comment = Comment::create($data);

        return response()->json([
            'message' => 'Comment added successfully',
            'comment' => $comment->load('user', 'replies'),
        ], 201);
    }

    // Delete comment (nested replies will also be deleted)
    public function destroy($id)
    {
        $comment = Comment::findOrFail($id);

        // Recursive delete for replies
        $this->deleteReplies($comment);

        $comment->delete();

        return response()->json(['message' => 'Comment deleted successfully'], 200);
    }

    // Recursive delete function
    private function deleteReplies(Comment $comment)
    {
        foreach ($comment->replies as $reply) {
            $this->deleteReplies($reply);
            $reply->delete();
        }
    }
}
