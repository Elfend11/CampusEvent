<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Email Verification OTP
    |--------------------------------------------------------------------------
    |
    | New accounts must verify their email address before they can log in.
    | Only a hash of the six-digit code is stored. The code expires after the
    | configured number of minutes and can be resent after the cooldown.
    |
    */

    'code_length' => 6,

    'expires_after_minutes' => (int) env('EMAIL_OTP_EXPIRES_MINUTES', 10),

    'resend_cooldown_seconds' => (int) env('EMAIL_OTP_RESEND_COOLDOWN_SECONDS', 60),

    'max_attempts' => (int) env('EMAIL_OTP_MAX_ATTEMPTS', 5),

];
