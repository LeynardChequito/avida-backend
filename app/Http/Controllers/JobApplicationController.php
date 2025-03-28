<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JobApplication;
use App\Services\MailService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class JobApplicationController extends Controller
{
    public function index()
    {
        return response()->json(JobApplication::with('job')->latest()->get());
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'job_id' => 'required|exists:jobs,id',
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone_number' => 'required|string|max:20',
            'cover_letter' => 'nullable|string',
            'resume' => 'required|mimes:pdf,doc,docx|max:10240', // ✅ 10MB max
            'linkedin_url' => 'nullable|url|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        if ($request->hasFile('resume')) {
            $resumeFile = $request->file('resume');
            $fileName = time() . '_' . $resumeFile->getClientOriginalName(); // Unique filename
            $resumePath = $resumeFile->storeAs('resumes', $fileName, 'public'); // Store in `storage/app/public/resumes`
            
            // Generate public URL for frontend
            $publicResumePath = asset('storage/resumes/' . $fileName);
        } else {
            return response()->json(['message' => 'Resume file is required'], 422);
        }
        
        // Save the application with the correct resume path
        $application = JobApplication::create([
            'job_id' => $request->job_id,
            'full_name' => $request->full_name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'cover_letter' => $request->cover_letter,
            'resume' => $publicResumePath, // ✅ Save public URL for frontend
            'linkedin_url' => $request->linkedin_url,
            'status' => 'Pending'
        ]);
        
        return response()->json([
            'message' => 'Application submitted successfully!',
            'application' => $application
        ], 201);
        
    }

    /**
     * Update the job application status and send an email notification.
     */
    public function updateStatus(Request $request, $id)
    {
        \Log::info("updateStatus triggered for Job Application ID: " . $id);
    
        $validatedData = $request->validate([
            'status' => 'required|in:Pending,Reviewed,Shortlisted,Rejected',
            'admin_message' => 'nullable|string'
        ]);
    
        $application = JobApplication::find($id);
        if (!$application) {
            \Log::error("Job Application ID " . $id . " not found.");
            return response()->json(['error' => 'Application not found'], 404);
        }
    
        $application->status = $validatedData['status'];
        if ($request->has('admin_message')) {
            $application->admin_message = $validatedData['admin_message'];
        }
        $application->save();
    
        \Log::info("Job Application ID " . $id . " updated to status: " . $application->status);
    
        // ✅ Log Before Sending Email
        \Log::info("Attempting to send email to: " . $application->email);
    
        $subject = "Your Job Application Status Has Been Updated";
        $body = "
            <h2>Hello {$application->full_name},</h2>
            <p>Your job application status has been updated to: <strong>{$application->status}</strong></p>
            <p><strong>Job Title:</strong> {$application->job->title}</p>
            <p><strong>Admin Message:</strong> " . ($application->admin_message ?: 'No additional message from admin.') . "</p>
            <p>Thank you for applying!</p>
        ";
    
        // ✅ Send Email and Log Success or Failure
        $emailSent = MailService::sendEmail($application->email, $subject, $body);
    
        if ($emailSent) {
            \Log::info("Email successfully sent to: " . $application->email);
        } else {
            \Log::error("Failed to send email to: " . $application->email);
        }
    
        return response()->json(['message' => 'Status updated and email notification attempted.'], 200);
    }
    
    /**
     * Admin can send a personalized email response to the applicant.
     */
    public function sendReply(Request $request, $id) {
        \Log::info("sendReply method triggered for Job Application ID: " . $id); // ✅ Log the request
    
        $application = JobApplication::find($id);
    
        if (!$application) {
            \Log::error("Application not found for ID: " . $id); // ✅ Log if application is missing
            return response()->json(['error' => 'Application not found'], 404);
        }
    
        $request->validate([
            'admin_reply' => 'required|string|max:1000',
        ]);
    
        // Save reply
        $application->admin_reply = $request->admin_reply;
        $application->save();
        
        \Log::info("Admin reply saved: " . $application->admin_reply); // ✅ Log reply content
    
        // Send email using MailService
        $subject = "Update on Your Job Application";
        $body = "
            <h2>Hello {$application->full_name},</h2>
            <p>Your application for the <strong>{$application->job->title}</strong> position has been updated.</p>
            <p><strong>Current Status:</strong> <span style='color: blue;'>{$application->status}</span></p>
            <p><strong>Admin Reply:</strong></p>
            <p style='border-left: 4px solid #990e15; padding-left: 10px; color: #333;'>{$application->admin_reply}</p>
            <br>
            <p>If you have any questions, feel free to reply to this email.</p>
            <p>Best Regards,</p>
            <p><strong>Avida Careers Team</strong></p>
        ";
    
        $emailSent = MailService::sendEmail($application->email, $subject, $body);
    
        if ($emailSent) {
            \Log::info("Email successfully sent to: " . $application->email); // ✅ Log email success
        } else {
            \Log::error("Failed to send email to: " . $application->email); // ✅ Log email failure
        }
    
        return response()->json(['message' => 'Reply sent and email notification attempted.'], 200);
    }
    
    /**
     * Delete a job application.
     */
    public function destroy($id)
    {
        $application = JobApplication::findOrFail($id);
        $application->delete();

        return response()->json(['message' => 'Application deleted successfully']);
    }
}
