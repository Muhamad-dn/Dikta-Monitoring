@extends('layouts.app')

@section('title', 'Pendaftaran Perangkat - Teknisi PT DIKTA')
@section('page_title', 'Pendaftaran Perangkat')
@section('page_subtitle', 'Pindai barcode linier perangkat dan masukkan data alokasi lapangan')

@section('content')
<div class="max-w-4xl mx-auto" x-data="technicianDeviceRegistrationApp()">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        
        <!-- Header Form -->
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Form Pendaftaran Perangkat</h2>
                <p class="text-xs text-slate-500 mt-0.5">Pindai barcode fisik atau ketik registrasi unit untuk alokasi kerja</p>
            </div>
            <a href="{{ route('technician.dashboard') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800 flex items-center gap-1">
                &larr; <span>Kembali ke Dashboard</span>
            </a>
        </div>

        <form action="{{ route('technician.devices.store') }}" method="POST" class="p-6 space-y-6" id="techDeviceForm">
            @csrf

            <!-- 1. BAGIAN SCAN LINIER BARCODE PERANGKAT -->
            <div class="rounded-2xl border border-slate-200 overflow-hidden bg-slate-950 text-white shadow-md">
                
                <!-- Baris Judul & Tombol Kontrol Scanner -->
                <div class="px-5 py-3.5 bg-slate-900/90 border-b border-slate-800 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-cyan-500/20 text-cyan-400 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-xs font-bold text-white tracking-wide">Scan Linier Barcode Perangkat</h3>
                            <p class="text-[10px] text-slate-400">Pindai barcode horizontal stiker unit perangkat</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 flex-wrap">
                        <button type="button" @click="manualMode = !manualMode"
                                class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all shadow-xs"
                                :class="manualMode ? 'bg-white text-slate-900' : 'bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white border border-slate-700'">
                            <span>Ketik Manual</span>
                        </button>

                        <button type="button" @click="toggleCamera()"
                                class="px-3.5 py-1.5 rounded-xl text-xs font-bold text-white transition-all shadow-xs flex items-center gap-1.5"
                                :class="cameraActive ? 'bg-rose-600 hover:bg-rose-700' : 'bg-cyan-600 hover:bg-cyan-700'">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            <span x-text="cameraActive ? 'Tutup Kamera' : 'Buka Kamera'"></span>
                        </button>
                    </div>
                </div>

                <!-- Viewfinder Kamera Scanner -->
                <div x-show="cameraActive" class="relative bg-black min-h-[260px] flex flex-col items-center justify-center p-4">
                    <div id="tech-linear-barcode-reader" class="w-full max-w-md mx-auto overflow-hidden rounded-xl"></div>

                    <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center p-4">
                        <div class="relative w-[280px] sm:w-[340px] h-[110px] sm:h-[120px] rounded-2xl border-2 border-cyan-400/80 shadow-[0_0_20px_rgba(34,211,238,0.3)] flex items-center justify-center">
                            <div class="absolute -top-1 -left-1 w-6 h-6 border-t-4 border-l-4 border-cyan-400 rounded-tl-xl"></div>
                            <div class="absolute -top-1 -right-1 w-6 h-6 border-t-4 border-r-4 border-cyan-400 rounded-tr-xl"></div>
                            <div class="absolute -bottom-1 -left-1 w-6 h-6 border-b-4 border-l-4 border-cyan-400 rounded-bl-xl"></div>
                            <div class="absolute -bottom-1 -right-1 w-6 h-6 border-b-4 border-r-4 border-cyan-400 rounded-br-xl"></div>
                            <div class="w-full h-0.5 bg-gradient-to-r from-transparent via-red-500 to-transparent animate-pulse shadow-sm shadow-red-500/80"></div>
                        </div>
                        <span class="mt-3 text-[11px] font-medium text-slate-300 bg-slate-900/80 px-3 py-1 rounded-full border border-slate-700">
                            Arahkan garis merah ke garis barcode linier pada stiker unit
                        </span>
                    </div>

                    <div class="mt-3 flex items-center justify-center gap-2 z-10" x-show="cameras.length > 1 || torchAvailable">
                        <button type="button" x-show="cameras.length > 1" @click="switchCamera()" 
                                class="px-3 py-1.5 rounded-lg bg-slate-800/90 hover:bg-slate-700 text-xs font-semibold text-white border border-slate-700 flex items-center gap-1.5 shadow-xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span>Ganti Kamera</span>
                        </button>
                        <button type="button" x-show="torchAvailable" @click="toggleTorch()" 
                                class="px-3 py-1.5 rounded-lg text-xs font-semibold border flex items-center gap-1.5 shadow-xs transition-colors"
                                :class="torchOn ? 'bg-amber-500 text-white border-amber-400' : 'bg-slate-800/90 text-white border-slate-700 hover:bg-slate-700'">
                            <span x-text="torchOn ? 'Matikan Lampu' : 'Nyalakan Lampu'"></span>
                        </button>
                    </div>
                </div>

                <!-- Banner Hasil Scan -->
                <div x-show="scannedSn" class="p-3.5 bg-emerald-950/80 border-t border-emerald-800/80 flex items-center justify-between gap-3 text-xs">
                    <div class="flex items-center gap-2.5">
                        <span class="w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center text-xs font-bold shadow-xs">✓</span>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-emerald-200 font-semibold">Barcode:</span>
                            <span class="font-mono text-white bg-slate-900/90 px-2 py-0.5 rounded border border-emerald-600/60" x-text="scannedSn"></span>
                            <span class="text-emerald-400 font-bold">&rarr; Registrasi:</span>
                            <span class="font-mono font-bold text-emerald-200 bg-emerald-900/90 px-2.5 py-0.5 rounded border border-emerald-500" x-text="regNumber"></span>
                        </div>
                    </div>
                    <button type="button" @click="clearScan()" class="text-emerald-300 hover:text-white underline text-[11px] shrink-0 font-medium">
                        Scan Ulang
                    </button>
                </div>

                <!-- Input Nomor Registrasi -->
                <div x-show="manualMode || scannedSn" class="p-4 bg-slate-900 border-t border-slate-800">
                    <label for="tech_manual_reg_input" class="block text-[11px] font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                        Nomor Registrasi <span class="text-cyan-400">*</span>
                    </label>
                    <input type="text" id="tech_manual_reg_input" x-model="regNumber"
                           class="w-full px-4 py-2.5 text-xs bg-slate-950 border border-slate-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-cyan-500 text-cyan-300 font-mono font-bold">
                </div>
            </div>

            <!-- Hidden Serial Number -->
            <input type="hidden" name="serial_number" :value="scannedSn || regNumber">

            <!-- Banner Notifikasi Jika Perangkat Sudah Ada di Stok -->
            <div x-show="existingDevice" x-transition class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-xs text-amber-900 flex items-start gap-3">
                <div class="w-7 h-7 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 font-bold text-xs">!</div>
                <div class="space-y-0.5">
                    <p class="font-bold text-amber-950 text-xs">
                        Perangkat Terdeteksi: <span x-text="existingDevice ? existingDevice.name : ''"></span> 
                        (<span class="font-mono font-bold text-blue-700" x-text="existingDevice ? existingDevice.registration_number : ''"></span>)
                    </p>
                    <p class="text-[11px] text-amber-800">
                        Lokasi saat ini: <strong x-text="existingDevice ? existingDevice.current_location : ''"></strong>. 
                        Data otomatis disinkronkan ke form di bawah.
                    </p>
                </div>
            </div>

            <input type="hidden" name="kode_spek" :value="specCode">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                
                <!-- Nomor Registrasi -->
                <div>
                    <label for="tech_reg_number" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Nomor Registrasi <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="registration_number" id="tech_reg_number" required 
                           x-model="regNumber"
                           @keydown.enter.prevent="checkExistingDevice(regNumber)"
                           class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all font-mono font-bold text-slate-900">
                </div>

                <!-- Nama Perangkat -->
                <div>
                    <label for="tech_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Nama Perangkat <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name" id="tech_name" required 
                           x-model="deviceName"
                           class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all font-medium text-slate-900">
                </div>

                <!-- Area -->
                <div>
                    <label for="tech_area" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Area
                    </label>
                    <select name="area" id="tech_area" x-model="area"
                            class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all text-slate-800 font-semibold">
                        <option value="">-- Pilih Area --</option>
                        @foreach(['BWA', 'BDA', 'SEOA', 'SAST', 'CDA', 'NEA'] as $a)
                            <option value="{{ $a }}">{{ $a }}</option>
                        @endforeach
                    </select>
                </div>

            </div>

            <!-- Kolom NOJAR, Pelanggan, dan Alamat -->
            <div x-show="selectedActivity !== 'Spare Dikta'" class="space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- Nojar -->
                    <div>
                        <label for="tech_nojar" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            NOJAR (Nomor Jaringan)
                        </label>
                        <input type="text" name="nojar" id="tech_nojar"
                               x-model="nojar"
                               class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all font-mono font-bold text-slate-800 uppercase">
                    </div>

                    <!-- Pelanggan -->
                    <div>
                        <label for="tech_customer_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Pelanggan
                        </label>
                        <input type="text" name="customer_name" id="tech_customer_name"
                               x-model="customerName"
                               class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all text-slate-800">
                    </div>
                </div>

                <!-- Alamat Pelanggan / Lokasi -->
                <div>
                    <label for="tech_address" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Alamat
                    </label>
                    <input type="text" name="address" id="tech_address"
                           x-model="address"
                           class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all text-slate-800">
                </div>
            </div>

            <!-- Aktivitas -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                    Aktivitas <span class="text-rose-500">*</span>
                </label>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <label class="p-3 rounded-xl border-2 cursor-pointer flex items-center justify-center gap-2 transition-all has-[:checked]:border-amber-600 has-[:checked]:bg-amber-50/70 hover:border-slate-300 bg-white">
                        <input type="radio" name="activity" value="PSB" x-model="selectedActivity" {{ old('activity', 'PSB') === 'PSB' ? 'checked' : '' }} class="text-amber-600 focus:ring-amber-500">
                        <span class="font-bold text-xs text-slate-900">PSB</span>
                    </label>

                    <label class="p-3 rounded-xl border-2 cursor-pointer flex items-center justify-center gap-2 transition-all has-[:checked]:border-amber-600 has-[:checked]:bg-amber-50/70 hover:border-slate-300 bg-white">
                        <input type="radio" name="activity" value="Troubleshoot" x-model="selectedActivity" {{ old('activity') === 'Troubleshoot' ? 'checked' : '' }} class="text-amber-600 focus:ring-amber-500">
                        <span class="font-bold text-xs text-slate-900">Troubleshoot</span>
                    </label>

                    <label class="p-3 rounded-xl border-2 cursor-pointer flex items-center justify-center gap-2 transition-all has-[:checked]:border-amber-600 has-[:checked]:bg-amber-50/70 hover:border-slate-300 bg-white">
                        <input type="radio" name="activity" value="Simulasi" x-model="selectedActivity" {{ old('activity') === 'Simulasi' ? 'checked' : '' }} class="text-amber-600 focus:ring-amber-500">
                        <span class="font-bold text-xs text-slate-900">Simulasi</span>
                    </label>

                    <label class="p-3 rounded-xl border-2 cursor-pointer flex items-center justify-center gap-2 transition-all has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50/70 hover:border-slate-300 bg-white">
                        <input type="radio" name="activity" value="Spare Dikta" x-model="selectedActivity" {{ old('activity') === 'Spare Dikta' ? 'checked' : '' }} class="text-blue-600 focus:ring-blue-500">
                        <span class="font-bold text-xs text-slate-900">Spare Dikta (Stok HB)</span>
                    </label>
                </div>
            </div>

            <!-- Pilihan Lokasi Gudang Stok HB & Sumber Perangkat (Saat Aktivitas: Spare Dikta) -->
            <div x-show="selectedActivity === 'Spare Dikta'" x-transition class="p-5 rounded-2xl bg-gradient-to-br from-emerald-500/10 via-teal-500/5 to-transparent border border-emerald-500/30 space-y-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-emerald-950 dark:text-emerald-300 mb-1.5 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <span>Pilihan Gudang Stok HB Tujuan <span class="text-rose-500">*</span></span>
                    </label>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-3">Tentukan ke mana unit spare ini akan dimasukkan ke stok inventaris sistem</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="p-3.5 rounded-xl border-2 cursor-pointer flex items-center gap-3 transition-all has-[:checked]:border-emerald-600 has-[:checked]:bg-emerald-50/80 dark:has-[:checked]:bg-emerald-950/40 hover:border-slate-300 bg-white dark:bg-slate-900">
                            <input type="radio" name="warehouse_hub" value="hb_jkt" checked class="text-emerald-600 focus:ring-emerald-500">
                            <div>
                                <div class="font-bold text-xs text-slate-900 dark:text-white flex items-center gap-1.5">
                                    <span>Gudang HB JKT</span>
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300">Tersedia di HB JKT</span>
                                </div>
                                <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">Stok Homebase Jakarta (Siap dialokasikan)</div>
                            </div>
                        </label>

                        <label class="p-3.5 rounded-xl border-2 cursor-pointer flex items-center gap-3 transition-all has-[:checked]:border-teal-600 has-[:checked]:bg-teal-50/80 dark:has-[:checked]:bg-teal-950/40 hover:border-slate-300 bg-white dark:bg-slate-900">
                            <input type="radio" name="warehouse_hub" value="hb_ckr" class="text-teal-600 focus:ring-teal-500">
                            <div>
                                <div class="font-bold text-xs text-slate-900 dark:text-white flex items-center gap-1.5">
                                    <span>Gudang HB CKR</span>
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-teal-100 text-teal-800 dark:bg-teal-900/60 dark:text-teal-300">Tersedia di HB CKR</span>
                                </div>
                                <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">Stok Homebase Cikarang (Siap dialokasikan)</div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Sumber Pengambilan -->
                <div class="pt-3 border-t border-emerald-500/20">
                    <label class="block text-xs font-bold uppercase tracking-wider text-emerald-950 dark:text-emerald-300 mb-1.5">
                        Sumber / Asal Pengambilan Unit
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="p-2.5 rounded-xl border cursor-pointer flex items-center gap-2.5 bg-white dark:bg-slate-900 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50/50 dark:has-[:checked]:bg-blue-950/30">
                            <input type="radio" name="source_origin" value="Lintasarta (Gudang Tekno / Drop Point)" checked class="text-blue-600 focus:ring-blue-500">
                            <div class="text-xs font-bold text-slate-800 dark:text-slate-200">
                                Diambil dari Lintasarta
                                <span class="block text-[10px] text-slate-500 font-normal">Gudang Tekno / Area Lintasarta</span>
                            </div>
                        </label>

                        <label class="p-2.5 rounded-xl border cursor-pointer flex items-center gap-2.5 bg-white dark:bg-slate-900 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50/50 dark:has-[:checked]:bg-blue-950/30">
                            <input type="radio" name="source_origin" value="Internal PT DIKTA" class="text-blue-600 focus:ring-blue-500">
                            <div class="text-xs font-bold text-slate-800 dark:text-slate-200">
                                Internal PT DIKTA
                                <span class="block text-[10px] text-slate-500 font-normal">Stok inventaris internal</span>
                            </div>
                        </label>
                    </div>

                    <div class="mt-2.5 flex items-center gap-2 text-[11px] text-emerald-800 dark:text-emerald-300 bg-emerald-100/70 dark:bg-emerald-950/50 p-2 rounded-xl border border-emerald-200 dark:border-emerald-800/60">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Notifikasi otomatis akan langsung dikirimkan ke <strong>Admin</strong> saat perangkat disimpan ke stok Gudang HB JKT / HB CKR.</span>
                    </div>
                </div>
            </div>

            <!-- Kolom Teknisi Bertugas (Hanya Jika Dibawa ke Lapangan) -->
            <div x-show="selectedActivity !== 'Spare Dikta'" class="p-4 rounded-2xl bg-amber-50/40 border border-amber-200/80">
                <label for="tech_technician_id" class="block text-xs font-bold uppercase tracking-wider text-slate-800 mb-1.5">
                    Teknisi Bertugas (Pemegang Perangkat)
                </label>

                <select name="technician_id" id="tech_technician_id" x-model="selectedTechnician"
                        class="w-full px-4 py-2.5 text-xs bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 transition-all text-slate-800 font-medium">
                    <option value="{{ auth()->id() }}">Saya Sendiri ({{ auth()->user()->name }})</option>
                    @foreach($technicians as $tech)
                        @if($tech->id !== auth()->id())
                            <option value="{{ $tech->id }}" {{ old('technician_id') == $tech->id ? 'selected' : '' }}>
                                {{ $tech->name }} {{ $tech->phone ? '('.$tech->phone.')' : '' }}
                            </option>
                        @endif
                    @endforeach
                </select>
            </div>

            <!-- Keterangan -->
            <div>
                <label for="tech_notes" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Keterangan
                </label>
                <textarea name="notes" id="tech_notes" rows="3"
                          class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all">{{ old('notes') }}</textarea>
            </div>

            <!-- Tombol Batal & Simpan -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('technician.dashboard') }}" class="px-4 py-2.5 text-xs font-semibold text-slate-600 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-xl shadow-xs transition-colors flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Simpan Perangkat</span>
                </button>
            </div>

        </form>
    </div>
