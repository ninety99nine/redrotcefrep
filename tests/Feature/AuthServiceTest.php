<?php

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Enums\SystemRole;
use Illuminate\Support\Str;
use App\Mail\UserRegistered;
use App\Services\AuthService;
use App\Services\UssdService;
use App\Mail\PasswordResetLink;
use App\Mail\VerifyUpdatedEmail;
use App\Services\MediaFileService;
use Illuminate\Support\Facades\DB;
use App\Services\PricingPlanService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use App\Enums\EmailVerificationType;
use Illuminate\Support\Facades\Event;
use App\Mail\VerifyRegistrationEmail;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    Mail::fake();
});

// LOGIN

test('user can login with email and password', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'password' => bcrypt('password123'),
        'email_verified_at' => now(),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'type' => 'email',
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['token', 'message'])
        ->assertJson(['message' => 'Login successful']);
});

test('login fails with non existent email', function () {
    $response = $this->postJson('/api/auth/login', [
        'type' => 'email',
        'email' => 'nonexistent@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email' => 'This account does not exist.']);
});

test('login fails with invalid email', function () {
    User::factory()->create([
        'email' => '1234',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'password' => bcrypt('password123'),
        'email_verified_at' => now(),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'type' => 'email',
        'email' => '1234',
        'password' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email' => 'Please provide a valid email address.']);
});

test('login fails with empty email', function () {
    User::factory()->create([
        'email' => 'test@example.com',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'password' => bcrypt('password123'),
        'email_verified_at' => now(),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'type' => 'email',
        'email' => '',
        'password' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email' => 'The email field is required.']);
});

test('login fails with email but no database password set', function () {
    User::factory()->create([
        'email' => 'test@example.com',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'password' => null,
    ]);

    $response = $this->postJson('/api/auth/login', [
        'type' => 'email',
        'password' => 'password123',
        'email' => 'test@example.com',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email' => 'This account does not have a password set.']);
});

test('login fails with email but empty password', function () {
    User::factory()->create([
        'email' => 'test@example.com',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'type' => 'email',
        'password' => '',
        'email' => 'test@example.com',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password' => 'The password field is required.']);
});

test('login fails with email but missing password', function () {
    User::factory()->create([
        'email' => 'test@example.com',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'type' => 'email',
        'email' => 'test@example.com',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password' => 'The password field is required.']);
});

test('login fails with email but incorrect password', function () {
    User::factory()->create([
        'email' => 'test@example.com',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'password' => bcrypt('password123'),
        'email_verified_at' => now(),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'type' => 'email',
        'email' => 'test@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email' => 'The provided credentials are incorrect.']);
});

test('user can login with mobile number and password', function () {
    $user = User::factory()->create([
        'mobile_number' => '+26772000001',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'type' => 'mobile_number',
        'mobile_number' => '+26772000001',
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['token', 'message'])
        ->assertJson(['message' => 'Login successful']);
});

test('login fails with non existent mobile number', function () {
    $response = $this->postJson('/api/auth/login', [
        'type' => 'mobile_number',
        'mobile_number' => '+26772000001',
        'password' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['mobile_number' => 'This account does not exist.']);
});

test('login fails with invalid mobile number', function () {
    User::factory()->create([
        'mobile_number' => '+26772000001',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'password' => bcrypt('password123'),
        'email_verified_at' => now(),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'type' => 'mobile_number',
        'mobile_number' => '1234',
        'password' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['mobile_number' => 'Please provide a valid mobile number (e.g., +26772000001).']);
});

test('login fails with empty mobile number', function () {
    User::factory()->create([
        'mobile_number' => '+26772000001',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'password' => bcrypt('password123'),
        'email_verified_at' => now(),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'type' => 'mobile_number',
        'mobile_number' => '',
        'password' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['mobile_number' => 'The mobile number field is required.']);
});

test('login fails with mobile number but no database password set', function () {
    User::factory()->create([
        'mobile_number' => '+26772000001',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'password' => null,
    ]);

    $response = $this->postJson('/api/auth/login', [
        'type' => 'mobile_number',
        'password' => 'password123',
        'mobile_number' => '+26772000001',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['mobile_number' => 'This account does not have a password set.']);
});

test('login fails with mobile number but missing password', function () {
    User::factory()->create([
        'mobile_number' => '+26772000001',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'type' => 'mobile_number',
        'mobile_number' => '+26772000001',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password' => 'The password field is required.']);
});

test('login fails with mobile number but empty password', function () {
    User::factory()->create([
        'mobile_number' => '+26772000001',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'type' => 'mobile_number',
        'password' => '',
        'mobile_number' => '+26772000001',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password' => 'The password field is required.']);
});

test('login fails with mobile number but incorrect password', function () {
    User::factory()->create([
        'mobile_number' => '+26772000001',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'password' => bcrypt('password123'),
        'email_verified_at' => now(),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'type' => 'mobile_number',
        'mobile_number' => '+26772000001',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['mobile_number' => 'The provided credentials are incorrect.']);
});

test('login fails with missing type', function () {
    $response = $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['type' => 'The type field is required']);
});

test('login fails with invalid type', function () {
    $response = $this->postJson('/api/auth/login', [
        'type' => 'invalid',
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['type' => 'The type must be either email or mobile_number.']);
});

test('login fails with empty type', function () {
    $response = $this->postJson('/api/auth/login', [
        'type' => '',
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['type' => 'The type field is required']);
});

test('login fails for unverified email if verification is required', function () {
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
        'email_verified_at' => null,
    ]);

    $response = $this->postJson('/api/auth/login', [
        'type' => 'email',
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email' => 'Email must be verified before login.']);
});

// REGISTER

test('user can register with email and requires verification', function () {
    $response = $this->postJson('/api/auth/register', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john.doe@example.com',
        'password' => 'password123',
        'confirm_password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['user', 'message'])
        ->assertJson(['message' => 'Registration successful. Please check your email to verify your account.'])
        ->assertJsonMissing(['token']);

    $this->assertDatabaseHas('users', [
        'email' => 'john.doe@example.com',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email_verified_at' => null,
    ]);

    $this->assertDatabaseHas('email_verification_tokens', [
        'email' => 'john.doe@example.com',
    ]);

    // Verify the email was sent with the correct parameters
    Mail::assertSent(VerifyRegistrationEmail::class, function ($mail) {
        return $mail->hasTo('john.doe@example.com') &&
               $mail->firstName === 'John' &&
               str_contains($mail->verificationUrl, rtrim(config('app.url'), '/') . '/auth/verify-email?') &&
               str_contains($mail->verificationUrl, 'email=' . urlencode('john.doe@example.com')) &&
               str_contains($mail->verificationUrl, '&type=' . EmailVerificationType::REGISTRATION_EMAIL->value) &&
               str_contains($mail->verificationUrl, 'token='); // Ensure a token is present without matching it exactly
    });

    Mail::assertNotSent(VerifyUpdatedEmail::class);
});

test('user can register with mobile number and logs in immediately', function () {
    $response = $this->postJson('/api/auth/register', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'mobile_number' => '+26772000001',
        'password' => 'password123',
        'confirm_password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['token', 'message'])
        ->assertJson(['message' => 'Login successful']);

    $this->assertDatabaseHas('users', [
        'mobile_number' => '+26772000001',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email_verified_at' => null,
    ]);

    $this->assertDatabaseMissing('email_verification_tokens', [
        'email' => null,
    ]);

    Mail::assertNotSent(VerifyRegistrationEmail::class);
});

test('registration fails with duplicate email', function () {
    User::factory()->create(['email' => 'test@example.com']);

    $response = $this->postJson('/api/auth/register', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'test@example.com',
        'password' => 'password123',
        'confirm_password' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email' => 'This email address is already registered.']);
});

test('registration fails with duplicate mobile number', function () {
    User::factory()->create(['mobile_number' => '+26772000001']);

    $response = $this->postJson('/api/auth/register', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'mobile_number' => '+26772000001',
        'password' => 'password123',
        'confirm_password' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['mobile_number' => 'This mobile number is already registered.']);
});

test('registration fails with invalid email format', function () {
    $response = $this->postJson('/api/auth/register', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'invalid-email',
        'password' => 'password123',
        'confirm_password' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email' => 'Please provide a valid email address.']);
});

test('registration fails with invalid mobile number format', function () {
    $response = $this->postJson('/api/auth/register', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'mobile_number' => 'invalid-phone',
        'password' => 'password123',
        'confirm_password' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['mobile_number' => 'Please provide a valid mobile number (e.g., +26772000001).']);
});

test('registration fails with missing confirm_password', function () {
    $response = $this->postJson('/api/auth/register', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john.doe@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['confirm_password' => 'The confirm password is required.']);
});

test('registration fails with mismatched passwords', function () {
    $response = $this->postJson('/api/auth/register', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john.doe@example.com',
        'password' => 'password123',
        'confirm_password' => 'different123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['confirm_password' => 'The passwords do not match.']);
});

test('registration fails with short password', function () {
    $response = $this->postJson('/api/auth/register', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john.doe@example.com',
        'password' => 'short',
        'confirm_password' => 'short',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password' => 'The password must be at least 6 characters.']);
});

test('registration fails with missing first_name', function () {
    $response = $this->postJson('/api/auth/register', [
        'last_name' => 'Doe',
        'email' => 'john.doe@example.com',
        'password' => 'password123',
        'confirm_password' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['first_name' => 'The first name is required.']);
});

test('registration fails with short first_name', function () {
    $response = $this->postJson('/api/auth/register', [
        'first_name' => 'J',
        'last_name' => 'Doe',
        'email' => 'john.doe@example.com',
        'password' => 'password123',
        'confirm_password' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['first_name' => 'The first name must be at least 3 characters or more.']);
});

test('registration fails with long first_name', function () {
    $response = $this->postJson('/api/auth/register', [
        'first_name' => str_repeat('J', 21),
        'last_name' => 'Doe',
        'email' => 'john.doe@example.com',
        'password' => 'password123',
        'confirm_password' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['first_name' => 'The first name must not exceed 20 characters.']);
});


test('registration fails with short last_name', function () {
    $response = $this->postJson('/api/auth/register', [
        'first_name' => 'John',
        'last_name' => 'D',
        'email' => 'john.doe@example.com',
        'password' => 'password123',
        'confirm_password' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['last_name' => 'The last name must be at least 3 characters or more.']);
});

test('registration fails with long last_name', function () {
    $response = $this->postJson('/api/auth/register', [
        'first_name' => 'John',
        'last_name' => str_repeat('D', 21),
        'email' => 'john.doe@example.com',
        'password' => 'password123',
        'confirm_password' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['last_name' => 'The last name must not exceed 20 characters.']);
});

test('registration succeeds with both email and mobile number and requires verification', function () {
    $response = $this->postJson('/api/auth/register', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john.doe@example.com',
        'mobile_number' => '+26772000001',
        'password' => 'password123',
        'confirm_password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['user', 'message'])
        ->assertJson(['message' => 'Registration successful. Please check your email to verify your account.'])
        ->assertJsonMissing(['token']);

    $this->assertDatabaseHas('users', [
        'email' => 'john.doe@example.com',
        'mobile_number' => '+26772000001',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email_verified_at' => null,
    ]);

    $this->assertDatabaseHas('email_verification_tokens', [
        'email' => 'john.doe@example.com',
    ]);

    // Verify the email was sent with the correct parameters
    Mail::assertSent(VerifyRegistrationEmail::class, function ($mail) {
        return $mail->hasTo('john.doe@example.com') &&
               $mail->firstName === 'John' &&
               str_contains($mail->verificationUrl, rtrim(config('app.url'), '/') . '/auth/verify-email?') &&
               str_contains($mail->verificationUrl, 'email=' . urlencode('john.doe@example.com')) &&
               str_contains($mail->verificationUrl, '&type=' . EmailVerificationType::REGISTRATION_EMAIL->value) &&
               str_contains($mail->verificationUrl, 'token='); // Ensure a token is present without matching it exactly
    });

    Mail::assertNotSent(VerifyUpdatedEmail::class);
});

// FORGOT PASSWORD

test('user can request password reset link', function () {
    User::factory()->create([
        'email' => 'test@example.com',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/api/auth/forgot-password', [
        'email' => 'test@example.com',
    ]);

    $response->assertStatus(200)
        ->assertJson(['message' => 'A password reset link has been sent to your email.']);

    Mail::assertSent(PasswordResetLink::class, function ($mail) {
        return $mail->hasTo('test@example.com') && !empty($mail->resetUrl);
    });

    $this->assertDatabaseHas('password_reset_tokens', [
        'email' => 'test@example.com',
    ]);
});

test('password reset fails with nonexistent email', function () {
    $response = $this->postJson('/api/auth/forgot-password', [
        'email' => 'nonexistent@example.com',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email' => 'The email address does not exist.']);
});

test('password reset fails for user without password', function () {
    User::factory()->create([
        'email' => 'test@example.com',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'password' => null,
    ]);

    $response = $this->postJson('/api/auth/forgot-password', [
        'email' => 'test@example.com',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email' => 'The account has not been set up. Please use the setup link sent to your email.']);
});

// VALIDATE TOKEN

test('user can validate password reset token', function () {
    User::factory()->create([
        'email' => 'test@example.com',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'password' => null,
    ]);

    $token = Str::random(60);
    DB::table('password_reset_tokens')->insert([
        'email' => 'test@example.com',
        'token' => hash('sha256', $token),
        'created_at' => now(),
    ]);

    $response = $this->postJson('/api/auth/validate-token', [
        'email' => 'test@example.com',
        'token' => $token,
    ]);

    $response->assertStatus(200)
        ->assertJson(['message' => 'Token is valid.']);
});

test('validate token fails with invalid token', function () {
    User::factory()->create([
        'email' => 'test@example.com',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'password' => null,
    ]);

    $response = $this->postJson('/api/auth/validate-token', [
        'email' => 'test@example.com',
        'token' => 'invalid-token',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['token' => 'The token is invalid.']);
});

test('validate token fails for user with password', function () {
    User::factory()->create([
        'email' => 'test@example.com',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'password' => bcrypt('password123'),
    ]);

    $token = Str::random(60);
    DB::table('password_reset_tokens')->insert([
        'email' => 'test@example.com',
        'token' => hash('sha256', $token),
        'created_at' => now(),
    ]);

    $response = $this->postJson('/api/auth/validate-token', [
        'email' => 'test@example.com',
        'token' => $token,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email' => 'The account has already been set up. Please log in or reset your password.']);
});

test('validate token fails with expired token', function () {
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => null,
    ]);

    $token = Str::random(60);
    DB::table('password_reset_tokens')->insert([
        'email' => 'test@example.com',
        'token' => hash('sha256', $token),
        'created_at' => now()->subHours(25),
    ]);

    $response = $this->postJson('/api/auth/validate-token', [
        'email' => 'test@example.com',
        'token' => $token,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['token' => 'The token has expired.']);
});

// RESET PASSWORD

test('user can reset password with valid token', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'password' => bcrypt('oldpassword'),
    ]);

    $token = Str::random(60);
    DB::table('password_reset_tokens')->insert([
        'email' => 'test@example.com',
        'token' => hash('sha256', $token),
        'created_at' => now(),
    ]);

    $response = $this->postJson('/api/auth/reset-password', [
        'email' => 'test@example.com',
        'token' => $token,
        'password' => 'newpassword123',
    ]);

    $response->assertStatus(200)
        ->assertJson(['message' => 'Password reset successfully. Please log in with your new password.']);

    $this->assertDatabaseMissing('password_reset_tokens', [
        'email' => 'test@example.com',
    ]);

    $user->refresh();
    expect(Hash::check('newpassword123', $user->password))->toBeTrue();
});

test('reset password fails with nonexistent email', function () {
    $token = Str::random(60);
    $response = $this->postJson('/api/auth/reset-password', [
        'email' => 'nonexistent@example.com',
        'token' => $token,
        'password' => 'newpassword123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email' => 'The email address does not exist.']);
});

test('reset password fails with short password', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('oldpassword'),
    ]);

    $token = Str::random(60);
    DB::table('password_reset_tokens')->insert([
        'email' => 'test@example.com',
        'token' => hash('sha256', $token),
        'created_at' => now(),
    ]);

    $response = $this->postJson('/api/auth/reset-password', [
        'email' => 'test@example.com',
        'token' => $token,
        'password' => 'short',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password' => 'The password must be at least 6 characters long.']);
});

// SETUP PASSWORD

test('user can set up password with valid token', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'password' => null,
    ]);

    $token = Str::random(60);
    DB::table('password_reset_tokens')->insert([
        'email' => 'test@example.com',
        'token' => hash('sha256', $token),
        'created_at' => now(),
    ]);

    $response = $this->postJson('/api/auth/setup-password', [
        'email' => 'test@example.com',
        'token' => $token,
        'password' => 'newpassword123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['token', 'message'])
        ->assertJson(['message' => 'Password set successfully. You are now logged in.']);

    $this->assertDatabaseMissing('password_reset_tokens', [
        'email' => 'test@example.com',
    ]);

    $user->refresh();
    expect(Hash::check('newpassword123', $user->password))->toBeTrue();
});

test('setup password fails with existing password', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('oldpassword'),
    ]);

    $token = Str::random(60);
    DB::table('password_reset_tokens')->insert([
        'email' => 'test@example.com',
        'token' => hash('sha256', $token),
        'created_at' => now(),
    ]);

    $response = $this->postJson('/api/auth/setup-password', [
        'email' => 'test@example.com',
        'token' => $token,
        'password' => 'newpassword123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email' => 'The account has already been set up. Please log in or reset your password.']);
});

// LOGOUT

test('user can logout', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'first_name' => 'John',
        'last_name' => 'Doe',
    ]);
    $token = $user->createToken('auth_token')->plainTextToken;

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->postJson('/api/auth/logout');

    $response->assertStatus(200)
        ->assertJson(['message' => 'Logged out successfully']);

    $this->assertDatabaseMissing('personal_access_tokens', [
        'tokenable_id' => $user->id,
        'token' => hash('sha256', explode('|', $token)[1]),
    ]);
});

// AUTH USER

test('user can view their profile', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'mobile_number' => '+26772000001',
        'email_verified_at' => now(),
    ]);
    $token = $user->createToken('auth_token')->plainTextToken;

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->getJson('/api/auth/user');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'id',
            'name',
            'email',
            'last_name',
            'first_name',
            'created_at',
            'updated_at',
            'email_verified_at',
            'mobile_number' => [
                'country',
                'dialing_code',
                'international',
                'national',
            ],
        ])
        ->assertJsonFragment([
            'email' => 'test@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'name' => 'John Doe',
            'mobile_number' => [
                'country' => 'BW',
                'dialing_code' => '+267',
                'international' => '+26772000001',
                'national' => '72000001',
            ],
        ]);
});

test('user can view their profile with null mobile number', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'mobile_number' => null,
        'email_verified_at' => now(),
    ]);
    $token = $user->createToken('auth_token')->plainTextToken;

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->getJson('/api/auth/user');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'id',
            'name',
            'email',
            'last_name',
            'first_name',
            'created_at',
            'updated_at',
            'email_verified_at',
            'mobile_number',
        ])
        ->assertJsonFragment([
            'email' => 'test@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'name' => 'John Doe',
            'mobile_number' => null,
        ]);
});

test('user can view their profile with eager loaded relationships', function () {
    // Mock UssdService
    $ussdService = Mockery::mock(UssdService::class);
    $ussdService->shouldReceive('isValidUssdRequest')->andReturn(false);
    $ussdService->shouldReceive('cacheManager')->andReturnSelf();
    $ussdService->shouldReceive('forget')->andReturnNull();
    $this->app->instance(UssdService::class, $ussdService);

    // Mock PricingPlanService
    $pricingPlanService = Mockery::mock(PricingPlanService::class);
    $pricingPlanService->shouldReceive('payPricingPlan')->andReturnNull();
    $this->app->instance(PricingPlanService::class, $pricingPlanService);

    // Mock MediaFileService
    $mediaFileService = Mockery::mock(MediaFileService::class);
    $mediaFileService->shouldReceive('createMediaFile')->andReturnNull();
    $this->app->instance(MediaFileService::class, $mediaFileService);

    $user = User::factory()->create([
        'email' => 'test@example.com',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'mobile_number' => '+26772000001',
        'email_verified_at' => now(),
    ]);

    // Log in the user
    $this->actingAs($user, 'sanctum');

    // Create a store with observers disabled
    Event::fakeFor(function () use ($user) {
        $store = $user->stores()->create([
            'currency' => 'BWP',
            'name' => 'Test Store',
            'alias' => 'test-store',
            'description' => 'A test store',
            'qr_code_file_path' => 'mocked_qr_code_path.png',
        ]);

        // Create admin role and assign to user
        $adminRole = Role::create([
            'name' => 'admin',
            'store_id' => $store->id,
            'guard_name' => 'sanctum',
        ]);
        $user->assignRole($adminRole);
    });

    $token = $user->createToken('auth_token')->plainTextToken;

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->getJson('/api/auth/user?_relationships=stores,roles');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'id',
            'name',
            'email',
            'last_name',
            'first_name',
            'created_at',
            'updated_at',
            'email_verified_at',
            'mobile_number',
            'stores',
            'roles',
        ])
        ->assertJsonFragment([
            'email' => 'test@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'name' => 'John Doe',
            'mobile_number' => [
                'country' => 'BW',
                'dialing_code' => '+267',
                'international' => '+26772000001',
                'national' => '72000001',
            ],
            'stores' => [
                [
                    'id' => $user->stores->first()->id,
                    'name' => 'Test Store',
                    'alias' => 'test-store',
                    'email' => null,
                    'tax_id' => null,
                    'online' => true,
                    'country' => 'BW',
                    'currency' => 'BWP',
                    'language' => 'en',
                    'web_link' => rtrim(config('app.url'), '/').'/test-store',
                    'description' => 'A test store',
                    'opening_hours' => [],
                    'offer_rewards' => false,
                    'tax_method' => 'inclusive',
                    'call_to_action' => 'Buy',
                    'offline_message' => 'We are currently offline',
                    'sms_sender_name' => null,
                    'weight_unit' => 'kg',
                    'distance_unit' => 'km',
                    'order_number_padding' => 2,
                    'order_number_counter' => 0,
                    'order_number_prefix' => null,
                    'order_number_suffix' => null,
                    'qr_code_file_path' => 'mocked_qr_code_path.png',
                    'show_opening_hours' => false,
                    'message_footer' => null,
                    'show_sms_channel' => false,
                    'show_line_channel' => false,
                    'skip_payment_page' => false,
                    'show_whatsapp_channel' => true,
                    'show_telegram_channel' => false,
                    'show_messenger_channel' => false,
                    'line_channel_username' => null,
                    'telegram_channel_username' => null,
                    'messenger_channel_username' => null,
                    'invoice_show_logo' => true,
                    'invoice_show_qr_code' => true,
                    'invoice_header' => null,
                    'invoice_footer' => null,
                    'invoice_company_name' => null,
                    'invoice_company_email' => null,
                    'tax_percentage_rate' => '0.00',
                    'reward_percentage_rate' => '0.00',
                    'allow_checkout_on_closed_hours' => true,
                    'tips' => [],
                    'checkout_fees' => [],
                    'combine_fees' => false,
                    'combine_discounts' => false,
                    'seo_title' => null,
                    'seo_keywords' => [],
                    'meta_pixel_id' => null,
                    'tiktok_pixel_id' => null,
                    'seo_description' => null,
                    'google_analytics_id' => null,
                    'created_at' => $user->stores->first()->created_at->toDateTimeString(),
                    'updated_at' => $user->stores->first()->updated_at->toDateTimeString(),
                    'deleted_at' => null,
                    'ussd_mobile_number' => null,
                    'whatsapp_mobile_number' => null,
                    'invoice_company_mobile_number' => null,
                ],
            ],
            'roles' => [
                [
                    'name' => 'admin',
                    'id' => $user->roles->first()->id,
                    'store_id' => $user->stores->first()->id,
                    'created_at' => $user->roles->first()->created_at->toDateTimeString(),
                    'updated_at' => $user->roles->first()->updated_at->toDateTimeString(),
                ],
            ],
        ]);
});

test('user can update their profile', function () {
    $user = User::factory()->create([
        'mobile_number' => '+26772000001',
        'email' => 'test@example.com',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email_verified_at' => now(),
    ]);
    $token = $user->createToken('auth_token')->plainTextToken;

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->putJson('/api/auth/user', [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'password' => 'newpassword123',
            'mobile_number' => '+26772000002',
        ]);

    $response->assertStatus(200)
        ->assertJson(['message' => 'Account updated successfully.']);

    $user->refresh();

    expect($user->first_name)->toBe('Jane');
    expect($user->last_name)->toBe('Smith');
    expect($user->mobile_number->formatE164())->toBe('+26772000002');
    expect(Hash::check('newpassword123', $user->password))->toBeTrue();
});

test('update user with empty payload', function () {
    $user = User::factory()->create([
        'mobile_number' => '+26772000001',
        'password' => 'newpassword123',
        'email' => 'test@example.com',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email_verified_at' => now(),
    ]);
    $token = $user->createToken('auth_token')->plainTextToken;

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->putJson('/api/auth/user', []);

    $response->assertStatus(200)
        ->assertJson(['message' => 'Account updated successfully.']);

    $user->refresh();
    expect($user->first_name)->toBe('John');
    expect($user->last_name)->toBe('Doe');
    expect($user->email)->toBe('test@example.com');
    expect($user->mobile_number->formatE164())->toBe('+26772000001');
    expect(Hash::check('newpassword123', $user->password))->toBeTrue();
});

test('update user fails with duplicate email', function () {
    $user1 = User::factory()->create(['email' => 'existing@example.com']);
    $user2 = User::factory()->create(['email' => 'test@example.com']);
    $token = $user2->createToken('auth_token')->plainTextToken;

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->putJson('/api/auth/user', [
            'email' => 'existing@example.com',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email' => 'This email address is already registered.']);
});

test('update user sends verification email on email change', function () {
    // Fake mail to prevent actual emails from being sent
    Mail::fake();

    // Create a user with a verified email
    $user = User::factory()->create([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'test@example.com',
        'email_verified_at' => now(),
    ]);

    // Generate an auth token for the user
    $token = $user->createToken('auth_token')->plainTextToken;

    // Make a request to update the user's email
    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->putJson('/api/auth/user', [
            'email' => 'new@example.com',
        ]);

    // Assert the response is successful
    $response->assertStatus(200)
        ->assertJson(['message' => 'Account updated successfully.']);

    // Refresh the user model to get updated data
    $user->refresh();

    // Assert the email was updated and email_verified_at was reset
    expect($user->first_name)->toBe('John');
    expect($user->last_name)->toBe('Doe');
    expect($user->email)->toBe('new@example.com');
    expect($user->email_verified_at)->toBeNull();

    // Assert a verification email was sent to the updated email
    Mail::assertSent(VerifyUpdatedEmail::class, function ($mail) {
        return $mail->hasTo('new@example.com') && // Corrected to new email
               $mail->firstName === 'John' &&
               str_contains($mail->verificationUrl, rtrim(config('app.url'), '/') . '/auth/verify-email?') &&
               str_contains($mail->verificationUrl, 'email=' . urlencode('new@example.com')) &&
               str_contains($mail->verificationUrl, '&type=' . EmailVerificationType::UPDATED_EMAIL->value) &&
               str_contains($mail->verificationUrl, 'token='); // Ensure a token is present without matching it exactly
    });

    Mail::assertNotSent(VerifyRegistrationEmail::class);

    // Assert an email verification token was created
    $tokenRecord = DB::table('email_verification_tokens')
        ->where('email', 'new@example.com')
        ->first();

    expect($tokenRecord)->not()->toBeNull();
    expect($tokenRecord->expires_at)->toBeGreaterThan(now());
});

// VERIFY EMAIL

test('verify email with valid token marks email as verified and logs in', function () {
    // Fake mail
    Mail::fake();

    // Create a user with an unverified email
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'email_verified_at' => null,
    ]);

    // Create a valid email verification token
    $token = Str::random(60);
    DB::table('email_verification_tokens')->insert([
        'email' => 'test@example.com',
        'token' => hash('sha256', $token),
        'created_at' => now(),
        'expires_at' => now()->addDays(7),
    ]);

    // Make a request to verify the email
    $response = $this->postJson('/api/auth/verify-email', [
        'email' => 'test@example.com',
        'token' => $token,
    ]);

    // Assert the response is successful
    $response->assertStatus(200)
        ->assertJsonStructure(['message', 'user', 'token'])
        ->assertJson(['message' => 'Email verified successfully']);

    // Refresh the user model
    $user->refresh();

    // Assert the email is marked as verified
    expect($user->email_verified_at)->not()->toBeNull();

    // Assert the token was deleted
    $this->assertDatabaseMissing('email_verification_tokens', [
        'email' => 'test@example.com',
    ]);
});

test('verify email with invalid token returns error', function () {
    // Create a user
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'email_verified_at' => null,
    ]);

    // Make a request with an invalid token
    $response = $this->postJson('/api/auth/verify-email', [
        'email' => 'test@example.com',
        'token' => 'invalid-token',
    ]);

    // Assert the response returns a 422 error with appropriate message
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['token' => 'The verification token is invalid.']);
});

test('verify email with expired token returns error', function () {
    // Create a user
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'email_verified_at' => null,
    ]);

    // Create an expired email verification token
    $token = Str::random(60);
    DB::table('email_verification_tokens')->insert([
        'email' => 'test@example.com',
        'token' => hash('sha256', $token),
        'created_at' => now()->subDays(8),
        'expires_at' => now()->subDay(),
    ]);

    // Make a request to verify the email
    $response = $this->postJson('/api/auth/verify-email', [
        'email' => 'test@example.com',
        'token' => $token,
    ]);

    // Assert the response returns a 422 error with appropriate message
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['token' => 'The verification token has expired.']);
});

test('verify email with non-existent email returns error', function () {
    // Make a request with a non-existent email
    $response = $this->postJson('/api/auth/verify-email', [
        'email' => 'nonexistent@example.com',
        'token' => 'some-token',
    ]);

    // Assert the response returns a 422 error with appropriate message
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email' => 'The email address does not exist.']);
});

test('verify email fails for already verified email', function () {
    // Create a user with a verified email
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'email_verified_at' => now(),
    ]);

    // Create a valid email verification token
    $token = Str::random(60);
    DB::table('email_verification_tokens')->insert([
        'email' => 'test@example.com',
        'token' => hash('sha256', $token),
        'created_at' => now(),
        'expires_at' => now()->addDays(7),
    ]);

    // Make a request to verify the email
    $response = $this->postJson('/api/auth/verify-email', [
        'email' => 'test@example.com',
        'token' => $token,
    ]);

    // Assert the response returns a 422 error with appropriate message
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email' => 'This email is already verified.']);
});

// RESEND EMAIL VERIFICATION

test('resend email verification sends new verification email for registration', function () {
    // Fake mail
    Mail::fake();

    // Create a user with an unverified email
    $user = User::factory()->create([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'test@example.com',
        'email_verified_at' => null,
    ]);

    // Make a request to resend verification email
    $response = $this->postJson('/api/auth/resend-email-verification', [
        'email' => 'test@example.com',
        'type' => 'registration email',
    ]);

    // Assert the response is successful
    $response->assertStatus(200)
        ->assertJson(['message' => 'Verification email sent successfully.']);

    // Verify the email was sent with the correct parameters
    Mail::assertSent(VerifyRegistrationEmail::class, function ($mail) {
        return $mail->hasTo('test@example.com') &&
               $mail->firstName === 'John' &&
               str_contains($mail->verificationUrl, rtrim(config('app.url'), '/') . '/auth/verify-email?') &&
               str_contains($mail->verificationUrl, 'email=' . urlencode('test@example.com')) &&
               str_contains($mail->verificationUrl, '&type=' . EmailVerificationType::REGISTRATION_EMAIL->value) &&
               str_contains($mail->verificationUrl, 'token='); // Ensure a token is present without matching it exactly
    });

    Mail::assertNotSent(VerifyUpdatedEmail::class);

    // Assert a new verification token was created
    $tokenRecord = DB::table('email_verification_tokens')
        ->where('email', 'test@example.com')
        ->first();

    expect($tokenRecord)->not()->toBeNull();
    expect($tokenRecord->expires_at)->toBeGreaterThan(now());
});

test('resend email verification sends new verification email for updated email', function () {
    // Fake mail
    Mail::fake();

    // Create a user with an unverified email
    $user = User::factory()->create([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'new@example.com',
        'email_verified_at' => null,
    ]);

    // Make a request to resend verification email
    $response = $this->postJson('/api/auth/resend-email-verification', [
        'email' => 'new@example.com',
        'type' => 'updated email',
    ]);

    // Assert the response is successful
    $response->assertStatus(200)
        ->assertJson(['message' => 'Verification email sent successfully.']);

    // Assert a verification email was sent to the updated email
    Mail::assertSent(VerifyUpdatedEmail::class, function ($mail) {
        return $mail->hasTo('new@example.com') && // Corrected to new email
               $mail->firstName === 'John' &&
               str_contains($mail->verificationUrl, rtrim(config('app.url'), '/') . '/auth/verify-email?') &&
               str_contains($mail->verificationUrl, 'email=' . urlencode('new@example.com')) &&
               str_contains($mail->verificationUrl, '&type=' . EmailVerificationType::UPDATED_EMAIL->value) &&
               str_contains($mail->verificationUrl, 'token='); // Ensure a token is present without matching it exactly
    });

    Mail::assertNotSent(VerifyRegistrationEmail::class);

    // Assert a new verification token was created
    $tokenRecord = DB::table('email_verification_tokens')
        ->where('email', 'new@example.com')
        ->first();

    expect($tokenRecord)->not()->toBeNull();
    expect($tokenRecord->expires_at)->toBeGreaterThan(now());
});

test('resend email verification defaults to registration email type', function () {
    // Fake mail
    Mail::fake();

    // Create a user with an unverified email
    $user = User::factory()->create([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'test@example.com',
        'email_verified_at' => null,
    ]);

    // Make a request to resend verification email without type
    $response = $this->postJson('/api/auth/resend-email-verification', [
        'email' => 'test@example.com',
    ]);

    // Assert the response is successful
    $response->assertStatus(200)
        ->assertJson(['message' => 'Verification email sent successfully.']);

    // Verify the email was sent with the correct parameters
    Mail::assertSent(VerifyRegistrationEmail::class, function ($mail) {
        return $mail->hasTo('test@example.com') &&
               $mail->firstName === 'John' &&
               str_contains($mail->verificationUrl, rtrim(config('app.url'), '/') . '/auth/verify-email?') &&
               str_contains($mail->verificationUrl, 'email=' . urlencode('test@example.com')) &&
               str_contains($mail->verificationUrl, '&type=' . EmailVerificationType::REGISTRATION_EMAIL->value) &&
               str_contains($mail->verificationUrl, 'token='); // Ensure a token is present without matching it exactly
    });

    Mail::assertNotSent(VerifyUpdatedEmail::class);

    // Assert a new verification token was created
    $tokenRecord = DB::table('email_verification_tokens')
        ->where('email', 'test@example.com')
        ->first();

    expect($tokenRecord)->not()->toBeNull();
    expect($tokenRecord->expires_at)->toBeGreaterThan(now());
});

test('resend email verification for verified email returns error', function () {
    // Create a user with a verified email
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'email_verified_at' => now(),
    ]);

    // Make a request to resend verification email
    $response = $this->postJson('/api/auth/resend-email-verification', [
        'email' => 'test@example.com',
    ]);

    // Assert the response returns a 422 error with appropriate message
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email' => 'This email is already verified.']);
});

test('resend email verification for non-existent email returns error', function () {
    // Make a request with a non-existent email
    $response = $this->postJson('/api/auth/resend-email-verification', [
        'email' => 'nonexistent@example.com',
    ]);

    // Assert the response returns a 422 error with appropriate message
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email' => 'The email address does not exist.']);
});

test('user can view terms and conditions', function () {
    $response = $this->getJson('/api/auth/terms-and-conditions');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'website',
            'buyer' => ['title', 'instruction', 'takeaways'],
            'seller' => ['title', 'instruction', 'takeaways'],
        ]);
});

// SOCIAL LOGIN

test('user can view social login links', function () {
    $response = $this->getJson('/api/auth/social-login-links');

    $response->assertStatus(200)
        ->assertJsonCount(3)
        ->assertJsonStructure([
            '*' => ['label', 'platform', 'url', 'logo_url'],
        ])
        ->assertJsonFragment(['platform' => 'Google'])
        ->assertJsonFragment(['platform' => 'Facebook'])
        ->assertJsonFragment(['platform' => 'Linkedin']);
});

test('social login redirect for google', function () {
    Socialite::shouldReceive('driver')->with('google')->once()->andReturnSelf();
    Socialite::shouldReceive('redirect')->once()->andReturn(redirect('https://google.com/auth'));

    $response = $this->get('/auth/google');

    $response->assertStatus(302)
        ->assertRedirect('https://google.com/auth');
});

test('social login redirect for facebook', function () {
    Socialite::shouldReceive('driver')->with('facebook')->once()->andReturnSelf();
    Socialite::shouldReceive('redirect')->once()->andReturn(redirect('https://facebook.com/auth'));

    $response = $this->get('/auth/facebook');

    $response->assertStatus(302)
        ->assertRedirect('https://facebook.com/auth');
});

test('social login redirect for linkedin', function () {
    Socialite::shouldReceive('driver')->with('linkedin-openid')->once()->andReturnSelf();
    Socialite::shouldReceive('redirect')->once()->andReturn(redirect('https://linkedin.com/auth'));

    $response = $this->get('/auth/linkedin');

    $response->assertStatus(302)
        ->assertRedirect('https://linkedin.com/auth');
});

test('handle google callback successfully', function () {
    $googleUser = Mockery::mock();
    $googleUser->shouldReceive('getEmail')->andReturn('test@example.com');
    $googleUser->shouldReceive('getId')->andReturn('google123');
    $googleUser->user = ['given_name' => 'John', 'family_name' => 'Doe'];

    Socialite::shouldReceive('driver')->with('google')->once()->andReturnSelf();
    Socialite::shouldReceive('user')->once()->andReturn($googleUser);

    $response = $this->get('/auth/google/callback');

    // Check that the response is a redirect
    $response->assertStatus(302);

    // Get the redirect URL
    $redirectUrl = $response->headers->get('Location');

    // Assert the redirect URL contains the expected components
    expect($redirectUrl)->toContain('/auth/social-login');
    expect($redirectUrl)->toContain('token=');
    expect($redirectUrl)->toContain('provider=google');

    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'google_id' => 'google123',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email_verified_at' => now()->format('Y-m-d H:i:s'),
    ]);

    Mail::assertSent(UserRegistered::class, function ($mail) {
        return $mail->hasTo('test@example.com') && $mail->firstName === 'John';
    });
});

test('handle facebook callback successfully', function () {
    $facebookUser = Mockery::mock();
    $facebookUser->shouldReceive('getEmail')->andReturn('test@example.com');
    $facebookUser->shouldReceive('getId')->andReturn('facebook123');
    $facebookUser->user = ['name' => 'John Doe'];

    Socialite::shouldReceive('driver')->with('facebook')->once()->andReturnSelf();
    Socialite::shouldReceive('user')->once()->andReturn($facebookUser);

    $response = $this->get('/auth/facebook/callback');

    // Check that the response is a redirect
    $response->assertStatus(302);

    // Get the redirect URL
    $redirectUrl = $response->headers->get('Location');

    // Assert the redirect URL contains the expected components
    expect($redirectUrl)->toContain('/auth/social-login');
    expect($redirectUrl)->toContain('token=');
    expect($redirectUrl)->toContain('provider=facebook');

    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'facebook_id' => 'facebook123',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email_verified_at' => now()->format('Y-m-d H:i:s'),
    ]);

    Mail::assertSent(UserRegistered::class, function ($mail) {
        return $mail->hasTo('test@example.com') && $mail->firstName === 'John';
    });
});

