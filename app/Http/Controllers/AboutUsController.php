<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AboutUs;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AboutUsController extends Controller
{
    public function show()
    {
        // ✅ Fetch the first "Published" record only
        return response()->json(AboutUs::where('status', 'Published')->first());
    }

    public function getAdminData()
    {
        return response()->json(AboutUs::first());
    }

    public function update(Request $request)
    {
        \Log::info('Received Update Request:', $request->all());

        $about = AboutUs::first();
        if (!$about) {
            return response()->json(['message' => 'Error: About Us record not found.'], 404);
        }

        // ✅ Validation for all fields
        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255',
            'established_year' => 'nullable|integer',
            'parent_company' => 'nullable|string|max:255',
            'company_slogan' => 'nullable|string|max:255',
            'brief_intro' => 'nullable|string',
            'mission_statement' => 'nullable|string',
            'vision_statement' => 'nullable|string',
            'our_story' => 'nullable|string',
            'milestones' => 'nullable|json',
            'evolution' => 'nullable|string',
            'real_estate_services' => 'nullable|json',
            'property_types' => 'nullable|json',
            'investment_opportunities' => 'nullable|json',
            'customer_segments' => 'nullable|json',
            'quality_innovation' => 'nullable|string',
            'prime_locations' => 'nullable|string',
            'affordability_financing' => 'nullable|string',
            'sustainability' => 'nullable|string',
            'awards' => 'nullable|string',
            'contact_address' => 'nullable|string',
            'phone_numbers' => 'nullable|json',
            'email_support' => 'nullable|string|email',
            'live_chat' => 'nullable|string',
            'social_media_links' => 'nullable|json',
            'status' => 'nullable|string|in:Published,Unpublished',
            // 'company_logo' => 'nullable|image|mimes:jpg,png,jpeg,svg|max:2048',
            'office_images.*' => 'nullable|image|mimes:jpg,png,jpeg|max:2048'
        ]);

        if ($validator->fails()) {
            \Log::error('Validation Failed:', $validator->errors()->toArray());
            return response()->json([
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // ✅ Handle Company Logo Upload
            // if ($request->hasFile('company_logo')) {
            //     $path = $request->file('company_logo')->store('uploads/about', 'public');
            //     $about->company_logo = $path;
            // }

            // ✅ Handle Multiple Office Images
            if ($request->hasFile('office_images')) {
                $images = [];
                foreach ($request->file('office_images') as $image) {
                    $images[] = $image->store('uploads/about', 'public');
                }
                $about->office_images = json_encode($images);
            }

            // ✅ Convert JSON fields before saving
            $jsonFields = ['milestones', 'real_estate_services', 'property_types', 'investment_opportunities', 'customer_segments', 'phone_numbers', 'social_media_links'];
            foreach ($jsonFields as $field) {
                if ($request->has($field)) {
                    $about->$field = json_encode($request->$field);
                }
            }

            // ✅ Update other text fields
            $about->update($request->only([
                'company_name', 'established_year', 'parent_company', 'company_slogan',
                'brief_intro', 'mission_statement', 'vision_statement', 'our_story',
                'evolution', 'quality_innovation', 'prime_locations', 'affordability_financing',
                // 'company_logo',
                 'office_images', 'milestone', 'real_estate_services', 'property_types',
                'sustainability', 'awards', 'contact_address','phone_numbers', 'email_support',
                'investment_opportunities','customer_segments', 'live_chat', 'social_media_links', 'status',
                'version_history'
            ]));

            return response()->json(['message' => 'Updated successfully'], 200);
        } catch (\Exception $e) {
            \Log::error('Update Error:', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function updateStatus(Request $request)
    {
        \Log::info('Received Status Update Request:', $request->all());

        $about = AboutUs::first();
        if (!$about) {
            return response()->json(['message' => 'Error: About Us record not found.'], 404);
        }

        // ✅ Validate status input
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:Published,Unpublished'
        ]);

        if ($validator->fails()) {
            \Log::error('Status Validation Failed:', $validator->errors()->toArray());
            return response()->json([
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $about->status = $request->status;
            $about->save();

            return response()->json(['message' => 'Status Updated Successfully'], 200);
        } catch (\Exception $e) {
            \Log::error('Status Update Error:', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
    
    public function revertVersion($version)
    {
        $about = AboutUs::first();
        $versionHistory = json_decode($about->version_history, true);
    
        if (isset($versionHistory[$version])) {
            $about->update($versionHistory[$version]);
            return response()->json(['message' => 'Version reverted successfully']);
        }
    
        return response()->json(['message' => 'Version not found'], 404);
    }
}
