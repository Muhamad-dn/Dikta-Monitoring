@extends('layouts.app')

@section('title', 'Arsip Stock Opname')
@section('page_title', 'Arsip Stock Opname')
@section('page_subtitle', 'Audit fisik stok gudang berkala akhir bulan dan arsip laporan Excel PT DIKTA')

@section('content')
<div class="space-y-6" x-data="{ openModal: false }">

    <!-- Top Action & Overview -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                Arsip Stock Opname
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Lakukan cek fisik barcode di akhir bulan dan cetak arsip laporan hasil stock opname langsung ke format Excel.
            </p>
        </div>

        <!-- HANYA SATU TOMBOL UTAMA -->
        <div>
            @if(isset($activeOpname) && $activeOpname)
                <a href="{{ route('admin.stock-opname.show', $activeOpname) }}" 
                   class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-500/20 transition-all hover:scale-[1.02] active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    <span>Lanjutkan Scan Stock Opname</span>
                </a>
            @else
                <button type="button" @click="openModal = true" 
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-500/20 transition-all hover:scale-[1.02] active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Mulai Stock Opname</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Arsip Selesai</span>
            <div class="text-2xl font-black text-slate-900 dark:text-white mt-1">{{ $stats['total_completed'] }} <span class="text-xs font-medium text-slate-400">Arsip</span></div>
            <p class="text-[11px] text-emerald-600 dark:text-emerald-400 font-medium mt-1">Siap dicetak atau diunduh ke Excel</p>
        </div>

        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-amber-200 dark:border-amber-900/50 bg-amber-50/20 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">Sesi Sedang Berjalan</span>
            <div class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1">{{ $stats['total_in_progress'] }} <span class="text-xs font-medium text-amber-700/60">Aktif</span></div>
            <p class="text-[11px] text-amber-600/80 font-medium mt-1">Sedang proses scan barcode di gudang</p>
        </div>

        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Arsip Terakhir</span>
            <div class="text-sm font-bold text-slate-900 dark:text-white mt-2 truncate">
                {{ $stats['last_opname'] ? ('Stock Opname Bulan ' . $stats['last_opname']->month_name . ' ' . $stats['last_opname']->period_year) : 'Belum pernah selesai' }}
            </div>
            <p class="text-[11px] text-slate-400 font-medium mt-1">
                {{ $stats['last_opname'] && $stats['last_opname']->completed_at ? $stats['last_opname']->completed_at->format('d M Y H:i') : '-' }}
            </p>
        </div>
    </div>

    <!-- TABEL ARSIP STOCK OPNAME & FITUR CETAK EXCEL -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="p-4 sm:px-6 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Arsip Stock Opname Bulanan</span>
                </h3>
                <p class="text-[11px] text-slate-500 mt-0.5">Seluruh arsip hasil pengecekan fisik perangkat per bulan dengan tombol cetak Excel di sampingnya</p>
            </div>

            <form method="GET" action="{{ route('admin.stock-opname.index') }}" class="flex items-center gap-2">
                <select name="status" onchange="this.form.submit()" 
                        class="text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 px-3 py-1.5 focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Status</option>
                    <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Selesai</option>
                    <option value="in_progress" {{ $status === 'in_progress' ? 'selected' : '' }}>Sedang Berjalan</option>
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
                <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200">Belum Ada Arsip Stock Opname</h4>
                <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                    Klik tombol <strong>"Mulai Stock Opname"</strong> di atas untuk mengisi bulan dan nama admin pemeriksa, lalu mulai memindai barcode fisik perangkat.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50/70 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider border-b border-slate-100 dark:border-slate-800">
                            <th class="py-3 px-4">Arsip Stock Opname</th>
                            <th class="py-3 px-4">Tanggal Pelaksanaan</th>
                            <th class="py-3 px-4 text-center">Target Sistem</th>
                            <th class="py-3 px-4 text-center">Fisik Sesuai (Match)</th>
                            <th class="py-3 px-4 text-center">Selisih Kurang</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Fitur Cetak Excel & Laporan</th>
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
                                    <div class="font-bold text-slate-900 dark:text-white text-sm flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <span>Stock Opname Bulan {{ $opname->month_name }} {{ $opname->period_year }}</span>
                                    </div>
                                    <div class="text-[11px] text-slate-500 mt-1">
                                        Pemeriksa: <strong class="text-slate-700 dark:text-slate-300">{{ $opname->admin_name ?: ($opname->conductor ? $opname->conductor->name : 'Administrator') }}</strong>
                                        • <span class="font-mono text-slate-400">{{ $opname->opname_code }}</span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-medium text-slate-700 dark:text-slate-300">
                                        {{ $opname->completed_at ? $opname->completed_at->format('d/m/Y H:i') : ($opname->created_at ? $opname->created_at->format('d/m/Y H:i') : '-') }} WIB
                                    </div>
                                    <div class="text-[10px] text-slate-400">
                                        {{ $opname->completed_at ? 'Selesai audit' : 'Mulai dibuat' }}
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="font-bold text-slate-800 dark:text-slate-200 text-sm">{{ $opname->total_system }}</span>
                                    <span class="text-[10px] text-slate-400 block">Unit</span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="font-black text-emerald-600 dark:text-emerald-400 text-sm">{{ $opname->total_matched }}</span>
                                    <span class="text-[10px] text-emerald-600/70 block">{{ $percent }}%</span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($opname->total_missing > 0)
                                        <span class="font-bold text-rose-600 dark:text-rose-400 text-sm">{{ $opname->total_missing }}</span>
                                        <span class="text-[10px] text-rose-500/70 block">Belum Di-Scan</span>
                                    @else
                                        <span class="text-xs font-semibold text-emerald-500">0 (Lengkap)</span>
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
                                    <div class="inline-flex items-center gap-2">
                                        <!-- FITUR CETAK EXCEL (DI SAMPINGNYA LANGSUNG) -->
                                        <a href="{{ route('admin.stock-opname.export', $opname) }}" 
                                           class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl border border-emerald-600 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition-all shadow-sm hover:scale-[1.02]">
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                            <span>Cetak Excel</span>
                                        </a>

                                        <!-- Tombol Cetak Berita Acara -->
                                        <a href="{{ route('admin.stock-opname.print', $opname) }}" target="_blank"
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 font-bold text-xs transition-colors shadow-xs">
                                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                            <span>Cetak PDF</span>
                                        </a>

                                        <!-- Tombol Lihat / Lanjutkan Scan -->
                                        <a href="{{ route('admin.stock-opname.show', $opname) }}" 
                                           class="inline-flex items-center gap-1 px-3 py-1.5 {{ $opname->status === 'completed' ? 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300' : 'bg-blue-600 hover:bg-blue-700 text-white' }} rounded-xl text-xs font-bold transition-all shadow-xs">
                                            @if($opname->status === 'completed')
                                                <span>Detail</span>
                                            @else
                                                <span>Scan &rarr;</span>
                                            @endif
                                        </a>
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

    <!-- POP-UP FORMULIR MULAI STOCK OPNAME -->
    <div x-show="openModal" x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
         @keydown.escape.window="openModal = false">
        
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl max-w-md w-full p-6 space-y-5"
             @click.away="openModal = false">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-2xl bg-blue-500/10 text-blue-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Formulir Mulai Stock Opname</h3>
                        <p class="text-[11px] text-slate-400">Pemeriksaan fisik stok akhir bulan</p>
                    </div>
                </div>
                <button type="button" @click="openModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.stock-opname.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="location" value="hb_jkt">

                <!-- 1. Periode Bulan & Tahun -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Bulan <span class="text-rose-500">*</span>
                        </label>
                        <select name="period_month" required class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 px-3 py-2.5 focus:ring-2 focus:ring-blue-500">
                            @foreach(\App\Models\StockOpname::MONTHS as $mNum => $mLabel)
                                <option value="{{ $mNum }}" {{ (int)date('n') === $mNum ? 'selected' : '' }}>{{ $mLabel }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Tahun <span class="text-rose-500">*</span>
                        </label>
                        <select name="period_year" required class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 px-3 py-2.5 focus:ring-2 focus:ring-blue-500">
                            @for($y = date('Y') + 1; $y >= 2024; $y--)
                                <option value="{{ $y }}" {{ (int)date('Y') === $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                </div>

                <!-- 2. Nama Admin Pemeriksa -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Nama Admin / Pemeriksa <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="admin_name" value="{{ auth()->user()->name ?? 'Administrator Sistem' }}" required
                           placeholder="Masukkan nama admin pemeriksa..."
                           class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 px-3 py-2.5 focus:ring-2 focus:ring-blue-500 font-medium">
                    <p class="text-[10px] text-slate-400 mt-1">Nama ini akan dicantumkan di arsip laporan audit dan file cetak Excel.</p>
                </div>

                <!-- Tombol Aksi Bawah Modal -->
                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="openModal = false"
                            class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-500/20 transition-all flex items-center gap-1.5">
                        <span>Mulai Scan Sekarang</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
