@extends('layouts.app')

@section('title', 'Mobile Teknisi - DEVTRACK')
@section('page_title', 'Mobile Teknisi')
@section('page_subtitle', 'Scan dan update status perangkat')

@section('content')
<div class="max-w-lg mx-auto pb-24" x-data="technicianMobileApp()" @open-profile-modal.window="profileModal = true">

    <!-- TAB 1: SCAN (MODE TEKNISI) -->
    <div x-show="activeTab === 'scan'" x-transition class="space-y-4">
        
        <!-- Mode Teknisi Gradient Banner -->
        <div class="p-4 rounded-2xl bg-gradient-to-r from-blue-600 via-sky-600 to-teal-500 text-white shadow-md flex items-center justify-between">
            <div>
                <div class="text-xs font-medium text-blue-100">Mode Teknisi</div>
                <div class="text-sm sm:text-base font-bold text-white mt-0.5 leading-snug">
                    Scan dan update status<br class="hidden sm:inline"> perangkat
                </div>
            </div>
            <button type="button" @click="toggleScanner()" 
                    class="px-4 py-2 bg-white text-slate-900 hover:bg-slate-100 rounded-xl font-bold text-xs shadow-sm transition-all flex items-center gap-1.5 shrink-0">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                <span x-text="scannerActive ? 'Tutup' : 'Scan'">Scan</span>
            </button>
        </div>

        <!-- Scanner Camera Box -->
        <div x-show="scannerActive" x-cloak class="p-4 bg-slate-900 rounded-2xl border border-slate-800 space-y-3 shadow-lg">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-300 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                    <span>Arahkan Kamera ke Barcode / QR Code:</span>
                </span>
                <button type="button" @click="stopScanner()" class="text-xs text-rose-400 font-semibold hover:underline">
                    ✕ Tutup Kamera
                </button>
            </div>
            <div id="qr-reader" class="w-full rounded-xl overflow-hidden bg-black aspect-video flex items-center justify-center"></div>
            <div class="text-[11px] text-slate-400 text-center">
                Mendukung Linear Barcode (Code 128 / Code 39) & QR Code perangkat.
            </div>
        </div>

        <!-- Pencarian / Input Manual Kode Perangkat -->
        <div class="p-4 rounded-2xl bg-white dark:bg-[#131c31] border border-slate-200 dark:border-[#243048] shadow-sm space-y-3">
            <div class="text-sm font-bold text-slate-900 dark:text-white">
                Pencarian / Input Manual Kode Perangkat
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400">
                Ketik No Registrasi
            </p>
            <div class="flex gap-2">
                <input type="text" x-model="scanInput" @keydown.enter.prevent="searchAndProceed()"
                       placeholder="Ketik No Registrasi..." 
                       class="flex-1 px-3.5 py-2.5 text-xs bg-slate-50 dark:bg-[#0b1329] border border-slate-300 dark:border-[#243048] rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono font-bold text-slate-900 dark:text-white">
                <button type="button" @click="searchAndProceed()" 
                        class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-xs transition-all flex items-center gap-1">
                    <span>Lanjut</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
            </div>
        </div>

        <!-- Kolom Daftarkan Perangkat Baru -->
        <div class="p-4 rounded-2xl bg-white dark:bg-[#131c31] border border-slate-200 dark:border-[#243048] shadow-sm flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800/80 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </div>
                <div class="min-w-0">
                    <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white truncate">Daftarkan Perangkat Baru</h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Form pendaftaran unit baru ke sistem inventaris</p>
                </div>
            </div>
            <a href="{{ route('technician.devices.create') }}" 
               class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-xs flex items-center gap-1.5 shrink-0 transition-all">
                <span>Daftarkan</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        <!-- Shortcut Cepat ke Perangkat Saya -->
        @if($carriedDevices->count() > 0)
            <div class="p-4 rounded-2xl bg-amber-50/50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800/40 flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-amber-800 dark:text-amber-300 block">Unit Fisik Dipegang</span>
                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Anda membawa {{ $carriedDevices->count() }} unit perangkat</span>
                </div>
                <button type="button" @click="activeTab = 'saya'" class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold shadow-xs">
                    Lihat Unit &rarr;
                </button>
            </div>
        @endif

    </div>

    <!-- TAB 2: SAYA (PERANGKAT SAYA) -->
    <div x-show="activeTab === 'saya'" x-transition class="space-y-3.5">
        
        <!-- Header Perangkat Saya -->
        <div class="flex items-center justify-between px-1">
            <div>
                <h2 class="text-base font-extrabold text-slate-900 dark:text-white">Perangkat Saya</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Unit fisik yang saat ini Anda pegang di lapangan</p>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="px-2.5 py-1 rounded-full text-xs font-black bg-blue-100 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                    {{ $carriedDevices->count() }} Unit
                </span>
                <a href="{{ route('technician.devices.create') }}" 
                   title="Daftarkan Perangkat Baru"
                   class="px-2.5 py-1 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-xs transition-colors">
                    + Daftarkan Perangkat
                </a>
            </div>
        </div>

        <!-- Kolom Pencarian Real-time -->
        <div class="relative">
            <input type="text" x-model="searchCarried" placeholder="Cari nomor registrasi, nojar, atau nama unit..." 
                   class="w-full pl-9 pr-4 py-2 text-xs bg-white dark:bg-[#131c31] border border-slate-300 dark:border-[#243048] rounded-xl focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-white placeholder-slate-400">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
        </div>

        <!-- Stack of Device Cards -->
        <div class="space-y-2.5">
            @forelse($carriedDevices as $dev)
                <div x-show="matchesCarried('{{ strtolower($dev->name . ' ' . $dev->registration_number . ' ' . $dev->nojar . ' ' . ($dev->customer_name ?? '') . ' ' . ($dev->activeWorkOrder?->nojar ?? '') . ' ' . ($dev->activeWorkOrder?->customer_name ?? '')) }}')"
                     class="p-4 rounded-2xl bg-white dark:bg-[#131c31] border border-slate-200 dark:border-[#243048] shadow-sm space-y-2.5">
                    
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <span class="text-[10px] text-slate-400 font-bold uppercase block tracking-wider">NO REGISTRASI</span>
                            <span class="font-mono font-black text-sm text-slate-900 dark:text-white tracking-wide truncate block">
                                {{ $dev->registration_number }}
                            </span>
                        </div>
                        <div class="flex flex-col items-end gap-1 shrink-0">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                {{ $dev->area ?: 'BWA' }}
                            </span>
                            <span class="px-2 py-0.5 rounded text-[9px] font-extrabold uppercase tracking-wider
                                @if(str_contains($dev->field_status_label, 'SAMPAI'))
                                    bg-sky-50 dark:bg-sky-950/70 text-sky-600 dark:text-sky-300 border border-sky-200 dark:border-sky-800/80
                                @elseif(str_contains($dev->field_status_label, 'PENDING'))
                                    bg-amber-50 dark:bg-amber-950/70 text-amber-600 dark:text-amber-300 border border-amber-200 dark:border-amber-800/80
                                @elseif(str_contains($dev->field_status_label, 'TERINSTAL'))
                                    bg-emerald-50 dark:bg-emerald-950/70 text-emerald-600 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/80
                                @elseif(str_contains($dev->field_status_label, 'GAGAL') || str_contains($dev->field_status_label, 'RUSAK'))
                                    bg-rose-50 dark:bg-rose-950/70 text-rose-600 dark:text-rose-300 border border-rose-200 dark:border-rose-800/80
                                @else
                                    bg-blue-50 dark:bg-blue-950/70 text-blue-600 dark:text-blue-300 border border-blue-200 dark:border-blue-800/80
                                @endif
                            ">
                                {{ $dev->field_status_label }}
                            </span>
                        </div>
                    </div>

                    <div>
                        <span class="text-[10px] text-slate-400 font-bold uppercase block tracking-wider">NAMA PERANGKAT</span>
                        <div class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ $dev->name }}</div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs pt-1.5 border-t border-slate-100 dark:border-slate-800">
                        <div>
                            <span class="text-[10px] text-slate-400 font-bold uppercase block tracking-wider">NOJAR</span>
                            <span class="font-mono font-bold text-blue-600 dark:text-blue-400">
                                {{ $dev->nojar ?: ($dev->activeWorkOrder?->nojar ?: '-') }}
                            </span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 font-bold uppercase block tracking-wider">PELANGGAN</span>
                            <span class="font-medium text-slate-800 dark:text-slate-200 truncate block">
                                {{ $dev->customer_name ?: ($dev->activeWorkOrder?->customer_name ?: '-') }}
                            </span>
                        </div>
                    </div>

                    @if($dev->address || ($dev->activeWorkOrder && $dev->activeWorkOrder->location_address))
                        <div class="text-xs text-slate-600 dark:text-slate-400 flex items-start gap-1.5 bg-slate-50 dark:bg-slate-900/40 p-2 rounded-xl border border-slate-100 dark:border-slate-800">
                            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider shrink-0 mt-0.5">LOKASI:</span>
                            <span class="font-medium text-slate-800 dark:text-slate-200 text-xs">
                                {{ $dev->address ?: $dev->activeWorkOrder->location_address }}
                            </span>
                        </div>
                    @endif

                    <!-- Action Buttons: Update Status, Handover, Kembalikan -->
                    <div class="pt-2 flex items-center justify-end gap-1.5 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="selectForUpdate({{ json_encode($dev) }})"
                                class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-xs transition-colors flex items-center gap-1">
                            <span>Update Status</span>
                        </button>
                        <button type="button" @click="openHandoverModal({{ json_encode($dev) }})"
                                class="px-2.5 py-1.5 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 font-bold text-xs rounded-xl border border-indigo-200 dark:border-indigo-800 hover:bg-indigo-100 transition-colors">
                            Handover
                        </button>
                        <button type="button" @click="openReturnModal({{ json_encode($dev) }})"
                                class="px-2.5 py-1.5 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 font-bold text-xs rounded-xl border border-emerald-200 dark:border-emerald-800 hover:bg-emerald-100 transition-colors">
                            Kembalikan
                        </button>
                    </div>

                </div>
            @empty
                <div class="p-8 text-center bg-white dark:bg-[#131c31] rounded-2xl border border-slate-200 dark:border-[#243048] text-slate-400">
                    <p class="font-bold text-slate-700 dark:text-slate-300 text-sm">Tidak ada perangkat yang dibawa</p>
                    <p class="text-xs text-slate-400 mt-1">Daftarkan atau scan perangkat baru untuk mulai pengerjaan.</p>
                    <a href="{{ route('technician.devices.create') }}" class="mt-3 inline-block px-4 py-2 bg-blue-600 text-white rounded-xl text-xs font-bold">
                        + Daftarkan Perangkat
                    </a>
                </div>
            @endforelse
        </div>

    </div>

    <!-- TAB 3: UPDATE (UPDATE STATUS LAPANGAN) - PERSIS SCREENSHOT 1 -->
    <div x-show="activeTab === 'update'" x-transition class="space-y-4">
        
        <!-- Mode Teknisi Gradient Banner (Screenshot 1) -->
        <div class="p-4 rounded-2xl bg-gradient-to-r from-blue-600 via-sky-600 to-teal-500 text-white shadow-md flex items-center justify-between">
            <div>
                <div class="text-xs font-medium text-blue-100">Mode Teknisi</div>
                <div class="text-sm sm:text-base font-bold text-white mt-0.5 leading-snug">
                    Scan dan update status<br class="hidden sm:inline"> perangkat
                </div>
            </div>
            <button type="button" @click="activeTab = 'scan'; startScanner();" 
                    class="px-4 py-2 bg-white text-slate-900 hover:bg-slate-100 rounded-xl font-bold text-xs shadow-sm transition-all flex items-center gap-1.5 shrink-0">
                <span>Scan</span>
            </button>
        </div>

        <!-- Card: Update Status Lapangan (Screenshot 1) -->
        <div class="p-5 rounded-2xl bg-white dark:bg-[#131c31] border border-slate-200 dark:border-[#243048] shadow-sm space-y-4">
            
            <h2 class="text-base font-bold text-slate-900 dark:text-white">Update Status Lapangan</h2>

            <form action="{{ route('technician.devices.update-status') }}" method="POST" enctype="multipart/form-data" class="space-y-3.5">
                @csrf

                <!-- 1. Kode Perangkat / Barcode -->
                <div>
                    <label for="update_device_code" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        Kode Perangkat / Barcode
                    </label>

                    <input type="hidden" name="device_id" :value="updateForm.deviceId">

                    <div class="relative">
                        <input type="text" 
                               name="device_code" 
                               id="update_device_code" 
                               required
                               x-model="updateForm.deviceCode"
                               list="carried_devices_datalist"
                               @input="onDeviceCodeInput($event.target.value)"
                               placeholder="Contoh: B2WS03VI010201423"
                               class="w-full px-3.5 py-2.5 text-xs bg-slate-50 dark:bg-[#0b1329] border border-blue-500/80 dark:border-blue-500 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono font-bold text-slate-900 dark:text-white shadow-xs">
                        
                        <datalist id="carried_devices_datalist">
                            @foreach($carriedDevices as $dev)
                                <option value="{{ $dev->registration_number }}">{{ $dev->name }} ({{ $dev->nojar ?: '-' }})</option>
                            @endforeach
                        </datalist>
                    </div>

                    @if($carriedDevices->count() > 1)
                        <div class="mt-2 flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-none">
                            <span class="text-[10px] text-slate-400 shrink-0 font-medium">Pilih cepat:</span>
                            @foreach($carriedDevices as $dev)
                                <button type="button" 
                                        @click="selectForUpdate({{ $dev->toJson() }})"
                                        class="px-2 py-0.5 rounded-lg text-[10px] font-mono font-bold transition-all shrink-0"
                                        :class="updateForm.deviceCode === '{{ $dev->registration_number }}' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-300 dark:hover:bg-slate-700'">
                                    {{ $dev->registration_number }}
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- 2. Status Dropdown -->
                <div>
                    <label for="update_status" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        Status
                    </label>
                    <div class="relative">
                        <select name="status" id="update_status" required x-model="updateForm.status"
                                class="w-full appearance-none px-3.5 py-2.5 pr-10 text-xs bg-slate-50 dark:bg-[#0b1329] border border-slate-300 dark:border-[#243048] rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-white font-medium">
                            <option value="TERINSTAL">Terinstal</option>
                            <option value="SAMPAI LOKASI">Sampai Lokasi</option>
                            <option value="PENDING">Pending</option>
                            <option value="GAGAL INSTAL">Gagal Instal</option>
                            <option value="DIBAWA KEMBALI KE HB JKT">Dibawa Kembali ke HB JKT</option>
                            <option value="DIBAWA KEMBALI KE HB CKR">Dibawa Kembali ke HB CKR</option>
                            <option value="DIBAWA KEMBALI KE HB DIKTA" class="hidden">Dibawa Kembali ke HB Dikta</option>
                            <option value="DI BAWA KEMBALI KE AREA">Di Bawa Kembali ke Area</option>
                            <option value="RUSAK">Rusak</option>
                            <option value="HILANG">Hilang</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>

                <!-- 3. NOJAR Lokasi Aktual (Nama Pelanggan) -->
                <div>
                    <label for="update_customer" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        NOJAR Lokasi Aktual
                    </label>
                    <input type="text" name="current_location" id="update_customer" required x-model="updateForm.location"
                           placeholder="Contoh: BANK NEGARA INDONESIA 1946 (PERSERO)"
                           class="w-full px-3.5 py-2.5 text-xs bg-slate-50 dark:bg-[#0b1329] border border-slate-300 dark:border-[#243048] rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-white font-medium">
                </div>

                <!-- 4. NOJAR (Subfield box) -->
                <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-[#0b1329] border border-slate-200 dark:border-[#243048]">
                    <label for="update_nojar" class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">
                        NOJAR
                    </label>
                    <input type="text" name="nojar" id="update_nojar" x-model="updateForm.nojar"
                           placeholder="Contoh: 2016006136"
                           class="w-full bg-transparent border-0 p-0 text-xs font-mono font-bold text-slate-900 dark:text-white focus:ring-0">
                </div>

                <!-- 5. ALAMAT (Subfield box) -->
                <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-[#0b1329] border border-slate-200 dark:border-[#243048]">
                    <label for="update_address" class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">
                        ALAMAT
                    </label>
                    <input type="text" name="address" id="update_address" x-model="updateForm.address"
                           placeholder="Contoh: Citra raya 2"
                           class="w-full bg-transparent border-0 p-0 text-xs text-slate-900 dark:text-white focus:ring-0">
                </div>

                <!-- 6. Catatan pekerjaan -->
                <div>
                    <label for="update_notes" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        Catatan pekerjaan
                    </label>
                    <textarea name="notes" id="update_notes" rows="2" x-model="updateForm.notes"
                              placeholder="Contoh: perangkat sudah terpasang dan..."
                              class="w-full px-3.5 py-2.5 text-xs bg-slate-50 dark:bg-[#0b1329] border border-slate-300 dark:border-[#243048] rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-white"></textarea>
                </div>

                <!-- 7. Foto Bukti (Kamera / Unggah) -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        Foto Bukti (Kamera / Galeri)
                    </label>
                    <input type="file" name="evidence_photo" id="update_evidence_photo" accept="image/*" capture="environment"
                           @change="previewPhoto($event)"
                           class="block w-full text-xs text-slate-500 dark:text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 dark:file:bg-blue-950 file:text-blue-600 dark:file:text-blue-300 hover:file:bg-blue-100 cursor-pointer">
                    <template x-if="photoPreviewUrl">
                        <div class="mt-2 flex items-center gap-2.5 p-2 bg-slate-50 dark:bg-[#0b1329] border border-slate-200 dark:border-[#243048] rounded-xl w-fit">
                            <img :src="photoPreviewUrl" class="h-14 w-14 object-cover rounded-lg border border-slate-300 dark:border-slate-700">
                            <span class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">✓ Foto siap dikirim</span>
                        </div>
                    </template>
                </div>

                <!-- 8. Submit Button -->
                <button type="submit" 
                        class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-md shadow-blue-600/20 transition-all flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Simpan Update Status</span>
                </button>
            </form>
        </div>

    </div>

    <!-- TAB 4: RIWAYAT (RIWAYAT UPDATE SAYA) - PERSIS SCREENSHOT 2 & 3 -->
    <div x-show="activeTab === 'riwayat'" x-transition class="space-y-4">
        
        <!-- Top Card: Ringkasan Instalasi Hari Ini (WhatsApp Ready! - Screenshot 3) -->
        <div class="p-4 rounded-2xl bg-white dark:bg-[#131c31] border border-slate-200 dark:border-[#243048] shadow-sm space-y-2.5">
            <div class="flex items-center justify-between">
                <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">Ringkasan Instalasi Hari Ini</h2>
                <button type="button" @click="copyWhatsAppReport()" 
                        class="px-3.5 py-1.5 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-xs transition-all flex items-center gap-1">
                    <span x-text="copiedReport ? '✓ Tersalin!' : 'Salin'">Salin</span>
                </button>
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400">
                Buat laporan siap tempel untuk grup WhatsApp dari perangkat yang Anda instal hari ini.
            </p>

            <!-- Preformatted Report Box (Screenshot 3) -->
            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-[#0b1329] border border-slate-200 dark:border-[#243048] text-xs text-slate-800 dark:text-slate-200 font-sans leading-relaxed whitespace-pre-wrap select-all">
{{ $todayReportText }}
            </div>
        </div>

        <!-- Section: Riwayat Update Saya (Screenshot 2 & 3) -->
        <div class="space-y-3">
            <div class="flex items-center justify-between px-1">
                <h2 class="text-base font-bold text-slate-900 dark:text-white">Riwayat Update Saya</h2>
                <span class="text-xs text-slate-400">Riwayat Lapangan</span>
            </div>

            <div class="space-y-2.5">
                @forelse($recentHistories as $h)
                    <div class="p-4 rounded-2xl bg-white dark:bg-[#131c31] border border-slate-200 dark:border-[#243048] shadow-sm space-y-1.5 transition-all">
                        
                        <!-- Line 1: Barcode / Registration Number on Left, Timestamp on Right -->
                        <div class="flex items-center justify-between">
                            <span class="font-mono font-bold text-xs text-slate-900 dark:text-white tracking-wide">
                                {{ $h->device?->registration_number ?: '-' }}
                            </span>
                            <span class="text-[11px] text-slate-400 dark:text-slate-400 font-mono">
                                {{ $h->created_at->format('Y-m-d H:i:s') }}
                            </span>
                        </div>

                        <!-- Line 2: Device Name -->
                        @if($h->device)
                            <div class="text-xs font-semibold text-slate-800 dark:text-slate-200">
                                {{ $h->device->name }}
                            </div>
                        @endif

                        <!-- Line 3: Transition Flow (e.g. Sampai Lokasi -> Terinstal) -->
                        <div class="text-[11px] text-slate-500 dark:text-slate-400">
                            {{ $h->previous_status ? ucfirst(str_replace('_', ' ', $h->previous_status)) : 'Keluar Kantor' }} 
                            &rarr; 
                            <span class="font-medium text-slate-700 dark:text-slate-300">
                                {{ $h->new_status ? ucfirst(str_replace('_', ' ', $h->new_status)) : $h->action }}
                            </span>
                        </div>

                        <!-- Line 4: Customer / Location Name -->
                        @php
                            $locName = $h->workOrder?->customer_name ?: ($h->device?->customer_name ?: ($h->to_location ?: null));
                        @endphp
                        @if($locName)
                            <div class="text-xs font-medium text-slate-800 dark:text-slate-200">
                                {{ $locName }}
                            </div>
                        @endif

                        <!-- Line 5: Status / Notes -->
                        @if($h->notes)
                            <div class="text-xs text-slate-600 dark:text-slate-400">
                                {{ $h->notes }}
                            </div>
                        @endif

                        <!-- Line 6: Lihat foto bukti link -->
                        @if($h->photo_path)
                            <div class="pt-1">
                                <button type="button" 
                                        @click="openPhotoPreview('{{ asset('storage/' . $h->photo_path) }}', 'Bukti: {{ $h->device?->registration_number }}')"
                                        class="text-xs font-semibold text-blue-500 hover:text-blue-600 hover:underline">
                                    Lihat foto bukti
                                </button>
                            </div>
                        @endif

                    </div>
                @empty
                    <div class="p-6 text-center bg-white dark:bg-[#131c31] rounded-2xl border border-slate-200 dark:border-[#243048] text-slate-400 text-xs">
                        Belum ada riwayat aktivitas yang tercatat.
                    </div>
                @endforelse
            </div>
        </div>

    </div>

    <!-- BOTTOM NAVIGATION BAR (PERSIS SCREENSHOT 1, 2, 3 DENGAN PILL BIRU AKTIF) -->
    <nav class="fixed bottom-0 inset-x-0 z-40 bg-slate-900/95 dark:bg-slate-950/95 backdrop-blur-md border-t border-slate-800 py-2.5 px-3 shadow-2xl">
        <div class="flex items-center justify-around gap-1 max-w-lg mx-auto">
            
            <!-- 1. Scan -->
            <button type="button" @click="activeTab = 'scan'"
                    class="flex-1 py-2 text-center text-xs font-bold transition-all rounded-2xl"
                    :class="activeTab === 'scan' ? 'bg-blue-600 text-white shadow-md' : 'text-slate-400 hover:text-slate-200'">
                Scan
            </button>

            <!-- 2. Saya -->
            <button type="button" @click="activeTab = 'saya'"
                    class="flex-1 py-2 text-center text-xs font-bold transition-all rounded-2xl"
                    :class="activeTab === 'saya' ? 'bg-blue-600 text-white shadow-md' : 'text-slate-400 hover:text-slate-200'">
                Saya
            </button>

            <!-- 3. Update -->
            <button type="button" @click="activeTab = 'update'"
                    class="flex-1 py-2 text-center text-xs font-bold transition-all rounded-2xl"
                    :class="activeTab === 'update' ? 'bg-blue-600 text-white shadow-md' : 'text-slate-400 hover:text-slate-200'">
                Update
            </button>

            <!-- 4. Riwayat -->
            <button type="button" @click="activeTab = 'riwayat'"
                    class="flex-1 py-2 text-center text-xs font-bold transition-all rounded-2xl"
                    :class="activeTab === 'riwayat' ? 'bg-blue-600 text-white shadow-md' : 'text-slate-400 hover:text-slate-200'">
                Riwayat
            </button>

        </div>
    </nav>

    <!-- MODAL PROFIL TEKNISI -->
    <div x-show="profileModal" x-cloak class="fixed inset-0 z-50 bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white dark:bg-[#131c31] rounded-2xl max-w-sm w-full p-5 shadow-2xl border border-slate-200 dark:border-[#243048] space-y-4" @click.outside="profileModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Profil Teknisi</h3>
                <button type="button" @click="profileModal = false" class="text-slate-400 hover:text-slate-600 text-sm font-bold">✕</button>
            </div>
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-lg shadow-sm">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div>
                    <h4 class="text-sm font-extrabold text-slate-900 dark:text-white uppercase">{{ auth()->user()->name }}</h4>
                    <p class="text-xs text-slate-400">@<span>{{ auth()->user()->username }}</span></p>
                </div>
            </div>
            <div class="space-y-2 text-xs border-t border-slate-100 dark:border-slate-800 pt-3">
                <div class="flex justify-between py-1">
                    <span class="text-slate-400">Area Bertugas:</span>
                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ auth()->user()->area ?: 'BWA' }}</span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-slate-400">Nomor WhatsApp:</span>
                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ auth()->user()->phone ?: '-' }}</span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-slate-400">Unit Dibawa:</span>
                    <span class="font-bold text-blue-600 dark:text-blue-400">{{ $carriedDevices->count() }} Unit</span>
                </div>
            </div>
            <div class="pt-2">
                <button type="button" @click="profileModal = false" class="w-full py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 font-bold text-xs rounded-xl">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL HANDOVER -->
    <div x-show="handoverModal" x-cloak class="fixed inset-0 z-50 bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white dark:bg-[#131c31] rounded-2xl max-w-sm w-full p-5 shadow-2xl border border-slate-200 dark:border-[#243048] space-y-4" @click.outside="handoverModal = false">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Handover Perangkat</h3>
                <button type="button" @click="handoverModal = false" class="text-slate-400 hover:text-slate-600 text-sm font-bold">✕</button>
            </div>
            <div class="p-2.5 bg-indigo-50 dark:bg-indigo-950/40 rounded-xl text-xs space-y-0.5">
                <div class="font-mono font-bold text-indigo-700 dark:text-indigo-300" x-text="selectedDevice?.registration_number"></div>
                <div class="text-slate-700 dark:text-slate-300 font-semibold" x-text="selectedDevice?.name"></div>
            </div>
            <form :action="'{{ url('technician/devices') }}/' + (selectedDevice?.id || 0) + '/handover'" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Pilih Rekan Teknisi *</label>
                    <select name="to_technician_id" required class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-[#0b1329] border border-slate-300 dark:border-[#243048] rounded-xl text-slate-900 dark:text-white">
                        <option value="">-- Pilih Teknisi Tujuan --</option>
                        @foreach($otherTechnicians as $tech)
                            <option value="{{ $tech->id }}">{{ $tech->name }} (Area: {{ $tech->area ?: 'BWA' }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Catatan Handover</label>
                    <textarea name="notes" rows="2" placeholder="Alasan oper unit..." class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-[#0b1329] border border-slate-300 dark:border-[#243048] rounded-xl text-slate-900 dark:text-white"></textarea>
                </div>
                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="handoverModal = false" class="px-3 py-1.5 bg-slate-100 dark:bg-slate-800 text-xs font-semibold rounded-xl">Batal</button>
                    <button type="submit" class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-xs">Konfirmasi Handover</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL KEMBALIKAN -->
    <div x-show="returnModal" x-cloak class="fixed inset-0 z-50 bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white dark:bg-[#131c31] rounded-2xl max-w-sm w-full p-5 shadow-2xl border border-slate-200 dark:border-[#243048] space-y-4" @click.outside="returnModal = false">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Kembalikan Perangkat</h3>
                <button type="button" @click="returnModal = false" class="text-slate-400 hover:text-slate-600 text-sm font-bold">✕</button>
            </div>
            <div class="p-2.5 bg-emerald-50 dark:bg-emerald-950/40 rounded-xl text-xs space-y-0.5">
                <div class="font-mono font-bold text-emerald-700 dark:text-emerald-300" x-text="selectedDevice?.registration_number"></div>
                <div class="text-slate-700 dark:text-slate-300 font-semibold" x-text="selectedDevice?.name"></div>
            </div>
            <form :action="'{{ url('technician/devices') }}/' + (selectedDevice?.id || 0) + '/return'" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Tujuan Pengembalian *</label>
                    <select name="destination" required class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-[#0b1329] border border-slate-300 dark:border-[#243048] rounded-xl text-slate-900 dark:text-white">
                        <option value="Gudang HB JKT">Gudang HB JKT (Homebase Jakarta)</option>
                        <option value="Gudang HB CKR">Gudang HB CKR (Homebase Cikarang)</option>
                        <option value="Gudang Tekno">Lintasarta - Gudang Tekno</option>
                        <option value="BWA">Lintasarta - Area BWA</option>
                        <option value="BDA">Lintasarta - Area BDA</option>
                        <option value="SEOA">Lintasarta - Area SEOA</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Kondisi Fisik *</label>
                    <select name="condition" required class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-[#0b1329] border border-slate-300 dark:border-[#243048] rounded-xl text-slate-900 dark:text-white">
                        <option value="bekas_bagus" selected>Bekas Bagus / Normal</option>
                        <option value="baru">Baru / Normal</option>
                        <option value="rusak">Rusak</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Catatan</label>
                    <textarea name="return_notes" rows="2" placeholder="Catatan pengembalian..." class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-[#0b1329] border border-slate-300 dark:border-[#243048] rounded-xl text-slate-900 dark:text-white"></textarea>
                </div>
                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="returnModal = false" class="px-3 py-1.5 bg-slate-100 dark:bg-slate-800 text-xs font-semibold rounded-xl">Batal</button>
                    <button type="submit" class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs">Konfirmasi Kembalikan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL FOTO BUKTI -->
    <div x-show="photoModal" x-cloak class="fixed inset-0 z-50 bg-black/80 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white dark:bg-[#131c31] rounded-2xl max-w-sm w-full p-4 space-y-3 shadow-2xl" @click.outside="photoModal = false">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
                <span class="text-xs font-bold text-slate-800 dark:text-slate-200" x-text="photoModalTitle"></span>
                <button type="button" @click="photoModal = false" class="text-slate-400 hover:text-slate-600 text-sm font-bold">✕</button>
            </div>
            <div class="rounded-xl overflow-hidden bg-black flex items-center justify-center">
                <img :src="photoModalUrl" alt="Foto Bukti" class="max-h-[60vh] w-auto object-contain">
            </div>
            <button type="button" @click="photoModal = false" class="w-full py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-xs rounded-xl">
                Tutup
            </button>
        </div>
    </div>

</div>

@push('scripts')
<script>
function technicianMobileApp() {
    return {
        // Tab aktif default: 'update' (seperti di screenshot 1) atau dari query parameter
        activeTab: new URLSearchParams(window.location.search).get('tab') || 'update',
        scannerActive: false,
        html5QrCode: null,
        scanInput: '',
        searchCarried: '',
        copiedReport: false,
        profileModal: false,
        handoverModal: false,
        returnModal: false,
        photoModal: false,
        photoModalUrl: '',
        photoModalTitle: '',
        photoPreviewUrl: null,
        selectedDevice: null,

        carriedDevicesCount: {{ $carriedDevices->count() }},
        devicesList: @json($carriedDevices),
        firstDeviceId: '{{ $carriedDevices->first()?->id }}',
        firstDeviceReg: '{{ $carriedDevices->first()?->registration_number }}',
        firstDeviceNojar: '{{ $carriedDevices->first()?->nojar ?: ($carriedDevices->first()?->activeWorkOrder?->nojar ?: '') }}',
        firstDeviceCustomer: '{{ $carriedDevices->first()?->customer_name ?: ($carriedDevices->first()?->activeWorkOrder?->customer_name ?: '') }}',
        firstDeviceAddress: '{{ $carriedDevices->first()?->address ?: ($carriedDevices->first()?->activeWorkOrder?->location_address ?: '') }}',
        firstDeviceLocation: '{{ $carriedDevices->first()?->current_location }}',

        updateForm: {
            deviceId: '{{ $carriedDevices->first()?->id }}',
            deviceCode: '{{ $carriedDevices->first()?->registration_number }}',
            status: 'TERINSTAL',
            nojar: '{{ $carriedDevices->first()?->nojar ?: ($carriedDevices->first()?->activeWorkOrder?->nojar ?: '') }}',
            location: '{{ $carriedDevices->first()?->customer_name ?: ($carriedDevices->first()?->activeWorkOrder?->customer_name ?: ($carriedDevices->first()?->current_location ?: '')) }}',
            address: '{{ $carriedDevices->first()?->address ?: ($carriedDevices->first()?->activeWorkOrder?->location_address ?: '') }}',
            notes: ''
        },

        init() {
            if (this.carriedDevicesCount === 1 && this.firstDeviceId) {
                this.updateForm.deviceId = this.firstDeviceId;
                this.updateForm.deviceCode = this.firstDeviceReg;
                this.updateForm.nojar = this.firstDeviceNojar;
                this.updateForm.location = this.firstDeviceCustomer || this.firstDeviceLocation;
                this.updateForm.address = this.firstDeviceAddress;
            }
        },

        matchesCarried(rowText) {
            if (!this.searchCarried.trim()) return true;
            return rowText.toLowerCase().includes(this.searchCarried.toLowerCase().trim());
        },

        selectForUpdate(dev) {
            this.selectedDevice = dev;
            this.updateForm.deviceId = dev.id;
            this.updateForm.deviceCode = dev.registration_number;
            this.updateForm.nojar = dev.nojar || (dev.active_work_order ? dev.active_work_order.nojar : '');
            this.updateForm.location = dev.customer_name || (dev.active_work_order ? dev.active_work_order.customer_name : (dev.current_location || ''));
            this.updateForm.address = dev.address || (dev.active_work_order ? dev.active_work_order.location_address : '');
            this.activeTab = 'update';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        onDeviceCodeInput(val) {
            if (!val) return;
            const trimmed = val.trim();
            const dev = this.devicesList.find(d => 
                (d.registration_number && d.registration_number.toLowerCase() === trimmed.toLowerCase()) || 
                String(d.id) === trimmed
            );
            if (dev) {
                this.updateForm.deviceId = dev.id;
                this.updateForm.nojar = dev.nojar || (dev.active_work_order ? dev.active_work_order.nojar : '');
                this.updateForm.location = dev.customer_name || (dev.active_work_order ? dev.active_work_order.customer_name : (dev.current_location || ''));
                this.updateForm.address = dev.address || (dev.active_work_order ? dev.active_work_order.location_address : '');
            }
        },

        searchAndProceed() {
            const code = this.scanInput.trim();
            if (!code) return;
            this.updateForm.deviceCode = code;
            this.onDeviceCodeInput(code);
            this.activeTab = 'update';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        copyWhatsAppReport() {
            const reportText = @json($todayReportText);
            if (navigator.clipboard) {
                navigator.clipboard.writeText(reportText).then(() => {
                    this.copiedReport = true;
                    setTimeout(() => { this.copiedReport = false; }, 2500);
                });
            } else {
                const textArea = document.createElement('textarea');
                textArea.value = reportText;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                this.copiedReport = true;
                setTimeout(() => { this.copiedReport = false; }, 2500);
            }
        },

        openPhotoPreview(url, title = 'Foto Bukti') {
            this.photoModalUrl = url;
            this.photoModalTitle = title;
            this.photoModal = true;
        },

        previewPhoto(event) {
            const file = event.target.files[0];
            if (file) {
                this.photoPreviewUrl = URL.createObjectURL(file);
            } else {
                this.photoPreviewUrl = null;
            }
        },

        openHandoverModal(dev) {
            this.selectedDevice = dev;
            this.handoverModal = true;
        },

        openReturnModal(dev) {
            this.selectedDevice = dev;
            this.returnModal = true;
        },

        toggleScanner() {
            if (this.scannerActive) {
                this.stopScanner();
            } else {
                this.startScanner();
            }
        },

        startScanner() {
            this.scannerActive = true;
            this.$nextTick(() => {
                if (typeof Html5Qrcode !== 'undefined') {
                    if (this.html5QrCode) {
                        try { this.html5QrCode.stop(); } catch(e) {}
                    }
                    this.html5QrCode = new Html5Qrcode("qr-reader");
                    const config = { fps: 10, qrbox: { width: 250, height: 150 } };
                    this.html5QrCode.start(
                        { facingMode: "environment" },
                        config,
                        (decodedText) => {
                            this.stopScanner();
                            this.scanInput = decodedText;
                            this.searchAndProceed();
                        },
                        (errorMessage) => {
                            // frame scan non-match, abaikan
                        }
                    ).catch(err => {
                        console.warn("Kamera tidak dapat dimulai:", err);
                    });
                }
            });
        },

        stopScanner() {
            if (this.html5QrCode) {
                try {
                    this.html5QrCode.stop().then(() => {
                        this.html5QrCode.clear();
                    }).catch(e => {});
                } catch(e) {}
            }
            this.scannerActive = false;
        }
    };
}
</script>
@endpush
@endsection
