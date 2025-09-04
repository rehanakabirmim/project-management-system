<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Models\UserOffday;
use Illuminate\Http\Request;

class UserOffdayController extends Controller
{
    // List all offdays
    public function index()
    {
        $offdays = UserOffday::with('user')->paginate(10);

        return response()->json([
            'status' => 'success',
            'message' => 'User offdays retrieved successfully',
            'data' => $offdays
        ]);
    }

    // Create new offday
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'off_day' => 'required|string|max:20',  // any day name
            'reason' => 'nullable|string|max:255',
        ]);

        $offday = UserOffday::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'User offday created successfully',
            'data' => $offday
        ], 201);
    }

    // Show single offday
    public function show(UserOffday $userOffday)
    {
        $userOffday->load('user');

        return response()->json([
            'status' => 'success',
            'message' => 'User offday retrieved successfully',
            'data' => $userOffday
        ]);
    }

    // Update offday
    public function update(Request $request, UserOffday $userOffday)
    {
        $validated = $request->validate([
            'user_id' => 'sometimes|exists:users,id',
            'off_day' => 'sometimes|string|max:20',
            'reason' => 'nullable|string|max:255',
        ]);

        $userOffday->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'User offday updated successfully',
            'data' => $userOffday
        ]);
    }

    // Delete offday
    public function destroy(UserOffday $userOffday)
    {
        $userOffday->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'User offday deleted successfully'
        ]);
    }
}
