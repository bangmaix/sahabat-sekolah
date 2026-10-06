@extends('layouts.app', [
    'title' => 'Master Data | Sahabat Sekolah',
    'activeNav' => 'admin-master-data',
    'eyebrow' => 'MASTER DATA',
    'pageTitle' => 'Kategori Perundungan & Parameter Risiko',
    'pageSubtitle' => 'Kelola kategori, subkategori, dan parameter risiko untuk seluruh sekolah.'
])

@section('content')
<section class="admin-grid">
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

<section class="panel master-category-list">
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
@endsection
