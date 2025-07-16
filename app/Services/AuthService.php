<?php

namespace App\Services;

use Exception;
use App\Models\User;
use App\Enums\CacheName;
use App\Enums\SystemRole;
use Illuminate\Support\Str;
use App\Mail\UserRegistered;
use App\Mail\PasswordResetLink;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Login user.
     *
     * @param array $credentials
     * @return string
     * @throws ValidationException
     */
    public function login(array $credentials): string
    {
        $type = $credentials['type'];
        $identifier = $type === 'email' ? $credentials['email'] : $credentials['mobile_number'];

        $user = User::where($type, $identifier)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                $type => ['This account does not exist.']
            ]);
        }

        if (!$user->password) {
            throw ValidationException::withMessages([
                $type => ['This account does not have a password set.']
            ]);
        }

        if (!Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                $type => ['The provided credentials are incorrect.']
            ]);
        }

        return $user->createToken('auth_token')->plainTextToken;
    }

    /**
     * Register user.
     *
     * @param array $data
     * @return string
     */
    public function register(array $data): string
    {
        $user = User::create([
            'id' => Str::uuid(),
            'email' => $data['email'],
            'last_name' => $data['last_name'],
            'first_name' => $data['first_name'],
            'password' => Hash::make($data['password'])
        ]);

        if($user->email) {
            Mail::to($user->email)->send(new UserRegistered($user->email, $user->first_name));
        }

        return $user->createToken('auth_token')->plainTextToken;
    }

    /**
     * Send a password reset link to the user.
     *
     * @param string $email
     * @return void
     * @throws ValidationException
     */
    public function sendPasswordResetLink(string $email): void
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['The email address does not exist.']
            ]);
        }

        if (!$user->password) {
            throw ValidationException::withMessages([
                'email' => ['The account has not been set up. Please use the setup link sent to your email.']
            ]);
        }

        $token = Str::random(60);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            ['token' => hash('sha256', $token), 'created_at' => now()]
        );

        $resetUrl = config('app.url') . '/auth/reset-password?token=' . $token . '&email=' . urlencode($user->email);
        Mail::to($user->email)->send(new PasswordResetLink($user->email, $resetUrl));
    }

    /**
     * Validate a password setup token.
     *
     * @param string $email
     * @param string $token
     * @return void
     * @throws ValidationException
     */
    public function validateToken(string $email, string $token): void
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['The email address does not exist.'],
            ]);
        }

        $tokenRecord = DB::table('password_reset_tokens')
                         ->where('email', $email)
                         ->where('token', hash('sha256', $token))
                         ->first();

        if (!$tokenRecord) {
            throw ValidationException::withMessages([
                'token' => ['The token is invalid.'],
            ]);
        }

        if ($tokenRecord->created_at < now()->subHours(24)) {
            throw ValidationException::withMessages([
                'token' => ['The token has expired.'],
            ]);
        }

        if ($user->password) {
            throw ValidationException::withMessages([
                'email' => ['The account has already been set up. Please log in or reset your password.'],
            ]);
        }
    }

    /**
     * Reset a user's password using a token.
     *
     * @param string $email
     * @param string $token
     * @param string $password
     * @return void
     * @throws ValidationException
     */
    public function resetPassword(string $email, string $token, string $password): void
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['The email address does not exist.']
            ]);
        }

        $tokenRecord = DB::table('password_reset_tokens')
                         ->where('email', $email)
                         ->where('token', hash('sha256', $token))
                         ->first();

        if (!$tokenRecord) {
            throw ValidationException::withMessages([
                'token' => ['The token is invalid.']
            ]);
        }

        if ($tokenRecord->created_at < now()->subHours(24)) {
            throw ValidationException::withMessages([
                'token' => ['The token has expired.']
            ]);
        }

        $user->password = Hash::make($password);
        $user->save();

        DB::table('password_reset_tokens')->where('email', $email)->delete();
    }

    /**
     * Set up a user's password using a token.
     *
     * @param string $email
     * @param string $token
     * @param string $password
     * @return string
     * @throws ValidationException
     */
    public function setupPassword(string $email, string $token, string $password): string
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['The email address does not exist.'],
            ]);
        }

        if ($user->password) {
            throw ValidationException::withMessages([
                'email' => ['The account has already been set up. Please log in or reset your password.'],
            ]);
        }

        $tokenRecord = DB::table('password_reset_tokens')
                         ->where('email', $email)
                         ->where('token', hash('sha256', $token))
                         ->first();

        if (!$tokenRecord) {
            throw ValidationException::withMessages([
                'token' => ['The token is invalid.'],
            ]);
        }

        if ($tokenRecord->created_at < now()->subHours(24)) {
            throw ValidationException::withMessages([
                'token' => ['The token has expired.'],
            ]);
        }

        $user->password = Hash::make($password);
        $user->save();

        DB::table('password_reset_tokens')->where('email', $email)->delete();

        return $user->createToken('auth_token')->plainTextToken;
    }

    /**
     * Log out the authenticated user.
     *
     * @return void
     */
    public function logout(): void
    {
        auth()->user()->currentAccessToken()?->delete();
    }

    /**
     * Update authenticated user.
     *
     * @param User $user
     * @param array $data
     * @return void
     */
    public function updateAuthUser(User $user, array $data): void
    {
        $user->name = $data['name'];

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();
    }

    /**
     * Show social login links.
     *
     * @return array
     */
    public function showSocialLoginLinks(): array
    {
        $platforms = ['google', 'facebook', 'linkedin'];

        return collect($platforms)->map(function($platform) {

            $platformCapitalized = ucfirst($platform);
            $label = 'Continue with '.$platformCapitalized;

            return [
                'label' => $label,
                'platform' => $platformCapitalized,
                'url' => route("social.auth.$platform"),
                'logo_url' => asset("/images/social-login-icons/$platform.png")
            ];
        })->toArray();
    }

    /**
     * Handle the Google callback.
     *
     * @return RedirectResponse
     */
    public function handleGoogleCallback(): RedirectResponse
    {
        $params = [
            'provider' => 'google',
            'logo_url' => asset("/images/social-login-icons/google.png"),
        ];

        if (request()->has('error')) {

            $params = array_merge($params, [
                'error' => request()->get('error')
            ]);

            return redirect()->away(
                config('app.url'). '/auth/social-login' . '?' . http_build_query($params)
            );

        }

        try {

            $googleUser = Socialite::driver('google')->user();

            $email = $googleUser->getEmail();
            $googleId = $googleUser->getId();
            $lastName = $googleUser->user['family_name'] ?? null;
            $firstName = $googleUser->user['given_name'] ?? 'Unknown';

            $user = User::firstOrCreate(
                !empty($email)
                    ? ['email' => $email]
                    : ['google_id' => $googleId],
                [
                    'email' => $email,
                    'google_id' => $googleId,
                    'last_name' => $lastName,
                    'first_name' => $firstName,
                    'email_verified_at' => !empty($email) ? now() : null,
                ]
            );

            if ($user->wasRecentlyCreated) {
                Mail::to($user->email)->send(new UserRegistered($user->email, $user->first_name));
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            $params = array_merge($params, [
                'token' => $token
            ]);

            return redirect()->away(
                config('app.url'). '/auth/social-login' . '?' . http_build_query($params)
            );

        } catch (\GuzzleHttp\Exception\ClientException $e) {

            // Handle errors returned by Google
            $errorResponse = json_decode($e->getResponse()->getBody()->getContents(), true);

            $params = array_merge($params, [
                'error' => $errorResponse['error'] ?? 'unknown_error',
                'error_description' => $errorResponse['error_description'] ?? 'An unknown error occurred while communicating with Google.',
            ]);

            return redirect()->away(
                config('app.url'). '/auth/social-login' . '?' . http_build_query($params)
            );

        } catch (\Exception $e) {

            // Handle unexpected errors
            $params = array_merge($params, [
                'error' => 'server_error',
                'error_description' => $e->getMessage()
            ]);

            return redirect()->away(
                config('app.url'). '/auth/social-login' . '?' . http_build_query($params)
            );

        }
    }

    /**
     * Handle the Facebook callback.
     *
     * @return RedirectResponse
     */
    public function handleFacebookCallback(): RedirectResponse
    {
        $params = [
            'provider' => 'facebook',
            'logo_url' => asset("/images/social-login-icons/facebook.png"),
        ];

        if (request()->has('error')) {

            $params = array_merge($params, [
                'error' => request()->get('error'),
                'error_reason' => request()->get('error_reason'),
                'error_description' => request()->get('error_description')
            ]);

            return redirect()->away(
                config('app.url'). '/auth/social-login' . '?' . http_build_query($params)
            );

        }

        try {

            $facebookUser = Socialite::driver('facebook')->user();

            $name = $facebookUser->user['name'] ?? '';
            $nameParts = explode(' ', $name, 2);

            $lastName = $nameParts[1] ?? null;
            $email = $facebookUser->getEmail();
            $facebookId = $facebookUser->getId();
            $firstName = $nameParts[0] ?? 'Unknown';

            if(!$email) {
                throw new Exception('This facebook account does not have an email');
            }

            $user = User::firstOrCreate(
                !empty($email)
                    ? ['email' => $email]
                    : ['facebook_id' => $facebookId],
                [
                    'email' => $email,
                    'last_name' => $lastName,
                    'first_name' => $firstName,
                    'facebook_id' => $facebookId,
                    'email_verified_at' => !empty($email) ? now() : null,
                ]
            );

            if ($user->wasRecentlyCreated) {
                Mail::to($user->email)->send(new UserRegistered($user->email, $user->first_name));
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            $params = array_merge($params, [
                'token' => $token
            ]);

            return redirect()->away(
                config('app.url'). '/auth/social-login' . '?' . http_build_query($params)
            );

        } catch (\GuzzleHttp\Exception\ClientException $e) {

            // Handle errors returned by Facebook
            $errorResponse = json_decode($e->getResponse()->getBody()->getContents(), true);

            $params = array_merge($params, [
                'error' => $errorResponse['error'] ?? 'unknown_error',
                'error_description' => $errorResponse['error_description'] ?? 'An unknown error occurred while communicating with Facebook.',
            ]);

            return redirect()->away(
                config('app.url'). '/auth/social-login' . '?' . http_build_query($params)
            );

        } catch (\Exception $e) {

            // Handle unexpected errors
            $params = array_merge($params, [
                'error' => 'server_error',
                'error_description' => $e->getMessage()
            ]);

            return redirect()->away(
                config('app.url'). '/auth/social-login' . '?' . http_build_query($params)
            );

        }
    }

    /**
     * Handle the LinkedIn callback.
     *
     * @return RedirectResponse
     */
    public function handleLinkedInCallback(): RedirectResponse
    {
        $params = [
            'provider' => 'linkedin',
            'logo_url' => asset("/images/social-login-icons/linkedin.png"),
        ];

        if (request()->has('error')) {

            $params = array_merge($params, [
                'error' => request()->get('error'),
                'error_description' => request()->get('error_description')
            ]);

            return redirect()->away(
                config('app.url'). '/auth/social-login' . '?' . http_build_query($params)
            );

        }

        try{

            $linkedinUser = Socialite::driver('linkedin-openid')->user();

            $email = $linkedinUser->getEmail();
            $linkedinId = $linkedinUser->getId();
            $lastName = $linkedinUser->user['family_name'] ?? null;
            $firstName = $linkedinUser->user['given_name'] ?? 'Unknown';

            $user = User::firstOrCreate(
                !empty($email)
                    ? ['email' => $email]
                    : ['linkedin_id' => $linkedinId],
                [
                    'email' => $email,
                    'last_name' => $lastName,
                    'first_name' => $firstName,
                    'linkedin_id' => $linkedinId,
                    'email_verified_at' => !empty($email) ? now() : null,
                ]
            );

            if ($user->wasRecentlyCreated) {
                Mail::to($user->email)->send(new UserRegistered($user->email, $user->first_name));
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            $params = array_merge($params, [
                'token' => $token
            ]);

            return redirect()->away(
                config('app.url'). '/auth/social-login' . '?' . http_build_query($params)
            );

        } catch (\GuzzleHttp\Exception\ClientException $e) {

            // Handle errors returned by Linkedin
            $errorResponse = json_decode($e->getResponse()->getBody()->getContents(), true);

            $params = array_merge($params, [
                'error' => $errorResponse['error'] ?? 'unknown_error',
                'error_description' => $errorResponse['error_description'] ?? 'An unknown error occurred while communicating with Linkedin.',
            ]);

            return redirect()->away(
                config('app.url'). '/auth/social-login' . '?' . http_build_query($params)
            );

        } catch (\Exception $e) {

            // Handle unexpected errors
            $params = array_merge($params, [
                'error' => 'server_error',
                'error_description' => $e->getMessage()
            ]);

            return redirect()->away(
                config('app.url'). '/auth/social-login' . '?' . http_build_query($params)
            );

        }
    }

    /**
     * Check if the user is a super admin with a role not tied to any store.
     *
     * @param User $user
     * @return bool
     */
    public function isSuperAdmin(User $user): bool
    {
        return (new CacheService(CacheName::IS_SUPER_ADMIN))->append($user->id)->remember(now()->addDay(), function () use ($user) {
            return $user->roles()->where('name', SystemRole::SUPER_ADMIN->value)
                        ->whereNull('roles.store_id')
                        ->exists();
        });
    }
}
