@extends('layouts.guest')

@section('content')
<div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-2xl shadow-blue-900/30 border border-white/20 overflow-hidden p-8">
    @if (session('message'))
    <div class="mb-4 p-3 rounded-xl bg-green-50 border border-green-200 text-green-700 text-sm">
        {{ session('message') }}
    </div>
@endif

@if (session('mail_error'))
    <div class="mb-4 p-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm">
        {{ session('mail_error') }}
    </div>
@endif
    <div class="text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-100 text-blue-600 mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
        </div>
        <h2 class="text-2xl font-bold text-slate-900">Verify Your Email</h2>
        <p class="mt-2 text-slate-600">
            We sent a verification link to <strong>{{ session('verification_email') ?? 'your email' }}</strong>.
        </p>
        <p class="mt-1 text-sm text-slate-500">
            Please check your inbox (and spam folder) and click the link to activate your account.
        </p>

        {{-- Optional: Resend verification --}}
        <div class="mt-6 flex flex-col sm:flex-row items-center justify-center gap-3">
            <a href="{{ route('login') }}" class="rounded-full bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-blue-500 transition shadow-sm">
                Go to Login
            </a>
            @if (session('verification_email'))
                <form action="{{ route('verify.resend') }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="email" value="{{ session('verification_email') }}">
                    <button type="submit" class="rounded-full border border-slate-200 px-6 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                        Resend Verification Email
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection