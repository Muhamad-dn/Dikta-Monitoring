<!-- Modal Import Excel / CSV -->
<div id="importExcelModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" onclick="closeImportExcelModal()"></div>

    <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-xl border border-slate-200">
            
            <!-- Modal Header -->
            <div id="modalHeaderBg" class="bg-gradient-to-r from-emerald-600 to-teal-700 px-6 py-4 flex items-center justify-between text-white transition-all">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-white" id="modal-title">Import Perangkat ke Gudang HB JKT</h3>
                        <p class="text-xs text-white/90" id="modal-subtitle">Tambahkan banyak data perangkat sekaligus ke Stok Gudang HB Jakarta</p>
                    </div>
                </div>
                <button type="button" onclick="closeImportExcelModal()" class="text-white/80 hover:text-white rounded-lg p-1.5 hover:bg-white/10 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 space-y-5">
                
                <!-- Form Upload -->
                <form id="importExcelForm" action="{{ route('admin.devices.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <!-- 1. Pilihan Gudang Penyimpanan Tujuan -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-xs font-bold text-slate-800">
                                1. Pilih Gudang Tujuan Penyimpanan: <span class="text-rose-500">*</span>
                            </label>
                            <span class="text-[11px] text-slate-400">Pilih salah satu gudang</span>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <!-- Opsi Gudang HB JKT -->
                            <label id="cardHubJkt" onclick="switchWarehouseHub('hb_jkt')" class="relative flex items-center gap-3 p-3.5 rounded-xl border-2 cursor-pointer transition-all border-emerald-500 bg-emerald-50/70 shadow-xs ring-1 ring-emerald-500/20">
                                <input type="radio" name="warehouse_hub" value="hb_jkt" id="radioHubJkt" checked class="sr-only">
                                <div class="w-9 h-9 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-xs font-bold text-emerald-950">Gudang HB JKT</span>
                                        <span id="badgeJkt" class="text-[9px] font-bold px-1.5 py-0.5 rounded-full bg-emerald-200 text-emerald-800">Aktif</span>
                                    </div>
                                    <p class="text-[11px] text-emerald-700 truncate">Homebase Jakarta</p>
                                </div>
                            </label>

                            <!-- Opsi Gudang HB CKR -->
                            <label id="cardHubCkr" onclick="switchWarehouseHub('hb_ckr')" class="relative flex items-center gap-3 p-3.5 rounded-xl border-2 cursor-pointer transition-all border-slate-200 bg-white hover:border-slate-300">
                                <input type="radio" name="warehouse_hub" value="hb_ckr" id="radioHubCkr" class="sr-only">
                                <div class="w-9 h-9 rounded-lg bg-teal-100 text-teal-700 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-xs font-bold text-slate-800">Gudang HB CKR</span>
                                        <span id="badgeCkr" class="hidden text-[9px] font-bold px-1.5 py-0.5 rounded-full bg-teal-200 text-teal-800">Aktif</span>
                                    </div>
                                    <p class="text-[11px] text-slate-500 truncate">Homebase Cikarang</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- 2. Download Template Banner Sesuai Gudang -->
                    <div class="bg-slate-50/90 border border-slate-200 rounded-xl p-4">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between flex-wrap gap-1">
                                    <h4 class="text-xs font-bold text-slate-900">2. Unduh Format Template Resmi</h4>
                                    <span id="templateTargetBadge" class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Format: Gudang HB JKT
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-600 mt-0.5">Gunakan format template ini agar data kolom terisi otomatis ke gudang yang dipilih:</p>
                                <div class="flex flex-wrap gap-2 mt-2.5">
                                    <a id="btnDownloadXlsx" href="{{ route('admin.devices.import-template', ['format' => 'xlsx', 'hub' => 'hb_jkt']) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-700 hover:bg-emerald-800 text-white shadow-xs transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
                                        <span id="btnDownloadXlsxText">Download Template Excel (.xlsx)</span>
                                    </a>
                                    <a id="btnDownloadCsv" href="{{ route('admin.devices.import-template', ['format' => 'csv', 'hub' => 'hb_jkt']) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-white hover:bg-slate-100 text-slate-700 border border-slate-300 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <span id="btnDownloadCsvText">Download Format CSV (.csv)</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Pilih File Spreadsheet Excel / CSV -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2">3. Pilih File Spreadsheet Excel / CSV:</label>
                        <div class="relative border-2 border-dashed border-slate-300 hover:border-emerald-500 rounded-xl p-6 text-center transition-colors bg-slate-50/50 hover:bg-emerald-50/20 cursor-pointer" onclick="document.getElementById('importFileInput').click()">
                            <input type="file" id="importFileInput" name="file" accept=".xlsx,.csv" required class="hidden" onchange="handleFileSelect(this)">
                            
                            <div id="fileUploadPrompt" class="space-y-2">
                                <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 text-slate-500 flex items-center justify-center">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                </div>
                                <div>
                                    <span class="text-xs font-bold text-emerald-700">Klik untuk mencari file</span>
                                    <span class="text-xs text-slate-500"> atau seret file ke sini</span>
                                </div>
                                <p class="text-[11px] text-slate-400">Mendukung file .xlsx (Excel) atau .csv (Maksimal 10MB)</p>
                            </div>

                            <div id="fileSelectedInfo" class="hidden flex items-center justify-between p-3 bg-white rounded-lg border border-emerald-200">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
                                    </div>
                                    <div class="text-left">
                                        <p id="selectedFileName" class="text-xs font-bold text-slate-800 truncate max-w-[280px]">file.xlsx</p>
                                        <p id="selectedFileSize" class="text-[10px] text-slate-400">0 KB</p>
                                    </div>
                                </div>
                                <button type="button" onclick="resetFileSelect(event)" class="text-slate-400 hover:text-rose-600 p-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Smart Feature Notes -->
                    <div class="bg-slate-50 rounded-xl p-3.5 border border-slate-200 text-[11px] space-y-1.5 text-slate-600">
                        <div class="font-bold text-slate-800 flex items-center gap-1.5">
                            <span class="text-emerald-600 text-sm">✨</span>
                            <span>Fitur Cerdas Otomatis:</span>
                        </div>
                        <ul class="space-y-1 pl-4 list-disc marker:text-emerald-500">
                            <li><strong class="text-slate-700">Target Stok Terjamin:</strong> Seluruh unit yang diimport langsung dialokasikan ke <span id="smartHubText" class="text-emerald-700 font-bold">Gudang HB JKT</span>.</li>
                            <li><strong class="text-slate-700">Barcode Lintasarta Otomatis:</strong> Jika kolom registrasi/SN berisi barcode panjang seperti <code class="bg-white px-1 py-0.5 rounded border border-slate-300 font-mono text-[10px] text-emerald-700">B2WN0262020HA0267</code>, sistem otomatis mengekstrak <code class="bg-white px-1 py-0.5 rounded border border-slate-300 font-mono text-[10px] text-brand-700">HA0267</code> sebagai Nomor Registrasi!</li>
                            <li><strong class="text-slate-700">Deteksi Merk & Tipe:</strong> Merk & Tipe otomatis terdeteksi dari nama perangkat (Huawei, ZTE, MikroTik, ONT, Router).</li>
                            <li><strong class="text-slate-700">Pembaruan Tanpa Duplikasi:</strong> Jika Nomor Registrasi sudah ada di database, data akan otomatis diupdate.</li>
                        </ul>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                        <button type="button" onclick="closeImportExcelModal()" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 hover:bg-slate-100 rounded-xl transition-colors">
                            Batal
                        </button>
                        <button type="submit" id="btnSubmitImport" class="inline-flex items-center gap-2 px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            <span id="btnSubmitImportText">Unggah ke Gudang HB JKT</span>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<script>
