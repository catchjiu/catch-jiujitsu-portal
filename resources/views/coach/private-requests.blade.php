@extends('layouts.app')

@section('title', app()->getLocale() === 'zh-TW' ? '私教預約請求' : 'Private Class Requests')

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('coach.private-availability') }}" class="text-slate-400 hover:text-white transition-colors">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <div>
            <h1 class="text-xl font-bold text-white" style="font-family: 'Bebas Neue', sans-serif;">{{ app()->getLocale() === 'zh-TW' ? '私教預約請求' : 'Private Class Requests' }}</h1>
            <p class="text-slate-500 text-sm">{{ app()->getLocale() === 'zh-TW' ? '接受或拒絕會員的私教預約' : 'Accept or decline private class requests from members' }}</p>
        </div>
    </div>

    @if(session('success'))
        <div class="p-3 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm">{{ session('success') }}</div>
    @endif

    @if($pendingRequests->isEmpty())
        <div class="glass rounded-2xl p-8 text-center">
            <span class="material-symbols-outlined text-5xl text-slate-600 mb-4">inbox</span>
            <p class="text-slate-400">{{ app()->getLocale() === 'zh-TW' ? '目前沒有待處理的預約請求' : 'No pending requests.' }}</p>
            <a href="{{ route('coach.private-availability') }}" class="inline-block mt-4 px-4 py-2 rounded-lg bg-blue-500/20 text-blue-400 text-sm font-semibold hover:bg-blue-500/30 transition-colors">{{ app()->getLocale() === 'zh-TW' ? '管理時段' : 'Manage availability' }}</a>
        </div>
    @else
        <div class="space-y-4">
            @foreach($pendingRequests as $req)
                <div class="glass rounded-2xl p-5 border border-amber-500/30 relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
                    <div class="relative z-10 flex flex-col sm:flex-row sm:items-center gap-4">
                        <div class="flex items-center gap-4 flex-1">
                            @if($req->member->avatar)
                                <img src="{{ $req->member->avatar }}" alt="" class="w-14 h-14 rounded-full object-cover border-2 border-slate-600 flex-shrink-0">
                            @else
                                <div class="w-14 h-14 rounded-full bg-slate-700 border-2 border-slate-600 flex items-center justify-center text-slate-400 font-bold text-lg flex-shrink-0">{{ strtoupper(substr($req->member->name, 0, 2)) }}</div>
                            @endif
                            <div class="min-w-0">
                                <p class="text-white font-semibold truncate">{{ $req->member->name }}</p>
                                <p class="text-slate-500 text-sm">{{ $req->scheduled_at->format('l, M j \a\t g:i A') }}</p>
                                <p class="text-slate-400 text-sm">{{ $req->duration_minutes }} min @if($req->price) · NT${{ number_format($req->price) }} @endif</p>
                            </div>
                        </div>
                        <div class="flex gap-2 flex-shrink-0">
                            <form action="{{ route('coach.private-request.accept', $req->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="px-4 py-2.5 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold transition-colors flex items-center gap-2">
                                    <span class="material-symbols-outlined text-lg">check</span>
                                    {{ app()->getLocale() === 'zh-TW' ? '接受' : 'Accept' }}
                                </button>
                            </form>
                            <form action="{{ route('coach.private-request.decline', $req->id) }}" method="POST" class="inline" onsubmit="return confirm('{{ app()->getLocale() === 'zh-TW' ? '確定拒絕？' : 'Decline this request?' }}');">
                                @csrf
                                <button type="submit" class="px-4 py-2.5 rounded-lg border border-red-500/50 text-red-400 hover:bg-red-500/10 text-sm font-semibold transition-colors flex items-center gap-2">
                                    <span class="material-symbols-outlined text-lg">close</span>
                                    {{ app()->getLocale() === 'zh-TW' ? '拒絕' : 'Decline' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
