@extends('central.layouts.app')
@section('title', 'Platform Admin Login')

@push('styles')
<style>
    body { background: linear-gradient(135deg, #0A2D74, #1a47a0); }
    main { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem; }
    .login-card { background: #fff; border-radius: 16px; padding: 3rem; width: 100%; max-width: 420px; box-shadow: 0 20px 60px rgba(0,0,0,.3); }
    .login-card .logo { text-align: center; margin-bottom: 2rem; }
    .login-card .logo .brand { font-size: 1.5rem; font-weight: 800; color: #0A2D74; }
    .login-card .logo .brand span { color: #F5A623; }
    .login-card .logo .subtitle { color: #6b7280; font-size: .9rem; margin-top: .3rem; }
    h2 { font-size: 1.5rem; font-weight: 800; color: #0A2D74; margin-bottom: .3rem; }
    .sub { color: #6b7280; font-size: .9rem; margin-bottom: 2rem; }
    .form-group { margin-bottom: 1.2rem; }
    .form-group label { display: block; font-weight: 600; font-size: .88rem; margin-bottom: .4rem; }
    .form-group input { width: 100%; padding: .75rem 1rem; border: 2px solid #e5e7eb; border-radius: 8px; font-family: inherit; font-size: .95rem; }
    .form-group input:focus { outline: none; border-color: #0A2D74; }
    .btn-submit { width: 100%; padding: .85rem; background: #0A2D74; color: #fff; border: none; border-radius: 8px; font-weight: 700; font-size: .95rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: .5rem; margin-top: .5rem; transition: background .2s; }
    .btn-submit:hover { background: #1a47a0; }
    .error { background: #fee2e2; color: #991b1b; padding: .8rem 1rem; border-radius: 8px; font-size: .88rem; margin-bottom: 1rem; display: flex; gap: .5rem; }
</style>
@endpush

@section('content')
<div class="login-card">
    <div class="logo">
        <div class="brand">Tour<span>SaaS</span></div>
        <div class="subtitle">Platform Administration</div>
    </div>

    @if($errors->any())
        <div class="error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('platform.login') }}">
        @csrf
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="{{ old('email') }}" placeholder="admin@platform.com" required autofocus>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="Platform admin password" required>
        </div>
        <button type="submit" class="btn-submit">
            <i class="fas fa-shield-alt"></i> Access Platform
        </button>
    </form>
</div>
@endsection

@push('scripts')
<script>
window.addEventListener('pageshow', function (event) {
    if (event.persisted) {
        window.location.reload();
    }
});
</script>
@endpush
