@extends('layouts.app')

@section('title', 'Kelola Stok Perangkat')
@section('page_title', 'Manajemen Stok Perangkat')
@section('page_subtitle', 'Pantau kuantitas stok di gudang PT, alokasi teknisi, dan status dismantle')

@section('content')
<div class="space-y-6" x-data="stockAllocationApp()">

    <!-- Overview Cards -->
    <!-- Overview Cards (8 Kolom Metrik) -->
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3">
        <a href="{{ route('admin.dashboard', ['status' => 'all']) }}" class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs hover:border-slate-400 hover:shadow-md transition-all block group">
            <span class="text-[10px] font-bold uppercase text-slate-500 group-hover:underline">Total Stok &rarr;</span>
            <div class="text-xl font-black text-slate-900 mt-1">{{ $summary['total'] }}</div>
        </a>
        <a href="{{ route('admin.dashboard', ['status' => 'tersedia_hb_jkt']) }}" class="bg-white p-4 rounded-xl border border-emerald-200 bg-emerald-50/30 shadow-xs hover:border-emerald-400 hover:shadow-md transition-all block group">
            <span class="text-[10px] font-bold uppercase text-emerald-700 group-hover:underline">HB JKT &rarr;</span>
            <div class="text-xl font-black text-emerald-600 mt-1">{{ $summary['tersedia_hb_jkt'] }}</div>
        </a>
        <a href="{{ route('admin.dashboard', ['status' => 'tersedia_hb_ckr']) }}" class="bg-white p-4 rounded-xl border border-teal-200 bg-teal-50/30 shadow-xs hover:border-teal-400 hover:shadow-md transition-all block group">
            <span class="text-[10px] font-bold uppercase text-teal-700 group-hover:underline">HB CKR &rarr;</span>
            <div class="text-xl font-black text-teal-600 mt-1">{{ $summary['tersedia_hb_ckr'] }}</div>
        </a>
        <a href="{{ route('admin.dashboard', ['status' => 'dibawa_teknisi']) }}" class="bg-white p-4 rounded-xl border border-amber-300 bg-amber-50/30 shadow-xs hover:border-amber-400 hover:shadow-md transition-all block group">
            <span class="text-[10px] font-bold uppercase text-amber-800 group-hover:underline">Dibawa Teknisi &rarr;</span>
            <div class="text-xl font-black text-amber-600 mt-1">{{ $summary['dibawa_teknisi'] }}</div>
        </a>
        <a href="{{ route('admin.dashboard', ['status' => 'terinstal']) }}" class="bg-white p-4 rounded-xl border border-blue-200 bg-blue-50/30 shadow-xs hover:border-blue-400 hover:shadow-md transition-all block group">
            <span class="text-[10px] font-bold uppercase text-blue-700 group-hover:underline">Terinstal &rarr;</span>
            <div class="text-xl font-black text-blue-600 mt-1">{{ $summary['terpasang_pelanggan'] }}</div>
        </a>
        <a href="{{ route('admin.dashboard', ['status' => 'pending']) }}" class="bg-white p-4 rounded-xl border border-orange-300 bg-orange-50/30 shadow-xs hover:border-orange-400 hover:shadow-md transition-all block group">
            <span class="text-[10px] font-bold uppercase text-orange-800 group-hover:underline">Pending &rarr;</span>
            <div class="text-xl font-black text-orange-600 mt-1">{{ $summary['pending'] }}</div>
        </a>
        <a href="{{ route('admin.dashboard', ['status' => 'rusak']) }}" class="bg-white p-4 rounded-xl border border-rose-200 bg-rose-50/30 shadow-xs hover:border-rose-400 hover:shadow-md transition-all block group">
            <span class="text-[10px] font-bold uppercase text-rose-700 group-hover:underline">Rusak &rarr;</span>
            <div class="text-xl font-black text-rose-600 mt-1">{{ $summary['rusak'] }}</div>
        </a>
        <a href="{{ route('admin.dashboard', ['status' => 'hilang']) }}" class="bg-white p-4 rounded-xl border border-purple-200 bg-purple-50/30 shadow-xs hover:border-purple-400 hover:shadow-md transition-all block group">
            <span class="text-[10px] font-bold uppercase text-purple-800 group-hover:underline">Hilang &rarr;</span>
            <div class="text-xl font-black text-purple-700 mt-1">{{ $summary['hilang'] }}</div>
        </a>
    </div>

    <!-- Detail Unit Tersedia di Gudang HB (JKT & CKR) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <h3 class="text-sm font-bold text-slate-900">Daftar Unit Tersedia di Gudang HB</h3>
                <div class="inline-flex rounded-lg border border-slate-200 p-0.5 bg-slate-50 text-xs font-semibold">
                    <a href="{{ route('admin.devices.stock', array_filter(['search' => request('search')])) }}" 
                       class="px-2.5 py-1 rounded-md transition-all {{ empty($hub) ? 'bg-white text-slate-900 shadow-xs font-bold' : 'text-slate-500 hover:text-slate-900' }}">
                        Semua ({{ $summary['tersedia_hb_jkt'] + $summary['tersedia_hb_ckr'] }})
                    </a>
                    <a href="{{ route('admin.devices.stock', array_filter(['hub' => 'hb_jkt', 'search' => request('search')])) }}" 
                       class="px-2.5 py-1 rounded-md transition-all {{ $hub === 'hb_jkt' ? 'bg-emerald-600 text-white shadow-xs font-bold' : 'text-emerald-700 hover:text-emerald-900' }}">
                        HB JKT ({{ $summary['tersedia_hb_jkt'] }})
                    </a>
                    <a href="{{ route('admin.devices.stock', array_filter(['hub' => 'hb_ckr', 'search' => request('search')])) }}" 
                       class="px-2.5 py-1 rounded-md transition-all {{ $hub === 'hb_ckr' ? 'bg-teal-600 text-white shadow-xs font-bold' : 'text-teal-700 hover:text-teal-900' }}">
                        HB CKR ({{ $summary['tersedia_hb_ckr'] }})
                    </a>
                </div>
            </div>
            <div class="flex items-center gap-2.5 flex-wrap">
                <button type="button" onclick="openImportExcelModal()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-emerald-800 bg-emerald-50 hover:bg-emerald-100 border border-emerald-300 rounded-lg shadow-xs transition-colors cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
                    <span>Import Excel / CSV</span>
                </button>
                <a href="{{ route('admin.devices.create') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-brand-600 hover:bg-brand-700 rounded-lg shadow-xs transition-colors">
                    + Tambah Unit ke Stok
                </a>
            </div>
        </div>

        <!-- Search Bar No. Registrasi / Pelanggan / Device -->
        <div class="p-4 bg-slate-50/70 border-b border-slate-100">
            <form method="GET" action="{{ route('admin.devices.stock') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" 
                           name="search" 
                           x-model="searchQuery" 
                           placeholder="Cari dengan No.registrasi / Nojar / Kode Spek / Nama" 
                           class="w-full pl-9 pr-24 py-2 text-xs bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 shadow-xs placeholder-slate-400 font-medium">
                    <div class="absolute inset-y-0 right-0 pr-1.5 flex items-center gap-1">
                        <button type="button" 
                                x-show="searchQuery" 
                                @click="searchQuery = ''; if('{{ request('search') }}') window.location.href='{{ route('admin.devices.stock') }}';" 
                                class="px-2 py-0.5 text-[10px] font-semibold text-slate-500 hover:text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-md transition-colors"
                                title="Bersihkan input pencarian" x-cloak>
                            ✕ Hapus
                        </button>
                        <button type="submit" 
                                class="px-2.5 py-1 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-xs transition-colors">
                            Cari
                        </button>
                    </div>
                </div>

                <!-- Tombol Buka Scanner Barcode Linier -->
                <button type="button" 
                        @click="openScannerModal()" 
                        class="inline-flex items-center justify-center gap-2 px-4 py-2 text-xs font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 rounded-xl shadow-xs transition-all shrink-0 hover:shadow-emerald-500/20 active:scale-95 cursor-pointer">
                    <svg class="w-4 h-4 text-emerald-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                    </svg>
                    <span>Scan Barcode Linier</span>
                </button>

                <div x-show="searchQuery" class="flex items-center gap-1.5 text-xs text-emerald-800 bg-emerald-50 px-3 py-1.5 rounded-xl border border-emerald-200 shrink-0" x-cloak>
                    <span>Menampilkan:</span>
                    <strong x-text="matchCount"></strong>
                    <span>dari {{ $devicesInWarehouse->count() }} unit</span>
                </div>
            </form>
        </div>

        <!-- Feedback Banner Scan Barcode Linier -->
        @if(request('highlight'))
            @php
                $highlightedDev = $devicesInWarehouse->firstWhere('id', request('highlight'));
            @endphp
            @if($highlightedDev)
                <div class="p-3.5 bg-gradient-to-r from-amber-500/15 via-emerald-500/10 to-transparent border-b border-amber-300/80 dark:border-amber-700/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-amber-500 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
                            🔔
                        </div>
                        <div>
                            <div class="font-bold text-slate-900 dark:text-white flex items-center gap-2 flex-wrap">
                                <span>Unit dari Notifikasi Teknisi:</span>
                                <span class="font-mono bg-white dark:bg-slate-900 px-2 py-0.5 rounded border border-amber-300 dark:border-amber-700 text-amber-700 dark:text-amber-300 font-bold">
                                    {{ $highlightedDev->registration_number }}
                                </span>
                                <span class="text-slate-800 dark:text-slate-200">({{ $highlightedDev->name }})</span>
                            </div>
                            <div class="text-[11px] text-slate-600 dark:text-slate-300 mt-0.5">
                                Unit telah masuk ke <strong>{{ $highlightedDev->stock_status === 'tersedia_hb_ckr' ? 'Stok HB CKR' : 'Stok HB JKT' }}</strong> dan siap dialokasikan.
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <a href="{{ route('admin.devices.show', $highlightedDev) }}" class="px-3 py-1.5 text-xs font-bold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-100 rounded-lg border border-slate-300 dark:border-slate-700 shadow-xs transition-colors">
                            Lihat Detail Lengkap &rarr;
                        </a>
                        <a href="{{ route('admin.devices.stock', array_filter(['hub' => request('hub')])) }}" class="p-1.5 text-slate-400 hover:text-slate-700 rounded-lg" title="Tutup Highlight">
                            ✕
                        </a>
                    </div>
                </div>
            @endif
        @endif

        <div x-show="highlightedDeviceId" x-cloak class="p-3.5 bg-gradient-to-r from-emerald-50 to-teal-50 border-b border-emerald-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
            <div class="flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg bg-emerald-600 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
                    ✓
                </div>
                <div>
                    <div class="font-bold text-emerald-950 flex items-center gap-2">
                        <span>Barcode Ditemukan:</span>
                        <span class="font-mono bg-white px-2 py-0.5 rounded border border-emerald-300 text-emerald-700 font-bold" x-text="scannedCode"></span>
                        <span class="text-emerald-800" x-text="'(' + (scannedDevice?.name || '') + ')'"></span>
                    </div>
                    <div class="flex items-center gap-2 mt-0.5 text-[11px] text-emerald-700">
                        <span>Perangkat tersedia di Gudang HB dan siap dialokasikan ke teknisi.</span>
                        <span x-show="rawScannedCode && rawScannedCode !== scannedCode" class="font-mono text-slate-500 bg-white/70 px-1.5 py-0.5 rounded border border-emerald-200 text-[10px]" x-text="'(Barcode Fisik: ' + rawScannedCode + ')'"></span>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <button type="button" 
                        x-show="scannedDevice"
                        @click="allocateFromScan()" 
                        class="px-3 py-1.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-xs transition-colors flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>Alokasikan Unit Ini</span>
                </button>
                <button type="button" 
                        @click="openScannerModal()" 
                        class="px-3 py-1.5 text-xs font-bold text-emerald-700 bg-white hover:bg-emerald-100 rounded-lg border border-emerald-300 transition-colors">
                    Scan Unit Lain
                </button>
                <button type="button" 
                        @click="clearScanFilter()" 
                        class="p-1.5 text-slate-400 hover:text-slate-700 rounded-lg hover:bg-slate-200 transition-colors"
                        title="Tutup & Reset">
                    ✕
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5">No. Registrasi</th>
                        <th class="px-5 py-3.5">Nama Perangkat</th>
                        <th class="px-5 py-3.5">Area</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($devicesInWarehouse as $dev)
                        <tr id="device-row-{{ $dev->id }}"
                            x-show="matchesSearch({{ $dev->id }})" 
                            class="hover:bg-slate-50/80 transition-all duration-300 {{ request('highlight') == $dev->id ? 'bg-amber-50/80 dark:bg-amber-950/30 ring-2 ring-amber-400' : '' }}"
                            :class="highlightedDeviceId === {{ $dev->id }} ? 'bg-emerald-50/90 ring-2 ring-emerald-500 shadow-sm' : ''">
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="font-mono font-bold text-slate-900 dark:text-white text-xs">
                                        {{ $dev->registration_number }}
                                    </span>
                                    @if(request('highlight') == $dev->id)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500 text-white shadow-xs animate-pulse">
                                            ✓ Unit Baru Ditambahkan
                                        </span>
                                    @endif
                                    <span x-show="highlightedDeviceId === {{ $dev->id }}" 
                                          class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-600 text-white animate-pulse" 
                                          x-cloak>
                                        ✓ Hasil Scan
                                    </span>
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="font-bold text-slate-900">{{ $dev->name }}</div>
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                @if($dev->area)
                                    <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-blue-50 text-blue-800 border border-blue-200 inline-block font-mono">
                                        {{ $dev->area }}
                                    </span>
                                @else
                                    <span class="text-slate-400 font-medium italic text-xs">-</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button" 
                                            @click="openAllocateModal('{{ $dev->id }}', '{{ $dev->registration_number }}', '{{ addslashes($dev->name) }}', '{{ $dev->nojar }}', '{{ $dev->activity }}', '{{ addslashes($dev->customer_name ?? '') }}', '{{ addslashes($dev->address ?? '') }}', '{{ $dev->area }}')"
                                            class="px-2.5 py-1 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-xs transition-colors inline-flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        <span>Alokasikan</span>
                                    </button>
                                    <a href="{{ route('admin.devices.show', $dev) }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900">
                                        Detail &rarr;
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-6 text-center text-slate-400">
                                Tidak ada perangkat berstatus tersedia di gudang HB.
                            </td>
                        </tr>
                    @endforelse

                    <!-- Empty state ketika filter pencarian tidak menemukan data -->
                    <tr x-show="isSearchEmpty" x-cloak>
                        <td colspan="4" class="px-6 py-8 text-center text-slate-400">
                            <svg class="w-8 h-8 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <p class="font-medium text-slate-600 text-xs">Tidak ditemukan perangkat dengan No. Registrasi / kata kunci "<span class="font-bold text-slate-900" x-text="searchQuery"></span>"</p>
                            <button type="button" @click="searchQuery = ''; window.location.href = '{{ route('admin.devices.stock') }}'" class="mt-2 text-xs font-bold text-emerald-600 hover:text-emerald-800 underline">
                                Reset Pencarian
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Alokasi Stok HB Dikta ke Teknisi -->
    <div x-show="allocateModalOpen" x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="allocateModalOpen = false" 
             class="bg-white rounded-2xl shadow-xl border border-slate-200 w-full max-w-lg overflow-hidden my-6 max-h-[90vh] flex flex-col">
            
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-emerald-50/60 shrink-0">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Alokasikan Ke Tim Teknisi</h3>
                    </div>
                </div>
                <button type="button" @click="allocateModalOpen = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold leading-none cursor-pointer">&times;</button>
            </div>

            <form :action="allocateActionUrl" method="POST" class="p-6 space-y-4 overflow-y-auto flex-1">
                @csrf
                
                <!-- Info Perangkat (Dipisah Kolom) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Nama Perangkat</label>
                        <div class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl font-bold text-slate-900" x-text="selectedDeviceName"></div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">No. Registrasi</label>
                        <div class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl font-mono font-bold text-slate-900" x-text="selectedDeviceReg"></div>
                    </div>
                </div>

                <!-- Pilih Teknisi -->
                <div>
                    <label for="stock_modal_technician_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Pilih Teknisi Penerima <span class="text-rose-500">*</span>
                    </label>
                    <select name="technician_id" id="stock_modal_technician_id" required 
                            class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white font-medium text-slate-800">
                        <option value="">-- Pilih Nama Teknisi --</option>
                        @foreach($technicians as $tech)
                            <option value="{{ $tech->id }}">{{ $tech->name }} {{ $tech->phone ? '('.$tech->phone.')' : '' }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- NOJAR & Pelanggan -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="stock_modal_nojar" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            NOJAR
                        </label>
                        <input type="text" name="nojar" id="stock_modal_nojar" x-model="selectedNojar"
                               class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white font-mono font-bold uppercase text-slate-800">
                    </div>
                    <div>
                        <label for="stock_modal_customer" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Pelanggan
                        </label>
                        <input type="text" name="customer_name" id="stock_modal_customer" x-model="selectedCustomer"
                               class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white font-medium text-slate-800">
                    </div>
                </div>

                <!-- Alamat -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="stock_modal_address" class="inline-flex items-center gap-1.5 text-xs font-black uppercase tracking-wider text-emerald-800 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200 shadow-2xs">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>ALAMAT</span>
                        </label>
                        <span class="text-[10px] text-slate-400">Lokasi / Alamat Pemasangan</span>
                    </div>
                    <textarea name="address" id="stock_modal_address" rows="2" x-model="selectedAddress"
                              class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-emerald-200/80 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white text-slate-800 font-medium transition-all"></textarea>
                </div>

                <!-- Aktivitas & Area -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Aktivitas Penugasan
                        </label>
                        <select name="activity" x-model="selectedActivity" class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white text-slate-800 font-medium">
                            <option value="PSB">PSB</option>
                            <option value="Troubleshoot">Troubleshoot</option>
                            <option value="Simulasi">Simulasi</option>
                            <option value="Spare Dikta">Spare Dikta</option>
                        </select>
                    </div>
                    <div>
                        <label for="stock_modal_area" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Area
                        </label>
                        <select name="area" id="stock_modal_area" x-model="selectedArea" class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white text-slate-800 font-semibold">
                            <option value="">-- Pilih Area --</option>
                            @foreach(['BWA', 'BDA', 'SEOA', 'SAST', 'CDA', 'NEA'] as $a)
                                <option value="{{ $a }}">{{ $a }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Keterangan -->
                <div>
                    <label for="stock_modal_notes" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Keterangan
                    </label>
                    <textarea name="notes" id="stock_modal_notes" rows="2"
                              class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5 shrink-0">
                    <button type="button" @click="allocateModalOpen = false" 
                            class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition-colors">
                        Konfirmasi Alokasi ke Teknisi
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Scan Barcode Linier Stok HB Dikta -->
    <div x-show="scannerModalOpen" x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-950/80 backdrop-blur-xs">
        <div @click.away="closeScannerModal()" 
             class="bg-slate-900 border border-slate-700 rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden my-4 max-h-[95vh] flex flex-col text-white">
            
            <!-- Modal Header -->
            <div class="px-5 py-4 border-b border-slate-800 flex items-center justify-between bg-slate-950 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-cyan-500/20 text-cyan-400 flex items-center justify-center border border-cyan-500/30 shadow-inner">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white tracking-wide">Scan Barcode Linier Stok HB Dikta</h3>
                        <p class="text-[11px] text-slate-400">Pindai barcode horizontal (Code 128 / SN) untuk mencari perangkat</p>
                    </div>
                </div>
                <button type="button" @click="closeScannerModal()" class="w-8 h-8 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white flex items-center justify-center transition-colors text-lg font-bold">
                    &times;
                </button>
            </div>

            <!-- Viewfinder Kamera Scanner -->
            <div class="relative bg-black flex flex-col items-center justify-center p-3 sm:p-4 min-h-[260px] sm:min-h-[290px] overflow-hidden">
                <!-- Target render html5-qrcode -->
                <div id="stock-linear-barcode-reader" class="w-full max-w-md mx-auto overflow-hidden rounded-2xl"></div>

                <!-- Overlay Persegi Panjang Linier (Laser Scan Guide Khusus Barcode Horizontal) -->
                <div x-show="cameraActive" class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center p-4">
                    <div class="relative w-[280px] sm:w-[350px] h-[110px] sm:h-[125px] rounded-2xl border-2 border-cyan-400/80 shadow-[0_0_25px_rgba(34,211,238,0.35)] flex items-center justify-center">
                        <!-- Sudut-Sudut Cyan Tebal -->
                        <div class="absolute -top-1 -left-1 w-6 h-6 border-t-4 border-l-4 border-cyan-400 rounded-tl-xl"></div>
                        <div class="absolute -top-1 -right-1 w-6 h-6 border-t-4 border-r-4 border-cyan-400 rounded-tr-xl"></div>
                        <div class="absolute -bottom-1 -left-1 w-6 h-6 border-b-4 border-l-4 border-cyan-400 rounded-bl-xl"></div>
                        <div class="absolute -bottom-1 -right-1 w-6 h-6 border-b-4 border-r-4 border-cyan-400 rounded-br-xl"></div>

                        <!-- Laser Pemindai Merah Bergerak -->
                        <div class="w-full h-0.5 bg-gradient-to-r from-transparent via-red-500 to-transparent animate-pulse shadow-sm shadow-red-500/80"></div>
                    </div>
                    <span class="mt-3 text-[11px] font-medium text-slate-300 bg-slate-900/90 px-3.5 py-1 rounded-full border border-slate-700 backdrop-blur-xs shadow-md text-center">
                        Arahkan garis merah melintang di sepanjang stiker barcode linier
                    </span>
                </div>

                <!-- State jika kamera tidak aktif -->
                <div x-show="!cameraActive" class="py-8 text-center text-slate-400 space-y-3">
                    <div class="w-12 h-12 mx-auto rounded-2xl bg-slate-800 flex items-center justify-center text-slate-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    </div>
                    <p class="text-xs">Kamera scanner sedang nonaktif</p>
                    <button type="button" @click="startCamera()" class="px-4 py-2 bg-cyan-600 hover:bg-cyan-700 text-white rounded-xl text-xs font-bold transition-all shadow-md">
                        Aktifkan Kamera Kembali
                    </button>
                </div>

                <!-- Controls: Switch Camera & Torch -->
                <div class="mt-2.5 flex items-center justify-center gap-2 z-10" x-show="cameraActive && (cameras.length > 1 || torchAvailable)">
                    <button type="button" x-show="cameras.length > 1" @click="switchCamera()" 
                            class="px-3 py-1.5 rounded-xl bg-slate-800/90 hover:bg-slate-700 text-xs font-semibold text-white border border-slate-700 flex items-center gap-1.5 shadow-xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Ganti Kamera HP</span>
                    </button>
                    <button type="button" x-show="torchAvailable" @click="toggleTorch()" 
                            class="px-3 py-1.5 rounded-xl text-xs font-semibold border flex items-center gap-1.5 shadow-xs transition-colors"
                            :class="torchOn ? 'bg-amber-500 text-white border-amber-400' : 'bg-slate-800/90 text-white border-slate-700 hover:bg-slate-700'">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                        <span x-text="torchOn ? 'Matikan Lampu' : 'Nyalakan Lampu'"></span>
                    </button>
                </div>
            </div>

            <!-- Bagian Hasil Scan & Aksi -->
            <div class="p-4 sm:p-5 bg-slate-900 border-t border-slate-800 space-y-3.5 overflow-y-auto max-h-[300px]">
                
                <!-- Loading State saat cek database -->
                <div x-show="scanStatus === 'loading'" class="p-4 rounded-2xl bg-slate-800/60 border border-slate-700 text-center space-y-2">
                    <svg class="animate-spin h-6 w-6 text-cyan-400 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <p class="text-xs text-slate-300">Memeriksa barcode <span class="font-mono font-bold text-cyan-300" x-text="scannedCode"></span> di database...</p>
                </div>

                <!-- HASIL 1: DITEMUKAN DI GUDANG HB DIKTA (SIAP ALOKASI) -->
                <div x-show="scanStatus === 'found_warehouse'" class="rounded-2xl bg-emerald-950/70 border border-emerald-500/50 p-4 space-y-3 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center text-xs font-bold shadow-xs">✓</span>
                            <div>
                                <span class="text-xs font-bold text-emerald-300 uppercase tracking-wide">Unit Tersedia di Gudang HB!</span>
                                <div x-show="rawScannedCode && rawScannedCode !== scannedCode" class="text-[10px] text-slate-400 font-mono mt-0.5">
                                    Barcode Fisik: <span class="text-slate-300" x-text="rawScannedCode"></span> &bull; Terbaca: <span class="text-emerald-300 font-bold" x-text="scannedCode"></span>
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-[9px] uppercase tracking-wider text-slate-400 block font-bold">No. Registrasi:</span>
                            <span class="font-mono text-xs font-bold text-emerald-100 bg-emerald-900/80 px-2.5 py-0.5 rounded border border-emerald-600 inline-block mt-0.5" x-text="scannedDevice?.registration_number || scannedCode"></span>
                        </div>
                    </div>

                    <div class="bg-slate-950/60 rounded-xl p-3 border border-emerald-900/40 text-xs space-y-1.5">
                        <div class="font-bold text-white text-sm" x-text="scannedDevice?.name"></div>
                        <div class="text-[11px] text-slate-300 flex flex-wrap gap-x-3 gap-y-1">
                            <span>Merk/Tipe: <strong class="text-white" x-text="(scannedDevice?.brand || '') + ' ' + (scannedDevice?.type || '')"></strong></span>
                            <span x-show="scannedDevice?.serial_number">SN: <strong class="font-mono text-white" x-text="scannedDevice?.serial_number"></strong></span>
                            <span x-show="scannedDevice?.area">Area: <strong class="text-blue-300" x-text="scannedDevice?.area"></strong></span>
                        </div>
                        <div x-show="scannedDevice?.customer_name" class="text-[11px] text-emerald-300 pt-1 border-t border-slate-800">
                            Pelanggan: <strong class="text-white" x-text="scannedDevice?.customer_name"></strong>
                            <span x-show="scannedDevice?.nojar" class="ml-1 text-slate-400 font-mono" x-text="'(NOJAR: ' + scannedDevice?.nojar + ')'"></span>
                        </div>
                        <div class="text-[10px] text-slate-400">
                            Lokasi: <span class="text-slate-300" x-text="scannedDevice?.current_location || 'Gudang HB Dikta'"></span>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 pt-1">
                        <button type="button" 
                                @click="allocateFromScan()" 
                                class="flex-1 py-2 px-3 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold shadow-md transition-all flex items-center justify-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span>Alokasikan ke Teknisi</span>
                        </button>
                        <button type="button" 
                                @click="filterTableFromScan()" 
                                class="py-2 px-3 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl text-xs font-semibold border border-slate-700 transition-all">
                            Lihat di Tabel
                        </button>
                        <button type="button" 
                                @click="scanAgain()" 
                                class="py-2 px-3 text-cyan-400 hover:text-cyan-300 text-xs font-semibold transition-all">
                            Scan Unit Lain
                        </button>
                    </div>
                </div>

                <!-- HASIL 2: DITEMUKAN DI SISTEM, TAPI BUKAN DI GUDANG HB DIKTA -->
                <div x-show="scanStatus === 'found_other'" class="rounded-2xl bg-amber-950/70 border border-amber-500/50 p-4 space-y-3 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-amber-500 text-slate-950 flex items-center justify-center text-xs font-bold">!</span>
                            <div>
                                <span class="text-xs font-bold text-amber-300 uppercase tracking-wide">Unit Tidak Berada di Gudang HB</span>
                                <div x-show="rawScannedCode && rawScannedCode !== scannedCode" class="text-[10px] text-amber-300/80 font-mono mt-0.5">
                                    Barcode Fisik: <span x-text="rawScannedCode"></span> &bull; Terbaca: <span class="font-bold" x-text="scannedCode"></span>
                                </div>
                            </div>
                        </div>
                        <span class="font-mono text-xs font-bold text-amber-200 bg-amber-900/80 px-2.5 py-0.5 rounded border border-amber-600" x-text="scannedDevice?.registration_number || scannedCode"></span>
                    </div>

                    <div class="bg-slate-950/60 rounded-xl p-3 border border-amber-900/40 text-xs space-y-1.5">
                        <div class="font-bold text-white text-sm" x-text="scannedDevice?.name"></div>
                        <div class="text-[11px] text-amber-200">
                            Status Saat Ini: <strong class="px-2 py-0.5 rounded bg-amber-900/90 text-amber-300 font-bold" x-text="scannedDevice?.stock_status_label"></strong>
                        </div>
                        <div class="text-[11px] text-slate-300">
                            Lokasi Terakhir: <strong class="text-white" x-text="scannedDevice?.current_location"></strong>
                        </div>
                        <div x-show="scannedDevice?.current_technician_name" class="text-[11px] text-slate-300">
                            Teknisi Pembawa: <strong class="text-cyan-300" x-text="scannedDevice?.current_technician_name"></strong>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 pt-1">
                        <a :href="scannedDevice?.show_url" 
                           class="flex-1 py-2 px-3 bg-amber-600 hover:bg-amber-500 text-slate-950 rounded-xl text-xs font-bold shadow-md text-center transition-all">
                            Buka Detail Perangkat &rarr;
                        </a>
                        <button type="button" 
                                @click="scanAgain()" 
                                class="py-2 px-3 text-amber-300 hover:text-white text-xs font-semibold transition-all">
                            Scan Unit Lain
                        </button>
                    </div>
                </div>

                <!-- HASIL 3: TIDAK DITEMUKAN DI SISTEM -->
                <div x-show="scanStatus === 'not_found'" class="rounded-2xl bg-rose-950/70 border border-rose-500/50 p-4 space-y-3 shadow-lg">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-rose-500 text-white flex items-center justify-center text-xs font-bold">✕</span>
                        <span class="text-xs font-bold text-rose-300 uppercase tracking-wide">Barcode Belum Terdaftar</span>
                    </div>

                    <div class="space-y-1 text-xs text-slate-300">
                        <p>
                            No. Registrasi <span class="font-mono font-bold text-white bg-slate-950 px-2 py-0.5 rounded border border-rose-800" x-text="scannedCode"></span> tidak ditemukan dalam database sistem PT DIKTA.
                        </p>
                        <p x-show="rawScannedCode && rawScannedCode !== scannedCode" class="text-[11px] text-slate-400 font-mono">
                            Barcode Fisik: <span class="text-slate-300" x-text="rawScannedCode"></span> (ekstrak 5-6 karakter terakhir: <span class="text-rose-300 font-bold" x-text="scannedCode"></span>)
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 pt-1">
                        <a :href="'{{ route('admin.devices.create') }}?reg=' + encodeURIComponent(scannedCode) + '&sn=' + encodeURIComponent(rawScannedCode || scannedCode)" 
                           class="flex-1 py-2 px-3 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-bold shadow-md text-center transition-all">
                            + Daftarkan Unit Baru (Reg: <span x-text="scannedCode"></span>)
                        </a>
                        <button type="button" 
                                @click="scanAgain()" 
                                class="py-2 px-3 text-rose-300 hover:text-white text-xs font-semibold transition-all">
                            Scan Ulang
                        </button>
                    </div>
                </div>

                <!-- Input Manual / Dukungan Scanner Barcode Gun USB -->
                <div class="pt-2 border-t border-slate-800">
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                        Atau Tembak Barcode Scanner USB / Ketik Manual:
                    </label>
                    <div class="flex items-center gap-2">
                        <div class="relative flex-1">
                            <input type="text" 
                                   x-model="manualBarcodeInput" 
                                   @keydown.enter.prevent="processManualInput()"
                                   placeholder="Contoh: REG-001 atau Serial Number..." 
                                   class="w-full px-3 py-2 text-xs bg-slate-950 border border-slate-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-cyan-500 text-white font-mono placeholder-slate-500">
                        </div>
                        <button type="button" 
                                @click="processManualInput()" 
                                class="px-3.5 py-2 text-xs font-bold text-white bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-xl transition-colors">
                            Cek
                        </button>
                    </div>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="px-5 py-3 bg-slate-950 border-t border-slate-800 flex items-center justify-between text-xs text-slate-400 shrink-0">
                <span class="text-[11px]">Mendukung Code 128, Code 39, EAN, dan Barcode Gun</span>
                <button type="button" @click="closeScannerModal()" 
                        class="px-3.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold transition-colors">
                    Selesai / Tutup
                </button>
            </div>
        </div>
    </div>

</div>

@include('admin.devices.partials.import_modal')

@push('scripts')
<script>
function stockAllocationApp() {
    return {
        searchQuery: @json($search ?? ''),
        allocateModalOpen: false,
        selectedDeviceId: '',
        selectedDeviceName: '',
        selectedDeviceReg: '',
        selectedNojar: '',
        selectedCustomer: '',
        selectedAddress: '',
        selectedActivity: 'PSB',
        selectedArea: '',
        allocateActionUrl: '',

        // Barcode Scanner State
        scannerModalOpen: false,
        cameraActive: false,
        html5QrCode: null,
        cameras: [],
        currentCameraIndex: 0,
        torchAvailable: false,
        torchOn: false,
        scanStatus: 'idle', // 'idle' | 'loading' | 'found_warehouse' | 'found_other' | 'not_found'
        scannedCode: '',
        rawScannedCode: '',
        scannedDevice: null,
        manualBarcodeInput: '',
        highlightedDeviceId: null,

        devices: {!! json_encode($devicesInWarehouseData ?? []) !!},

        extractRegNumber(raw) {
            if (!raw) return '';
            const clean = raw.trim();
            // Pola barcode Lintasarta (contoh B2WN0262020HA0267 -> ambil HA0267 / HA2067)
            const suffixPattern = /([A-Za-z]{1,3}[-_]?\d{3,6})$/;
            const match = clean.match(suffixPattern);
            if (match && match[1]) {
                return match[1].toUpperCase();
            }
            if (clean.length > 10) {
                return clean.slice(-6).toUpperCase();
            }
            return clean.toUpperCase();
        },

        matchesSearch(id) {
            if (!this.searchQuery || this.searchQuery.trim() === '') return true;
            const q = this.searchQuery.toLowerCase().trim();
            const ext = this.extractRegNumber(q).toLowerCase();
            const dev = this.devices.find(d => d.id === id);
            if (!dev) return true;
            const reg = (dev.registration_number || '').toLowerCase();
            const sn = (dev.serial_number || '').toLowerCase();
            return (
                dev.searchStr.includes(q) ||
                dev.searchStr.includes(ext) ||
                (reg && q.endsWith(reg)) ||
                (sn && q.endsWith(sn)) ||
                (reg && ext.endsWith(reg))
            );
        },

        get matchCount() {
            if (!this.searchQuery || this.searchQuery.trim() === '') return this.devices.length;
            const q = this.searchQuery.toLowerCase().trim();
            return this.devices.filter(d => d.searchStr.includes(q)).length;
        },

        get isSearchEmpty() {
            return this.devices.length > 0 && this.matchCount === 0;
        },

        openAllocateModal(id, reg, name, nojar, activity, customer, address, area) {
            this.selectedDeviceId = id;
            this.selectedDeviceName = name;
            this.selectedDeviceReg = reg;
            this.selectedNojar = nojar || '';
            this.selectedCustomer = customer || '';
            this.selectedAddress = address || '';
            this.selectedActivity = activity || 'PSB';
            this.selectedArea = area || '';
            this.allocateActionUrl = '/admin/devices/' + id + '/allocate';
            this.allocateModalOpen = true;
        },

        // --- Fitur Scan Barcode Linier ---
        openScannerModal() {
            this.scannerModalOpen = true;
            this.scanStatus = 'idle';
            this.scannedCode = '';
            this.rawScannedCode = '';
            this.scannedDevice = null;
            this.manualBarcodeInput = '';
            this.startCamera();
        },

        closeScannerModal() {
            this.stopCamera();
            this.scannerModalOpen = false;
        },

        async startCamera() {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                alert("Perhatian: Browser memblokir kamera jika website dibuka melalui HTTP biasa non-localhost.\n\nSilakan buka website melalui link HTTPS Cloudflare (jalankan mulai-aplikasi-online.bat di laptop) agar izin kamera di HP otomatis aktif!");
                return;
            }

            this.cameraActive = true;
            this.scanStatus = 'idle';
            this.$nextTick(async () => {
                const self = this;
                try {
                    if (this.html5QrCode) {
                        try { await this.html5QrCode.stop(); } catch(e) {}
                        try { await this.html5QrCode.clear(); } catch(e) {}
                    }

                    this.html5QrCode = new Html5Qrcode("stock-linear-barcode-reader", {
                        verbose: false,
                        experimentalFeatures: {
                            useBarCodeDetectorIfSupported: true
                        }
                    });

                    // Ambil daftar kamera
                    try {
                        const devices = await Html5Qrcode.getCameras();
                        if (devices && devices.length) {
                            self.cameras = devices;
                        }
                    } catch(e) {}

                    // Konfigurasi pemindaian: TANPA qrbox agar 100% frame kamera dibaca dan garis barcode panjang tidak terpotong!
                    const config = {
                        fps: 25,
                        aspectRatio: 1.777778
                    };

                    const onScanSuccess = (decodedText) => {
                        self.handleBarcodeScanned(decodedText);
                    };

                    try {
                        await this.html5QrCode.start(
                            { facingMode: "environment" },
                            config,
                            onScanSuccess,
                            () => {}
                        );
                    } catch(facingErr) {
                        if (self.cameras && self.cameras.length) {
                            const backCam = self.cameras.find(c => c.label.toLowerCase().includes('back') || c.label.toLowerCase().includes('rear')) || self.cameras[self.cameras.length - 1];
                            await this.html5QrCode.start(
                                backCam.id,
                                config,
                                onScanSuccess,
                                () => {}
                            );
                        } else {
                            await this.html5QrCode.start(
                                undefined,
                                config,
                                onScanSuccess,
                                () => {}
                            );
                        }
                    }

                    try {
                        const trackCapabilities = self.html5QrCode.getRunningTrackCameraCapabilities();
                        if (trackCapabilities && trackCapabilities.torchFeature && trackCapabilities.torchFeature().isSupported()) {
                            self.torchAvailable = true;
                        }
                    } catch(e) {}

                } catch (err) {
                    console.error("Camera start error:", err);
                    self.cameraActive = false;
                }
            });
        },

        async switchCamera() {
            if (!this.html5QrCode || this.cameras.length < 2) return;
            this.currentCameraIndex = (this.currentCameraIndex + 1) % this.cameras.length;
            const nextCam = this.cameras[this.currentCameraIndex];

            try {
                await this.html5QrCode.stop();
                const self = this;
                const config = {
                    fps: 25,
                    aspectRatio: 1.777778
                };
                await this.html5QrCode.start(
                    nextCam.id,
                    config,
                    (decodedText) => {
                        self.handleBarcodeScanned(decodedText);
                    },
                    () => {}
                );
            } catch(e) {
                console.error("Gagal ganti kamera:", e);
            }
        },

        async toggleTorch() {
            if (!this.html5QrCode || !this.torchAvailable) return;
            try {
                this.torchOn = !this.torchOn;
                await this.html5QrCode.applyVideoConstraints({
                    advanced: [{ torch: this.torchOn }]
                });
            } catch(e) {
                this.torchOn = false;
            }
        },

        playBeep() {
            try {
                const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = audioCtx.createOscillator();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(800, audioCtx.currentTime);
                osc.connect(audioCtx.destination);
                osc.start();
                osc.stop(audioCtx.currentTime + 0.12);
            } catch (e) {}
        },

        stopCamera() {
            this.torchOn = false;
            this.torchAvailable = false;
            if (this.html5QrCode && this.cameraActive) {
                this.html5QrCode.stop().then(() => {
                    this.cameraActive = false;
                }).catch(() => {
                    this.cameraActive = false;
                });
            } else {
                this.cameraActive = false;
            }
        },

        handleBarcodeScanned(code) {
            const raw = (code || '').trim();
            if (!raw) return;
            this.playBeep();
            if (navigator.vibrate) {
                try { navigator.vibrate(100); } catch(e) {}
            }
            this.rawScannedCode = raw;
            const extracted = this.extractRegNumber(raw);
            this.scannedCode = extracted;
            this.stopCamera();
            this.lookupBarcode(extracted, raw);
        },

        processManualInput() {
            if (!this.manualBarcodeInput || !this.manualBarcodeInput.trim()) return;
            this.handleBarcodeScanned(this.manualBarcodeInput.trim());
        },

        scanAgain() {
            this.scanStatus = 'idle';
            this.scannedCode = '';
            this.rawScannedCode = '';
            this.scannedDevice = null;
            this.manualBarcodeInput = '';
            this.startCamera();
        },

        lookupBarcode(extracted, raw) {
            const extQ = (extracted || '').toLowerCase().trim();
            const rawQ = (raw || extQ).toLowerCase().trim();
            
            // 1. Cek di daftar unit lokal gudang HB Dikta
            const inWarehouse = this.devices.find(d => {
                const reg = (d.registration_number || '').toLowerCase();
                const sn = (d.serial_number || '').toLowerCase();
                return (
                    (reg && (reg === extQ || reg === rawQ || rawQ.endsWith(reg) || extQ.endsWith(reg))) ||
                    (sn && (sn === rawQ || sn === extQ || rawQ.endsWith(sn))) ||
                    d.searchStr.includes(extQ) ||
                    d.searchStr.includes(rawQ)
                );
            });

            if (inWarehouse) {
                this.scannedDevice = inWarehouse;
                this.scanStatus = 'found_warehouse';
                this.highlightedDeviceId = inWarehouse.id;
                this.searchQuery = inWarehouse.registration_number;
                return;
            }

            // 2. Jika tidak ada di array lokal, cek database global via AJAX
            this.scanStatus = 'loading';
            fetch(`{{ route('admin.devices.check-registration') }}?reg=${encodeURIComponent(extracted)}&raw=${encodeURIComponent(raw || extracted)}`)
                .then(res => res.json())
                .then(data => {
                    if (data && data.exists) {
                        this.scannedDevice = data;
                        if (data.is_in_warehouse) {
                            this.scanStatus = 'found_warehouse';
                            this.highlightedDeviceId = data.id;
                            this.searchQuery = data.registration_number;
                        } else {
                            this.scanStatus = 'found_other';
                        }
                    } else {
                        this.scanStatus = 'not_found';
                    }
                })
                .catch(err => {
                    console.error("Gagal memeriksa barcode:", err);
                    this.scanStatus = 'not_found';
                });
        },

        allocateFromScan() {
            if (!this.scannedDevice) return;
            const d = this.scannedDevice;
            this.closeScannerModal();
            this.openAllocateModal(
                d.id,
                d.registration_number,
                d.name,
                d.nojar || '',
                d.activity || 'PSB',
                d.customer_name || '',
                d.address || '',
                d.area || ''
            );
        },

        filterTableFromScan() {
            if (!this.scannedDevice) return;
            const id = this.scannedDevice.id;
            this.highlightedDeviceId = id;
            this.searchQuery = this.scannedDevice.registration_number;
            this.closeScannerModal();
            this.$nextTick(() => {
                const row = document.getElementById('device-row-' + id);
                if (row) {
                    row.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            });
        },

        clearScanFilter() {
            this.highlightedDeviceId = null;
            this.scannedCode = '';
            this.rawScannedCode = '';
            this.scannedDevice = null;
            this.searchQuery = '';
        }
    }
}
</script>
@endpush
@endsection
