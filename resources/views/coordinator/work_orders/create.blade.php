@extends('layouts.app')

@section('title', 'Tugaskan Pekerjaan Baru')
@section('page_title', 'Penugasan Pekerjaan Baru')
@section('page_subtitle', 'Tentukan NOJAR, pilih teknisi yang bertugas, dan alokasikan unit perangkat dari gudang PT')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900">Form Buat Tiket Penugasan</h2>
            <a href="{{ route('coordinator.work-orders.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">
                &larr; Batal & Kembali
            </a>
        </div>

        <form action="{{ route('coordinator.work-orders.store') }}" method="POST" class="p-6 space-y-6">
            @csrf

            <!-- Section 1: Data Tiket NOJAR -->
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-indigo-700 mb-3 flex items-center gap-1.5">
                    <span class="w-5 h-5 rounded-full bg-indigo-100 text-indigo-800 flex items-center justify-center text-[10px]">1</span>
                    <span>Informasi Pekerjaan & NOJAR</span>
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="nojar" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Nomor Jaringan (NOJAR) <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="nojar" id="nojar" required 
                               value="{{ old('nojar', '') }}"
                               class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all font-mono font-bold text-slate-900">
                        <p class="text-[11px] text-slate-400 mt-1">Nomor tiket referensi jaringan pelanggan.</p>
                    </div>

                    <div>
                        <label for="category" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Kategori Pekerjaan <span class="text-rose-500">*</span>
                        </label>
                        <select name="category" id="category" required class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">
                            <option value="Instalasi" {{ old('category') == 'Instalasi' ? 'selected' : '' }}>Instalasi Baru (Pasang ONT)</option>
                            <option value="Aktivasi" {{ old('category') == 'Aktivasi' ? 'selected' : '' }}>Aktivasi Port / Layanan</option>
                            <option value="Troubleshooting" {{ old('category') == 'Troubleshooting' ? 'selected' : '' }}>Troubleshooting / Ganti Perangkat</option>
                            <option value="Survei" {{ old('category') == 'Survei' ? 'selected' : '' }}>Survei Lokasi (Tanpa Perangkat)</option>
                            <option value="Dismantle" {{ old('category') == 'Dismantle' ? 'selected' : '' }}>Dismantle (Bongkar Perangkat Pelanggan)</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label for="title" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Judul / Deskripsi Singkat Pekerjaan <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="title" id="title" required 
                               value="{{ old('title') }}"
                               placeholder="Contoh: Pemasangan Baru Internet Fiber 50 Mbps"
                               class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all font-medium">
                    </div>
                </div>
            </div>

            <!-- Section 2: Pelanggan & Alamat Lokasi -->
            <div class="pt-4 border-t border-slate-100">
                <h3 class="text-xs font-bold uppercase tracking-wider text-indigo-700 mb-3 flex items-center gap-1.5">
                    <span class="w-5 h-5 rounded-full bg-indigo-100 text-indigo-800 flex items-center justify-center text-[10px]">2</span>
                    <span>Data Pelanggan & Alamat Lokasi</span>
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="customer_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Nama Pelanggan <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="customer_name" id="customer_name" required 
                               value="{{ old('customer_name') }}"
                               placeholder="Contoh: Bapak Suryono / PT Maju Terus"
                               class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all font-medium">
                    </div>

                    <div>
                        <label for="customer_phone" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Nomor Telepon Pelanggan
                        </label>
                        <input type="text" name="customer_phone" id="customer_phone" 
                               value="{{ old('customer_phone') }}"
                               placeholder="Contoh: 081233445566"
                               class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all font-medium">
                    </div>

                    <div class="md:col-span-2">
                        <label for="location_address" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Alamat Lengkap Lokasi Pekerjaan <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="location_address" id="location_address" rows="2" required
                                  placeholder="Nama jalan, nomor rumah/ruko, RT/RW, kelurahan, dan patokan lokasi..."
                                  class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">{{ old('location_address') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Section 3: Penetapan Teknisi & Alokasi Perangkat -->
            <div class="pt-4 border-t border-slate-100">
                <h3 class="text-xs font-bold uppercase tracking-wider text-indigo-700 mb-3 flex items-center gap-1.5">
                    <span class="w-5 h-5 rounded-full bg-indigo-100 text-indigo-800 flex items-center justify-center text-[10px]">3</span>
                    <span>Penetapan Teknisi & Alokasi Perangkat</span>
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="technician_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Pilih Teknisi Penanggung Jawab <span class="text-rose-500">*</span>
                        </label>
                        <select name="technician_id" id="technician_id" required class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">
                            <option value="">-- Pilih Teknisi Lapangan --</option>
                            @foreach($technicians as $tech)
                                <option value="{{ $tech->id }}" {{ old('technician_id') == $tech->id ? 'selected' : '' }}>
                                    {{ $tech->name }} ({{ $tech->phone }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-slate-400 mt-1">Teknisi terpilih akan melihat tiket ini di HP dan wajib mengambil perangkat di PT.</p>
                    </div>

                    <div>
                        <label for="device_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Alokasikan Unit Perangkat (Dari Gudang PT)
                        </label>
                        <select name="device_id" id="device_id" class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">
                            <option value="">-- Tidak Memerlukan Perangkat / Survei --</option>
                            @foreach($availableDevices as $dev)
                                <option value="{{ $dev->id }}" {{ old('device_id') == $dev->id ? 'selected' : '' }}>
                                    [{{ $dev->registration_number }}] - {{ $dev->name }} ({{ $dev->brand }} - {{ $dev->current_location }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-slate-400 mt-1">Hanya menampilkan unit yang siap di gudang PT. Teknisi wajib scan nomor ini saat pengambilan fisik.</p>
                    </div>
                </div>
            </div>

            <!-- Catatan Tambahan -->
            <div class="pt-4 border-t border-slate-100">
                <label for="status_notes" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Instruksi Khusus untuk Teknisi
                </label>
                <textarea name="status_notes" id="status_notes" rows="2" 
                          placeholder="Catatan koordinasi, jam janji temu dengan pelanggan, atau konfigurasi khusus..."
                          class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">{{ old('status_notes') }}</textarea>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('coordinator.work-orders.index') }}" class="px-4 py-2.5 text-xs font-semibold text-slate-600 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition-colors">
                    Simpan & Terbitkan Penugasan
                </button>
            </div>

        </form>
    </div>
</div>
@endsection
