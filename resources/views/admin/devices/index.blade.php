@extends('layouts.app')

@section('title', 'Kelola Data Perangkat')
@section('page_title', 'Master Data Perangkat')
@section('page_subtitle', 'Daftar seluruh perangkat jaringan, status ketersediaan, dan posisi terkini')

@section('content')
<div class="space-y-5"
     x-data="deviceIndexApp({
         initialQuery: @json(request('search', '')),
         stockStatus: @json(request('stock_status', '')),
         area: @json(request('area', '')),
         devices: {!! json_encode($devicesData ?? []) !!}
     })">

    <!-- Top Action Bar & Filter -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Pencarian & Penyaringan Perangkat</h2>
                <p class="text-xs text-slate-500">Cari berdasarkan Nomor Registrasi, NOJAR, Nama Perangkat, atau Area</p>
            </div>
            <div class="flex items-center gap-2 shrink-0 flex-wrap">
                <button type="button" onclick="openImportExcelModal()" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-emerald-800 bg-emerald-50 hover:bg-emerald-100 border border-emerald-300 rounded-xl shadow-xs transition-colors cursor-pointer">
                    <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
                    <span>Import Excel / CSV</span>
                </button>
                <a href="{{ route('admin.devices.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-white bg-brand-600 hover:bg-brand-700 rounded-xl shadow-xs transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Tambah Perangkat Baru</span>
                </a>
            </div>
        </div>

        <!-- Filter Form -->
        <form x-ref="filterForm" 
              action="{{ route('admin.devices.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 pt-2">
            
            <div class="lg:col-span-2 relative">
                <div class="relative">
                    <input type="text" 
                           name="search" 
                           x-model="query"
                           @input="onInput()"
                           @focus="if((query || '').trim().length >= 1) { isOpen = true; if(suggestions.length === 0) onInput(); }"
                           @keydown.arrow-down.prevent="onArrowDown()"
                           @keydown.arrow-up.prevent="onArrowUp()"
                           @keydown.enter="onEnter($event)"
                           @keydown.escape="close()"
                           placeholder="Cari No. Registrasi, NOJAR, Nama Perangkat, atau Area..."
                           autocomplete="off"
                           class="w-full pl-9 pr-14 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all font-medium text-slate-900 placeholder:text-slate-400">
                    
                    <!-- Search Icon Left -->
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>

                    <!-- Clear / Loading Spinner Right -->
                    <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center gap-1">
                        <span x-show="isLoading" class="inline-block animate-spin text-brand-600 text-xs" x-cloak>
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        </span>
                        <button type="button" 
                                x-show="query" 
                                @click="clearSearch()" 
                                class="text-slate-400 hover:text-slate-700 p-1 rounded-md text-xs transition-colors"
                                title="Hapus pencarian" x-cloak>
                            ✕
                        </button>
                    </div>
                </div>

                <!-- Autocomplete Dropdown Popover (Muncul saat mengetik 1 huruf atau lebih) -->
                <div x-show="isOpen && (query || '').trim().length >= 1" 
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 translate-y-1"
                     @click.outside="close()"
                     class="absolute left-0 right-0 top-full mt-1.5 z-50 bg-white rounded-2xl border border-slate-200 shadow-xl overflow-hidden"
                     x-cloak>
                    
                    <!-- Header status -->
                    <div class="px-3.5 py-2 bg-slate-50 border-b border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                        <div class="flex items-center gap-1.5 font-semibold">
                            <span x-show="isLoading" class="text-brand-600">Mencari pilihan perangkat...</span>
                            <span x-show="!isLoading">
                                <span class="text-emerald-700 font-bold" x-text="suggestions.length"></span> pilihan perangkat ditemukan:
                            </span>
                        </div>
                        <span class="text-[10px] text-slate-400">Klik unit untuk memilih / Enter</span>
                    </div>

                    <!-- Suggestions List -->
                    <div class="max-h-80 overflow-y-auto divide-y divide-slate-100">
                        <template x-for="(item, idx) in suggestions" :key="item.id">
                            <div :id="'suggestion-item-' + idx"
                                 @click="selectSuggestion(item)"
                                 @mouseenter="selectedIndex = idx"
                                 class="px-4 py-2.5 cursor-pointer transition-colors flex items-center justify-between gap-3 text-left group"
                                 :class="selectedIndex === idx ? 'bg-brand-50/80 text-brand-900' : 'hover:bg-slate-50/90 text-slate-700'">
                                
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-1.5 flex-wrap mb-1">
                                        <!-- No Registrasi -->
                                        <span class="font-mono font-bold text-xs text-brand-700 bg-brand-50 px-2 py-0.5 rounded border border-brand-200"
                                              x-text="item.registration_number">
                                        </span>

                                        <!-- Area Badge -->
                                        <template x-if="item.area">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200 font-mono"
                                                  x-text="'Area ' + item.area">
                                            </span>
                                        </template>

                                        <!-- Nojar if available -->
                                        <template x-if="item.nojar">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200 font-mono"
                                                  x-text="'NOJAR: ' + item.nojar">
                                            </span>
                                        </template>

                                        <!-- Status Badge -->
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold border"
                                              :class="item.status_badge_class"
                                              x-text="item.stock_status_label">
                                        </span>
                                    </div>

                                    <!-- Device Name -->
                                    <div class="text-xs font-bold text-slate-900 group-hover:text-brand-600 transition-colors" x-text="item.name"></div>

                                    <!-- Position / Holder -->
                                    <div class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-1.5 truncate">
                                        <span class="text-slate-400">Posisi:</span>
                                        <strong class="text-slate-700 font-medium" x-text="item.holder || item.current_location"></strong>
                                    </div>
                                </div>

                                <!-- Action Button Pill -->
                                <div class="shrink-0 flex items-center gap-1.5">
                                    <span class="text-[11px] font-bold text-brand-600 bg-brand-50 group-hover:bg-brand-600 group-hover:text-white px-2.5 py-1 rounded-lg border border-brand-200 transition-all flex items-center gap-1">
                                        <span>Pilih</span>
                                        <span>&rarr;</span>
                                    </span>
                                </div>
                            </div>
                        </template>

                        <!-- Empty state when no matches -->
                        <div x-show="!isLoading && suggestions.length === 0" class="p-6 text-center text-xs text-slate-500">
                            <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2 text-sm font-bold">✕</div>
                            <p class="font-bold text-slate-700">Tidak ada perangkat yang cocok dengan "<span class="text-slate-900" x-text="query"></span>"</p>
                            <p class="text-[11px] text-slate-400 mt-1">Pencarian dapat menggunakan No. Registrasi, NOJAR, Nama Perangkat, atau Area</p>
                        </div>
                    </div>

                    <!-- Dropdown Footer -->
                    <div class="px-4 py-2 bg-slate-50 border-t border-slate-100 text-[11px] text-slate-500 flex items-center justify-between">
                        <span>Tekan <strong>Enter</strong> untuk melihat seluruh hasil di tabel</span>
                        <button type="submit" class="font-bold text-brand-600 hover:text-brand-800 text-xs">
                            Cari di Tabel &rarr;
                        </button>
                    </div>
                </div>
            </div>

            <div>
                <select name="stock_status" 
                        x-model="stockStatus"
                        @change="onFilterChange()"
                        class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
                    <option value="">Semua Status Stok</option>
                    <option value="tersedia_hb_jkt" {{ in_array(request('stock_status'), ['tersedia_hb_jkt', 'tersedia_pt', 'tersedia_hb_dikta']) ? 'selected' : '' }}>Tersedia di HB JKT</option>
                    <option value="tersedia_hb_ckr" {{ request('stock_status') === 'tersedia_hb_ckr' ? 'selected' : '' }}>Tersedia di HB CKR</option>
                    <option value="dibawa_teknisi" {{ request('stock_status') == 'dibawa_teknisi' ? 'selected' : '' }}>Dibawa Teknisi</option>
                    <option value="terpasang_pelanggan" {{ request('stock_status') == 'terpasang_pelanggan' ? 'selected' : '' }}>Terinstal</option>
                    <option value="pending" {{ request('stock_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="rusak" {{ request('stock_status') == 'rusak' ? 'selected' : '' }}>Rusak</option>
                    <option value="hilang" {{ request('stock_status') == 'hilang' ? 'selected' : '' }}>Hilang</option>
                    <option value="dismantle" {{ request('stock_status') == 'dismantle' ? 'selected' : '' }}>Dismantle (Bongkaran)</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <select name="area" 
                        x-model="area"
                        @change="onFilterChange()"
                        class="flex-1 px-3.5 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
                    <option value="">Semua Area</option>
                    @foreach(['BWA', 'BDA', 'SEOA', 'SAST', 'CDA', 'NEA'] as $a)
                        <option value="{{ $a }}" {{ request('area') === $a ? 'selected' : '' }}>{{ $a }}</option>
                    @endforeach
                </select>
                <button type="submit" class="py-2 px-3.5 text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 rounded-xl transition-colors shrink-0">
                    Filter Data
                </button>
                @if(request()->hasAny(['search', 'stock_status', 'condition', 'area']))
                    <a href="{{ route('admin.devices.index') }}" class="p-2 text-slate-500 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors shrink-0" title="Reset filter">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                @endif
            </div>
        </form>

        <!-- Live Search Counter on Current Page -->
        <div x-show="query" class="flex items-center gap-1.5 text-xs text-brand-800 bg-brand-50 px-3.5 py-2 rounded-xl border border-brand-200" x-cloak>
            <span>Menampilkan di halaman ini:</span>
            <strong x-text="matchCount"></strong>
            <span>dari {{ $devices->count() }} unit</span>
            <span class="text-slate-400 mx-1">&bull;</span>
            <span class="text-[11px] text-slate-600">Tekan Enter atau klik Filter Data untuk mencari "<span x-text="query"></span>" di seluruh database ({{ $devices->total() }} unit)</span>
        </div>
    </div>

    <!-- Table of Devices -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">Nomor Registrasi</th>
                        <th class="px-6 py-3.5">Nama Perangkat</th>
                        <th class="px-6 py-3.5">Area</th>
                        <th class="px-6 py-3.5">Status Stok</th>
                        <th class="px-6 py-3.5">Posisi / Pemegang Terkini</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($devices as $device)
                        <tr id="device-row-{{ $device->id }}" x-show="matchesSearch({{ $device->id }})" class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-mono font-bold text-sm text-slate-900">{{ $device->registration_number }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900">{{ $device->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($device->area)
                                    <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-blue-50 text-blue-800 border border-blue-200 inline-block font-mono">
                                        {{ $device->area }}
                                    </span>
                                @else
                                    <span class="text-slate-400 font-medium italic text-xs">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold border {{ $device->status_badge_class }}">
                                    {{ $device->stock_status_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        @if($device->stock_status === 'dibawa_teknisi' && $device->currentTechnician)
                                            <div class="flex items-center gap-2">
                                                <div class="w-5 h-5 rounded-full bg-amber-200 text-amber-900 font-bold text-[10px] flex items-center justify-center shrink-0">
                                                    {{ substr($device->currentTechnician->name, 0, 1) }}
                                                </div>
                                                <div class="truncate">
                                                    <span class="font-semibold text-slate-900">{{ $device->currentTechnician->name }}</span>
                                                    <div class="text-[10px] text-slate-500">{{ $device->current_location }}</div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="text-slate-800 font-medium truncate">{{ $device->current_location }}</div>
                                        @endif
                                    </div>
                                    <button type="button" 
                                            onclick="openReturnDeviceModal('{{ $device->id }}', '{{ $device->registration_number }}', '{{ addslashes($device->name) }}', '{{ $device->area ?: '-' }}')"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[11px] font-bold text-amber-700 bg-amber-50 hover:bg-amber-100 hover:text-amber-800 border border-amber-200 rounded-lg shadow-2xs transition-colors shrink-0 cursor-pointer"
                                            title="Kembalikan ke Lintasarta (Otomatis Dikeluarkan)">
                                        <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                        <span>Kembalikan</span>
                                    </button>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('admin.devices.show', $device) }}" 
                                       class="p-1.5 text-slate-600 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition-colors" title="Lihat Detail & Riwayat">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <button type="button" 
                                            onclick="openDeviceEvidenceModal('{{ $device->id }}', '{{ $device->registration_number }}', '{{ addslashes($device->name) }}')" 
                                            class="p-1.5 text-slate-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors relative" 
                                            title="Bukti Foto / Evidence">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        @if(($device->evidence_count ?? 0) > 0)
                                            <span class="absolute -top-1 -right-1 flex h-3.5 w-3.5 items-center justify-center rounded-full bg-emerald-500 text-[8px] font-bold text-white shadow-xs">
                                                {{ $device->evidence_count }}
                                            </span>
                                        @endif
                                    </button>
                                    <form action="{{ route('admin.devices.destroy', $device) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus perangkat {{ $device->registration_number }}?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-slate-600 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Hapus Perangkat">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-slate-400">
                                <p class="text-sm">Tidak ditemukan data perangkat yang sesuai dengan kriteria pencarian.</p>
                            </td>
                        </tr>
                    @endforelse

                    <!-- Empty state row untuk live search client-side pada halaman ini -->
                    <tr x-show="isSearchEmpty" x-cloak>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                            <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2 text-base font-bold">🔍</div>
                            <p class="font-bold text-slate-700 text-xs">Tidak ada perangkat di halaman ini yang cocok dengan "<span class="text-slate-900" x-text="query"></span>"</p>
                            <p class="text-[11px] text-slate-400 mt-1">Gunakan tombol di bawah atau tekan Enter untuk mencari di seluruh database</p>
                            <button type="button" @click="$refs.filterForm.submit()" class="mt-3 px-3.5 py-1.5 text-xs font-bold text-white bg-brand-600 hover:bg-brand-700 rounded-xl transition-colors">
                                Cari di Seluruh Database &rarr;
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        @if($devices->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $devices->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Modal Bukti Foto / Evidence Perangkat -->
<div id="deviceEvidenceModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" onclick="closeDeviceEvidenceModal()"></div>

    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-4xl border border-slate-200">
            <!-- Modal Header -->
            <div class="bg-slate-900 px-6 py-4 flex items-center justify-between text-white">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-emerald-500/20 border border-emerald-400/30 flex items-center justify-center text-emerald-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold" id="modalDeviceReg">Bukti Foto / Evidence</h3>
                        <p class="text-xs text-slate-300" id="modalDeviceName">Memuat data perangkat...</p>
                    </div>
                </div>
                <button type="button" onclick="closeDeviceEvidenceModal()" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 max-h-[75vh] overflow-y-auto bg-slate-50/50">
                <!-- Loading State -->
                <div id="evidenceLoadingState" class="py-12 text-center text-slate-500">
                    <svg class="w-8 h-8 mx-auto animate-spin text-emerald-600 mb-3" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <p class="text-xs font-semibold">Memuat bukti foto pekerjaan...</p>
                </div>

                <!-- Empty State -->
                <div id="evidenceEmptyState" class="hidden py-12 text-center text-slate-400">
                    <div class="w-14 h-14 mx-auto rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <p class="text-sm font-bold text-slate-700">Belum Ada Bukti Foto</p>
                    <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Teknisi belum mengunggah foto evidence (pengambilan, tiba lokasi, pemasangan, atau kendala) untuk perangkat ini.</p>
                </div>

                <!-- Gallery Grid -->
                <div id="evidenceGalleryGrid" class="hidden grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Dynamic cards injected via JS -->
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="bg-white px-6 py-3.5 border-t border-slate-200 flex items-center justify-between">
                <span class="text-xs text-slate-500" id="evidenceCountLabel">0 foto ditemukan</span>
                <div class="flex items-center gap-2">
                    <a id="modalFullPageLink" href="#" class="px-3.5 py-2 text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-xl transition-colors">
                        Buka Halaman Lengkap &rarr;
                    </a>
                    <button type="button" onclick="closeDeviceEvidenceModal()" class="px-4 py-2 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Pengembalian Unit ke Lintasarta -->
<div id="returnDeviceModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" onclick="closeReturnDeviceModal()"></div>

    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-slate-200">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-amber-600 to-amber-700 px-6 py-4 flex items-center justify-between text-white">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white">Kembalikan ke Lintasarta</h3>
                        <p class="text-[11px] text-amber-100">Perangkat otomatis dikeluarkan dari sistem</p>
                    </div>
                </div>
                <button type="button" onclick="closeReturnDeviceModal()" class="text-white/80 hover:text-white p-1 rounded-lg hover:bg-amber-800/40 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Modal Form -->
            <form id="returnDeviceForm" method="POST" action="{{ route('admin.devices.process-return') }}" onsubmit="return confirm('Konfirmasi: Kembalikan perangkat ini ke Lintasarta? Data perangkat akan otomatis dikeluarkan dan hilang dari Master Data.');" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="device_id" id="returnModalDeviceId" value="">

                <!-- Info Perangkat -->
                <div class="p-3.5 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-950 leading-relaxed space-y-1">
                    <p class="font-semibold text-amber-900">Perangkat yang Dipilih:</p>
                    <div class="font-medium text-slate-800">
                        <span id="returnModalReg" class="font-mono font-bold text-brand-700"></span> &bull; <span id="returnModalName" class="font-semibold"></span>
                    </div>
                    <p class="text-[11px] text-amber-800 pt-1">
                        Unit akan dicatat pengembaliannya ke Lintasarta dan <strong>otomatis data perangkatnya hilang</strong> dari Master Data.
                    </p>
                </div>

                <!-- Tujuan Pengembalian Lintasarta -->
                <div>
                    <label for="return_destination" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Tujuan Pengembalian ke Lintasarta <span class="text-rose-500">*</span>
                    </label>
                    <select name="destination" id="return_destination" required class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all font-semibold text-slate-800">
                        <option value="">-- Pilih Lokasi / Area Tujuan --</option>
                        <option value="Gudang Tekno">Gudang Tekno (Gudang Utama)</option>
                        <option value="BWA">Area BWA</option>
                        <option value="BDA">Area BDA</option>
                        <option value="SEOA">Area SEOA</option>
                        <option value="SAST">Area SAST</option>
                        <option value="CDA">Area CDA</option>
                        <option value="NEA">Area NEA</option>
                    </select>
                </div>

                <!-- Catatan Pengembalian -->
                <div>
                    <label for="return_notes" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Catatan Pengembalian (Opsional)
                    </label>
                    <textarea name="notes" id="return_notes" rows="2" placeholder="Catatan atau nomor surat jalan pengembalian..."
                              class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all"></textarea>
                </div>

                <!-- Footer Buttons -->
                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeReturnDeviceModal()" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-xl shadow-xs transition-colors flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Submit & Kembalikan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('admin.devices.partials.import_modal')

<script>
    function openReturnDeviceModal(deviceId, regNumber, deviceName, area) {
        document.getElementById('returnModalDeviceId').value = deviceId;
        document.getElementById('returnModalReg').textContent = regNumber;
        document.getElementById('returnModalName').textContent = deviceName;

        const destSelect = document.getElementById('return_destination');
        if (area && area !== '-' && ['BWA', 'BDA', 'SEOA', 'SAST', 'CDA', 'NEA'].includes(area)) {
            destSelect.value = area;
        } else {
            destSelect.value = 'Gudang Tekno';
        }
        document.getElementById('return_notes').value = '';

        const modal = document.getElementById('returnDeviceModal');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeReturnDeviceModal() {
        const modal = document.getElementById('returnDeviceModal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }
    }

    function openDeviceEvidenceModal(deviceId, regNumber, deviceName) {
        const modal = document.getElementById('deviceEvidenceModal');
        const titleEl = document.getElementById('modalDeviceReg');
        const descEl = document.getElementById('modalDeviceName');
        const loadingEl = document.getElementById('evidenceLoadingState');
        const emptyEl = document.getElementById('evidenceEmptyState');
        const gridEl = document.getElementById('evidenceGalleryGrid');
        const countEl = document.getElementById('evidenceCountLabel');
        const fullPageLink = document.getElementById('modalFullPageLink');

        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        titleEl.textContent = `Bukti Foto / Evidence (${regNumber})`;
        descEl.textContent = deviceName;
        fullPageLink.href = `/admin/devices/${deviceId}/evidence`;

        loadingEl.classList.remove('hidden');
        emptyEl.classList.add('hidden');
        gridEl.classList.add('hidden');
        gridEl.innerHTML = '';
        countEl.textContent = 'Memuat...';

        fetch(`/admin/devices/${deviceId}/evidence`, {
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            loadingEl.classList.add('hidden');
            const items = (data && data.evidence) ? data.evidence : [];
            countEl.textContent = `${items.length} foto ditemukan`;

            if (items.length === 0) {
                emptyEl.classList.remove('hidden');
                return;
            }

            gridEl.classList.remove('hidden');
            gridEl.innerHTML = items.map(item => `
                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-2xs flex flex-col justify-between">
                    <div>
                        <div class="h-44 bg-slate-900 relative group overflow-hidden flex items-center justify-center">
                            <img src="${item.photo_url}" 
                                 alt="${item.photo_type_label}" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                 onerror="this.onerror=null; this.src='https://placehold.co/600x400/0f172a/cbd5e1?text=Foto+Evidence';">
                            <a href="${item.photo_url}" target="_blank" 
                               class="absolute bottom-2 right-2 bg-slate-900/80 hover:bg-slate-900 text-white p-1.5 rounded-lg text-xs opacity-0 group-hover:opacity-100 transition-opacity" title="Buka Ukuran Penuh">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                        </div>
                        <div class="p-3.5">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 inline-block mb-1.5">
                                ${item.photo_type_label}
                            </span>
                            <p class="text-xs font-semibold text-slate-800 line-clamp-2">${item.caption}</p>
                            <div class="mt-2.5 pt-2.5 border-t border-slate-100 text-[11px] space-y-1 text-slate-500">
                                <div class="flex justify-between">
                                    <span class="text-slate-400">Teknisi:</span>
                                    <span class="font-medium text-slate-700">${item.technician_name}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-400">NOJAR:</span>
                                    <span class="font-mono font-bold text-brand-700">${item.nojar}</span>
                                </div>
                                ${item.customer_name && item.customer_name !== '-' ? `
                                <div class="flex justify-between">
                                    <span class="text-slate-400">Pelanggan:</span>
                                    <span class="font-medium text-slate-700">${item.customer_name}</span>
                                </div>` : ''}
                            </div>
                        </div>
                    </div>
                    <div class="px-3.5 py-2 bg-slate-50 border-t border-slate-100 text-[10px] text-slate-400 flex justify-between items-center font-mono">
                        <span>${item.created_at_formatted}</span>
                        <span>${item.created_at_human}</span>
                    </div>
                </div>
            `).join('');
        })
        .catch(err => {
            loadingEl.classList.add('hidden');
            emptyEl.classList.remove('hidden');
            emptyEl.querySelector('p.text-sm').textContent = 'Terjadi Kesalahan';
            emptyEl.querySelector('p.text-xs').textContent = 'Gagal memuat foto evidence. Silakan coba kembali.';
            countEl.textContent = 'Gagal memuat';
        });
    }

    function closeDeviceEvidenceModal() {
        const modal = document.getElementById('deviceEvidenceModal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeDeviceEvidenceModal();
            closeReturnDeviceModal();
        }
    });

    function deviceIndexApp(config = {}) {
        return {
            query: config.initialQuery || '',
            stockStatus: config.stockStatus || '',
            area: config.area || '',
            devicesList: config.devices || [],
            suggestions: [],
            isLoading: false,
            isOpen: false,
            selectedIndex: -1,
            debounceTimeout: null,

            matchesSearch(id) {
                if (!this.query || this.query.trim() === '') return true;
                const q = this.query.toLowerCase().trim();
                const dev = this.devicesList.find(d => d.id === id);
                if (!dev) return true;
                return (
                    (dev.searchStr && dev.searchStr.includes(q)) ||
                    (dev.registration_number && dev.registration_number.toLowerCase().includes(q)) ||
                    (dev.name && dev.name.toLowerCase().includes(q)) ||
                    (dev.nojar && dev.nojar.toLowerCase().includes(q)) ||
                    (dev.area && dev.area.toLowerCase().includes(q)) ||
                    (dev.serial_number && dev.serial_number.toLowerCase().includes(q))
                );
            },

            get matchCount() {
                if (!this.query || this.query.trim() === '') return this.devicesList.length;
                const q = this.query.toLowerCase().trim();
                return this.devicesList.filter(dev => {
                    return (
                        (dev.searchStr && dev.searchStr.includes(q)) ||
                        (dev.registration_number && dev.registration_number.toLowerCase().includes(q)) ||
                        (dev.name && dev.name.toLowerCase().includes(q)) ||
                        (dev.nojar && dev.nojar.toLowerCase().includes(q)) ||
                        (dev.area && dev.area.toLowerCase().includes(q)) ||
                        (dev.serial_number && dev.serial_number.toLowerCase().includes(q))
                    );
                }).length;
            },

            get isSearchEmpty() {
                return this.devicesList.length > 0 && this.query && this.query.trim() !== '' && this.matchCount === 0;
            },

            onInput() {
                clearTimeout(this.debounceTimeout);
                const q = (this.query || '').trim();
                if (q.length < 1) {
                    this.suggestions = [];
                    this.isOpen = false;
                    this.isLoading = false;
                    return;
                }

                this.isLoading = true;
                this.isOpen = true;
                this.selectedIndex = -1;

                this.debounceTimeout = setTimeout(() => {
                    this.fetchSuggestions(q);
                }, 120);
            },

            async fetchSuggestions(q) {
                try {
                    const params = new URLSearchParams({ q: q });
                    if (this.stockStatus) params.append('stock_status', this.stockStatus);
                    if (this.area) params.append('area', this.area);

                    const res = await fetch(`{{ route('admin.devices.search-suggestions') }}?${params.toString()}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    if (!res.ok) throw new Error('Network error');
                    const data = await res.json();
                    
                    if ((this.query || '').trim().toLowerCase() === q.toLowerCase()) {
                        this.suggestions = Array.isArray(data) ? data : [];
                        this.isOpen = true;
                    }
                } catch (err) {
                    console.error('Error fetching suggestions:', err);
                    this.suggestions = [];
                } finally {
                    this.isLoading = false;
                }
            },

            selectSuggestion(suggestion) {
                if (!suggestion) return;
                this.query = suggestion.registration_number;
                this.isOpen = false;
                this.suggestions = [];
                this.$nextTick(() => {
                    if (this.$refs.filterForm) {
                        this.$refs.filterForm.submit();
                    }
                });
            },

            clearSearch() {
                this.query = '';
                this.suggestions = [];
                this.isOpen = false;
                if (window.location.search.includes('search=')) {
                    const url = new URL(window.location.href);
                    url.searchParams.delete('search');
                    url.searchParams.delete('page');
                    window.location.href = url.toString();
                }
            },

            onFilterChange() {
                if (this.$refs.filterForm) {
                    this.$refs.filterForm.submit();
                }
            },

            onArrowDown() {
                if (!this.isOpen || this.suggestions.length === 0) return;
                if (this.selectedIndex < this.suggestions.length - 1) {
                    this.selectedIndex++;
                } else {
                    this.selectedIndex = 0;
                }
                this.scrollToSelected();
            },

            onArrowUp() {
                if (!this.isOpen || this.suggestions.length === 0) return;
                if (this.selectedIndex > 0) {
                    this.selectedIndex--;
                } else {
                    this.selectedIndex = this.suggestions.length - 1;
                }
                this.scrollToSelected();
            },

            scrollToSelected() {
                this.$nextTick(() => {
                    const el = document.getElementById(`suggestion-item-${this.selectedIndex}`);
                    if (el) el.scrollIntoView({ block: 'nearest' });
                });
            },

            onEnter(event) {
                if (this.isOpen && this.selectedIndex >= 0 && this.suggestions[this.selectedIndex]) {
                    event.preventDefault();
                    this.selectSuggestion(this.suggestions[this.selectedIndex]);
                } else {
                    this.isOpen = false;
                }
            },

            close() {
                this.isOpen = false;
                this.selectedIndex = -1;
            }
        };
    }
</script>
@endsection
