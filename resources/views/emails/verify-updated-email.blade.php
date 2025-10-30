@component('mail::message')

<div style="text-align: center; margin-bottom: 20px;">
    <img src="{{ asset('images/logo-black-transparent.png') }}" alt="{{ config('app.name') }} Logo" style="max-width: 150px; height: auto;" />
</div>

# Verify Your New Email Address, {{ $firstName }}!

You’ve recently updated your email address to **{{ $email }}**. Please verify your new email address to continue using {{ config('app.name') }}.

@component('mail::button', ['url' => $verificationUrl, 'color' => 'primary'])
Verify Email Address
@endcomponent

If you did not request this change, please contact our support team immediately.

Thanks,
<br>
{{ config('app.name') }}

@endcomponent