test('handle linkedin callback successfully', function () {
    $linkedinUser = Mockery::mock();
    $linkedinUser->shouldReceive('getEmail')->andReturn('test@example.com');
    $linkedinUser->shouldReceive('getId')->andReturn('linkedin123');
    $linkedinUser->user = ['given_name' => 'John', 'family_name' => 'Doe'];

    Socialite::shouldReceive('driver')->with('linkedin-openid')->once()->andReturnSelf();
    Socialite::shouldReceive('user')->once()->andReturn($linkedinUser);

    $response = $this->get('/auth/linkedin/callback');

    // Check that the response is a redirect
    $response->assertStatus(302);

    // Get the redirect URL
    $redirectUrl = $response->headers->get('Location');

    // Assert the redirect URL contains the expected components
    expect($redirectUrl)->toContain('/auth/social-login');
    expect($redirectUrl)->toContain('token=');
    expect($redirectUrl)->toContain('provider=linkedin');

    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'linkedin_id' => 'linkedin123',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email_verified_at' => now()->format('Y-m-d H:i:s'),
    ]);

    Mail::assertSent(UserRegistered::class, function ($mail) {
        return $mail->hasTo('test@example.com') && $mail->firstName === 'John';
    });
});

test('handle google callback with error', function () {
    $response = $this->get('/auth/google/callback?error=access_denied');

    // Check that the response is a redirect
    $response->assertStatus(302);

    // Get the redirect URL
    $redirectUrl = $response->headers->get('Location');

    // Assert the redirect URL contains the expected components
    expect($redirectUrl)->toContain('/auth/social-login');
    expect($redirectUrl)->toContain('error=access_denied');
    expect($redirectUrl)->toContain('provider=google');
});

