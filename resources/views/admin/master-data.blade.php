@extends('layouts.app', [
    'title' => 'Master Data | Sahabat Sekolah',
    'activeNav' => 'admin-master-data',
    'eyebrow' => 'MASTER DATA',
    'pageTitle' => 'Master Data',
    'pageSubtitle' => 'Kelola kategori perundungan, kategori prestasi, dan periode akademik untuk seluruh sekolah.'
])

@section('content')

{{-- Flash --}}
@if(session('success'))
    <div style="background:#ecfdf5;border:1px solid #6ee7b7;border-radius:.75rem;padding:1rem 1.25rem;margin-bottom:1.5rem;color:#065f46;font-size:.9rem;display:flex;align-items:center;gap:.75rem;">
        <i data-lucide="check-circle-2" style="width:18px;height:18px;flex-shrink:0;"></i>
        <span>{{ session('success') }}</span>
    </div>
@endif
@if(session('error'))
    <div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:.75rem;padding:1rem 1.25rem;margin-bottom:1.5rem;color:#7f1d1d;font-size:.9rem;display:flex;align-items:center;gap:.75rem;">
        <i data-lucide="alert-circle" style="width:18px;height:18px;flex-shrink:0;"></i>
        <span>{{ session('error') }}</span>
    </div>
@endif

{{-- ===================== SECTION 1: BULLYING CATEGORIES ===================== --}}
<div style="margin-bottom:.5rem;"><h3 style="font-size:1rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.08em;">Kategori Perundungan</h3></div>
<section class="admin-grid" style="margin-bottom:2.5rem;">
    <div class="panel">
        <div class="panel-heading">
            <div>
                <h3>Tambah Kategori</h3>
                <p>Kategori utama perundungan</p>
            </div>
        </div>
        <form class="admin-form" method="POST" action="{{ route('admin.categories.store') }}">
            @csrf
            <div style="margin-bottom: 1rem;">
                <label for="category_name" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Nama Kategori</label>
                <input id="category_name" name="name" required placeholder="Contoh: Perundungan Verbal">
            </div>
            <div style="margin-bottom: 1rem;">
                <label for="category_desc" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Deskripsi</label>
                <textarea id="category_desc" name="description" rows="3" placeholder="Deskripsi kategori"></textarea>
            </div>
            <button class="primary-button" type="submit">
                <i data-lucide="plus"></i> Tambah kategori
            </button>
        </form>
    </div>

    <div class="panel">
        <div class="panel-heading">
            <div>
                <h3>Tambah Subkategori</h3>
                <p>Parameter default risk engine</p>
            </div>
        </div>
        <form class="admin-form" method="POST" action="{{ route('admin.subcategories.store') }}">
            @csrf
            <div style="margin-bottom: 1rem;">
                <label for="subcategory_category_id" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Pilih Kategori Utama</label>
                <select id="subcategory_category_id" name="category_id" required>
                    <option value="">Pilih kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div style="margin-bottom: 1rem;">
                <label for="subcategory_name" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Nama Subkategori</label>
                <input id="subcategory_name" name="name" required placeholder="Nama subkategori">
            </div>
            <div style="margin-bottom: 1rem;">
                <label for="subcategory_risk_level" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Tingkat Risiko Default</label>
                <select id="subcategory_risk_level" name="default_risk_level">
                    <option value="LOW">LOW</option>
                    <option value="MEDIUM">MEDIUM</option>
                    <option value="HIGH">HIGH</option>
                    <option value="CRITICAL">CRITICAL</option>
                </select>
            </div>
            <div style="margin-bottom: 1rem;">
                <label for="subcategory_risk_score" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Skor Risiko Default (0 - 100)</label>
                <input id="subcategory_risk_score" type="number" name="risk_score" min="0" max="100" value="0" required placeholder="Risk score">
            </div>
            <button class="primary-button" type="submit">
                <i data-lucide="plus"></i> Tambah subkategori
            </button>
        </form>
    </div>
</section>

