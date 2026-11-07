@extends('layouts.app')

@section('title', 'Pendaftaran Perangkat Baru')
@section('page_title', 'Form Pendaftaran Perangkat')
@section('page_subtitle', 'Lengkapi detail registrasi dan alokasi pekerjaan perangkat PT DIKTA')

@section('content')
<div class="max-w-4xl mx-auto" x-data="deviceRegistrationApp()">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        
        <!-- Header Form -->
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Form Pendaftaran Perangkat</h2>
                <p class="text-xs text-slate-500 mt-0.5">Lengkapi detail registrasi perangkat</p>
            </div>
            <a href="{{ route('admin.devices.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">
                &larr; Kembali ke Daftar
            </a>
        </div>

        <form action="{{ route('admin.devices.store') }}" method="POST" class="p-6 space-y-6" id="deviceForm">
            @csrf

            <!-- Hidden Serial Number simpan nomor registrasi -->
            <input type="hidden" name="serial_number" :value="regNumber">

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
        regNumber: '{{ old('registration_number', request('reg', '')) }}',
        specCode: '{{ old('kode_spek', '') }}',
        deviceName: '{{ old('name', '') }}',
        area: '{{ old('area', '') }}',
        selectedActivity: '{{ old('activity', 'PSB') }}',
        nojar: '{{ old('nojar', '') }}',
        customerName: '{{ old('customer_name', '') }}',
        address: '{{ old('address', '') }}',
        selectedTechnician: '{{ old('technician_id', '') }}',
        existingDevice: null,
        autoFilledFromSpec: false,
        debounceTimer: null,
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

        debounceCheck(value) {
            clearTimeout(this.debounceTimer);
            this.debounceTimer = setTimeout(() => {
                this.checkExistingDevice(value);
            }, 250);
        },

        async checkExistingDevice(val) {
            const reg = (val || this.regNumber || '').trim();
            if (!reg) {
                this.existingDevice = null;
                return;
            }

            // Coba deteksi kode spek langsung dari prefix registrasi
            let matchedSpec = null;
            const cUpper = reg.toUpperCase();
            for (const [k, v] of Object.entries(this.specsMap || {})) {
                if (cUpper.startsWith(k)) {
                    matchedSpec = { code: k, ...v };
                    break;
                }
            }

            if (matchedSpec) {
                this.specCode = matchedSpec.code;
                this.deviceName = matchedSpec.name;
                this.autoFilledFromSpec = true;
            }

            try {
                const response = await fetch(`{{ route('admin.devices.check-registration') }}?reg=${encodeURIComponent(reg)}`);
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
        }
    }
}
</script>
@endpush
@endsection
