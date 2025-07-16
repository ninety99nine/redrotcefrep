@component('mail::message')

<div style="text-align: center; margin-bottom: 20px;">
    <img src="{{ asset('images/logo-black-transparent.png') }}" alt="Perfect Order Logo" style="max-width: 150px; height: auto;" />
</div>

# Welcome to Perfect Order, {{ $firstName }}!

We’re thrilled to have you on board. Your account has been successfully created.

**Email:** {{ $email }}

Get started by exploring your dashboard and setting up your e-commerce store.

@component('mail::button', ['url' => config('app.url') . '/dashboard', 'color' => 'primary'])
Go to Dashboard
@endcomponent

If you have any questions, feel free to contact our support team.

Thanks,
<br>
Perfect Order

@endcomponent