<section class="panel master-category-list" style="margin-bottom:2.5rem;">
    <div class="panel-heading">
        <div>
            <h3>Daftar Kategori dan Subkategori</h3>
            <p>Data aktif yang digunakan pada form laporan.</p>
        </div>
    </div>
    @foreach ($categories as $category)
        <div class="master-category">
            <div>
                <strong>{{ $category->name }}</strong>
                <small>{{ $category->description }}</small>
            </div>
            <span>{{ $category->subcategories->count() }} subkategori</span>
        </div>
        @foreach ($category->subcategories as $subcategory)
            <div class="master-subcategory">
                <span>{{ $subcategory->name }}</span>
                <small>{{ $subcategory->default_risk_level }} · {{ $subcategory->risk_score }}</small>
            </div>
        @endforeach
    @endforeach
</section>

{{-- ===================== SECTION 2: ACHIEVEMENT CATEGORIES ===================== --}}
<div style="margin-bottom:.5rem;"><h3 style="font-size:1rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.08em;">Kategori Prestasi Siswa</h3></div>
<section class="admin-grid" style="margin-bottom:2.5rem;">
    <div class="panel">
        <div class="panel-heading">
            <div>
                <h3>Tambah Kategori Prestasi</h3>
                <p>Bidang prestasi yang dapat dipilih Guru BK</p>
            </div>
        </div>
        <form class="admin-form" method="POST" action="{{ route('admin.achievement-categories.store') }}">
            @csrf
            <div style="margin-bottom:1rem;">
                <label for="ach_cat_name" style="display:block;margin-bottom:.5rem;font-weight:500;">Nama Kategori</label>
                <input id="ach_cat_name" name="name" required placeholder="Contoh: Seni Tari Kreasi">
            </div>
            <div style="margin-bottom:1rem;">
                <label for="ach_cat_desc" style="display:block;margin-bottom:.5rem;font-weight:500;">Deskripsi</label>
                <textarea id="ach_cat_desc" name="description" rows="2" placeholder="Penjelasan singkat kategori prestasi"></textarea>
            </div>
            <button class="primary-button" type="submit" id="btn-tambah-kategori-prestasi">
                <i data-lucide="plus"></i> Tambah Kategori Prestasi
            </button>
        </form>
    </div>

    <div class="panel">
        <div class="panel-heading">
            <div>
                <h3>Daftar Kategori Prestasi</h3>
                <p>Klik tombol untuk mengaktifkan/menonaktifkan.</p>
            </div>
        </div>
        <div style="display:flex;flex-direction:column;gap:.5rem;">
            @forelse($achievementCategories as $cat)
            <div style="display:flex;align-items:center;justify-content:space-between;padding:.7rem .9rem;background:var(--surface-card);border-radius:.5rem;border:1px solid var(--border-subtle);">
                <div>
                    <div style="font-weight:600;font-size:.9rem;">{{ $cat->name }}</div>
                    @if($cat->description)
                        <div style="font-size:.78rem;color:var(--text-muted);">{{ $cat->description }}</div>
                    @endif
                </div>
                <form method="POST" action="{{ route('admin.achievement-categories.toggle', $cat->id) }}">
                    @csrf
                    <button type="submit" style="padding:.3rem .7rem;border-radius:.375rem;font-size:.78rem;font-weight:600;cursor:pointer;border:1px solid;{{ $cat->status ? 'background:#ecfdf5;color:#065f46;border-color:#6ee7b7;' : 'background:#f3f4f6;color:#6b7280;border-color:#d1d5db;' }}">
                        {{ $cat->status ? '✓ Aktif' : 'Nonaktif' }}
                    </button>
                </form>
            </div>
            @empty
                <p style="color:var(--text-muted);font-size:.875rem;text-align:center;padding:1.5rem 0;">Belum ada kategori prestasi.</p>
            @endforelse
        </div>
    </div>
</section>

