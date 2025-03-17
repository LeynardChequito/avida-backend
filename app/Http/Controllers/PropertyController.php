<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Property;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\PropertyMedia;

class PropertyController extends Controller
{
    // ✅ Submit Property (Supports Panolens & Lightbox2 separately)
    public function submitProperty(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'email' => 'required|email',
                'phone_number' => ['required', 'regex:/^(09\d{9}|\+639\d{9})$/'],
                'type' => 'required|in:Owner,Agent,Broker',
                'property_name' => 'required|string',
                'location' => 'required|string',
                'unit_type' => 'required|in:Studio Room,1BR,2BR,3BR,Loft,Penthouse',
                'unit_status' => 'required|in:Bare,Semi-Furnished,Fully-Furnished,Interiored',
                'price' => 'required|numeric',
                'square_meter' => 'required|numeric',
                'floor_number' => 'required|integer',
                'parking' => 'required|in:With Parking,No Parking',
                'property_status' => 'required|in:For Rent,For Sale',
                'features_amenities' => 'required|json',

                // ✅ Separate inputs for Panolens and Lightbox2
                'panolens_images' => 'nullable|array',
                'panolens_images.*' => 'nullable|mimes:jpeg,png,jpg,webp|max:50120',

                'lightbox2_media' => 'nullable|array',
                'lightbox2_media.*' => 'nullable|mimes:jpeg,png,jpg,webp,mp4,mov,avi,mkv|max:512000',

                'virtual_tour_link' => 'nullable|url',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Validation failed.', 
                    'errors' => $validator->errors()
                ], 422);
            }

            // ✅ Create Property Entry
            $property = Property::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'type' => $request->type,
                'property_name' => $request->property_name,
                'location' => $request->location,
                'unit_type' => $request->unit_type,
                'unit_status' => $request->unit_status,
                'price' => $request->price,
                'square_meter' => $request->square_meter,
                'floor_number' => $request->floor_number,
                'parking' => $request->parking,
                'property_status' => $request->property_status,
                'features_amenities' => json_encode(json_decode($request->features_amenities, true)),
                'virtual_tour_link' => $request->virtual_tour_link,
                'status' => 'pending',
            ]);

            // ✅ Store media separately
            $this->storePanolensImages($request, $property->id);
            $this->storeLightboxMedia($request, $property->id);

            return response()->json([
                'message' => 'Property submitted successfully!',
                'property' => $property->load('media')
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Server Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ Store Panolens (360° Images)
     */
    private function storePanolensImages($request, $propertyId)
    {
        if ($request->hasFile('panolens_images')) {
            foreach ($request->file('panolens_images') as $file) {
                $path = $file->store("property_panorama_images/{$propertyId}", 'public');
    
                // ✅ Store only the relative path (not the full URL)
                PropertyMedia::create([
                    'property_id' => $propertyId,
                    'url' => $path, // ✅ Only store the relative storage path
                    'type' => '360',
                ]);
            }
        }
    }
    
    /**
     * ✅ Store Lightbox2 (Normal Images & Videos)
     */
    private function storeLightboxMedia($request, $propertyId)
    {
        if ($request->hasFile('lightbox2_media')) {
            foreach ($request->file('lightbox2_media') as $file) {
                $extension = $file->getClientOriginalExtension();
                $type = in_array($extension, ['mp4', 'mov', 'avi', 'mkv']) ? 'video' : 'image';
                $path = $file->store("property_lightbox_media/{$propertyId}", 'public');
    
                // ✅ Store only the relative path (not the full URL)
                PropertyMedia::create([
                    'property_id' => $propertyId,
                    'url' => $path, // ✅ Only store the relative storage path
                    'type' => $type,
                ]);
            }
        }
    }
    
    /**
     * ✅ Get Single Property with Media
     */
    public function getProperty($id, Request $request)
    {
        $status = $request->query('status', 'approved');
        $property = Property::where('id', $id)
            ->where('status', $status)
            ->with('media')
            ->first();
    
        if (!$property) {
            return response()->json(['error' => 'Property not found or not approved'], 404);
        }
    
        // ✅ Convert relative paths to full URLs
        foreach ($property->media as $media) {
            if (!str_starts_with($media->url, 'http')) {
                $media->url = asset('storage/' . ltrim($media->url, '/'));
            }
        }
    
        return response()->json($property);
    }
    
    // ✅ 5. Get All Published Properties (Approved Only)
    public function getPublishedProperties()
    {
        try {
            $properties = Property::where('status', 'approved')
                ->with('media')
                ->orderBy('created_at', 'desc')
                ->get();
    
            // ✅ Convert storage paths to full URLs
            foreach ($properties as $property) {
                foreach ($property->media as $media) {
                    if (!str_starts_with($media->url, 'http')) {
                        $media->url = asset('storage/' . ltrim($media->url, '/'));
                    }
                }
            }
    
            return response()->json($properties, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Server Error: ' . $e->getMessage()], 500);
        }
    }
    
    public function getAllProperties()
    {
        try {
            $properties = Property::with('media')->orderBy('created_at', 'desc')->get();
    
            // ✅ Convert relative paths to full URLs
            $properties->each(function ($property) {
                foreach ($property->media as $media) {
                    if (!str_starts_with($media->url, 'http')) {
                        $media->url = asset('storage/' . $media->url);
                    }
                }
            });
    
            return response()->json($properties, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    // ✅ 6. Delete Property
    public function deleteProperty($id)
    {
        try {
            $property = Property::findOrFail($id);
    
            // ✅ Delete associated media files from storage
            foreach ($property->media as $file) {
                $filePath = str_replace('storage/', '', $file->url);
                Storage::disk('public')->delete($filePath);
            }
    
            // ✅ Delete from database
            $property->delete();
    
            return response()->json(['message' => 'Property deleted successfully!'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Server Error: ' . $e->getMessage()], 500);
        }
    }
    
/**
 * ✅ Store Media Files (Images, Videos, 360° Panorama)
 */
private function storeMediaFiles($request, $propertyId)
{
    $mediaTypes = [
        'images' => 'image',
        'panorama_images' => '360',
        'videos' => 'video'
    ];

    foreach ($mediaTypes as $inputName => $type) {
        if ($request->hasFile($inputName)) {
            foreach ($request->file($inputName) as $file) {
                $path = $file->store("property_images/{$propertyId}", 'public');

                // ✅ Save to `property_media` table
                PropertyMedia::create([
                    'property_id' => $propertyId,
                    'url' => $path,
                    'type' => $type,
                ]);
            }
        }
    }
}
public function updateApprovalStatus(Request $request, $id)
{
    try {
        // ✅ Validate request input
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,approved,rejected',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        // ✅ Find the property
        $property = Property::findOrFail($id);
        $property->status = $request->status;
        $property->save();

        // ✅ Ensure media URLs are correctly formatted
        foreach ($property->media as &$media) {
            $media->url = asset('storage/' . $media->url);
        }

        return response()->json([
            'message' => 'Property status updated successfully!',
            'property' => $property
        ], 200);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Server Error: ' . $e->getMessage()], 500);
    }
}

    // ✅ Delete Media Files (Helper Function)
    private function deleteMediaFiles($images)
    {
        $mediaFiles = json_decode($images, true) ?? [];
        foreach ($mediaFiles as $file) {
            $filePath = str_replace('/storage/', '', $file['url']);
            Storage::disk('public')->delete($filePath);
        }
    }
}
