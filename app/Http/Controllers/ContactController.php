<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller {
    public function publicIndex()
    {
        return response()->json(
            Contact::select(
                'address',
                'main_phone',
                'sales_phone',
                'leasing_phone',
                'employment_phone',
                'customer_care_phone',
                'email',
                'facebook_link',
                'instagram_link',
                'youtube_link',
                'linkedin_link',
                'tiktok_link'
            )->get()
        );
    }

    // ✅ Admin: Full access
    public function adminIndex()
    {
        return response()->json(Contact::all());
    }

    public function store(Request $request) {
        $request->validate([
            'name' => 'required|string',
            'address' => 'required|string',
            'main_phone' => 'nullable|string',
            'sales_phone' => 'nullable|string',
            'leasing_phone' => 'nullable|string',
            'employment_phone' => 'nullable|string',
            'customer_care_phone' => 'nullable|string',
            'customer_care_landline' => 'nullable|string',
            'email' => 'required|email',
            'support_email' => 'nullable|email',
            'business_hours' => 'nullable|string',
            'facebook_link' => 'nullable|url',
            'instagram_link' => 'nullable|url',
            'youtube_link' => 'nullable|url',
            'linkedin_link' => 'nullable|url',
            'tiktok_link' => 'nullable|url',
        ]);
    
        $contact = Contact::create($request->all());
        return response()->json($contact, 201);
    }
    
    
    public function update(Request $request, $id) {
        $contact = Contact::findOrFail($id);
    
        $request->validate([
            'name' => 'required|string',
            'address' => 'required|string',
            'email' => 'required|email',
        ]);
    
        // Merge with existing data to avoid missing fields
        $updatedData = array_merge($contact->toArray(), $request->all());
    
        $contact->update($updatedData);
        return response()->json(['message' => 'Contact updated successfully']);
    }
    public function destroy($id) {
        $contact = Contact::findOrFail($id); // Find the contact or return 404
        $contact->delete(); // Delete the contact
        return response()->json(['message' => 'Contact deleted successfully'], 200);
    }
    
}