test('handle facebook callback with error', function () {
    $response = $this->get('/auth/facebook/callback?error=access_denied&error_reason=user_denied');

    // Check that the response is a redirect
    $response->assertStatus(302);

    // Get the redirect URL
    $redirectUrl = $response->headers->get('Location');

    // Assert the redirect URL contains the expected components
    expect($redirectUrl)->toContain('/auth/social-login');
    expect($redirectUrl)->toContain('error=access_denied');
    expect($redirectUrl)->toContain('error_reason=user_denied');
    expect($redirectUrl)->toContain('provider=facebook');
});

test('handle linkedin callback with error', function () {
    $response = $this->get('/auth/linkedin/callback?error=access_denied');

    // Check that the response is a redirect
    $response->assertStatus(302);

    // Get the redirect URL
    $redirectUrl = $response->headers->get('Location');

    // Assert the redirect URL contains the expected components
    expect($redirectUrl)->toContain('/auth/social-login');
    expect($redirectUrl)->toContain('error=access_denied');
    expect($redirectUrl)->toContain('provider=linkedin');
});

// SUPER ADMIN STATUS

test('isSuperAdmin returns true for super admin user', function () {
    $user = User::factory()->create([
        'email' => 'superadmin@example.com',
        'first_name' => 'John',
        'last_name' => 'Doe'
    ]);

    // Create and assign super_admin role
    $role = Role::create(['name' => SystemRole::SUPER_ADMIN->value, 'store_id' => null]);
    $user->roles()->attach($role);

    $authService = app(AuthService::class);

    expect($authService->isSuperAdmin($user))->toBeTrue();
});

test('isSuperAdmin returns false for non-super admin user', function () {
    $user = User::factory()->create([
        'email' => 'user@example.com',
        'first_name' => 'John',
        'last_name' => 'Doe',
    ]);

    // No roles are assigned to the user
    $authService = app(AuthService::class);

    expect($authService->isSuperAdmin($user))->toBeFalse();
});
