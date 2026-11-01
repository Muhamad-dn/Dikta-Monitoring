@extends('layouts.app')

@section('title', 'Detail Tugas ' . $task->nojar)
@section('page_title', 'Tiket Pekerjaan: ' . $task->nojar)
@section('page_subtitle', $task->title)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Primary Status & Action Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="font-mono text-lg font-black text-slate-900">{{ $task->nojar }}</span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $task->category_badge_class }}">
                        {{ $task->category }}
                    </span>
                </div>
                <h3 class="text-sm font-bold text-slate-800">{{ $task->title }}</h3>
            </div>
            <div>
                <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $task->status_badge_class }}">
                    {{ $task->status_label }}
                </span>
            </div>
        </div>

        <!-- Next Action Button (Prominent Call to Action) -->
        <div class="pt-5">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-2">Langkah Tindakan Anda Selanjutnya:</span>
            
            @if($task->status === 'belum_dikerjakan')
                <div class="p-4 rounded-xl bg-amber-500/10 border border-amber-300">
                    <p class="text-xs text-amber-900 font-semibold mb-3">
                        Perangkat fisik belum divalidasi. Anda wajib mengambil dan memindai barcode unit di gudang PT DIKTA sebelum berangkat.
                    </p>
                    <a href="{{ route('technician.tasks.scan-pickup', $task) }}" 
                       class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-700 hover:to-orange-700 text-white font-bold text-sm shadow-md shadow-amber-600/20 text-center flex items-center justify-center gap-2 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        <span>Scan & Validasi Pengambilan Perangkat di PT &rarr;</span>
                    </a>
                </div>

            @elseif($task->status === 'perangkat_diambil')
                <form action="{{ route('technician.tasks.flow', $task) }}" method="POST">
                    @csrf
                    <input type="hidden" name="action" value="start_journey">
                    <button type="submit" class="w-full py-3 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm shadow-md shadow-blue-600/20 text-center flex items-center justify-center gap-2 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        <span>Mulai Perjalanan ke Lokasi Pelanggan &rarr;</span>
                    </button>
                </form>

            @elseif($task->status === 'dalam_perjalanan')
                <form action="{{ route('technician.tasks.flow', $task) }}" method="POST">
                    @csrf
                    <input type="hidden" name="action" value="arrived">
                    <button type="submit" class="w-full py-3 px-4 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white font-bold text-sm shadow-md shadow-cyan-600/20 text-center flex items-center justify-center gap-2 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Saya Sudah Tiba di Alamat Lokasi &rarr;</span>
                    </button>
                </form>

            @elseif($task->status === 'sampai_lokasi')
                <form action="{{ route('technician.tasks.flow', $task) }}" method="POST">
                    @csrf
                    <input type="hidden" name="action" value="start_work">
                    <button type="submit" class="w-full py-3 px-4 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-sm shadow-md shadow-amber-600/20 text-center flex items-center justify-center gap-2 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/></svg>
                        <span>Mulai Pengerjaan Instalasi Fisik &rarr;</span>
                    </button>
                </form>

            @elseif($task->status === 'sedang_dikerjakan')
                <a href="{{ route('technician.tasks.complete', $task) }}" 
                   class="w-full py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow-md shadow-emerald-600/20 text-center flex items-center justify-center gap-2 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Selesaikan / Laporkan Hasil & Upload Bukti Foto &rarr;</span>
                </a>

            @elseif(in_array($task->status, ['terinstal', 'selesai']))
                <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs text-center font-semibold">
                    &check; Pekerjaan ini telah selesai dilaporkan dan perangkat telah tercatat terpasang di lokasi pelanggan.
                </div>
            @endif
        </div>
    </div>

    <!-- Informasi Pelanggan & Alamat -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-4">
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Data Pelanggan & Lokasi Tujuan</h3>
        
        <div class="space-y-3 text-xs">
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-bold">Nama Pelanggan</span>
                <strong class="text-sm text-slate-900">{{ $task->customer_name }}</strong>
            </div>

            @if($task->customer_phone)
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">No. WhatsApp / Telp</span>
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $task->customer_phone) }}" target="_blank"
                       class="font-semibold text-emerald-700 hover:underline inline-flex items-center gap-1 mt-0.5">
                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                        <span>{{ $task->customer_phone }} (Kirim Chat WhatsApp)</span>
                    </a>
                </div>
            @endif

            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-bold">Alamat Pekerjaan</span>
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 mt-1 font-medium text-slate-800">
                    {{ $task->location_address }}
                </div>
            </div>

            <div class="pt-2">
                <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($task->location_address) }}" target="_blank"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 transition-colors shadow-xs">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Buka Rute di Google Maps &rarr;</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Perangkat Yang Ditugaskan -->
    @if($task->device)
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Unit Perangkat Pekerjaan Ini</h3>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold border {{ $task->device->status_badge_class }}">
                    {{ $task->device->stock_status_label }}
                </span>
            </div>

            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-sm font-extrabold text-slate-900">{{ $task->device->name }}</h4>
                    <p class="text-xs text-slate-500 font-mono">SN: {{ $task->device->serial_number }} &bull; Tipe: {{ $task->device->type }}</p>
                </div>
                <div class="text-right">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block">No. Registrasi</span>
                    <span class="font-mono text-lg font-black text-brand-700">{{ $task->device->registration_number }}</span>
                </div>
            </div>

            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs flex items-center gap-1.5">
                <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>Posisi Fisik Tercatat: <strong>{{ $task->device->current_location }}</strong></span>
            </div>
        </div>
    @endif

    <!-- Foto Evidence Yang Sudah Diunggah -->
    @if($task->evidence->count() > 0)
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Foto Evidence Terunggah</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                @foreach($task->evidence as $evi)
                    <div class="rounded-xl border border-slate-200 overflow-hidden bg-slate-50">
                        <div class="h-32 bg-slate-950 overflow-hidden">
                            <img src="{{ asset('storage/' . $evi->photo_path) }}" 
                                 class="w-full h-full object-cover"
                                 onerror="this.onerror=null; this.src='https://placehold.co/600x400/0f172a/cbd5e1?text=Evidence';">
                        </div>
                        <div class="p-2 text-[10px]">
                            <span class="font-bold text-slate-800 block truncate">{{ $evi->photo_type_label }}</span>
                            <span class="text-slate-400 font-mono">{{ $evi->created_at->format('H:i') }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>
@endsection
