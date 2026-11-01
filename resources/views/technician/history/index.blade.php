@extends('layouts.app')

@section('title', 'Riwayat Pekerjaan & Update Lapangan - Teknisi PT DIKTA')
@section('page_title', 'Riwayat Lapangan Teknisi')
@section('page_subtitle', 'Catatan seluruh pembaruan status, handover, pengembalian, dan foto bukti pekerjaan Anda')

@section('content')
<div class="space-y-5" x-data="{ photoModal: false, activePhotoUrl: '', activePhotoTitle: '' }">

    <!-- Filter & Pencarian Riwayat -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-sm font-bold text-slate-900 dark:text-white">Riwayat Lapangan</h1>
                <p class="text-xs text-slate-500 font-medium">Log aktivitas dan bukti pengerjaan Anda</p>
            </div>
            <a href="{{ route('technician.dashboard', ['tab' => 'riwayat']) }}" class="text-xs font-bold text-blue-600 hover:underline">
                &larr; Dashboard Mobile
            </a>
        </div>

        <form action="{{ route('technician.history.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Cari No. Registrasi / Nama Perangkat / NOJAR / Catatan..."
                       class="w-full px-4 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all">
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 rounded-xl transition-colors shadow-xs">
                    Saring Riwayat
                </button>
                @if(request()->has('search'))
                    <a href="{{ route('technician.history.index') }}" class="p-2 text-slate-500 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabel Riwayat -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5">Waktu</th>
                        <th class="px-5 py-3.5">Perangkat</th>
                        <th class="px-5 py-3.5">Aksi / Status</th>
                        <th class="px-5 py-3.5">NOJAR / Pelanggan</th>
                        <th class="px-5 py-3.5">Lokasi Aktual</th>
                        <th class="px-5 py-3.5">Catatan Pekerjaan</th>
                        <th class="px-5 py-3.5 text-center">Foto Bukti</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($histories as $h)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <div class="font-bold text-slate-900">{{ $h->created_at->format('d/m/Y H:i') }}</div>
                                <div class="text-[10px] text-slate-400">{{ $h->created_at->diffForHumans() }}</div>
                            </td>
                            <td class="px-5 py-3.5">
                                @if($h->device)
                                    <span class="font-mono font-black text-slate-900 block text-xs">{{ $h->device->registration_number }}</span>
                                    <span class="text-[11px] text-slate-600 font-medium">{{ $h->device->name }}</span>
                                    @if($h->device->area)
                                        <span class="ml-1 px-1.5 py-0.5 rounded text-[9px] font-bold bg-slate-100 text-slate-600 border border-slate-200 uppercase">{{ $h->device->area }}</span>
                                    @endif
                                @else
                                    <span class="text-slate-400 italic">Perangkat Tidak Ditemukan</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="font-bold text-slate-900 block">{{ $h->action }}</span>
                                @if($h->new_status)
                                    <span class="inline-block mt-0.5 px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                        @if(in_array($h->new_status, ['terinstal', 'terpasang_pelanggan'])) bg-blue-100 text-blue-800 border border-blue-200
                                        @elseif($h->new_status === 'pending') bg-orange-100 text-orange-800 border border-orange-200
                                        @elseif($h->new_status === 'rusak') bg-rose-100 text-rose-800 border border-rose-200
                                        @elseif($h->new_status === 'hilang') bg-purple-100 text-purple-800 border border-purple-200
                                        @elseif(in_array($h->new_status, ['tersedia_pt', 'tersedia_hb_dikta'])) bg-emerald-100 text-emerald-800 border border-emerald-200
                                        @else bg-amber-100 text-amber-800 border border-amber-200
                                        @endif">
                                        {{ ucfirst(str_replace('_', ' ', $h->new_status)) }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                @if($h->workOrder)
                                    <span class="font-mono font-bold text-xs text-blue-700 bg-blue-50 px-2 py-0.5 rounded border border-blue-200 inline-block">
                                        {{ $h->workOrder->nojar }}
                                    </span>
                                    <div class="text-[11px] text-slate-700 font-semibold mt-0.5">{{ $h->workOrder->customer_name }}</div>
                                @elseif($h->device && $h->device->nojar)
                                    <span class="font-mono font-bold text-xs text-blue-700 bg-blue-50 px-2 py-0.5 rounded border border-blue-200 inline-block">
                                        {{ $h->device->nojar }}
                                    </span>
                                    @if($h->device->customer_name)
                                        <div class="text-[11px] text-slate-700 font-semibold mt-0.5">{{ $h->device->customer_name }}</div>
                                    @endif
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="text-[11px] text-slate-800 font-medium max-w-xs truncate" title="{{ $h->to_location ?: $h->from_location }}">
                                    {{ $h->to_location ?: ($h->from_location ?: '-') }}
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="text-[11px] text-slate-600 max-w-xs break-words">
                                    {{ $h->notes ?: '-' }}
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                @if($h->photo_path)
                                    <button type="button" 
                                            @click="activePhotoUrl = '{{ asset('storage/' . $h->photo_path) }}'; activePhotoTitle = 'Foto Bukti: {{ $h->action }}'; photoModal = true"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-300 font-bold text-[11px] transition-colors shadow-xs">
                                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span>Lihat Foto</span>
                                    </button>
                                @else
                                    <span class="text-slate-300 text-[10px]">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-slate-400">
                                <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Belum ada riwayat aktivitas yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($histories->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $histories->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL PREVIEW FOTO BUKTI -->
    <div x-show="photoModal" x-cloak class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-xl w-full p-5 shadow-2xl border border-slate-200 space-y-4" @click.outside="photoModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-slate-900" x-text="activePhotoTitle"></h3>
                <button type="button" @click="photoModal = false" class="text-slate-400 hover:text-slate-700 p-1 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="rounded-xl overflow-hidden bg-black flex items-center justify-center max-h-[70vh]">
                <img :src="activePhotoUrl" alt="Foto Bukti" class="max-h-[65vh] w-auto object-contain">
            </div>
            <div class="flex justify-end pt-2">
                <button type="button" @click="photoModal = false" class="px-4 py-2 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl">
                    Tutup
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
