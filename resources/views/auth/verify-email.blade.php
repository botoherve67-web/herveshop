@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="rounded-lg border border-slate-200 bg-white p-8 text-center shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-wide text-teal-600">HerveShop</p>
            <h1 class="mt-3 text-2xl font-bold text-slate-900">Confirmez votre adresse e-mail</h1>
            <p class="mt-4 text-slate-600">
                Un code de verification a ete envoye a <strong>{{ auth()->user()->email }}</strong>.
            </p>

            @php
                $lockedUntil = auth()->user()->email_verification_otp_locked_until;
                $lockMinutes = $lockedUntil && now()->lessThan($lockedUntil)
                    ? ceil(now()->diffInSeconds($lockedUntil) / 60)
                    : 0;
            @endphp

            @if ($lockMinutes > 0)
                <p class="mt-5 text-sm font-semibold text-red-600">
                    Verification bloquee. Reessayez dans {{ $lockMinutes }} minute(s).
                </p>
            @endif

            @if (session('success'))
                <p class="mt-5 text-sm font-medium text-teal-700">{{ session('success') }}</p>
            @endif

            <details class="mt-5 text-left">
                <summary class="cursor-pointer text-sm font-semibold text-teal-700">Changer mon adresse e-mail</summary>
                <form method="POST" action="{{ route('verification.email.update') }}" class="mt-4">
                    @csrf
                    @method('PATCH')
                    <label for="verification-email-input" class="text-sm font-semibold text-slate-700">Nouvelle adresse e-mail</label>
                    <input id="verification-email-input" type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required class="mt-2 w-full rounded-md border border-slate-300 px-4 py-3 text-sm text-slate-900">
                    @error('email')
                        <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                    @enderror
                    <button type="submit" class="mt-3 rounded-md bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-900">
                        Modifier et renvoyer le code
                    </button>
                </form>
            </details>

            <form method="POST" action="{{ route('verification.otp') }}" class="mt-7">
                @csrf
                <label for="otp" class="sr-only">Code OTP</label>
                <input
                    id="otp"
                    type="text"
                    name="otp"
                    value="{{ old('otp') }}"
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    maxlength="6"
                    pattern="[0-9]{6}"
                    required
                    @disabled($lockMinutes > 0)
                    class="w-full rounded-md border border-slate-300 px-4 py-3 text-center text-2xl font-semibold tracking-[0.35em] text-slate-900"
                >
                @error('otp')
                    <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                @enderror
                <button type="submit" @disabled($lockMinutes > 0) class="mt-5 rounded-md bg-teal-600 px-5 py-3 text-sm font-semibold text-white hover:bg-teal-700 disabled:cursor-not-allowed disabled:bg-slate-300">
                    Valider le code
                </button>
            </form>

            @php
                $resendAvailableAt = auth()->user()->email_verification_otp_sent_at?->copy()->addSeconds(60);
                $resendSeconds = $resendAvailableAt && now()->lessThan($resendAvailableAt)
                    ? now()->diffInSeconds($resendAvailableAt)
                    : 0;
            @endphp

            <form method="POST" action="{{ route('verification.send') }}" class="mt-4">
                @csrf
                <button
                    id="resend-otp-button"
                    type="submit"
                    data-seconds="{{ $resendSeconds }}"
                    @disabled($resendSeconds > 0)
                    class="text-sm font-semibold text-teal-700 hover:text-teal-800 disabled:cursor-not-allowed disabled:text-slate-400"
                >
                    Renvoyer un code
                </button>
            </form>
        </div>
    </div>

    <script>
        (function () {
            const button = document.getElementById('resend-otp-button');
            if (!button) return;

            let seconds = Number(button.dataset.seconds || 0);
            const originalText = button.textContent.trim();

            function render() {
                if (seconds <= 0) {
                    button.disabled = false;
                    button.textContent = originalText;
                    return;
                }

                button.disabled = true;
                button.textContent = `Renvoyer dans ${seconds}s`;
                seconds -= 1;
                window.setTimeout(render, 1000);
            }

            render();
        })();
    </script>
@endsection