{{-- ===================== SECTION 3: ACADEMIC PERIODS ===================== --}}
<div style="margin-bottom:.5rem;"><h3 style="font-size:1rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.08em;">Periode Akademik (Master Semester Dapodik)</h3></div>
<section class="admin-grid">
    <div class="panel">
        <div class="panel-heading">
            <div>
                <h3>Tambah Periode</h3>
                <p>ID semester Dapodik yang akan digunakan saat tarik data siswa</p>
            </div>
        </div>
        <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:.6rem;padding:.75rem 1rem;margin-bottom:1.25rem;font-size:.83rem;color:#92400e;">
            <strong>Petunjuk:</strong> ID semester berformat 5 digit, contoh: <code>20261</code> (Tahun 2026, Semester 1 = Ganjil) atau <code>20252</code> (Tahun 2025, Semester 2 = Genap).
        </div>
        <form class="admin-form" method="POST" action="{{ route('admin.academic-periods.store') }}">
            @csrf
            <div style="margin-bottom:1rem;">
                <label for="period_semester_id" style="display:block;margin-bottom:.5rem;font-weight:500;">ID Semester Dapodik</label>
                <input id="period_semester_id" name="semester_id" required placeholder="Contoh: 20261" pattern="\d{5}" maxlength="5">
            </div>
            <div style="margin-bottom:1rem;">
                <label for="period_name" style="display:block;margin-bottom:.5rem;font-weight:500;">Nama Periode</label>
                <input id="period_name" name="name" required placeholder="Contoh: Tahun Ajaran 2026/2027 Ganjil">
            </div>
            <button class="primary-button" type="submit" id="btn-tambah-periode">
                <i data-lucide="plus"></i> Tambah Periode
            </button>
        </form>
    </div>

    <div class="panel">
        <div class="panel-heading">
            <div>
                <h3>Daftar Periode Akademik</h3>
                <p>Tetapkan semester aktif dan semester cadangan (fallback).</p>
            </div>
        </div>
        <div style="display:flex;flex-direction:column;gap:.6rem;">
            @forelse($academicPeriods as $period)
            <div style="padding:.8rem 1rem;background:var(--surface-card);border-radius:.5rem;border:2px solid {{ $period->is_active ? '#6ee7b7' : ($period->is_fallback ? '#fde68a' : 'var(--border-subtle)') }};">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:.5rem;">
                    <div>
                        <div style="font-weight:600;font-size:.9rem;display:flex;align-items:center;gap:.4rem;">
                            {{ $period->name }}
                            @if($period->is_active)
                                <span style="background:#ecfdf5;color:#065f46;font-size:.72rem;padding:.1rem .4rem;border-radius:.3rem;font-weight:700;border:1px solid #6ee7b7;">✦ AKTIF</span>
                            @elseif($period->is_fallback)
                                <span style="background:#fffbeb;color:#92400e;font-size:.72rem;padding:.1rem .4rem;border-radius:.3rem;font-weight:700;border:1px solid #fde68a;">CADANGAN</span>
                            @endif
                        </div>
                        <div style="font-family:monospace;font-size:.82rem;color:var(--text-muted);margin-top:.15rem;">ID: {{ $period->semester_id }}</div>
                    </div>
                    <div style="display:flex;gap:.4rem;flex-shrink:0;">
                        @if(!$period->is_active)
                        <form method="POST" action="{{ route('admin.academic-periods.set-active', $period->id) }}">
                            @csrf
                            <button type="submit" style="padding:.3rem .6rem;border-radius:.375rem;font-size:.75rem;font-weight:600;cursor:pointer;background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;" title="Jadikan semester aktif">
                                Aktifkan
                            </button>
                        </form>
                        @endif
                        @if(!$period->is_fallback)
                        <form method="POST" action="{{ route('admin.academic-periods.set-fallback', $period->id) }}">
                            @csrf
                            <button type="submit" style="padding:.3rem .6rem;border-radius:.375rem;font-size:.75rem;font-weight:600;cursor:pointer;background:#fffbeb;color:#92400e;border:1px solid #fde68a;" title="Jadikan semester cadangan">
                                Cadangkan
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
            @empty
                <p style="color:var(--text-muted);font-size:.875rem;text-align:center;padding:1.5rem 0;">Belum ada periode akademik.</p>
            @endforelse
        </div>
    </div>
</section>

@endsection


