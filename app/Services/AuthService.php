<?php

namespace App\Services;

use Exception;
use App\Models\User;
use App\Enums\CacheName;
use App\Enums\SystemRole;
use Illuminate\Support\Str;
use App\Mail\UserRegistered;
use App\Mail\PasswordResetLink;
use App\Mail\VerifyUpdatedEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Http\Resources\UserResource;
use App\Enums\EmailVerificationType;
use App\Mail\TeamMemberInvitation;
use App\Mail\VerifyRegistrationEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Validation\ValidationException;

class AuthService extends BaseService
{
    /**
     * Login user.
     *
     * @param array $credentials
     * @return array
     * @throws ValidationException
     */
    public function login(array $credentials): array
    {
        $type = $credentials['type'];
        $identifier = $type === 'email' ? $credentials['email'] : $credentials['mobile_number'];

        $user = User::where($type, $identifier)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                $type => ['This account does not exist.']
            ]);
        }

        if($type === 'email') {
            if (is_null($user->email_verified_at)) {
                throw ValidationException::withMessages([
                    'email' => 'Email must be verified before login.',
                ]);
            }
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

        return [
            'token' => $user->createToken('auth_token')->plainTextToken,
            'message' => 'Login successful'
        ];
    }

    /**
     * Register user.
     *
     * @param array $data
     * @return array
     */
    public function register(array $data): array
    {
        $email = $data['email'] ?? null;

        $user = DB::transaction(function () use ($data) {
            return User::create([
                'id' => Str::uuid(),
                'email_verified_at' => null,
                'email' => $data['email'] ?? null,
                'last_name' => $data['last_name'],
                'first_name' => $data['first_name'],
                'password' => Hash::make($data['password']),
                'mobile_number' => $data['mobile_number'] ?? null,
            ]);
        });

        if ($email) {

            $this->sendEmailVerification($user, EmailVerificationType::REGISTRATION_EMAIL);

            return [
                'user' => new UserResource($user),
                'message' => 'Registration successful. Please check your email to verify your account.'
            ];

        }else{

            return [
                'token' => $user->createToken('auth_token')->plainTextToken,
                'message' => 'Login successful'
            ];

        }
    }

    /**
     * Send a password reset link to the user.
     *
     * @param array $data
     * @return array
     * @throws ValidationException
     */
    public function sendPasswordResetLink(array $data): array
    {
        $email = $data['email'];
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

        return [
            'message' => 'A password reset link has been sent to your email.'
        ];
    }

    /**
     * Validate a password setup token.
     *
     * @param array $data
     * @return array
     * @throws ValidationException
     */
    public function validateToken(array $data): array
    {
        $email = $data['email'];
        $token = $data['token'];

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

        return [
            'message' => 'Token is valid.'
        ];
    }

    /**
     * Reset a user's password using a token.
     *
     * @param array $data
     * @return array
     * @throws ValidationException
     */
    public function resetPassword(array $data): array
    {
        $email = $data['email'];
        $token = $data['token'];
        $password = $data['password'];

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

        return [
            'message' => 'Password reset successfully. Please log in with your new password.'
        ];
    }


    /**
     * Verify a user's email using a token.
     *
     * @param array $data
     * @return array
     */
    public function verifyEmail(array $data): array
    {
        $email = $data['email'];
        $token = $data['token'];

        $user = User::where('email', $email)->first();

        if ($user->email_verified_at) {
            throw ValidationException::withMessages(['email' => 'This email is already verified.']);
        }

        $tokenRecord = DB::table('email_verification_tokens')
                        ->where('email', $email)
                        ->where('token', hash('sha256', $token))
                        ->first();

        if (!$tokenRecord) {
            throw ValidationException::withMessages([
                'token' => ['The verification token is invalid.'],
            ]);
        }

        if ($tokenRecord->expires_at < now()) {
            throw ValidationException::withMessages([
                'token' => ['The verification token has expired.'],
            ]);
        }

        DB::transaction(function () use ($user, $email) {
            $user->update(['email_verified_at' => now()]);
            DB::table('email_verification_tokens')->where('email', $email)->delete();
        });

        // Generate token for automatic login
        $authToken = $user->createToken('auth_token')->plainTextToken;

        return [
            'message' => 'Email verified successfully',
            'user' => new UserResource($user),
            'token' => $authToken,
        ];
    }

    /**
     * Resend email verification.
     *
     * @param array $data
     * @return array
     */
    public function resendEmailVerification(array $data): array
    {
        $email = $data['email'];
        $type = isset($data['type']) ? EmailVerificationType::from($data['type']) : EmailVerificationType::REGISTRATION_EMAIL;

        $user = User::where('email', $email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['The email address does not exist.'],
            ]);
        }

        if ($user->email_verified_at) {
            throw ValidationException::withMessages([
                'email' => ['This email is already verified.'],
            ]);
        }

        $this->sendEmailVerification($user, $type);

        return [
            'message' => 'Verification email sent successfully.'
        ];
    }

    /**
     * Set up a user's password using a token.
     *
     * @param array $data
     * @return array
     * @throws ValidationException
     */
    public function setupPassword(array $data): array
    {
        $email = $data['email'];
        $token = $data['token'];
        $password = $data['password'];

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

        return [
            'token' => $user->createToken('auth_token')->plainTextToken,
            'message' => 'Password set successfully. You are now logged in.'
        ];
    }

    /**
     * Show terms and conditions.
     *
     * @return array
     */
    public function showTermsAndConditions(): array
    {
        $website = config('app.url').'/terms-and-conditions';

        return [
            'website' => $website,
            'buyer' => [
                'title' => 'Buyer Takeaways',
                'instruction' => 'As a buyer on '.config('app.name').', here are the key terms you must accept:',
                'takeaways' => 'As a buyer on '.config('app.name').', you must create an account with accurate details and keep your login information secure. Before purchasing, carefully review product descriptions, images, and store details, and ensure you buy from trusted sellers. It is crucial to provide a correct delivery address and contact information, as '.config('app.name').' is not responsible for undelivered or misrepresented orders. Please note that '.config('app.name').' does not offer refunds. Payments include the agreed price and any applicable fees. If issues arise, communicate with sellers respectfully, and they are expected to resolve matters promptly. You can leave honest reviews to help future buyers. Your data is protected, and we comply with applicable privacy laws. For disputes, you should first try to resolve them directly with the seller, but '.config('app.name').' can mediate if necessary. Additionally, always adhere to our platform\'s code of conduct, which emphasizes respectful and lawful behavior. For the full terms and conditions, please visit our website at '. $website
            ],
            'seller' => [
                'title' => 'Seller Takeaways',
                'instruction' => 'As a seller on '.config('app.name').', here are the key terms you must accept:',
                'takeaways' => 'As a seller on '.config('app.name').', you need to register with accurate and authorized business details. Ensure your product listings are accurate, up-to-date, and comply with relevant laws. Update your product availability regularly to avoid disappointing customers. Fulfill orders promptly and ensure the quality of your products before delivery. Set transparent and fair prices, and note that '.config('app.name').' may deduct fees—review the fee structure on our platform. Respond to customer inquiries promptly and professionally. You are responsible for safeguarding customer data and using it solely for order fulfillment or communication via '.config('app.name').'. Improper use of data can lead to penalties. Customer feedback through reviews and ratings is essential for improving your business. In case of disputes, try to resolve them directly with the buyer, but '.config('app.name').' can mediate if necessary. Unethical behavior or violation of the terms may lead to the suspension or termination of your account. Additionally, the shortcodes provided by '.config('app.name').' remain our property and may be reassigned if your subscription ends. For the full terms and conditions, please visit our website at '. $website
            ]
        ];
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
     * Redirect the user to the Google authentication page.
     *
     * @param string|null $storeId
     * @return RedirectResponse
     */
    public function redirectToGoogle(string|null $storeId = null): RedirectResponse
    {
        if ($storeId) {
            Session::put('social_login_store_id', $storeId);
        }
        return Socialite::driver('google')->redirect();
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

        // Retrieve store_id from session
        $storeId = Session::get('social_login_store_id');

        if ($storeId) {
            $params['store_id'] = $storeId;
            Session::forget('social_login_store_id'); // Clean up session
        }

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
     * Redirect the user to the Facebook authentication page.
     *
     * @param string|null $storeId
     * @return RedirectResponse
     */
    public function redirectToFacebook(string|null $storeId = null): RedirectResponse
    {
        if ($storeId) {
            Session::put('social_login_store_id', $storeId);
        }
        return Socialite::driver('facebook')->redirect();
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

        // Retrieve store_id from session
        $storeId = Session::get('social_login_store_id');

        if ($storeId) {
            $params['store_id'] = $storeId;
            Session::forget('social_login_store_id'); // Clean up session
        }

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
     * Redirect the user to the LinkedIn authentication page.
     *
     * @param string|null $storeId
     * @return RedirectResponse
     */
    public function redirectToLinkedIn(string|null $storeId = null): RedirectResponse
    {
        if ($storeId) {
            Session::put('social_login_store_id', $storeId);
        }
        return Socialite::driver('linkedin-openid')->redirect();
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

        // Retrieve store_id from session
        $storeId = Session::get('social_login_store_id');

        if ($storeId) {
            $params['store_id'] = $storeId;
            Session::forget('social_login_store_id'); // Clean up session
        }

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
     * Show authenticated user.
     *
     * @param User $user
     * @return UserResource
     */
    public function showAuthUser(User $user): UserResource
    {
        if($this->hasRequestRelationships()) {
            $user->load($this->getRequestRelationships());
        }

        if($this->hasRequestCountableRelationships()) {
            $user->loadCount($this->getRequestCountableRelationships());
        }

        return new UserResource($user);
    }

    /**
     * Update authenticated user.
     *
     * @param User $user
     * @param array $data
     * @return array
     */
    public function updateAuthUser(User $user, array $data): array
    {
        // Hash password if provided
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $originalEmail = $user->email;
        $email = $data['email'] ?? $originalEmail;
        $emailChanged = !is_null($email) && $email !== $originalEmail;

        // Update user
        $user->update($data);

        // If email changed, send verification email
        if ($emailChanged) {

            $user->email_verified_at = null;
            $user->save();

            $this->sendEmailVerification($user, EmailVerificationType::UPDATED_EMAIL);
        }

        (new UssdService)->cacheManager($user)->forget();

        return [
            'message' => 'Account updated successfully.'
        ];
    }

    /**
     * Log out the authenticated user.
     *
     * @param array $data
     * @return array
     */
    public function logout(array $data): array
    {
        /** @var User $user */
        $user = Auth::user();
        $user->currentAccessToken()?->delete();

        return [
            'message' => 'Logged out successfully'
        ];
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

    /**
     * Send email verification token.
     *
     * @param User $user
     * @param EmailVerificationType $emailVerificationType
     * @param string|null $storeId
     * @return void
     */
    public function sendEmailVerification(User $user, EmailVerificationType $emailVerificationType, string|null $storeId = null): void
    {
        $token = Str::random(60);

        DB::table('email_verification_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => hash('sha256', $token),
                'created_at' => now(),
                'expires_at' => now()->addDays(7) // 7 days expiration
            ]
        );

        $verificationUrl = config('app.url') . '/auth/verify-email?' .
                          'token=' . $token .
                          '&email=' . urlencode($user->email) .
                          '&type=' . $emailVerificationType->value;

        if ($emailVerificationType == EmailVerificationType::REGISTRATION_EMAIL) {
            Mail::to($user->email)->send(new VerifyRegistrationEmail($user->email, $user->first_name, $verificationUrl));
        } elseif ($emailVerificationType == EmailVerificationType::UPDATED_EMAIL) {
            Mail::to($user->email)->send(new VerifyUpdatedEmail($user->email, $user->first_name, $verificationUrl));
        } elseif ($emailVerificationType == EmailVerificationType::INVITED_EMAIL) {
            Mail::to($user->email)->send(new TeamMemberInvitation($user->email, $user->first_name, $storeId, $verificationUrl));
        }
    }
}
