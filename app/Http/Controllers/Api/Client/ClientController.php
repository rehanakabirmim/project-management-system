<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Client;

class ClientController extends Controller
{
    // List all clients
    public function index()
    {
        $clients = Client::all();
        return response()->json([
            'status' => 'success',
            'data' => $clients
        ]);
    }

    // Store a new client
    public function store(Request $request)
    {
        $request->validate([
            'client_name' => 'required|string|max:255',
            'profile_name' => 'required|string|max:255',
            'mood' => 'nullable|in:cool,hyper,happy,normal',
        ]);

        $client = Client::create($request->only('client_name', 'profile_name', 'mood'));

        return response()->json([
            'status' => 'success',
            'message' => 'Client created successfully',
            'data' => $client
        ], 201);
    }

    // Show single client
    public function show($id)
    {
        $client = Client::findOrFail($id);
        return response()->json([
            'status' => 'success',
            'data' => $client
        ]);
    }

    // Update client
    public function update(Request $request, $id)
    {
        $client = Client::findOrFail($id);

        $request->validate([
            'client_name' => 'required|string|max:255',
            'profile_name' => 'required|string|max:255',
            'mood' => 'nullable|in:cool,hyper,happy,normal',
        ]);

        $client->update($request->only('client_name', 'profile_name', 'mood'));

        return response()->json([
            'status' => 'success',
            'message' => 'Client updated successfully',
            'data' => $client
        ]);
    }

    // Delete client
    public function destroy($id)
    {
        $client = Client::findOrFail($id);
        $client->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Client deleted successfully'
        ]);
    }
}
