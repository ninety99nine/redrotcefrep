@component('mail::message')

<div style="text-align: center; margin-bottom: 20px;">
    <img src="{{ asset('images/logo-black-transparent.png') }}" alt="{{ config('app.name') }} Logo" style="max-width: 150px; height: auto;" />
</div>

# Verify Your Email Address, {{ $firstName }}!

Thank you for signing up with {{ config('app.name') }}. Please verify your email address to activate your account.

@component('mail::button', ['url' => $verificationUrl, 'color' => 'primary'])
Verify Email Address
@endcomponent

If you did not create an account, please ignore this email or contact our support team.

Thanks,
<br>
{{ config('app.name') }}

@endcomponent
