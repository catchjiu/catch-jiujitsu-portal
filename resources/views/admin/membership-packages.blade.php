@extends('layouts.admin')

@section('title', 'Membership Packages')

@section('content')
<div class="space-y-5">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <button onclick="openMenu()" class="text-slate-400 hover:text-white transition-colors">
                <span class="material-symbols-outlined">menu</span>
            </button>
            <h1 class="text-xl font-bold text-white" style="font-family: 'Bebas Neue', sans-serif;">Membership Packages</h1>
        </div>
        <a href="{{ route('admin.packages.create') }}" class="w-10 h-10 rounded-full bg-blue-500 hover:bg-blue-600 flex items-center justify-center text-white transition-colors shadow-lg shadow-blue-500/20">
            <span class="material-symbols-outlined">add</span>
        </a>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 gap-3">
        <div class="glass rounded-2xl p-4 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
            <div class="relative z-10">
                <p class="text-xs text-slate-400 uppercase">Total Packages</p>
                <p class="text-2xl font-bold text-white" style="font-family: 'Bebas Neue', sans-serif;">{{ $packages->count() }}</p>
            </div>
        </div>
        <div class="glass rounded-2xl p-4 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
            <div class="relative z-10">
                <p class="text-xs text-slate-400 uppercase">Active</p>
                <p class="text-2xl font-bold text-emerald-400" style="font-family: 'Bebas Neue', sans-serif;">{{ $packages->where('is_active', true)->count() }}</p>
            </div>
        </div>
    </div>

    <!-- Packages List -->
    <div class="space-y-3">
        @forelse($packages as $package)
            <div class="glass rounded-2xl p-4 relative overflow-hidden {{ !$package->is_active ? 'opacity-50' : '' }}">
                <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
                <div class="relative z-10">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="text-white font-bold">{{ $package->name }}</h3>
                                @if(!$package->is_active)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-700 text-slate-400">Inactive</span>
                                @endif
                                @if($package->age_group !== 'All')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $package->age_group === 'Kids' ? 'bg-green-500/20 text-green-400' : 'bg-blue-500/20 text-blue-400' }}">
                                        {{ $package->age_group }}
                                    </span>
                                @endif
                            </div>
                            <div class="flex items-center gap-3 text-sm">
                                <span class="text-slate-400">
                                    <span class="material-symbols-outlined text-sm align-middle mr-1">schedule</span>
                                    {{ $package->duration_label }}
                                </span>
                                <span class="text-emerald-400 font-semibold">NT${{ number_format($package->price) }}</span>
                            </div>
                            @if($package->description)
                                <p class="text-slate-500 text-xs mt-2">{{ $package->description }}</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-1">
                            <form action="{{ route('admin.packages.toggle', $package->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="p-2 text-slate-400 hover:text-white transition-colors" title="{{ $package->is_active ? 'Deactivate' : 'Activate' }}">
                                    <span class="material-symbols-outlined text-lg">{{ $package->is_active ? 'visibility' : 'visibility_off' }}</span>
                                </button>
                            </form>
                            <a href="{{ route('admin.packages.edit', $package->id) }}" class="p-2 text-slate-400 hover:text-blue-400 transition-colors" title="Edit">
                                <span class="material-symbols-outlined text-lg">edit</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="glass rounded-2xl p-8 text-center">
                <span class="material-symbols-outlined text-4xl text-slate-600 mb-2">inventory_2</span>
                <p class="text-slate-500">No packages yet.</p>
                <a href="{{ route('admin.packages.create') }}" class="text-blue-500 text-sm hover:text-blue-400 mt-2 inline-block">Create your first package</a>
            </div>
        @endforelse
    </div>
</div>
@endsection
