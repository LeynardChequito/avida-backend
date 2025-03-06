<?php

namespace App\Http\Controllers;

use App\Models\Inquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\InquiryReply;
use Illuminate\Support\Facades\Mail;
use app\Services\MailService;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class InquiryController extends Controller {
    
    // ✅ Fetch all inquiries (Admin)
    public function index()
    {
        return response()->json(Inquiry::orderBy('created_at', 'desc')->get());
    }


    // ✅ Fetch single inquiry details
    public function show($id)
    {
        $inquiry = Inquiry::find($id);

        if (!$inquiry) {
            return response()->json(['message' => 'Inquiry not found'], 404);
        }

        return response()->json($inquiry);
    }


    // ✅ Store user inquiries
    public function store(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email',
                'phone' => 'required|string|max:20',
                'inquiry_type' => 'required|string',
                'message' => 'required|string|max:5000',
            ]);
    
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
    
            $inquiry = Inquiry::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'inquiry_type' => $request->inquiry_type,
                'message' => $request->message,
                'status' => 'pending',
            ]);
    
            return response()->json(['message' => 'Inquiry submitted successfully', 'inquiry' => $inquiry], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    // ✅ Update inquiry status (Admin)
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,on_process,done',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $inquiry = Inquiry::find($id);
        if (!$inquiry) {
            return response()->json(['message' => 'Inquiry not found'], 404);
        }

        $inquiry->status = $request->status;
        $inquiry->save();

        return response()->json(['message' => 'Inquiry status updated successfully']);
    }


    // ✅ Delete an inquiry (Admin)
    public function destroy($id)
    {
        $inquiry = Inquiry::find($id);
        if (!$inquiry) {
            return response()->json(['message' => 'Inquiry not found'], 404);
        }

        $inquiry->delete();
        return response()->json(['message' => 'Inquiry deleted successfully']);
    }

// ✅ Fetch inquiry with replies (Make sure replies are loaded correctly)
public function showWithReplies($id) {
    $inquiry = Inquiry::with('replies')->find($id);
    
    if (!$inquiry) {
        return response()->json(['message' => 'Inquiry not found'], 404);
    }

    return response()->json($inquiry);
}

public function reply(Request $request, $id) {
    $request->validate(['message' => 'required|string']);

    $inquiry = Inquiry::find($id);
    if (!$inquiry) {
        return response()->json(['error' => 'Inquiry not found'], 404);
    }

    try {
        // ✅ Store the reply in the database
        $reply = InquiryReply::create([
            'inquiry_id' => $id,
            'email' => $inquiry->email,
            'message' => $request->message,
            'sender' => 'Admin',
        ]);

        // ✅ Send Professional Email Using PHPMailer
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = env('MAIL_HOST');
        $mail->SMTPAuth   = true;
        $mail->Username   = env('MAIL_USERNAME');
        $mail->Password   = env('MAIL_PASSWORD');
        $mail->SMTPSecure = env('MAIL_ENCRYPTION');
        $mail->Port       = env('MAIL_PORT');
        $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
        $mail->addAddress($inquiry->email);
        $mail->Subject = "RE: Your Real Estate Inquiry | Avida Land Customer Service";
        $mail->isHTML(true);

        // ✅ Professional Email Content
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px; background-color: #f9f9f9;'>
                <h2 style='color: #007BFF;'>Hello, {$inquiry->first_name} {$inquiry->last_name}</h2>
                <p>Thank you for reaching out to <strong>Avida Land Customer Service</strong>. We appreciate your interest and inquiry.</p>
                
                <h3 style='color: #007BFF;'>Our Response:</h3>
                <p style='background: #f3f3f3; padding: 15px; border-left: 5px solid #007BFF;'>
                    {$request->message}
                </p>

                <p>Should you have any further questions, please feel free to reply to this email or contact us at <a href='mailto:support@avidaland.com'>support@avidaland.com</a>.</p>

                <br>
                <p>Best regards,</p>
                <p><strong>Avida Land Customer Support Team</strong></p>
                <p style='font-size: 12px; color: #888;'>This is an automated message. Please do not reply directly to this email.</p>
            </div>
        ";

        $mail->send();

        return response()->json(['message' => 'Reply sent successfully!']);
    } catch (Exception $e) {
        \Log::error("Email sending failed: " . $mail->ErrorInfo);
        return response()->json(['error' => 'Failed to send email. Check logs.'], 500);
    }
}

    public function receiveUserReply()
    {
        $client = Client::account('default');
        $client->connect();
    
        $inbox = $client->getFolder('INBOX');
        $messages = $inbox->messages()->unseen()->get();
    
        foreach ($messages as $message) {
            $email = $message->getFrom()[0]->mail;
            $text = $message->getTextBody();
    
            $inquiry = Inquiry::where('email', $email)->first();
            if ($inquiry) {
                InquiryReply::create([
                    'inquiry_id' => $inquiry->id,
                    'email' => $email,
                    'message' => $text,
                    'sender' => 'User',
                ]);
                $message->setFlag('Seen'); // Mark email as read
            }
        }
    
        return response()->json(['message' => 'User replies fetched successfully!']);
    }
    
}
