@extends('layouts.app')

@section('title', 'Scan Pengambilan Perangkat di PT - ' . $task->nojar)
@section('page_title', 'Validasi Pengambilan di Gudang PT')
@section('page_subtitle', 'Scan barcode/QR perangkat fisik di kantor PT DIKTA sebelum Anda berangkat ke lokasi')

@section('content')
<div class="max-w-xl mx-auto space-y-5" x-data="technicianPickup()">

    <!-- Expected Device Card -->
    <div class="bg-amber-500/10 border-2 border-amber-300 rounded-2xl p-5 shadow-xs">
        <span class="text-[10px] font-bold uppercase tracking-wider bg-amber-200 text-amber-900 px-2 py-0.5 rounded-full inline-block mb-2">
            Target Perangkat Yang Wajib Diambil
        </span>
        
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-base font-extrabold text-slate-900">{{ $task->device->name }}</h3>
                <p class="text-xs text-slate-600 font-mono mt-0.5">SN: {{ $task->device->serial_number }} &bull; Merk: {{ $task->device->brand }}</p>
            </div>
            <div class="text-right">
                <span class="text-[10px] uppercase font-bold text-slate-400 block">No. Registrasi:</span>
                <span class="font-mono text-xl font-black text-brand-700">{{ $task->device->registration_number }}</span>
            </div>
        </div>

        <div class="mt-3 pt-3 border-t border-amber-200/60 text-xs text-amber-900 flex items-center gap-1.5">
            <svg class="w-4 h-4 text-amber-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Ambil unit di: <strong>{{ $task->device->current_location }}</strong></span>
        </div>
    </div>

    <!-- Scanner Box -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>Pindai Barcode / QR Perangkat</span>
            </h3>
            <button type="button" @click="toggleCamera()" 
                    class="px-2.5 py-1 text-xs font-bold rounded-lg border transition-all"
                    :class="cameraActive ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-brand-50 text-brand-700 border-brand-200'">
                <span x-text="cameraActive ? 'Tutup Kamera' : 'Buka Kamera'"></span>
            </button>
        </div>

        <div class="p-6 space-y-5">
            <!-- Camera Render Box -->
            <div id="tech-reader" class="w-full bg-slate-950 rounded-xl overflow-hidden min-h-[220px] flex items-center justify-center border border-slate-800 text-slate-400 text-xs text-center p-4">
                <div x-show="!cameraActive">
                    <svg class="w-12 h-12 mx-auto text-slate-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    Arahkan kamera ke barcode stiker perangkat.<br>
                    <button type="button" @click="toggleCamera()" class="mt-2 text-indigo-400 font-semibold underline">Nyalakan Kamera HP</button>
                </div>
            </div>

            <!-- Form Konfirmasi Pengambilan -->
            <form action="{{ route('technician.tasks.confirm-pickup', $task) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <div>
                    <label for="registration_number" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Nomor Registrasi Hasil Scan / Input Manual <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="registration_number" id="registration_number" required 
                           x-model="scannedCode"
                           placeholder="Contoh: {{ $task->device->registration_number }}"
                           class="w-full px-4 py-3 text-sm bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white font-mono font-black text-brand-800 uppercase transition-all">
                    
                    <!-- Quick Match Button for Easy Demo -->
                    <div class="mt-2 flex items-center justify-between text-[11px]">
                        <span class="text-slate-400">Tekan tombol cepat jika manual:</span>
                        <button type="button" @click="scannedCode = '{{ $task->device->registration_number }}'" 
                                class="font-mono font-bold text-amber-700 hover:text-amber-900 bg-amber-50 hover:bg-amber-100 px-2.5 py-0.5 rounded border border-amber-200">
                            Isi {{ $task->device->registration_number }}
                        </button>
                    </div>
                </div>

                <div>
                    <label for="pickup_photo" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Foto Bukti Pengambilan di Gudang PT (Opsional)
                    </label>
                    <input type="file" name="pickup_photo" id="pickup_photo" accept="image/*" capture="environment"
                           class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                    <p class="text-[10px] text-slate-400 mt-1">Gunakan kamera HP untuk memotret unit yang Anda ambil di rak PT.</p>
                </div>

                <div>
                    <label for="notes" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Catatan Pengambilan
                    </label>
                    <input type="text" name="notes" id="notes" 
                           placeholder="Contoh: Unit lengkap kabel adaptor & patchcord kuning"
                           class="w-full px-4 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all">
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-3">
                    <a href="{{ route('technician.tasks.show', $task) }}" class="px-4 py-2.5 text-xs font-semibold text-slate-600 hover:text-slate-800">
                        Batal
                    </a>
                    <button type="submit" 
                            class="px-5 py-2.5 text-xs font-bold text-white bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-700 hover:to-orange-700 rounded-xl shadow-md shadow-amber-600/20 transition-all">
                        Validasi & Ambil Perangkat di PT &rarr;
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
function technicianPickup() {
    return {
        scannedCode: '{{ old("registration_number") }}',
        cameraActive: false,
        qrScanner: null,

        toggleCamera() {
            if (this.cameraActive) {
                this.stopCamera();
            } else {
                this.startCamera();
            }
        },

        async startCamera() {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                alert("Perhatian: Browser HP memblokir kamera jika website dibuka melalui HTTP biasa non-localhost.\n\nSilakan buka website melalui link HTTPS Cloudflare (jalankan mulai-aplikasi-online.bat di laptop) agar izin kamera di HP otomatis aktif!");
                return;
            }

            const self = this;
            try {
                if (this.qrScanner) {
                    try { await this.qrScanner.stop(); } catch(e) {}
                    try { await this.qrScanner.clear(); } catch(e) {}
                }

                this.qrScanner = new Html5Qrcode("tech-reader", {
                    formatsToSupport: typeof Html5QrcodeSupportedFormats !== 'undefined' ? [
                        Html5QrcodeSupportedFormats.CODE_128,
                        Html5QrcodeSupportedFormats.CODE_39,
                        Html5QrcodeSupportedFormats.EAN_13,
                        Html5QrcodeSupportedFormats.EAN_8,
                        Html5QrcodeSupportedFormats.UPC_A,
                        Html5QrcodeSupportedFormats.UPC_E,
                        Html5QrcodeSupportedFormats.QR_CODE,
                    ] : undefined,
                    verbose: false
                });

                const config = {
                    fps: 15,
                    qrbox: (viewfinderWidth, viewfinderHeight) => {
                        const w = Math.min(Math.floor(viewfinderWidth * 0.85), 280);
                        const h = Math.min(Math.floor(w * 0.6), 200);
                        return { width: Math.max(w, 180), height: Math.max(h, 120) };
                    }
                };

                const onScan = (decodedText) => {
                    self.scannedCode = decodedText.trim();
                    self.stopCamera();
                    try {
                        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                        const osc = audioCtx.createOscillator();
                        osc.frequency.setValueAtTime(800, audioCtx.currentTime);
                        osc.connect(audioCtx.destination);
                        osc.start();
                        osc.stop(audioCtx.currentTime + 0.1);
                    } catch(e) {}
                };

                try {
                    await this.qrScanner.start({ facingMode: "environment" }, config, onScan, () => {});
                } catch(facingErr) {
                    const devices = await Html5Qrcode.getCameras();
                    if (devices && devices.length) {
                        const backCam = devices.find(d => d.label.toLowerCase().includes('back') || d.label.toLowerCase().includes('rear')) || devices[devices.length - 1];
                        await this.qrScanner.start(backCam.id, config, onScan, () => {});
                    } else {
                        throw facingErr;
                    }
                }
                self.cameraActive = true;
            } catch (err) {
                console.error("Tech camera error:", err);
                alert("Kamera tidak dapat diakses: " + (err.message || err) + "\n\nPastikan Anda mengizinkan akses kamera di browser ponsel.");
                self.cameraActive = false;
            }
        },

        async stopCamera() {
            if (this.qrScanner && this.cameraActive) {
                try {
                    await this.qrScanner.stop();
                } catch (e) {}
                this.cameraActive = false;
            } else {
                this.cameraActive = false;
            }
        }
    }
}
</script>
@endpush
@endsection
