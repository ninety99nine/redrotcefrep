@component('mail::message')

<div style="text-align: center; margin-bottom: 20px;">
    <img src="{{ asset('images/logo-black-transparent.png') }}" alt="Perfect Order Logo" style="max-width: 150px; height: auto;" />
</div>

# You’ve Been Invited to Join {{ $storeName }}!

Hello {{ $firstName ?: 'there' }},

You’ve been invited to join the team for **{{ $storeName }}** on Perfect Order. To get started, please verify your email address to activate your account.

**Email:** {{ $email }}

@component('mail::button', ['url' => $verificationUrl, 'color' => 'primary'])
Verify Email Address
@endcomponent

If you did not expect this invitation, please ignore this email or contact our support team.

Thanks,
<br>
Perfect Order

@endcomponent
