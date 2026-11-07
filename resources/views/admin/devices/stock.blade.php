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
                @if(request('hub'))
                    <input type="hidden" name="hub" value="{{ request('hub') }}">
                @endif
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
                                @click="searchQuery = ''; window.location.href='{{ route('admin.devices.stock', array_filter(['hub' => request('hub')])) }}';" 
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

                <div x-show="searchQuery" class="flex items-center gap-1.5 text-xs text-emerald-800 bg-emerald-50 px-3 py-1.5 rounded-xl border border-emerald-200 shrink-0" x-cloak>
                    <span>Menampilkan:</span>
                    <strong x-text="matchCount"></strong>
                    <span>dari {{ $devicesInWarehouse->total() }} unit</span>
                </div>
            </form>
        </div>

        @if(request('highlight'))
            @php
                $highlightedDev = $devicesInWarehouse->firstWhere('id', request('highlight')) ?? \App\Models\Device::find(request('highlight'));
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

        @if($devicesInWarehouse->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                {{ $devicesInWarehouse->links() }}
            </div>
        @endif
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
        }
    }
}
</script>
@endpush
@endsection
