<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth; // ✅ Import JWTAuth
use Illuminate\Support\Facades\Auth;

class ServiceController extends Controller
{
    public function __construct()
    {
        // ✅ Apply JWT Authentication for all admin-related functions
        $this->middleware('jwt.auth')->except(['index', 'show']);
    }

    public function index()
    {
        return response()->json(Service::where('status', 1)->get(), 200);
    }
// 🛠️ Admin-only: Fetch all services, regardless of status
public function getAll()
{
    $user = Auth::user();
    if (!$user) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $services = Service::all();
    return response()->json($services, 200);
}

    public function store(Request $request)
    {
        $user = Auth::user(); // ✅ Ensure user is authenticated
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

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
        $service->status = intval($validated['status']);

        if ($request->hasFile('image')) {
            $service->image = $request->file('image')->store('services', 'public');
        }

        $service->save();

        return response()->json(['message' => 'Service created successfully', 'service' => $service], 201);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user(); // ✅ Ensure user is authenticated
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $service = Service::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes||string|max:255',
            'description' => 'sometimes||string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'sometimes||in:0,1',
        ]);

        $service->fill($validated);


        if ($request->hasFile('image')) {
            if ($service->image) {
                Storage::disk('public')->delete($service->image);
            }
            $service->image = $request->file('image')->store('services', 'public');
        }

        $service->save();

        return response()->json(['message' => 'Service updated successfully', 'service' => $service], 200);
    }

    public function updateStatus(Request $request, $id)
    {
        $user = Auth::user(); // ✅ Ensure user is authenticated
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $service = Service::findOrFail($id);
        $validated = $request->validate(['status' => 'required|boolean']);

        $service->update(['status' => $validated['status']]);

        return response()->json(['message' => 'Service status updated successfully', 'service' => $service], 200);
    }

    public function destroy($id)
    {
        $user = Auth::user(); // ✅ Ensure user is authenticated
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $service = Service::findOrFail($id);
        if ($service->image) {
            Storage::disk('public')->delete($service->image);
        }
        $service->delete();
        return response()->json(['message' => 'Service deleted successfully'], 200);
    }

    public function show($id)
    {
        $service = Service::where('id', $id)->where('status', 1)->first();

        if (!$service) {
            return response()->json(['message' => 'Service not found or inactive'], 404);
        }

        return response()->json($service, 200);
    }
}
