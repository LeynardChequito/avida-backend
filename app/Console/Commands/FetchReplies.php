<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Webklex\IMAP\Facades\Client;
use App\Models\Inquiry;
use App\Models\InquiryReply;
class FetchReplies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-replies';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
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
                $message->setFlag('Seen'); // Mark as read
            }
        }

        $this->info("Email replies fetched successfully!");
    }
}
