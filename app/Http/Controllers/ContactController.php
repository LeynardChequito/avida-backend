<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller {
    public function index() {
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
    
    

        // ✅ Update contact details (Admin)
        public function update(Request $request, $id) {
            $contact = Contact::findOrFail($id);
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
    
            $contact->update($request->all());
            return response()->json(['message' => 'Contact updated successfully']);
        }
}