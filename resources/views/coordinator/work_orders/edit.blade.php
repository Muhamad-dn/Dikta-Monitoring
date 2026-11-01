@extends('layouts.app')

@section('title', 'Ubah Penugasan NOJAR ' . $workOrder->nojar)
@section('page_title', 'Ubah Tiket Penugasan')
@section('page_subtitle', 'Edit data pekerjaan, ganti teknisi, atau sesuaikan alokasi perangkat untuk ' . $workOrder->nojar)

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900">Edit NOJAR: <span class="font-mono text-emerald-700">{{ $workOrder->nojar }}</span></h2>
            <a href="{{ route('coordinator.work-orders.show', $workOrder) }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">
                &larr; Batal & Kembali
            </a>
        </div>

        <form action="{{ route('coordinator.work-orders.update', $workOrder) }}" method="POST" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <!-- Informasi Pekerjaan -->
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
                               value="{{ old('nojar', $workOrder->nojar) }}"
                               class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all font-mono font-bold text-slate-900">
                    </div>

                    <div>
                        <label for="category" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Kategori Pekerjaan <span class="text-rose-500">*</span>
                        </label>
                        <select name="category" id="category" required class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">
                            <option value="Instalasi" {{ old('category', $workOrder->category) == 'Instalasi' ? 'selected' : '' }}>Instalasi</option>
                            <option value="Aktivasi" {{ old('category', $workOrder->category) == 'Aktivasi' ? 'selected' : '' }}>Aktivasi</option>
                            <option value="Troubleshooting" {{ old('category', $workOrder->category) == 'Troubleshooting' ? 'selected' : '' }}>Troubleshooting</option>
                            <option value="Survei" {{ old('category', $workOrder->category) == 'Survei' ? 'selected' : '' }}>Survei</option>
                            <option value="Dismantle" {{ old('category', $workOrder->category) == 'Dismantle' ? 'selected' : '' }}>Dismantle</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label for="title" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Judul Pekerjaan <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="title" id="title" required 
                               value="{{ old('title', $workOrder->title) }}"
                               class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all font-medium">
                    </div>
                </div>
            </div>

            <!-- Pelanggan & Alamat -->
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
                               value="{{ old('customer_name', $workOrder->customer_name) }}"
                               class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all font-medium">
                    </div>

                    <div>
                        <label for="customer_phone" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Nomor Telepon Pelanggan
                        </label>
                        <input type="text" name="customer_phone" id="customer_phone" 
                               value="{{ old('customer_phone', $workOrder->customer_phone) }}"
                               class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all font-medium">
                    </div>

                    <div class="md:col-span-2">
                        <label for="location_address" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Alamat Lengkap Lokasi <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="location_address" id="location_address" rows="2" required
                                  class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">{{ old('location_address', $workOrder->location_address) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Teknisi & Perangkat -->
            <div class="pt-4 border-t border-slate-100">
                <h3 class="text-xs font-bold uppercase tracking-wider text-indigo-700 mb-3 flex items-center gap-1.5">
                    <span class="w-5 h-5 rounded-full bg-indigo-100 text-indigo-800 flex items-center justify-center text-[10px]">3</span>
                    <span>Penetapan Teknisi & Alokasi Perangkat</span>
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="technician_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Teknisi Penanggung Jawab <span class="text-rose-500">*</span>
                        </label>
                        <select name="technician_id" id="technician_id" required class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">
                            @foreach($technicians as $tech)
                                <option value="{{ $tech->id }}" {{ old('technician_id', $workOrder->technician_id) == $tech->id ? 'selected' : '' }}>
                                    {{ $tech->name }} ({{ $tech->phone }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="device_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Alokasikan Unit Perangkat
                        </label>
                        <select name="device_id" id="device_id" class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">
                            <option value="">-- Tidak Memerlukan Perangkat --</option>
                            @foreach($availableDevices as $dev)
                                <option value="{{ $dev->id }}" {{ old('device_id', $workOrder->device_id) == $dev->id ? 'selected' : '' }}>
                                    [{{ $dev->registration_number }}] - {{ $dev->name }} ({{ $dev->brand }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div>
                <label for="status_notes" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Catatan / Instruksi Khusus
                </label>
                <textarea name="status_notes" id="status_notes" rows="2" 
                          class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">{{ old('status_notes', $workOrder->status_notes) }}</textarea>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('coordinator.work-orders.show', $workOrder) }}" class="px-4 py-2.5 text-xs font-semibold text-slate-600 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition-colors">
                    Simpan Perubahan
                </button>
            </div>

        </form>
    </div>
</div>
@endsection
