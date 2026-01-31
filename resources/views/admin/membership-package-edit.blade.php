@extends('layouts.admin')

@section('title', 'Edit Package')

@section('content')
<div class="space-y-5">
    <!-- Header -->
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.packages.index') }}" class="text-slate-400 hover:text-white transition-colors">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <h1 class="text-xl font-bold text-white" style="font-family: 'Bebas Neue', sans-serif;">Edit Package</h1>
    </div>

    <!-- Form -->
    <form action="{{ route('admin.packages.update', $package->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="glass rounded-2xl p-5 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
            <div class="relative z-10 space-y-5">
                
                <!-- Package Name -->
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Package Name</label>
                    <input type="text" name="name" value="{{ old('name', $package->name) }}" required
                        class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 transition-colors"
                        placeholder="e.g., 1 Month Unlimited">
                    @error('name')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Description (optional)</label>
                    <textarea name="description" rows="2"
                        class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 transition-colors resize-none"
                        placeholder="Brief description of the package">{{ old('description', $package->description) }}</textarea>
                </div>

                <!-- Duration -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Duration Type</label>
                        <select name="duration_type" required
                            class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white focus:outline-none focus:border-blue-500 transition-colors">
                            <option value="days" {{ old('duration_type', $package->duration_type) === 'days' ? 'selected' : '' }}>Days</option>
                            <option value="weeks" {{ old('duration_type', $package->duration_type) === 'weeks' ? 'selected' : '' }}>Weeks</option>
                            <option value="months" {{ old('duration_type', $package->duration_type) === 'months' ? 'selected' : '' }}>Months</option>
                            <option value="years" {{ old('duration_type', $package->duration_type) === 'years' ? 'selected' : '' }}>Years</option>
                            <option value="classes" {{ old('duration_type', $package->duration_type) === 'classes' ? 'selected' : '' }}>Classes</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Duration Value</label>
                        <input type="number" name="duration_value" value="{{ old('duration_value', $package->duration_value) }}" min="1" required
                            class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white focus:outline-none focus:border-blue-500 transition-colors">
                    </div>
                </div>

                <!-- Price -->
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Price (฿)</label>
                    <input type="number" name="price" value="{{ old('price', $package->price) }}" min="0" step="0.01" required
                        class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 transition-colors"
                        placeholder="0.00">
                    @error('price')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Age Group -->
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Age Group</label>
                    <select name="age_group" required
                        class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white focus:outline-none focus:border-blue-500 transition-colors">
                        <option value="All" {{ old('age_group', $package->age_group) === 'All' ? 'selected' : '' }}>All (Adults & Kids)</option>
                        <option value="Adults" {{ old('age_group', $package->age_group) === 'Adults' ? 'selected' : '' }}>Adults Only</option>
                        <option value="Kids" {{ old('age_group', $package->age_group) === 'Kids' ? 'selected' : '' }}>Kids Only</option>
                    </select>
                </div>

                <!-- Active Status -->
                <div class="flex items-center gap-3">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ $package->is_active ? 'checked' : '' }}
                        class="w-5 h-5 rounded bg-slate-800 border-slate-700 text-blue-500 focus:ring-blue-500 focus:ring-offset-0">
                    <label for="is_active" class="text-slate-300">Package is active and available</label>
                </div>

                <!-- Submit -->
                <button type="submit"
                    class="w-full py-3 rounded-lg bg-blue-500 hover:bg-blue-600 text-white font-bold uppercase text-sm tracking-wider transition-colors">
                    Save Changes
                </button>
            </div>
        </div>
    </form>

    <!-- Delete Package -->
    <div class="glass rounded-2xl p-5 relative overflow-hidden border border-red-500/20">
        <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-red-500/5 to-transparent pointer-events-none"></div>
        <div class="relative z-10">
            <h3 class="text-lg font-bold text-red-400 mb-2" style="font-family: 'Bebas Neue', sans-serif;">Danger Zone</h3>
            <p class="text-slate-500 text-sm mb-4">Permanently delete this package.</p>
            
            <form action="{{ route('admin.packages.destroy', $package->id) }}" method="POST" 
                  onsubmit="return confirm('Are you sure you want to delete this package? This action cannot be undone.')">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="w-full py-3 rounded-lg border border-red-500/50 text-red-400 font-bold uppercase text-sm tracking-wider hover:bg-red-500/10 transition-colors">
                    Delete Package
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
