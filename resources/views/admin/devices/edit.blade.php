@extends('layouts.app')

@section('title', 'Ubah Data Perangkat ' . $device->registration_number)
@section('page_title', 'Ubah Data Perangkat')
@section('page_subtitle', 'Edit informasi master, status stok, atau kondisi perangkat ' . $device->registration_number)

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900">Edit Perangkat: <span class="font-mono text-brand-700">{{ $device->registration_number }}</span></h2>
            <a href="{{ route('admin.devices.show', $device) }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">
                &larr; Batal & Kembali
            </a>
        </div>

        <form action="{{ route('admin.devices.update', $device) }}" method="POST" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                
                <div>
                    <label for="registration_number" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Nomor Registrasi <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="registration_number" id="registration_number" required 
                           value="{{ old('registration_number', $device->registration_number) }}"
                           class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all font-mono font-bold text-brand-700">
                </div>

                <input type="hidden" name="kode_spek" id="kode_spek" value="{{ old('kode_spek', $device->kode_spek) }}">

                <div>
                    <label for="serial_number" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Serial Number (SN) <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="serial_number" id="serial_number" required 
                           value="{{ old('serial_number', $device->serial_number) }}"
                           class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all font-mono">
                </div>

                <div>
                    <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Nama Perangkat <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name" id="name" required 
                           value="{{ old('name', $device->name) }}"
                           class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
                </div>

                <div>
                    <label for="brand" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Merk / Pabrikan <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="brand" id="brand" required 
                           value="{{ old('brand', $device->brand) }}"
                           class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
                </div>

                <div>
                    <label for="model" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Model / Tipe Spesifik
                    </label>
                    <input type="text" name="model" id="model" 
                           value="{{ old('model', $device->model) }}"
                           class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
                </div>

                <div>
                    <label for="type" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Kategori Perangkat <span class="text-rose-500">*</span>
                    </label>
                    <select name="type" id="type" required class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
                        <option value="ONT" {{ old('type', $device->type) == 'ONT' ? 'selected' : '' }}>ONT / Modem Fiber Optik</option>
                        <option value="Router" {{ old('type', $device->type) == 'Router' ? 'selected' : '' }}>Router / MikroTik</option>
                        <option value="Switch" {{ old('type', $device->type) == 'Switch' ? 'selected' : '' }}>Switch Hub / Managed Switch</option>
                        <option value="Access Point" {{ old('type', $device->type) == 'Access Point' ? 'selected' : '' }}>Access Point Wi-Fi</option>
                        <option value="Modem" {{ old('type', $device->type) == 'Modem' ? 'selected' : '' }}>Modem</option>
                        <option value="Lainnya" {{ old('type', $device->type) == 'Lainnya' ? 'selected' : '' }}>Lain-lain</option>
                    </select>
                </div>

                <div>
                    <label for="stock_status" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Status Stok <span class="text-rose-500">*</span>
                    </label>
                    <select name="stock_status" id="stock_status" required class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
                        <option value="tersedia_hb_jkt" {{ in_array(old('stock_status', $device->stock_status), ['tersedia_hb_jkt', 'tersedia_pt', 'tersedia_hb_dikta']) ? 'selected' : '' }}>Tersedia di HB JKT</option>
                        <option value="tersedia_hb_ckr" {{ old('stock_status', $device->stock_status) === 'tersedia_hb_ckr' ? 'selected' : '' }}>Tersedia di HB CKR</option>
                        <option value="dibawa_teknisi" {{ old('stock_status', $device->stock_status) == 'dibawa_teknisi' ? 'selected' : '' }}>Dibawa Teknisi</option>
                        <option value="terpasang_pelanggan" {{ in_array(old('stock_status', $device->stock_status), ['terpasang_pelanggan', 'terinstal']) ? 'selected' : '' }}>Terinstal</option>
                        <option value="pending" {{ old('stock_status', $device->stock_status) == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="rusak" {{ old('stock_status', $device->stock_status) == 'rusak' ? 'selected' : '' }}>Rusak</option>
                        <option value="hilang" {{ old('stock_status', $device->stock_status) == 'hilang' ? 'selected' : '' }}>Hilang</option>
                        <option value="dismantle" {{ old('stock_status', $device->stock_status) == 'dismantle' ? 'selected' : '' }}>Dismantle (Bongkaran)</option>
                    </select>
                </div>

                <div>
                    <label for="condition" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Kondisi Fisik <span class="text-rose-500">*</span>
                    </label>
                    <select name="condition" id="condition" required class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
                        <option value="baru" {{ old('condition', $device->condition) == 'baru' ? 'selected' : '' }}>Baru</option>
                        <option value="bekas_bagus" {{ old('condition', $device->condition) == 'bekas_bagus' ? 'selected' : '' }}>Bekas Bagus</option>
                        <option value="rusak" {{ old('condition', $device->condition) == 'rusak' ? 'selected' : '' }}>Rusak</option>
                    </select>
                </div>

                <!-- Area -->
                <div>
                    <label for="area" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Area
                    </label>
                    <select name="area" id="area" class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all text-slate-800 font-semibold">
                        <option value="">-- Pilih Area --</option>
                        @foreach(['BWA', 'BDA', 'SEOA', 'SAST', 'CDA', 'NEA'] as $a)
                            <option value="{{ $a }}" {{ old('area', $device->area) == $a ? 'selected' : '' }}>
                                {{ $a }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- NOJAR & Pelanggan -->
                <div>
                    <label for="nojar" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        NOJAR (Nomor Jaringan)
                    </label>
                    <input type="text" name="nojar" id="nojar"
                           value="{{ old('nojar', $device->nojar) }}"
                           placeholder="Contoh: NJR001"
                           class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all font-mono font-bold text-slate-800 uppercase">
                </div>

                <div>
                    <label for="customer_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Pelanggan
                    </label>
                    <input type="text" name="customer_name" id="customer_name"
                           value="{{ old('customer_name', $device->customer_name) }}"
                           placeholder="Contoh: Bapak Suryono / PT Mitra Abadi"
                           class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all text-slate-800">
                </div>

                <!-- Alamat -->
                <div class="md:col-span-2">
                    <label for="address" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Alamat
                    </label>
                    <input type="text" name="address" id="address"
                           value="{{ old('address', $device->address) }}"
                           placeholder="Contoh: Jl. Melati No. 12, RT 02/RW 05, Kel. Sukamaju"
                           class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all text-slate-800">
                </div>

                <!-- Aktivitas -->
                <div>
                    <label for="activity" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Aktivitas Terkait
                    </label>
                    <select name="activity" id="activity" class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
                        <option value="">-- Tidak Ada / Default --</option>
                        <option value="PSB" {{ old('activity', $device->activity) == 'PSB' ? 'selected' : '' }}>PSB</option>
                        <option value="Troubleshoot" {{ old('activity', $device->activity) == 'Troubleshoot' ? 'selected' : '' }}>Troubleshoot</option>
                        <option value="Simulasi" {{ old('activity', $device->activity) == 'Simulasi' ? 'selected' : '' }}>Simulasi</option>
                        <option value="Spare Dikta" {{ old('activity', $device->activity) == 'Spare Dikta' ? 'selected' : '' }}>Spare Dikta</option>
                    </select>
                </div>

                <!-- Teknisi Penanggung Jawab -->
                <div>
                    <label for="current_technician_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Teknisi Penanggung Jawab
                    </label>
                    <select name="current_technician_id" id="current_technician_id" class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all text-slate-800">
                        <option value="">-- Tidak Ada (Tersimpan di Gudang / Terpasang) --</option>
                        @foreach($technicians as $tech)
                            <option value="{{ $tech->id }}" {{ old('current_technician_id', $device->current_technician_id) == $tech->id ? 'selected' : '' }}>
                                {{ $tech->name }} {{ $tech->phone ? '('.$tech->phone.')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label for="current_location" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Lokasi / Posisi Terkini <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="current_location" id="current_location" required 
                           value="{{ old('current_location', $device->current_location) }}"
                           class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
                </div>

            </div>

            <div>
                <label for="notes" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Catatan Tambahan
                </label>
                <textarea name="notes" id="notes" rows="3" 
                          class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">{{ old('notes', $device->notes) }}</textarea>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('admin.devices.show', $device) }}" class="px-4 py-2.5 text-xs font-semibold text-slate-600 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 text-xs font-bold text-white bg-brand-600 hover:bg-brand-700 rounded-xl shadow-xs transition-colors">
                    Perbarui Data Perangkat
                </button>
            </div>

        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const specInput = document.getElementById('kode_spek');
    const nameInput = document.getElementById('name');
    const specsMap = {!! json_encode($specs->keyBy('spec_code')->map(fn($s) => ['name' => $s->name, 'brand' => $s->brand, 'type' => $s->type])) !!};

    if (specInput && nameInput) {
        specInput.addEventListener('input', function() {
            const val = this.value.trim().toUpperCase();
            if (!val) return;
            if (specsMap[val]) {
                nameInput.value = specsMap[val].name;
            } else {
                for (const [k, v] of Object.entries(specsMap)) {
                    if (val === k || val.startsWith(k)) {
                        nameInput.value = v.name;
                        break;
                    }
                }
            }
        });
    }
});
</script>
@endpush
@endsection
