<?php

namespace App\Services;

use App\Exceptions\OtpException;
use App\Models\Otp;
use App\Services\Sms\SmsGateway;
use Illuminate\Support\Facades\App;

class OtpService
{
    public function __construct(private readonly SmsGateway $sms) {}

    public function issue(string $mobile): array
    {
        $length = (int) config('dinelify.otp.length');
        $ttl = (int) config('dinelify.otp.ttl_seconds');

        $code = (string) random_int(
            (int) str_pad('1', $length, '0'),
            (int) str_pad('', $length, '9')
        );

        $otp = Otp::query()->create([
            'mobile' => $mobile,
            'otp_code' => $code,
            'expires_at' => now()->addSeconds($ttl),
            'attempts' => 0,
        ]);

        $this->sms->send($mobile, "Your Dinelify verification code is {$code}. It expires in ".intdiv($ttl, 60).' minutes.');

        return [
            'expires_in' => $ttl,
            'debug_otp' => App::environment('production') ? null : $code,
        ];
    }

    /**
     * @throws OtpException
     */
    public function verify(string $mobile, string $code): Otp
    {
        $otp = Otp::query()
            ->where('mobile', $mobile)
            ->whereNull('verified_at')
            ->latest('id')
            ->first();

        if (! $otp) {
            throw new OtpException('No pending OTP for this mobile number. Please request a new one.');
        }

        if ($otp->attempts >= (int) config('dinelify.otp.max_attempts')) {
            throw new OtpException('Too many attempts. Please request a new OTP.', 429);
        }

        if ($otp->isExpired()) {
            throw new OtpException('OTP has expired. Please request a new one.');
        }

        if (! hash_equals($otp->otp_code, $code)) {
            $otp->increment('attempts');

            throw new OtpException('Invalid OTP.');
        }

        $otp->update(['verified_at' => now()]);

        return $otp;
    }

    public function hasRecentlyVerified(string $mobile): bool
    {
        return Otp::query()
            ->where('mobile', $mobile)
            ->whereNotNull('verified_at')
            ->where('verified_at', '>=', now()->subMinutes(15))
            ->exists();
    }
}