</div>

@push('scripts')
<script>
function technicianDeviceRegistrationApp() {
    return {
        cameraActive: false,
        manualMode: false,
        regNumber: '{{ old('registration_number', $suggestedReg ?? request('reg', '')) }}',
        specCode: '{{ old('kode_spek', '') }}',
        deviceName: '{{ old('name', '') }}',
        area: '{{ old('area', 'BWA') }}',
        scannedSn: '{{ old('serial_number', request('sn', request('reg', ''))) }}',
        selectedActivity: '{{ old('activity', 'PSB') }}',
        nojar: '{{ old('nojar', $suggestedNojar ?? '') }}',
        customerName: '{{ old('customer_name', '') }}',
        address: '{{ old('address', '') }}',
        selectedTechnician: '{{ old('technician_id', auth()->id()) }}',
        existingDevice: null,
        autoFilledFromSpec: false,
        debounceTimer: null,
        html5QrCode: null,
        cameras: [],
        currentCameraIndex: 0,
        torchAvailable: false,
        torchOn: false,
        specsMap: {!! json_encode($specs->keyBy('spec_code')->map(fn($s) => ['name' => $s->name, 'brand' => $s->brand, 'type' => $s->type])) !!},

        init() {
            this.$watch('regNumber', (value) => {
                this.debounceCheck(value);
            });
            if (this.regNumber) {
                this.debounceCheck(this.regNumber);
            }
            if (this.specCode && !this.deviceName) {
                this.onSpecCodeChange(this.specCode);
            }
        },

        onSpecCodeChange(val) {
            const code = (val || this.specCode || '').trim().toUpperCase();
            if (!code) {
                this.autoFilledFromSpec = false;
                return;
            }

            // 1. Cek memory specsMap
            if (this.specsMap && this.specsMap[code]) {
                this.deviceName = this.specsMap[code].name;
                this.autoFilledFromSpec = true;
                return;
            }

            // 2. Cek prefix match
            for (const [k, v] of Object.entries(this.specsMap || {})) {
                if (code === k || code.startsWith(k) || k.startsWith(code)) {
                    this.deviceName = v.name;
                    this.autoFilledFromSpec = true;
                    return;
                }
            }

            // 3. Fallback AJAX
            fetch(`{{ route('technician.devices.check-spec') }}?spec=${encodeURIComponent(code)}`)
                .then(res => res.json())
                .then(data => {
                    if (data && data.found) {
                        this.deviceName = data.name;
                        this.autoFilledFromSpec = true;
                    }
                })
                .catch(() => {});
        },

        debounceCheck(value) {
            clearTimeout(this.debounceTimer);
            this.debounceTimer = setTimeout(() => {
                this.checkExistingDevice(value);
            }, 250);
        },

        async checkExistingDevice(val) {
            const reg = (val || this.regNumber || '').trim();
            const rawVal = (this.scannedSn || '').trim();
            if (!reg && !rawVal) {
                this.existingDevice = null;
                return;
            }

            // Coba deteksi kode spek langsung dari prefix registrasi / barcode
            const candidates = [rawVal, reg].filter(Boolean);
            let matchedSpec = null;
            for (const cand of candidates) {
                const cUpper = cand.toUpperCase();
                for (const [k, v] of Object.entries(this.specsMap || {})) {
                    if (cUpper.startsWith(k)) {
                        matchedSpec = { code: k, ...v };
                        break;
                    }
                }
                if (matchedSpec) break;
            }

            if (matchedSpec) {
                this.specCode = matchedSpec.code;
                this.deviceName = matchedSpec.name;
                this.autoFilledFromSpec = true;
            }

            try {
                const response = await fetch(`{{ route('technician.devices.check-registration') }}?reg=${encodeURIComponent(reg)}&raw=${encodeURIComponent(rawVal)}`);
                const data = await response.json();
                if (data.exists) {
                    this.existingDevice = data;
                    if (!this.deviceName && data.name) {
                        this.deviceName = data.name;
                    }
                    if (data.kode_spek) {
                        this.specCode = data.kode_spek;
                    }
                    if (data.area) {
                        this.area = data.area;
                    }
                    if (data.nojar && !this.nojar) {
                        this.nojar = data.nojar;
                    }
                    if (data.customer_name && !this.customerName) {
                        this.customerName = data.customer_name;
                    }
                    if (data.address && !this.address) {
                        this.address = data.address;
                    }
                } else if (data.spec_found) {
                    this.existingDevice = null;
                    this.specCode = data.spec_code;
                    this.deviceName = data.name;
                    this.autoFilledFromSpec = true;
                } else {
                    this.existingDevice = null;
                }
            } catch (err) {
                console.warn('Gagal cek nomor registrasi:', err);
            }
        },

        async toggleCamera() {
            if (this.cameraActive) {
                await this.stopCamera();
            } else {
                await this.startCamera();
            }
        },

        async startCamera() {
            try {
                this.cameraActive = true;
                await this.$nextTick();

                if (!this.html5QrCode) {
                    this.html5QrCode = new Html5Qrcode('tech-linear-barcode-reader', {
                        formatsToSupport: [
                            Html5QrcodeSupportedFormats.CODE_128,
                            Html5QrcodeSupportedFormats.CODE_39,
                            Html5QrcodeSupportedFormats.EAN_13,
                            Html5QrcodeSupportedFormats.QR_CODE
                        ],
                        verbose: false
                    });
                }

                this.cameras = await Html5Qrcode.getCameras();
                if (!this.cameras || this.cameras.length === 0) {
                    alert('Tidak ditemukan kamera pada perangkat ini.');
                    this.cameraActive = false;
                    return;
                }

                let cameraId = this.cameras[this.currentCameraIndex].id;
                const backCamera = this.cameras.find(c => c.label.toLowerCase().includes('back') || c.label.toLowerCase().includes('belakang'));
                if (backCamera) {
                    cameraId = backCamera.id;
                    this.currentCameraIndex = this.cameras.indexOf(backCamera);
                }

                await this.html5QrCode.start(
                    cameraId,
                    {
                        fps: 20,
                        qrbox: { width: 320, height: 110 }
                    },
                    (decodedText) => {
                        this.onScanSuccess(decodedText);
                    },
                    (errorMessage) => {}
                );
            } catch (err) {
                console.error('Kamera error:', err);
                alert('Gagal mengakses kamera: ' + (err.message || err));
                this.cameraActive = false;
            }
        },

        async stopCamera() {
            if (this.html5QrCode && this.html5QrCode.isScanning) {
                try {
                    await this.html5QrCode.stop();
                } catch(e) {}
            }
            this.cameraActive = false;
        },

        onScanSuccess(decodedText) {
            const raw = decodedText.trim();
            this.scannedSn = raw;
            this.regNumber = raw;
            this.stopCamera();
            this.checkExistingDevice(raw);
        },

        clearScan() {
            this.scannedSn = '';
            this.regNumber = '';
            this.existingDevice = null;
        }
    };
}
</script>
@endpush
@endsection
