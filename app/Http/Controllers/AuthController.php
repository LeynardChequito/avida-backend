<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;
use App\Services\MailService;

class AuthController extends Controller
{
    /**
     * Register new user (default role: admin for testing)
     */
    public function register(Request $request)
    {
        // Validate incoming request
        $request->validate([
            'name'           => 'required|string|max:255',
            'phone_number'   => 'required|string|max:20',
            'address'        => 'required|string|max:255',
            'email'          => 'required|email|max:255|unique:users',
            'password'       => 'required|string|min:6',
            'profile_photo'  => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        // Process profile photo if provided
        $profilePhotoPath = null;
        if ($request->hasFile('profile_photo')) {
            $profilePhotoPath = $request->file('profile_photo')->store('profile_photos', 'public');
        }

        // Generate a random token for email verification
        $verificationToken = Str::random(60);

        // Create the user record (here we force role 'admin' for testing)
        $user = User::create([
            'name'                    => $request->name,
            'phone_number'            => $request->phone_number,
            'address'                 => $request->address,
            'email'                   => $request->email,
            'password'                => Hash::make($request->password),
            'profile_photo'           => $profilePhotoPath ? asset("storage/$profilePhotoPath") : null,
            'role'                    => 'admin', // Set default role to admin (for testing)
            'email_verification_token' => $verificationToken
        ]);

        // Send verification email (adjust MailService accordingly)
        $verifyLink = env('APP_URL') . "/api/verify-email?token=" . $verificationToken;
        MailService::sendVerificationEmail($user->email, $user->name, $verifyLink);

        return response()->json([
            'message' => 'Registration successful. Please check your email to verify your account.',
            'user'    => $user
        ], 201);
    }

    /**
     * Email verification endpoint
     */
    public function verifyEmail(Request $request)
    {
        $user = User::where('email_verification_token', $request->token)->first();
    
        if (!$user) {
            return redirect(env('CORS_ALLOWED_ORIGINS') . '/auth/login?verified=fail');
        }
    
        // If already verified
        if ($user->email_verified_at) {
            return redirect(env('CORS_ALLOWED_ORIGINS') . '/auth/login?verified=already');
        }
    
        // Mark email as verified
        $user->email_verified_at = now();
        $user->email_verification_token = null;
        $user->save();
    
        return redirect(env('CORS_ALLOWED_ORIGINS') . '/auth/login?verified=success');
    }
    /**
     * Login and generate token
     */
    public function login(Request $request)
    {
        // Validate email and password are provided
        $credentials = $request->only('email', 'password');

        // Try to find the user by email
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        // Check if the user has verified their email
        if (!$user->email_verified_at) {
            return response()->json(['error' => 'Please verify your email before logging in.'], 403);
        }

        // Attempt to create a token using the credentials
        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Failed to create token'], 401);
        }

        return response()->json([
            'message' => 'Login successful',
            'token'   => $token,
            'user'    => $user
        ]);
    }

    /**
     * Get authenticated user
     */
    public function user()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            return response()->json($user, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $request->validate([
                'name'          => 'string|max:255',
                'email'         => 'email|max:255|unique:users,email,' . $user->id,
                'phone_number'  => 'nullable|string|max:20',
                'address'       => 'nullable|string|max:255',
                'password'      => 'nullable|string|min:6',
                'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
            ]);

            if ($request->hasFile('profile_photo')) {
                $path = $request->file('profile_photo')->store('profile_photos', 'public');
                $user->profile_photo = asset("storage/$path");
            }

            $user->update([
                'name'         => $request->name ?? $user->name,
                'email'        => $request->email ?? $user->email,
                'phone_number' => $request->phone_number ?? $user->phone_number,
                'address'      => $request->address ?? $user->address,
                'password'     => $request->password ? Hash::make($request->password) : $user->password,
            ]);

            return response()->json([
                'message' => 'Profile updated successfully',
                'user'    => $user
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'Profile update failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout and invalidate token
     */
    public function logout(Request $request)
    {
        try {
            JWTAuth::invalidate(JWTAuth::parseToken());

            return response()->json([
                'message' => 'Successfully logged out'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'Failed to logout',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
