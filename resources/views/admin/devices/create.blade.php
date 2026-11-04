@extends('layouts.app')

@section('title', 'Pendaftaran Perangkat Baru')
@section('page_title', 'Form Pendaftaran Perangkat')
@section('page_subtitle', 'Pindai barcode linier perangkat dan masukkan data alokasi pekerjaan PT DIKTA')

@section('content')
<div class="max-w-4xl mx-auto" x-data="deviceRegistrationApp()">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        
        <!-- Header Form -->
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Form Pendaftaran Perangkat</h2>
                <p class="text-xs text-slate-500 mt-0.5">Pindai barcode fisik dan lengkapi detail registrasi perangkat</p>
            </div>
            <a href="{{ route('admin.devices.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">
                &larr; Kembali ke Daftar
            </a>
        </div>

        <form action="{{ route('admin.devices.store') }}" method="POST" class="p-6 space-y-6" id="deviceForm">
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
                            <p class="text-[10px] text-slate-400">Pindai barcode horizontal (Code 128 / SN) pada fisik unit</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 flex-wrap">
                        <!-- Tombol Input Manual -->
                        <button type="button" @click="manualMode = !manualMode"
                                class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all shadow-xs"
                                :class="manualMode ? 'bg-white text-slate-900' : 'bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white border border-slate-700'">
                            <span>Masukkan Registrasi Manual</span>
                        </button>

                        <!-- Tombol Kamera Scanner -->
                        <button type="button" @click="toggleCamera()"
                                class="px-3.5 py-1.5 rounded-xl text-xs font-bold text-white transition-all shadow-xs flex items-center gap-1.5"
                                :class="cameraActive ? 'bg-rose-600 hover:bg-rose-700' : 'bg-cyan-600 hover:bg-cyan-700'">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            <span x-text="cameraActive ? 'Tutup Kamera' : 'Buka Kamera'"></span>
                        </button>
                    </div>
                </div>

                <!-- Viewfinder Kamera Scanner Linier -->
                <div x-show="cameraActive" class="relative bg-black min-h-[280px] flex flex-col items-center justify-center p-4">
                    <!-- Target elemen render html5-qrcode -->
                    <div id="linear-barcode-reader" class="w-full max-w-md mx-auto overflow-hidden rounded-xl"></div>

                    <!-- Overlay Viewfinder Persegi Panjang Linier (Sesuai Foto) -->
                    <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center p-4">
                        <div class="relative w-[280px] sm:w-[340px] h-[110px] sm:h-[120px] rounded-2xl border-2 border-cyan-400/80 shadow-[0_0_20px_rgba(34,211,238,0.3)] flex items-center justify-center">
                            <!-- Sudut-Sudut Biru Cyan Tebal -->
                            <div class="absolute -top-1 -left-1 w-6 h-6 border-t-4 border-l-4 border-cyan-400 rounded-tl-xl"></div>
                            <div class="absolute -top-1 -right-1 w-6 h-6 border-t-4 border-r-4 border-cyan-400 rounded-tr-xl"></div>
                            <div class="absolute -bottom-1 -left-1 w-6 h-6 border-b-4 border-l-4 border-cyan-400 rounded-bl-xl"></div>
                            <div class="absolute -bottom-1 -right-1 w-6 h-6 border-b-4 border-r-4 border-cyan-400 rounded-br-xl"></div>

                            <!-- Laser Pemindai Bergerak -->
                            <div class="w-full h-0.5 bg-gradient-to-r from-transparent via-red-500 to-transparent animate-pulse shadow-sm shadow-red-500/80"></div>
                        </div>
                        <span class="mt-3 text-[11px] font-medium text-slate-300 bg-slate-900/80 px-3 py-1 rounded-full border border-slate-700 backdrop-blur-xs">
                            Arahkan garis merah ke garis barcode linier pada stiker perangkat
                        </span>
                    </div>

                    <!-- Controls: Switch Camera & Torch -->
                    <div class="mt-3 flex items-center justify-center gap-2 z-10" x-show="cameras.length > 1 || torchAvailable">
                        <button type="button" x-show="cameras.length > 1" @click="switchCamera()" 
                                class="px-3 py-1.5 rounded-lg bg-slate-800/90 hover:bg-slate-700 text-xs font-semibold text-white border border-slate-700 flex items-center gap-1.5 shadow-xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span>Ganti Kamera HP</span>
                        </button>
                        <button type="button" x-show="torchAvailable" @click="toggleTorch()" 
                                class="px-3 py-1.5 rounded-lg text-xs font-semibold border flex items-center gap-1.5 shadow-xs transition-colors"
                                :class="torchOn ? 'bg-amber-500 text-white border-amber-400' : 'bg-slate-800/90 text-white border-slate-700 hover:bg-slate-700'">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                            <span x-text="torchOn ? 'Matikan Lampu' : 'Nyalakan Lampu'"></span>
                        </button>
                    </div>
                </div>

                <!-- Banner Hasil Scan Sukses / Notifikasi -->
                <div x-show="scannedSn" class="p-3.5 bg-emerald-950/80 border-t border-emerald-800/80 flex items-center justify-between gap-3 text-xs">
                    <div class="flex items-center gap-2.5">
                        <span class="w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center text-xs font-bold shadow-xs">✓</span>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-emerald-200 font-semibold">Barcode Fisik:</span>
                                <span class="font-mono text-white bg-slate-900/90 px-2 py-0.5 rounded border border-emerald-600/60" x-text="scannedSn"></span>
                                <span class="text-emerald-400 font-bold">&rarr; No. Registrasi:</span>
                                <span class="font-mono font-bold text-emerald-200 bg-emerald-900/90 px-2.5 py-0.5 rounded border border-emerald-500" x-text="regNumber"></span>
                            </div>
                            <p class="text-[10px] text-emerald-300/80 mt-0.5" x-show="scannedSn !== regNumber">
                                *Otomatis mengekstrak nomor registrasi unit dari 5-6 digit/huruf terakhir barcode fisik (misal: HA0267 / HA2067)
                            </p>
                        </div>
                    </div>
                    <button type="button" @click="clearScan()" class="text-emerald-300 hover:text-white underline text-[11px] shrink-0 font-medium">
                        Scan Ulang
                    </button>
                </div>

                <!-- Input Nomor Registrasi (Manual / Hasil Scan) -->
                <div x-show="manualMode || scannedSn" class="p-4 bg-slate-900 border-t border-slate-800">
                    <label for="manual_reg_input" class="block text-[11px] font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                        Nomor Registrasi <span class="text-cyan-400">*</span>
                    </label>
                    <input type="text" id="manual_reg_input" x-model="regNumber"
                           @keydown.enter.prevent="checkExistingDevice(regNumber)"
                           class="w-full px-4 py-2.5 text-xs bg-slate-950 border border-slate-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-cyan-500 text-cyan-300 font-mono font-bold">
                </div>
            </div>

            <!-- Hidden Serial Number simpan barcode fisik lengkap (atau nomor registrasi jika tidak ada barcode terpisah) -->
            <input type="hidden" name="serial_number" :value="scannedSn || regNumber">

            <!-- Banner Notifikasi Jika Perangkat Sudah Ada di Stok -->
            <div x-show="existingDevice" x-transition class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-xs text-amber-900 flex items-start gap-3">
                <div class="w-7 h-7 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 font-bold text-xs">!</div>
                <div class="space-y-0.5">
                    <p class="font-bold text-amber-950 text-xs">
                        Perangkat Terdaftar di Stok: <span x-text="existingDevice ? existingDevice.name : ''"></span> 
                        (<span class="font-mono font-bold text-brand-700" x-text="existingDevice ? existingDevice.registration_number : ''"></span>)
                    </p>
                    <p class="text-[11px] text-amber-800">
                        Lokasi saat ini: <strong x-text="existingDevice ? existingDevice.current_location : ''"></strong>. 
                        Penginputan ini akan <strong>otomatis memindahkan status & mengalokasikan</strong> perangkat ke tim teknisi yang dipilih.
                    </p>
                </div>
            </div>

            <!-- 2. DATA IDENTITAS PERANGKAT -->
            <!-- Hidden kode_spek sebagai acuan backend tanpa ditampilkan di UI -->
            <input type="hidden" name="kode_spek" :value="specCode">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                
                <!-- Nomor Registrasi -->
                <div>
                    <label for="registration_number" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Nomor Registrasi <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="registration_number" id="registration_number" required 
                           x-model="regNumber"
                           @keydown.enter.prevent="checkExistingDevice(regNumber)"
                           class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all font-mono font-bold text-brand-700">
                </div>

                <!-- Nama Perangkat -->
                <div>
                    <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Nama Perangkat <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name" id="name" required 
                           x-model="deviceName"
                           class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all font-medium text-slate-900">
                </div>

                <!-- Area -->
                <div>
                    <label for="area" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Area
                    </label>
                    <select name="area" id="area" x-model="area"
                            class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all text-slate-800 font-semibold">
                        <option value="">-- Pilih Area --</option>
                        @foreach(['BWA', 'BDA', 'SEOA', 'SAST', 'CDA', 'NEA'] as $a)
                            <option value="{{ $a }}">{{ $a }}</option>
                        @endforeach
                    </select>
                </div>

            </div>

            <!-- Kolom NOJAR, Pelanggan, dan Alamat (Tidak Perlu Diisi Jika Spare Dikta) -->
            <div x-show="selectedActivity !== 'Spare Dikta'" class="space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- Nojar (Nomor Jaringan) -->
                    <div>
                        <label for="nojar" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            NOJAR (Nomor Jaringan)
                        </label>
                        <input type="text" name="nojar" id="nojar"
                               x-model="nojar"
                               :disabled="selectedActivity === 'Spare Dikta'"
                               class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all font-mono font-bold text-slate-800 uppercase">
                    </div>

                    <!-- Pelanggan -->
                    <div>
                        <label for="customer_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Pelanggan
                        </label>
                        <input type="text" name="customer_name" id="customer_name"
                               x-model="customerName"
                               :disabled="selectedActivity === 'Spare Dikta'"
                               class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all text-slate-800">
                    </div>
                </div>

                <!-- Alamat Pelanggan / Lokasi -->
                <div>
                    <label for="address" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Alamat
                    </label>
                    <input type="text" name="address" id="address"
                           x-model="address"
                           :disabled="selectedActivity === 'Spare Dikta'"
                           class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all text-slate-800">
                </div>
            </div>

            <!-- Aktivitas (Survey, PSB, Troubleshoot, Simulasi, Spare Dikta) - Posisi tepat di atas Teknisi -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                    Aktivitas <span class="text-rose-500">*</span>
                </label>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <!-- PSB -->
                    <label class="p-3 rounded-xl border-2 cursor-pointer flex items-center justify-center gap-2 transition-all has-[:checked]:border-emerald-600 has-[:checked]:bg-emerald-50/70 has-[:checked]:shadow-xs hover:border-slate-300 bg-white">
                        <input type="radio" name="activity" value="PSB" x-model="selectedActivity" {{ old('activity', 'PSB') === 'PSB' ? 'checked' : '' }} class="text-emerald-600 focus:ring-emerald-500">
                        <span class="font-bold text-xs text-slate-900">PSB</span>
                    </label>

                    <!-- Troubleshoot -->
                    <label class="p-3 rounded-xl border-2 cursor-pointer flex items-center justify-center gap-2 transition-all has-[:checked]:border-amber-600 has-[:checked]:bg-amber-50/70 has-[:checked]:shadow-xs hover:border-slate-300 bg-white">
                        <input type="radio" name="activity" value="Troubleshoot" x-model="selectedActivity" {{ old('activity') === 'Troubleshoot' ? 'checked' : '' }} class="text-amber-600 focus:ring-amber-500">
                        <span class="font-bold text-xs text-slate-900">Troubleshoot</span>
                    </label>

                    <!-- Simulasi -->
                    <label class="p-3 rounded-xl border-2 cursor-pointer flex items-center justify-center gap-2 transition-all has-[:checked]:border-purple-600 has-[:checked]:bg-purple-50/70 has-[:checked]:shadow-xs hover:border-slate-300 bg-white">
                        <input type="radio" name="activity" value="Simulasi" x-model="selectedActivity" {{ old('activity') === 'Simulasi' ? 'checked' : '' }} class="text-purple-600 focus:ring-purple-500">
                        <span class="font-bold text-xs text-slate-900">Simulasi</span>
                    </label>

                    <!-- Spare Dikta -->
                    <label class="p-3 rounded-xl border-2 cursor-pointer flex items-center justify-center gap-2 transition-all has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50/70 has-[:checked]:shadow-xs hover:border-slate-300 bg-white">
                        <input type="radio" name="activity" value="Spare Dikta" x-model="selectedActivity" {{ old('activity') === 'Spare Dikta' ? 'checked' : '' }} class="text-blue-600 focus:ring-blue-500">
                        <span class="font-bold text-xs text-slate-900">Spare Dikta</span>
                    </label>
                </div>
            </div>

            <!-- Kolom Teknisi (Jika Perangkat Ingin Dibawa Teknisi) -->
            <div class="p-4 rounded-2xl bg-amber-50/40 border border-amber-200/80">
                <label for="technician_id" class="block text-xs font-bold uppercase tracking-wider text-slate-800 mb-1.5">
                    Teknisi
                </label>

                <select name="technician_id" id="technician_id" x-model="selectedTechnician"
                        class="w-full px-4 py-2.5 text-xs bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 transition-all text-slate-800 font-medium">
                    <option value="">-- Tetap Tersimpan di Gudang HB (Tidak Dibawa Teknisi) --</option>
                    @foreach($technicians as $tech)
                        <option value="{{ $tech->id }}" {{ old('technician_id') == $tech->id ? 'selected' : '' }}>
                            {{ $tech->name }} {{ $tech->phone ? '('.$tech->phone.')' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Pilihan Lokasi Gudang HB (Jika Tidak Dibawa Teknisi) -->
            <div x-show="!selectedTechnician" class="p-4 rounded-2xl bg-emerald-50/40 border border-emerald-200/80">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-800 mb-2">
                    Lokasi Gudang Penyimpanan
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="p-3 rounded-xl border-2 cursor-pointer flex items-center gap-2.5 transition-all has-[:checked]:border-emerald-600 has-[:checked]:bg-emerald-50 has-[:checked]:shadow-xs bg-white">
                        <input type="radio" name="warehouse_hub" value="hb_jkt" checked class="text-emerald-600 focus:ring-emerald-500">
                        <div>
                            <div class="font-bold text-xs text-slate-900">Gudang HB JKT</div>
                            <div class="text-[10px] text-slate-500">Tersedia di Homebase Jakarta</div>
                        </div>
                    </label>
                    <label class="p-3 rounded-xl border-2 cursor-pointer flex items-center gap-2.5 transition-all has-[:checked]:border-teal-600 has-[:checked]:bg-teal-50 has-[:checked]:shadow-xs bg-white">
                        <input type="radio" name="warehouse_hub" value="hb_ckr" class="text-teal-600 focus:ring-teal-500">
                        <div>
                            <div class="font-bold text-xs text-slate-900">Gudang HB CKR</div>
                            <div class="text-[10px] text-slate-500">Tersedia di Homebase Cikarang</div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Catatan Tambahan -->
            <div>
                <label for="notes" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Catatan Tambahan
                </label>
                <textarea name="notes" id="notes" rows="3" 
                          class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">{{ old('notes') }}</textarea>
            </div>

            <!-- Tombol Batal & Simpan -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('admin.devices.index') }}" class="px-4 py-2.5 text-xs font-semibold text-slate-600 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-xs transition-colors">
                    <span x-text="existingDevice ? (selectedTechnician ? 'Pindahkan Alokasi ke Teknisi' : 'Perbarui Data Perangkat') : 'Simpan Perangkat & Daftarkan Stok'"></span>
                </button>
            </div>

        </form>
    </div>
