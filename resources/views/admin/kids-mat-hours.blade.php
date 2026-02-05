@extends('layouts.admin')

@section('title', 'Kids Mat Hours')

@section('content')
<div class="space-y-5">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.members') }}" class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-slate-400 hover:text-white transition-colors">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <h1 class="text-xl font-bold text-white" style="font-family: 'Bebas Neue', sans-serif;">Kids Mat Hours</h1>
    </div>

    @if(session('success'))
        <div class="glass rounded-xl p-4 border border-emerald-500/30 text-emerald-400">
            {{ session('success') }}
        </div>
    @endif

    <p class="text-slate-400 text-sm">Edit starting mat hours for Kids members. Total displayed elsewhere = this + hours from class attendance.</p>

    <form action="{{ route('admin.members.kids-mat-hours.update') }}" method="POST" class="glass rounded-2xl overflow-hidden">
        @csrf
        @method('PUT')
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-slate-700 bg-slate-800/50">
                        <th class="text-left py-3 px-4 text-xs font-bold text-slate-400 uppercase tracking-wider">First Name</th>
                        <th class="text-left py-3 px-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Last Name</th>
                        <th class="text-left py-3 px-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Mat Hours</th>
                        <th class="w-20"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kids as $user)
                        <tr class="border-b border-slate-700/50 hover:bg-slate-800/30">
                            <td class="py-3 px-4 text-white">{{ $user->first_name }}</td>
                            <td class="py-3 px-4 text-white">{{ $user->last_name }}</td>
                            <td class="py-3 px-4">
                                <input type="number" name="mat_hours[{{ $user->id }}]" value="{{ $user->mat_hours ?? 0 }}" min="0"
                                    class="w-24 px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-white focus:outline-none focus:border-blue-500">
                            </td>
                            <td class="py-3 px-4">
                                <a href="{{ route('admin.members.show', $user->id) }}" class="text-slate-400 hover:text-blue-400 text-sm">Profile</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-10 text-center text-slate-500">No Kids members found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($kids->isNotEmpty())
            <div class="p-4 border-t border-slate-700">
                <button type="submit" class="px-4 py-2 rounded-lg bg-blue-500 text-white font-semibold hover:bg-blue-600 transition-colors">
                    Save Changes
                </button>
            </div>
        @endif
    </form>
</div>
@endsection
