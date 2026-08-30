<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Log;

class LogSmsGateway implements SmsGateway
{
    public function send(string $mobile, string $message): bool
    {
        Log::channel('single')->info("[SMS to {$mobile}] {$message}");

        return true;
    }
}
