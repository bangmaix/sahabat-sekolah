@extends('layouts.app', [
    'title' => 'Detail Prestasi | Sahabat Sekolah',
    'activeNav' => 'achievements',
    'eyebrow' => 'PRESTASI SISWA',
    'pageTitle' => $achievement->student_name,
    'pageSubtitle' => 'Detail capaian prestasi siswa.',
])

@section('content')

@if(session('success'))
    <div style="background:#ecfdf5;border:1px solid #6ee7b7;border-radius:.75rem;padding:1rem 1.25rem;margin-bottom:1.5rem;color:#065f46;font-size:.9rem;display:flex;align-items:center;gap:.75rem;">
        <i data-lucide="check-circle-2" style="width:18px;height:18px;flex-shrink:0;"></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

<div style="display:grid;grid-template-columns:1fr auto;gap:1rem;align-items:start;margin-bottom:1.5rem;">
    <div>
        <a href="{{ route('achievements.index') }}" style="display:inline-flex;align-items:center;gap:.4rem;color:var(--text-muted);font-size:.85rem;text-decoration:none;margin-bottom:.75rem;">
            <i data-lucide="arrow-left" style="width:15px;height:15px;"></i> Kembali
        </a>
    </div>
    @if(session('user_role') === 'COUNSELOR')
    <div style="display:flex;gap:.6rem;">
        <a href="{{ route('achievements.edit', $achievement->id) }}" class="secondary-button" id="btn-edit-prestasi">
            <i data-lucide="pencil"></i> Edit
        </a>
        <form method="POST" action="{{ route('achievements.destroy', $achievement->id) }}"
            onsubmit="return confirm('Hapus data prestasi ini? Tindakan tidak dapat dibatalkan.')">
            @csrf
            <button type="submit" class="danger-button" id="btn-hapus-prestasi" style="display:inline-flex;align-items:center;gap:.4rem;padding:.5rem 1rem;background:#fef2f2;color:#dc2626;border:1px solid #fca5a5;border-radius:.5rem;font-weight:600;font-size:.875rem;cursor:pointer;">
                <i data-lucide="trash-2" style="width:15px;height:15px;"></i> Hapus
            </button>
        </form>
    </div>
    @endif
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;">

    {{-- Panel Utama --}}
    <section class="panel">
        <div class="panel-heading" style="padding-bottom:1rem;border-bottom:1px solid var(--border-subtle);">
            <div>
                <h3>{{ $achievement->achievement_name }}</h3>
                <p>{{ $achievement->category->name ?? '—' }}</p>
            </div>
            @php
                $levelColors = ['INTERNASIONAL'=>'#7c3aed','NASIONAL'=>'#1d4ed8','PROVINSI'=>'#0369a1','KOTA'=>'#0f766e','KECAMATAN'=>'#4d7c0f','SEKOLAH'=>'#92400e'];
                $levelBg = ['INTERNASIONAL'=>'#f5f3ff','NASIONAL'=>'#eff6ff','PROVINSI'=>'#f0f9ff','KOTA'=>'#f0fdfa','KECAMATAN'=>'#f7fee7','SEKOLAH'=>'#fffbeb'];
                $lc = $levelColors[$achievement->achievement_level] ?? '#374151';
                $lb = $levelBg[$achievement->achievement_level] ?? '#f9fafb';
            @endphp
            <span style="padding:.35rem .85rem;background:{{ $lb }};color:{{ $lc }};border-radius:.5rem;font-size:.82rem;font-weight:700;letter-spacing:.02em;white-space:nowrap;">
                {{ ucfirst(strtolower($achievement->achievement_level)) }}
            </span>
        </div>

        <div style="padding:1.25rem 0;display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div>
                <div style="font-size:.78rem;font-weight:600;color:var(--text-muted);margin-bottom:.25rem;text-transform:uppercase;letter-spacing:.06em;">Peringkat</div>
                <div style="font-weight:700;font-size:1.05rem;color:var(--text-primary);">{{ $achievement->ranking }}</div>
            </div>
            <div>
                <div style="font-size:.78rem;font-weight:600;color:var(--text-muted);margin-bottom:.25rem;text-transform:uppercase;letter-spacing:.06em;">Tanggal</div>
                <div style="font-weight:600;">{{ $achievement->achievement_date->translatedFormat('d F Y') }}</div>
            </div>
            @if($achievement->organizer)
            <div style="grid-column:1/-1;">
                <div style="font-size:.78rem;font-weight:600;color:var(--text-muted);margin-bottom:.25rem;text-transform:uppercase;letter-spacing:.06em;">Penyelenggara</div>
                <div>{{ $achievement->organizer }}</div>
            </div>
            @endif
            @if($achievement->description)
            <div style="grid-column:1/-1;">
                <div style="font-size:.78rem;font-weight:600;color:var(--text-muted);margin-bottom:.25rem;text-transform:uppercase;letter-spacing:.06em;">Catatan</div>
                <div style="line-height:1.6;color:var(--text-secondary);">{{ $achievement->description }}</div>
            </div>
            @endif
        </div>

        {{-- Certificate --}}
        @if($achievement->certificate_path)
        <div style="border-top:1px solid var(--border-subtle);padding-top:1.25rem;">
            <div style="font-size:.78rem;font-weight:600;color:var(--text-muted);margin-bottom:.75rem;text-transform:uppercase;letter-spacing:.06em;">Bukti / Sertifikat</div>
            <a href="{{ route('achievements.certificate', $achievement->id) }}" class="primary-button" id="btn-unduh-sertifikat" style="display:inline-flex;align-items:center;gap:.5rem;">
                <i data-lucide="download"></i> Unduh Sertifikat
            </a>
        </div>
        @endif
    </section>

    {{-- Panel Siswa --}}
    <section class="panel" style="height:fit-content;">
        <div style="padding:.25rem 0;">
            <div style="width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#8b5cf6);display:flex;align-items:center;justify-content:center;font-size:1.5rem;font-weight:700;color:#fff;margin-bottom:1rem;">
                {{ mb_substr($achievement->student_name, 0, 1) }}
            </div>
            <h4 style="font-weight:700;font-size:1rem;margin-bottom:.25rem;">{{ $achievement->student_name }}</h4>

            <div style="margin-top:1rem;display:flex;flex-direction:column;gap:.75rem;">
                <div>
                    <div style="font-size:.78rem;font-weight:600;color:var(--text-muted);margin-bottom:.2rem;text-transform:uppercase;letter-spacing:.06em;">NISN</div>
                    <div style="font-family:monospace;font-size:.9rem;font-weight:600;">{{ $achievement->nisn }}</div>
                </div>
                @if($achievement->class_name)
                <div>
                    <div style="font-size:.78rem;font-weight:600;color:var(--text-muted);margin-bottom:.2rem;text-transform:uppercase;letter-spacing:.06em;">Kelas</div>
                    <div style="font-weight:600;">{{ $achievement->class_name }}</div>
                </div>
                @endif
                @if($achievement->semester_id)
                <div>
                    <div style="font-size:.78rem;font-weight:600;color:var(--text-muted);margin-bottom:.2rem;text-transform:uppercase;letter-spacing:.06em;">Semester Data</div>
                    <div style="font-size:.85rem;">{{ $achievement->semester_id }}</div>
                </div>
                @endif
            </div>

            <div style="border-top:1px solid var(--border-subtle);margin-top:1.25rem;padding-top:1rem;">
                <div style="font-size:.78rem;font-weight:600;color:var(--text-muted);margin-bottom:.4rem;text-transform:uppercase;letter-spacing:.06em;">Dicatat oleh</div>
                <div style="font-size:.85rem;">{{ $achievement->creator?->name ?? '—' }}</div>
                <div style="font-size:.78rem;color:var(--text-muted);">{{ $achievement->created_at->translatedFormat('d M Y, H:i') }}</div>
            </div>
        </div>
    </section>
</div>

@endsection
