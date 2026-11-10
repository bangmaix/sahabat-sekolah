<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Laporan | Sahabat Sekolah</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="{{ asset('js/tw-select.js') }}" defer></script>
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>

<body>
    <!-- Tombol Keluar Cepat (Panic Exit) -->
    <a href="https://www.google.com" id="panic-exit-btn" class="fixed top-3 right-3 md:top-5 md:right-5 z-50 flex items-center gap-2 bg-rose-600 hover:bg-rose-700 text-white font-medium text-xs md:text-sm px-3.5 py-2 rounded-full shadow-lg transition-all duration-200 cursor-pointer" title="Klik tombol ini untuk segera menutup halaman jika ada orang mendekat">
        <i data-lucide="shield-alert" class="w-4 h-4"></i>
        <span>Keluar Cepat</span>
    </a>

    <div class="form-shell">
        <a class="back-link" href="{{ url('/') }}"><i data-lucide="arrow-left"></i> Kembali ke beranda</a>
        <div class="form-layout">
            <section class="form-intro">
                <span class="pill">KANAL PELAPORAN AMAN</span>
                <h1>Mulai dari cerita yang ingin Anda sampaikan.</h1>
                <p>Setiap laporan akan diterima Guru BK dan ditangani dengan prinsip kerahasiaan serta perlindungan peserta didik.</p>
                <div class="trust-list">
                    <span><i data-lucide="shield-check"></i> Identitas dilindungi</span>
                    <span><i data-lucide="clock-3"></i> Respons awal terukur</span>
                    <span><i data-lucide="message-circle-heart"></i> Komunikasi dua arah yang aman</span>
                </div>
            </section>

            <form id="report-form" class="report-form" method="POST" action="{{ route('reports.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="form-heading">
                    <div>
                        <span class="eyebrow">LAPORAN BARU</span>
                        <h2>Sampaikan laporan</h2>
                        <p>Kolom bertanda * wajib diisi.</p>
                    </div>
                    <div class="form-step">01 <span>/ 01</span></div>
                </div>

                @if ($errors->any())
                    <div class="form-alert error">
                        <strong>Periksa kembali isian laporan Anda:</strong>
                        <ul style="margin: 6px 0 0 16px; padding: 0;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (!empty($selectedNpsn))
                    <div class="mb-3 p-3 bg-blue-50 border border-blue-200 rounded-lg flex items-center gap-2 text-xs text-blue-800">
                        <i data-lucide="qr-code" class="w-4 h-4 flex-none text-blue-600"></i>
                        <span><strong>Sekolah Terpilih Otomatis:</strong> Sesuai poster QR Code sekolah yang Anda pindai.</span>
                    </div>
                @endif

                <label>Sekolah <span>*</span>
                    <div class="tw-select-container relative w-full mt-1" data-name="school_npsn">
                        <select name="school_npsn" style="opacity: 0; position: absolute; z-index: -1;" required>
                            <option value="">Pilih sekolah</option>
                            @foreach ($schools as $school)
                                <option value="{{ $school->npsn }}" @selected(old('school_npsn', $selectedNpsn ?? '') == $school->npsn)>{{ $school->nama_sekolah }}</option>
                            @endforeach
                        </select>
                        <button type="button" class="tw-select-trigger w-full flex items-center justify-between bg-white border border-gray-300 rounded-lg px-4 py-2.5 text-sm text-left focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-colors">
                            <span class="tw-select-text text-gray-500 truncate">Pilih sekolah...</span>
                            <svg class="w-4 h-4 text-gray-400 pointer-events-none flex-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <div class="tw-select-dropdown absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg hidden opacity-0 transition-opacity duration-200">
                            <div class="p-2 border-b border-gray-100">
                                <div class="relative">
                                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                    <input type="text" class="tw-select-search w-full bg-gray-50 border-none text-sm rounded-md pl-9 pr-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500" placeholder="Cari sekolah...">
                                </div>
                            </div>
                            <ul class="tw-select-list max-h-60 overflow-y-auto custom-scrollbar p-1" role="listbox"></ul>
                            <div class="tw-select-empty hidden p-4 text-center text-sm text-gray-500">Pencarian tidak ditemukan.</div>
                        </div>
                    </div>
                </label>

                <div class="two-fields">
                    <label>Peran pelapor <span>*</span>
                        <select name="reporter_role" required>
                            <option value="">Pilih peran</option>
                            <option value="VICTIM" @selected(old('reporter_role') === 'VICTIM')>Saya korban</option>
                            <option value="WITNESS" @selected(old('reporter_role') === 'WITNESS')>Saya saksi</option>
                            <option value="CONCERNED_PERSON" @selected(old('reporter_role') === 'CONCERNED_PERSON')>Saya mengetahui kejadian</option>
                        </select>
                    </label>
                    <label>Mode identitas <span>*</span>
                        <select name="identity_mode" id="identity-mode-select" required>
                            <option value="CONFIDENTIAL" @selected(old('identity_mode', 'CONFIDENTIAL') === 'CONFIDENTIAL')>Rahasia (Hanya Guru BK yang tahu)</option>
                            <option value="IDENTIFIED" @selected(old('identity_mode') === 'IDENTIFIED')>Terbuka (Nama Jelas)</option>
                            <option value="ANONYMOUS" @selected(old('identity_mode') === 'ANONYMOUS')>Anonim (Tanpa Nama & Nomor HP)</option>
                        </select>
                    </label>
                </div>

                <div id="anonymous-privacy-banner" class="p-3 bg-emerald-50 border border-emerald-200 rounded-lg text-xs text-emerald-800 flex items-start gap-2.5 my-2 hidden">
                    <i data-lucide="shield-check" class="w-4 h-4 flex-none text-emerald-600 mt-0.5"></i>
                    <div>
                        <strong>Jaminan Privasi Total:</strong> Dalam mode Anonim, kamu tidak perlu menulis nama atau nomor HP. Foto bukti yang diunggah akan otomatis dibersihkan dari informasi GPS lokasi HP. Kamu akan diberikan 4 kata rahasia untuk membaca balasan Guru BK.
                    </div>
                </div>

                <div id="reporter-identity-container" class="flex flex-col gap-4 mb-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-semibold text-gray-700">Nama pelapor <span
                                    id="reporter-name-asterisk" class="text-red-500">*</span></label>
                            <input type="text" name="reporter_name" id="reporter-name-input"
                                value="{{ old('reporter_name') }}" placeholder="Nama lengkap Anda"
                                class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                            @error('reporter_name')
                                <small id="reporter-name-help" class="text-xs text-red-500">Wajib diisi untuk mode Rahasia
                                    atau Terbuka.</small>
                            @enderror
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-semibold text-gray-700">Kontak</label>
                            <input type="text" name="reporter_contact" value="{{ old('reporter_contact') }}"
                                placeholder="No. HP / WhatsApp (opsional)"
                                class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-semibold text-gray-700">Kelas</label>
                            <input type="text" name="reporter_class" value="{{ old('reporter_class') }}"
                                placeholder="Contoh: X IPA 1 (opsional)"
                                class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-semibold text-gray-700">Waktu kejadian</label>
                            <input type="datetime-local" name="incident_time" value="{{ old('incident_time') }}"
                                class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <!-- Kategori Kejadian (Tombol 2 Kolom) -->
                    <div id="category-selection-wrapper">
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-sm font-bold text-[#496985] m-0">Kategori kejadian <span class="text-[#eb6d6f]">*</span></label>
                            <span id="category-selected-label" class="text-xs text-blue-600 font-semibold"></span>
                        </div>
                        <input type="hidden" name="category" id="category-input" value="{{ old('category') }}" required>
                        <input type="hidden" name="category_id" id="category-id-input" value="{{ old('category_id') }}">

                        @php
                            $categoryPresets = [
                                'Perundungan Fisik' => ['icon' => '🥊'],
                                'Perundungan Verbal' => ['icon' => '🗣️'],
                                'Perundungan Sosial' => ['icon' => '👥'],
                                'Cyberbullying' => ['icon' => '📱'],
                                'Perundungan Bernuansa Seksual' => ['icon' => '⚠️'],
                                'Lainnya' => ['icon' => '📌'],
                            ];
                        @endphp

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2" id="category-grid">
                            @foreach ($categories as $category)
                                @php
                                    $preset = $categoryPresets[$category->name] ?? ['icon' => '🏷️'];
                                @endphp
                                <button type="button"
                                    class="category-btn flex items-center gap-2.5 px-3 py-2.5 bg-slate-50 hover:bg-blue-50 hover:border-blue-400 border border-slate-200 rounded-lg text-xs md:text-sm font-medium text-slate-700 transition-all cursor-pointer text-left"
                                    data-category-id="{{ $category->id }}"
                                    data-category-name="{{ $category->name }}">
                                    <span class="text-base flex-none leading-none">{{ $preset['icon'] }}</span>
                                    <span class="truncate flex-1">{{ $category->name }}</span>
                                </button>
                            @endforeach
                        </div>
                        <span id="category-error-msg" class="text-xs text-red-500 hidden mt-1.5 block">Silakan pilih salah satu kategori kejadian di atas.</span>
                        @error('category')
                            <small class="text-xs text-red-500 mt-1 block">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- Subkategori Kejadian (Tombol Menyesuaikan dengan Kategori yang Dipilih) -->
                    <div id="subcategory-panel" class="hidden mt-3 pt-3 border-t border-slate-100">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-semibold text-slate-600">Pilih bentuk spesifik (<span id="subcategory-for-category-title" class="text-blue-600 font-bold"></span>):</span>
                            <span class="text-[11px] text-slate-400 font-normal">Opsional (klik untuk memilih/batal)</span>
                        </div>
                        <input type="hidden" name="subcategory" id="subcategory-input" value="{{ old('subcategory') }}">
                        <input type="hidden" name="subcategory_id" id="subcategory-id-input" value="{{ old('subcategory_id') }}">

                        @foreach ($categories as $category)
                            <div class="subcategory-group hidden grid grid-cols-1 sm:grid-cols-2 gap-2"
                                id="subcat-group-{{ $category->id }}"
                                data-category-id="{{ $category->id }}">
                                @forelse ($category->subcategories as $subcategory)
                                    <button type="button"
                                        class="subcat-btn text-xs font-medium px-3 py-2 bg-slate-50 hover:bg-blue-50 hover:border-blue-400 border border-slate-200 rounded-lg transition-all cursor-pointer text-left truncate"
                                        data-subcat-id="{{ $subcategory->id }}"
                                        data-subcat-name="{{ $subcategory->name }}"
                                        data-category-id="{{ $category->id }}">
                                        {{ $subcategory->name }}
                                    </button>
                                @empty
                                    <div class="col-span-1 sm:col-span-2 text-xs text-slate-400 italic py-1">
                                        Tidak ada subkategori khusus untuk kategori ini.
                                    </div>
                                @endforelse
                            </div>
                        @endforeach

                        @error('subcategory')
                            <small class="text-xs text-red-500 mt-1 block">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <label>
                    <div class="flex items-center justify-between mb-1">
                        <span>Ceritakan kejadian <span>*</span></span>
                        <button type="button" id="voice-input-btn" class="inline-flex items-center gap-1.5 text-xs font-medium text-blue-600 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 px-2.5 py-1 rounded-md transition-colors">
                            <i data-lucide="mic" class="w-3.5 h-3.5"></i>
                            <span id="voice-btn-label">Bicara / Dikte Suara</span>
                        </button>
                    </div>
                    <textarea name="description" id="description-textarea" rows="6" minlength="20" required
                        placeholder="Tuliskan apa yang terjadi, kapan, dan di mana. Ceritakan fakta yang Anda lihat atau alami. Jika sulit mengetik, klik tombol 'Bicara / Dikte Suara' di atas.">{{ old('description') }}</textarea>
                    <small>Minimal 20 karakter. Ceritakan fakta yang Anda lihat atau alami secara jujur dan tenang.</small>
                </label>

                <div class="flex flex-col gap-1 mb-6">
                    <label class="text-sm font-semibold text-gray-700" style="margin-bottom: 4px;">Bukti / Dokumen Pendukung (Opsional)</label>
                    <input type="file" name="evidences[]" multiple accept="image/*,application/pdf"
                        class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all" style="font-size: 16px;">
                    <small class="text-xs text-gray-500 mt-1">Bisa upload hingga 5 file (Foto atau PDF). Metadata lokasi foto HP Anda otomatis dibersihkan demi privasi.</small>
                    @error('evidences')
                        <small class="text-xs text-red-500">{{ $message }}</small>
                    @enderror
                    @error('evidences.*')
                        <small class="text-xs text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                <button class="primary-button" type="submit">Kirim laporan <i data-lucide="arrow-right"></i></button>
            </form>
        </div>
    </div>

    <script>
        lucide.createIcons();

        const reportForm = document.getElementById('report-form');
        const categoryInput = document.getElementById('category-input');
        const categoryIdInput = document.getElementById('category-id-input');
        const subcategoryInput = document.getElementById('subcategory-input');
        const subcategoryIdInput = document.getElementById('subcategory-id-input');
        const categoryButtons = document.querySelectorAll('.category-btn');
        const subcatButtons = document.querySelectorAll('.subcat-btn');
        const subcatGroups = document.querySelectorAll('.subcategory-group');
        const subcatPanel = document.getElementById('subcategory-panel');
        const subcatForTitle = document.getElementById('subcategory-for-category-title');
        const catSelectedLabel = document.getElementById('category-selected-label');
        const catErrorMsg = document.getElementById('category-error-msg');
        const catWrapper = document.getElementById('category-selection-wrapper');

        const identityModeSelect = document.getElementById('identity-mode-select');
        const reporterNameAsterisk = document.getElementById('reporter-name-asterisk');
        const reporterNameInput = document.getElementById('reporter-name-input');
        const reporterNameHelp = document.getElementById('reporter-name-help');
        const anonymousBanner = document.getElementById('anonymous-privacy-banner');
        const panicBtn = document.getElementById('panic-exit-btn');
        const descTextarea = document.getElementById('description-textarea');
        const voiceBtn = document.getElementById('voice-input-btn');
        const voiceLabel = document.getElementById('voice-btn-label');

        function updateCategoryVisual(selectedCategoryId) {
            categoryButtons.forEach(btn => {
                const isSelected = String(btn.dataset.categoryId) === String(selectedCategoryId);
                if (isSelected) {
                    btn.classList.add('bg-blue-600', 'text-white', 'border-blue-600', 'shadow-sm', 'font-semibold');
                    btn.classList.remove('bg-slate-50', 'text-slate-700', 'border-slate-200', 'hover:bg-blue-50');
                } else {
                    btn.classList.remove('bg-blue-600', 'text-white', 'border-blue-600', 'shadow-sm', 'font-semibold');
                    btn.classList.add('bg-slate-50', 'text-slate-700', 'border-slate-200', 'hover:bg-blue-50');
                }
            });

            if (catSelectedLabel) {
                const activeBtn = Array.from(categoryButtons).find(b => String(b.dataset.categoryId) === String(selectedCategoryId));
                const catName = activeBtn ? activeBtn.dataset.categoryName : '';
                catSelectedLabel.textContent = catName ? 'Terpilih: ' + catName : '';
            }
        }

        function updateSubcategoryVisual(selectedSubcatId) {
            subcatButtons.forEach(btn => {
                const isSelected = Boolean(selectedSubcatId) && String(btn.dataset.subcatId) === String(selectedSubcatId);
                if (isSelected) {
                    btn.classList.add('bg-blue-600', 'text-white', 'border-blue-600', 'shadow-sm', 'font-semibold');
                    btn.classList.remove('bg-slate-50', 'text-slate-700', 'border-slate-200', 'hover:bg-blue-50');
                } else {
                    btn.classList.remove('bg-blue-600', 'text-white', 'border-blue-600', 'shadow-sm', 'font-semibold');
                    btn.classList.add('bg-slate-50', 'text-slate-700', 'border-slate-200', 'hover:bg-blue-50');
                }
            });
        }

        function selectCategory(categoryId, categoryName) {
            categoryInput.value = categoryName;
            if (categoryIdInput) categoryIdInput.value = categoryId;
            if (catErrorMsg) catErrorMsg.classList.add('hidden');

            updateCategoryVisual(categoryId);

            // Buka panel subkategori dan sesuaikan dengan category_id dari database
            if (subcatPanel) {
                subcatPanel.classList.remove('hidden');
            }
            if (subcatForTitle) {
                subcatForTitle.textContent = categoryName;
            }

            // Tampilkan hanya subkategori-group yang cocok dengan categoryId
            subcatGroups.forEach(group => {
                if (String(group.dataset.categoryId) === String(categoryId)) {
                    group.classList.remove('hidden');
                } else {
                    group.classList.add('hidden');
                }
            });

            // Reset subkategori jika tidak valid untuk kategori yang baru dipilih
            const currentSubcatBtn = Array.from(subcatButtons).find(b => String(b.dataset.subcatId) === String(subcategoryIdInput?.value));
            if (!currentSubcatBtn || String(currentSubcatBtn.dataset.categoryId) !== String(categoryId)) {
                if (subcategoryInput) subcategoryInput.value = '';
                if (subcategoryIdInput) subcategoryIdInput.value = '';
                updateSubcategoryVisual(null);
            } else {
                updateSubcategoryVisual(subcategoryIdInput.value);
            }
        }

        // Event listener tombol kategori (2 Kolom)
        categoryButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const catId = this.dataset.categoryId;
                const catName = this.dataset.categoryName;
                selectCategory(catId, catName);
            });
        });

        // Event listener tombol subkategori (klik lagi untuk toggle / batal pilih)
        subcatButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const subcatId = this.dataset.subcatId;
                const subcatName = this.dataset.subcatName;

                if (subcategoryIdInput && String(subcategoryIdInput.value) === String(subcatId)) {
                    // Toggle off / batalkan pilihan
                    subcategoryIdInput.value = '';
                    if (subcategoryInput) subcategoryInput.value = '';
                    updateSubcategoryVisual(null);
                } else {
                    if (subcategoryIdInput) subcategoryIdInput.value = subcatId;
                    if (subcategoryInput) subcategoryInput.value = subcatName;
                    updateSubcategoryVisual(subcatId);
                }
            });
        });

        // Validasi Submit Form
        if (reportForm) {
            reportForm.addEventListener('submit', function(e) {
                if (!categoryInput.value || !categoryInput.value.trim()) {
                    e.preventDefault();
                    if (catErrorMsg) catErrorMsg.classList.remove('hidden');
                    if (catWrapper) {
                        catWrapper.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            });
        }

        // Panic Exit
        if (panicBtn) {
            panicBtn.addEventListener('click', function(e) {
                e.preventDefault();
                window.location.replace('https://www.google.com');
            });
        }

        // Voice Input (Speech to Text in Indonesian)
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        if (SpeechRecognition && voiceBtn) {
            const recognition = new SpeechRecognition();
            recognition.lang = 'id-ID';
            recognition.continuous = false;
            recognition.interimResults = false;

            let isRecording = false;

            voiceBtn.addEventListener('click', function() {
                if (!isRecording) {
                    try {
                        recognition.start();
                        isRecording = true;
                        voiceBtn.classList.remove('bg-blue-50', 'text-blue-600');
                        voiceBtn.classList.add('bg-red-500', 'text-white', 'animate-pulse');
                        voiceLabel.textContent = 'Mendengarkan... Bicara sekarang';
                    } catch (err) {
                        console.log(err);
                    }
                } else {
                    recognition.stop();
                    isRecording = false;
                    resetVoiceBtn();
                }
            });

            recognition.onresult = function(event) {
                const transcript = event.results[0][0].transcript;
                if (descTextarea.value.trim().length > 0) {
                    descTextarea.value += ' ' + transcript;
                } else {
                    descTextarea.value = transcript;
                }
            };

            recognition.onspeechend = function() {
                recognition.stop();
                isRecording = false;
                resetVoiceBtn();
            };

            recognition.onerror = function() {
                isRecording = false;
                resetVoiceBtn();
            };

            function resetVoiceBtn() {
                voiceBtn.classList.remove('bg-red-500', 'text-white', 'animate-pulse');
                voiceBtn.classList.add('bg-blue-50', 'text-blue-600');
                voiceLabel.textContent = 'Bicara / Dikte Suara';
            }
        } else if (voiceBtn) {
            voiceBtn.style.display = 'none';
        }

        function updateIdentityFields() {
            const mode = identityModeSelect.value;
            const container = document.getElementById('reporter-identity-container');

            if (mode === 'ANONYMOUS') {
                container.style.display = 'none';
                if (anonymousBanner) anonymousBanner.classList.remove('hidden');
                reporterNameInput.removeAttribute('required');
            } else {
                container.style.display = 'block';
                if (anonymousBanner) anonymousBanner.classList.add('hidden');
                reporterNameInput.setAttribute('required', 'required');
            }
        }

        identityModeSelect.addEventListener('change', updateIdentityFields);
        updateIdentityFields();

        // Inisialisasi awal jika ada nilai lama (misal gagal validasi backend)
        if (categoryInput && categoryInput.value) {
            const initialBtn = Array.from(categoryButtons).find(b => b.dataset.categoryName === categoryInput.value || String(b.dataset.categoryId) === String(categoryIdInput?.value));
            if (initialBtn) {
                selectCategory(initialBtn.dataset.categoryId, initialBtn.dataset.categoryName);
            }
        }
        if (subcategoryIdInput && subcategoryIdInput.value) {
            updateSubcategoryVisual(subcategoryIdInput.value);
        } else if (subcategoryInput && subcategoryInput.value) {
            const initialSubcat = Array.from(subcatButtons).find(b => b.dataset.subcatName === subcategoryInput.value);
            if (initialSubcat) {
                if (subcategoryIdInput) subcategoryIdInput.value = initialSubcat.dataset.subcatId;
                updateSubcategoryVisual(initialSubcat.dataset.subcatId);
            }
        }
    </script>
</body>

</html>
