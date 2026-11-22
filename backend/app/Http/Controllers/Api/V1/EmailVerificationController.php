<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResendEmailOtpRequest;
use App\Http\Requests\Auth\VerifyEmailOtpRequest;
use App\Models\User;
use App\Services\EmailVerificationService;
use Illuminate\Http\JsonResponse;

class EmailVerificationController extends Controller
{
    public function verify(
        VerifyEmailOtpRequest $request,
        EmailVerificationService $verificationService,
    ): JsonResponse {
        $user = User::query()
            ->where('email', $request->validated('email'))
            ->first();

        if ($user === null) {
            return $this->verificationError(
                'Kode verifikasi tidak valid atau sudah kedaluwarsa.',
                'OTP_INVALID',
            );
        }

        $status = $verificationService->verify($user, $request->validated('code'));

        return match ($status) {
            EmailVerificationService::STATUS_VERIFIED => response()->json([
                'message' => 'Email berhasil diverifikasi. Silakan login.',
            ]),
            EmailVerificationService::STATUS_ALREADY_VERIFIED => $this->verificationError(
                'Email sudah terverifikasi. Silakan login.',
                'EMAIL_ALREADY_VERIFIED',
            ),
            EmailVerificationService::STATUS_EXPIRED => $this->verificationError(
                'Kode verifikasi sudah kedaluwarsa. Minta kode baru.',
                'OTP_EXPIRED',
            ),
            EmailVerificationService::STATUS_TOO_MANY_ATTEMPTS => $this->verificationError(
                'Terlalu banyak percobaan. Minta kode verifikasi baru.',
                'OTP_TOO_MANY_ATTEMPTS',
            ),
            default => $this->verificationError(
                'Kode verifikasi tidak valid atau sudah kedaluwarsa.',
                'OTP_INVALID',
            ),
        };
    }

    public function resend(
        ResendEmailOtpRequest $request,
        EmailVerificationService $verificationService,
    ): JsonResponse {
        $user = User::query()
            ->where('email', $request->validated('email'))
            ->first();

        if ($user === null || $user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Jika email terdaftar dan belum terverifikasi, kode baru akan dikirim.',
            ]);
        }

        $result = $verificationService->resend($user);

        if ($result['status'] === 'cooldown') {
            return response()->json([
                'message' => 'Tunggu sebelum meminta kode baru.',
                'code' => 'OTP_RESEND_COOLDOWN',
                'retry_after' => $result['retry_after'],
            ], 429);
        }

        if ($result['status'] === 'delivery_failed') {
            return response()->json([
                'message' => 'Kode baru dibuat, tetapi email gagal dikirim. Silakan coba lagi.',
                'code' => 'EMAIL_DELIVERY_FAILED',
                'retry_after' => (int) config(
                    'email_verification.resend_cooldown_seconds',
                    60,
                ),
            ], 503);
        }

        if ($result['status'] === EmailVerificationService::STATUS_ALREADY_VERIFIED) {
            return response()->json([
                'message' => 'Email sudah terverifikasi. Silakan login.',
            ]);
        }

        return response()->json([
            'message' => 'Kode verifikasi baru telah dikirim.',
            'retry_after' => $result['retry_after'] ?? 0,
        ]);
    }

    private function verificationError(string $message, string $code): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'code' => $code,
            'errors' => [
                'code' => [$message],
            ],
        ], 422);
    }
}
