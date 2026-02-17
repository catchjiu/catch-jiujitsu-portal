<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catch Jiu Jitsu - Taiwan's Premier Jiu Jitsu Academy</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;500;600;700&family=Noto+Sans+TC:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <link href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @if(app()->getLocale() === 'zh-TW')
    <style>body { font-family: 'Noto Sans TC', 'Inter', sans-serif; }</style>
    @endif
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 font-sans antialiased">
    {{-- Menu bar: fixed top, logo left, globe right --}}
    <header class="fixed top-0 left-0 right-0 z-50 h-14 flex items-center justify-between px-4 bg-slate-950/95 backdrop-blur border-b border-white/5">
        <a href="{{ url('/') }}" class="flex items-center gap-2">
            <div class="w-8 h-8 rounded bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center font-bold text-black text-lg" style="font-family: 'Bebas Neue', sans-serif;">C</div>
            <span class="font-bold text-lg tracking-wider text-white" style="font-family: 'Bebas Neue', sans-serif;">CATCH <span class="text-amber-500">JIU JITSU</span></span>
        </a>
        <form action="{{ route('locale.switch') }}" method="POST" class="inline">
            @csrf
            <input type="hidden" name="locale" value="{{ app()->getLocale() === 'zh-TW' ? 'en' : 'zh-TW' }}">
            <button type="submit" class="w-10 h-10 rounded-full flex items-center justify-center text-slate-400 hover:text-white hover:bg-slate-800 transition-colors" title="{{ app()->getLocale() === 'zh-TW' ? 'Switch to English' : '切換至繁體中文' }}" aria-label="{{ app()->getLocale() === 'zh-TW' ? 'Switch to English' : '切換至繁體中文' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
            </button>
        </form>
    </header>
    <div class="min-h-screen flex items-center justify-center pt-14 pb-8 px-4">
    <div class="w-full max-w-sm">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center mx-auto mb-4">
                <span class="text-3xl font-bold text-black" style="font-family: 'Bebas Neue', sans-serif;">C</span>
            </div>
            <h1 class="text-3xl font-bold tracking-wider text-white" style="font-family: 'Bebas Neue', sans-serif;">
                CATCH <span class="text-amber-500">JIU JITSU</span>
            </h1>
            <p class="text-slate-400 text-sm mt-2">{{ __('app.auth.join_the_team') }}</p>
        </div>

        <!-- Register Form -->
        <div class="glass rounded-2xl p-6">
            <h2 class="text-xl font-bold text-white mb-6">{{ __('app.auth.create_account') }}</h2>

            @if ($errors->any())
                <div class="mb-4 p-3 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 text-sm">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" class="space-y-4" id="register-form" enctype="multipart/form-data">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="first_name" class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">{{ __('app.auth.first_name') }}</label>
                        <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" required autofocus
                            class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors">
                    </div>
                    <div>
                        <label for="last_name" class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">{{ __('app.auth.last_name') }}</label>
                        <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}" required
                            class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors">
                    </div>
                </div>
                <div>
                    <label for="email" class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">{{ __('app.auth.email') }}</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required
                        class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors">
                </div>
                <div>
                    <label for="phone" class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">{{ __('app.auth.phone') }}</label>
                    <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" placeholder="+886 912 345 678"
                        class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors">
                </div>
                <div>
                    <label for="dob" class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">{{ __('app.auth.date_of_birth') }}</label>
                    <input type="date" id="dob" name="dob" value="{{ old('dob') }}"
                        class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors">
                </div>
                <div>
                    <label for="line_id" class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">{{ __('app.auth.line_id') }}</label>
                    <input type="text" id="line_id" name="line_id" value="{{ old('line_id') }}" placeholder="Line ID or username"
                        class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors">
                </div>
                <div>
                    <label for="avatar" class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">{{ __('app.auth.profile_picture_optional') }}</label>
                    <input type="file" id="avatar" name="avatar" accept="image/jpeg,image/png,image/webp,image/gif" class="hidden">
                    <input type="hidden" name="avatar_data" id="avatar_data" value="">
                    <div class="flex items-center gap-3">
                        <button type="button" id="avatar-trigger" class="px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-slate-400 hover:text-white hover:border-blue-500 transition-colors text-sm">
                            {{ __('app.auth.profile_picture') }}
                        </button>
                        <span id="avatar-filename" class="text-slate-500 text-sm"></span>
                    </div>
                    <p class="text-slate-500 text-xs mt-1">Max 1MB. You can crop the area after selecting.</p>
                </div>
                <div>
                    <label for="password" class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">{{ __('app.auth.password') }}</label>
                    <input type="password" id="password" name="password" required
                        class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors">
                </div>
                <div>
                    <label for="password_confirmation" class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">{{ __('app.auth.confirm_password') }}</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required
                        class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors">
                </div>

                <!-- Waiver -->
                <div class="flex flex-wrap items-start gap-2">
                    <input type="checkbox" id="waiver_accepted" name="waiver_accepted" value="1" {{ old('waiver_accepted') ? 'checked' : '' }} required
                        class="mt-1.5 h-4 w-4 rounded border-slate-600 bg-slate-800 text-blue-500 focus:ring-blue-500 focus:ring-offset-slate-800">
                    <div class="flex-1 min-w-0">
                        <label for="waiver_accepted" class="text-sm text-slate-300 cursor-pointer">
                            {{ __('waiver.accept_label') }}
                            <button type="button" id="waiver-view-btn" class="ml-1 text-blue-400 hover:text-blue-300 underline font-medium">{{ __('waiver.view_button') }}</button>
                        </label>
                        @error('waiver_accepted')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                <!-- reCAPTCHA -->
                <div class="flex justify-center">
                    <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}" data-theme="dark"></div>
                </div>
                @error('g-recaptcha-response')
                    <p class="text-red-400 text-xs text-center">{{ $message }}</p>
                @enderror

                <button type="submit"
                    class="w-full py-3 rounded-lg bg-blue-500 hover:bg-blue-600 text-white font-bold uppercase text-sm tracking-wider transition-colors shadow-lg shadow-blue-500/20">
                    {{ __('app.auth.create_account') }}
                </button>
            </form>

            <p class="text-center text-slate-400 text-sm mt-6">
                {{ __('app.auth.already_have_account') }}
                <a href="{{ route('login') }}" class="text-blue-400 hover:text-blue-300 font-medium">{{ __('app.auth.sign_in') }}</a>
            </p>
        </div>
    </div>
    </div>

    <!-- Waiver modal -->
    <div id="waiver-modal" class="fixed inset-0 z-[70] hidden items-center justify-center bg-black/80 p-4" aria-modal="true" role="dialog" aria-labelledby="waiver-modal-title">
        <div class="bg-slate-800 rounded-2xl max-w-2xl w-full max-h-[90vh] flex flex-col overflow-hidden">
            <div class="p-4 border-b border-slate-700 flex justify-between items-center shrink-0">
                <h2 id="waiver-modal-title" class="text-lg font-bold text-white">{{ __('waiver.modal_title') }}</h2>
                <button type="button" id="waiver-modal-close" class="text-slate-400 hover:text-white p-1 text-2xl leading-none" aria-label="Close">&times;</button>
            </div>
            <div class="p-4 overflow-y-auto flex-1 min-h-0 text-slate-300 text-sm leading-relaxed whitespace-pre-line">{{ __('waiver.full') }}</div>
            <div class="p-4 border-t border-slate-700 shrink-0">
                <button type="button" id="waiver-modal-ok" class="w-full py-2 rounded-lg bg-blue-500 hover:bg-blue-600 text-white font-medium">{{ app()->getLocale() === 'zh-TW' ? '關閉' : 'Close' }}</button>
            </div>
        </div>
    </div>

    <!-- Crop modal -->
    <div id="crop-modal" class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/80 p-4">
        <div class="bg-slate-800 rounded-2xl max-w-2xl w-full max-h-[90vh] flex flex-col overflow-hidden">
            <div class="p-4 border-b border-slate-700 flex justify-between items-center">
                <h3 class="text-white font-bold">{{ __('app.auth.crop_profile_picture') }}</h3>
                <button type="button" id="crop-modal-close" class="text-slate-400 hover:text-white p-1" aria-label="Close">&times;</button>
            </div>
            <div class="p-4 overflow-hidden flex-1 min-h-0">
                <div class="w-full max-h-[60vh] min-h-[280px] bg-slate-900 mx-auto" style="max-width: 400px;">
                    <img id="crop-image" src="" alt="Crop" style="max-width: 100%; max-height: 60vh; display: block;">
                </div>
            </div>
            <div class="p-4 border-t border-slate-700 flex justify-end gap-2">
                <button type="button" id="crop-cancel" class="px-4 py-2 rounded-lg bg-slate-700 text-slate-300 hover:bg-slate-600">{{ app()->getLocale() === 'zh-TW' ? '取消' : 'Cancel' }}</button>
                <button type="button" id="crop-apply" class="px-4 py-2 rounded-lg bg-blue-500 text-white hover:bg-blue-600">{{ app()->getLocale() === 'zh-TW' ? '套用' : 'Apply' }}</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>
    <script>
