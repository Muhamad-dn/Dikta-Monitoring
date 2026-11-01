@extends('layouts.app')

@section('title', 'Pelacakan Perangkat Lapangan')
@section('page_title', 'Pelacakan Posisi Perangkat')
@section('page_subtitle', 'Sistem pengawasan aset ketat untuk mencegah kehilangan perangkat yang dibawa ke lapangan')

@section('content')
<div class="space-y-6">

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border-2 border-amber-300 shadow-xs">
            <span class="text-xs font-bold uppercase tracking-wider text-amber-700">Dibawa Teknisi (Lapangan)</span>
            <div class="text-2xl font-black text-amber-700 mt-1">{{ $stats['total_field'] }} <span class="text-xs font-normal text-slate-400">Unit</span></div>
            <p class="text-[11px] text-amber-800 mt-1 font-medium">Prioritas pengawasan</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs font-bold uppercase tracking-wider text-emerald-600">Tersedia di Gudang PT</span>
            <div class="text-2xl font-black text-emerald-600 mt-1">{{ $stats['total_warehouse'] }} <span class="text-xs font-normal text-slate-400">Unit</span></div>
            <p class="text-[11px] text-slate-500 mt-1">Stok aman di kantor</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs font-bold uppercase tracking-wider text-blue-600">Terpasang Pelanggan</span>
            <div class="text-2xl font-black text-blue-600 mt-1">{{ $stats['total_installed'] }} <span class="text-xs font-normal text-slate-400">Unit</span></div>
            <p class="text-[11px] text-slate-500 mt-1">Aktif di rumah/kantor</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs font-bold uppercase tracking-wider text-purple-600">Dismantle (Bongkaran)</span>
            <div class="text-2xl font-black text-purple-600 mt-1">{{ $stats['total_dismantle'] }} <span class="text-xs font-normal text-slate-400">Unit</span></div>
            <p class="text-[11px] text-slate-500 mt-1">Hasil cabutan</p>
        </div>
    </div>

    <!-- Filter & Search Matrix -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900">Matriks Filter Lokasi & Teknisi</h2>
            <span class="text-xs text-slate-400 font-mono">Live Tracking Status</span>
        </div>

        <form action="{{ route('coordinator.tracking.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Cari No. Reg / SN / Nama / Lokasi..."
                       class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">
            </div>

            <div>
                <select name="technician_id" class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">
                    <option value="">Semua Teknisi Pembawa</option>
                    @foreach($technicians as $tech)
                        <option value="{{ $tech->id }}" {{ request('technician_id') == $tech->id ? 'selected' : '' }}>
                            {{ $tech->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <select name="stock_status" class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">
                    <option value="">Semua Status Keberadaan</option>
                    <option value="dibawa_teknisi" {{ request('stock_status') == 'dibawa_teknisi' ? 'selected' : '' }}>Sedang Dibawa Teknisi</option>
                    <option value="tersedia_pt" {{ request('stock_status') == 'tersedia_pt' ? 'selected' : '' }}>Tersedia di Gudang PT</option>
                    <option value="terpasang_pelanggan" {{ request('stock_status') == 'terpasang_pelanggan' ? 'selected' : '' }}>Terpasang di Pelanggan</option>
                    <option value="dismantle" {{ request('stock_status') == 'dismantle' ? 'selected' : '' }}>Dismantle (Bongkaran)</option>
                    <option value="rusak" {{ request('stock_status') == 'rusak' ? 'selected' : '' }}>Rusak</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 px-3 text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 rounded-xl transition-colors">
                    Saring Data
                </button>
                @if(request()->hasAny(['search', 'technician_id', 'stock_status']))
                    <a href="{{ route('coordinator.tracking.index') }}" class="p-2 text-slate-500 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors" title="Reset">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tracking Matrix Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">Nomor Registrasi</th>
                        <th class="px-6 py-3.5">Nama Perangkat & Merk</th>
                        <th class="px-6 py-3.5">Teknisi Penanggung Jawab</th>
                        <th class="px-6 py-3.5">Lokasi / Alamat Terakhir</th>
                        <th class="px-6 py-3.5">Status Keberadaan</th>
                        <th class="px-6 py-3.5">Waktu Update Terakhir</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($devices as $device)
                        <tr class="hover:bg-slate-50/80 transition-colors {{ $device->stock_status === 'dibawa_teknisi' ? 'bg-amber-50/30' : '' }}">
                            <td class="px-6 py-4">
                                <div class="font-mono font-bold text-sm text-brand-700">{{ $device->registration_number }}</div>
                                <span class="text-[10px] font-mono text-slate-400">SN: {{ $device->serial_number }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900">{{ $device->name }}</div>
                                <div class="text-[11px] text-slate-500">{{ $device->brand }} &bull; {{ $device->type }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @if($device->currentTechnician)
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-xl bg-amber-200 text-amber-900 font-bold text-xs flex items-center justify-center shrink-0">
                                            {{ substr($device->currentTechnician->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <span class="font-bold text-slate-900 block">{{ $device->currentTechnician->name }}</span>
                                            <span class="text-[10px] text-slate-500">{{ $device->currentTechnician->phone }}</span>
                                        </div>
                                    </div>
                                @elseif($device->stock_status === 'tersedia_pt')
                                    <span class="text-emerald-700 font-semibold bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                                        Gudang PT (Stok Aman)
                                    </span>
                                @else
                                    <span class="text-slate-400 italic">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-1.5 text-slate-800 font-medium">
                                    <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span>{{ $device->current_location }}</span>
                                </div>
                                @if($device->activeWorkOrder)
                                    <span class="text-[10px] text-indigo-700 font-mono font-semibold mt-0.5 block">
                                        Tiket: {{ $device->activeWorkOrder->nojar }} ({{ $device->activeWorkOrder->customer_name }})
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold border {{ $device->status_badge_class }}">
                                    {{ $device->stock_status_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-slate-500">
                                <div class="font-medium text-slate-700">{{ $device->updated_at->format('d/m/Y H:i') }}</div>
                                <div class="text-[10px] text-slate-400">{{ $device->updated_at->diffForHumans() }}</div>
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <a href="{{ route('coordinator.tracking.detail', $device) }}" class="px-3 py-1.5 text-xs font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-lg border border-emerald-200 transition-colors">
                                    Lihat Riwayat &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-slate-400">
                                Tidak ada data perangkat yang cocok dengan kriteria pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($devices->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $devices->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
