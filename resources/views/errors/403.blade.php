@extends('layouts.app')

@section('title', '403 – Unauthorized')

@section('content')
<div class="min-h-[60vh] flex flex-col items-center justify-center px-4 text-center">
    <h1 class="text-6xl font-bold text-slate-400 mb-2" style="font-family: 'Bebas Neue', sans-serif;">403</h1>
    <p class="text-xl font-semibold text-white mb-2">Unauthorized access</p>
    <p class="text-slate-400 text-sm max-w-md mb-6">
        This page is for administrators only. If you should have access, make sure you are logged in with an account that has admin rights.
    </p>
    @guest
        <a href="{{ route('login') }}" class="px-6 py-3 rounded-lg bg-blue-500 hover:bg-blue-600 text-white font-semibold transition-colors">
            Log in
        </a>
    @else
        <a href="{{ url('/') }}" class="px-6 py-3 rounded-lg bg-slate-600 hover:bg-slate-500 text-white font-semibold transition-colors">
            Go to dashboard
        </a>
    @endguest
</div>
@endsection
