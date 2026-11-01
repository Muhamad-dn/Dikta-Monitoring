@extends('layouts.app')

@section('title', 'Monitor Tiket NOJAR ' . $workOrder->nojar)
@section('page_title', 'Monitoring Pekerjaan: ' . $workOrder->nojar)
@section('page_subtitle', 'Pantau kemajuan alur teknisi, validasi perangkat, dan bukti foto evidence')

@section('content')
<div class="space-y-6">

    <!-- Top Card: Header & Status Summary -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 pb-5 border-b border-slate-100">
            <div>
                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <span class="font-mono text-xl font-extrabold text-slate-900">{{ $workOrder->nojar }}</span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $workOrder->category_badge_class }}">
                        {{ $workOrder->category }}
                    </span>
                    <span class="px-3 py-0.5 rounded-full text-xs font-bold border {{ $workOrder->status_badge_class }}">
                        {{ $workOrder->status_label }}
                    </span>
                </div>
                <h2 class="text-base font-bold text-slate-800">{{ $workOrder->title }}</h2>
                <p class="text-xs text-slate-500 mt-0.5">Dibuat oleh Koordinator: <strong>{{ $workOrder->coordinator ? $workOrder->coordinator->name : 'Koordinator' }}</strong> &bull; {{ $workOrder->created_at->format('d M Y H:i') }}</p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('coordinator.work-orders.edit', $workOrder) }}" class="px-3 py-2 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                    Edit Tiket
                </a>
                <a href="{{ route('coordinator.work-orders.index') }}" class="px-3 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800">
                    &larr; Daftar Pekerjaan
                </a>
            </div>
        </div>

        <!-- Workflow Stepper / Progression Bar -->
        <div class="pt-6">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4">Tahapan Alur Lapangan</h3>
            
            @php
                $stages = [
                    'belum_dikerjakan' => ['label' => 'Belum Diambil', 'desc' => 'Di Gudang PT'],
                    'perangkat_diambil' => ['label' => 'Diambil di PT', 'desc' => 'Scan Validasi Sukses'],
                    'dalam_perjalanan' => ['label' => 'Perjalanan', 'desc' => 'Menuju Lokasi'],
                    'sampai_lokasi' => ['label' => 'Sampai Lokasi', 'desc' => 'Tiba di Pelanggan'],
                    'sedang_dikerjakan' => ['label' => 'Dikerjakan', 'desc' => 'Pemasangan Aktif'],
                    'terinstal' => ['label' => 'Terinstal / Selesai', 'desc' => 'Evidence Terunggah'],
                ];
                $orderSteps = array_keys($stages);
                $currentIndex = array_search($workOrder->status, $orderSteps);
                if ($currentIndex === false && in_array($workOrder->status, ['selesai'])) $currentIndex = 5;
            @endphp

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2">
                @foreach($stages as $key => $stage)
                    @php
                        $stepIndex = $loop->index;
                        $isCompleted = ($currentIndex !== false && $stepIndex <= $currentIndex && !in_array($workOrder->status, ['pending', 'gagal_install']));
                        $isCurrent = ($currentIndex !== false && $stepIndex == $currentIndex);
                    @endphp
                    <div class="p-3 rounded-xl border text-center transition-all {{ $isCompleted ? 'bg-emerald-50 border-emerald-300 text-emerald-900' : ($isCurrent ? 'bg-amber-50 border-amber-300 text-amber-900 ring-2 ring-amber-400' : 'bg-slate-50 border-slate-200 text-slate-400') }}">
                        <div class="w-6 h-6 mx-auto rounded-full text-xs font-bold flex items-center justify-center mb-1 {{ $isCompleted ? 'bg-emerald-600 text-white' : ($isCurrent ? 'bg-amber-500 text-white animate-pulse' : 'bg-slate-200 text-slate-600') }}">
                            @if($isCompleted)
                                &check;
                            @else
                                {{ $loop->iteration }}
                            @endif
                        </div>
                        <div class="font-bold text-[11px] leading-tight">{{ $stage['label'] }}</div>
                        <div class="text-[9px] mt-0.5 opacity-80">{{ $stage['desc'] }}</div>
                    </div>
                @endforeach
            </div>

            @if(in_array($workOrder->status, ['pending', 'gagal_install']))
                <div class="mt-4 p-3.5 rounded-xl {{ $workOrder->status === 'pending' ? 'bg-yellow-50 border border-yellow-300 text-yellow-900' : 'bg-rose-50 border border-rose-300 text-rose-900' }} text-xs">
                    <strong class="font-bold uppercase tracking-wider block mb-1">
                        Status Saat Ini: {{ $workOrder->status_label }}
                    </strong>
                    <p class="italic">"{{ $workOrder->status_notes }}"</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Info Grid: Pelanggan, Teknisi, Perangkat -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        
        <!-- Pelanggan & Lokasi -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block mb-2">Pelanggan & Lokasi</span>
                <h4 class="text-sm font-bold text-slate-900">{{ $workOrder->customer_name }}</h4>
                @if($workOrder->customer_phone)
                    <div class="text-xs text-slate-600 mt-1 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        <span>{{ $workOrder->customer_phone }}</span>
                    </div>
                @endif
                <div class="text-xs text-slate-700 mt-2 p-3 bg-slate-50 rounded-xl border border-slate-200">
                    <span class="text-slate-400 block text-[10px] uppercase font-bold mb-0.5">Alamat Lengkap:</span>
                    {{ $workOrder->location_address }}
                </div>
            </div>
            <div class="mt-3 pt-2">
                <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($workOrder->location_address) }}" target="_blank"
                   class="text-xs font-semibold text-emerald-700 hover:text-emerald-900 inline-flex items-center gap-1">
                    <span>Buka Petunjuk Google Maps</span> &rarr;
                </a>
            </div>
        </div>

        <!-- Teknisi Penanggung Jawab -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block mb-2">Teknisi Bertugas</span>
                @if($workOrder->technician)
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-900 font-bold flex items-center justify-center shrink-0 border border-amber-200">
                            {{ substr($workOrder->technician->name, 0, 1) }}
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-slate-900">{{ $workOrder->technician->name }}</h4>
                            <p class="text-xs text-slate-500 font-mono">No. WhatsApp: {{ $workOrder->technician->phone }}</p>
                        </div>
                    </div>
                    <div class="mt-3 text-xs space-y-1 text-slate-600 bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Diambil di PT:</span>
                            <span class="font-medium">{{ $workOrder->pickup_time ? $workOrder->pickup_time->format('H:i, d M') : 'Belum' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Sampai Lokasi:</span>
                            <span class="font-medium">{{ $workOrder->arrival_time ? $workOrder->arrival_time->format('H:i, d M') : 'Belum' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Selesai:</span>
                            <span class="font-medium">{{ $workOrder->completion_time ? $workOrder->completion_time->format('H:i, d M') : 'Belum' }}</span>
                        </div>
                    </div>
                @else
                    <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs">
                        Belum ada teknisi yang ditetapkan.
                    </div>
                @endif
            </div>
            @if($workOrder->technician)
                <div class="mt-3 pt-2">
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $workOrder->technician->phone) }}" target="_blank"
                       class="text-xs font-semibold text-emerald-700 hover:text-emerald-900 inline-flex items-center gap-1">
                        <span>Hubungi WhatsApp Teknisi</span> &rarr;
                    </a>
                </div>
            @endif
        </div>

        <!-- Perangkat Yang Ditugaskan -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block mb-2">Pelacakan Perangkat</span>
                @if($workOrder->device)
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-mono text-sm font-extrabold text-brand-700">{{ $workOrder->device->registration_number }}</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $workOrder->device->status_badge_class }}">
                                {{ $workOrder->device->stock_status_label }}
                            </span>
                        </div>
                        <h4 class="text-xs font-bold text-slate-900">{{ $workOrder->device->name }}</h4>
                        <p class="text-[11px] text-slate-500 font-mono mt-0.5">SN: {{ $workOrder->device->serial_number }}</p>

                        <div class="mt-2.5 p-2 bg-slate-50 rounded-xl border border-slate-200 text-xs text-slate-700">
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Posisi Terkini:</span>
                            <span class="font-semibold">{{ $workOrder->device->current_location }}</span>
                        </div>
                    </div>
                @else
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-slate-500 text-xs text-center">
                        Pekerjaan ini tidak menggunakan perangkat hardware.
                    </div>
                @endif
            </div>
            @if($workOrder->device)
                <div class="mt-3 pt-2">
                    <a href="{{ route('coordinator.tracking.detail', $workOrder->device) }}" class="text-xs font-semibold text-brand-600 hover:text-brand-800 inline-flex items-center gap-1">
                        <span>Buka Linimasa Perangkat Ini</span> &rarr;
                    </a>
                </div>
            @endif
        </div>

    </div>

    <!-- Bukti Foto Lapangan (Evidence) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Foto Bukti Pengerjaan (Evidence Lapangan)</h3>
                <p class="text-xs text-slate-500">Bukti visual yang diunggah oleh teknisi dari smartphone di lokasi pekerjaan</p>
            </div>
            <span class="text-xs font-semibold text-slate-600">{{ $workOrder->evidence->count() }} Foto</span>
        </div>

        <div class="p-6">
            @if($workOrder->evidence->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    @foreach($workOrder->evidence as $evi)
                        <div class="rounded-xl border border-slate-200 overflow-hidden bg-slate-50 shadow-xs">
                            <div class="h-48 bg-slate-900 flex items-center justify-center overflow-hidden">
                                <img src="{{ asset('storage/' . $evi->photo_path) }}" 
                                     alt="Bukti foto" class="w-full h-full object-cover hover:scale-105 transition-transform duration-300"
                                     onerror="this.onerror=null; this.src='https://placehold.co/600x400/0f172a/cbd5e1?text=Bukti+Pengerjaan';">
                            </div>
                            <div class="p-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 block w-fit mb-1">
                                    {{ $evi->photo_type_label }}
                                </span>
                                <p class="text-xs font-medium text-slate-800">{{ $evi->caption ?: 'Tidak ada keterangan' }}</p>
                                <span class="text-[10px] text-slate-400 mt-1 block font-mono">{{ $evi->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-10 text-slate-400 text-xs">
                    <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Belum ada foto evidence yang diunggah oleh teknisi untuk pekerjaan ini.
                </div>
            @endif
        </div>
    </div>

    <!-- Riwayat Perpindahan Khusus Tiket Ini -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-900">Audit Trail Tiket NOJAR {{ $workOrder->nojar }}</h3>
            <p class="text-xs text-slate-500">Log setiap perubahan status dan pergerakan lokasi dari awal hingga akhir</p>
        </div>

        <div class="p-6">
            <div class="flow-root">
                <ul class="-mb-8">
                    @forelse($workOrder->histories as $idx => $history)
                        <li>
                            <div class="relative pb-8">
                                @if(!$loop->last)
                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-slate-200" aria-hidden="true"></span>
                                @endif
                                <div class="relative flex space-x-3">
                                    <div>
                                        <span class="h-8 w-8 rounded-full bg-emerald-50 border border-emerald-200 flex items-center justify-center ring-8 ring-white text-emerald-700 font-bold text-xs">
                                            {{ $loop->iteration }}
                                        </span>
                                    </div>
                                    <div class="flex-1 min-w-0 flex justify-between space-x-4 pt-1">
                                        <div>
                                            <p class="text-xs font-bold text-slate-900">{{ $history->action }}</p>
                                            <p class="text-xs text-slate-600 mt-0.5 font-mono">
                                                {{ $history->from_location }} &rarr; <strong>{{ $history->to_location }}</strong>
                                            </p>
                                            @if($history->notes)
                                                <p class="text-[11px] text-slate-500 mt-1 italic">{{ $history->notes }}</p>
                                            @endif
                                        </div>
                                        <div class="text-right text-[11px] whitespace-nowrap text-slate-400 font-mono">
                                            {{ $history->created_at->format('H:i:s, d M') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @empty
                        <li class="text-center py-6 text-slate-400 text-xs">
                            Belum ada riwayat tercatat untuk penugasan ini.
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

</div>
@endsection
