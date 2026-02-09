@extends('layouts.admin')

@section('title', 'Add Member')

@section('content')
<div class="space-y-6">
    <!-- Back Button & Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.members') }}" class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-slate-400 hover:text-white transition-colors">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <h1 class="text-xl font-bold text-white" style="font-family: 'Bebas Neue', sans-serif;">Add New Member</h1>
    </div>

    <!-- Form -->
    <div class="glass rounded-2xl p-6 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
        <div class="relative z-10">
            @if ($errors->any())
                <div class="mb-4 p-3 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 text-sm">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.members.store') }}" method="POST" class="space-y-4" enctype="multipart/form-data">
                @csrf

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">First Name</label>
                        <input type="text" name="first_name" value="{{ old('first_name') }}" required
                            class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 transition-colors"
                            placeholder="John">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Last Name</label>
                        <input type="text" name="last_name" value="{{ old('last_name') }}" required
                            class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 transition-colors"
                            placeholder="Doe">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Chinese Name (optional)</label>
                    <input type="text" name="chinese_name" value="{{ old('chinese_name') }}"
                        class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 transition-colors"
                        placeholder="中文姓名">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 transition-colors"
                        placeholder="john@example.com">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Password</label>
                    <input type="password" name="password" required
                        class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 transition-colors"
                        placeholder="Minimum 8 characters">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Phone</label>
                        <input type="tel" name="phone" value="{{ old('phone') }}"
                            class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 transition-colors"
                            placeholder="+886 912 345 678">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Date of Birth</label>
                        <input type="date" name="dob" value="{{ old('dob') }}"
                            class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white focus:outline-none focus:border-blue-500 transition-colors">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Line ID</label>
                        <input type="text" name="line_id" value="{{ old('line_id') }}"
                            class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 transition-colors"
                            placeholder="Line ID">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Gender</label>
                        <select name="gender"
                            class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white focus:outline-none focus:border-blue-500 transition-colors">
                            <option value="">—</option>
                            <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Profile Picture (optional)</label>
                    <input type="file" id="member-create-avatar" accept="image/jpeg,image/png,image/webp,image/gif" class="hidden">
                    <input type="hidden" name="avatar_data" id="member-create-avatar-data" value="">
                    <div class="flex items-center gap-3">
                        <button type="button" id="member-create-avatar-trigger" class="px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-slate-400 hover:text-white hover:border-blue-500 transition-colors text-sm">
                            Profile Picture
                        </button>
                        <span id="member-create-avatar-filename" class="text-slate-500 text-sm"></span>
                    </div>
                    <p class="text-slate-500 text-xs mt-1">Max 1MB. You can crop the area after selecting.</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Age Group</label>
                        <select name="age_group" id="age_group" required
                            class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white focus:outline-none focus:border-blue-500 transition-colors">
                            <option value="Adults" {{ old('age_group', 'Adults') === 'Adults' ? 'selected' : '' }}>Adults</option>
                            <option value="Kids" {{ old('age_group') === 'Kids' ? 'selected' : '' }}>Kids</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Belt Rank</label>
                        <select name="rank" id="rank" required
                            class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white focus:outline-none focus:border-blue-500 transition-colors">
                            <option value="White" {{ old('rank', 'White') === 'White' ? 'selected' : '' }}>White</option>
                            <option value="Grey" {{ old('rank') === 'Grey' ? 'selected' : '' }}>Grey</option>
                            <option value="Yellow" {{ old('rank') === 'Yellow' ? 'selected' : '' }}>Yellow</option>
                            <option value="Orange" {{ old('rank') === 'Orange' ? 'selected' : '' }}>Orange</option>
                            <option value="Green" {{ old('rank') === 'Green' ? 'selected' : '' }}>Green</option>
                            <option value="Blue" {{ old('rank') === 'Blue' ? 'selected' : '' }}>Blue</option>
                            <option value="Purple" {{ old('rank') === 'Purple' ? 'selected' : '' }}>Purple</option>
                            <option value="Brown" {{ old('rank') === 'Brown' ? 'selected' : '' }}>Brown</option>
                            <option value="Black" {{ old('rank') === 'Black' ? 'selected' : '' }}>Black</option>
                        </select>
                    </div>
                </div>

                <div id="belt_variation_row" class="hidden">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Belt Variation (Kids)</label>
                    <select name="belt_variation"
                        class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white focus:outline-none focus:border-blue-500 transition-colors">
                        <option value="">—</option>
                        <option value="white" {{ old('belt_variation') === 'white' ? 'selected' : '' }}>White Bar</option>
                        <option value="solid" {{ old('belt_variation') === 'solid' ? 'selected' : '' }}>Solid (No Bar)</option>
                        <option value="black" {{ old('belt_variation') === 'black' ? 'selected' : '' }}>Black Bar</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Stripes</label>
                    <select name="stripes" required
                        class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white focus:outline-none focus:border-blue-500 transition-colors">
                        @for($i = 0; $i <= 4; $i++)
                            <option value="{{ $i }}" {{ (string)old('stripes', '0') === (string)$i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </div>

                <div class="pt-4">
                    <button type="submit"
                        class="w-full py-3 rounded-lg bg-blue-500 hover:bg-blue-600 text-white font-bold uppercase text-sm tracking-wider transition-colors shadow-lg shadow-blue-500/20">
                        Add Member
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Crop modal (same as register) -->
    <div id="member-create-crop-modal" class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/80 p-4">
        <div class="bg-slate-800 rounded-2xl max-w-2xl w-full max-h-[90vh] flex flex-col overflow-hidden">
            <div class="p-4 border-b border-slate-700 flex justify-between items-center">
                <h3 class="text-white font-bold">{{ __('app.auth.crop_profile_picture') }}</h3>
                <button type="button" id="member-create-crop-close" class="text-slate-400 hover:text-white p-1" aria-label="Close">&times;</button>
            </div>
            <div class="p-4 overflow-hidden flex-1 min-h-0">
                <div class="w-full max-h-[60vh] min-h-[280px] bg-slate-900 mx-auto" style="max-width: 400px;">
                    <img id="member-create-crop-image" src="" alt="Crop" style="max-width: 100%; max-height: 60vh; display: block;">
                </div>
            </div>
            <div class="p-4 border-t border-slate-700 flex justify-end gap-2">
                <button type="button" id="member-create-crop-cancel" class="px-4 py-2 rounded-lg bg-slate-700 text-slate-300 hover:bg-slate-600">{{ app()->getLocale() === 'zh-TW' ? '取消' : 'Cancel' }}</button>
                <button type="button" id="member-create-crop-apply" class="px-4 py-2 rounded-lg bg-blue-500 text-white hover:bg-blue-600">{{ app()->getLocale() === 'zh-TW' ? '套用' : 'Apply' }}</button>
            </div>
        </div>
    </div>

    <link href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var rankSelect = document.getElementById('rank');
        var beltRow = document.getElementById('belt_variation_row');
        var kidsBelts = ['Grey', 'Yellow', 'Orange', 'Green'];
        function toggleBeltVariation() { beltRow.classList.toggle('hidden', !kidsBelts.includes(rankSelect.value)); }
        rankSelect.addEventListener('change', toggleBeltVariation);
        toggleBeltVariation();
    });
    (function() {
        var MAX_AVATAR_BYTES = 1024 * 1024;
        var avatarInput = document.getElementById('member-create-avatar');
        var avatarData = document.getElementById('member-create-avatar-data');
        var avatarTrigger = document.getElementById('member-create-avatar-trigger');
        var avatarFilename = document.getElementById('member-create-avatar-filename');
        var cropModal = document.getElementById('member-create-crop-modal');
        var cropImage = document.getElementById('member-create-crop-image');
        var cropClose = document.getElementById('member-create-crop-close');
        var cropCancel = document.getElementById('member-create-crop-cancel');
        var cropApply = document.getElementById('member-create-crop-apply');
        var cropper = null;
        avatarTrigger.addEventListener('click', function() { avatarInput.click(); });
        avatarInput.addEventListener('change', function(e) {
            var file = e.target.files[0];
            if (!file || !file.type.startsWith('image/')) return;
            var url = URL.createObjectURL(file);
            cropModal.classList.remove('hidden');
            cropModal.classList.add('flex');
            if (cropper) { cropper.destroy(); cropper = null; }
            cropImage.onload = function() {
                cropImage.onload = null;
                cropper = new Cropper(cropImage, { aspectRatio: 1, viewMode: 1, dragMode: 'move', autoCropArea: 0.8, background: false, guides: true, center: true, highlight: false });
            };
            cropImage.src = url;
        });
        function closeCropModal() {
            cropModal.classList.add('hidden');
            cropModal.classList.remove('flex');
            if (cropper) { cropper.destroy(); cropper = null; }
            if (cropImage.src) URL.revokeObjectURL(cropImage.src);
            cropImage.src = '';
            avatarInput.value = '';
        }
        cropClose.addEventListener('click', closeCropModal);
        cropCancel.addEventListener('click', closeCropModal);
        cropApply.addEventListener('click', function() {
            if (!cropper) return;
            var canvas = cropper.getCroppedCanvas({ maxWidth: 800, maxHeight: 800, imageSmoothingQuality: 'high' });
            if (!canvas) return;
            function toBlobWithQuality(quality) { return new Promise(function(resolve) { canvas.toBlob(resolve, 'image/jpeg', quality); }); }
            (function tryQuality(quality) {
                toBlobWithQuality(quality).then(function(blob) {
                    if (blob.size <= MAX_AVATAR_BYTES || quality <= 0.2) {
                        var reader = new FileReader();
                        reader.onloadend = function() {
                            avatarData.value = reader.result;
                            avatarFilename.textContent = (blob.size / 1024).toFixed(1) + ' KB';
                            closeCropModal();
                        };
                        reader.readAsDataURL(blob);
                        return;
                    }
                    tryQuality(Math.max(0.2, quality - 0.1));
                });
            })(0.9);
        });
    })();
    </script>

    <!-- Info Card -->
</div>
@endsection
