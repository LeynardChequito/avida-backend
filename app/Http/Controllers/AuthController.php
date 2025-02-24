<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'phone_number' => 'required|string|max:20',
        'address' => 'required|string|max:255',
        'email' => 'required|email|max:255|unique:users',
        'password' => 'required|string|min:6',
        'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
    ]);

    // Process file upload
    $profilePhotoPath = null;
    if ($request->hasFile('profile_photo')) {
        $profilePhotoPath = $request->file('profile_photo')->store('profile_photos', 'public');
    }

    // Create user
    $user = User::create([
        'name' => $request->name,
        'phone_number' => $request->phone_number,
        'address' => $request->address,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'profile_photo' => $profilePhotoPath ? asset("storage/$profilePhotoPath") : null
    ]);

    // Generate JWT Token
    $token = JWTAuth::fromUser($user);

    return response()->json([
        'message' => 'User registered successfully',
        'token' => $token,
        'user' => $user
    ], 201);
}

public function updateProfile(Request $request)
{
    try {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'name' => 'string|max:255',
            'email' => 'email|max:255|unique:users,email,' . $user->id,
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:6',
            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('profile_photos', 'public');
            $user->profile_photo = asset("storage/$path");
        }

        $user->update([
            'name' => $request->name ?? $user->name,
            'email' => $request->email ?? $user->email,
            'phone_number' => $request->phone_number ?? $user->phone_number,
            'address' => $request->address ?? $user->address,
            'password' => $request->password ? Hash::make($request->password) : $user->password,
        ]);

        return response()->json(['message' => 'Profile updated successfully', 'user' => $user], 200);

    } catch (\Exception $e) {
        return response()->json(['error' => 'Profile update failed', 'message' => $e->getMessage()], 500);
    }
}


    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        // ✅ Manually fetch user and check password
        $user = User::where('email', $credentials['email'])->first();
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        // ✅ Generate JWT Token
        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Could not create token'], 401);
        }

        return response()->json(['token' => $token, 'user' => $user]);
    }

    public function user()
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            return response()->json($user, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            JWTAuth::invalidate(JWTAuth::parseToken()); // Invalidate the token
            return response()->json(['message' => 'Successfully logged out']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to logout'], 500);
        }
    }

}
