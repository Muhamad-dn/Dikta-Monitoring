@extends('layouts.app')

@section('title', 'Laporan Penyelesaian - ' . $task->nojar)
@section('page_title', 'Laporan Hasil Pekerjaan: ' . $task->nojar)
@section('page_subtitle', 'Pilih hasil akhir pekerjaan, ambil satu foto bukti lapangan langsung, dan isi catatan teknisi')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Form Penyelesaian NOJAR: <span class="font-mono text-emerald-700">{{ $task->nojar }}</span></h2>
                <p class="text-xs text-slate-500 mt-0.5">Pelanggan: <strong class="text-slate-800">{{ $task->customer_name }}</strong> &bull; {{ $task->category }}</p>
            </div>
            <a href="{{ route('technician.tasks.show', $task) }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">
                &larr; Batal & Kembali
            </a>
        </div>

        <form action="{{ route('technician.tasks.submit-complete', $task) }}" method="POST" enctype="multipart/form-data" id="completionForm" class="p-6 space-y-6">
            @csrf

            <!-- Status Outcome Selection -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                    Hasil Akhir Pekerjaan Lapangan <span class="text-rose-500">*</span>
                </label>
                
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <!-- Terinstal -->
                    <label class="p-4 rounded-xl border-2 cursor-pointer flex flex-col items-center text-center transition-all has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50/60 has-[:checked]:shadow-xs hover:border-slate-300">
                        <input type="radio" name="status" value="terinstal" checked class="text-emerald-600 focus:ring-emerald-500 mb-2" onchange="updateEvidenceGuidance()">
                        <span class="font-bold text-xs text-slate-900">Terinstal / Selesai</span>
                        <span class="text-[10px] text-slate-500 mt-1">Perangkat terpasang baik di pelanggan & online</span>
                    </label>

                    <!-- Pending -->
                    <label class="p-4 rounded-xl border-2 cursor-pointer flex flex-col items-center text-center transition-all has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50/60 has-[:checked]:shadow-xs hover:border-slate-300">
                        <input type="radio" name="status" value="pending" class="text-amber-600 focus:ring-amber-500 mb-2" onchange="updateEvidenceGuidance()">
                        <span class="font-bold text-xs text-slate-900">Pending / Tertunda</span>
                        <span class="text-[10px] text-slate-500 mt-1">Pelanggan tidak ada / kendala izin / FO putus</span>
                    </label>

                    <!-- Gagal -->
                    <label class="p-4 rounded-xl border-2 cursor-pointer flex flex-col items-center text-center transition-all has-[:checked]:border-rose-500 has-[:checked]:bg-rose-50/60 has-[:checked]:shadow-xs hover:border-slate-300">
                        <input type="radio" name="status" value="gagal_install" class="text-rose-600 focus:ring-rose-500 mb-2" onchange="updateEvidenceGuidance()">
                        <span class="font-bold text-xs text-slate-900">Gagal Install</span>
                        <span class="text-[10px] text-slate-500 mt-1">Batal langganan / tidak tercover redaman</span>
                    </label>
                </div>
            </div>

            <!-- Single Direct Evidence Photo Section -->
            <div class="pt-4 border-t border-slate-100">
                <div class="flex items-center justify-between mb-2">
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-indigo-700 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Foto Bukti Lapangan (Satu Foto Bukti)
                        </h3>
                        <p id="evidencePromptText" class="text-xs text-slate-600 mt-0.5 font-medium">
                            Ambil 1 foto bukti perangkat ONT/modem yang telah terpasang & lampu menyala normal di rumah/kantor pelanggan.
                        </p>
                    </div>
                    <span id="evidenceStatusBadge" class="hidden sm:inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                        Wajib 1 Foto
                    </span>
                </div>

                <!-- Hidden Native File & Base64 Inputs (NO UGLY CHOOSE FILE) -->
                <input type="file" 
                       name="evidence_photo" 
                       id="evidence_photo" 
                       accept="image/*" 
                       capture="environment" 
                       class="hidden" 
                       onchange="handleDirectFileChange(event)">

                <input type="file" 
                       id="evidence_photo_gallery" 
                       accept="image/*" 
                       class="hidden" 
                       onchange="handleDirectFileChange(event)">

                <input type="hidden" 
                       name="evidence_photo_base64" 
                       id="evidence_photo_base64">

                <!-- Capture State: Empty (No Photo Yet) -->
                <div id="capturePromptCard" class="mt-3 p-6 border-2 border-dashed border-indigo-200 rounded-2xl bg-indigo-50/30 text-center hover:bg-indigo-50/60 transition-all">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-indigo-600 to-brand-500 text-white flex items-center justify-center mx-auto mb-3 shadow-md shadow-indigo-500/20 ring-4 ring-indigo-100">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>

                    <h4 class="text-sm font-bold text-slate-800">Ambil Foto Bukti Pekerjaan</h4>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1 mb-5">
                        Langsung gunakan kamera perangkat Anda untuk memotret bukti lapangan tanpa perlu memilih file manual.
                    </p>

                    <!-- Direct Camera Trigger Buttons -->
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-2.5">
                        <!-- 1. Direct Native Camera Button (Primary on Mobile) -->
                        <button type="button" 
                                onclick="openNativeCamera()" 
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 text-white text-xs font-bold shadow-md shadow-brand-500/25 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span>Buka Kamera & Foto Langsung</span>
                        </button>

                        <!-- 2. Live In-Browser Camera Modal (For Desktop/Laptop/Tablet) -->
                        <button type="button" 
                                onclick="openLiveCameraModal()" 
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-2xs transition-all">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                            <span>Kamera Web (Live Stream)</span>
                        </button>
                    </div>

                    <!-- Subtle gallery fallback link -->
                    <div class="mt-4 pt-3 border-t border-indigo-100/70">
                        <button type="button" 
                                onclick="openGalleryPicker()" 
                                class="text-[11px] font-medium text-slate-400 hover:text-indigo-600 transition-colors">
                            Atau pilih foto yang sudah tersimpan di galeri &rarr;
                        </button>
                    </div>
                </div>

                <!-- Preview State (Shown when photo has been taken) -->
                <div id="photoPreviewContainer" class="hidden mt-3 p-4 rounded-2xl border border-emerald-200 bg-emerald-50/30">
                    <div class="flex flex-col sm:flex-row items-center gap-4">
                        <!-- Image thumbnail -->
                        <div class="relative w-full sm:w-48 h-48 sm:h-36 rounded-xl overflow-hidden bg-slate-900 border-2 border-emerald-500 shadow-sm shrink-0">
                            <img id="previewImage" src="" alt="Preview Foto Bukti" class="w-full h-full object-cover">
                            <div class="absolute top-2 left-2 px-2 py-0.5 bg-emerald-600/90 backdrop-blur-xs text-white text-[10px] font-bold rounded-md flex items-center gap-1 shadow-xs">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Siap Terkirim
                            </div>
                        </div>

                        <!-- Details & Action Buttons -->
                        <div class="flex-1 w-full text-center sm:text-left">
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 mb-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-pulse"></span>
                                Foto Bukti Berhasil Diambil
                            </div>
                            <h4 id="previewFileName" class="text-xs font-bold text-slate-800 break-all">bukti_lapangan.jpg</h4>
                            <p id="previewFileSize" class="text-[11px] text-slate-500 mt-0.5">Ukuran: - &bull; Foto siap dilampirkan ke laporan</p>

                            <div class="mt-4 flex flex-wrap items-center justify-center sm:justify-start gap-2">
                                <button type="button" 
                                        onclick="openNativeCamera()" 
                                        class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-100 rounded-lg shadow-2xs transition-colors">
                                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    <span>Ambil Ulang Foto</span>
                                </button>
                                <button type="button" 
                                        onclick="clearCapturedPhoto()" 
                                        class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50 rounded-lg transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    <span>Hapus</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Work Notes -->
            <div class="pt-4 border-t border-slate-100">
                <label for="status_notes" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Catatan Hasil Kerja / Keterangan Lapangan <span class="text-rose-500">*</span>
                </label>
                <textarea name="status_notes" id="status_notes" rows="3" required
                          placeholder="Contoh: Pemasangan sukses, ONT aktif dengan redaman -19.2 dBm, pelanggan puas dan tes koneksi lancar..."
                          class="w-full px-4 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all font-medium">{{ old('status_notes') }}</textarea>
            </div>

            <!-- Form Actions -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('technician.tasks.show', $task) }}" class="px-4 py-2.5 text-xs font-semibold text-slate-600 hover:text-slate-800">
                    Batal
                </a>
                <button type="submit" 
                        class="px-6 py-2.5 text-xs font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 rounded-xl shadow-md shadow-emerald-600/20 transition-all flex items-center gap-1.5">
                    <span>Kirim Laporan & Selesaikan Tugas</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </button>
            </div>

        </form>
    </div>