</div>

@push('scripts')
<script>
function deviceRegistrationApp() {
    return {
        cameraActive: false,
        manualMode: false,
        regNumber: '{{ old('registration_number', request('reg', '')) }}',
        specCode: '{{ old('kode_spek', '') }}',
        deviceName: '{{ old('name', '') }}',
        area: '{{ old('area', '') }}',
        scannedSn: '{{ old('serial_number', request('sn', request('reg', ''))) }}',
        selectedActivity: '{{ old('activity', 'PSB') }}',
        nojar: '{{ old('nojar', '') }}',
        customerName: '{{ old('customer_name', '') }}',
        address: '{{ old('address', '') }}',
        selectedTechnician: '{{ old('technician_id', '') }}',
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

            // 1. Cek langsung di memory specsMap
            if (this.specsMap && this.specsMap[code]) {
                this.deviceName = this.specsMap[code].name;
                this.autoFilledFromSpec = true;
                return;
            }

            // 2. Cek apakah ada spec yang kodenya match
            for (const [k, v] of Object.entries(this.specsMap || {})) {
                if (code === k || code.startsWith(k) || k.startsWith(code)) {
                    this.deviceName = v.name;
                    this.autoFilledFromSpec = true;
                    return;
                }
            }

            // 3. Fallback query AJAX ke server
            fetch(`{{ route('admin.devices.check-spec') }}?spec=${encodeURIComponent(code)}`)
                .then(res => res.json())
                .then(data => {
                    if (data && data.found) {
                        this.deviceName = data.name;
                        this.autoFilledFromSpec = true;
                    }
                })
                .catch(() => {});
        },

        extractRegNumber(raw) {
            if (!raw) return '';
            const clean = raw.trim().toUpperCase();

            // Jika raw diawali dengan kode spek telko (misal B2WS..., B2WN..., B1AC...), pertahankan nomor registrasi lengkapnya!
            for (const k of Object.keys(this.specsMap || {})) {
                if (clean.startsWith(k)) {
                    return clean;
                }
            }

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

        debounceCheck(value) {
            clearTimeout(this.debounceTimer);
            this.debounceTimer = setTimeout(() => {
                this.checkExistingDevice(value);
            }, 250);
        },

        async checkExistingDevice(val, raw) {
            const reg = (val || this.regNumber || '').trim();
            const rawVal = (raw || this.scannedSn || '').trim();
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
                const response = await fetch(`{{ route('admin.devices.check-registration') }}?reg=${encodeURIComponent(reg)}&raw=${encodeURIComponent(rawVal)}`);
                const data = await response.json();
                if (data && data.exists) {
                    this.existingDevice = data;
                    this.deviceName = data.name;
                    if (data.kode_spek) {
                        this.specCode = data.kode_spek;
                    }
                    if (data.area) {
                        this.area = data.area;
                    }
                } else if (data && data.spec_found) {
                    this.existingDevice = null;
                    this.specCode = data.spec_code;
                    this.deviceName = data.name;
                    this.autoFilledFromSpec = true;
                } else {
                    this.existingDevice = null;
                }
            } catch(e) {
                this.existingDevice = null;
            }
        },

        toggleCamera() {
            if (this.cameraActive) {
                this.stopCamera();
            } else {
                this.startCamera();
            }
        },

        async startCamera() {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                alert("Perhatian: Browser HP memblokir kamera jika website dibuka melalui HTTP biasa non-localhost.\n\nSilakan buka website melalui link HTTPS Cloudflare (jalankan mulai-aplikasi-online.bat di laptop) agar izin kamera di HP otomatis aktif!");
                return;
            }

            this.cameraActive = true;
            this.$nextTick(async () => {
                const self = this;

                try {
                    if (this.html5QrCode) {
                        try { await this.html5QrCode.stop(); } catch(e) {}
                        try { await this.html5QrCode.clear(); } catch(e) {}
                    }

                    this.html5QrCode = new Html5Qrcode("linear-barcode-reader", {
                        verbose: false,
                        experimentalFeatures: {
                            useBarCodeDetectorIfSupported: true
                        }
                    });

                    // Cek daftar kamera perangkat
                    try {
                        const devices = await Html5Qrcode.getCameras();
                        if (devices && devices.length) {
                            self.cameras = devices;
                        }
                    } catch(e) {}

                    // Konfigurasi pemindaian: TANPA qrbox agar 100% area kamera dibaca dan garis barcode panjang tidak terpotong!
                    const config = {
                        fps: 25,
                        aspectRatio: 1.777778
                    };

                    const onScanSuccess = (decodedText) => {
                        const val = decodedText.trim();
                        self.scannedSn = val;
                        self.regNumber = self.extractRegNumber(val);
                        self.stopCamera();
                        self.playBeep();
                        self.checkExistingDevice(self.regNumber, val);
                    };

                    // Prioritaskan kamera belakang (environment)
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
                            throw facingErr;
                        }
                    }

                    // Cek ketersediaan senter / flash
                    try {
                        const trackCapabilities = self.html5QrCode.getRunningTrackCameraCapabilities();
                        if (trackCapabilities && trackCapabilities.torchFeature && trackCapabilities.torchFeature().isSupported()) {
                            self.torchAvailable = true;
                        }
                    } catch(e) {}

                } catch (err) {
                    console.error("Camera start error:", err);
                    alert("Tidak dapat mengakses kamera: " + (err.message || err) + "\n\nPastikan izin akses kamera telah diizinkan pada browser ponsel Anda.");
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
                        const val = decodedText.trim();
                        self.scannedSn = val;
                        self.regNumber = self.extractRegNumber(val);
                        self.stopCamera();
                        self.playBeep();
                        self.checkExistingDevice(self.regNumber, val);
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
                osc.stop(audioCtx.currentTime + 0.1);
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

        clearScan() {
            this.scannedSn = '';
            this.startCamera();
        }
    }
}
</script>
@endpush
@endsection