(function() {
    // Waiver modal
    var waiverModal = document.getElementById('waiver-modal');
    var waiverViewBtn = document.getElementById('waiver-view-btn');
    var waiverModalClose = document.getElementById('waiver-modal-close');
    var waiverModalOk = document.getElementById('waiver-modal-ok');
    if (waiverViewBtn) waiverViewBtn.addEventListener('click', function(e) { e.preventDefault(); e.stopPropagation(); waiverModal.classList.remove('hidden'); waiverModal.classList.add('flex'); });
    if (waiverModalClose) waiverModalClose.addEventListener('click', function() { waiverModal.classList.add('hidden'); waiverModal.classList.remove('flex'); });
    if (waiverModalOk) waiverModalOk.addEventListener('click', function() { waiverModal.classList.add('hidden'); waiverModal.classList.remove('flex'); });
})();
(function() {
    const MAX_AVATAR_BYTES = 1024 * 1024; // 1MB
    const avatarInput = document.getElementById('avatar');
    const avatarData = document.getElementById('avatar_data');
    const avatarTrigger = document.getElementById('avatar-trigger');
    const avatarFilename = document.getElementById('avatar-filename');
    const cropModal = document.getElementById('crop-modal');
    const cropImage = document.getElementById('crop-image');
    const cropModalClose = document.getElementById('crop-modal-close');
    const cropCancel = document.getElementById('crop-cancel');
    const cropApply = document.getElementById('crop-apply');

    let cropper = null;

    avatarTrigger.addEventListener('click', function() { avatarInput.click(); });

    avatarInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file || !file.type.startsWith('image/')) return;
        const url = URL.createObjectURL(file);
        cropModal.classList.remove('hidden');
        cropModal.classList.add('flex');
        if (cropper) { cropper.destroy(); cropper = null; }
        cropImage.onload = function() {
            cropImage.onload = null;
            cropper = new Cropper(cropImage, {
                aspectRatio: 1,
                viewMode: 1,
                dragMode: 'move',
                autoCropArea: 0.8,
                background: false,
                guides: true,
                center: true,
                highlight: false,
            });
        };
        cropImage.src = url;
    });

    function closeCropModal() {
        cropModal.classList.add('hidden');
        cropModal.classList.remove('flex');
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
        if (cropImage.src) URL.revokeObjectURL(cropImage.src);
        cropImage.src = '';
        avatarInput.value = '';
    }

    cropModalClose.addEventListener('click', closeCropModal);
    cropCancel.addEventListener('click', closeCropModal);

    cropApply.addEventListener('click', function() {
        if (!cropper) return;
        const canvas = cropper.getCroppedCanvas({ maxWidth: 800, maxHeight: 800, imageSmoothingQuality: 'high' });
        if (!canvas) return;

        function toBlobWithQuality(quality) {
            return new Promise(function(resolve) {
                canvas.toBlob(resolve, 'image/jpeg', quality);
            });
        }

        (function tryQuality(quality) {
            toBlobWithQuality(quality).then(function(blob) {
                if (blob.size <= MAX_AVATAR_BYTES || quality <= 0.2) {
                    const reader = new FileReader();
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
</body>
</html>
