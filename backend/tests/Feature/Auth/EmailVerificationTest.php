<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Mail\EmailVerificationCode as EmailVerificationMail;
use App\Models\EmailVerificationCode as EmailVerificationCodeModel;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withHeader('Origin', 'http://localhost:8000');
    }

    private function registerNewUser(string $email = 'otp@example.test'): TestResponse
    {
        Mail::fake();

        return $this->postJson('/api/v1/auth/register', [
            'name' => 'Mahasiswa OTP',
            'email' => $email,
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);
    }

    private function captureCode(string $email): string
    {
        $captured = null;

        Mail::assertSent(EmailVerificationMail::class, function (EmailVerificationMail $mail) use ($email, &$captured) {
            if (! $mail->hasTo($email)) {
                return false;
            }

            $captured = $mail->code;

            return true;
        });

        $this->assertIsString($captured);

        return $captured;
    }

    private function userWithRole(UserRole $role, array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        UserRoleAssignment::create(['user_id' => $user->id, 'role' => $role->value]);

        return $user;
    }

    private function wrongCode(string $code): string
    {
        return str_pad((string) (((int) $code + 1) % 1000000), 6, '0', STR_PAD_LEFT);
    }

    public function test_otp_is_sent_and_stored_only_as_a_hash(): void
    {
        $this->registerNewUser()->assertCreated();

        $code = $this->captureCode('otp@example.test');
        $user = User::where('email', 'otp@example.test')->firstOrFail();
        $record = EmailVerificationCodeModel::where('user_id', $user->id)->firstOrFail();

        $this->assertMatchesRegularExpression('/^\d{6}$/', $code);
        $this->assertNotSame($code, $record->code_hash);
        $this->assertTrue(Hash::check($code, $record->code_hash));
        $this->assertTrue($record->expires_at->isFuture());
        $this->assertNotNull($record->sent_at);
        $this->assertGuest();
    }

    public function test_valid_otp_verifies_the_account_without_auto_login(): void
    {
        $this->registerNewUser()->assertCreated();
        $code = $this->captureCode('otp@example.test');

        $this->postJson('/api/v1/auth/verify-otp', [
            'email' => 'otp@example.test',
            'code' => $code,
        ])->assertOk();

        $user = User::where('email', 'otp@example.test')->firstOrFail();

        $this->assertNotNull($user->email_verified_at);
        $this->assertDatabaseMissing('email_verification_codes', ['user_id' => $user->id]);
        $this->assertGuest();
    }

    public function test_invalid_otp_increments_attempts_and_does_not_verify(): void
    {
        $this->registerNewUser()->assertCreated();
        $code = $this->captureCode('otp@example.test');

        $this->postJson('/api/v1/auth/verify-otp', [
            'email' => 'otp@example.test',
            'code' => $this->wrongCode($code),
        ])
            ->assertStatus(422)
            ->assertJsonPath('code', 'OTP_INVALID');

        $user = User::where('email', 'otp@example.test')->firstOrFail();
        $record = EmailVerificationCodeModel::where('user_id', $user->id)->firstOrFail();

        $this->assertNull($user->email_verified_at);
        $this->assertSame(1, $record->attempts);
    }

    public function test_expired_otp_is_rejected_and_removed(): void
    {
        $this->registerNewUser()->assertCreated();
        $code = $this->captureCode('otp@example.test');

        $this->travel(11)->minutes();

        $this->postJson('/api/v1/auth/verify-otp', [
            'email' => 'otp@example.test',
            'code' => $code,
        ])
            ->assertStatus(422)
            ->assertJsonPath('code', 'OTP_EXPIRED');

        $user = User::where('email', 'otp@example.test')->firstOrFail();

        $this->assertNull($user->email_verified_at);
        $this->assertDatabaseMissing('email_verification_codes', ['user_id' => $user->id]);
    }

    public function test_otp_is_rejected_after_maximum_attempts(): void
    {
        $this->registerNewUser()->assertCreated();
        $code = $this->captureCode('otp@example.test');
        $wrongCode = $this->wrongCode($code);

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->postJson('/api/v1/auth/verify-otp', [
                'email' => 'otp@example.test',
                'code' => $wrongCode,
            ])->assertStatus(422);
        }

        $this->postJson('/api/v1/auth/verify-otp', [
            'email' => 'otp@example.test',
            'code' => $code,
        ])
            ->assertStatus(422)
            ->assertJsonPath('code', 'OTP_TOO_MANY_ATTEMPTS');

        $user = User::where('email', 'otp@example.test')->firstOrFail();
        $record = EmailVerificationCodeModel::where('user_id', $user->id)->firstOrFail();

        $this->assertNull($user->email_verified_at);
        $this->assertSame(5, $record->attempts);
    }

    public function test_resend_is_blocked_during_cooldown_and_issues_a_new_code_afterwards(): void
    {
        $this->registerNewUser()->assertCreated();
        $firstCode = $this->captureCode('otp@example.test');

        $this->travel(30)->seconds();

        $this->postJson('/api/v1/auth/resend-otp', ['email' => 'otp@example.test'])
            ->assertStatus(429)
            ->assertJsonPath('code', 'OTP_RESEND_COOLDOWN')
            ->assertJsonPath('retry_after', 30);

        $this->travel(31)->seconds();

        $this->postJson('/api/v1/auth/resend-otp', ['email' => 'otp@example.test'])
            ->assertOk()
            ->assertJsonPath('retry_after', 60);

        $sent = Mail::sent(EmailVerificationMail::class);

        $this->assertCount(2, $sent);
        $this->assertNotSame($firstCode, $sent[1]->code);

        $user = User::where('email', 'otp@example.test')->firstOrFail();
        $record = EmailVerificationCodeModel::where('user_id', $user->id)->firstOrFail();

        $this->assertSame(0, $record->attempts);
        $this->assertTrue(Hash::check($sent[1]->code, $record->code_hash));

        $this->postJson('/api/v1/auth/verify-otp', [
            'email' => 'otp@example.test',
            'code' => $sent[1]->code,
        ])->assertOk();

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_resend_endpoint_is_rate_limited(): void
    {
        $user = $this->userWithRole(UserRole::STUDENT);

        for ($request = 1; $request <= 5; $request++) {
            $this->postJson('/api/v1/auth/resend-otp', ['email' => $user->email])
                ->assertOk();
        }

        $this->postJson('/api/v1/auth/resend-otp', ['email' => $user->email])
            ->assertStatus(429);
    }

    public function test_registration_endpoint_is_rate_limited(): void
    {
        Mail::fake();

        for ($request = 1; $request <= 5; $request++) {
            $this->postJson('/api/v1/auth/register', [
                'name' => "Mahasiswa {$request}",
                'email' => "rate.limit.{$request}@example.test",
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
            ])->assertCreated();
        }

        $this->postJson('/api/v1/auth/register', [
            'name' => 'Mahasiswa Terlalu Banyak',
            'email' => 'rate.limit.6@example.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertStatus(429);
    }

    public function test_unverified_user_cannot_login(): void
    {
        $this->registerNewUser('unverified@example.test')->assertCreated();

        $this->postJson('/api/v1/auth/login', [
            'email' => 'unverified@example.test',
            'password' => 'Password123!',
        ])
            ->assertStatus(403)
            ->assertJsonPath('code', 'EMAIL_UNVERIFIED');

        $this->assertGuest();
    }

    public function test_verified_user_can_login_without_otp(): void
    {
        $user = $this->userWithRole(UserRole::STUDENT, [
            'email' => 'verified@example.test',
            'password' => 'Password123!',
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'verified@example.test',
            'password' => 'Password123!',
        ])->assertOk();

        $this->assertAuthenticatedAs($user);
    }

    public function test_backfill_migration_does_not_verify_existing_student_account(): void
    {
        $user = $this->userWithRole(UserRole::STUDENT, [
            'email' => 'legacy@example.test',
            'password' => 'Password123!',
            'email_verified_at' => null,
        ]);

        $migration = require database_path('migrations/2026_11_22_000001_backfill_email_verified_at.php');
        $migration->up();

        $this->assertNull($user->fresh()->email_verified_at);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'legacy@example.test',
            'password' => 'Password123!',
        ])
            ->assertStatus(403)
            ->assertJsonPath('code', 'EMAIL_UNVERIFIED');
    }

    public function test_email_delivery_failure_keeps_account_pending_and_consistent(): void
    {
        Mail::shouldReceive('to')
            ->once()
            ->andThrow(new \RuntimeException('SMTP unavailable'));

        $this->postJson('/api/v1/auth/register', [
            'name' => 'Mahasiswa Gagal Email',
            'email' => 'mail.failure@example.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])
            ->assertCreated()
            ->assertJsonPath('data.email_sent', false)
            ->assertJsonPath('data.requires_email_verification', true)
            ->assertJsonPath('data.resend_after_seconds', 60);

        $user = User::where('email', 'mail.failure@example.test')->firstOrFail();

        $this->assertNull($user->email_verified_at);
        $this->assertDatabaseHas('email_verification_codes', ['user_id' => $user->id]);
        $this->assertNotNull($user->emailVerificationCode()->firstOrFail()->sent_at);
        $this->assertGuest();
    }

    public function test_resend_after_delivery_failure_still_applies_cooldown(): void
    {
        Mail::shouldReceive('to')
            ->once()
            ->andThrow(new \RuntimeException('SMTP unavailable'));

        $this->postJson('/api/v1/auth/register', [
            'name' => 'Mahasiswa Resend Gagal',
            'email' => 'resend.failure@example.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])
            ->assertCreated()
            ->assertJsonPath('data.email_sent', false)
            ->assertJsonPath('data.resend_after_seconds', 60);

        $this->travel(61)->seconds();

        Mail::shouldReceive('to')
            ->once()
            ->andThrow(new \RuntimeException('SMTP unavailable'));

        $this->postJson('/api/v1/auth/resend-otp', [
            'email' => 'resend.failure@example.test',
        ])
            ->assertStatus(503)
            ->assertJsonPath('code', 'EMAIL_DELIVERY_FAILED')
            ->assertJsonPath('retry_after', 60);

        $this->postJson('/api/v1/auth/resend-otp', [
            'email' => 'resend.failure@example.test',
        ])
            ->assertStatus(429)
            ->assertJsonPath('code', 'OTP_RESEND_COOLDOWN');
    }

    public function test_verify_endpoint_validates_six_digit_code(): void
    {
        $this->postJson('/api/v1/auth/verify-otp', [
            'email' => 'otp@example.test',
            'code' => '123',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('code');
    }
}
