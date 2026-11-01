@extends('layouts.app')

@section('title', 'Detail Perangkat ' . $device->registration_number)
@section('page_title', 'Detail Perangkat')
@section('page_subtitle', 'Informasi lengkap perangkat, status pelacakan fisik, dan riwayat perpindahan')

@section('content')
<div class="space-y-6" x-data="{ allocateModalOpen: false, returnModalOpen: false }">

    <!-- Top Card: Header Perangkat -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6">
        <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-6">
            
            <!-- Info Utama Perangkat -->
            <div class="flex-1 space-y-4">
                <div class="flex flex-wrap items-center gap-3">
                    <h2 class="text-2xl font-black text-slate-900 tracking-tight">{{ $device->name }}</h2>
                    <span class="px-3 py-1 rounded-full text-xs font-bold border shadow-2xs {{ $device->status_badge_class }}">
                        {{ $device->stock_status_label }}
                    </span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5 p-4 rounded-xl bg-slate-50 border border-slate-100">
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">No. Registrasi</span>
                        <span class="font-mono font-bold text-slate-900 text-sm mt-0.5 inline-block">{{ $device->registration_number }}</span>
                    </div>
                    @if($device->nojar)
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">NOJAR</span>
                        <span class="font-mono font-bold text-indigo-700 text-sm mt-0.5 inline-block">{{ $device->nojar }}</span>
                    </div>
                    @endif
                    @if($device->customer_name)
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Pelanggan</span>
                        <span class="font-bold text-slate-900 text-sm mt-0.5 inline-block">{{ $device->customer_name }}</span>
                    </div>
                    @endif
                    @if($device->activity)
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Aktivitas</span>
                        <span class="font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200 text-xs mt-0.5 inline-block">{{ $device->activity }}</span>
                    </div>
                    @endif
                    @if($device->area)
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Area</span>
                        <span class="font-bold text-blue-800 bg-blue-50 px-2.5 py-0.5 rounded border border-blue-200 text-xs mt-0.5 inline-block font-mono">
                            {{ $device->area }}
                        </span>
                    </div>
                    @endif
                    <div class="col-span-2 sm:col-span-3 lg:col-span-5 pt-2.5 border-t border-slate-200/60">
                        <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Alamat</span>
                        <span class="font-semibold text-slate-800 text-xs mt-0.5 inline-block">{{ $device->address ?: '-' }}</span>
                    </div>
                </div>

                <div class="flex items-center gap-2 text-xs text-slate-700 bg-slate-50/80 px-3.5 py-2 rounded-xl border border-slate-100">
                    <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Posisi Terkini: <strong class="text-slate-900">{{ $device->current_location }}</strong></span>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-wrap lg:flex-col items-stretch gap-2.5 shrink-0 min-w-[200px]">
                @if(in_array($device->stock_status, ['tersedia_pt', 'tersedia_hb_dikta']))
                    <button type="button" @click="allocateModalOpen = true" 
                            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span>Alokasikan ke Teknisi</span>
                    </button>
                @endif
                <button type="button" @click="returnModalOpen = true"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-xl shadow-xs transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                    <span>Kembalikan ke Lintasarta</span>
                </button>
                <a href="{{ route('admin.devices.edit', $device) }}" 
                   class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-xs font-semibold text-white bg-brand-600 hover:bg-brand-700 rounded-xl shadow-xs transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Edit Data</span>
                </a>
            </div>

        </div>
    </div>

    <!-- Details Grid: Status, Teknisi, Work Order -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        
        <!-- Pemegang / Teknisi -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block mb-3">Teknisi Penanggung Jawab</span>
            @if($device->currentTechnician)
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-900 font-bold flex items-center justify-center shrink-0 border border-amber-200">
                        {{ substr($device->currentTechnician->name, 0, 1) }}
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-slate-900">{{ $device->currentTechnician->name }}</h4>
                        <p class="text-xs text-slate-500">No. HP: {{ $device->currentTechnician->phone }}</p>
                        <span class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                            Membawa Perangkat
                        </span>
                    </div>
                </div>
            @else
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-center text-xs text-slate-500">
                    Perangkat saat ini tidak sedang dipegang oleh teknisi (ada di gudang atau terpasang).
                </div>
            @endif
        </div>

        <!-- Pekerjaan Terkait -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block mb-3">Tiket Pekerjaan Terkait</span>
            @if($device->activeWorkOrder)
                <div>
                    <span class="px-2.5 py-0.5 rounded-md font-mono text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                        {{ $device->activeWorkOrder->nojar }}
                    </span>
                    <h4 class="text-xs font-bold text-slate-900 mt-2">{{ $device->activeWorkOrder->title }}</h4>
                    <p class="text-[11px] text-slate-500 mt-1">Status: <strong>{{ $device->activeWorkOrder->status_label }}</strong></p>
                </div>
            @else
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-center text-xs text-slate-500">
                    Tidak ada penugasan pekerjaan aktif saat ini.
                </div>
            @endif
        </div>

        <!-- Informasi Registrasi & Catatan -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block mb-3">Informasi Registrasi</span>
            <div class="space-y-1.5 text-xs">
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-500">Tanggal Teknisi Terima</span>
                    <span class="font-medium text-slate-800">
                        @php
                            $techHistory = $device->histories->whereNotNull('technician_id')->first();
                            $tglTerima = $techHistory ? $techHistory->created_at->format('d M Y') : ($device->current_technician_id ? $device->updated_at->format('d M Y') : '-');
                        @endphp
                        {{ $tglTerima }}
                    </span>
                </div>
                <div class="py-1 border-b border-slate-100">
                    <span class="text-slate-500 block text-[11px]">Alamat:</span>
                    <span class="font-medium text-slate-800">{{ $device->address ?: '-' }}</span>
                </div>
                <div class="pt-1">
                    <span class="text-slate-500 block text-[11px]">Catatan:</span>
                    <p class="text-slate-700 italic text-xs mt-0.5">{{ $device->notes ?: '-' }}</p>
                </div>
            </div>
        </div>

    </div>

    <!-- Complete Audit Trail (Riwayat Perpindahan Perangkat) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Riwayat Perpindahan & Pelacakan (Audit Trail)</h3>
                <p class="text-xs text-slate-500">Rekam jejak setiap perpindahan tangan, lokasi, dan status pekerjaan</p>
            </div>
            <span class="text-xs text-slate-400 font-medium">Total: {{ $device->histories->count() }} Aktivitas</span>
        </div>

        <div class="p-6">
            <div class="flow-root">
                <ul class="-mb-8">
                    @forelse($device->histories as $idx => $history)
                        <li>
                            <div class="relative pb-8">
                                @if(!$loop->last)
                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-slate-200" aria-hidden="true"></span>
                                @endif
                                <div class="relative flex space-x-3">
                                    <div>
                                        <span class="h-8 w-8 rounded-full bg-slate-100 border border-slate-300 flex items-center justify-center ring-8 ring-white text-slate-700 font-bold text-xs">
                                            {{ $loop->iteration }}
                                        </span>
                                    </div>
                                    <div class="flex-1 min-w-0 flex justify-between space-x-4 pt-1">
                                        <div>
                                            <p class="text-xs font-bold text-slate-900">
                                                {{ $history->action }}
                                            </p>
                                            
                                            <div class="mt-1 flex flex-wrap items-center gap-2 text-xs">
                                                @if($history->actor)
                                                    <span class="text-slate-500">Oleh: <strong>{{ $history->actor->name }}</strong> ({{ ucfirst($history->actor->role) }})</span>
                                                @endif
                                                @if($history->technician)
                                                    <span class="text-amber-800 bg-amber-50 px-2 py-0.5 rounded border border-amber-200 font-medium">
                                                        Teknisi: {{ $history->technician->name }}
                                                    </span>
                                                @endif
                                                @if($history->workOrder)
                                                    <span class="font-mono text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-200 font-bold">
                                                        {{ $history->workOrder->nojar }}
                                                    </span>
                                                @endif
                                            </div>

                                            <p class="text-xs text-slate-600 mt-1 font-mono">
                                                <span class="text-slate-400">Dari:</span> {{ $history->from_location ?: 'Gudang' }} 
                                                &rarr; 
                                                <span class="text-slate-400">Ke:</span> <strong class="text-slate-800">{{ $history->to_location }}</strong>
                                            </p>

                                            @if($history->notes)
                                                <p class="text-[11px] text-slate-500 mt-1 italic bg-slate-50 p-2 rounded-lg border border-slate-100">
                                                    {{ $history->notes }}
                                                </p>
                                            @endif
                                        </div>

                                        <div class="text-right text-[11px] whitespace-nowrap text-slate-400">
                                            <div>{{ $history->created_at->format('d/m/Y H:i') }}</div>
                                            <div class="text-[10px] text-slate-400">{{ $history->created_at->diffForHumans() }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @empty
                        <li class="text-center py-6 text-slate-400 text-xs">
                            Belum ada riwayat tercatat untuk perangkat ini.
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <!-- Modal Alokasi Stok HB Dikta ke Teknisi -->
    <div x-show="allocateModalOpen" x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="allocateModalOpen = false" 
             class="bg-white rounded-2xl shadow-xl border border-slate-200 w-full max-w-md overflow-hidden animate-in fade-in zoom-in-95 duration-150">
            
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

            <form action="{{ route('admin.devices.allocate', $device) }}" method="POST" class="p-6 space-y-4">
                @csrf
                
                <!-- Info Perangkat (Dipisah Kolom) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Nama Perangkat</label>
                        <div class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl font-bold text-slate-900">{{ $device->name }}</div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">No. Registrasi</label>
                        <div class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl font-mono font-bold text-slate-900">{{ $device->registration_number }}</div>
                    </div>
                </div>

                <!-- Pilih Teknisi -->
                <div>
                    <label for="show_modal_technician_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Pilih Teknisi Penerima <span class="text-rose-500">*</span>
                    </label>
                    <select name="technician_id" id="show_modal_technician_id" required 
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
                        <label for="show_modal_nojar" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            NOJAR
                        </label>
                        <input type="text" name="nojar" id="show_modal_nojar" value="{{ old('nojar', $device->nojar) }}"
                               class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white font-mono font-bold uppercase text-slate-800">
                    </div>
                    <div>
                        <label for="show_modal_customer" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Pelanggan
                        </label>
                        <input type="text" name="customer_name" id="show_modal_customer" value="{{ old('customer_name', $device->customer_name) }}"
                               class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white font-medium text-slate-800">
                    </div>
                </div>

                <!-- Alamat -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="show_modal_address" class="inline-flex items-center gap-1.5 text-xs font-black uppercase tracking-wider text-emerald-800 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200 shadow-2xs">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>ALAMAT</span>
                        </label>
                        <span class="text-[10px] text-slate-400">Lokasi / Alamat Pemasangan</span>
                    </div>
                    <textarea name="address" id="show_modal_address" rows="2"
                              class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-emerald-200/80 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white text-slate-800 font-medium transition-all">{{ old('address', $device->address) }}</textarea>
                </div>

                <!-- Aktivitas & Area -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Aktivitas Penugasan
                        </label>
                        <select name="activity" class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white text-slate-800 font-medium">
                            <option value="PSB" {{ $device->activity === 'PSB' ? 'selected' : '' }}>PSB</option>
                            <option value="Troubleshoot" {{ $device->activity === 'Troubleshoot' ? 'selected' : '' }}>Troubleshoot</option>
                            <option value="Simulasi" {{ $device->activity === 'Simulasi' ? 'selected' : '' }}>Simulasi</option>
                            <option value="Spare Dikta" {{ $device->activity === 'Spare Dikta' ? 'selected' : '' }}>Spare Dikta</option>
                        </select>
                    </div>
                    <div>
                        <label for="show_modal_area" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Area
                        </label>
                        <select name="area" id="show_modal_area" class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white text-slate-800 font-semibold">
                            <option value="">-- Pilih Area --</option>
                            @foreach(['BWA', 'BDA', 'SEOA', 'SAST', 'CDA', 'NEA'] as $a)
                                <option value="{{ $a }}" {{ $device->area === $a ? 'selected' : '' }}>{{ $a }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Keterangan -->
                <div>
                    <label for="show_modal_notes" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Keterangan
                    </label>
                    <textarea name="notes" id="show_modal_notes" rows="2"
                              class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                    <button type="button" @click="allocateModalOpen = false" 
                            class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition-colors">
                        Konfirmasi Alokasi
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Kembalikan ke Lintasarta -->
    <div x-show="returnModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="returnModalOpen = false"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-slate-200">
                <!-- Modal Header -->
                <div class="bg-amber-600 px-6 py-4 flex items-center justify-between text-white">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-white">Kembalikan ke Lintasarta</h3>
                            <p class="text-[11px] text-amber-100">Perangkat otomatis dikeluarkan dari sistem</p>
                        </div>
                    </div>
                    <button type="button" @click="returnModalOpen = false" class="text-white/80 hover:text-white text-lg font-bold p-1 rounded-lg">
                        &times;
                    </button>
                </div>

                <!-- Modal Body / Form -->
                <form method="POST" action="{{ route('admin.devices.return-lintasarta', $device) }}" onsubmit="return confirm('Konfirmasi: Kembalikan perangkat ini ke Lintasarta? Data perangkat akan otomatis dikeluarkan dan hilang dari Master Data.');" class="p-6 space-y-4">
                    @csrf
                    <div class="p-3.5 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-950 leading-relaxed space-y-1">
                        <p class="font-semibold text-amber-900">Konfirmasi Pengembalian Perangkat:</p>
                        <p>Unit <strong>{{ $device->name }}</strong> (<strong class="font-mono text-brand-700">{{ $device->registration_number }}</strong>) akan dicatat pengembaliannya ke Lintasarta dan <strong>otomatis data perangkatnya hilang</strong> dari Master Data.</p>
                    </div>

                    <div>
                        <label for="show_return_destination" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Tujuan Pengembalian Lintasarta <span class="text-rose-500">*</span>
                        </label>
                        <select name="destination" id="show_return_destination" required class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all font-semibold text-slate-800">
                            <option value="">-- Pilih Tujuan Pengembalian --</option>
                            <option value="Gudang Tekno">Gudang Tekno</option>
                            <option value="BWA">BWA</option>
                            <option value="BDA">BDA</option>
                            <option value="SEOA">SEOA</option>
                            <option value="SAST">SAST</option>
                            <option value="CDA">CDA</option>
                            <option value="NEA">NEA</option>
                        </select>
                    </div>

                    <div>
                        <label for="show_return_notes" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Catatan Pengembalian (Opsional)
                        </label>
                        <textarea name="notes" id="show_return_notes" rows="2" placeholder="Catatan atau nomor surat jalan pengembalian..."
                                  class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all"></textarea>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                        <button type="button" @click="returnModalOpen = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-xl shadow-xs transition-colors flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Submit & Kembalikan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
