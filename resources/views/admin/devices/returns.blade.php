@extends('layouts.app')

@section('title', 'Fitur Pengembalian Perangkat ke Lintasarta')

@section('content')
<div class="space-y-6" x-data="returnDeviceApp()">
    <!-- Header Page -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="p-2 rounded-xl bg-amber-500/10 text-amber-600 border border-amber-500/20">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                </span>
                <div>
                    <h1 class="text-xl font-black tracking-tight text-slate-900">Fitur Kembalikan Perangkat</h1>
                    <p class="text-xs text-slate-500">Pengembalian unit perangkat ke Lintasarta (Gudang Tekno & Area)</p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.devices.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-xl shadow-xs transition-colors">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Kembali ke Data Perangkat</span>
            </a>
        </div>
    </div>

    <!-- Alert Success / Error -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-3">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold space-y-1">
            <div class="font-bold flex items-center gap-2">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Terdapat kesalahan penginputan:</span>
            </div>
            <ul class="list-disc pl-7 space-y-0.5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Form Panel: Kembalikan Perangkat -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="bg-gradient-to-r from-amber-600 to-amber-700 px-6 py-4 text-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-white">Form Pengembalian Perangkat ke Lintasarta</h2>
                    <p class="text-[11px] text-amber-100">Pilih perangkat dan tentukan tujuan pengembalian</p>
                </div>
            </div>
            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-800/60 text-amber-200 border border-amber-400/30">
                Otomatis Hapus Data
            </span>
        </div>

        <form action="{{ route('admin.devices.process-return') }}" method="POST" class="p-6 space-y-5" onsubmit="return confirmReturnDevice(event)">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- Pilihan Perangkat -->
                <div>
                    <label for="device_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Pilih Perangkat (Nomor Registrasi) <span class="text-rose-500">*</span>
                    </label>
                    <select name="device_id" id="device_id" required x-model="selectedDeviceId" @change="onDeviceChange()"
                            class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all font-semibold text-slate-800">
                        <option value="">-- Pilih Nomor Registrasi Perangkat --</option>
                        @foreach($allDevices as $d)
                            <option value="{{ $d->id }}" 
                                    data-reg="{{ $d->registration_number }}" 
                                    data-name="{{ $d->name }}" 
                                    data-area="{{ $d->area ?: '-' }}" 
                                    data-status="{{ $d->stock_status }}" 
                                    data-location="{{ $d->current_location }}">
                                {{ $d->registration_number }} - {{ $d->name }} (Area: {{ $d->area ?: '-' }})
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-[11px] text-slate-400">Pilih dari daftar perangkat aktif di atas atau klik tombol pada tabel di bawah.</p>
                </div>

                <!-- Tujuan Pengembalian Lintasarta -->
                <div>
                    <label for="destination" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Tujuan Pengembalian ke Lintasarta <span class="text-rose-500">*</span>
                    </label>
                    <select name="destination" id="destination" required
                            class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all font-bold text-slate-800">
                        <option value="">-- Pilih Lokasi / Area Tujuan --</option>
                        <option value="Gudang Tekno">Gudang Tekno</option>
                        <option value="BWA">BWA</option>
                        <option value="BDA">BDA</option>
                        <option value="SEOA">SEOA</option>
                        <option value="SAST">SAST</option>
                        <option value="CDA">CDA</option>
                        <option value="NEA">NEA</option>
                    </select>
                </div>
            </div>

            <!-- Preview Data Perangkat yang Dipilih -->
            <div x-show="selectedDeviceData.id" x-cloak class="p-4 bg-amber-50/70 border border-amber-200 rounded-xl transition-all">
                <div class="flex items-center gap-2 mb-2">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                    <span class="text-xs font-bold text-amber-900 uppercase tracking-wide">Detail Unit Perangkat Terpilih:</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">No. Registrasi</span>
                        <span class="font-bold text-brand-700 font-mono text-sm" x-text="selectedDeviceData.reg"></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Nama / Merk</span>
                        <span class="font-semibold text-slate-800" x-text="selectedDeviceData.name"></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Area Asal</span>
                        <span class="font-bold text-blue-700" x-text="selectedDeviceData.area"></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Posisi Terkini</span>
                        <span class="font-medium text-slate-700" x-text="selectedDeviceData.location"></span>
                    </div>
                </div>
            </div>

            <!-- Catatan Pengembalian -->
            <div>
                <label for="notes" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Catatan / Keterangan Pengembalian (Opsional)
                </label>
                <textarea name="notes" id="notes" rows="2" placeholder="Tuliskan nomor surat jalan atau alasan pengembalian..."
                          class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all"></textarea>
            </div>

            <!-- Warning Notice -->
            <div class="p-3.5 bg-rose-50 border border-rose-200 rounded-xl flex items-start gap-3 text-xs text-rose-900 leading-relaxed">
                <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <div>
                    <span class="font-bold block">Pemberitahuan Otomatis:</span>
                    Saat Anda mengklik tombol di bawah, status pengembalian ke Lintasarta akan dicatat dan <strong>data perangkat ini akan otomatis dihapus / hilang dari inventaris sistem</strong>.
                </div>
            </div>

            <!-- Action Button -->
            <div class="pt-2 flex items-center justify-end gap-3">
                <a href="{{ route('admin.devices.index') }}" class="px-5 py-2.5 text-xs font-semibold text-slate-600 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                    Batal
                </a>
                <button type="submit" 
                        class="inline-flex items-center gap-2 px-6 py-2.5 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-xl shadow-md shadow-amber-600/20 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Submit & Kembalikan ke Lintasarta</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Table Section: Daftar Perangkat yang Ada -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Daftar Perangkat Aktif</h3>
                <p class="text-xs text-slate-500">Pilih langsung unit di bawah untuk mengisi form pengembalian</p>
            </div>
            
            <!-- Filter / Cari -->
            <form action="{{ route('admin.devices.returns') }}" method="GET" class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Cari No. Registrasi / Nama..."
                       class="px-3.5 py-1.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all w-full sm:w-56">

                <select name="area" class="px-3 py-1.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all">
                    <option value="">Semua Area</option>
                    @foreach(['BWA', 'BDA', 'SEOA', 'SAST', 'CDA', 'NEA'] as $ar)
                        <option value="{{ $ar }}" {{ request('area') == $ar ? 'selected' : '' }}>{{ $ar }}</option>
                    @endforeach
                </select>

                <button type="submit" class="px-4 py-1.5 text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 rounded-xl transition-colors">
                    Filter
                </button>
            </form>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/75 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="px-6 py-3.5">Nomor Registrasi</th>
                        <th class="px-6 py-3.5">Nama & Merk</th>
                        <th class="px-6 py-3.5">Area</th>
                        <th class="px-6 py-3.5">Status Stok</th>
                        <th class="px-6 py-3.5">Posisi / Pemegang Terkini</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-600">
                    @forelse($devices as $device)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-bold font-mono text-slate-900">{{ $device->registration_number }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-semibold text-slate-800">{{ $device->name }}</div>
                                <div class="text-[11px] text-slate-400">{{ $device->brand ?: '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($device->area)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ $device->area }}
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400 font-medium">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold
                                    @if($device->stock_status === 'tersedia_pt') bg-blue-50 text-blue-700 border border-blue-200
                                    @elseif($device->stock_status === 'dibawa_teknisi') bg-amber-50 text-amber-700 border border-amber-200
                                    @elseif($device->stock_status === 'terpasang_pelanggan') bg-emerald-50 text-emerald-700 border border-emerald-200
                                    @elseif($device->stock_status === 'pending') bg-purple-50 text-purple-700 border border-purple-200
                                    @elseif($device->stock_status === 'rusak') bg-rose-50 text-rose-700 border border-rose-200
                                    @else bg-slate-100 text-slate-700 border border-slate-200
                                    @endif">
                                    {{ $device->stock_status_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($device->currentTechnician)
                                    <div class="font-medium text-slate-900">{{ $device->currentTechnician->name }}</div>
                                    <div class="text-[11px] text-slate-400">Teknisi</div>
                                @else
                                    <div class="text-slate-800 font-medium">{{ $device->current_location }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <button type="button" 
                                        @click="selectDeviceFromTable('{{ $device->id }}', '{{ $device->registration_number }}', '{{ addslashes($device->name) }}', '{{ $device->area ?: '-' }}', '{{ $device->current_location }}')"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200 rounded-xl transition-colors">
                                    <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                    <span>Pilih Unit</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-slate-400">
                                <p class="text-sm">Tidak ada data perangkat aktif yang dapat dikembalikan.</p>
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

<script>
    function returnDeviceApp() {
        return {
            selectedDeviceId: '',
            selectedDeviceData: {
                id: '',
                reg: '',
                name: '',
                area: '',
                location: ''
            },
            onDeviceChange() {
                const select = document.getElementById('device_id');
                const opt = select.options[select.selectedIndex];
                if (opt && opt.value) {
                    this.selectedDeviceData = {
                        id: opt.value,
                        reg: opt.dataset.reg || '',
                        name: opt.dataset.name || '',
                        area: opt.dataset.area || '-',
                        location: opt.dataset.location || ''
                    };
                } else {
                    this.selectedDeviceData = { id: '', reg: '', name: '', area: '', location: '' };
                }
            },
            selectDeviceFromTable(id, reg, name, area, location) {
                this.selectedDeviceId = id;
                this.selectedDeviceData = { id, reg, name, area, location };
                const select = document.getElementById('device_id');
                if (select) {
                    select.value = id;
                }
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        };
    }

    function confirmReturnDevice(e) {
        const destSelect = document.getElementById('destination');
        const destination = destSelect ? destSelect.value : '';
        const devSelect = document.getElementById('device_id');

        if (!devSelect || !devSelect.value) {
            alert('Silakan pilih perangkat yang ingin dikembalikan.');
            e.preventDefault();
            return false;
        }

        if (!destination) {
            alert('Silakan pilih tujuan pengembalian ke Lintasarta.');
            e.preventDefault();
            return false;
        }

        const msg = `Konfirmasi Pengembalian:\n\nApakah Anda yakin ingin mengembalikan unit perangkat ini ke Lintasarta (${destination})?\n\nPERINGATAN: Data perangkat ini akan otomatis dikeluarkan / hilang dari sistem.`;
        if (!confirm(msg)) {
            e.preventDefault();
            return false;
        }

        return true;
    }
</script>
@endsection
