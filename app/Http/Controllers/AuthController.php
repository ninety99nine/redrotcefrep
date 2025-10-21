<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AuthService;
use App\Http\Resources\UserResource;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\LogoutRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\VerifyEmailRequest;
use App\Http\Requests\Auth\ShowAuthUserRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\ValidateTokenRequest;
use App\Http\Requests\Auth\SetupPasswordRequest;
use App\Http\Requests\Auth\UpdateAuthUserRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResendVerificationRequest;
use App\Http\Requests\Auth\ShowSocialLoginLinksRequest;
use App\Http\Requests\Auth\ShowTermsAndConditionsRequest;

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
    public function login(LoginRequest $request): array
    {
        return $this->service->login($request->validated());
    }

    /**
     * Register user.
     *
     * @param RegisterRequest $request
     * @return array
     */
    public function register(RegisterRequest $request): array
    {
        return $this->service->register($request->validated());
    }

    /**
     * Request a password reset link.
     *
     * @param ForgotPasswordRequest $request
     * @return array
     */
    public function forgotPassword(ForgotPasswordRequest $request): array
    {
        return $this->service->sendPasswordResetLink($request->validated());
    }

    /**
     * Validate a password setup token.
     *
     * @param ValidateTokenRequest $request
     * @return array
     */
    public function validateToken(ValidateTokenRequest $request): array
    {
        return $this->service->validateToken($request->validated());
    }

    /**
     * Reset user password using a token.
     *
     * @param ResetPasswordRequest $request
     * @return array
     */
    public function resetPassword(ResetPasswordRequest $request): array
    {
        return $this->service->resetPassword($request->validated());
    }

    /**
     * Verify email using a token.
     *
     * @param VerifyEmailRequest $request
     * @return array
     */
    public function verifyEmail(VerifyEmailRequest $request): array
    {
        return $this->service->verifyEmail($request->validated());
    }

    /**
     * Resend email verification.
     *
     * @param ResendVerificationRequest $request
     * @return array
     */
    public function resendEmailVerification(ResendVerificationRequest $request): array
    {
        return $this->service->resendEmailVerification($request->validated());
    }

    /**
     * Set up user password using a token.
     *
     * @param SetupPasswordRequest $request
     * @return array
     */
    public function setupPassword(SetupPasswordRequest $request): array
    {
        return $this->service->setupPassword($request->validated());
    }

    /**
     * Show social login links.
     *
     * @param ShowTermsAndConditionsRequest $request
     * @return array
     */
    public function showTermsAndConditions(ShowTermsAndConditionsRequest $request): array
    {
        return $this->service->showTermsAndConditions();
    }

    /**
     * Show social login links.
     *
     * @param ShowSocialLoginLinksRequest $request
     * @return array
     */
    public function showSocialLoginLinks(ShowSocialLoginLinksRequest $request): array
    {
        return $this->service->showSocialLoginLinks();
    }

    /**
     * Redirect the user to the Google authentication page.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function redirectToGoogle(Request $request): RedirectResponse
    {
        return $this->service->redirectToGoogle($request->query('store_id'));
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
     * @param Request $request
     * @return RedirectResponse
     */
    public function redirectToFacebook(Request $request): RedirectResponse
    {
        return $this->service->redirectToFacebook($request->query('store_id'));
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
     * @param Request $request
     * @return RedirectResponse
     */
    public function redirectToLinkedIn(Request $request): RedirectResponse
    {
        return $this->service->redirectToLinkedIn($request->query('store_id'));
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

    /**
     * Show authenticated user.
     *
     * @param ShowAuthUserRequest $request
     * @return UserResource
     */
    public function showAuthUser(ShowAuthUserRequest $request): UserResource
    {
        return $this->service->showAuthUser($request->user());
    }

    /**
     * Update authenticated user.
     *
     * @param UpdateAuthUserRequest $request
     * @return array
     */
    public function updateAuthUser(UpdateAuthUserRequest $request): array
    {
        return $this->service->updateAuthUser($request->user(), $request->validated());
    }

    /**
     * Perform user logout.
     *
     * @param LogoutRequest $request
     * @return array
     */
    public function logout(LogoutRequest $request): array
    {
        return $this->service->logout($request->validated());
    }
}
