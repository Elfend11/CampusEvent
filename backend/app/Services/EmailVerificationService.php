<?php

namespace App\Services;

use App\Mail\EmailVerificationCode as EmailVerificationMail;
use App\Models\EmailVerificationCode;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class EmailVerificationService
{
    public const STATUS_VERIFIED = 'verified';
    public const STATUS_ALREADY_VERIFIED = 'already_verified';
    public const STATUS_INVALID = 'invalid';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_TOO_MANY_ATTEMPTS = 'too_many_attempts';
    public const STATUS_MISSING = 'missing';

    /**
     * Create a new OTP record and return the plaintext code for delivery.
     */
    public function prepare(User $user): string
    {
        $code = $this->generateCode();

        EmailVerificationCode::updateOrCreate(
            ['user_id' => $user->id],
            [
                'code_hash' => Hash::make($code),
                'attempts' => 0,
                'expires_at' => now()->addMinutes($this->expiresAfterMinutes()),
                'sent_at' => now(),
            ],
        );

        return $code;
    }

    /**
     * Send the OTP email. Failures are logged and reported without exposing the code.
     */
    public function deliver(User $user, string $code): bool
    {
        try {
            Mail::to($user)->send(new EmailVerificationMail($user, $code));

            EmailVerificationCode::query()
                ->where('user_id', $user->id)
                ->update(['sent_at' => now()]);

            return true;
        } catch (Throwable $exception) {
            Log::error('Gagal mengirim kode verifikasi email.', [
                'user_id' => $user->id,
                'exception' => $exception::class,
            ]);

            return false;
        }
    }

    /**
     * Verify an OTP and mark the account as verified.
     *
     * @return string One of the STATUS_* constants.
     */
    public function verify(User $user, string $code): string
    {
        if ($user->hasVerifiedEmail()) {
            return self::STATUS_ALREADY_VERIFIED;
        }

        return DB::transaction(function () use ($user, $code) {
            /** @var EmailVerificationCode|null $record */
            $record = EmailVerificationCode::query()
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if ($record === null) {
                return self::STATUS_MISSING;
            }

            if ($record->isExpired()) {
                $record->delete();

                return self::STATUS_EXPIRED;
            }

            if ($record->hasExceededAttempts()) {
                return self::STATUS_TOO_MANY_ATTEMPTS;
            }

            if (! Hash::check($code, $record->code_hash)) {
                $record->increment('attempts');
                $record->refresh();

                return $record->hasExceededAttempts()
                    ? self::STATUS_TOO_MANY_ATTEMPTS
                    : self::STATUS_INVALID;
            }

            $user->markEmailAsVerified();
            $record->delete();

            return self::STATUS_VERIFIED;
        });
    }

    /**
     * Issue and send a replacement OTP after the cooldown has elapsed.
     *
     * @return array{status: string, retry_after?: int}
     */
    public function resend(User $user): array
    {
        if ($user->hasVerifiedEmail()) {
            return ['status' => self::STATUS_ALREADY_VERIFIED];
        }

        $outcome = DB::transaction(function () use ($user) {
            /** @var EmailVerificationCode|null $record */
            $record = EmailVerificationCode::query()
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if ($record?->sent_at !== null) {
                $retryAfter = $this->remainingCooldownSeconds($record);

                if ($retryAfter > 0) {
                    return [
                        'status' => 'cooldown',
                        'retry_after' => $retryAfter,
                    ];
                }
            }

            return [
                'status' => 'issued',
                'code' => $this->prepare($user),
            ];
        });

        if ($outcome['status'] === 'cooldown') {
            return $outcome;
        }

        $sent = $this->deliver($user, $outcome['code']);

        return [
            'status' => $sent ? 'sent' : 'delivery_failed',
            'retry_after' => $sent ? $this->resendCooldownSeconds() : 0,
        ];
    }

    private function generateCode(): string
    {
        $length = max(4, (int) config('email_verification.code_length', 6));
        $maximum = (10 ** $length) - 1;

        return str_pad(
            (string) random_int(0, $maximum),
            $length,
            '0',
            STR_PAD_LEFT,
        );
    }

    private function remainingCooldownSeconds(EmailVerificationCode $record): int
    {
        $availableAt = $record->sent_at->copy()->addSeconds($this->resendCooldownSeconds());

        return max(0, $availableAt->getTimestamp() - now()->getTimestamp());
    }

    private function expiresAfterMinutes(): int
    {
        return max(1, (int) config('email_verification.expires_after_minutes', 10));
    }

    private function resendCooldownSeconds(): int
    {
        return max(0, (int) config('email_verification.resend_cooldown_seconds', 60));
    }
}
