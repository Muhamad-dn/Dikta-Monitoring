@extends('layouts.app')

@section('title', 'Arsip Stock Opname')
@section('page_title', 'Stock Opname Bulanan')
@section('page_subtitle', 'Audit fisik stok gudang berkala akhir bulan dan arsip laporan Excel PT DIKTA')

@section('content')

<!-- Alpine App Definition (Didefinisikan sebelum DOM Render) -->
<script>
function stockOpnameIndexApp(config) {
    return {
        openModal: false,
        openCompleteModal: false,
        submitting: false,
        hasActive: config.hasActive,
        opnameId: config.opnameId,
        scanUrl: config.scanUrl,
        csrfToken: config.csrfToken,
        stats: config.initialStats,
        scanInput: '',
        isLoading: false,
        cameraActive: false,
        html5QrCode: null,
        scanMessage: '',
        scanMessageType: '',

        percentMatch() {
            if (!this.stats.total_system || this.stats.total_system === 0) return 0;
            return ((this.stats.total_matched / this.stats.total_system) * 100).toFixed(1);
        },

        playBeep(success = true) {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);

                if (success) {
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(880, ctx.currentTime);
                    gain.gain.setValueAtTime(0.15, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.15);
                    osc.start();
                    osc.stop(ctx.currentTime + 0.15);
                } else {
                    osc.type = 'sawtooth';
                    osc.frequency.setValueAtTime(220, ctx.currentTime);
                    gain.gain.setValueAtTime(0.2, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.3);
                    osc.start();
                    osc.stop(ctx.currentTime + 0.3);
                }
            } catch (e) {}
        },

        async submitScan() {
            const code = this.scanInput.trim();
            if (!code || this.isLoading || !this.scanUrl) return;

            this.isLoading = true;
            this.scanMessage = '';

            try {
                const res = await fetch(this.scanUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ code: code })
                });

                const data = await res.json();

                if (res.ok && data.success) {
                    if (data.type === 'matched') {
                        this.playBeep(true);
                        this.scanMessageType = 'matched';
                        this.scanMessage = data.message;
                    } else if (data.type === 'anomaly') {
                        this.playBeep(false);
                        this.scanMessageType = 'anomaly';
                        this.scanMessage = data.message;
                    }

                    if (data.stats) {
                        this.stats = data.stats;
                    }

                    if (data.item && data.item.id) {
                        const row = document.getElementById('row-item-' + data.item.id);
                        if (row) {
                            row.classList.add('bg-emerald-500/20');
                            setTimeout(() => row.classList.remove('bg-emerald-500/20'), 3000);
                        }
                    }

                    this.scanInput = '';
                    if (this.$refs.scanInputRef) {
                        this.$refs.scanInputRef.focus();
                    }
                } else {
                    this.playBeep(false);
                    this.scanMessageType = data.not_found ? 'not_found' : 'error';
                    this.scanMessage = data.message || 'Gagal memproses barcode.';
                    this.scanInput = '';
                    if (this.$refs.scanInputRef) {
                        this.$refs.scanInputRef.focus();
                    }
                }
            } catch (err) {
                this.playBeep(false);
                this.scanMessageType = 'error';
                this.scanMessage = 'Terjadi kesalahan jaringan atau server.';
            } finally {
                this.isLoading = false;
            }
        },

        quickCheck(regNumber) {
            this.scanInput = regNumber;
            this.submitScan();
        },

        toggleCamera() {
            if (this.cameraActive) {
                this.stopCamera();
            } else {
                this.startCamera();
            }
        },

        startCamera() {
            this.cameraActive = true;
            this.$nextTick(() => {
                if (typeof Html5Qrcode !== 'undefined') {
                    if (this.html5QrCode) {
                        try { this.html5QrCode.stop(); } catch(e) {}
                    }
                    this.html5QrCode = new Html5Qrcode("stock-opname-scanner");
                    this.html5QrCode.start(
                        { facingMode: "environment" },
                        { fps: 10, qrbox: { width: 240, height: 150 } },
                        (decodedText) => {
                            this.scanInput = decodedText;
                            this.submitScan();
                        },
                        () => {}
                    ).catch(() => {
                        alert('Tidak dapat mengakses kamera. Pastikan izin kamera aktif.');
                        this.cameraActive = false;
                    });
                }
            });
        },

        stopCamera() {
            if (this.html5QrCode) {
                this.html5QrCode.stop().then(() => {
                    this.html5QrCode.clear();
                }).catch(() => {});
            }
            this.cameraActive = false;
        }
    };
}
</script>

