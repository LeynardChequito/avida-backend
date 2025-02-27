<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Property;
use Illuminate\Support\Facades\Validator;

class PropertyController extends Controller
{
    // ✅ Submit Property
    public function submitProperty(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'email' => 'required|email',
                'phone_number' => [
                    'required',
                    'regex:/^(09\d{9}|\+639\d{9})$/', // ✅ Allows 09XXXXXXXXX or +639XXXXXXXXX
                ],
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
                'images' => 'required|array',
                'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:200048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed. Please check your inputs.',
                    'errors' => $validator->errors()
                ], 422);
            }
            

            // ✅ Store Images
            $imagePaths = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('property_images', 'public');
                    $imagePaths[] = $path;
                }
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
                'images' => json_encode($imagePaths),
                'status' => 'pending',
            ]);

            return response()->json(['message' => 'Property submitted successfully!', 'property' => $property], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Server Error: ' . $e->getMessage()], 500);
        }
    }

    // ✅ Get Single Property
    public function getProperty($id)
    {
        $property = Property::where('id', $id)->where('status', 'approved')->first();

        if (!$property) {
            return response()->json(['error' => 'Property not found or not approved'], 404);
        }

        // ✅ Convert images to correct URLs
        $property->images = array_map(fn($img) => asset("storage/" . $img), json_decode($property->images, true));

        return response()->json($property);
    }

    // ✅ Get All Published Properties
    public function getPublishedProperties()
{
    $properties = Property::where('status', 'approved')->orderBy('created_at', 'desc')->get();

    // Convert images to proper URLs
    foreach ($properties as $property) {
        $property->images = array_map(fn($img) => asset("storage/" . $img), json_decode($property->images, true));
    }

    return response()->json($properties, 200);
}
public function getAllProperties()
{
    return response()->json(Property::orderBy('created_at', 'desc')->get());
}


    public function updateApprovalStatus(Request $request, $id)
{
    try {
        $request->validate(['status' => 'required|in:pending,approved,rejected']);

        $property = Property::findOrFail($id);
        $property->status = $request->status;
        $property->save();

        return response()->json(['message' => 'Property status updated successfully!', 'property' => $property], 200);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Server Error: ' . $e->getMessage()], 500);
    }
}
}
