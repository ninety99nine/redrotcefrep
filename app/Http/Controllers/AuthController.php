<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use App\Http\Resources\UserResource;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\LogoutRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ShowAuthUserRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\ValidateTokenRequest;
use App\Http\Requests\Auth\SetupPasswordRequest;
use App\Http\Requests\Auth\UpdateAuthUserRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ShowSocialLoginLinksRequest;

class AuthController extends Controller
{
    /**
     * @var AuthService
     */
    protected $service;

    /**
     * AuthController constructor.
     *
     * @param AuthService $service
     */
    public function __construct(AuthService $service)
    {
        $this->service = $service;
    }

    /**
     * Login user.
     *
     * @param LoginRequest $request
     * @return array
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();
        $token = $this->service->login($credentials);

        return [
            'token' => $token,
            'message' => 'Login successful'
        ];
    }

    /**
     * Register user.
     *
     * @param RegisterRequest $request
     * @return array
     */
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();
        $token = $this->service->register($data);

        return [
            'token' => $token,
            'message' => 'Login successful'
        ];
    }

    /**
     * Request a password reset link.
     *
     * @param ForgotPasswordRequest $request
     * @return array
     */
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $data = $request->validated();
        $this->service->sendPasswordResetLink($data['email']);

        return [
            'message' => 'A password reset link has been sent to your email.'
        ];
    }

    /**
     * Validate a password setup token.
     *
     * @param ValidateTokenRequest $request
     * @return array
     */
    public function validateToken(ValidateTokenRequest $request)
    {
        $data = $request->validated();
        $this->service->validateToken($data['email'], $data['token']);

        return [
            'message' => 'Token is valid.'
        ];
    }

    /**
     * Reset user password using a token.
     *
     * @param ResetPasswordRequest $request
     * @return array
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        $data = $request->validated();
        $this->service->resetPassword($data['email'], $data['token'], $data['password']);

        return [
            'message' => 'Password reset successfully. Please log in with your new password.'
        ];
    }

    /**
     * Set up user password using a token.
     *
     * @param SetupPasswordRequest $request
     * @return array
     */
    public function setupPassword(SetupPasswordRequest $request)
    {
        $data = $request->validated();
        $token = $this->service->setupPassword($data['email'], $data['token'], $data['password']);

        return [
            'token' => $token,
            'message' => 'Password set successfully. You are now logged in.'
        ];
    }

    /**
     * Perform user logout.
     *
     * @param LogoutRequest $request
     * @return array
     */
    public function logout(LogoutRequest $request)
    {
        $this->service->logout();

        return [
            'message' => 'Logged out successfully'
        ];
    }

    /**
     * Show authenticated user.
     *
     * @param ShowAuthUserRequest $request
     * @return UserResource
     */
    public function showAuthUser(ShowAuthUserRequest $request): UserResource
    {
        return new UserResource($request->user());
    }

    /**
     * Update authenticated user.
     *
     * @param UpdateAuthUserRequest $request
     * @return array
     */
    public function updateAuthUser(UpdateAuthUserRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();
        $this->service->updateAuthUser($user, $data);

        return [
            'message' => 'Account updated successfully.'
        ];
    }

    /**
     * Show social login links.
     *
     * @param ShowSocialLoginLinksRequest $request
     * @return array
     */
    public function showSocialLoginLinks(ShowSocialLoginLinksRequest $request)
    {
        return $this->service->showSocialLoginLinks();
    }

    /**
     * Redirect the user to the Google authentication page.
     *
     * @return RedirectResponse
     */
    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle the Google callback.
     *
     * @return RedirectResponse
     */
    public function handleGoogleCallback(): RedirectResponse
    {
        return $this->service->handleGoogleCallback();
    }

    /**
     * Redirect the user to the Facebook authentication page.
     *
     * @return RedirectResponse
     */
    public function redirectToFacebook(): RedirectResponse
    {
        return Socialite::driver('facebook')->redirect();
    }

    /**
     * Handle the Facebook callback.
     *
     * @return RedirectResponse
     */
    public function handleFacebookCallback(): RedirectResponse
    {
        return $this->service->handleFacebookCallback();
    }

    /**
     * Redirect the user to the LinkedIn authentication page.
     *
     * @return RedirectResponse
     */
    public function redirectToLinkedIn(): RedirectResponse
    {
        return Socialite::driver('linkedin-openid')->redirect();
    }

    /**
     * Handle the LinkedIn callback.
     *
     * @return RedirectResponse
     */
    public function handleLinkedInCallback(): RedirectResponse
    {
        return $this->service->handleLinkedInCallback();
    }
}
