<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Monitoring Teknisi') - DEVTRACK &bull; PT DIKTA</title>
    <link rel="icon" type="image/png" href="{{ asset('images/devtrack-logo.png') }}">

    <!-- Google Fonts: Inter (Clean Normal UI for letters & numbers), JetBrains Mono (Codes) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Theme Script (Prevent FOUC) -->
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <!-- Tailwind CSS (CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
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
                            50: '#eff6ff',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- HTML5-QRCode Scanner -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

    <style>
        [x-cloak] { display: none !important; }

        /* Typography: Clean normal sans-serif font for all letters, numbers, headings, and UI */
        *, html, body, h1, h2, h3, h4, h5, h6, input, optgroup, select, textarea, button, .font-sans, .font-heading, .font-poppins, .font-serif {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
        }

        /* Dark mode global transitions & styling overrides */
        html.dark {
            color-scheme: dark;
        }

        html.dark body {
            background-color: #0b0f19 !important;
            color: #cbd5e1 !important;
        }

        /* Card & Surface Elements */
        html.dark .bg-white {
            background-color: #141c2e !important;
            color: #cbd5e1;
        }
        html.dark .bg-white\/95,
        html.dark .bg-white\/70,
        html.dark .bg-white\/80 {
            background-color: rgba(20, 28, 46, 0.95) !important;
        }
        html.dark .bg-slate-50 {
            background-color: #0d1524 !important;
        }
        html.dark .bg-slate-100 {
            background-color: #0b0f19 !important;
        }
        html.dark .bg-slate-200 {
            background-color: #1e293b !important;
        }

        /* Borders & Separators */
        html.dark .border-slate-200,
        html.dark .border-slate-300,
        html.dark .border-slate-100 {
            border-color: #243048 !important;
        }
        html.dark .divide-slate-200 > :not([hidden]) ~ :not([hidden]),
        html.dark .divide-slate-100 > :not([hidden]) ~ :not([hidden]) {
            border-color: #243048 !important;
        }

        /* Typography */
        html.dark .text-slate-900,
        html.dark .text-slate-800 {
            color: #f1f5f9 !important;
        }
        html.dark .text-slate-700 {
            color: #e2e8f0 !important;
        }
        html.dark .text-slate-600 {
            color: #cbd5e1 !important;
        }
        html.dark .text-slate-500 {
            color: #94a3b8 !important;
        }

        /* Forms, inputs, selects, textareas */
        html.dark input:not([type="checkbox"]):not([type="radio"]):not([type="submit"]):not([type="button"]),
        html.dark select,
        html.dark textarea {
            background-color: #0d1524 !important;
            border-color: #2a3854 !important;
            color: #f8fafc !important;
        }
        html.dark input::placeholder,
        html.dark textarea::placeholder {
            color: #64748b !important;
        }
        html.dark select option {
            background-color: #141c2e;
            color: #f8fafc;
        }

        /* Tables */
        html.dark table tbody tr:hover {
            background-color: #1c263c !important;
        }
        html.dark table thead th {
            background-color: #0d1524 !important;
            color: #94a3b8 !important;
            border-color: #243048 !important;
        }

        /* Cards with accent backgrounds */
        html.dark .bg-emerald-50,
        html.dark .bg-emerald-50\/30 {
            background-color: rgba(16, 185, 129, 0.12) !important;
        }
        html.dark .bg-blue-50,
        html.dark .bg-blue-50\/30 {
            background-color: rgba(59, 130, 246, 0.12) !important;
        }
        html.dark .bg-amber-50,
        html.dark .bg-amber-50\/30 {
            background-color: rgba(245, 158, 11, 0.12) !important;
        }
        html.dark .bg-rose-50,
        html.dark .bg-rose-50\/30 {
            background-color: rgba(244, 63, 94, 0.12) !important;
        }
        html.dark .bg-orange-50,
        html.dark .bg-orange-50\/30 {
            background-color: rgba(249, 115, 22, 0.12) !important;
        }
        html.dark .bg-purple-50,
        html.dark .bg-purple-50\/30,
        html.dark .bg-indigo-50,
        html.dark .bg-indigo-50\/30 {
            background-color: rgba(99, 102, 241, 0.12) !important;
        }

        /* Text colors for badges and alerts */
        html.dark .text-emerald-700,
        html.dark .text-emerald-800 {
            color: #6ee7b7 !important;
        }

        /* Pagination in dark mode */
        html.dark nav[role="navigation"] {
            color: #cbd5e1 !important;
        }
        html.dark nav[role="navigation"] p {
            color: #94a3b8 !important;
        }
        html.dark nav[role="navigation"] span.font-medium {
            color: #f1f5f9 !important;
        }
        html.dark nav[role="navigation"] span.relative,
        html.dark nav[role="navigation"] a.relative {
            background-color: #141c2e !important;
            border-color: #243048 !important;
            color: #cbd5e1 !important;
        }
        html.dark nav[role="navigation"] span[aria-current="page"] span {
            background-color: #2563eb !important;
            border-color: #3b82f6 !important;
            color: #ffffff !important;
        }
        html.dark nav[role="navigation"] a.relative:hover {
            background-color: #1e293b !important;
            color: #ffffff !important;
        }
        html.dark .text-blue-700,
        html.dark .text-blue-800 {
            color: #93c5fd !important;
        }
        html.dark .text-amber-700,
        html.dark .text-amber-800 {
            color: #fcd34d !important;
        }
        html.dark .text-rose-700,
        html.dark .text-rose-800 {
            color: #fda4af !important;
        }
        html.dark .text-orange-700,
        html.dark .text-orange-800 {
            color: #fdba74 !important;
        }

        html.dark .border-emerald-200 { border-color: rgba(16, 185, 129, 0.3) !important; }
        html.dark .border-blue-200 { border-color: rgba(59, 130, 246, 0.3) !important; }
        html.dark .border-amber-200, html.dark .border-amber-300 { border-color: rgba(245, 158, 11, 0.3) !important; }
        html.dark .border-rose-200 { border-color: rgba(244, 63, 94, 0.3) !important; }
        html.dark .border-orange-200 { border-color: rgba(249, 115, 22, 0.3) !important; }

        /* Shadows in dark mode */
        html.dark .shadow-xs,
        html.dark .shadow-sm,
        html.dark .shadow,
        html.dark .shadow-md,
        html.dark .shadow-lg,
        html.dark .shadow-xl {
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.5) !important;
        }
    </style>
