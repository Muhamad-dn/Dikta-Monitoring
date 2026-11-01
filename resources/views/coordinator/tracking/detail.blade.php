@extends('layouts.app')

@section('title', 'Riwayat Pelacakan ' . $device->registration_number)
@section('page_title', 'Pelacakan Perangkat: ' . $device->registration_number)
@section('page_subtitle', 'Audit jejak fisik pergerakan perangkat ' . $device->name . ' dari gudang hingga lokasi akhir')

@section('content')
<div class="space-y-6">

    <!-- Overview Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="font-mono text-xl font-extrabold text-brand-700">{{ $device->registration_number }}</span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $device->status_badge_class }}">
                        {{ $device->stock_status_label }}
                    </span>
                </div>
                <h3 class="text-base font-bold text-slate-800">{{ $device->name }} ({{ $device->brand }} &bull; {{ $device->type }})</h3>
                <p class="text-xs text-slate-500 mt-0.5 font-mono">Serial Number: {{ $device->serial_number }}</p>
            </div>
            <a href="{{ route('coordinator.tracking.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">
                &larr; Kembali ke Matriks Pelacakan
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-4 text-xs">
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                <span class="text-slate-400 block text-[10px] uppercase font-bold">Teknisi Penanggung Jawab</span>
                @if($device->currentTechnician)
                    <strong class="text-sm font-bold text-slate-900 block mt-0.5">{{ $device->currentTechnician->name }}</strong>
                    <span class="text-slate-500">{{ $device->currentTechnician->phone }}</span>
                @else
                    <span class="text-slate-500 italic">Tidak sedang dibawa teknisi</span>
                @endif
            </div>

            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 sm:col-span-2">
                <span class="text-slate-400 block text-[10px] uppercase font-bold">Posisi Fisik / Alamat Terkini</span>
                <strong class="text-sm font-bold text-slate-900 block mt-0.5">{{ $device->current_location }}</strong>
                <span class="text-[10px] text-slate-400">Pembaruan: {{ $device->updated_at->diffForHumans() }} ({{ $device->updated_at->format('d/m/Y H:i') }})</span>
            </div>
        </div>
    </div>

    <!-- Movement Timeline (Audit Trail) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Linimasa Perpindahan Tangan & Lokasi</h3>
                <p class="text-xs text-slate-500">Mencatat setiap kali perangkat berpindah tangan teknisi atau lokasi pengerjaan</p>
            </div>
            <span class="text-xs font-semibold text-slate-500">{{ $device->histories->count() }} Peristiwa Tercatat</span>
        </div>

        <div class="p-6">
            <div class="flow-root">
                <ul class="-mb-8">
                    @forelse($device->histories as $idx => $history)
                        <li>
                            <div class="relative pb-8">
                                @if(!$loop->last)
                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-slate-200" aria-hidden="true"></span>
                                @endif
                                <div class="relative flex space-x-3">
                                    <div>
                                        <span class="h-8 w-8 rounded-full bg-emerald-50 border border-emerald-300 flex items-center justify-center ring-8 ring-white text-emerald-700 font-bold text-xs">
                                            {{ $loop->iteration }}
                                        </span>
                                    </div>
                                    <div class="flex-1 min-w-0 flex justify-between space-x-4 pt-1">
                                        <div>
                                            <p class="text-xs font-bold text-slate-900">{{ $history->action }}</p>
                                            
                                            <div class="mt-1 flex flex-wrap items-center gap-2 text-xs">
                                                @if($history->technician)
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                                        Teknisi: {{ $history->technician->name }}
                                                    </span>
                                                @endif
                                                @if($history->workOrder)
                                                    <span class="font-mono text-[11px] font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-200">
                                                        NOJAR: {{ $history->workOrder->nojar }}
                                                    </span>
                                                @endif
                                            </div>

                                            <p class="text-xs text-slate-600 mt-1.5 font-mono">
                                                <span class="text-slate-400">Dari:</span> {{ $history->from_location ?: 'Gudang' }} 
                                                &rarr; 
                                                <span class="text-slate-400">Ke:</span> <strong class="text-slate-800">{{ $history->to_location }}</strong>
                                            </p>

                                            @if($history->notes)
                                                <p class="text-[11px] text-slate-500 mt-1 italic bg-slate-50 p-2 rounded-lg border border-slate-100">
                                                    {{ $history->notes }}
                                                </p>
                                            @endif
                                        </div>

                                        <div class="text-right text-[11px] whitespace-nowrap text-slate-400">
                                            <div>{{ $history->created_at->format('d/m/Y H:i') }}</div>
                                            <div class="text-[10px] text-slate-400">{{ $history->created_at->diffForHumans() }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @empty
                        <li class="text-center py-8 text-slate-400 text-xs">
                            Belum ada riwayat pergerakan yang tercatat untuk perangkat ini.
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

</div>
@endsection
