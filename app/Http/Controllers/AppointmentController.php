<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Services\MailService;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone_number' => 'required|string',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required',
            'message' => 'nullable|string',
        ]);

        $appointment = Appointment::create($request->all());

        return response()->json(['message' => 'Appointment booked successfully!', 'appointment' => $appointment], 201);
    }

    public function index()
    {
        return response()->json(Appointment::orderBy('appointment_date', 'asc')->get());
    }

    public function updateStatus(Request $request, $id)
    {
        // Ensure only authenticated admins can perform this action
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized access'], 401);
        }

        $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled',
            'admin_message' => 'nullable|string'
        ]);

        $appointment = Appointment::findOrFail($id);
        $appointment->update([
            'status' => $request->status,
            'admin_message' => $request->admin_message
        ]);

        // Get the logged-in admin's email
        $admin = Auth::user();
        $email = $admin ? $admin->email : env('MAIL_FROM_ADDRESS');

        // Email subject and body
        $subject = "Your Appointment Status Has Been Updated";
        $body = "
            <h2>Hello " . $appointment->first_name . " " . $appointment->last_name . ",</h2>
            <p>Your appointment status has been updated to: <strong>" . ucfirst($appointment->status) . "</strong></p>
            <p><strong>Date:</strong> " . $appointment->appointment_date . "</p>
            <p><strong>Time:</strong> " . $appointment->appointment_time . "</p>
            <p><strong>Admin Message:</strong> " . ($appointment->admin_message ?: 'No additional message from admin.') . "</p>
            <p>If you have any questions, you can reply to this email or contact: <strong>$email</strong></p>
            <p>Thank you for using our service.</p>
        ";

        // Send email notification
        MailService::sendEmail($appointment->email, $subject, $body);

        return response()->json(['message' => 'Appointment status updated and user notified.']);
    }
    public function sendMessage(Request $request, $id)
{
    try {
        $appointment = Appointment::find($id);

        if (!$appointment) {
            return response()->json(['error' => 'Appointment not found'], 404);
        }

        // Validate input
        $request->validate([
            'admin_message' => 'required|string|max:500',
        ]);

        // Update admin message
        $appointment->admin_message = $request->admin_message;
        $appointment->save();

        // Prepare email
        $subject = "Admin Message Regarding Your Appointment";
        $body = '
<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background-color: #990e15;
            color: #ffffff;
            text-align: center;
            padding: 15px;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
            font-size: 20px;
            font-weight: bold;
        }
        .message {
            padding: 20px;
            color: #333;
            font-size: 16px;
            line-height: 1.6;
        }
        .footer {
            background-color: #990e15;
            color: #ffffff;
            text-align: center;
            padding: 10px;
            border-bottom-left-radius: 8px;
            border-bottom-right-radius: 8px;
            font-size: 14px;
        }
        .highlight {
            color: #990e15;
            font-weight: bold;
        }
        .contact-info {
            text-align: center;
            margin-top: 10px;
            font-size: 14px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">Appointment Notification</div>
        <div class="message">
            <p>Hello <span class="highlight">' . $appointment->first_name . ' ' . $appointment->last_name . '</span>,</p>
            <p>' . nl2br($request->admin_message) . '</p>
            <p>Thank you!</p>
        </div>
        <div class="footer">
            <p>&copy; ' . date("Y") . ' All rights reserved.</p>
        </div>
        <div class="contact-info">
            Need assistance? Contact us at <a href="mailto:support@yourcompany.com">support@yourcompany.com</a>
        </div>
    </div>
</body>
</html>
';

        // Send email using MailService
        $emailSent = MailService::sendEmail($appointment->email, $subject, $body);

        if (!$emailSent) {
            return response()->json(['error' => 'Email sending failed, but message was saved.'], 500);
        }

        return response()->json(['message' => 'Message sent successfully!'], 200);

    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

}
