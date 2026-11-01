@extends('layouts.app')

@section('title', 'Dashboard Koordinator')
@section('page_title', 'Dashboard Koordinator Lapangan')
@section('page_subtitle', 'Pantau perangkat yang dibawa teknisi dan buat penugasan NOJAR baru')

@section('content')
<div class="space-y-6">

    <!-- Tombol Aksi Cepat -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-sm font-extrabold text-slate-900">Panel Operasional Koordinator</h2>
            <p class="text-xs text-slate-500">Tugaskan teknisi dan pastikan tidak ada perangkat kantor yang hilang di lapangan</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('coordinator.work-orders.create') }}" class="px-4 py-2 text-xs font-black text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition-colors flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>+ Buat Penugasan Baru</span>
            </a>
            <a href="{{ route('coordinator.tracking.index') }}" class="px-3 py-2 text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-300 rounded-xl transition-colors">
                Pelacakan Semua Perangkat &rarr;
            </a>
        </div>
    </div>

    <!-- FOKUS UTAMA: SIAPA TEKNISI YANG SEDANG BAWA PERANGKAT HARI INI? -->
    <div class="bg-amber-500/10 border-2 border-amber-300 rounded-2xl p-5 shadow-xs">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse"></span>
                <h3 class="text-xs font-black text-amber-950 uppercase tracking-wider">
                    Perangkat Sedang Dibawa Teknisi Hari Ini ({{ $activeFieldDevices->count() }} Unit Beredar)
                </h3>
            </div>
            <span class="text-[11px] font-bold text-amber-800">Prioritas Pantauan</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @forelse($activeFieldDevices as $dev)
                <div class="bg-white p-4 rounded-xl border border-amber-200 shadow-xs flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-mono text-sm font-black text-blue-700">{{ $dev->registration_number }}</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                Dibawa Teknisi
                            </span>
                        </div>
                        <div class="font-bold text-xs text-slate-900">{{ $dev->name }}</div>
                        <div class="text-[11px] text-slate-500 font-mono">SN: {{ $dev->serial_number }}</div>

                        <!-- Teknisi Yang Membawa -->
                        <div class="mt-3 pt-2 border-t border-slate-100 text-xs">
                            <span class="text-[10px] uppercase font-bold text-slate-400 block">Dipegang Oleh:</span>
                            <span class="font-bold text-slate-900">{{ $dev->currentTechnician ? $dev->currentTechnician->name : '-' }}</span>
                            <span class="text-[10px] text-slate-500 block">{{ $dev->currentTechnician ? $dev->currentTechnician->phone : '' }}</span>
                        </div>

                        <!-- Lokasi Sekarang -->
                        <div class="mt-2 text-[11px] text-slate-600 bg-slate-50 p-2 rounded-lg border border-slate-100">
                            <span class="text-[10px] uppercase font-bold text-slate-400 block">Posisi Terkini:</span>
                            <span class="font-medium text-slate-800">{{ $dev->current_location }}</span>
                        </div>
                    </div>

                    <div class="mt-3 pt-2 text-right">
                        <a href="{{ route('coordinator.tracking.detail', $dev) }}" class="text-xs font-bold text-emerald-700 hover:underline">
                            Lihat Riwayat Perjalanan &rarr;
                        </a>
                    </div>
                </div>
            @empty
                <div class="sm:col-span-3 bg-white p-6 rounded-xl border border-amber-200 text-center text-slate-400 text-xs">
                    Semua perangkat aman di gudang PT. Tidak ada perangkat yang sedang beredar di luar.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Daftar Pekerjaan NOJAR Aktif -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Daftar Tiket Pekerjaan (NOJAR)</h3>
                <p class="text-xs text-slate-500">Monitor kemajuan pengerjaan teknisi di lapangan</p>
            </div>
            <a href="{{ route('coordinator.work-orders.index') }}" class="text-xs font-bold text-emerald-700 hover:underline">
                Lihat Semua &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3">NOJAR</th>
                        <th class="px-6 py-3">Pelanggan & Alamat</th>
                        <th class="px-6 py-3">Teknisi</th>
                        <th class="px-6 py-3">Perangkat</th>
                        <th class="px-6 py-3">Status Saat Ini</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentJobs as $job)
                        <tr class="hover:bg-slate-50/80">
                            <td class="px-6 py-3.5 font-mono font-bold text-slate-900">
                                {{ $job->nojar }}
                            </td>
                            <td class="px-6 py-3.5">
                                <div class="font-bold text-slate-900">{{ $job->customer_name }}</div>
                                <div class="text-[11px] text-slate-500 truncate max-w-xs">{{ $job->location_address }}</div>
                            </td>
                            <td class="px-6 py-3.5 font-semibold text-slate-800">
                                {{ $job->technician ? $job->technician->name : '-' }}
                            </td>
                            <td class="px-6 py-3.5">
                                @if($job->device)
                                    <span class="font-mono font-bold text-blue-700">{{ $job->device->registration_number }}</span>
                                    <span class="text-[10px] text-slate-500 block">{{ $job->device->name }}</span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-3.5">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $job->status_badge_class }}">
                                    {{ $job->status_label }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-right">
                                <a href="{{ route('coordinator.work-orders.show', $job) }}" class="font-bold text-emerald-700 hover:underline">
                                    Detail &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-6 text-center text-slate-400">
                                Belum ada data pekerjaan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
