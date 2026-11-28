@extends('layouts.app')

@section('title', 'Kelola Data Teknisi')
@section('page_title', 'Manajemen Teknisi')
@section('page_subtitle', 'Kelola data teknisi lapangan, status aktif akun, dan pantau beban pekerjaan')

@section('content')
<div class="space-y-5" x-data="technicianCarriedDevicesModal()">

    <!-- Top Action & Filter -->
    <div class="bg-white dark:bg-[#131c31] p-4 sm:p-5 rounded-2xl border border-slate-200 dark:border-[#243048] shadow-xs flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div>
                <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span>Daftar Teknisi PT DIKTA</span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-blue-100 dark:bg-blue-950/70 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                        Total {{ $technicians->total() }} Teknisi
                    </span>
                </h2>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Daftar teknisi lapangan (10 teknisi per slide / halaman)</p>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
            <!-- Search & Filter Form -->
            <form action="{{ route('admin.technicians.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
                <div class="relative flex-1 sm:w-60">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Cari nama, no hp, username..."
                           class="w-full pl-8 pr-3 py-2 text-xs bg-slate-50 dark:bg-[#0b1329] border border-slate-300 dark:border-[#243048] rounded-xl text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                </div>

                <select name="status" onchange="this.form.submit()" 
                        class="px-2.5 py-2 text-xs bg-slate-50 dark:bg-[#0b1329] border border-slate-300 dark:border-[#243048] rounded-xl text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>

                <button type="submit" class="px-3 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-xl transition-colors">
                    Cari
                </button>
                @if(request()->hasAny(['search', 'status']))
                    <a href="{{ route('admin.technicians.index') }}" class="px-2.5 py-2 text-xs text-rose-500 hover:text-rose-600 font-semibold">
                        Reset
                    </a>
                @endif
            </form>

            <a href="{{ route('admin.technicians.create') }}" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 text-xs font-bold text-white bg-brand-600 hover:bg-brand-700 rounded-xl shadow-xs transition-colors shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                <span>Tambah Teknisi</span>
            </a>
        </div>
    </div>

    <!-- Table of Technicians -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">Teknisi</th>
                        <th class="px-6 py-3.5">No. HP</th>
                        <th class="px-6 py-3.5 text-center">Perangkat Dibawa</th>
                        <th class="px-6 py-3.5 text-center">Status Akun</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($technicians as $tech)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center font-bold text-slate-700 text-xs shrink-0 shadow-2xs">
                                        {{ strtoupper(substr($tech->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <button type="button" 
                                                @click="openModal(@js($tech->name), @js($tech->phone), @js($tech->carriedDevices))"
                                                class="font-bold text-slate-900 text-sm hover:text-brand-600 transition-colors cursor-pointer text-left focus:outline-none block">
                                            {{ $tech->name }}
                                        </button>
                                        <div class="flex items-center gap-2 mt-0.5 text-[11px] text-slate-500">
                                            <span class="inline-flex items-center gap-1 font-mono font-semibold text-slate-700 bg-slate-100 border border-slate-200/80 px-1.5 py-0.5 rounded-md" title="Username login teknisi">
                                                <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                                <span>{{ $tech->username }}</span>
                                            </span>
                                            @if($tech->email)
                                                <span class="text-slate-300">&bull;</span>
                                                <span class="truncate max-w-[150px] text-slate-500">{{ $tech->email }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-800">{{ $tech->phone }}</div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <button type="button" 
                                        @click="openModal(@js($tech->name), @js($tech->phone), @js($tech->carriedDevices))"
                                        class="inline-block transition-transform hover:scale-105 cursor-pointer focus:outline-none"
                                        title="Klik untuk melihat perangkat yang dibawa teknisi ini">
                                    @if($tech->carried_devices_count > 0)
                                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200 hover:bg-amber-200 inline-flex items-center gap-1">
                                            <span>{{ $tech->carried_devices_count }} Unit</span>
                                            <svg class="w-3 h-3 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-500 border border-slate-200 hover:bg-slate-200">
                                            0 Unit
                                        </span>
                                    @endif
                                </button>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($tech->status === 'active')
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Aktif
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-300">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    <form action="{{ route('admin.technicians.toggle-status', $tech) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="px-2.5 py-1 text-xs font-semibold rounded-lg border transition-colors {{ $tech->status === 'active' ? 'bg-slate-50 text-slate-600 hover:bg-slate-100 border-slate-200' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border-emerald-200' }}">
                                            {{ $tech->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                    <a href="{{ route('admin.technicians.edit', $tech) }}" class="p-1.5 text-slate-600 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition-colors" title="Edit Teknisi">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <form action="{{ route('admin.technicians.destroy', $tech) }}" method="POST" onsubmit="return confirm('Hapus teknisi {{ $tech->name }}?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-slate-600 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Hapus Teknisi">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-400">
                                Belum ada data teknisi terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination / Slide Navigator Per 10 Kolom -->
        <div class="px-6 py-4 border-t border-slate-100 dark:border-[#243048] bg-slate-50/60 dark:bg-[#131c31]/60 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="text-xs text-slate-500 dark:text-slate-400">
                Menampilkan <span class="font-bold text-slate-800 dark:text-slate-200">{{ $technicians->firstItem() ?? 0 }}</span> - <span class="font-bold text-slate-800 dark:text-slate-200">{{ $technicians->lastItem() ?? 0 }}</span> dari <span class="font-bold text-slate-900 dark:text-white">{{ $technicians->total() }}</span> teknisi (10 teknisi per slide)
            </div>

            <div class="flex items-center gap-2">
                @if($technicians->hasPages())
                    <!-- Tombol Slide Sebelumnya -->
                    @if($technicians->onFirstPage())
                        <span class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-slate-400 dark:text-slate-500 bg-slate-100 dark:bg-slate-800/60 rounded-xl cursor-not-allowed border border-slate-200 dark:border-slate-700/60">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            <span>Slide Sebelumnya</span>
                        </span>
                    @else
                        <a href="{{ $technicians->previousPageUrl() }}" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-bold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-brand-50 hover:text-brand-600 rounded-xl border border-slate-300 dark:border-slate-700 shadow-2xs transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            <span>Slide Sebelumnya</span>
                        </a>
                    @endif

                    <!-- Nomor Slide / Halaman -->
                    <div class="flex items-center gap-1">
                        @for($page = 1; $page <= $technicians->lastPage(); $page++)
                            @if($page == $technicians->currentPage())
                                <span class="w-8 h-8 flex items-center justify-center rounded-xl bg-brand-600 text-white font-extrabold text-xs shadow-xs">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $technicians->url($page) }}" class="w-8 h-8 flex items-center justify-center rounded-xl bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 font-bold text-xs transition-colors">
                                    {{ $page }}
                                </a>
                            @endif
                        @endfor
                    </div>

                    <!-- Tombol Slide Selanjutnya -->
                    @if($technicians->hasMorePages())
                        <a href="{{ $technicians->nextPageUrl() }}" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-bold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-brand-50 hover:text-brand-600 rounded-xl border border-slate-300 dark:border-slate-700 shadow-2xs transition-colors">
                            <span>Slide Selanjutnya</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    @else
                        <span class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-slate-400 dark:text-slate-500 bg-slate-100 dark:bg-slate-800/60 rounded-xl cursor-not-allowed border border-slate-200 dark:border-slate-700/60">
                            <span>Slide Selanjutnya</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </span>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <!-- Modal Daftar Perangkat Dibawa Teknisi -->
    <div x-show="modalOpen" x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="modalOpen = false" 
             class="bg-white rounded-2xl shadow-xl border border-slate-200 w-full max-w-2xl overflow-hidden my-6 max-h-[90vh] flex flex-col">
            
            <!-- Header Modal -->
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/80 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <span>Perangkat Dibawa:</span>
                            <span class="text-amber-700 font-extrabold" x-text="activeTechName"></span>
                        </h3>
                        <p class="text-[11px] text-slate-500">
                            No. HP: <span x-text="activeTechPhone" class="font-medium text-slate-700"></span> &bull; 
                            Total <span x-text="activeDevices.length" class="font-bold text-slate-900"></span> unit berada di tangan teknisi
                        </p>
                    </div>
                </div>
                <button type="button" @click="modalOpen = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold p-1 rounded-lg hover:bg-slate-100 transition-colors">&times;</button>
            </div>

            <!-- Body Modal -->
            <div class="p-6 overflow-y-auto flex-1 space-y-3">
                <!-- Jika Tidak Ada Perangkat -->
                <template x-if="activeDevices.length === 0">
                    <div class="text-center py-10 px-4 bg-slate-50 rounded-xl border border-slate-200">
                        <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                        <h4 class="text-sm font-bold text-slate-800">Tidak Ada Perangkat yang Dibawa</h4>
                        <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Teknisi <span x-text="activeTechName" class="font-semibold"></span> saat ini sedang tidak membawa unit perangkat operasional apapun di luar kantor.</p>
                    </div>
                </template>

                <!-- Daftar Perangkat -->
                <template x-if="activeDevices.length > 0">
                    <div class="space-y-3">
                        <template x-for="(dev, idx) in activeDevices" :key="dev.id">
                            <div class="p-4 bg-white rounded-xl border border-slate-200 shadow-xs hover:border-amber-300 transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="space-y-1.5 flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-mono font-bold text-xs text-slate-900 bg-slate-100 px-2 py-0.5 rounded border border-slate-300" x-text="dev.registration_number"></span>
                                        <span class="font-bold text-slate-900 text-sm" x-text="dev.name"></span>
                                        <span class="text-[11px] text-slate-400" x-text="'(' + dev.brand + (dev.type ? ' • ' + dev.type : '') + ')'"></span>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-amber-50 text-amber-700 border border-amber-200">Dibawa Teknisi</span>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs pt-1">
                                        <div>
                                            <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Pelanggan & NOJAR:</span>
                                            <div class="font-semibold text-slate-800 flex items-center gap-1 mt-0.5">
                                                <span x-text="dev.customer_name ? dev.customer_name : '-'"></span>
                                                <span x-show="dev.nojar" class="font-mono text-[10px] bg-blue-50 text-blue-700 px-1.5 py-0.2 rounded border border-blue-200 font-bold" x-text="'NOJAR: ' + dev.nojar"></span>
                                            </div>
                                        </div>
                                        <div>
                                            <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Alamat Pemasangan:</span>
                                            <span class="text-slate-700 mt-0.5 block truncate" :title="dev.address" x-text="dev.address ? dev.address : '-'"></span>
                                        </div>
                                    </div>

                                    <div class="text-[11px] text-slate-500 pt-0.5 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        <span x-text="dev.current_location"></span>
                                    </div>
                                </div>

                                <div class="shrink-0 flex items-center sm:self-center">
                                    <a :href="'/admin/devices/' + dev.id" target="_blank" class="px-3 py-1.5 text-xs font-semibold text-brand-700 bg-brand-50 hover:bg-brand-100 rounded-lg border border-brand-200 transition-colors flex items-center gap-1">
                                        <span>Detail Unit</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                    </a>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            <!-- Footer Modal -->
            <div class="px-6 py-3 border-t border-slate-100 bg-slate-50/50 flex items-center justify-end shrink-0">
                <button type="button" @click="modalOpen = false" 
                        class="px-4 py-2 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-100 border border-slate-300 rounded-xl transition-colors">
                    Tutup
                </button>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
function technicianCarriedDevicesModal() {
    return {
        modalOpen: false,
        activeTechName: '',
        activeTechPhone: '',
        activeDevices: [],
        openModal(name, phone, devices) {
            this.activeTechName = name;
            this.activeTechPhone = phone || '-';
            this.activeDevices = devices || [];
            this.modalOpen = true;
        }
    }
}
</script>
@endpush
@endsection