const baseTemplateUrl = "{{ route('admin.devices.import-template') }}";

function switchWarehouseHub(hub) {
    const isCkr = hub === 'hb_ckr';
    
    const radioJkt = document.getElementById('radioHubJkt');
    const radioCkr = document.getElementById('radioHubCkr');
    if (radioJkt && radioCkr) {
        radioJkt.checked = !isCkr;
        radioCkr.checked = isCkr;
    }

    const cardJkt = document.getElementById('cardHubJkt');
    const cardCkr = document.getElementById('cardHubCkr');
    const badgeJkt = document.getElementById('badgeJkt');
    const badgeCkr = document.getElementById('badgeCkr');
    const modalTitle = document.getElementById('modal-title');
    const modalSubtitle = document.getElementById('modal-subtitle');
    const modalHeaderBg = document.getElementById('modalHeaderBg');
    const templateBadge = document.getElementById('templateTargetBadge');
    const btnDownloadXlsx = document.getElementById('btnDownloadXlsx');
    const btnDownloadCsv = document.getElementById('btnDownloadCsv');
    const smartHubText = document.getElementById('smartHubText');
    const btnSubmit = document.getElementById('btnSubmitImport');
    const btnSubmitText = document.getElementById('btnSubmitImportText');

    if (isCkr) {
        // CKR Active
        if (cardCkr) cardCkr.className = "relative flex items-center gap-3 p-3.5 rounded-xl border-2 cursor-pointer transition-all border-teal-500 bg-teal-50/70 shadow-xs ring-1 ring-teal-500/20";
        if (badgeCkr) badgeCkr.classList.remove('hidden');

        if (cardJkt) cardJkt.className = "relative flex items-center gap-3 p-3.5 rounded-xl border-2 cursor-pointer transition-all border-slate-200 bg-white hover:border-slate-300";
        if (badgeJkt) badgeJkt.classList.add('hidden');

        if (modalTitle) modalTitle.textContent = "Import Perangkat ke Gudang HB CKR";
        if (modalSubtitle) modalSubtitle.textContent = "Tambahkan banyak data perangkat sekaligus ke Stok Gudang HB Cikarang";
        if (modalHeaderBg) modalHeaderBg.className = "bg-gradient-to-r from-teal-600 to-cyan-700 px-6 py-4 flex items-center justify-between text-white transition-all";

        if (templateBadge) {
            templateBadge.className = "text-[10px] font-bold px-2 py-0.5 rounded-md bg-teal-100 text-teal-800 border border-teal-200";
            templateBadge.textContent = "Format: Gudang HB CKR";
        }

        if (btnDownloadXlsx) {
            btnDownloadXlsx.href = baseTemplateUrl + "?format=xlsx&hub=hb_ckr";
            btnDownloadXlsx.className = "inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-teal-700 hover:bg-teal-800 text-white shadow-xs transition-colors";
        }
        if (btnDownloadCsv) {
            btnDownloadCsv.href = baseTemplateUrl + "?format=csv&hub=hb_ckr";
        }

        if (smartHubText) {
            smartHubText.textContent = "Gudang HB CKR (Cikarang)";
            smartHubText.className = "text-teal-700 font-bold";
        }

        if (btnSubmit) {
            btnSubmit.className = "inline-flex items-center gap-2 px-5 py-2 text-xs font-bold text-white bg-teal-600 hover:bg-teal-700 rounded-xl shadow-xs transition-colors";
        }
        if (btnSubmitText) {
            btnSubmitText.textContent = "Unggah ke Gudang HB CKR";
        }
    } else {
        // JKT Active
        if (cardJkt) cardJkt.className = "relative flex items-center gap-3 p-3.5 rounded-xl border-2 cursor-pointer transition-all border-emerald-500 bg-emerald-50/70 shadow-xs ring-1 ring-emerald-500/20";
        if (badgeJkt) badgeJkt.classList.remove('hidden');

        if (cardCkr) cardCkr.className = "relative flex items-center gap-3 p-3.5 rounded-xl border-2 cursor-pointer transition-all border-slate-200 bg-white hover:border-slate-300";
        if (badgeCkr) badgeCkr.classList.add('hidden');

        if (modalTitle) modalTitle.textContent = "Import Perangkat ke Gudang HB JKT";
        if (modalSubtitle) modalSubtitle.textContent = "Tambahkan banyak data perangkat sekaligus ke Stok Gudang HB Jakarta";
        if (modalHeaderBg) modalHeaderBg.className = "bg-gradient-to-r from-emerald-600 to-teal-700 px-6 py-4 flex items-center justify-between text-white transition-all";

        if (templateBadge) {
            templateBadge.className = "text-[10px] font-bold px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 border border-emerald-200";
            templateBadge.textContent = "Format: Gudang HB JKT";
        }

        if (btnDownloadXlsx) {
            btnDownloadXlsx.href = baseTemplateUrl + "?format=xlsx&hub=hb_jkt";
            btnDownloadXlsx.className = "inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-700 hover:bg-emerald-800 text-white shadow-xs transition-colors";
        }
        if (btnDownloadCsv) {
            btnDownloadCsv.href = baseTemplateUrl + "?format=csv&hub=hb_jkt";
        }

        if (smartHubText) {
            smartHubText.textContent = "Gudang HB JKT (Jakarta)";
            smartHubText.className = "text-emerald-700 font-bold";
        }

        if (btnSubmit) {
            btnSubmit.className = "inline-flex items-center gap-2 px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition-colors";
        }
        if (btnSubmitText) {
            btnSubmitText.textContent = "Unggah ke Gudang HB JKT";
        }
    }
}

