@extends('layouts.app')

@section('title', 'Stock Opname Perangkat')
@section('page_title', 'Stock Opname Bulanan')
@section('page_subtitle', 'Audit fisik stok gudang berkala akhir bulan vs catatan sistem PT DIKTA')

@section('content')
<div class="space-y-6">

    <!-- Top Action & Overview -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                Riwayat Sesi Stock Opname
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Lakukan cek fisik barcode di akhir bulan untuk memastikan seluruh unit di gudang dan lapangan selalu sinkron.
            </p>
        </div>

        <!-- HANYA SATU TOMBOL UTAMA -->
        <div>
            <a href="{{ route('admin.stock-opname.start') }}" 
               class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-500/20 transition-all hover:scale-[1.02] active:scale-[0.98]">
                @if(isset($activeOpname) && $activeOpname)
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    <span>Lanjutkan Scan Stock Opname</span>
                @else
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Mulai Stock Opname</span>
                @endif
            </a>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Sesi Selesai</span>
            <div class="text-2xl font-black text-slate-900 dark:text-white mt-1">{{ $stats['total_completed'] }} <span class="text-xs font-medium text-slate-400">Sesi</span></div>
            <p class="text-[11px] text-emerald-600 dark:text-emerald-400 font-medium mt-1">Arsip laporan audit tersimpan</p>
        </div>

        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-amber-200 dark:border-amber-900/50 bg-amber-50/20 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">Sesi Sedang Berjalan</span>
            <div class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1">{{ $stats['total_in_progress'] }} <span class="text-xs font-medium text-amber-700/60">Aktif</span></div>
            <p class="text-[11px] text-amber-600/80 font-medium mt-1">Siap untuk dipindai dengan barcode</p>
        </div>

        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Terakhir Dilaksanakan</span>
            <div class="text-sm font-bold text-slate-900 dark:text-white mt-2 truncate">
                {{ $stats['last_opname'] ? $stats['last_opname']->title : 'Belum pernah selesai' }}
            </div>
            <p class="text-[11px] text-slate-400 font-medium mt-1">
                {{ $stats['last_opname'] && $stats['last_opname']->completed_at ? $stats['last_opname']->completed_at->format('d M Y H:i') : '-' }}
            </p>
        </div>
    </div>

    <!-- Filters & List Sesi -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="p-4 sm:px-6 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span>Daftar Sesi Audit Stock Opname</span>
            </h3>

            <form method="GET" action="{{ route('admin.stock-opname.index') }}" class="flex items-center gap-2">
                <select name="status" onchange="this.form.submit()" 
                        class="text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 px-3 py-1.5 focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Status</option>
                    <option value="in_progress" {{ $status === 'in_progress' ? 'selected' : '' }}>Sedang Berjalan</option>
                    <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Selesai</option>
                </select>

                <select name="year" onchange="this.form.submit()" 
                        class="text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 px-3 py-1.5 focus:ring-2 focus:ring-blue-500">
                    @for($y = date('Y') + 1; $y >= 2024; $y--)
                        <option value="{{ $y }}" {{ (int)$year === $y ? 'selected' : '' }}>Tahun {{ $y }}</option>
                    @endfor
                </select>
            </form>
        </div>

        @if($opnames->isEmpty())
            <div class="p-12 text-center">
                <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200">Belum Ada Sesi Stock Opname</h4>
                <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                    Klik tombol <strong>"Mulai Stock Opname"</strong> di atas untuk langsung membuka halaman scan barcode fisik perangkat.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50/70 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider border-b border-slate-100 dark:border-slate-800">
                            <th class="py-3 px-4">Kode & Judul Sesi</th>
                            <th class="py-3 px-4">Periode & Lokasi</th>
                            <th class="py-3 px-4 text-center">Target Sistem</th>
                            <th class="py-3 px-4 text-center">Fisik Ada (Match)</th>
                            <th class="py-3 px-4 text-center">Selisih Kurang</th>
                            <th class="py-3 px-4 text-center">Anomali</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($opnames as $opname)
                            @php
                                $percent = $opname->total_system > 0 
                                    ? round(($opname->total_matched / $opname->total_system) * 100, 1) 
                                    : 0;
                            @endphp
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900 dark:text-white">{{ $opname->title }}</div>
                                    <div class="text-[11px] font-mono text-slate-400 mt-0.5">{{ $opname->opname_code }}</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-semibold text-slate-800 dark:text-slate-200">{{ $opname->month_name }} {{ $opname->period_year }}</div>
                                    <div class="inline-flex items-center gap-1 text-[11px] text-slate-500 mt-0.5">
                                        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                        {{ $opname->location_label }}
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ $opname->total_system }}</span>
                                    <span class="text-[10px] text-slate-400 block">Unit</span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="font-black text-emerald-600 dark:text-emerald-400">{{ $opname->total_matched }}</span>
                                    <span class="text-[10px] text-emerald-600/70 block">{{ $percent }}%</span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($opname->total_missing > 0)
                                        <span class="font-bold text-rose-600 dark:text-rose-400">{{ $opname->total_missing }}</span>
                                        <span class="text-[10px] text-rose-500/70 block">Belum Di-Scan</span>
                                    @else
                                        <span class="text-xs font-semibold text-emerald-500">0 (Lengkap)</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($opname->total_surplus > 0)
                                        <span class="font-bold text-amber-600 dark:text-amber-400">{{ $opname->total_surplus }}</span>
                                        <span class="text-[10px] text-amber-500/70 block">Temuan</span>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($opname->status === 'completed')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Selesai
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 animate-pulse">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            Sedang Berjalan
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5">
                                        <a href="{{ route('admin.stock-opname.show', $opname) }}" 
                                           class="inline-flex items-center gap-1 px-3 py-1.5 {{ $opname->status === 'completed' ? 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300' : 'bg-blue-600 hover:bg-blue-700 text-white' }} rounded-lg text-xs font-bold transition-all shadow-xs">
                                            @if($opname->status === 'completed')
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                <span>Lihat Hasil</span>
                                            @else
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                                <span>Lanjutkan Scan</span>
                                            @endif
                                        </a>

                                        <a href="{{ route('admin.stock-opname.print', $opname) }}" target="_blank" title="Cetak Berita Acara"
                                           class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                        </a>

                                        <a href="{{ route('admin.stock-opname.export', $opname) }}" title="Export CSV / Excel"
                                           class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-emerald-600 dark:text-emerald-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        </a>

                                        @if($opname->status !== 'completed')
                                            <form method="POST" action="{{ route('admin.stock-opname.destroy', $opname) }}" 
                                                  onsubmit="return confirm('Apakah Anda yakin ingin membatalkan dan menghapus sesi stock opname ini?')" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Hapus Sesi" class="p-1.5 rounded-lg border border-rose-200 dark:border-rose-900 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($opnames->hasPages())
                <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                    {{ $opnames->links() }}
                </div>
            @endif
        @endif
    </div>

</div>
@endsection
