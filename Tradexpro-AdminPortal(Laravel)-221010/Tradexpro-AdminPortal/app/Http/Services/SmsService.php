<?php
namespace App\Http\Services;

use Aloha\Twilio\Twilio;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected $twilio;

    public function __construct()
    {
        $sid = allsetting('twillo_secret_key');
        $token = allsetting('twillo_auth_token');
        $from = allsetting('twillo_number');

        $this->twilio = new Twilio($sid, $token, $from);
    }

    public function send($number, $message)
    {
        try {
            $this->twilio->message($number, $message);
        } catch (\Exception $e) {
            Log::info('sms send problem -- '.$e->getMessage());
            return false;
        }

        return true;
    }
}
