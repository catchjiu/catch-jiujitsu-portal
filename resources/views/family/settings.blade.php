@extends('layouts.app')

@section('title', app()->getLocale() === 'zh-TW' ? '家庭設定' : 'Family – Switch member')

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('family.dashboard') }}" class="text-slate-400 hover:text-white transition-colors">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <h1 class="text-xl font-bold text-white" style="font-family: 'Bebas Neue', sans-serif;">
            {{ app()->getLocale() === 'zh-TW' ? '切換家庭成員' : 'Switch family member' }}
        </h1>
    </div>

    <p class="text-slate-400 text-sm">
        {{ app()->getLocale() === 'zh-TW' ? '選擇要代為預約課程與查看付款的成員。' : 'Choose which family member to book classes and view payments for.' }}
    </p>

    <div class="space-y-2">
        @foreach($familyMembers as $member)
            @php 
                $isCurrent = session('viewing_family_user_id') == $member->id; 
                $hasMembership = $member->hasActiveMembership() || $member->isGratis();
            @endphp
            <form action="{{ route('family.switch') }}" method="POST" class="block">
                @csrf
                <input type="hidden" name="user_id" value="{{ $member->id }}">
                <button type="submit" class="w-full flex items-center gap-4 p-4 rounded-xl border-2 transition-colors text-left {{ $isCurrent ? 'bg-blue-500/20 border-blue-500/50' : 'bg-slate-800/40 border-slate-700/30 hover:bg-slate-800/60' }}">
                    @if($member->avatar)
                        <img src="{{ $member->avatar }}" alt="" class="w-14 h-14 rounded-full object-cover border-2 border-slate-600 flex-shrink-0">
                    @else
                        <div class="w-14 h-14 rounded-full bg-slate-700 border-2 border-slate-600 flex items-center justify-center text-slate-400 font-bold text-lg flex-shrink-0">
                            {{ strtoupper(substr($member->name, 0, 2)) }}
                        </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <p class="text-white font-semibold truncate">{{ $member->name }}</p>
                        <p class="text-slate-500 text-sm">{{ $member->rank }} Belt @if($member->age_group) • {{ $member->age_group }} @endif</p>
                        @if($hasMembership)
                            <span class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-500/20 text-emerald-400">{{ app()->getLocale() === 'zh-TW' ? '有效會籍' : 'Active membership' }}</span>
                        @endif
                    </div>
                    @if($isCurrent)
                        <span class="px-2 py-1 rounded text-[10px] font-bold uppercase bg-blue-500/30 text-blue-400">{{ app()->getLocale() === 'zh-TW' ? '目前' : 'Current' }}</span>
                    @else
                        <span class="text-slate-500 text-sm">{{ app()->getLocale() === 'zh-TW' ? '切換' : 'Switch' }}</span>
                    @endif
                </button>
            </form>
        @endforeach
    </div>
</div>
@endsection
