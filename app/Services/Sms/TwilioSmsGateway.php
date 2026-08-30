<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Production SMS driver stub. Wire real Twilio (or any provider) credentials
 * via TWILIO_SID / TWILIO_TOKEN / TWILIO_FROM in .env — never hardcode secrets.
 */
class TwilioSmsGateway implements SmsGateway
{
    public function send(string $mobile, string $message): bool
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $from = config('services.twilio.from');

        if (! $sid || ! $token || ! $from) {
            Log::warning('TwilioSmsGateway is not configured; SMS not sent.', ['mobile' => $mobile]);

            return false;
        }

        $response = Http::withBasicAuth($sid, $token)
            ->asForm()
            ->timeout(10)
            ->connectTimeout(5)
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                'To' => $mobile,
                'From' => $from,
                'Body' => $message,
            ]);

        return $response->successful();
    }
}