<div class="space-y-8" x-data="stockOpnameIndexApp({
    hasActive: {{ isset($activeOpname) && $activeOpname ? 'true' : 'false' }},
    opnameId: {{ isset($activeOpname) && $activeOpname ? $activeOpname->id : 'null' }},
    scanUrl: '{{ isset($activeOpname) && $activeOpname ? route('admin.stock-opname.scan', $activeOpname) : '' }}',
    csrfToken: '{{ csrf_token() }}',
    initialStats: {
        total_system: {{ isset($activeOpname) && $activeOpname ? $activeOpname->total_system : 0 }},
        total_matched: {{ isset($activeOpname) && $activeOpname ? $activeOpname->total_matched : 0 }},
        total_missing: {{ isset($activeOpname) && $activeOpname ? $activeOpname->total_missing : 0 }},
        total_surplus: {{ isset($activeOpname) && $activeOpname ? $activeOpname->total_surplus : 0 }},
        total_physical: {{ isset($activeOpname) && $activeOpname ? $activeOpname->total_physical : 0 }}
    }
})">

    <!-- Notifikasi Sukses / Error Halaman -->
    @if(session('success'))
        <div class="p-4 bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 dark:text-emerald-300 rounded-2xl text-xs font-bold flex items-center gap-3 shadow-xs">
            <span class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-xs shrink-0">&check;</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-rose-500/10 border border-rose-500/30 text-rose-700 dark:text-rose-300 rounded-2xl text-xs font-bold flex items-center gap-3 shadow-xs">
            <span class="w-6 h-6 rounded-full bg-rose-600 text-white flex items-center justify-center font-bold text-xs shrink-0">&cross;</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if(session('info'))
        <div class="p-4 bg-blue-500/10 border border-blue-500/30 text-blue-700 dark:text-blue-300 rounded-2xl text-xs font-bold flex items-center gap-3 shadow-xs">
            <span class="w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-xs shrink-0">i</span>
            <span>{{ session('info') }}</span>
        </div>
    @endif

    <!-- Top Action & Overview -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                Arsip Stock Opname
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Lakukan cek fisik barcode di akhir bulan dan cetak arsip laporan hasil stock opname langsung ke format Excel.
            </p>
        </div>

        <!-- HANYA SATU TOMBOL UTAMA -->
        <div>
            @if(isset($activeOpname) && $activeOpname)
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500/10 border border-amber-500/30 rounded-xl text-xs font-bold text-amber-600 dark:text-amber-400">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                    <span>Sesi Sedang Aktif: Stock Opname Bulan {{ $activeOpname->month_name }} {{ $activeOpname->period_year }}</span>
                </div>
            @else
                <button type="button" @click="openModal = true" 
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-500/20 transition-all hover:scale-[1.02] active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Mulai Stock Opname</span>
                </button>
            @endif
        </div>
    </div>

    <!-- JIKA ADA SESI AKTIF: TAMPILKAN TABEL BARU UNTUK SCAN BARCODE -->
    @if(isset($activeOpname) && $activeOpname)
        <div class="bg-white dark:bg-slate-900 rounded-3xl border-2 border-blue-500/30 shadow-lg p-5 sm:p-6 space-y-6">
            
            <!-- Header Sesi Scan Aktif -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-blue-600 text-white flex items-center justify-center shadow-md shadow-blue-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">
                                Tabel Scan Barcode - Stock Opname Bulan {{ $activeOpname->month_name }} {{ $activeOpname->period_year }}
                            </h3>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/30 animate-pulse">
                                Sedang Berjalan
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            Pemeriksa: <strong class="text-slate-800 dark:text-slate-200">{{ $activeOpname->admin_name }}</strong> 
                            • Lokasi: <strong>{{ $activeOpname->location_label }}</strong> 
                            • Kode Sesi: <strong class="font-mono text-blue-600 dark:text-blue-400">{{ $activeOpname->opname_code }}</strong>
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" @click="openCompleteModal = true"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-500/20 transition-all hover:scale-[1.02]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Selesaikan Stock Opname</span>
                    </button>
                </div>
            </div>

            <!-- Card Metrik Live -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-center sm:text-left">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Target Sistem</span>
                    <div class="text-2xl font-black text-slate-900 dark:text-white mt-1" x-text="stats.total_system">{{ $activeOpname->total_system }}</div>
                    <span class="text-[10px] text-slate-400">Unit di gudang</span>
                </div>

                <div class="p-4 rounded-2xl bg-emerald-50/40 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-800 text-center sm:text-left">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Fisik Ada (Match)</span>
                    <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1" x-text="stats.total_matched">{{ $activeOpname->total_matched }}</div>
                    <span class="text-[10px] text-emerald-600/70" x-text="percentMatch() + '% terverifikasi'">{{ $activeOpname->total_system > 0 ? round(($activeOpname->total_matched / $activeOpname->total_system) * 100, 1) : 0 }}%</span>
                </div>

                <div class="p-4 rounded-2xl bg-rose-50/40 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-800 text-center sm:text-left">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-rose-700 dark:text-rose-400">Belum Di-Scan</span>
                    <div class="text-2xl font-black text-rose-600 dark:text-rose-400 mt-1" x-text="stats.total_missing">{{ $activeOpname->total_missing }}</div>
                    <span class="text-[10px] text-rose-600/70">Fisik belum ditemukan</span>
                </div>

                <div class="p-4 rounded-2xl bg-amber-50/40 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800 text-center sm:text-left">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">Anomali Posisi</span>
                    <div class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1" x-text="stats.total_surplus">{{ $activeOpname->total_surplus }}</div>
                    <span class="text-[10px] text-amber-600/70">Beda status/lokasi</span>
                </div>
            </div>

            <!-- AREA INPUT SCAN & KAMERA BARCODE -->
            <div class="bg-gradient-to-r from-blue-900 to-slate-900 text-white rounded-2xl p-5 shadow-lg border border-blue-800/60 space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></span>
                        <span class="text-xs font-bold text-blue-200">Siap Scan Barcode Fisik:</span>
                    </div>

                    <button type="button" @click="toggleCamera()" 
                            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-bold transition-all"
                            :class="cameraActive ? 'bg-rose-600 hover:bg-rose-700 text-white' : 'bg-blue-600 hover:bg-blue-500 text-white'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span x-text="cameraActive ? 'Tutup Kamera' : 'Buka Kamera Barcode'">Buka Kamera Barcode</span>
                    </button>
                </div>

                <!-- Box Kamera Jika Aktif -->
                <div x-show="cameraActive" x-cloak class="p-3 bg-slate-950 rounded-xl border border-blue-500/40 space-y-2">
                    <div class="flex items-center justify-between text-xs text-blue-300">
                        <span>Arahkan kamera ke stiker barcode fisik perangkat:</span>
                        <button type="button" @click="stopCamera()" class="text-rose-400 hover:underline font-bold">Tutup Kamera</button>
                    </div>
                    <div id="stock-opname-scanner" class="overflow-hidden rounded-xl max-w-sm mx-auto aspect-video bg-black flex items-center justify-center"></div>
                </div>

                <!-- Kolom Input Scanner (Pistol Barcode / Ketik Manual) -->
                <div class="flex items-stretch gap-2">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input type="text" x-ref="scanInputRef" x-model="scanInput" 
                               @keydown.enter.prevent="submitScan()"
                               placeholder="Tembak pistol barcode atau ketik No. Registrasi / SN di sini lalu tekan Enter..."
                               class="w-full pl-10 pr-4 py-2.5 bg-slate-950/80 border border-slate-700 rounded-xl text-white placeholder-slate-400 text-xs focus:outline-none focus:ring-2 focus:ring-blue-400 font-mono shadow-inner"
                               autofocus>
                    </div>

                    <button type="button" @click="submitScan()" :disabled="isLoading"
                            class="px-5 py-2.5 bg-blue-600 hover:bg-blue-500 disabled:opacity-50 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-500/20 transition-all flex items-center gap-1.5">
                        <span x-show="!isLoading">Verifikasi</span>
                        <span x-show="isLoading" class="inline-flex items-center gap-1">
                            <svg class="animate-spin w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            Memeriksa...
                        </span>
                    </button>
                </div>

                <!-- Notifikasi Hasil Scan Live -->
                <template x-if="scanMessage">
                    <div class="p-3 rounded-xl text-xs font-semibold flex items-center justify-between gap-3 animate-fadeIn"
                         :class="{
                             'bg-emerald-500/20 border border-emerald-400/40 text-emerald-200': scanMessageType === 'matched',
                             'bg-amber-500/20 border border-amber-400/40 text-amber-200': scanMessageType === 'anomaly',
                             'bg-rose-500/20 border border-rose-400/40 text-rose-200': scanMessageType === 'error' || scanMessageType === 'not_found'
                         }">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full"
                                  :class="{
                                      'bg-emerald-400 animate-ping': scanMessageType === 'matched',
                                      'bg-amber-400': scanMessageType === 'anomaly',
                                      'bg-rose-400': scanMessageType === 'error' || scanMessageType === 'not_found'
                                  }"></span>
                            <span x-text="scanMessage"></span>
                        </div>
                        <button type="button" @click="scanMessage = ''" class="text-white/60 hover:text-white">&times;</button>
                    </div>
                </template>
            </div>

            <!-- TABEL SCAN BARCODE DETAIL -->
            <div class="border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden">
                <div class="p-3.5 bg-slate-50/70 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <!-- Filter Tabs -->
                    <div class="flex flex-wrap items-center gap-1 text-xs font-semibold">
                        <a href="{{ route('admin.stock-opname.index', ['tab' => 'all', 'search' => request('search')]) }}"
                           class="px-2.5 py-1 rounded-lg transition-all {{ $tab === 'all' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-xs font-bold' : 'text-slate-500 hover:text-slate-900' }}">
                            Semua (<span x-text="stats.total_system + stats.total_surplus">{{ $activeCounts['all'] }}</span>)
                        </a>

                        <a href="{{ route('admin.stock-opname.index', ['tab' => 'matched', 'search' => request('search')]) }}"
                           class="px-2.5 py-1 rounded-lg transition-all {{ $tab === 'matched' ? 'bg-emerald-600 text-white shadow-xs font-bold' : 'text-emerald-700 dark:text-emerald-400 hover:bg-emerald-50' }}">
                            🟢 Fisik Ada (<span x-text="stats.total_matched">{{ $activeCounts['matched'] }}</span>)
                        </a>

                        <a href="{{ route('admin.stock-opname.index', ['tab' => 'missing', 'search' => request('search')]) }}"
                           class="px-2.5 py-1 rounded-lg transition-all {{ $tab === 'missing' ? 'bg-rose-600 text-white shadow-xs font-bold' : 'text-rose-700 dark:text-rose-400 hover:bg-rose-50' }}">
                            🔴 Belum Di-Scan (<span x-text="stats.total_missing">{{ $activeCounts['missing'] }}</span>)
                        </a>

                        <a href="{{ route('admin.stock-opname.index', ['tab' => 'anomaly', 'search' => request('search')]) }}"
                           class="px-2.5 py-1 rounded-lg transition-all {{ $tab === 'anomaly' ? 'bg-amber-600 text-white shadow-xs font-bold' : 'text-amber-700 dark:text-amber-400 hover:bg-amber-50' }}">
                            🟡 Anomali (<span x-text="stats.total_surplus">{{ $activeCounts['anomaly'] }}</span>)
                        </a>
                    </div>

                    <!-- Search dalam sesi opname -->
                    <form method="GET" action="{{ route('admin.stock-opname.index') }}" class="flex items-center gap-2">
                        <input type="hidden" name="tab" value="{{ $tab }}">
                        <div class="relative">
                            <input type="text" name="search" value="{{ $search }}" placeholder="Cari No. Registrasi / Nama..."
                                   class="text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 pl-8 pr-3 py-1.5 w-52 focus:ring-2 focus:ring-blue-500">
                            <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        @if($search)
                            <a href="{{ route('admin.stock-opname.index', ['tab' => $tab]) }}" class="text-xs text-slate-400 hover:text-slate-600">Reset</a>
                        @endif
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="bg-slate-50/70 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider border-b border-slate-100 dark:border-slate-800">
                                <th class="py-2.5 px-3 w-10 text-center">No</th>
                                <th class="py-2.5 px-3">Nomor Registrasi</th>
                                <th class="py-2.5 px-3">Nama Perangkat</th>
                                <th class="py-2.5 px-3">Area</th>
                                <th class="py-2.5 px-3">Status Sistem</th>
                                <th class="py-2.5 px-3 text-center">Hasil Cek Fisik</th>
                                <th class="py-2.5 px-3">Waktu Scan</th>
                                <th class="py-2.5 px-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @if($activeItems)
                                @forelse($activeItems as $index => $item)
                                    @php $badge = $item->status_badge; @endphp
                                    <tr id="row-item-{{ $item->id }}" class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors {{ $item->physical_status === 'matched' ? 'bg-emerald-50/10' : '' }}">
                                        <td class="py-2.5 px-3 text-center font-medium text-slate-400">
                                            {{ $activeItems->firstItem() + $index }}
                                        </td>
                                        <td class="py-2.5 px-3">
                                            <span class="font-mono font-bold text-slate-900 dark:text-white">{{ $item->registration_number }}</span>
                                            @if($item->device && $item->device->serial_number)
                                                <span class="block text-[10px] font-mono text-slate-400">SN: {{ $item->device->serial_number }}</span>
                                            @endif
                                        </td>
                                        <td class="py-2.5 px-3">
                                            <div class="font-semibold text-slate-800 dark:text-slate-200">{{ $item->device_name }}</div>
                                        </td>
                                        <td class="py-2.5 px-3">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                                {{ $item->area ?? '-' }}
                                            </span>
                                        </td>
                                        <td class="py-2.5 px-3">
                                            <span class="text-slate-600 dark:text-slate-300">{{ str_replace('_', ' ', ucfirst($item->system_status ?? 'gudang')) }}</span>
                                        </td>
                                        <td class="py-2.5 px-3 text-center">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $badge['bg'] }}">
                                                <span class="w-1.5 h-1.5 rounded-full {{ $badge['dot'] }}"></span>
                                                {{ $badge['label'] }}
                                            </span>
                                        </td>
                                        <td class="py-2.5 px-3 text-slate-500 whitespace-nowrap">
                                            @if($item->scanned_at)
                                                <span class="font-medium text-slate-700 dark:text-slate-200">{{ $item->scanned_at->format('H:i:s') }}</span>
                                            @else
                                                <span class="text-slate-400">-</span>
                                            @endif
                                        </td>
                                        <td class="py-2.5 px-3 text-right whitespace-nowrap">
                                            @if($item->physical_status === 'missing')
                                                <button type="button" @click="quickCheck('{{ $item->registration_number }}')"
                                                        class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-blue-600 hover:text-white text-slate-700 dark:text-slate-300 text-[11px] font-bold transition-all">
                                                    Centang Manual
                                                </button>
                                            @else
                                                <span class="text-emerald-500 font-bold text-xs">&#10003;</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="py-6 text-center text-slate-400">
                                            Tidak ada perangkat yang cocok dengan filter ini.
                                        </td>
                                    </tr>
                                @endforelse
                            @endif
                        </tbody>
                    </table>
                </div>

                @if($activeItems && $activeItems->hasPages())
                    <div class="p-3 border-t border-slate-100 dark:border-slate-800">
                        {{ $activeItems->links() }}
                    </div>
                @endif
            </div>

        </div>
    @endif

    <!-- TABEL ARSIP STOCK OPNAME & FITUR CETAK EXCEL (DI BAGIAN BAWAH) -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="p-4 sm:px-6 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Arsip Stock Opname Bulanan</span>
                </h3>
                <p class="text-[11px] text-slate-500 mt-0.5">Seluruh arsip hasil pengecekan fisik perangkat per bulan dengan tombol cetak Excel di sampingnya</p>
            </div>

            <form method="GET" action="{{ route('admin.stock-opname.index') }}" class="flex items-center gap-2">
                <select name="status" onchange="this.form.submit()" 
                        class="text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 px-3 py-1.5 focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Status</option>
                    <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Selesai</option>
                    <option value="in_progress" {{ $status === 'in_progress' ? 'selected' : '' }}>Sedang Berjalan</option>
                </select>

                <select name="year" onchange="this.form.submit()" 
                        class="text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 px-3 py-1.5 focus:ring-2 focus:ring-blue-500">
                    @for($y = date('Y') + 1; $y >= 2024; $y--)
                        <option value="{{ $y }}" {{ (int)$year === $y ? 'selected' : '' }}>Tahun {{ $y }}</option>
                    @endfor
                </select>
            </form>
        </div>

        @if($opnames->isEmpty())
            <div class="p-12 text-center">
                <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200">Belum Ada Arsip Stock Opname</h4>
                <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                    Klik tombol <strong>"Mulai Stock Opname"</strong> di atas untuk mengisi bulan dan nama admin pemeriksa, lalu mulai memindai barcode fisik perangkat.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50/70 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider border-b border-slate-100 dark:border-slate-800">
                            <th class="py-3 px-4">Arsip Stock Opname</th>
                            <th class="py-3 px-4">Tanggal Pelaksanaan</th>
                            <th class="py-3 px-4 text-center">Target Sistem</th>
                            <th class="py-3 px-4 text-center">Fisik Sesuai (Match)</th>
                            <th class="py-3 px-4 text-center">Selisih Kurang</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Fitur Cetak Excel & Laporan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($opnames as $opname)
                            @php
                                $percent = $opname->total_system > 0 
                                    ? round(($opname->total_matched / $opname->total_system) * 100, 1) 
                                    : 0;
                            @endphp
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900 dark:text-white text-sm flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <span>Stock Opname Bulan {{ $opname->month_name }} {{ $opname->period_year }}</span>
                                    </div>
                                    <div class="text-[11px] text-slate-500 mt-1">
                                        Pemeriksa: <strong class="text-slate-700 dark:text-slate-300">{{ $opname->admin_name ?: ($opname->conductor ? $opname->conductor->name : 'Administrator') }}</strong>
                                        • <span class="font-mono text-slate-400">{{ $opname->opname_code }}</span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-medium text-slate-700 dark:text-slate-300">
                                        {{ $opname->completed_at ? $opname->completed_at->format('d/m/Y H:i') : ($opname->created_at ? $opname->created_at->format('d/m/Y H:i') : '-') }} WIB
                                    </div>
                                    <div class="text-[10px] text-slate-400">
                                        {{ $opname->completed_at ? 'Selesai audit' : 'Mulai dibuat' }}
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="font-bold text-slate-800 dark:text-slate-200 text-sm">{{ $opname->total_system }}</span>
                                    <span class="text-[10px] text-slate-400 block">Unit</span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="font-black text-emerald-600 dark:text-emerald-400 text-sm">{{ $opname->total_matched }}</span>
                                    <span class="text-[10px] text-emerald-600/70 block">{{ $percent }}%</span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($opname->total_missing > 0)
                                        <span class="font-bold text-rose-600 dark:text-rose-400 text-sm">{{ $opname->total_missing }}</span>
                                        <span class="text-[10px] text-rose-500/70 block">Belum Di-Scan</span>
                                    @else
                                        <span class="text-xs font-semibold text-emerald-500">0 (Lengkap)</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($opname->status === 'completed')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Selesai
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 animate-pulse">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            Sedang Berjalan
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-2">
                                        <!-- FITUR CETAK EXCEL (DI SAMPINGNYA LANGSUNG) -->
                                        <a href="{{ route('admin.stock-opname.export', $opname) }}" 
                                           class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl border border-emerald-600 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition-all shadow-sm hover:scale-[1.02]">
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                            <span>Cetak Excel</span>
                                        </a>

                                        <!-- Tombol Cetak Berita Acara -->
                                        <a href="{{ route('admin.stock-opname.print', $opname) }}" target="_blank"
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 font-bold text-xs transition-colors shadow-xs">
                                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                            <span>Cetak PDF</span>
                                        </a>

                                        <!-- Tombol Lihat / Lanjutkan Scan -->
                                        <a href="{{ route('admin.stock-opname.show', $opname) }}" 
                                           class="inline-flex items-center gap-1 px-3 py-1.5 {{ $opname->status === 'completed' ? 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300' : 'bg-blue-600 hover:bg-blue-700 text-white' }} rounded-xl text-xs font-bold transition-all shadow-xs">
                                            @if($opname->status === 'completed')
                                                <span>Detail</span>
                                            @else
                                                <span>Scan &rarr;</span>
                                            @endif
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($opnames->hasPages())
                <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                    {{ $opnames->links() }}
                </div>
            @endif
        @endif
    </div>

    <!-- POP-UP FORMULIR MULAI STOCK OPNAME -->
    <div x-show="openModal" x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
         @keydown.escape.window="openModal = false">
        
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl max-w-md w-full p-6 space-y-5"
             @click.away="openModal = false">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-2xl bg-blue-500/10 text-blue-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Formulir Mulai Stock Opname</h3>
                        <p class="text-[11px] text-slate-400">Pemeriksaan fisik stok akhir bulan</p>
                    </div>
                </div>
                <button type="button" @click="openModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.stock-opname.store') }}" @submit="submitting = true" class="space-y-4">
                @csrf
                <input type="hidden" name="location" value="hb_jkt">

                <!-- 1. Periode Bulan & Tahun -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Bulan <span class="text-rose-500">*</span>
                        </label>
                        <select name="period_month" required class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 px-3 py-2.5 focus:ring-2 focus:ring-blue-500">
                            @foreach(\App\Models\StockOpname::MONTHS as $mNum => $mLabel)
                                <option value="{{ $mNum }}" {{ (int)date('n') === $mNum ? 'selected' : '' }}>{{ $mLabel }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Tahun <span class="text-rose-500">*</span>
                        </label>
                        <select name="period_year" required class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 px-3 py-2.5 focus:ring-2 focus:ring-blue-500">
                            @for($y = date('Y') + 1; $y >= 2024; $y--)
                                <option value="{{ $y }}" {{ (int)date('Y') === $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                </div>

                <!-- 2. Nama Admin Pemeriksa -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Nama Admin / Pemeriksa <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="admin_name" value="{{ auth()->user()->name ?? 'Administrator Sistem' }}" required
                           placeholder="Masukkan nama admin pemeriksa..."
                           class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 px-3 py-2.5 focus:ring-2 focus:ring-blue-500 font-medium">
                    <p class="text-[10px] text-slate-400 mt-1">Nama ini akan dicantumkan di arsip laporan audit dan file cetak Excel.</p>
                </div>

                <!-- Tombol Aksi Bawah Modal -->
                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="openModal = false" :disabled="submitting"
                            class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                        Batal
                    </button>
                    <button type="submit" id="btn-submit-opname" :disabled="submitting"
                            class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-500/20 transition-all flex items-center gap-1.5 disabled:opacity-50">
                        <span x-show="!submitting">Mulai Stock Opname</span>
                        <span x-show="submitting" x-cloak class="flex items-center gap-1.5">
                            <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            <span>Memproses...</span>
                        </span>
                        <svg x-show="!submitting" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL SELESAIKAN STOCK OPNAME -->
    @if(isset($activeOpname) && $activeOpname)
        <div x-show="openCompleteModal" x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
             @keydown.escape.window="openCompleteModal = false">
            
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl max-w-lg w-full p-6 space-y-5"
                 @click.away="openCompleteModal = false">
                
                <div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Selesaikan Stock Opname</h3>
                        <p class="text-[11px] text-slate-400">Hasil audit akan masuk ke Arsip Stock Opname di bawah</p>
                    </div>
                </div>

                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 space-y-2 text-xs">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Target Sistem:</span>
                        <strong class="text-slate-800 dark:text-slate-200" x-text="stats.total_system + ' Unit'"></strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-emerald-600">Fisik Sesuai (Match):</span>
                        <strong class="text-emerald-600 font-bold" x-text="stats.total_matched + ' Unit'"></strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-rose-600">Selisih Kurang (Missing):</span>
                        <strong class="text-rose-600 font-bold" x-text="stats.total_missing + ' Unit'"></strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-amber-600">Anomali / Temuan Posisi:</span>
                        <strong class="text-amber-600 font-bold" x-text="stats.total_surplus + ' Unit'"></strong>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.stock-opname.complete', $activeOpname) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Catatan Kesimpulan Audit / Berita Acara</label>
                        <textarea name="notes" rows="3" placeholder="Contoh: Seluruh unit di Gudang HB JKT telah selesai diverifikasi fisik..."
                                  class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 p-3 focus:ring-2 focus:ring-blue-500">{{ $activeOpname->notes }}</textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="openCompleteModal = false"
                                class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                            Batal
                        </button>
                        <button type="submit" 
                                class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-500/20 transition-all">
                            Selesaikan & Masukkan ke Arsip
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>
@endsection
