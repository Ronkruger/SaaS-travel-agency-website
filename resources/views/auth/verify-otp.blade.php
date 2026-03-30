@extends('layouts.app')
@section('title', 'Verify OTP')

@section('content')
<div class="auth-page">
    <div class="auth-card">
        <div class="auth-header">
            <i class="fas fa-shield-alt"></i>
            <h2>Enter Your OTP</h2>
            <p>We sent a 6-digit code to <strong>{{ $email }}</strong></p>
        </div>

        @if(session('status'))
            <div style="background:#f0fdf4;border:1px solid #86efac;color:#166534;padding:.75rem 1rem;border-radius:.5rem;margin-bottom:1rem;font-size:.875rem;display:flex;align-items:center;gap:.5rem">
                <i class="fas fa-check-circle"></i> {{ session('status') }}
            </div>
        @endif

        @if($errors->has('otp'))
            <div style="background:#fef2f2;border:1px solid #fca5a5;color:#b91c1c;padding:.75rem 1rem;border-radius:.5rem;margin-bottom:1rem;font-size:.875rem;display:flex;align-items:center;gap:.5rem">
                <i class="fas fa-exclamation-circle"></i> {{ $errors->first('otp') }}
            </div>
        @endif

        <form action="{{ route('password.verify.post') }}" method="POST" class="auth-form" id="otp-form">
            @csrf

            {{-- 6 individual digit boxes --}}
            <div class="form-group" style="text-align:center">
                <label style="margin-bottom:.75rem;display:block"><i class="fas fa-lock"></i> 6-Digit Code</label>
                <div id="otp-inputs" style="display:flex;justify-content:center;gap:.5rem">
                    @for($i = 0; $i < 6; $i++)
                        <input
                            type="text"
                            inputmode="numeric"
                            maxlength="1"
                            class="form-control otp-digit"
                            style="width:3rem;height:3.25rem;text-align:center;font-size:1.5rem;font-weight:700;padding:.25rem;letter-spacing:0"
                            autocomplete="off"
                        >
                    @endfor
                </div>
                {{-- Hidden combined input --}}
                <input type="hidden" name="otp" id="otp-value">
            </div>

            <button type="submit" class="btn btn-primary btn-block" id="otp-submit" disabled>
                <i class="fas fa-check-circle"></i> Verify Code
            </button>
        </form>

        <div class="auth-footer">
            Didn't receive it? <a href="{{ route('password.request') }}">Resend OTP</a>
        </div>
    </div>
</div>

<script>
(function () {
    const inputs = document.querySelectorAll('.otp-digit');
    const hidden = document.getElementById('otp-value');
    const submit = document.getElementById('otp-submit');
    const form   = document.getElementById('otp-form');

    function sync() {
        const val = Array.from(inputs).map(i => i.value).join('');
        hidden.value = val;
        submit.disabled = val.length < 6;
    }

    inputs.forEach(function(input, idx) {
        input.addEventListener('input', function() {
            // Only allow digits
            this.value = this.value.replace(/\D/g, '').slice(-1);
            sync();
            if (this.value && idx < inputs.length - 1) {
                inputs[idx + 1].focus();
            }
        });

        input.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' && !this.value && idx > 0) {
                inputs[idx - 1].focus();
            }
        });

        input.addEventListener('paste', function(e) {
            e.preventDefault();
            const pasted = (e.clipboardData || window.clipboardData)
                .getData('text').replace(/\D/g, '').slice(0, 6);
            pasted.split('').forEach(function(ch, i) {
                if (inputs[i]) inputs[i].value = ch;
            });
            sync();
            const nextEmpty = Array.from(inputs).findIndex(i => !i.value);
            if (nextEmpty >= 0) inputs[nextEmpty].focus();
            else inputs[5].focus();
        });
    });

    inputs[0].focus();
})();
</script>
@endsection
