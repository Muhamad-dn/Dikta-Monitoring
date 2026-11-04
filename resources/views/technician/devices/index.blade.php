@extends('layouts.app')

@section('title', 'Perangkat Saya - Teknisi PT DIKTA')
@section('page_title', 'Perangkat Saya')
@section('page_subtitle', 'Daftar fisik unit perangkat PT DIKTA dalam tanggung jawab Anda di lapangan')

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="technicianMyDevicesApp()">

    <!-- Header Actions & Peringatan Tanggung Jawab -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <h2 class="text-base font-extrabold text-slate-900">Perangkat Dalam Tanggung Jawab Saya</h2>
            <p class="text-xs text-slate-500 mt-0.5">Seluruh unit terintegrasi langsung dengan Dashboard Administrator</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('technician.devices.create') }}" 
               class="px-4 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-md shadow-amber-600/20 transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>+ Daftarkan Perangkat</span>
            </a>
            <a href="{{ route('technician.dashboard') }}#section-update-status" 
               class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-xs transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Update Status Lapangan</span>
            </a>
        </div>
    </div>

    <!-- Tabel Perangkat Saya -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-700 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5">NAMA PERANGKAT</th>
                        <th class="px-5 py-3.5">NO REGISTRASI</th>
                        <th class="px-5 py-3.5">NOJAR</th>
                        <th class="px-5 py-3.5">PELANGGAN</th>
                        <th class="px-5 py-3.5 text-center">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($devices as $dev)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <!-- NAMA PERANGKAT -->
                            <td class="px-5 py-3.5 font-bold text-slate-900">
                                <div>{{ $dev->name }}</div>
                                <div class="text-[10px] font-normal text-slate-500 mt-0.5">
                                    {{ $dev->brand }} ({{ $dev->type }}) &bull; Area: <strong class="text-slate-700">{{ $dev->area ?: 'BWA' }}</strong>
                                </div>
                            </td>

                            <!-- NO REGISTRASI -->
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <span class="font-mono font-black text-slate-900 text-xs bg-slate-100 px-2.5 py-1 rounded border border-slate-200 block w-fit">
                                    {{ $dev->registration_number }}
                                </span>
                                @if($dev->serial_number && $dev->serial_number !== $dev->registration_number)
                                    <span class="text-[10px] text-slate-400 font-mono block mt-0.5">SN: {{ $dev->serial_number }}</span>
                                @endif
                            </td>

                            <!-- NOJAR -->
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                @if($dev->nojar)
                                    <span class="font-mono font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded border border-blue-200">
                                        {{ $dev->nojar }}
                                    </span>
                                @elseif($dev->activeWorkOrder)
                                    <span class="font-mono font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded border border-blue-200">
                                        {{ $dev->activeWorkOrder->nojar }}
                                    </span>
                                @else
                                    <span class="text-slate-400 italic">Belum Ada NOJAR</span>
                                @endif
                            </td>

                            <!-- PELANGGAN -->
                            <td class="px-5 py-3.5">
                                @php
                                    $custName = $dev->customer_name ?: ($dev->activeWorkOrder ? $dev->activeWorkOrder->customer_name : null);
                                    $custAddr = $dev->address ?: ($dev->activeWorkOrder ? $dev->activeWorkOrder->location_address : null);
                                @endphp
                                @if($custName)
                                    <div class="font-semibold text-slate-900">{{ $custName }}</div>
                                    @if($custAddr)
                                        <div class="text-[11px] text-slate-500 max-w-xs truncate" title="{{ $custAddr }}">{{ $custAddr }}</div>
                                    @endif
                                @else
                                    <span class="text-slate-400 italic">Belum Ada Pelanggan</span>
                                @endif
                            </td>

                            <!-- AKSI -->
                            <td class="px-5 py-3.5 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                    <!-- 1. Tombol HANDOVER -->
                                    <button type="button" 
                                            @click="openHandoverModal({{ json_encode($dev) }})"
                                            title="Pindahkan ke teknisi lain"
                                            class="px-2.5 py-1.5 rounded-lg text-xs font-bold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 transition-colors flex items-center gap-1 shadow-xs">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                        <span>Handover</span>
                                    </button>

                                    <!-- 2. Tombol KEMBALIKAN (Seperti di Admin) -->
                                    <button type="button" 
                                            @click="openReturnModal({{ json_encode($dev) }})"
                                            title="Kembalikan perangkat (Gudang HB / Lintasarta)"
                                            class="px-2.5 py-1.5 rounded-lg text-xs font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-300 transition-colors flex items-center gap-1 shadow-xs">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                        <span>Kembalikan</span>
                                    </button>

                                    <!-- 3. Tombol UPDATE STATUS -->
                                    <a href="{{ route('technician.dashboard') }}#section-update-status"
                                       class="px-2.5 py-1.5 rounded-lg text-xs font-bold text-amber-800 bg-amber-50 hover:bg-amber-100 border border-amber-300 transition-colors flex items-center gap-1 shadow-xs">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        <span>Update Status</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                Saat ini Anda tidak sedang membawa unit perangkat fisik apapun.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL 1: HANDOVER KE TEKNISI LAIN -->
    <div x-show="handoverModal" x-cloak class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4" @click.outside="handoverModal = false">
            <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                <div class="w-9 h-9 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">Handover Perangkat ke Teknisi Lain</h3>
                    <p class="text-xs text-slate-500">Pindahkan tanggung jawab unit ke rekan teknisi</p>
                </div>
            </div>

            <!-- Detail Unit -->
            <div class="p-3 bg-indigo-50/50 border border-indigo-200 rounded-xl text-xs space-y-1">
                <div class="font-bold text-slate-900">
                    <span class="font-mono text-indigo-700" x-text="selectedDevice?.registration_number"></span>
                    &bull; <span x-text="selectedDevice?.name"></span>
                </div>
                <div class="text-[11px] text-slate-500" x-show="selectedDevice?.nojar">
                    NOJAR: <span class="font-mono font-semibold text-slate-800" x-text="selectedDevice?.nojar"></span>
                </div>
            </div>

            <form :action="'{{ url('technician/devices') }}/' + (selectedDevice?.id || 0) + '/handover'" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="handover_technician_id_page" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Pilih Teknisi Tujuan <span class="text-rose-500">*</span>
                    </label>
                    <select name="to_technician_id" id="handover_technician_id_page" required 
                            class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-semibold text-slate-800">
                        <option value="">-- Pilih Rekan Teknisi --</option>
                        @foreach($otherTechnicians as $tech)
                            <option value="{{ $tech->id }}">{{ $tech->name }} (Area: {{ $tech->area ?: 'BWA' }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="handover_notes_page" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Catatan / Alasan Handover
                    </label>
                    <textarea name="notes" id="handover_notes_page" rows="2" placeholder="Alasan perpindahan unit..."
                              class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="handoverModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-xs">
                        Konfirmasi Handover
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: KEMBALIKAN PERANGKAT (PERSIS SEPERTI DI ADMIN) -->
    <div x-show="returnModal" x-cloak class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4" @click.outside="returnModal = false">
            <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">Kembalikan Perangkat</h3>
                    <p class="text-xs text-slate-500">Kembalikan ke HB Dikta / Gudang HB (JKT / CKR) atau Lintasarta</p>
                </div>
            </div>

            <!-- Detail Perangkat Terpilih -->
            <div class="p-3 bg-emerald-50/50 border border-emerald-200 rounded-xl text-xs space-y-1">
                <div class="font-bold text-slate-900">
                    <span class="font-mono text-emerald-700" x-text="selectedDevice?.registration_number"></span>
                    &bull; <span x-text="selectedDevice?.name"></span>
                </div>
            </div>

            <form :action="'{{ url('technician/devices') }}/' + (selectedDevice?.id || 0) + '/return'" method="POST" class="space-y-4">
                @csrf
                
                <!-- Tujuan Pengembalian -->
                <div>
                    <label for="page_return_destination" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Tujuan Pengembalian <span class="text-rose-500">*</span>
                    </label>
                    <select name="destination" id="page_return_destination" required 
                            class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 font-semibold text-slate-800">
                        <option value="Gudang HB JKT">Gudang HB JKT (Homebase Jakarta)</option>
                        <option value="Gudang HB CKR">Gudang HB CKR (Homebase Cikarang)</option>
                        <option value="Gudang Tekno">Lintasarta - Gudang Tekno (Gudang Utama)</option>
                        <option value="BWA">Lintasarta - Area BWA</option>
                        <option value="BDA">Lintasarta - Area BDA</option>
                        <option value="SEOA">Lintasarta - Area SEOA</option>
                        <option value="SAST">Lintasarta - Area SAST</option>
                        <option value="CDA">Lintasarta - Area CDA</option>
                        <option value="NEA">Lintasarta - Area NEA</option>
                    </select>
                </div>

                <!-- Kondisi Fisik Unit -->
                <div>
                    <label for="page_return_condition" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Kondisi Fisik Unit <span class="text-rose-500">*</span>
                    </label>
                    <select name="condition" id="page_return_condition" required 
                            class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 font-semibold text-slate-800">
                        <option value="baru">Baru / Normal (Masih Segel/Baik)</option>
                        <option value="bekas_bagus" selected>Bekas Bagus / Normal</option>
                        <option value="rusak">Rusak (Perlu Cek Lab / Servis)</option>
                    </select>
                </div>

                <!-- Catatan Pengembalian -->
                <div>
                    <label for="page_return_notes" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Catatan Pengembalian (Opsional)
                    </label>
                    <textarea name="return_notes" id="page_return_notes" rows="2" placeholder="Catatan pengembalian atau nomor surat jalan..."
                              class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="returnModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs">
                        Konfirmasi Kembalikan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
function technicianMyDevicesApp() {
    return {
        handoverModal: false,
        returnModal: false,
        selectedDevice: null,

        openHandoverModal(device) {
            this.selectedDevice = device;
            this.handoverModal = true;
        },

        openReturnModal(device) {
            this.selectedDevice = device;
            this.returnModal = true;
        }
    };
}
</script>
@endpush
@endsection
