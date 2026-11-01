@extends('layouts.app')

@section('title', 'Bukti Foto / Evidence Perangkat ' . $device->registration_number)
@section('page_title', 'Bukti Foto / Evidence')
@section('page_subtitle', 'Dokumentasi visual pekerjaan teknisi di lapangan untuk perangkat ' . $device->registration_number)

@section('content')
<div class="space-y-6">

    <!-- Device Summary Card -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-600 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h2 class="text-base font-bold text-slate-900">{{ $device->name }}</h2>
                        <span class="font-mono text-xs font-bold px-2.5 py-0.5 rounded-md bg-brand-50 text-brand-700 border border-brand-200">
                            {{ $device->registration_number }}
                        </span>
                        <span class="text-xs px-2.5 py-0.5 rounded-full font-semibold border {{ $device->status_badge_class }}">
                            {{ $device->stock_status_label }}
                        </span>
                    </div>
                    <div class="text-xs text-slate-500 mt-1 flex items-center gap-4 flex-wrap">
                        @if($device->brand) <span>Merk: <strong>{{ $device->brand }}</strong></span> @endif
                        @if($device->type) <span>Tipe: <strong>{{ $device->type }}</strong></span> @endif
                        @if($device->customer_name) <span>Pelanggan: <strong>{{ $device->customer_name }}</strong></span> @endif
                        @if($device->address) <span>Alamat: <strong>{{ $device->address }}</strong></span> @endif
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('admin.devices.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Kembali ke Data Perangkat</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Gallery Grid -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-sm font-bold text-slate-900">Galeri Foto Evidence ({{ count($evidenceList) }} Foto)</h3>
            <span class="text-xs text-slate-400">Diunggah oleh Teknisi Lapangan</span>
        </div>

        @if(count($evidenceList) > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                @foreach($evidenceList as $item)
                    <div class="rounded-2xl border border-slate-200 overflow-hidden bg-slate-50 shadow-xs flex flex-col justify-between">
                        <div>
                            <div class="h-52 bg-slate-950 flex items-center justify-center overflow-hidden relative group">
                                <img src="{{ $item['photo_url'] }}" 
                                     alt="Evidence photo"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                     onerror="this.onerror=null; this.src='https://placehold.co/600x400/0f172a/cbd5e1?text=Foto+Evidence';">
                                <a href="{{ $item['photo_url'] }}" target="_blank"
                                   class="absolute bottom-2 right-2 bg-slate-900/80 hover:bg-slate-900 text-white p-1.5 rounded-lg text-xs opacity-0 group-hover:opacity-100 transition-opacity" title="Buka Ukuran Penuh">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                            </div>
                            <div class="p-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 inline-block mb-2">
                                    {{ $item['photo_type_label'] }}
                                </span>
                                
                                <p class="text-xs font-semibold text-slate-900 line-clamp-2">
                                    {{ $item['caption'] }}
                                </p>

                                <div class="mt-3 pt-3 border-t border-slate-200/80 text-[11px] space-y-1">
                                    <div class="flex justify-between">
                                        <span class="text-slate-400">NOJAR:</span>
                                        <span class="font-mono font-bold text-brand-700">{{ $item['nojar'] }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-slate-400">Pelanggan:</span>
                                        <span class="font-medium text-slate-800">{{ $item['customer_name'] }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-slate-400">Teknisi:</span>
                                        <span class="font-medium text-slate-800">{{ $item['technician_name'] }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="px-4 py-2.5 bg-slate-100/70 border-t border-slate-200 text-[10px] text-slate-400 flex justify-between items-center font-mono">
                            <span>{{ $item['created_at_formatted'] }}</span>
                            <span>{{ $item['created_at_human'] }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12 text-slate-400 text-xs">
                <svg class="w-12 h-12 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <p class="text-sm font-bold text-slate-700">Belum Ada Bukti Foto</p>
                <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Teknisi belum mengunggah foto evidence untuk perangkat ini.</p>
            </div>
        @endif
    </div>

</div>
@endsection