</div>

<!-- ======================================================== -->
<!-- LIVE CAMERA STREAM MODAL (WebRTC) -->
<!-- ======================================================== -->
<div id="liveCameraModal" class="fixed inset-0 z-50 hidden bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-700 rounded-3xl overflow-hidden shadow-2xl max-w-md w-full text-white">
        <div class="px-5 py-4 border-b border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-rose-500 animate-ping"></span>
                <h3 class="text-sm font-bold text-white">Kamera Langsung Lapangan</h3>
            </div>
            <button type="button" onclick="closeLiveCameraModal()" class="text-slate-400 hover:text-white p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="relative bg-black aspect-4/3 flex items-center justify-center overflow-hidden">
            <video id="liveVideo" autoplay playsinline class="w-full h-full object-cover"></video>
            <canvas id="liveCanvas" class="hidden"></canvas>
            
            <div id="cameraLoadingMsg" class="absolute inset-0 flex flex-col items-center justify-center bg-slate-900 text-slate-400 text-xs gap-2">
                <svg class="w-8 h-8 text-brand-500 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span>Menghubungkan ke kamera perangkat...</span>
            </div>
        </div>

        <div class="p-4 bg-slate-950 border-t border-slate-800 flex items-center justify-between gap-3">
            <button type="button" 
                    onclick="switchCameraFacing()" 
                    class="p-3 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-300 transition-colors"
                    title="Ganti Kamera Depan/Belakang">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </button>

            <!-- Big Shutter Button -->
            <button type="button" 
                    onclick="captureLiveFrame()" 
                    class="flex-1 py-3 px-6 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white font-bold text-sm shadow-lg shadow-emerald-500/30 flex items-center justify-center gap-2 transition-all">
                <span class="w-4 h-4 rounded-full border-2 border-white bg-rose-500"></span>
                <span>Jepret Foto Sekarang</span>
            </button>

            <button type="button" 
                    onclick="closeLiveCameraModal()" 
                    class="p-3 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-300 transition-colors">
                <span class="text-xs font-semibold">Tutup</span>
            </button>
        </div>
    </div>
