<?php

return [

    'otp' => [
        'length' => (int) env('OTP_LENGTH', 4),
        'ttl_seconds' => (int) env('OTP_TTL_SECONDS', 300),
        'max_attempts' => (int) env('OTP_MAX_ATTEMPTS', 5),
    ],

    'email_otp' => [
        'length' => (int) env('OTP_EMAIL_LENGTH', 6),
        'ttl_seconds' => (int) env('OTP_EMAIL_TTL_SECONDS', 300),
        'max_attempts' => (int) env('OTP_EMAIL_MAX_ATTEMPTS', 5),
    ],

    /*
     * Fallback defaults used only the very first time SettingSeeder runs.
     * Once seeded, the `settings` table (managed from the admin panel) is
     * the single source of truth read by App\Services\OrderRuleService.
     */
    'order_defaults' => [
        'advance_order_days' => (int) env('DEFAULT_ADVANCE_ORDER_DAYS', 5),
        'order_cutoff_time' => env('DEFAULT_ORDER_CUTOFF_TIME', '20:00'),
    ],

];
