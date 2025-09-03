<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Show the authenticated user's profile
     */
    public function profile()
    {
        $user = auth()->user();

        return response()->json([
            'message' => 'User profile fetched successfully',
            'user' => $user
        ]);
    }

    /**
     * Update the authenticated user's profile
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        // Validate the request
        $validated = $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'last_name'  => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id, // ignore current user
            'phone_number' => 'sometimes|string|max:20',
            'department_id' => 'sometimes|exists:departments,id',
            'status' => 'sometimes|in:active,inactive',
        ]);

        // Update only the fields sent in the request
        $user->update($validated);

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
    }

}
