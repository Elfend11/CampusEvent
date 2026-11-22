<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\UserRoleAssignment;
use App\Services\EmailVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(
        RegisterRequest $request,
        EmailVerificationService $verificationService,
    ): JsonResponse {
        [$user, $code] = DB::transaction(function () use ($request, $verificationService) {
            $user = User::create($request->safe()->only(['name', 'email', 'password']));

            UserRoleAssignment::create([
                'user_id' => $user->id,
                'role' => UserRole::STUDENT->value,
            ]);

            return [$user, $verificationService->prepare($user)];
        });

        $emailSent = $verificationService->deliver($user, $code);

        return response()->json([
            'message' => $emailSent
                ? 'Akun berhasil dibuat. Kode verifikasi telah dikirim ke email Anda.'
                : 'Akun berhasil dibuat, tetapi email verifikasi gagal dikirim. Silakan gunakan kirim ulang kode.',
            'data' => [
                'email' => $user->email,
                'requires_email_verification' => true,
                'email_sent' => $emailSent,
                'resend_after_seconds' => (int) config(
                    'email_verification.resend_cooldown_seconds',
                    60,
                ),
            ],
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        if (! Auth::guard('web')->validate($credentials)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        /** @var User $user */
        $user = User::query()->where('email', $credentials['email'])->firstOrFail();

        if (! $user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email belum diverifikasi. Verifikasi email terlebih dahulu sebelum login.',
                'code' => 'EMAIL_UNVERIFIED',
                'errors' => [
                    'email' => ['Email belum diverifikasi. Verifikasi email terlebih dahulu sebelum login.'],
                ],
            ], 403);
        }

        Auth::guard('web')->login($user);

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        return response()->json([
            'data' => new UserResource($user->load('roles')),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => new UserResource($request->user()->load('roles')),
        ]);
    }

    public function logout(Request $request): Response
    {
        Auth::guard('web')->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->noContent();
    }
}
