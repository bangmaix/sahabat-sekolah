<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Laporan | Sahabat Sekolah</title>
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
    <div class="form-shell">
        <a class="back-link" href="{{ url('/') }}"><i data-lucide="arrow-left"></i> Kembali ke dashboard</a>
        <div class="form-layout">
            <section class="form-intro">
                <span class="pill">KANAL PELAPORAN AMAN</span>
                <h1>Mulai dari cerita yang ingin Anda sampaikan.</h1>
                <p>Setiap laporan akan diterima Guru BK dan ditangani dengan prinsip kerahasiaan serta perlindungan
                    peserta didik.</p>
                <div class="trust-list">
                    <span><i data-lucide="shield-check"></i> Identitas dilindungi</span>
                    <span><i data-lucide="clock-3"></i> Respons awal maksimal 1 × 24 jam</span>
                    <span><i data-lucide="message-circle-heart"></i> Komunikasi yang aman</span>
                </div>
            </section>

            <form class="report-form" method="POST" action="{{ route('reports.store') }}">
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

                <label>Sekolah <span>*</span>
                    <div class="tw-select-container relative w-full mt-1" data-name="school_npsn">
                        <select name="school_npsn" style="opacity: 0; position: absolute; z-index: -1;" required>
                            <option value="">Pilih sekolah</option>
                            @foreach ($schools as $school)
                                <option value="{{ $school->npsn }}" @selected(old('school_npsn') == $school->npsn)>{{ $school->nama_sekolah }}</option>
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
                            <option value="CONCERNED_PERSON" @selected(old('reporter_role') === 'CONCERNED_PERSON')>Saya mengetahui kejadian
                            </option>
                        </select>
                    </label>
                    <label>Mode identitas <span>*</span>
                        <select name="identity_mode" id="identity-mode-select" required>
                            <option value="CONFIDENTIAL" @selected(old('identity_mode', 'CONFIDENTIAL') === 'CONFIDENTIAL')>Rahasia (Hanya Guru BK)</option>
                            <option value="IDENTIFIED" @selected(old('identity_mode') === 'IDENTIFIED')>Terbuka (Nama Jelas)</option>
                            <option value="ANONYMOUS" @selected(old('identity_mode') === 'ANONYMOUS')>Anonim (Tanpa Nama)</option>
                        </select>
                    </label>
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

                <div class="two-fields">
                    <label>Kategori kejadian <span>*</span>
                        <select name="category" id="category-select" required>
                            <option value="">Pilih kategori</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->name }}" data-category-id="{{ $category->id }}"
                                    @selected(old('category') === $category->name)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>Subkategori
                        <select name="subcategory" id="subcategory-select">
                            <option value="">Pilih subkategori</option>
                            @foreach ($categories as $category)
                                @foreach ($category->subcategories as $subcategory)
                                    <option value="{{ $subcategory->name }}" data-category-id="{{ $category->id }}"
                                        @selected(old('subcategory') === $subcategory->name)>{{ $subcategory->name }}</option>
                                @endforeach
                            @endforeach
                        </select>
                    </label>
                </div>

                <label>Ceritakan kejadian <span>*</span>
                    <textarea name="description" rows="6" minlength="20" required
                        placeholder="Tuliskan apa yang terjadi, kapan, dan di mana. Ceritakan fakta yang Anda lihat atau alami.">{{ old('description') }}</textarea>
                    <small>Minimal 20 karakter. Ceritakan fakta yang Anda lihat atau alami.</small>
                </label>

                <button class="primary-button" type="submit">Kirim laporan <i data-lucide="arrow-right"></i></button>
            </form>
        </div>
    </div>

    <script>
        lucide.createIcons();

        const categorySelect = document.getElementById('category-select');
        const subcategorySelect = document.getElementById('subcategory-select');
        const identityModeSelect = document.getElementById('identity-mode-select');
        const reporterNameAsterisk = document.getElementById('reporter-name-asterisk');
        const reporterNameInput = document.getElementById('reporter-name-input');
        const reporterNameHelp = document.getElementById('reporter-name-help');

        function filterSubcategories() {
            const selectedOption = categorySelect.options[categorySelect.selectedIndex];
            const id = selectedOption?.dataset.categoryId;
            const currentSub = subcategorySelect.value;

            Array.from(subcategorySelect.options).forEach(option => {
                if (!option.value) return;
                option.hidden = Boolean(option.dataset.categoryId) && option.dataset.categoryId !== id;
            });
        }

        function updateIdentityFields() {
            const mode = identityModeSelect.value;
            const container = document.getElementById('reporter-identity-container');

            if (mode === 'ANONYMOUS') {
                container.style.display = 'none';
                reporterNameInput.removeAttribute('required');
            } else {
                container.style.display = 'block';
                reporterNameInput.setAttribute('required', 'required');
            }
        }

        categorySelect.addEventListener('change', filterSubcategories);
        identityModeSelect.addEventListener('change', updateIdentityFields);

        filterSubcategories();
        updateIdentityFields();
    </script>
</body>

</html>
