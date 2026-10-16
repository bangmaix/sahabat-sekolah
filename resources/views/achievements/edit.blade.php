@extends('layouts.app', [
    'title' => 'Edit Prestasi | Sahabat Sekolah',
    'activeNav' => 'achievements',
    'eyebrow' => 'PRESTASI SISWA',
    'pageTitle' => 'Edit Prestasi',
    'pageSubtitle' => 'Perbarui data prestasi ' . $achievement->student_name . '.',
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

    {{-- Read-only student info --}}
    <div style="background:var(--surface-card);border:1px solid var(--border-subtle);border-radius:.75rem;padding:1rem 1.25rem;margin-bottom:1.5rem;display:flex;align-items:center;gap:1rem;">
        <div style="width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#8b5cf6);display:flex;align-items:center;justify-content:center;font-size:1.2rem;font-weight:700;color:#fff;flex-shrink:0;">
            {{ mb_substr($achievement->student_name, 0, 1) }}
        </div>
        <div>
            <div style="font-weight:700;">{{ $achievement->student_name }}</div>
            <div style="font-size:.83rem;color:var(--text-muted);">NISN: {{ $achievement->nisn }} @if($achievement->class_name) · {{ $achievement->class_name }} @endif</div>
        </div>
    </div>

    <form method="POST" action="{{ route('achievements.update', $achievement->id) }}" enctype="multipart/form-data" class="admin-form" id="form-edit-prestasi">
        @csrf

        <div style="margin-bottom:1rem;">
            <label for="category_id" style="display:block;margin-bottom:.4rem;font-size:.85rem;font-weight:600;">Kategori Prestasi <span style="color:#ef4444;">*</span></label>
            <select id="category_id" name="category_id" required>
                <option value="">— Pilih kategori —</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(old('category_id', $achievement->category_id) == $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>

        <div style="margin-bottom:1rem;">
            <label for="achievement_name" style="display:block;margin-bottom:.4rem;font-size:.85rem;font-weight:600;">Nama Lomba / Penghargaan <span style="color:#ef4444;">*</span></label>
            <input id="achievement_name" name="achievement_name" type="text"
                value="{{ old('achievement_name', $achievement->achievement_name) }}" required>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">
            <div>
                <label for="achievement_level" style="display:block;margin-bottom:.4rem;font-size:.85rem;font-weight:600;">Tingkat Prestasi <span style="color:#ef4444;">*</span></label>
                <select id="achievement_level" name="achievement_level" required>
                    <option value="">— Pilih tingkat —</option>
                    @foreach($levels as $key => $label)
                        <option value="{{ $key }}" @selected(old('achievement_level', $achievement->achievement_level) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="ranking" style="display:block;margin-bottom:.4rem;font-size:.85rem;font-weight:600;">Peringkat / Juara <span style="color:#ef4444;">*</span></label>
                <select id="ranking" name="ranking" required>
                    <option value="">— Pilih peringkat —</option>
                    @foreach($rankings as $rank)
                        <option value="{{ $rank }}" @selected(old('ranking', $achievement->ranking) === $rank)>{{ $rank }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">
            <div>
                <label for="organizer" style="display:block;margin-bottom:.4rem;font-size:.85rem;font-weight:600;">Penyelenggara</label>
                <input id="organizer" name="organizer" type="text"
                    value="{{ old('organizer', $achievement->organizer) }}"
                    placeholder="Nama instansi/lembaga">
            </div>
            <div>
                <label for="achievement_date" style="display:block;margin-bottom:.4rem;font-size:.85rem;font-weight:600;">Tanggal Perolehan <span style="color:#ef4444;">*</span></label>
                <input id="achievement_date" name="achievement_date" type="date"
                    value="{{ old('achievement_date', $achievement->achievement_date->format('Y-m-d')) }}" required>
            </div>
        </div>

        <div style="margin-bottom:1rem;">
            <label for="description" style="display:block;margin-bottom:.4rem;font-size:.85rem;font-weight:600;">Deskripsi / Catatan <span style="color:var(--text-muted);font-weight:400;">(opsional)</span></label>
            <textarea id="description" name="description" rows="3">{{ old('description', $achievement->description) }}</textarea>
        </div>

        <div style="margin-bottom:1.25rem;">
            <label for="certificate" style="display:block;margin-bottom:.4rem;font-size:.85rem;font-weight:600;">
                Ganti Bukti / Sertifikat <span style="color:var(--text-muted);font-weight:400;">(opsional, biarkan kosong untuk mempertahankan)</span>
            </label>
            @if($achievement->certificate_path)
                <div style="margin-bottom:.5rem;font-size:.83rem;color:var(--text-muted);display:flex;align-items:center;gap:.4rem;">
                    <i data-lucide="file-check" style="width:14px;height:14px;color:#16a34a;"></i>
                    Sertifikat sudah diunggah.
                    <a href="{{ route('achievements.certificate', $achievement->id) }}" target="_blank" style="color:var(--accent);">Lihat</a>
                </div>
            @endif
            <input id="certificate" name="certificate" type="file" accept=".jpg,.jpeg,.png,.pdf">
        </div>

        <div style="display:flex;gap:.75rem;padding-top:1rem;border-top:1px solid var(--border-subtle);">
            <button type="submit" class="primary-button" id="btn-update-prestasi">
                <i data-lucide="save"></i> Simpan Perubahan
            </button>
            <a href="{{ route('achievements.show', $achievement->id) }}" class="secondary-button">Batal</a>
        </div>
    </form>
</section>
@endsection
