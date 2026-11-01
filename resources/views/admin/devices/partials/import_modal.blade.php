<!-- Modal Import Excel / CSV -->
<div id="importExcelModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" onclick="closeImportExcelModal()"></div>

    <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-xl border border-slate-200">
            
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-emerald-600 to-teal-700 px-6 py-4 flex items-center justify-between text-white">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-white" id="modal-title">Import Perangkat dari Excel / CSV</h3>
                        <p class="text-xs text-emerald-100">Tambahkan banyak data perangkat sekaligus ke database</p>
                    </div>
                </div>
                <button type="button" onclick="closeImportExcelModal()" class="text-white/80 hover:text-white rounded-lg p-1.5 hover:bg-white/10 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 space-y-5">
                
                <!-- Download Template Banner -->
                <div class="bg-emerald-50/70 border border-emerald-200 rounded-xl p-4">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-xs font-bold text-emerald-950">Unduh Format Template</h4>
                            <p class="text-[11px] text-emerald-700 mt-0.5">Gunakan format template resmi agar data kolom terbaca rapi dan otomatis:</p>
                            <div class="flex flex-wrap gap-2 mt-2.5">
                                <a href="{{ route('admin.devices.import-template', ['format' => 'xlsx']) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-700 hover:bg-emerald-800 text-white shadow-xs transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
                                    <span>Download Template Excel (.xlsx)</span>
                                </a>
                                <a href="{{ route('admin.devices.import-template', ['format' => 'csv']) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-white hover:bg-emerald-100 text-emerald-800 border border-emerald-300 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <span>Download Format CSV (.csv)</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Upload -->
                <form id="importExcelForm" action="{{ route('admin.devices.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2">Pilih File Spreadsheet Excel / CSV:</label>
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
                            <span>Unggah & Tambahkan ke Database</span>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<script>
function openImportExcelModal() {
    const modal = document.getElementById('importExcelModal');
    if (modal) {
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
