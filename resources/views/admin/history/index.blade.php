@extends('layouts.app')

@section('title', 'Riwayat Perangkat & Audit Trail')
@section('page_title', 'Riwayat Perangkat')
@section('page_subtitle', 'Catatan audit perpindahan seluruh perangkat: siapa yang mengambil, lokasi tujuan, dan waktu perubahan')

@section('content')
<div class="space-y-5">

    <!-- Filter Bar -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900">Filter Log Riwayat</h2>
            <span class="text-xs text-slate-500 font-medium">Audit Trail Permanen</span>
        </div>

        <form action="{{ route('admin.history.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Cari dengan No.registrasi / Nojar"
                       class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
            </div>

            <div>
                <select name="device_id" class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
                    <option value="">Semua Perangkat</option>
                    @foreach($devices as $dev)
                        <option value="{{ $dev->id }}" {{ request('device_id') == $dev->id ? 'selected' : '' }}>
                            {{ $dev->registration_number }} - {{ $dev->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <select name="technician_id" class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
                    <option value="">Semua Teknisi</option>
                    @foreach($technicians as $tech)
                        <option value="{{ $tech->id }}" {{ request('technician_id') == $tech->id ? 'selected' : '' }}>
                            {{ $tech->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 px-3 text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 rounded-xl transition-colors">
                    Saring Riwayat
                </button>
                @if(request()->hasAny(['search', 'device_id', 'technician_id']))
                    <a href="{{ route('admin.history.index') }}" class="p-2 text-slate-500 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Timeline List -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">Waktu</th>
                        <th class="px-6 py-3.5">Perangkat</th>
                        <th class="px-6 py-3.5">Aktivitas / Aksi</th>
                        <th class="px-6 py-3.5">Teknisi / Pelaku</th>
                        <th class="px-6 py-3.5">Perpindahan Lokasi</th>
                        <th class="px-6 py-3.5">NOJAR / Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($histories as $history)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-3.5 whitespace-nowrap">
                                <div class="font-bold text-slate-900">{{ $history->created_at->format('d/m/Y H:i') }}</div>
                                <div class="text-[10px] text-slate-400">{{ $history->created_at->diffForHumans() }}</div>
                            </td>
                            <td class="px-6 py-3.5">
                                @if($history->device)
                                    <a href="{{ route('admin.devices.show', $history->device) }}" class="font-mono font-bold text-slate-900 hover:text-brand-600 hover:underline block">
                                        {{ $history->device->registration_number }}
                                    </a>
                                    <span class="text-[11px] text-slate-500">{{ $history->device->name }}</span>
                                @else
                                    <span class="text-slate-400">Dihapus</span>
                                @endif
                            </td>
                            <td class="px-6 py-3.5">
                                <span class="font-bold text-slate-900 block">{{ $history->action }}</span>
                                @if($history->new_status)
                                    <span class="text-[10px] text-indigo-700 bg-indigo-50 px-1.5 py-0.5 rounded border border-indigo-200 font-medium">
                                        Status: {{ ucfirst(str_replace('_', ' ', $history->new_status)) }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-3.5">
                                @if($history->technician)
                                    <div class="font-semibold text-slate-800">{{ $history->technician->name }}</div>
                                    <span class="text-[10px] text-amber-700 font-medium">Teknisi</span>
                                @elseif($history->actor)
                                    <div class="font-semibold text-slate-800">{{ $history->actor->name }}</div>
                                    <span class="text-[10px] text-slate-400">{{ ucfirst($history->actor->role) }}</span>
                                @else
                                    <span class="text-slate-400">Sistem</span>
                                @endif
                            </td>
                            <td class="px-6 py-3.5 font-mono text-[11px]">
                                <div class="text-slate-400">Dari: {{ $history->from_location ?: '-' }}</div>
                                <div class="text-slate-900 font-semibold mt-0.5">Ke: {{ $history->to_location ?: '-' }}</div>
                            </td>
                            <td class="px-6 py-3.5">
                                @if($history->workOrder)
                                    <span class="font-mono font-bold text-xs text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-200">
                                        {{ $history->workOrder->nojar }}
                                    </span>
                                @endif
                                @if($history->notes)
                                    <div class="text-[11px] text-slate-500 mt-1 italic">{{ $history->notes }}</div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-400">
                                Tidak ada catatan riwayat yang cocok.
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

</div>
@endsection
