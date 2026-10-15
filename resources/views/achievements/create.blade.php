@extends('layouts.app', [
    'title' => 'Tambah Prestasi Siswa | Sahabat Sekolah',
    'activeNav' => 'achievements',
    'eyebrow' => 'PRESTASI SISWA',
    'pageTitle' => 'Tambah Prestasi Siswa',
    'pageSubtitle' => 'Catat capaian dan penghargaan siswa. Tarik data siswa dari AConnect menggunakan NISN.',
])

@section('content')

<section class="panel" style="max-width:720px;">

    @if($errors->any())
        <div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:.75rem;padding:1rem 1.25rem;margin-bottom:1.5rem;color:#7f1d1d;font-size:.875rem;">
            <div style="font-weight:600;margin-bottom:.5rem;display:flex;align-items:center;gap:.5rem;"><i data-lucide="alert-circle" style="width:16px;height:16px;"></i> Terdapat kesalahan:</div>
            <ul style="margin:0;padding-left:1.25rem;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('achievements.store') }}" enctype="multipart/form-data" class="admin-form" id="form-tambah-prestasi">
        @csrf

        {{-- ===== BAGIAN 1: DATA SISWA ===== --}}
        <div style="margin-bottom:1.75rem;">
            <h4 style="font-size:.95rem;font-weight:700;color:var(--text-primary);margin-bottom:1rem;padding-bottom:.5rem;border-bottom:1px solid var(--border-subtle);display:flex;align-items:center;gap:.5rem;">
                <i data-lucide="user" style="width:16px;height:16px;"></i> Data Siswa
            </h4>

            {{-- Semester yang digunakan untuk lookup --}}
            <div style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:.6rem;padding:.75rem 1rem;margin-bottom:1.25rem;font-size:.83rem;color:#0369a1;display:flex;align-items:center;gap:.6rem;" id="period-info">
                <i data-lucide="info" style="width:15px;height:15px;flex-shrink:0;"></i>
                <span>
                    Sistem menggunakan semester:
                    <strong>{{ ($isFallback ?? false) ? ($fallbackPeriod?->name ?? $selectedSemesterId) : ($activePeriod?->name ?? $selectedSemesterId) }}</strong>
                    @if($isFallback ?? false)
                        <span style="background:#fef3c7;color:#92400e;padding:.1rem .45rem;border-radius:.3rem;font-size:.72rem;font-weight:700;margin-left:.25rem;border:1px solid #fde68a;">SEMESTER CADANGAN</span>
                    @endif
                </span>
            </div>

            {{-- SELECT2 / SEARCHABLE DROPDOWN SISWA DARI ACONNECT --}}
            @if(!empty($students))
            <div style="margin-bottom:1.25rem;">
                <label style="display:block;margin-bottom:.4rem;font-size:.85rem;font-weight:600;">
                    Pilih / Cari Siswa Sekolah <span style="font-weight:400;color:var(--text-muted);">(Bantuan Pencarian Cepat)</span>
                </label>
                <div class="tw-select-container relative w-full mt-1" data-name="student_lookup" id="student-lookup-container">
                    <select id="student_lookup" style="opacity: 0; position: absolute; z-index: -1;">
                        <option value="">Pilih / cari nama siswa...</option>
                        @foreach ($students as $student)
                            <option value="{{ $student['nisn'] }}"
                                data-name="{{ $student['nm_siswa'] }}"
                                data-class="{{ $student['nm_kelas'] ?? '' }}"
                                @selected(old('nisn') === $student['nisn'])>
                                {{ $student['nm_siswa'] }} — NISN: {{ $student['nisn'] }} (Kelas {{ $student['nm_kelas'] ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                    <button type="button" class="tw-select-trigger w-full flex items-center justify-between bg-white border border-gray-300 rounded-lg px-4 py-2.5 text-sm text-left focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-colors">
                        <span class="tw-select-text text-gray-500 truncate">Pilih / cari nama siswa...</span>
                        <svg class="w-4 h-4 text-gray-400 pointer-events-none flex-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div class="tw-select-dropdown absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg hidden opacity-0 transition-opacity duration-200">
                        <div class="p-2 border-b border-gray-100">
                            <div class="relative">
                                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                <input type="text" class="tw-select-search w-full bg-gray-50 border-none text-sm rounded-md pl-9 pr-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500" placeholder="Ketik nama atau NISN siswa...">
                            </div>
                        </div>
                        <ul class="tw-select-list max-h-60 overflow-y-auto custom-scrollbar p-1" role="listbox"></ul>
                        <div class="tw-select-empty hidden p-4 text-center text-sm text-gray-500">Siswa tidak ditemukan.</div>
                    </div>
                </div>
                <div style="font-size:.78rem;color:var(--text-muted);margin-top:.4rem;display:flex;align-items:center;gap:.35rem;">
                    <i data-lucide="info" style="width:13px;height:13px;flex-shrink:0;"></i>
                    <span>Tersedia <strong>{{ count($students) }}</strong> data siswa terdaftar di sekolah ini. Memilih siswa akan otomatis mengisi form di bawah.</span>
                </div>
            </div>

            <div style="display:flex;align-items:center;gap:1rem;margin:1.25rem 0;">
                <div style="flex:1;height:1px;background:var(--border-subtle);"></div>
                <span style="font-size:.75rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">atau cari manual via NISN</span>
                <div style="flex:1;height:1px;background:var(--border-subtle);"></div>
            </div>
            @endif

            {{-- NISN Lookup --}}
            <div style="margin-bottom:1rem;">
                <label for="nisn" style="display:block;margin-bottom:.4rem;font-size:.85rem;font-weight:600;">NISN Siswa <span style="color:#ef4444;">*</span></label>
                <div style="display:flex;gap:.6rem;">
                    <input id="nisn" name="nisn" type="text" value="{{ old('nisn') }}"
                        placeholder="Masukkan 10 digit NISN..."
                        maxlength="20" style="flex:1;"
                        oninput="resetStudentFields()">
                    <button type="button" id="btn-fetch-student" onclick="fetchStudent()"
                        style="white-space:nowrap;padding:.6rem 1.1rem;background:var(--accent);color:#fff;border:none;border-radius:.5rem;font-weight:600;font-size:.875rem;cursor:pointer;display:flex;align-items:center;gap:.4rem;transition:opacity .2s;">
                        <i data-lucide="search" style="width:15px;height:15px;"></i>
                        <span id="fetch-label">Tarik Data</span>
                    </button>
                </div>
                <div id="fetch-error" style="display:none;color:#dc2626;font-size:.82rem;margin-top:.4rem;"></div>
                <div id="fetch-fallback-info" style="display:none;background:#fffbeb;border:1px solid #fde68a;border-radius:.5rem;padding:.6rem .9rem;margin-top:.5rem;font-size:.82rem;color:#92400e;"></div>
            </div>

            {{-- Nama Siswa (auto-filled) --}}
            <div style="margin-bottom:1rem;">
                <label for="student_name" style="display:block;margin-bottom:.4rem;font-size:.85rem;font-weight:600;">Nama Siswa <span style="color:#ef4444;">*</span></label>
                <input id="student_name" name="student_name" type="text" value="{{ old('student_name') }}"
                    placeholder="Diisi otomatis setelah tarik data..." readonly
                    style="background:var(--surface-card);color:var(--text-muted);cursor:not-allowed;">
            </div>

            {{-- Kelas & Semester (auto-filled) --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div>
                    <label for="class_name" style="display:block;margin-bottom:.4rem;font-size:.85rem;font-weight:600;">Kelas</label>
                    <input id="class_name" name="class_name" type="text" value="{{ old('class_name') }}"
                        placeholder="Diisi otomatis..." readonly
                        style="background:var(--surface-card);color:var(--text-muted);cursor:not-allowed;">
                </div>
                <div>
                    <label for="semester_id" style="display:block;margin-bottom:.4rem;font-size:.85rem;font-weight:600;">Semester Data</label>
                    <select id="semester_id" name="semester_id">
                        <option value="">— Pilih semester —</option>
                        @foreach($periods as $period)
                            <option value="{{ $period->semester_id }}"
                                @selected(old('semester_id') === $period->semester_id)
                                data-active="{{ $period->is_active ? '1' : '0' }}"
                                data-fallback="{{ $period->is_fallback ? '1' : '0' }}">
                                {{ $period->name }}
                                @if($period->is_active) ✦ @endif
                                @if($period->is_fallback) (Cadangan) @endif
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- ===== BAGIAN 2: DETAIL PRESTASI ===== --}}
        <div style="margin-bottom:1.75rem;">
            <h4 style="font-size:.95rem;font-weight:700;color:var(--text-primary);margin-bottom:1rem;padding-bottom:.5rem;border-bottom:1px solid var(--border-subtle);display:flex;align-items:center;gap:.5rem;">
                <i data-lucide="trophy" style="width:16px;height:16px;"></i> Detail Prestasi
            </h4>

            <div style="margin-bottom:1rem;">
                <label for="category_id" style="display:block;margin-bottom:.4rem;font-size:.85rem;font-weight:600;">Kategori Prestasi <span style="color:#ef4444;">*</span></label>
                <select id="category_id" name="category_id" required>
                    <option value="">— Pilih kategori —</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" @selected(old('category_id') == $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div style="margin-bottom:1rem;">
                <label for="achievement_name" style="display:block;margin-bottom:.4rem;font-size:.85rem;font-weight:600;">Nama Lomba / Penghargaan <span style="color:#ef4444;">*</span></label>
                <input id="achievement_name" name="achievement_name" type="text" value="{{ old('achievement_name') }}"
                    placeholder="Contoh: Olimpiade Matematika Tingkat Kota" required>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">
                <div>
                    <label for="achievement_level" style="display:block;margin-bottom:.4rem;font-size:.85rem;font-weight:600;">Tingkat Prestasi <span style="color:#ef4444;">*</span></label>
                    <select id="achievement_level" name="achievement_level" required>
                        <option value="">— Pilih tingkat —</option>
                        @foreach($levels as $key => $label)
                            <option value="{{ $key }}" @selected(old('achievement_level') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="ranking" style="display:block;margin-bottom:.4rem;font-size:.85rem;font-weight:600;">Peringkat / Juara <span style="color:#ef4444;">*</span></label>
                    <select id="ranking" name="ranking" required>
                        <option value="">— Pilih peringkat —</option>
                        @foreach($rankings as $rank)
                            <option value="{{ $rank }}" @selected(old('ranking') === $rank)>{{ $rank }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">
                <div>
                    <label for="organizer" style="display:block;margin-bottom:.4rem;font-size:.85rem;font-weight:600;">Penyelenggara</label>
                    <input id="organizer" name="organizer" type="text" value="{{ old('organizer') }}"
                        placeholder="Nama instansi/lembaga penyelenggara">
                </div>
                <div>
                    <label for="achievement_date" style="display:block;margin-bottom:.4rem;font-size:.85rem;font-weight:600;">Tanggal Perolehan <span style="color:#ef4444;">*</span></label>
                    <input id="achievement_date" name="achievement_date" type="date" value="{{ old('achievement_date') }}" required>
                </div>
            </div>

            <div style="margin-bottom:1rem;">
                <label for="description" style="display:block;margin-bottom:.4rem;font-size:.85rem;font-weight:600;">Deskripsi / Catatan <span style="color:var(--text-muted);font-weight:400;">(opsional)</span></label>
                <textarea id="description" name="description" rows="3"
                    placeholder="Keterangan tambahan, nomor piagam, dll.">{{ old('description') }}</textarea>
            </div>

            <div>
                <label for="certificate" style="display:block;margin-bottom:.4rem;font-size:.85rem;font-weight:600;">Upload Bukti / Sertifikat <span style="color:var(--text-muted);font-weight:400;">(opsional, maks. 4 MB)</span></label>
                <input id="certificate" name="certificate" type="file" accept=".jpg,.jpeg,.png,.pdf">
                <p style="color:var(--text-muted);font-size:.8rem;margin-top:.4rem;">Format: JPG, PNG, atau PDF. Maksimal 4 MB.</p>
            </div>
        </div>

        {{-- Actions --}}
        <div style="display:flex;gap:.75rem;padding-top:1rem;border-top:1px solid var(--border-subtle);">
            <button type="submit" class="primary-button" id="btn-simpan-prestasi">
                <i data-lucide="save"></i> Simpan Prestasi
            </button>
            <a href="{{ route('achievements.index') }}" class="secondary-button">Batal</a>
        </div>
    </form>
</section>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const studentLookup = document.getElementById('student_lookup');
        if (studentLookup) {
            studentLookup.addEventListener('change', function() {
                const selectedNisn = this.value;
                if (!selectedNisn) return;
                const opt = this.options[this.selectedIndex];
                if (!opt) return;

                document.getElementById('nisn').value = selectedNisn;
                document.getElementById('student_name').value = opt.getAttribute('data-name') || '';
                document.getElementById('class_name').value = opt.getAttribute('data-class') || '';

                const selectedSemester = '{{ $selectedSemesterId ?? "" }}';
                const semesterSelect = document.getElementById('semester_id');
                for (let o of semesterSelect.options) {
                    if (o.value === selectedSemester) { o.selected = true; break; }
                }

                document.getElementById('fetch-error').style.display = 'none';

                @if($isFallback ?? false)
                const info = document.getElementById('fetch-fallback-info');
                info.innerHTML = `⚠️ Data dimuat dari semester cadangan: <strong>{{ $fallbackPeriod?->name ?? $selectedSemesterId }}</strong> karena data semester aktif belum ditarik ke Dapodik.`;
                info.style.display = 'block';
                @endif
            });
        }
    });

    function resetStudentFields() {
        document.getElementById('student_name').value = '';
        document.getElementById('class_name').value = '';
        document.getElementById('fetch-error').style.display = 'none';
        document.getElementById('fetch-fallback-info').style.display = 'none';
    }

    async function fetchStudent() {
        const nisn = document.getElementById('nisn').value.trim();
        if (!nisn) {
            showFetchError('Masukkan NISN terlebih dahulu.');
            return;
        }

        const btn = document.getElementById('btn-fetch-student');
        const label = document.getElementById('fetch-label');
        btn.disabled = true;
        label.textContent = 'Memuat...';

        document.getElementById('fetch-error').style.display = 'none';
        document.getElementById('fetch-fallback-info').style.display = 'none';

        try {
            const resp = await fetch(`{{ route('achievements.fetch-student') }}?nisn=${encodeURIComponent(nisn)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            const json = await resp.json();

            if (!resp.ok || !json.success) {
                showFetchError(json.message ?? 'Data siswa tidak ditemukan.');
                return;
            }

            const d = json.data;
            document.getElementById('student_name').value = d.student_name;
            document.getElementById('class_name').value = d.class_name ?? '';

            // Set semester_id dropdown
            const semesterSelect = document.getElementById('semester_id');
            for (let opt of semesterSelect.options) {
                if (opt.value === d.semester_id) { opt.selected = true; break; }
            }

            // Tampilkan info jika menggunakan fallback
            if (d.is_fallback) {
                const info = document.getElementById('fetch-fallback-info');
                info.innerHTML = `⚠️ Data ditemukan pada semester cadangan: <strong>${d.semester_name}</strong>. Artinya data semester aktif belum tersedia di sistem Dapodik.`;
                info.style.display = 'block';
            }

        } catch (e) {
            showFetchError('Gagal terhubung ke server. Coba lagi.');
        } finally {
            btn.disabled = false;
            label.textContent = 'Tarik Data';
        }
    }

    function showFetchError(msg) {
        const el = document.getElementById('fetch-error');
        el.textContent = msg;
        el.style.display = 'block';
        document.getElementById('student_name').value = '';
        document.getElementById('class_name').value = '';
    }

    // Pre-fill jika ada old value (validasi gagal)
    @if(old('nisn') && old('student_name'))
        // Data sudah terisi dari old(), tidak perlu fetch ulang
    @endif
</script>
@endsection