</div>

<script>
    let activeMediaStream = null;
    let currentFacingMode = 'environment'; // 'environment' = back camera, 'user' = selfie

    // 1. Dynamic guidance text according to chosen outcome
    function updateEvidenceGuidance() {
        const selectedStatus = document.querySelector('input[name="status"]:checked')?.value || 'terinstal';
        const promptEl = document.getElementById('evidencePromptText');
        const notesEl = document.getElementById('status_notes');

        if (selectedStatus === 'terinstal') {
            promptEl.textContent = 'Ambil 1 foto bukti perangkat ONT/modem yang telah terpasang & lampu menyala normal di rumah/kantor pelanggan.';
            if (!notesEl.value || notesEl.value.includes('Kendala') || notesEl.value.includes('Gagal')) {
                notesEl.placeholder = 'Contoh: Pemasangan sukses, ONT aktif dengan redaman -19.2 dBm, tes speedtest 50 Mbps lancar...';
            }
        } else if (selectedStatus === 'pending') {
            promptEl.textContent = 'Ambil 1 foto bukti kondisi lapangan penyebab penundaan (misal: rumah kosong, izin lingkungan, atau kabel FO putus).';
            notesEl.placeholder = 'Contoh: Pekerjaan tertunda karena pemilik rumah belum ada di tempat. Sudah koordinasi via WA untuk dijadwal ulang besok...';
        } else if (selectedStatus === 'gagal_install') {
            promptEl.textContent = 'Ambil 1 foto bukti kondisi kendala pembatalan instalasi atau tidak tercover redaman ODP.';
            notesEl.placeholder = 'Contoh: Instalasi dibatalkan karena jarak rumah ke ODP melebihi 250 meter dan redaman drop hingga -32 dBm...';
        }
    }

    // 2. Open Native Device Camera
    function openNativeCamera() {
        const nativeInput = document.getElementById('evidence_photo');
        nativeInput.click();
    }

    // 3. Open Gallery Picker
    function openGalleryPicker() {
        const galleryInput = document.getElementById('evidence_photo_gallery');
        galleryInput.click();
    }

    // 4. Handle Direct File Selection / Photo Snapped from Camera
    function handleDirectFileChange(event) {
        const file = event.target.files[0];
        if (!file) return;

        // Synchronize with main form file input if chosen from gallery input
        if (event.target.id === 'evidence_photo_gallery') {
            const mainInput = document.getElementById('evidence_photo');
            try {
                const dt = new DataTransfer();
                dt.items.add(file);
                mainInput.files = dt.files;
            } catch (e) {
                console.log('DataTransfer not supported, proceeding with reader', e);
            }
        }

        // Reset base64 input because real file is present
        document.getElementById('evidence_photo_base64').value = '';

        displayPhotoPreview(file);
    }

    // 5. Display Photo in UI Preview
    function displayPhotoPreview(file, dataUrl = null) {
        const previewContainer = document.getElementById('photoPreviewContainer');
        const promptCard = document.getElementById('capturePromptCard');
        const previewImg = document.getElementById('previewImage');
        const fileNameEl = document.getElementById('previewFileName');
        const fileSizeEl = document.getElementById('previewFileSize');

        if (dataUrl) {
            previewImg.src = dataUrl;
            fileNameEl.textContent = 'kamera_langsung_' + Date.now() + '.jpg';
            fileSizeEl.textContent = 'Hasil jepretan kamera langsung &bull; Siap dilampirkan';
            promptCard.classList.add('hidden');
            previewContainer.classList.remove('hidden');
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            fileNameEl.textContent = file.name || 'foto_bukti_lapangan.jpg';
            const sizeKB = (file.size / 1024).toFixed(1);
            fileSizeEl.textContent = `Ukuran: ${sizeKB} KB &bull; Siap dilampirkan ke laporan`;

            promptCard.classList.add('hidden');
            previewContainer.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    }

    // 6. Clear Photo
    function clearCapturedPhoto() {
        document.getElementById('evidence_photo').value = '';
        document.getElementById('evidence_photo_gallery').value = '';
        document.getElementById('evidence_photo_base64').value = '';
        document.getElementById('previewImage').src = '';

        document.getElementById('photoPreviewContainer').classList.add('hidden');
        document.getElementById('capturePromptCard').classList.remove('hidden');
    }

    // 7. Live Camera Stream Modal (WebRTC)
    async function openLiveCameraModal() {
        const modal = document.getElementById('liveCameraModal');
        const video = document.getElementById('liveVideo');
        const loadingMsg = document.getElementById('cameraLoadingMsg');

        modal.classList.remove('hidden');
        loadingMsg.classList.remove('hidden');

        try {
            if (activeMediaStream) {
                activeMediaStream.getTracks().forEach(track => track.stop());
            }

            activeMediaStream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: currentFacingMode,
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                },
                audio: false
            });

            video.srcObject = activeMediaStream;
            video.onloadedmetadata = () => {
                loadingMsg.classList.add('hidden');
            };
        } catch (err) {
            alert('Tidak dapat mengakses kamera: ' + (err.message || 'Izin kamera ditolak atau kamera tidak ditemukan'));
            closeLiveCameraModal();
            // Fallback to native camera input
            openNativeCamera();
        }
    }

    function switchCameraFacing() {
        currentFacingMode = (currentFacingMode === 'environment') ? 'user' : 'environment';
        openLiveCameraModal();
    }

    function captureLiveFrame() {
        const video = document.getElementById('liveVideo');
        const canvas = document.getElementById('liveCanvas');

        if (!video.videoWidth) return;

        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

        const dataUrl = canvas.toDataURL('image/jpeg', 0.85);

        // Put into hidden base64 input
        document.getElementById('evidence_photo_base64').value = dataUrl;

        // Try to also populate file input using DataTransfer
        try {
            canvas.toBlob((blob) => {
                if (blob) {
                    const file = new File([blob], `kamera_${Date.now()}.jpg`, { type: 'image/jpeg' });
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    document.getElementById('evidence_photo').files = dt.files;
                }
            }, 'image/jpeg', 0.85);
        } catch (e) {
            console.log('DataTransfer blob conversion skipped', e);
        }

        displayPhotoPreview(null, dataUrl);
        closeLiveCameraModal();
    }

    function closeLiveCameraModal() {
        const modal = document.getElementById('liveCameraModal');
        modal.classList.add('hidden');

        if (activeMediaStream) {
            activeMediaStream.getTracks().forEach(track => track.stop());
            activeMediaStream = null;
        }
    }

    // Initialize prompt text on load
    document.addEventListener('DOMContentLoaded', () => {
        updateEvidenceGuidance();
    });
</script>
@endsection
