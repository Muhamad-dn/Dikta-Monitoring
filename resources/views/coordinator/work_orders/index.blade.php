@extends('layouts.app')

@section('title', 'Data Pekerjaan Lapangan')
@section('page_title', 'Daftar Pekerjaan (NOJAR)')
@section('page_subtitle', 'Pantau status tiket pekerjaan lapangan teknisi berdasarkan kategori dan tahapan alur')

@section('content')
<div class="space-y-5">

    <!-- Filter & Action Bar -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Pencarian & Penyaringan Pekerjaan</h2>
                <p class="text-xs text-slate-500">Filter berdasarkan NOJAR, nama pelanggan, teknisi, kategori atau tahapan status</p>
            </div>
            <a href="{{ route('coordinator.work-orders.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition-colors shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Buat Penugasan Baru</span>
            </a>
        </div>

        <form action="{{ route('coordinator.work-orders.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 pt-2">
            <div class="lg:col-span-2">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Cari NOJAR, nama pelanggan, alamat, teknisi..."
                       class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">
            </div>

            <div>
                <select name="category" class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">
                    <option value="">Semua Kategori</option>
                    <option value="Survei" {{ request('category') == 'Survei' ? 'selected' : '' }}>Survei</option>
                    <option value="Instalasi" {{ request('category') == 'Instalasi' ? 'selected' : '' }}>Instalasi</option>
                    <option value="Aktivasi" {{ request('category') == 'Aktivasi' ? 'selected' : '' }}>Aktivasi</option>
                    <option value="Troubleshooting" {{ request('category') == 'Troubleshooting' ? 'selected' : '' }}>Troubleshooting</option>
                    <option value="Dismantle" {{ request('category') == 'Dismantle' ? 'selected' : '' }}>Dismantle</option>
                </select>
            </div>

            <div>
                <select name="status" class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">
                    <option value="">Semua Status Alur</option>
                    <option value="belum_dikerjakan" {{ request('status') == 'belum_dikerjakan' ? 'selected' : '' }}>Belum Dikerjakan</option>
                    <option value="perangkat_diambil" {{ request('status') == 'perangkat_diambil' ? 'selected' : '' }}>Perangkat Diambil di PT</option>
                    <option value="dalam_perjalanan" {{ request('status') == 'dalam_perjalanan' ? 'selected' : '' }}>Dalam Perjalanan</option>
                    <option value="sampai_lokasi" {{ request('status') == 'sampai_lokasi' ? 'selected' : '' }}>Sampai Lokasi</option>
                    <option value="sedang_dikerjakan" {{ request('status') == 'sedang_dikerjakan' ? 'selected' : '' }}>Sedang Dikerjakan</option>
                    <option value="terinstal" {{ request('status') == 'terinstal' ? 'selected' : '' }}>Terinstal / Selesai</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="gagal_install" {{ request('status') == 'gagal_install' ? 'selected' : '' }}>Gagal Install</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 px-3 text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 rounded-xl transition-colors">
                    Saring Tiket
                </button>
                @if(request()->hasAny(['search', 'category', 'status', 'technician_id']))
                    <a href="{{ route('coordinator.work-orders.index') }}" class="p-2 text-slate-500 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors" title="Reset">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table of Work Orders -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">NOJAR / Tiket</th>
                        <th class="px-6 py-3.5">Kategori</th>
                        <th class="px-6 py-3.5">Pelanggan & Lokasi</th>
                        <th class="px-6 py-3.5">Teknisi</th>
                        <th class="px-6 py-3.5">Perangkat Dialokasikan</th>
                        <th class="px-6 py-3.5">Status Alur Lapangan</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($workOrders as $order)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-4">
                                <span class="font-mono font-bold text-sm text-slate-900 block">{{ $order->nojar }}</span>
                                <span class="text-[11px] text-slate-500">{{ $order->created_at->format('d/m/Y') }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold border {{ $order->category_badge_class }}">
                                    {{ $order->category }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900">{{ $order->customer_name }}</div>
                                <div class="text-[11px] text-slate-500 mt-0.5 truncate max-w-xs">{{ $order->location_address }}</div>
                                @if($order->customer_phone)
                                    <div class="text-[10px] text-slate-400">Telp: {{ $order->customer_phone }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($order->technician)
                                    <div class="font-semibold text-slate-900">{{ $order->technician->name }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $order->technician->phone }}</div>
                                @else
                                    <span class="text-rose-500 italic">Belum ditentukan</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($order->device)
                                    <span class="font-mono font-bold text-brand-700">{{ $order->device->registration_number }}</span>
                                    <div class="text-[10px] text-slate-500">{{ $order->device->name }}</div>
                                @else
                                    <span class="text-slate-400 italic">Tidak membutuhkan</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold border {{ $order->status_badge_class }}">
                                    {{ $order->status_label }}
                                </span>
                                @if($order->status_notes)
                                    <div class="text-[10px] text-slate-500 mt-1 italic truncate max-w-xs">{{ $order->status_notes }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('coordinator.work-orders.show', $order) }}" 
                                       class="px-2.5 py-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-lg border border-emerald-200 transition-colors">
                                        Monitor
                                    </a>
                                    <a href="{{ route('coordinator.work-orders.edit', $order) }}" 
                                       class="p-1.5 text-slate-600 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-colors" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    @if(in_array($order->status, ['belum_dikerjakan', 'pending', 'gagal_install']))
                                        <form action="{{ route('coordinator.work-orders.destroy', $order) }}" method="POST" onsubmit="return confirm('Batalkan penugasan NOJAR {{ $order->nojar }}?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-slate-600 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Batalkan">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-slate-400">
                                Tidak ada data pekerjaan yang sesuai dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($workOrders->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $workOrders->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
