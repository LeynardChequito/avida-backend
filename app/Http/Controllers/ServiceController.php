<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ServiceController extends Controller
{
    public function index()
    {
        return response()->json(Service::all(), 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|in:0,1',
        ]);
        
        $service = new Service();
        $service->title = $validated['title'];
        $service->slug = Str::slug($validated['title']);
        $service->description = $validated['description'];
        $service->status = intval($validated['status']); // Convert to integer (0 or 1)
        
        if ($request->hasFile('image')) {
            $service->image = $request->file('image')->store('services', 'public');
        }
        
        $service->save();
        
        return response()->json(['message' => 'Service created successfully', 'service' => $service], 201);
    }

    public function update(Request $request, $id)
{
    $service = Service::findOrFail($id);

    // Fix Validation
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'status' => 'required|in:0,1', // Ensure status is 0 or 1
    ]);

    // Update Fields
    $service->fill([
        'title' => $validated['title'],
        'description' => $validated['description'],
        'status' => intval($validated['status']), // Convert to integer
    ]);

    // Handle Image Upload
    if ($request->hasFile('image')) {
        if ($service->image) {
            Storage::disk('public')->delete($service->image); // Delete old image
        }
        $service->image = $request->file('image')->store('services', 'public');
    }

    $service->save();

    return response()->json(['message' => 'Service updated successfully', 'service' => $service], 200);
}

    public function updateStatus(Request $request, $id)
    {
        $service = Service::findOrFail($id);
        $validated = $request->validate(['status' => 'required|boolean']);

        $service->update(['status' => $validated['status']]);

        return response()->json(['message' => 'Service status updated successfully', 'service' => $service], 200);
    }

    public function destroy($id)
    {
        $service = Service::findOrFail($id);
        if ($service->image) {
            Storage::disk('public')->delete($service->image);
        }
        $service->delete();
        return response()->json(['message' => 'Service deleted successfully'], 200);
    }
    public function show($id)
    {
        // Find service where ID matches and status is 1 (active)
        $service = Service::where('id', $id)->where('status', 1)->first();
    
        if (!$service) {
            return response()->json(['message' => 'Service not found or inactive'], 404);
        }
    
        return response()->json($service, 200);
    }
    
}