</head>
<body class="h-full font-sans antialiased text-slate-800 bg-slate-100 flex flex-col md:flex-row" x-data="{ mobileMenuOpen: false }">

    <!-- Backdrop Mobile -->
    <div x-show="mobileMenuOpen" x-cloak @click="mobileMenuOpen = false" 
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-40 md:hidden"></div>

    @if(auth()->user()->role !== 'teknisi')
    <!-- SIDEBAR BERSIH & RAMPING UNTUK ADMIN & KOORDINATOR -->
    <aside :class="mobileMenuOpen ? 'translate-x-0' : '-translate-x-full'" 
           class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 text-white flex flex-col transition-transform duration-200 ease-in-out md:translate-x-0 md:static md:w-64 shrink-0 shadow-xl">
        
        <!-- Header Brand: Tanpa Background, Hanya Logo & Nama DEVTRACK Saja -->
        <div class="py-3 px-4 border-b border-slate-800 bg-slate-950/70 flex items-center justify-between">
            <a href="{{ auth()->user()->role === 'admin' ? route('admin.dashboard') : (auth()->user()->role === 'koordinator' ? route('coordinator.dashboard') : route('technician.dashboard')) }}" 
               title="Kembali ke Dashboard Utama"
               class="flex items-center transition-transform hover:scale-105">
                <img src="{{ asset('images/devtrack-logo-dark.png') }}" 
                     alt="DEVTRACK" 
                     class="h-12 w-auto object-contain drop-shadow-[0_2px_10px_rgba(34,211,238,0.2)]">
            </a>
            <button @click="mobileMenuOpen = false" class="md:hidden text-slate-400 hover:text-white p-1.5 rounded-lg hover:bg-slate-800 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Role Badge -->
        <div class="px-5 py-3 border-b border-slate-800/80 bg-slate-900/50 flex items-center justify-between">
            <span class="text-[10px] uppercase font-bold text-slate-400">Login Sebagai:</span>
            @if(auth()->user()->role === 'admin')
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">ADMIN</span>
            @elseif(auth()->user()->role === 'koordinator')
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">KOORDINATOR</span>
            @else
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">TEKNISI</span>
            @endif
        </div>

        <!-- Menu Ramping -->
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">

            @if(auth()->user()->role === 'admin')
                <a href="{{ route('admin.dashboard') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span>Dashboard Utama</span>
                </a>

                <a href="{{ route('admin.devices.index') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('admin.devices.*') && !request()->routeIs('admin.devices.stock') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                    <span>Data Perangkat</span>
                </a>

                <a href="{{ route('admin.devices.stock') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('admin.devices.stock') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    <span>Stok Gudang HB</span>
                </a>

                <a href="{{ route('admin.stock-opname.index') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('admin.stock-opname.*') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    <span>Stock Opname</span>
                </a>


                <a href="{{ route('admin.technicians.index') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('admin.technicians.*') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Kelola Teknisi</span>
                </a>

                <a href="{{ route('admin.history.index') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('admin.history.index') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Riwayat Perpindahan</span>
                </a>
            @endif

            @if(auth()->user()->role === 'koordinator')
                <a href="{{ route('coordinator.dashboard') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('coordinator.dashboard') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span>Dashboard Koordinator</span>
                </a>

                <a href="{{ route('coordinator.tracking.index') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('coordinator.tracking.*') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Pelacakan Perangkat</span>
                </a>

                <a href="{{ route('coordinator.work-orders.index') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('coordinator.work-orders.*') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <span>Penugasan NOJAR</span>
                </a>

            @endif

            @if(auth()->user()->role === 'teknisi')
                <a href="{{ route('technician.dashboard') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('technician.dashboard') || request()->routeIs('technician.tasks.*') ? 'bg-amber-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <span>Tugas Lapangan Saya</span>
                </a>

                <a href="{{ route('technician.devices.create') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('technician.devices.create') ? 'bg-amber-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Pendaftaran Perangkat</span>
                </a>

                <a href="{{ route('technician.devices.index') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('technician.devices.index') ? 'bg-amber-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    <span>Perangkat Saya</span>
                </a>

                <a href="{{ route('technician.history.index') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('technician.history.*') ? 'bg-amber-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Riwayat Lapangan</span>
                </a>
            @endif

        </nav>

        <!-- User & Logout -->
        <div class="p-3 border-t border-slate-800 bg-slate-950/70 flex items-center justify-between">
            <div class="min-w-0 pr-2">
                <p class="text-xs font-bold text-white truncate">{{ auth()->user()->name }}</p>
                <p class="text-[10px] text-slate-400 truncate">@<span>{{ auth()->user()->username }}</span></p>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" title="Logout" class="p-1.5 text-slate-400 hover:text-rose-400 hover:bg-slate-800 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </button>
            </form>
        </div>

    </aside>
    @endif

    <!-- MAIN AREA -->
    <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">

        @if(auth()->user()->role !== 'teknisi')
        <!-- TOP BAR ADMIN & KOORDINATOR -->
        <header class="bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 sticky top-0 z-30 transition-colors duration-200">
            <div class="px-4 sm:px-6 h-14 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <button @click="mobileMenuOpen = true" class="md:hidden p-1.5 text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <a href="{{ auth()->user()->role === 'admin' ? route('admin.dashboard') : (auth()->user()->role === 'koordinator' ? route('coordinator.dashboard') : route('technician.dashboard')) }}" class="md:hidden flex items-center">
                        <img src="{{ asset('images/devtrack-logo.png') }}" alt="DEVTRACK Logo" class="h-8 w-auto object-contain dark:hidden">
                        <img src="{{ asset('images/devtrack-logo-white.png') }}" alt="DEVTRACK Logo" class="h-8 w-auto object-contain hidden dark:inline">
                    </a>
                    <div>
                        <h1 class="text-sm font-bold text-slate-900 dark:text-white leading-tight">
                            @yield('page_title', 'Monitoring Teknisi')
                        </h1>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 hidden sm:block">
                            @yield('page_subtitle', 'DEVTRACK &bull; Device Tracking System')
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 sm:gap-3">
                    @if(in_array(auth()->user()->role, ['admin', 'koordinator']))
                    <!-- NOTIFIKASI ADMIN: PERANGKAT DARI LINTASARTA KE STOK HB -->
                    <div class="relative" x-data="adminNotificationApp()" x-cloak>
                        <!-- Tombol Lonceng Notifikasi -->
                        <button type="button" 
                                @click="toggleMenu()"
                                class="relative inline-flex items-center justify-center p-2 rounded-xl text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 border border-slate-300 dark:border-slate-700 transition-all focus:outline-none shadow-xs"
                                :class="open ? 'ring-2 ring-brand-500' : ''"
                                title="Notifikasi Penambahan Perangkat ke Stok HB">
                            <svg class="w-4 h-4 text-slate-700 dark:text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            <!-- Badge Merah Jumlah Unread -->
                            <span x-show="unreadCount > 0" 
                                  class="absolute -top-1 -right-1 flex h-4 min-w-[16px] px-1 items-center justify-center rounded-full bg-rose-600 text-[10px] font-black text-white shadow-xs animate-pulse">
                                <span x-text="unreadCount > 9 ? '9+' : unreadCount"></span>
                            </span>
                        </button>

                        <!-- Dropdown Panel Notifikasi -->
                        <div x-show="open" 
                             @click.away="open = false" 
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                             x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                             class="absolute right-0 mt-2 w-80 sm:w-96 rounded-2xl bg-white dark:bg-[#131c31] border border-slate-200 dark:border-[#243048] shadow-2xl z-50 overflow-hidden">
                            
                            <!-- Header Dropdown -->
                            <div class="px-4 py-3 bg-slate-50 dark:bg-slate-900 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                    </div>
                                    <h4 class="text-xs font-bold text-slate-900 dark:text-white">Notifikasi Perangkat</h4>
                                    <span x-show="unreadCount > 0" class="px-1.5 py-0.5 rounded-full text-[10px] font-black bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300" x-text="unreadCount + ' Baru'"></span>
                                </div>
                                <button type="button" 
                                        x-show="unreadCount > 0"
                                        @click="markAllAsRead()"
                                        class="text-[11px] font-semibold text-brand-600 dark:text-brand-400 hover:underline">
                                    Tandai Semua Dibaca
                                </button>
                            </div>

                            <!-- List Item Notifikasi (Scrollable) -->
                            <div class="max-h-80 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800">
                                <template x-for="item in items" :key="item.id">
                                    <a :href="item.go_url" 
                                       class="p-3.5 block transition-colors group relative hover:bg-slate-50 dark:hover:bg-slate-800/60"
                                       :class="!item.is_read ? 'bg-amber-50/40 dark:bg-amber-950/20' : ''">
                                        <div class="flex items-start gap-3">
                                            <!-- Hub Badge Tag -->
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold shrink-0 mt-0.5 uppercase tracking-wide"
                                                  :class="item.hub === 'hb_ckr' ? 'bg-teal-100 text-teal-800 dark:bg-teal-950 dark:text-teal-300 border border-teal-300 dark:border-teal-700' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700'"
                                                  x-text="item.hub_label || 'HB JKT'">
                                            </span>

                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center justify-between gap-1">
                                                    <p class="text-xs font-bold text-slate-900 dark:text-white truncate group-hover:text-brand-600 transition-colors" x-text="item.title"></p>
                                                    <span x-show="!item.is_read" class="w-2 h-2 rounded-full bg-rose-500 shrink-0"></span>
                                                </div>

                                                <p class="text-[11px] text-slate-600 dark:text-slate-300 line-clamp-2 mt-0.5 leading-snug" x-text="item.message"></p>

                                                <div class="flex items-center justify-between gap-2 mt-1.5 text-[10px] text-slate-400 dark:text-slate-500">
                                                    <span class="font-mono font-bold text-slate-700 dark:text-slate-300" x-text="item.registration_number"></span>
                                                    <span x-text="item.time_ago"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </template>

                                <!-- State Kosong -->
                                <div x-show="items.length === 0" class="py-8 px-4 text-center">
                                    <svg class="w-8 h-8 mx-auto text-slate-300 dark:text-slate-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Belum ada notifikasi perangkat baru</p>
                                </div>
                            </div>

                            <!-- Footer Dropdown -->
                            <div class="px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs font-bold">
                                <a href="{{ route('admin.devices.stock', ['hub' => 'hb_jkt']) }}" class="text-emerald-700 dark:text-emerald-400 hover:underline">
                                    Stok HB JKT &rarr;
                                </a>
                                <a href="{{ route('admin.devices.stock', ['hub' => 'hb_ckr']) }}" class="text-teal-700 dark:text-teal-400 hover:underline">
                                    Stok HB CKR &rarr;
                                </a>
                            </div>

                        </div>
                    </div>
                    @endif

                    <!-- Tombol Toggle Mode Gelap / Terang -->
                    <button type="button" 
                            id="themeToggleBtn"
                            onclick="toggleDarkMode()" 
                            title="Ganti Mode Gelap / Terang"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 border border-slate-300 dark:border-slate-700 rounded-lg transition-all focus:outline-none shadow-xs">
                        <svg class="w-3.5 h-3.5 text-slate-700 dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                        <svg class="w-3.5 h-3.5 text-amber-400 hidden dark:inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <span class="hidden sm:inline">
                            <span class="dark:hidden">Gelap</span>
                            <span class="hidden dark:inline">Terang</span>
                        </span>
                    </button>

                    <button type="button" 
                            id="topbarRefreshBtn"
                            onclick="triggerPageRefresh(this)"
                            title="Segarkan data terbaru (minimal 2 detik)"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 border border-slate-300 dark:border-slate-700 rounded-lg transition-all focus:outline-none disabled:opacity-75 disabled:cursor-wait">
                        <svg id="topbarRefreshIcon" class="w-3.5 h-3.5 text-slate-600 dark:text-slate-300 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <span id="topbarRefreshText" class="hidden sm:inline">Segarkan Data</span>
                    </button>

                    <span class="text-xs text-slate-600 dark:text-slate-300 hidden md:inline">Halo, <strong>{{ auth()->user()->name }}</strong></span>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="px-2.5 py-1 text-xs font-semibold text-rose-700 dark:text-rose-300 bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-100 dark:hover:bg-rose-900/50 border border-rose-200 dark:border-rose-800/60 rounded-lg transition-colors">
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </header>
        @else
        <!-- TOP BAR MOBILE TEKNISI (PERSIS SEPERTI SCREENSHOT USER: AVATAR, ASSET MOVEMENT, TEMA, PROFIL, LOGOUT) -->
        <header class="bg-slate-900 dark:bg-slate-950 text-white border-b border-slate-800 sticky top-0 z-30 transition-colors duration-200 shadow-sm">
            <div class="px-4 py-2.5 flex items-center justify-between max-w-lg mx-auto w-full">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-base shadow-sm shrink-0">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <div class="text-[10px] text-slate-400 font-medium tracking-wide">DEVTRACK</div>
                        <div class="text-sm font-extrabold text-white uppercase tracking-tight truncate max-w-[130px] sm:max-w-xs">
                            {{ auth()->user()->name }}
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-1.5 shrink-0">
                    <!-- Dark / Light Mode Toggle (Sun/Moon) -->
                    <button type="button" 
                            onclick="toggleDarkMode()" 
                            class="w-8 h-8 rounded-full bg-slate-800 hover:bg-slate-700 flex items-center justify-center text-slate-300 transition-colors"
                            title="Ubah Tema Gelap / Terang">
                        <!-- Icon Matahari (saat Dark mode) -->
                        <svg class="w-4 h-4 hidden dark:block text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <!-- Icon Bulan (saat Light mode) -->
                        <svg class="w-4 h-4 block dark:hidden text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                        </svg>
                    </button>

                    <!-- Profil Button -->
                    <button type="button" 
                            onclick="window.dispatchEvent(new CustomEvent('open-profile-modal'))"
                            class="px-3 py-1 rounded-full text-xs font-semibold text-slate-300 bg-slate-800 hover:bg-slate-700 hover:text-white transition-colors">
                        Profil
                    </button>

                    <!-- Logout Button -->
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" 
                                class="px-3 py-1 rounded-full text-xs font-semibold text-slate-300 bg-slate-800 hover:bg-rose-900/60 hover:text-rose-200 transition-colors">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </header>
        @endif

        <!-- FLASH NOTIFIKASI -->
        <div class="px-4 sm:px-6 pt-4 space-y-2">
            @if(session('success'))
                <div class="p-3.5 bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-300 dark:border-emerald-700/60 text-emerald-900 dark:text-emerald-200 rounded-xl text-xs font-bold flex items-center gap-2.5 shadow-xs">
                    <span class="w-5 h-5 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">&check;</span>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('info'))
                <div class="p-3 bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800/50 text-blue-800 dark:text-blue-300 rounded-xl text-xs font-medium flex items-center gap-2">
                    <span class="text-blue-600 dark:text-blue-400 font-bold">&bull;</span>
                    <span>{{ session('info') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="p-3.5 bg-rose-50 dark:bg-rose-950/50 border border-rose-300 dark:border-rose-700/60 text-rose-900 dark:text-rose-200 rounded-xl text-xs font-semibold flex items-center gap-2.5 shadow-xs">
                    <span class="w-5 h-5 rounded-full bg-rose-600 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">&cross;</span>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="p-4 bg-rose-50 dark:bg-rose-950/50 border border-rose-300 dark:border-rose-700/60 text-rose-900 dark:text-rose-200 rounded-xl text-xs shadow-xs">
                    <div class="flex items-center gap-2 font-bold mb-1.5 text-rose-900 dark:text-rose-200">
                        <span class="w-5 h-5 rounded-full bg-rose-600 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">&cross;</span>
                        <span>Terjadi kesalahan pengisian form:</span>
                    </div>
                    <ul class="list-disc list-inside space-y-0.5 pl-7 text-rose-700 dark:text-rose-300">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <!-- CONTENT -->
        <main class="flex-1 px-4 sm:px-6 py-4">
            @yield('content')
        </main>

        @if(auth()->user()->role !== 'teknisi')
        <!-- FOOTER: Identitas DEVTRACK Seragam dengan Halaman Login -->
        <footer class="px-4 sm:px-6 py-3 border-t border-slate-200 dark:border-slate-800 bg-white/70 dark:bg-slate-900/80 text-center text-xs text-slate-500 dark:text-slate-400 mt-auto flex items-center justify-center gap-2 transition-colors">
            <img src="{{ asset('images/devtrack-logo.png') }}" alt="DEVTRACK Logo" class="h-4 w-auto object-contain opacity-75 dark:hidden">
            <img src="{{ asset('images/devtrack-logo-white.png') }}" alt="DEVTRACK Logo" class="h-4 w-auto object-contain opacity-75 hidden dark:inline">
            <span>&copy; {{ date('Y') }} DEVTRACK &bull; Device Tracking System</span>
        </footer>
        @endif

    </div>

    <script>
        // Toggle Dark / Light Mode
        function toggleDarkMode() {
            const isDark = document.documentElement.classList.contains('dark');
            if (isDark) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
        }

        // Fungsi segarkan halaman dengan durasi loading minimal 2 detik
        function triggerPageRefresh(btn) {
            const icon = document.getElementById('topbarRefreshIcon');
            const text = document.getElementById('topbarRefreshText');
            
            btn.disabled = true;
            btn.classList.add('cursor-wait');
            if (icon) icon.classList.add('animate-spin', 'text-blue-600');
            if (text) text.textContent = 'Menyegarkan...';
            
            setTimeout(function () {
                window.location.reload();
            }, 2000);
        }

        @if(auth()->check() && in_array(auth()->user()->role, ['admin', 'koordinator']))
        function adminNotificationApp() {
            return {
                open: false,
                unreadCount: {{ $adminUnreadNotificationCount ?? 0 }},
                items: [
                    @if(isset($adminNotifications))
                        @foreach($adminNotifications as $notif)
                        {
                            id: {{ $notif->id }},
                            title: {!! json_encode($notif->title) !!},
                            message: {!! json_encode($notif->message) !!},
                            device_name: {!! json_encode($notif->device_name) !!},
                            registration_number: {!! json_encode($notif->registration_number) !!},
                            hub: {!! json_encode($notif->hub) !!},
                            hub_label: {!! json_encode($notif->hub_label) !!},
                            hub_badge_class: {!! json_encode($notif->hub_badge_class) !!},
                            source: {!! json_encode($notif->source) !!},
                            is_read: {{ $notif->is_read ? 'true' : 'false' }},
                            time_ago: {!! json_encode($notif->created_at ? $notif->created_at->diffForHumans() : '') !!},
                            go_url: {!! json_encode(route('admin.notifications.go', $notif)) !!}
                        },
                        @endforeach
                    @endif
                ],
                pollInterval: null,
                init() {
                    // Polling berkala setiap 30 detik untuk memeriksa penambahan perangkat oleh teknisi
                    this.pollInterval = setInterval(() => {
                        this.fetchLatest();
                    }, 30000);
                },
                toggleMenu() {
                    this.open = !this.open;
                    if (this.open) {
                        this.fetchLatest();
                    }
                },
                fetchLatest() {
                    fetch("{{ route('admin.notifications.poll') }}", {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data && typeof data.unread_count !== 'undefined') {
                            this.unreadCount = data.unread_count;
                            this.items = data.notifications || [];
                        }
                    })
                    .catch(() => {});
                },
                markAllAsRead() {
                    fetch("{{ route('admin.notifications.mark-all-read') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => res.json())
                    .then(() => {
                        this.unreadCount = 0;
                        this.items.forEach(item => item.is_read = true);
                    })
                    .catch(() => {});
                }
            };
        }
        @endif
    </script>

    @stack('scripts')
</body>
</html>
