<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Verification - {{ config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>
<body class="min-h-screen bg-linear-to-br flex items-center justify-center p-4">

<div class="w-full max-w-md">

    {{-- Loading State --}}
    @if($isVerifying ?? true)
        <div class="text-center py-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-4 flex items-center justify-center gap-3">
                <svg class="w-8 h-8 text-blue-600 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Verifying Your Payment...
            </h1>
            <p class="text-gray-600">Please wait while we confirm your transaction.</p>
        </div>
    @endif

    {{-- Result State --}}
    @if(isset($transaction) && $transaction)
        {{-- Auto-redirect message + countdown --}}
        @if($transaction->payment_status === 'paid')
            <div class="text-center mb-6">
                <p class="text-sm text-gray-600 bg-white rounded-lg py-3 px-6 inline-block shadow-md">
                    Redirecting in <strong id="countdown">5</strong> second<span id="plural">s</span>...
                </p>
            </div>
        @endif

        {{-- Error Message --}}
        @if($error ?? false)
            <div class="mb-4 p-6 bg-red-50 border border-red-200 rounded-xl text-red-700 text-center">
                {{ $error }}
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-xl p-8 mb-40 text-center transition-all duration-300 hover:shadow-2xl border-3
            {{ $transaction->payment_status === 'paid'
                ? 'border-green-400'
                : ($transaction->payment_status === 'failed payment'
                    ? 'border-red-400'
                    : 'border-yellow-400') }}">

            {{-- Status Icon & Title --}}
            <div class="flex flex-col items-center mb-6">
                @if($transaction->payment_status === 'paid')
                    <svg class="w-16 h-16 text-green-500 mb-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <h1 class="text-3xl font-bold text-green-600">Payment Successful!</h1>

                @elseif($transaction->payment_status === 'failed payment')
                    <svg class="w-16 h-16 text-red-500 mb-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <h1 class="text-3xl font-bold text-red-600">Payment Failed</h1>

                @else
                    <svg class="w-16 h-16 text-yellow-500 mb-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.414-1.414L11 9.586V6z" clip-rule="evenodd"/>
                    </svg>
                    <h1 class="text-3xl font-bold text-yellow-600">Payment Pending</h1>
                @endif
            </div>

            <p class="text-lg text-gray-700 mb-6">{{ $transaction->description ?? 'Transaction processed' }}</p>

            <div class="text-4xl font-bold text-gray-800 mb-8">
                {{ $transaction->amount?->amount_with_currency ?? '—' }}
            </div>

            {{-- Failure Details --}}
            @if($transaction->payment_status === 'failed payment')
                <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-lg mb-6 text-left text-sm">
                    <p class="font-semibold">{{ $transaction->failure_type ?? 'Payment Failed' }}</p>
                    @if($transaction->failure_reason)
                        <p class="mt-1">{{ $transaction->failure_reason }}</p>
                    @endif
                </div>
            @endif

            {{-- Owner Info --}}
            @if($transaction->owner ?? false)
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-5 mb-6 text-left text-sm text-gray-600">
                    @if($transaction->owner_type === 'order')
                        <strong>Order #{{ $transaction->owner->number }}</strong><br>
                        <span class="text-gray-600">{{ $transaction->owner->summary ?? 'Order payment' }}</span>

                    @elseif($transaction->owner_type === 'pricing plan')
                        <strong class="text-base">{{ $transaction->owner->name }}</strong><br>
                        @php
                            $features = is_array($transaction->owner->features)
                                ? collect($transaction->owner->features)
                                : ($transaction->owner->features ?? collect());
                        @endphp

                        @if($features->isNotEmpty())
                            @php
                                $visible = $features->take(3)->implode(', ');
                                $visible = str_replace(':,', ': ', $visible);
                            @endphp

                            <span class="text-gray-600">
                                {{ $visible }}
                                @if($features->count() > 3)
                                    +{{ $features->count() - 3 }} more
                                @endif
                            </span>
                        @endif
                    @elseif($transaction->owner_type === 'domain')
                        <strong>Domain Purchase</strong><br>
                        <span class="text-gray-600">{{ $transaction->owner->name }}</span>

                    @else
                        {{ ucfirst(str_replace('_', ' ', $transaction->owner_type)) }} Payment
                    @endif
                </div>
            @endif

            {{-- Action Buttons --}}
            <div class="space-y-4">
                @if(in_array($transaction->payment_status, ['pending payment', 'failed payment']))
                    <a href="{{ $transaction->metadata['dpo_payment_url'] ?? '#' }}"
                       class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-4 rounded-3xl transition-all duration-200 active:scale-95 text-center">
                        {{ $transaction->payment_status === 'failed payment' ? 'Retry Payment' : 'Complete Payment' }}
                    </a>
                @endif

                @if($transaction->payment_status === 'paid')
                    <a href="{{ $url }}"
                       class="block w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-4 rounded-3xl transition-all duration-200 active:scale-95 text-center">
                        @if($transaction->owner_type === 'order')
                            My Order
                        @else
                            Go to Dashboard
                        @endif
                    </a>
                @endif
            </div>

            {{-- Footer --}}
            <div class="text-xs text-gray-500 space-y-1 pt-8 border-t mt-10">
                <div>Transaction ID: <span class="font-mono">{{ $transaction->id }}</span></div>
                <div>Date: {{ \Carbon\Carbon::parse($transaction->created_at)->format('M d, Y \a\t h:i A') }}</div>
            </div>
        </div>
    @endif
</div>

{{-- Auto-redirect script (only runs if payment is paid) --}}
@if(isset($transaction) && $transaction->payment_status === 'paid')
    <script>
        let seconds = 5;
        const countdownEl = document.getElementById('countdown');
        const pluralEl = document.getElementById('plural');

        const redirectUrl = "{{ $url }}";

        const timer = setInterval(() => {
            seconds--;
            if (countdownEl) countdownEl.textContent = seconds;
            if (pluralEl) pluralEl.textContent = seconds !== 1 ? 's' : '';

            if (seconds <= 0) {
                clearInterval(timer);
                window.location.href = redirectUrl;
            }
        }, 1000);

        // Fallback: ensure redirect even if JS is blocked
        setTimeout(() => {
            window.location.href = redirectUrl;
        }, 5500);
    </script>
@endif

</body>
</html>
