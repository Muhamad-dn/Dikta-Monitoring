@extends('layouts.app')

@section('title', 'Dashboard Admin')
@section('page_title', 'Dashboard Administrator')
@section('page_subtitle', 'Pusat kontrol stok perangkat dan data teknisi PT DIKTA')

@section('content')
<div class="space-y-6" x-data="dashboardAllocationApp()" x-data="{ openMetrics: true }">

    <!-- Aksi Utama & Status Database -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex flex-wrap items-center justify-between gap-3">
        <div>
            <a href="{{ route('admin.devices.create') }}" class="px-4 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-xs transition-colors inline-flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Tambah Perangkat Baru</span>
            </a>
        </div>
        <div class="text-xs text-slate-500 font-medium">
            Database: <span class="font-bold text-slate-800">SQLite (Aktif)</span>
        </div>
    </div>

    <!-- 7 KARTU METRIK: 4 DI ATAS & 3 DI BAWAH (INTERAKTIF & DAPAT DIKLIK) -->
    <div class="space-y-4" x-show="openMetrics">
        
        <!-- Baris 1: 4 Kartu di Atas -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            
            <!-- 1. Total Semua Perangkat -->
            <a href="{{ route('admin.dashboard', ['status' => 'all']) }}" 
               class="group p-5 rounded-2xl border transition-all duration-200 cursor-pointer block {{ $statusFilter === 'all' ? 'bg-slate-900 text-white border-slate-900 shadow-md ring-2 ring-slate-800' : 'bg-white border-slate-200 shadow-xs hover:border-slate-400 hover:shadow-md hover:-translate-y-0.5' }}">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold uppercase tracking-wider {{ $statusFilter === 'all' ? 'text-slate-300' : 'text-slate-400' }}">Total Semua Perangkat</span>
                    @if($statusFilter === 'all')
                        <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold bg-blue-500 text-white">Dipilih</span>
                    @endif
                </div>
                <div class="text-2xl font-black mt-1 {{ $statusFilter === 'all' ? 'text-white' : 'text-slate-900' }}">{{ $stats['total_devices'] }} <span class="text-xs font-normal {{ $statusFilter === 'all' ? 'text-slate-300' : 'text-slate-400' }}">Unit</span></div>
                <div class="mt-1 flex items-center justify-between text-[11px] {{ $statusFilter === 'all' ? 'text-slate-300' : 'text-slate-500' }}">
                    <span>Terdaftar di sistem</span>
                    <span class="font-bold {{ $statusFilter === 'all' ? 'text-blue-300' : 'text-blue-600' }} group-hover:underline">Buka &rarr;</span>
                </div>
            </a>

            <!-- 2. Tersedia di HB Dikta -->
            <a href="{{ route('admin.dashboard', ['status' => 'tersedia_hb_dikta']) }}" 
               class="group p-5 rounded-2xl border transition-all duration-200 cursor-pointer block {{ $statusFilter === 'tersedia_hb_dikta' ? 'bg-emerald-600 text-white border-emerald-700 shadow-md ring-2 ring-emerald-500' : 'bg-white border-emerald-200 bg-emerald-50/30 shadow-xs hover:border-emerald-400 hover:shadow-md hover:-translate-y-0.5' }}">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold uppercase tracking-wider {{ $statusFilter === 'tersedia_hb_dikta' ? 'text-emerald-100' : 'text-emerald-700' }}">Tersedia di HB Dikta</span>
                    @if($statusFilter === 'tersedia_hb_dikta')
                        <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold bg-white text-emerald-800">Dipilih</span>
                    @endif
                </div>
                <div class="text-2xl font-black mt-1 {{ $statusFilter === 'tersedia_hb_dikta' ? 'text-white' : 'text-emerald-600' }}">{{ $stats['devices_at_hb_dikta'] }} <span class="text-xs font-normal {{ $statusFilter === 'tersedia_hb_dikta' ? 'text-emerald-100' : 'text-slate-400' }}">Unit</span></div>
                <div class="mt-1 flex items-center justify-between text-[11px] {{ $statusFilter === 'tersedia_hb_dikta' ? 'text-emerald-100' : 'text-emerald-700' }}">
                    <span>Stok siap dialokasikan</span>
                    <span class="font-bold {{ $statusFilter === 'tersedia_hb_dikta' ? 'text-white' : 'text-emerald-700' }} group-hover:underline">Buka &rarr;</span>
                </div>
            </a>

            <!-- 3. Dibawa Teknisi -->
            <a href="{{ route('admin.dashboard', ['status' => 'dibawa_teknisi']) }}" 
               class="group p-5 rounded-2xl border-2 transition-all duration-200 cursor-pointer block {{ $statusFilter === 'dibawa_teknisi' ? 'bg-amber-500 text-slate-900 border-amber-600 shadow-md ring-2 ring-amber-400' : 'bg-white border-amber-300 bg-amber-50/30 shadow-xs hover:border-amber-400 hover:shadow-md hover:-translate-y-0.5' }}">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold uppercase tracking-wider {{ $statusFilter === 'dibawa_teknisi' ? 'text-slate-900 font-extrabold' : 'text-amber-800' }}">Dibawa Teknisi</span>
                    @if($statusFilter === 'dibawa_teknisi')
                        <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold bg-slate-900 text-white">Dipilih</span>
                    @endif
                </div>
                <div class="text-2xl font-black mt-1 {{ $statusFilter === 'dibawa_teknisi' ? 'text-slate-950' : 'text-amber-600' }}">{{ $stats['devices_with_tech'] }} <span class="text-xs font-normal {{ $statusFilter === 'dibawa_teknisi' ? 'text-slate-800' : 'text-slate-400' }}">Unit</span></div>
                <div class="mt-1 flex items-center justify-between text-[11px] {{ $statusFilter === 'dibawa_teknisi' ? 'text-slate-900 font-medium' : 'text-amber-800' }}">
                    <span>Sedang beredar di luar</span>
                    <span class="font-bold {{ $statusFilter === 'dibawa_teknisi' ? 'text-slate-950' : 'text-amber-700' }} group-hover:underline">Buka &rarr;</span>
                </div>
            </a>

            <!-- 4. Terinstal -->
            <a href="{{ route('admin.dashboard', ['status' => 'terinstal']) }}" 
               class="group p-5 rounded-2xl border transition-all duration-200 cursor-pointer block {{ $statusFilter === 'terinstal' ? 'bg-blue-600 text-white border-blue-700 shadow-md ring-2 ring-blue-500' : 'bg-white border-blue-200 bg-blue-50/30 shadow-xs hover:border-blue-400 hover:shadow-md hover:-translate-y-0.5' }}">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold uppercase tracking-wider {{ $statusFilter === 'terinstal' ? 'text-blue-100' : 'text-blue-700' }}">Terinstal</span>
                    @if($statusFilter === 'terinstal')
                        <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold bg-white text-blue-800">Dipilih</span>
                    @endif
                </div>
                <div class="text-2xl font-black mt-1 {{ $statusFilter === 'terinstal' ? 'text-white' : 'text-blue-600' }}">{{ $stats['devices_installed'] }} <span class="text-xs font-normal {{ $statusFilter === 'terinstal' ? 'text-blue-100' : 'text-slate-400' }}">Unit</span></div>
                <div class="mt-1 flex items-center justify-between text-[11px] {{ $statusFilter === 'terinstal' ? 'text-blue-100' : 'text-blue-700' }}">
                    <span>Aktif online di pelanggan</span>
                    <span class="font-bold {{ $statusFilter === 'terinstal' ? 'text-white' : 'text-blue-700' }} group-hover:underline">Buka &rarr;</span>
                </div>
            </a>

        </div>

        <!-- Baris 2: 3 Kartu di Bawah -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            
            <!-- 5. Pending -->
            <a href="{{ route('admin.dashboard', ['status' => 'pending']) }}" 
               class="group p-5 rounded-2xl border transition-all duration-200 cursor-pointer block {{ $statusFilter === 'pending' ? 'bg-orange-500 text-white border-orange-600 shadow-md ring-2 ring-orange-400' : 'bg-white border-orange-200 bg-orange-50/30 shadow-xs hover:border-orange-400 hover:shadow-md hover:-translate-y-0.5' }}">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold uppercase tracking-wider {{ $statusFilter === 'pending' ? 'text-orange-100' : 'text-orange-800' }}">Pending</span>
                    @if($statusFilter === 'pending')
                        <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold bg-white text-orange-900">Dipilih</span>
                    @endif
                </div>
                <div class="text-2xl font-black mt-1 {{ $statusFilter === 'pending' ? 'text-white' : 'text-orange-600' }}">{{ $stats['devices_pending'] }} <span class="text-xs font-normal {{ $statusFilter === 'pending' ? 'text-orange-100' : 'text-slate-400' }}">Unit</span></div>
                <div class="mt-1 flex items-center justify-between text-[11px] {{ $statusFilter === 'pending' ? 'text-orange-100' : 'text-orange-800' }}">
                    <span>Pemasangan tertunda teknisi</span>
                    <span class="font-bold {{ $statusFilter === 'pending' ? 'text-white' : 'text-orange-700' }} group-hover:underline">Buka &rarr;</span>
                </div>
            </a>

            <!-- 6. Rusak -->
            <a href="{{ route('admin.dashboard', ['status' => 'rusak']) }}" 
               class="group p-5 rounded-2xl border transition-all duration-200 cursor-pointer block {{ $statusFilter === 'rusak' ? 'bg-rose-600 text-white border-rose-700 shadow-md ring-2 ring-rose-500' : 'bg-white border-rose-200 bg-rose-50/30 shadow-xs hover:border-rose-400 hover:shadow-md hover:-translate-y-0.5' }}">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold uppercase tracking-wider {{ $statusFilter === 'rusak' ? 'text-rose-100' : 'text-rose-700' }}">Rusak</span>
                    @if($statusFilter === 'rusak')
                        <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold bg-white text-rose-800">Dipilih</span>
                    @endif
                </div>
                <div class="text-2xl font-black mt-1 {{ $statusFilter === 'rusak' ? 'text-white' : 'text-rose-600' }}">{{ $stats['devices_broken'] }} <span class="text-xs font-normal {{ $statusFilter === 'rusak' ? 'text-rose-100' : 'text-slate-400' }}">Unit</span></div>
                <div class="mt-1 flex items-center justify-between text-[11px] {{ $statusFilter === 'rusak' ? 'text-rose-100' : 'text-rose-700' }}">
                    <span>Perlu perbaikan / retur</span>
                    <span class="font-bold {{ $statusFilter === 'rusak' ? 'text-white' : 'text-rose-700' }} group-hover:underline">Buka &rarr;</span>
                </div>
            </a>

            <!-- 7. Hilang -->
            <a href="{{ route('admin.dashboard', ['status' => 'hilang']) }}" 
               class="group p-5 rounded-2xl border transition-all duration-200 cursor-pointer block {{ $statusFilter === 'hilang' ? 'bg-purple-600 text-white border-purple-700 shadow-md ring-2 ring-purple-500' : 'bg-white border-purple-200 bg-purple-50/30 shadow-xs hover:border-purple-400 hover:shadow-md hover:-translate-y-0.5' }}">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold uppercase tracking-wider {{ $statusFilter === 'hilang' ? 'text-purple-100' : 'text-purple-800' }}">Hilang</span>
                    @if($statusFilter === 'hilang')
                        <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold bg-white text-purple-900">Dipilih</span>
                    @endif
                </div>
                <div class="text-2xl font-black mt-1 {{ $statusFilter === 'hilang' ? 'text-white' : 'text-purple-700' }}">{{ $stats['devices_lost'] }} <span class="text-xs font-normal {{ $statusFilter === 'hilang' ? 'text-purple-100' : 'text-slate-400' }}">Unit</span></div>
                <div class="mt-1 flex items-center justify-between text-[11px] {{ $statusFilter === 'hilang' ? 'text-purple-100' : 'text-purple-800' }}">
                    <span>Hilang saat operasional</span>
                    <span class="font-bold {{ $statusFilter === 'hilang' ? 'text-white' : 'text-purple-700' }} group-hover:underline">Buka &rarr;</span>
                </div>
            </a>

        </div>

    </div>

    @php
        $statusMeta = [
            'all' => [
                'title' => 'Semua Perangkat Terdaftar',
                'desc' => 'Daftar keseluruhan unit perangkat dalam inventaris PT DIKTA',
                'badge' => $devices->count() . ' Unit Total',
                'badge_class' => 'bg-slate-100 text-slate-800 border-slate-300'
            ],
            'tersedia_hb_dikta' => [
                'title' => 'Perangkat Tersedia di HB Dikta',
                'desc' => 'Stok perangkat yang berada di gudang dan siap dialokasikan',
                'badge' => $devices->count() . ' Unit Tersedia',
                'badge_class' => 'bg-emerald-100 text-emerald-800 border-emerald-200'
            ],
            'dibawa_teknisi' => [
                'title' => 'Perangkat Yang Sedang Dibawa Teknisi (Di Luar Kantor)',
                'desc' => 'Pantau secara langsung untuk mencegah unit hilang atau tidak kembali',
                'badge' => $devices->count() . ' Unit Beredar',
                'badge_class' => 'bg-amber-100 text-amber-800 border-amber-200'
            ],
            'terinstal' => [
                'title' => 'Perangkat Terinstal di Pelanggan',
                'desc' => 'Perangkat yang telah terpasang aktif di lokasi pelanggan',
                'badge' => $devices->count() . ' Unit Terinstal',
                'badge_class' => 'bg-blue-100 text-blue-800 border-blue-200'
            ],
            'pending' => [
                'title' => 'Perangkat Berstatus Pending',
                'desc' => 'Pekerjaan pemasangan tertunda karena kendala teknis di lapangan',
                'badge' => $devices->count() . ' Unit Pending',
                'badge_class' => 'bg-orange-100 text-orange-800 border-orange-200'
            ],
            'rusak' => [
                'title' => 'Perangkat Rusak',
                'desc' => 'Unit mengalami kerusakan fisik/sistem dan perlu perbaikan atau retur',
                'badge' => $devices->count() . ' Unit Rusak',
                'badge_class' => 'bg-rose-100 text-rose-800 border-rose-200'
            ],
            'hilang' => [
                'title' => 'Perangkat Hilang',
                'desc' => 'Unit dilaporkan hilang saat operasional penugasan teknisi di lapangan',
                'badge' => $devices->count() . ' Unit Hilang',
                'badge_class' => 'bg-purple-100 text-purple-800 border-purple-200'
            ],
        ];
        $currentMeta = $statusMeta[$statusFilter] ?? $statusMeta['dibawa_teknisi'];
    @endphp

    <!-- TABEL FOKUS SESUAI STATUS YANG DIPILIH -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 class="text-sm font-bold text-slate-900">{{ $currentMeta['title'] }}</h3>
                <p class="text-xs text-slate-500">{{ $currentMeta['desc'] }}</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="px-2.5 py-1 rounded-full text-xs font-bold border {{ $currentMeta['badge_class'] }}">
                    {{ $currentMeta['badge'] }}
                </span>
                <a href="{{ route('admin.devices.index', $statusFilter !== 'all' ? ['stock_status' => $statusFilter] : []) }}" 
                   class="text-xs font-bold text-blue-600 hover:text-blue-800 hover:underline inline-flex items-center gap-1">
                    <span>Buka di Data Perangkat</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
            </div>
        </div>

        <!-- Search Bar No. Registrasi / NOJAR -->
        <div class="p-4 bg-slate-50/70 border-b border-slate-100">
            <form method="GET" action="{{ route('admin.dashboard') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <input type="hidden" name="status" value="{{ $statusFilter }}">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Cari dengan No.registrasi / Nojar" 
                           class="w-full pl-9 pr-24 py-2 text-xs bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-xs placeholder-slate-400 font-medium">
                    <div class="absolute inset-y-0 right-0 pr-1.5 flex items-center gap-1">
                        @if(request('search'))
                            <a href="{{ route('admin.dashboard', ['status' => $statusFilter]) }}" 
                               class="px-2 py-0.5 text-[10px] font-semibold text-slate-500 hover:text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-md transition-colors"
                               title="Bersihkan pencarian">
                                ✕ Hapus
                            </a>
                        @endif
                        <button type="submit" 
                                class="px-2.5 py-1 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-xs transition-colors">
                            Cari
                        </button>
                    </div>
                </div>
                @if(request('search'))
                    <div class="flex items-center gap-1.5 text-xs text-blue-800 bg-blue-50 px-3 py-1.5 rounded-xl border border-blue-200 shrink-0">
                        <span>Hasil cari:</span>
                        <strong>{{ $devices->count() }} unit</strong>
                    </div>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3">No. Registrasi</th>
                        <th class="px-6 py-3">Nama Perangkat</th>
                        <th class="px-6 py-3">Area</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Teknisi Penanggung Jawab</th>
                        <th class="px-6 py-3">Posisi Terkini</th>
                        <th class="px-6 py-3">NOJAR / Pelanggan</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($devices as $dev)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-3.5 whitespace-nowrap">
                                <div class="font-mono font-bold text-sm text-slate-900">{{ $dev->registration_number }}</div>
                            </td>
                            <td class="px-6 py-3.5">
                                <div class="font-bold text-slate-900">{{ $dev->name }}</div>
                            </td>
                            <td class="px-6 py-3.5 whitespace-nowrap">
                                @if(!empty($dev->area) && $dev->area !== '-')
                                    <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-blue-50 text-blue-800 border border-blue-200 inline-block font-mono">
                                        {{ $dev->area }}
                                    </span>
                                @else
                                    <span class="text-slate-400 font-medium italic text-xs">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-3.5 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold border {{ $dev->status_badge_class }}">
                                    {{ $dev->stock_status_label }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5">
                                @if($dev->currentTechnician)
                                    <div class="font-bold text-slate-900">{{ $dev->currentTechnician->name }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $dev->currentTechnician->phone ?? '-' }}</div>
                                @else
                                    <span class="text-slate-400 italic">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-3.5 font-medium text-slate-800">
                                {{ $dev->current_location }}
                            </td>
                            <td class="px-6 py-3.5">
                                @if($dev->nojar || $dev->activeWorkOrder)
                                    <span class="font-mono font-bold text-slate-900">{{ $dev->nojar ?: $dev->activeWorkOrder->nojar }}</span>
                                @endif
                                @if($dev->customer_name || ($dev->activeWorkOrder && $dev->activeWorkOrder->customer_name))
                                    <div class="text-[10px] text-slate-500">{{ $dev->customer_name ?: $dev->activeWorkOrder->customer_name }}</div>
                                @endif
                                @if(!$dev->nojar && !$dev->activeWorkOrder && !$dev->customer_name)
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-3.5 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    @if(in_array($dev->stock_status, ['tersedia_pt', 'tersedia_hb_dikta']))
                                        <button type="button" 
                                                @click="openAllocateModal('{{ $dev->id }}', '{{ $dev->registration_number }}', '{{ addslashes($dev->name) }}', '{{ $dev->nojar }}', '{{ $dev->activity }}', '{{ addslashes($dev->customer_name ?: ($dev->activeWorkOrder?->customer_name ?: '')) }}', '{{ addslashes($dev->address ?: ($dev->activeWorkOrder?->location_address ?: '')) }}', '{{ $dev->area }}')"
                                                class="px-2.5 py-1 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-xs transition-colors inline-flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                            <span>Alokasikan</span>
                                        </button>
                                    @endif
                                    <a href="{{ route('admin.devices.show', $dev) }}" class="font-bold text-blue-600 hover:underline">
                                        Detail &rarr;
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-slate-400">
                                @if(request('search'))
                                    <p class="font-medium text-slate-600 text-xs">Tidak ditemukan perangkat dengan No. Registrasi / NOJAR "<span class="font-bold text-slate-900">{{ request('search') }}</span>"</p>
                                    <a href="{{ route('admin.dashboard', ['status' => $statusFilter]) }}" class="mt-2 inline-block text-xs font-bold text-blue-600 hover:text-blue-800 underline">
                                        Reset Pencarian
                                    </a>
                                @else
                                    Tidak ada perangkat dengan status ini saat ini.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Alokasi Stok HB Dikta ke Teknisi -->
    <div x-show="allocateModalOpen" x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="allocateModalOpen = false" 
             class="bg-white rounded-2xl shadow-xl border border-slate-200 w-full max-w-md overflow-hidden">
            
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-emerald-50/50">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Alokasikan Ke Tim Teknisi</h3>
                    </div>
                </div>
                <button type="button" @click="allocateModalOpen = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
            </div>

            <form :action="allocateActionUrl" method="POST" class="p-6 space-y-4">
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
                    <label for="modal_technician_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Pilih Teknisi Penerima <span class="text-rose-500">*</span>
                    </label>
                    <select name="technician_id" id="modal_technician_id" required 
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
                        <label for="modal_nojar" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            NOJAR (Nomor Jaringan)
                        </label>
                        <input type="text" name="nojar" id="modal_nojar" x-model="selectedNojar"
                               class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white font-mono font-bold uppercase text-slate-800">
                    </div>
                    <div>
                        <label for="modal_customer" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Pelanggan
                        </label>
                        <input type="text" name="customer_name" id="modal_customer" x-model="selectedCustomer"
                               class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white font-medium text-slate-800">
                    </div>
                </div>

                <!-- Alamat Lokasi Aktual -->
                <div>
                    <label for="modal_address" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Alamat Lokasi Aktual
                    </label>
                    <textarea name="address" id="modal_address" rows="2" x-model="selectedAddress" 
                              class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white text-slate-800 font-medium transition-all"></textarea>
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
                        <label for="modal_area" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Area
                        </label>
                        <select name="area" id="modal_area" x-model="selectedArea" class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white text-slate-800 font-semibold">
                            <option value="">-- Pilih Area --</option>
                            @foreach(['BWA', 'BDA', 'SEOA', 'SAST', 'CDA', 'NEA'] as $a)
                                <option value="{{ $a }}">{{ $a }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Keterangan -->
                <div>
                    <label for="modal_notes" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Keterangan
                    </label>
                    <textarea name="notes" id="modal_notes" rows="2"
                              class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
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

@push('scripts')
<script>
function dashboardAllocationApp() {
    return { 
        openMetrics: true,
        allocateModalOpen: false,
        selectedDeviceName: '',
        selectedDeviceReg: '',
        selectedNojar: '',
        selectedCustomer: '',
        selectedAddress: '',
        selectedActivity: 'PSB',
        selectedArea: '',
        allocateActionUrl: '',
        openAllocateModal(id, reg, name, nojar, activity, customer, address, area) {
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
