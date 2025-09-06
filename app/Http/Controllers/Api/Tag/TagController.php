<?php

namespace App\Http\Controllers\Api\Tag;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tag;
use App\Models\Project;
use App\Models\Task;

class TagController extends Controller
{
    // List all tags
    public function index()
    {
        $tags = Tag::all();
        return response()->json($tags, 200);
    }

    // Create a new tag
    public function store(Request $request)
    {
        $data = $request->validate([
            'tag_name' => 'required|string|max:100',
            'color_code' => 'nullable|string|max:20',
        ]);

        $tag = Tag::create($data);

        return response()->json([
            'message' => 'Tag created successfully',
            'tag' => $tag
        ], 201);
    }

    // Attach tag to a project or task
    public function attach(Request $request)
    {
        $data = $request->validate([
            'tag_id' => 'required|exists:tags,id',
            'model_type' => 'required|in:project,task',
            'model_id' => 'required|integer',
        ]);

        $tag = Tag::findOrFail($data['tag_id']);

        if ($data['model_type'] === 'project') {
            $model = Project::findOrFail($data['model_id']);
        } else {
            $model = Task::findOrFail($data['model_id']);
        }

        $model->tags()->syncWithoutDetaching([$tag->id]);

        return response()->json([
            'message' => 'Tag attached successfully'
        ], 200);
    }

    // Detach tag from a project or task
    public function detach(Request $request)
    {
        $data = $request->validate([
            'tag_id' => 'required|exists:tags,id',
            'model_type' => 'required|in:project,task',
            'model_id' => 'required|integer',
        ]);

        $tag = Tag::findOrFail($data['tag_id']);

        if ($data['model_type'] === 'project') {
            $model = Project::findOrFail($data['model_id']);
        } else {
            $model = Task::findOrFail($data['model_id']);
        }

        $model->tags()->detach($tag->id);

        return response()->json([
            'message' => 'Tag detached successfully'
        ], 200);
    }

    // Fetch tags of a project or task
    public function fetchTags(Request $request)
    {
        $data = $request->validate([
            'model_type' => 'required|in:project,task',
            'model_id' => 'required|integer',
        ]);

        if ($data['model_type'] === 'project') {
            $model = Project::with('tags')->findOrFail($data['model_id']);
        } else {
            $model = Task::with('tags')->findOrFail($data['model_id']);
        }

        return response()->json($model->tags, 200);
    }
}
