<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="author" content="PT DIKTA">
    <title>Login - DEVTRACK</title>
    <link rel="icon" type="image/png" href="{{ asset('images/devtrack-dt-icon.png') }}">

    <!-- Google Fonts: Inter (Clean Normal UI for letters & numbers), JetBrains Mono (Codes) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS (CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Inter"', '-apple-system', 'BlinkMacSystemFont', '"Segoe UI"', 'Roboto', '"Helvetica Neue"', 'Arial', 'sans-serif'],
                        heading: ['"Inter"', '-apple-system', 'BlinkMacSystemFont', '"Segoe UI"', 'Roboto', 'sans-serif'],
                        poppins: ['"Inter"', '-apple-system', 'BlinkMacSystemFont', '"Segoe UI"', 'Roboto', 'sans-serif'],
                        serif: ['"Inter"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        brand: {
                            red: '#F71616',
                            black: '#0f172a',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        *, html, body, h1, h2, h3, h4, h5, h6, input, optgroup, select, textarea, button, .font-sans, .font-heading, .font-poppins, .font-serif {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
        }
    </style>
</head>
<body class="min-h-full font-sans antialiased flex flex-col justify-center py-12 sm:px-6 lg:px-8 text-slate-800 relative overflow-x-hidden selection:bg-[#F71616] selection:text-white"
      style="background-color: #090a0f; background-image: radial-gradient(ellipse 85% 72% at 50% 45%, #ffffff 0%, #f8fafc 42%, rgba(248, 250, 252, 0.92) 60%, rgba(248, 250, 252, 0.25) 80%, transparent 100%), linear-gradient(135deg, #050609 0%, #0f1117 22%, #181d27 38%, #881337 68%, #dc2626 85%, #F71616 100%); background-attachment: fixed;">

    <!-- Background Image Layer (Telecom & Global Tracking) with 50% Opacity -->
    <div class="fixed inset-0 z-0 pointer-events-none select-none overflow-hidden">
        <img src="{{ asset('images/login-bg.jpg') }}" 
             alt="Login Background" 
             class="w-full h-full object-cover object-center opacity-50">
    </div>

    <!-- Ambient Luxury Glowing Backdrops for Contrast and Depth (Hitam, Putih, Merah) -->
    <div class="fixed -top-28 -left-28 w-[460px] h-[460px] bg-black/50 rounded-full blur-3xl pointer-events-none"></div>
    <div class="fixed top-1/4 left-1/2 -translate-x-1/2 w-[760px] h-[480px] bg-white/40 blur-3xl rounded-full pointer-events-none"></div>
    <div class="fixed -bottom-28 -right-28 w-[540px] h-[540px] bg-[#F71616]/25 rounded-full blur-3xl pointer-events-none"></div>
    <div class="fixed inset-0 bg-[radial-gradient(#111827_1px,transparent_1px)] [background-size:24px_24px] opacity-[0.035] pointer-events-none"></div>

    <!-- Header Logo & Brand: Isolated Logo Without Background Card -->
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center px-4 relative z-10">
        <div class="flex items-center justify-center">
            <img src="{{ asset('images/devtrack-dt-icon.png') }}" 
                 alt="DEVTRACK Logo" 
                 class="w-24 sm:w-28 h-auto drop-shadow-[0_10px_22px_rgba(247,22,22,0.22)] transition-transform duration-300 hover:scale-105">
        </div>
        <div class="mt-3">
            <h1 class="text-3xl sm:text-4xl font-black tracking-tight text-slate-900 leading-none">
                DEV<span class="text-[#F71616]">TRACK</span>
            </h1>
            <p class="text-xs sm:text-[13px] font-bold tracking-[0.25em] text-slate-600 mt-2 uppercase">
                Device Tracking System
            </p>
        </div>
    </div>

    <!-- Form Container Card -->
    <div class="mt-7 sm:mx-auto sm:w-full sm:max-w-md px-4 relative z-10">
        <div class="bg-white/95 backdrop-blur-2xl py-8 px-6 sm:px-10 shadow-[0_20px_60px_-15px_rgba(0,0,0,0.22),0_10px_25px_-10px_rgba(247,22,22,0.18)] rounded-2xl border border-white/80 ring-1 ring-slate-900/5">

            @if(session('error'))
                <div class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if(session('success'))
                <div class="mb-5 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" id="loginForm" class="space-y-5">
                @csrf

                <div>
                    <label for="username" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Username / No. HP / Email
                    </label>
                    <div class="relative">
                        <input id="username" name="username" type="text" required value="{{ old('username') }}" autocomplete="username"
                               autocapitalize="none" autocorrect="off" spellcheck="false"
                               placeholder="Masukkan username, No. HP, atau email"
                               class="w-full px-4 py-3 bg-slate-50/70 border border-slate-300 rounded-xl text-slate-900 placeholder-slate-400 text-sm focus:outline-none focus:ring-2 focus:ring-[#F71616]/25 focus:border-[#F71616] focus:bg-white transition-all font-medium">
                    </div>
                    @error('username')
                        <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Password
                    </label>
                    <div class="relative">
                        <input id="password" name="password" type="password" required autocomplete="current-password"
                               placeholder="Masukkan kata sandi"
                               class="w-full pl-4 pr-11 py-3 bg-slate-50/70 border border-slate-300 rounded-xl text-slate-900 placeholder-slate-400 text-sm focus:outline-none focus:ring-2 focus:ring-[#F71616]/25 focus:border-[#F71616] focus:bg-white transition-all font-medium">
                        <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-700 transition-colors focus:outline-none" title="Lihat / Sembunyikan kata sandi">
                            <svg id="eyeIcon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between text-xs">
                    <label class="flex items-center text-slate-600 cursor-pointer select-none">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded bg-white border-slate-300 text-[#F71616] focus:ring-[#F71616]">
                        <span class="ml-2">Ingat sesi saya</span>
                    </label>
                    <span class="text-slate-500 text-[11px] flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        Koneksi Terenkripsi
                    </span>
                </div>

                <div>
                    <button type="submit" id="loginBtn"
                            class="w-full py-3.5 px-4 rounded-xl bg-gradient-to-r from-slate-950 via-slate-900 to-[#F71616] hover:from-black hover:via-slate-950 hover:to-[#d01010] text-white font-bold text-sm shadow-lg shadow-[#F71616]/25 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#F71616] transition-all duration-200 flex items-center justify-center gap-2.5 disabled:opacity-80 disabled:cursor-wait">
                        <svg id="loginBtnSpinner" class="hidden w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span id="loginBtnText">Masuk ke Sistem</span>
                        <svg id="loginBtnIcon" class="w-4 h-4 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </button>
                </div>
            </form>

        </div>

        <p class="mt-6 text-center text-xs font-semibold text-slate-500 tracking-wider">
            &copy; {{ date('Y') }} DEV<span class="text-[#F71616]">TRACK</span> &bull; Device Tracking System
        </p>
    </div>

    <script>
        function togglePasswordVisibility() {
            const pwdInput = document.getElementById('password');
            if (pwdInput.type === 'password') {
                pwdInput.type = 'text';
            } else {
                pwdInput.type = 'password';
            }
        }

        // Efek loading / refresh minimal 2 detik saat tombol diklik
        let isAuthenticating = false;
        const loginForm = document.getElementById('loginForm');
        const loginBtn = document.getElementById('loginBtn');
        const loginBtnText = document.getElementById('loginBtnText');
        const loginBtnIcon = document.getElementById('loginBtnIcon');
        const loginBtnSpinner = document.getElementById('loginBtnSpinner');

        if (loginForm) {
            loginForm.addEventListener('submit', function (e) {
                if (isAuthenticating) {
                    return;
                }

                const usernameVal = document.getElementById('username').value.trim();
                const passwordVal = document.getElementById('password').value;

                if (!usernameVal || !passwordVal) {
                    return; // Biarkan validasi HTML bawaan berjalan jika kosong
                }

                e.preventDefault();
                isAuthenticating = true;

                // Ubah status tombol menjadi loading visual (tanpa disable atribut agar tidak diblokir browser mobile)
                loginBtn.classList.add('pointer-events-none', 'opacity-80', 'cursor-wait');
                if (loginBtnSpinner) loginBtnSpinner.classList.remove('hidden');
                if (loginBtnIcon) loginBtnIcon.classList.add('hidden');
                if (loginBtnText) loginBtnText.textContent = 'Memverifikasi Data...';

                // Tahan animasi loading minimal 2 detik (2000 ms) sebelum submit
                setTimeout(function () {
                    if (loginBtnText) loginBtnText.textContent = 'Membuka Sesi...';
                    HTMLFormElement.prototype.submit.call(loginForm);
                }, 2000);
            });
        }
    </script>
</body>
</html>
