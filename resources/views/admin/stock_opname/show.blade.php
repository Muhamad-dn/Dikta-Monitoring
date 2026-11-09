@extends('layouts.app')

@section('title', $stockOpname->title)
@section('page_title', 'Pelaksanaan Stock Opname')
@section('page_subtitle', $stockOpname->title . ' • ' . $stockOpname->location_label)

@section('content')
<div class="space-y-6" x-data="stockOpnameApp({
    opnameId: {{ $stockOpname->id }},
    scanUrl: '{{ route('admin.stock-opname.scan', $stockOpname) }}',
    csrfToken: '{{ csrf_token() }}',
    isCompleted: {{ $stockOpname->status === 'completed' ? 'true' : 'false' }},
    initialStats: {
        total_system: {{ $stockOpname->total_system }},
        total_matched: {{ $stockOpname->total_matched }},
        total_missing: {{ $stockOpname->total_missing }},
        total_surplus: {{ $stockOpname->total_surplus }},
        total_physical: {{ $stockOpname->total_physical }}
    }
})">

    <!-- Top Navigation Bar & Action -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs">
        <div class="flex items-start sm:items-center gap-3">
            <a href="{{ route('admin.stock-opname.index') }}" 
               class="p-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-500 hover:text-slate-800 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ $stockOpname->title }}</h2>
                    @if($stockOpname->status === 'completed')
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                            Selesai & Terkunci
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 animate-pulse">
                            Sedang Berjalan (Aktif)
                        </span>
                    @endif
                </div>
                <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500 dark:text-slate-400 mt-1">
                    <span>Kode: <strong class="font-mono text-slate-700 dark:text-slate-200">{{ $stockOpname->opname_code }}</strong></span>
                    <span>•</span>
                    <span>Lokasi: <strong>{{ $stockOpname->location_label }}</strong></span>
                    <span>•</span>
                    <span>Pemeriksa: <strong>{{ $stockOpname->admin_name ?: ($stockOpname->conductor ? $stockOpname->conductor->name : 'Admin') }}</strong></span>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.stock-opname.print', $stockOpname) }}" target="_blank"
               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all shadow-xs">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Berita Acara</span>
            </a>

            <a href="{{ route('admin.stock-opname.export', $stockOpname) }}"
               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold border border-emerald-200 dark:border-emerald-800 bg-emerald-50/30 text-emerald-700 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-950/40 transition-all shadow-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Export Excel/CSV</span>
            </a>

            @if($stockOpname->status !== 'completed')
                <button type="button" @click="openCompleteModal = true"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-md shadow-emerald-600/20 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Selesaikan Stock Opname</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Metrik Statistik Real-Time -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <!-- Target Sistem -->
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Target di Sistem</span>
            <div class="text-2xl font-black text-slate-900 dark:text-white mt-1" x-text="stats.total_system">{{ $stockOpname->total_system }}</div>
            <p class="text-[11px] text-slate-400 mt-0.5">Seluruh unit terdaftar di lokasi ini</p>
        </div>

        <!-- Fisik Ditemukan (Match) -->
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-emerald-200 dark:border-emerald-900/50 bg-emerald-50/20 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Fisik Ada (Match)</span>
                <span class="text-[10px] font-extrabold px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-700 dark:text-emerald-300" 
                      x-text="percentMatch() + '%'">
                    {{ $stockOpname->total_system > 0 ? round(($stockOpname->total_matched / $stockOpname->total_system) * 100, 1) : 0 }}%
                </span>
            </div>
            <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1" x-text="stats.total_matched">{{ $stockOpname->total_matched }}</div>
            <p class="text-[11px] text-emerald-600/80 mt-0.5">Barcode cocok & terverifikasi</p>
        </div>

        <!-- Belum Di-Scan / Selisih Kurang -->
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-rose-200 dark:border-rose-900/50 bg-rose-50/20 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-rose-700 dark:text-rose-400">Belum Di-Scan (Selisih)</span>
            <div class="text-2xl font-black text-rose-600 dark:text-rose-400 mt-1" x-text="stats.total_missing">{{ $stockOpname->total_missing }}</div>
            <p class="text-[11px] text-rose-600/80 mt-0.5">Fisik belum ditemukan di rak</p>
        </div>

        <!-- Anomali / Temuan -->
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-amber-200 dark:border-amber-900/50 bg-amber-50/20 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">Anomali Posisi</span>
            <div class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1" x-text="stats.total_surplus">{{ $stockOpname->total_surplus }}</div>
            <p class="text-[11px] text-amber-700/80 mt-0.5">Fisik ada tapi tercatat beda posisi</p>
        </div>
    </div>

    <!-- AREA SCAN BARCODE INTERAKTIF (Hanya Tampil Jika Sesi Aktif) -->
    @if($stockOpname->status !== 'completed')
        <div class="bg-gradient-to-r from-blue-900/90 to-slate-900 text-white rounded-3xl p-5 sm:p-6 shadow-xl border border-blue-800/50 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-blue-500/20 border border-blue-400/30 flex items-center justify-center text-blue-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white">Scan Barcode Fisik Lapangan</h3>
                        <p class="text-xs text-blue-200">Gunakan pistol barcode scanner, ketik manual, atau nyalakan kamera smartphone/laptop.</p>
                    </div>
                </div>

                <button type="button" @click="toggleCamera()" 
                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition-all"
                        :class="cameraActive ? 'bg-rose-600 hover:bg-rose-700 text-white shadow-lg shadow-rose-600/30' : 'bg-blue-600 hover:bg-blue-500 text-white shadow-lg shadow-blue-600/30'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span x-text="cameraActive ? 'Tutup Kamera' : 'Buka Kamera Barcode'">Buka Kamera Barcode</span>
                </button>
            </div>

            <!-- Kamera Box jika aktif -->
            <div x-show="cameraActive" x-cloak class="p-4 bg-slate-950/80 rounded-2xl border border-blue-500/40 space-y-3">
                <div class="flex items-center justify-between text-xs text-blue-300">
                    <span>Arahkan kamera ke stiker barcode fisik perangkat di bodi / kardus:</span>
                    <button type="button" @click="stopCamera()" class="text-rose-400 hover:underline font-bold">Matikan Kamera</button>
                </div>
                <div id="stock-opname-scanner" class="overflow-hidden rounded-xl max-w-md mx-auto aspect-video bg-black flex items-center justify-center"></div>
            </div>

            <!-- Fast Input Box (Pistol Scanner / Laser / Ketik Manual) -->
            <div class="relative">
                <div class="flex items-stretch gap-2">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input type="text" x-ref="scanInputRef" x-model="scanInput" 
                               @keydown.enter.prevent="submitScan()"
                               placeholder="Scan barcode fisik atau ketik No. Registrasi / SN di sini lalu tekan Enter..."
                               class="w-full pl-10 pr-4 py-3 bg-slate-950/70 border border-slate-700/80 rounded-2xl text-white placeholder-slate-400 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent font-mono shadow-inner"
                               autofocus>
                    </div>

                    <button type="button" @click="submitScan()" :disabled="isLoading"
                            class="px-6 py-3 bg-blue-600 hover:bg-blue-500 disabled:opacity-50 text-white rounded-2xl text-xs font-bold shadow-lg shadow-blue-500/20 transition-all flex items-center gap-2">
                        <span x-show="!isLoading">Verifikasi</span>
                        <span x-show="isLoading" class="inline-flex items-center gap-1.5">
                            <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            Memeriksa...
                        </span>
                    </button>
                </div>
            </div>

            <!-- Pesan Notifikasi Scan Live (Banner) -->
            <template x-if="scanMessage">
                <div class="p-3.5 rounded-2xl text-xs font-semibold flex items-center justify-between gap-3 transition-all animate-fadeIn"
                     :class="{
                         'bg-emerald-500/20 border border-emerald-400/40 text-emerald-200': scanMessageType === 'matched',
                         'bg-amber-500/20 border border-amber-400/40 text-amber-200': scanMessageType === 'anomaly',
                         'bg-rose-500/20 border border-rose-400/40 text-rose-200': scanMessageType === 'error' || scanMessageType === 'not_found'
                     }">
                    <div class="flex items-center gap-2.5">
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
    @endif

    <!-- TABEL DATA & FILTER TAB -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        
        <!-- Tab Navigation & Local Search -->
        <div class="p-4 sm:px-6 border-b border-slate-100 dark:border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
            
            <!-- Filter Tabs -->
            <div class="flex flex-wrap items-center gap-1.5 p-1 rounded-2xl bg-slate-100 dark:bg-slate-800 text-xs font-semibold">
                <a href="{{ route('admin.stock-opname.show', [$stockOpname, 'tab' => 'all', 'search' => request('search')]) }}"
                   class="px-3 py-1.5 rounded-xl transition-all {{ $tab === 'all' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-xs font-bold' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white' }}">
                    Semua (<span x-text="stats.total_system + stats.total_surplus">{{ $counts['all'] }}</span>)
                </a>

                <a href="{{ route('admin.stock-opname.show', [$stockOpname, 'tab' => 'matched', 'search' => request('search')]) }}"
                   class="px-3 py-1.5 rounded-xl transition-all {{ $tab === 'matched' ? 'bg-emerald-600 text-white shadow-xs font-bold' : 'text-emerald-700 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-slate-700' }}">
                    🟢 Fisik Ada (<span x-text="stats.total_matched">{{ $counts['matched'] }}</span>)
                </a>

                <a href="{{ route('admin.stock-opname.show', [$stockOpname, 'tab' => 'missing', 'search' => request('search')]) }}"
                   class="px-3 py-1.5 rounded-xl transition-all {{ $tab === 'missing' ? 'bg-rose-600 text-white shadow-xs font-bold' : 'text-rose-700 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-slate-700' }}">
                    🔴 Belum Di-Scan (<span x-text="stats.total_missing">{{ $counts['missing'] }}</span>)
                </a>

                <a href="{{ route('admin.stock-opname.show', [$stockOpname, 'tab' => 'anomaly', 'search' => request('search')]) }}"
                   class="px-3 py-1.5 rounded-xl transition-all {{ $tab === 'anomaly' ? 'bg-amber-600 text-white shadow-xs font-bold' : 'text-amber-700 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-slate-700' }}">
                    🟡 Anomali Posisi (<span x-text="stats.total_surplus">{{ $counts['anomaly'] }}</span>)
                </a>
            </div>

            <!-- Search Field -->
            <form method="GET" action="{{ route('admin.stock-opname.show', $stockOpname) }}" class="flex items-center gap-2">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <div class="relative">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari No. Registrasi / Nama..."
                           class="text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 pl-8 pr-3 py-1.5 w-60 focus:ring-2 focus:ring-blue-500">
                    <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                @if($search)
                    <a href="{{ route('admin.stock-opname.show', [$stockOpname, 'tab' => $tab]) }}" class="text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">Reset</a>
                @endif
            </form>
        </div>

        <!-- Tabel Daftar Unit Opname -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/70 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider border-b border-slate-100 dark:border-slate-800">
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4">Nomor Registrasi</th>
                        <th class="py-3 px-4">Nama Perangkat</th>
                        <th class="py-3 px-4">Area</th>
                        <th class="py-3 px-4">Status di Sistem</th>
                        <th class="py-3 px-4 text-center">Hasil Cek Fisik</th>
                        <th class="py-3 px-4">Waktu Scan</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($items as $index => $item)
                        @php
                            $badge = $item->status_badge;
                        @endphp
                        <tr id="row-item-{{ $item->id }}" class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors {{ $item->physical_status === 'matched' ? 'bg-emerald-50/10' : '' }}">
                            <td class="py-3 px-4 text-center font-medium text-slate-400">
                                {{ $items->firstItem() + $index }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-mono font-bold text-slate-900 dark:text-white">{{ $item->registration_number }}</span>
                                @if($item->device && $item->device->serial_number)
                                    <span class="block text-[10px] font-mono text-slate-400">SN: {{ $item->device->serial_number }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-semibold text-slate-800 dark:text-slate-200">{{ $item->device_name }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                    {{ $item->area ?? '-' }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="text-slate-600 dark:text-slate-300">{{ str_replace('_', ' ', ucfirst($item->system_status ?? 'gudang')) }}</span>
                                @if($item->system_location)
                                    <span class="block text-[10px] text-slate-400">{{ $item->system_location }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold border {{ $badge['bg'] }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $badge['dot'] }}"></span>
                                    {{ $badge['label'] }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                @if($item->scanned_at)
                                    <span class="font-medium text-slate-700 dark:text-slate-200">{{ $item->scanned_at->format('H:i:s') }}</span>
                                    <span class="block text-[10px] text-slate-400">{{ $item->scanned_at->format('d/m/Y') }}</span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                @if($item->physical_status === 'wrong_location' && $stockOpname->status !== 'completed')
                                    <form method="POST" action="{{ route('admin.stock-opname.resolve', [$stockOpname, $item]) }}" class="inline">
                                        @csrf
                                        <button type="submit" title="Sinkronkan status perangkat ini ke Gudang"
                                                class="px-2.5 py-1 rounded-lg bg-amber-500 hover:bg-amber-600 text-white text-[11px] font-bold transition-all shadow-xs">
                                            Sinkron ke Gudang
                                        </button>
                                    </form>
                                @elseif($item->physical_status === 'missing' && $stockOpname->status !== 'completed')
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
                            <td colspan="8" class="py-8 text-center text-slate-400">
                                Tidak ada perangkat yang cocok dengan filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($items->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                {{ $items->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Konfirmasi Selesai Stock Opname -->
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
                    <p class="text-[11px] text-slate-400">Sesi akan dikunci sebagai Berita Acara resmi</p>
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

            <form method="POST" action="{{ route('admin.stock-opname.complete', $stockOpname) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Catatan Kesimpulan Audit / Berita Acara</label>
                    <textarea name="notes" rows="3" placeholder="Contoh: Seluruh unit di Gudang HB JKT telah selesai diverifikasi fisik. Selisih 2 unit sedang dalam investigasi..."
                              class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 p-3 focus:ring-2 focus:ring-blue-500">{{ $stockOpname->notes }}</textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="openCompleteModal = false"
                            class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-500/20 transition-all">
                        Kunci & Selesaikan Opname
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<!-- Alpine & HTML5-QRCode Scanner Controller Script -->
<script>
function stockOpnameApp(config) {
    return {
        opnameId: config.opnameId,
        scanUrl: config.scanUrl,
        csrfToken: config.csrfToken,
        isCompleted: config.isCompleted,
        stats: config.initialStats,
        scanInput: '',
        isLoading: false,
        cameraActive: false,
        html5QrCode: null,
        scanMessage: '',
        scanMessageType: '', // 'matched', 'anomaly', 'error', 'not_found'
        openCompleteModal: false,

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
                    osc.frequency.setValueAtTime(880, ctx.currentTime); // High A
                    gain.gain.setValueAtTime(0.15, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.15);
                    osc.start();
                    osc.stop(ctx.currentTime + 0.15);
                } else {
                    osc.type = 'sawtooth';
                    osc.frequency.setValueAtTime(220, ctx.currentTime); // Low A buzzer
                    gain.gain.setValueAtTime(0.2, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.3);
                    osc.start();
                    osc.stop(ctx.currentTime + 0.3);
                }
            } catch (e) {
                // AudioContext not permitted or supported, fail silently
            }
        },

        async submitScan() {
            const code = this.scanInput.trim();
            if (!code || this.isLoading) return;

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

                    // Update row jika ada di halaman saat ini
                    if (data.item && data.item.id) {
                        const row = document.getElementById('row-item-' + data.item.id);
                        if (row) {
                            row.classList.add('bg-emerald-500/20');
                            setTimeout(() => row.classList.remove('bg-emerald-500/20'), 3000);
                        }
                    }

                    // Kosongkan input dan refocus untuk scan berikutnya
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
                        {
                            fps: 10,
                            qrbox: { width: 260, height: 160 }
                        },
                        (decodedText) => {
                            // Berhasil baca barcode dari kamera
                            this.scanInput = decodedText;
                            this.submitScan();
                        },
                        (errorMessage) => {
                            // Frame scanning, ignore
                        }
                    ).catch(err => {
                        console.error('Kamera tidak dapat diakses:', err);
                        alert('Tidak dapat mengakses kamera. Pastikan izin kamera aktif.');
                        this.cameraActive = false;
                    });
                } else {
                    alert('Library scanner kamera tidak tersedia.');
                    this.cameraActive = false;
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
@endsection

