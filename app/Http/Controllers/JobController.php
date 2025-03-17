<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class JobController extends Controller
{
    // ✅ Fetch All Jobs (For Admin)
    public function index() {
        return response()->json(Job::all());
    }

    // ✅ Fetch Only Published Jobs (For Job Listings Page)
    public function getPublishedJobs() {
        return response()->json(Job::where('status', 'Published')->get());
    }

    // ✅ Store a New Job
    public function store(Request $request) {
        try {
            \Log::info("Job Create Request Received", $request->all());
    
            // ✅ Validate Incoming Data
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'department' => 'nullable|string|max:255',
                'description' => 'required|string',
                'responsibilities' => 'nullable|string',
                'qualifications' => 'nullable|string',
                'job_type' => 'required|in:Full-time,Part-time,Remote,On-site',
                'salary_min' => 'nullable|numeric',
                'salary_max' => 'nullable|numeric',
                'application_deadline' => 'required|date',
                'status' => 'required|in:Published,Unpublished',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);
    
            \Log::info("Validation Passed");
    
            // ✅ Handle Image Upload
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('job_images', 'public');
                \Log::info("Image Uploaded: " . $imagePath);
            } else {
                $imagePath = null;
            }
    
            // ✅ Create Job Entry
            $job = Job::create(array_merge($validatedData, ['image' => $imagePath]));
    
            \Log::info("Job Created Successfully: " . $job->id);
    
            return response()->json([
                'message' => 'Job added successfully!',
                'job' => $job
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error("Validation Error: " . json_encode($e->errors()));
            return response()->json(['error' => 'Validation failed', 'details' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error("Job Create Error: " . $e->getMessage());
            return response()->json(['error' => 'Failed to add job', 'details' => $e->getMessage()], 500);
        }
    }
    
    
    // ✅ Fetch a Single Job
    public function show($id) {
        return response()->json(Job::findOrFail($id));
    }

    // ✅ Update an Existing Job
    public function update(Request $request, $id) {
        $job = Job::findOrFail($id);

        $validatedData = $request->validate([
            'title' => 'string|max:255',
            'department' => 'string|max:255',
            'description' => 'nullable|string',
            'responsibilities' => 'nullable|string',
            'qualifications' => 'nullable|string',
            'job_type' => 'in:Full-time,Part-time,Remote,On-site',
            'salary_min' => 'nullable|numeric',
            'salary_max' => 'nullable|numeric',
            'application_deadline' => 'nullable|date',
            'status' => 'in:Published,Unpublished',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // ✅ Handle Image Upload (Delete Old Image First)
        if ($request->hasFile('image')) {
            if ($job->image) {
                Storage::delete('public/' . $job->image);
            }
            $imagePath = $request->file('image')->store('job_images', 'public');
            $validatedData['image'] = $imagePath;
        }

        $job->update($validatedData);

        return response()->json([
            'message' => 'Job updated successfully',
            'job' => $job
        ], Response::HTTP_OK);
    }

    // ✅ Delete a Job
    public function destroy($id) {
        $job = Job::findOrFail($id);
        
        // ✅ Delete Image if Exists
        if ($job->image) {
            Storage::delete('public/' . $job->image);
        }

        $job->delete();

        return response()->json(['message' => 'Job deleted successfully']);
    }
}