function openImportExcelModal(hub = 'hb_jkt') {
    const modal = document.getElementById('importExcelModal');
    if (modal) {
        switchWarehouseHub(hub);
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}

function closeImportExcelModal() {
    const modal = document.getElementById('importExcelModal');
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }
}

function handleFileSelect(input) {
    const prompt = document.getElementById('fileUploadPrompt');
    const info = document.getElementById('fileSelectedInfo');
    const nameEl = document.getElementById('selectedFileName');
    const sizeEl = document.getElementById('selectedFileSize');

    if (input.files && input.files[0]) {
        const file = input.files[0];
        nameEl.textContent = file.name;
        sizeEl.textContent = (file.size / 1024).toFixed(1) + ' KB';
        prompt.classList.add('hidden');
        info.classList.remove('hidden');
    }
}

function resetFileSelect(e) {
    if (e) e.stopPropagation();
    const input = document.getElementById('importFileInput');
    const prompt = document.getElementById('fileUploadPrompt');
    const info = document.getElementById('fileSelectedInfo');
    
    if (input) input.value = '';
    if (prompt) prompt.classList.remove('hidden');
    if (info) info.classList.add('hidden');
}

// Drag and drop support
document.addEventListener('DOMContentLoaded', function() {
    const dropArea = document.querySelector('#importExcelModal [onclick*="importFileInput"]');
    if (dropArea) {
        ['dragenter', 'dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropArea.classList.add('border-emerald-500', 'bg-emerald-50/40');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropArea.classList.remove('border-emerald-500', 'bg-emerald-50/40');
            }, false);
        });

        dropArea.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            const input = document.getElementById('importFileInput');
            if (files && files.length) {
                input.files = files;
                handleFileSelect(input);
            }
        }, false);
    }

    const form = document.getElementById('importExcelForm');
    if (form) {
        form.addEventListener('submit', function() {
            const btn = document.getElementById('btnSubmitImport');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = `<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Memproses Data Excel...`;
            }
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeImportExcelModal();
        }
    });
});
</script>