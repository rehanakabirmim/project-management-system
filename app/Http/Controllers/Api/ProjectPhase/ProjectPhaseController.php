<?php

namespace App\Http\Controllers\Api\ProjectPhase;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProjectPhase;
use Illuminate\Validation\Rule;

class ProjectPhaseController extends Controller
{
    // List all project phases
    public function index()
    {
        $phases = ProjectPhase::all();
        return response()->json($phases, 200);
    }

    // Store new phase
    public function store(Request $request)
    {
        $data = $request->validate([
            'project_id'   => 'required|exists:projects,id',
            'phases_name'  => 'required|string|max:255',
            'status'       => ['required', Rule::in(['not_started','in_progress','completed','hold'])],
            'start_date'   => 'nullable|date',
            'end_date'     => 'nullable|date|after_or_equal:start_date',
            'remarks'      => 'nullable|string',
        ]);

        $phase = ProjectPhase::create($data);
        return response()->json($phase, 201);
    }



    public function storeMultiple(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'phases'     => 'required|array',
            'phases.*.phases_name' => 'required|string|max:255',
            'phases.*.status'      => 'required|in:not_started,in_progress,completed,hold',
            'phases.*.start_date'  => 'nullable|date',
            'phases.*.end_date'    => 'nullable|date|after_or_equal:phases.*.start_date',
            'phases.*.remarks'     => 'nullable|string',
        ]);

        $created = [];
        foreach ($request->phases as $phaseData) {
            $phaseData['project_id'] = $request->project_id;
            $created[] = ProjectPhase::create($phaseData);
        }

        return response()->json([
            'message' => 'Phases added successfully',
            'data' => $created
        ], 201);
    }


    // Show single phase
    public function show($id)
    {
        $phase = ProjectPhase::findOrFail($id);
        return response()->json($phase, 200);
    }

   
    // Update phase
    public function update(Request $request, $id)
    {
        $phase = ProjectPhase::findOrFail($id);

        $data = $request->validate([
            'project_id'   => 'sometimes|required|exists:projects,id',
            'phases_name'  => 'sometimes|required|string|max:255',
            'status'       => ['sometimes','required', Rule::in(['not_started','in_progress','completed','hold'])],
            'start_date'   => 'nullable|date',
            'end_date'     => 'nullable|date|after_or_equal:start_date',
            'remarks'      => 'nullable|string',
        ]);

        $phase->update($data);

        return response()->json([
            'status'  => 'success',
            'message' => 'Phase updated successfully',
            'data'    => $phase
        ], 200);
    }


    // Delete phase
    public function destroy($id)
    {
        $phase = ProjectPhase::findOrFail($id);
        $phase->delete();
        return response()->json(['message'=>'Project phase deleted successfully'], 200);
    }
}
