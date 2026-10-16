@extends('layouts.app', [
    'title' => 'Portofolio Prestasi: ' . $student->student_name . ' | Sahabat Sekolah',
    'activeNav' => 'achievements',
    'eyebrow' => 'PORTOFOLIO PRESTASI SISWA',
    'pageTitle' => $student->student_name,
    'pageSubtitle' => 'Riwayat lengkap capaian prestasi siswa berdasarkan NISN ' . $student->nisn,
])

@section('content')

@php
    $levelColors = [
        'INTERNASIONAL' => '#7c3aed',
        'NASIONAL' => '#1d4ed8',
        'PROVINSI' => '#0369a1',
        'KOTA' => '#0f766e',
        'KECAMATAN' => '#4d7c0f',
        'SEKOLAH' => '#92400e',
    ];
    $levelBg = [
        'INTERNASIONAL' => '#f5f3ff',
        'NASIONAL' => '#eff6ff',
        'PROVINSI' => '#f0f9ff',
        'KOTA' => '#f0fdfa',
        'KECAMATAN' => '#f7fee7',
        'SEKOLAH' => '#fffbeb',
    ];
@endphp

{{-- Navigation Bar --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem;">
    <div>
        <a href="{{ route('achievements.index') }}" style="display:inline-flex;align-items:center;gap:.4rem;color:var(--text-muted);font-size:.875rem;text-decoration:none;">
            <i data-lucide="arrow-left" style="width:16px;height:16px;"></i> Kembali ke Rekap Prestasi
        </a>
    </div>
    @if(session('user_role') === 'COUNSELOR')
    <div>
        <a href="{{ route('achievements.create', ['nisn' => $student->nisn]) }}" class="primary-button" id="btn-tambah-prestasi-student">
            <i data-lucide="plus"></i> Tambah Prestasi untuk Siswa Ini
        </a>
    </div>
    @endif
</div>

{{-- Student Profile Card Banner --}}
<div class="panel" style="margin-bottom:1.75rem;padding:1.5rem;background:linear-gradient(135deg, rgba(79,70,229,.05), rgba(124,58,237,.08));border:1px solid rgba(99,102,241,.2);">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1.5rem;">
        <div style="display:flex;align-items:center;gap:1.25rem;">
            <div style="width:68px;height:68px;border-radius:50%;background:linear-gradient(135deg,#4f46e5,#7c3aed);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.85rem;font-weight:800;flex-shrink:0;box-shadow:0 4px 12px rgba(79,70,229,.3);">
                {{ mb_substr($student->student_name, 0, 1) }}
            </div>
            <div>
                <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;">
                    <h2 style="font-size:1.45rem;font-weight:800;color:var(--text-primary);margin:0;">{{ $student->student_name }}</h2>
                    @if($student->class_name)
                        <span style="background:var(--surface-card);border:1px solid var(--border-subtle);color:var(--text-secondary);font-size:.8rem;font-weight:600;padding:.2rem .65rem;border-radius:.35rem;">
                            Kelas {{ $student->class_name }}
                        </span>
                    @endif
                </div>
                <div style="display:flex;align-items:center;gap:1rem;margin-top:.4rem;font-size:.85rem;color:var(--text-muted);flex-wrap:wrap;">
                    <span>NISN: <code style="font-family:monospace;font-weight:700;color:var(--text-primary);">{{ $student->nisn }}</code></span>
                    <span>·</span>
                    <span>Sekolah NPSN: <code>{{ session('school_npsn') }}</code></span>
                </div>
            </div>
        </div>

        {{-- Highlight Stats Chips --}}
        <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
            <div style="background:var(--surface-card);padding:.75rem 1.25rem;border-radius:.75rem;border:1px solid var(--border-subtle);text-align:center;min-width:110px;">
                <div style="font-size:.72rem;color:var(--text-muted);font-weight:700;text-transform:uppercase;letter-spacing:.05em;">Total Prestasi</div>
                <div style="font-size:1.5rem;font-weight:800;color:#2563eb;margin-top:.15rem;">{{ $student->total_achievements }}</div>
            </div>

            <div style="background:var(--surface-card);padding:.75rem 1.25rem;border-radius:.75rem;border:1px solid var(--border-subtle);text-align:center;min-width:140px;">
                <div style="font-size:.72rem;color:var(--text-muted);font-weight:700;text-transform:uppercase;letter-spacing:.05em;">Capaian Tertinggi</div>
                <div style="font-size:1.05rem;font-weight:800;color:{{ $levelColors[$student->highest_level] ?? '#374151' }};margin-top:.3rem;">
                    {{ ucfirst(strtolower($student->highest_level)) }}
                </div>
                @if($student->highest_achievement?->ranking)
                    <div style="font-size:.72rem;color:var(--text-muted);margin-top:.1rem;">{{ $student->highest_achievement->ranking }}</div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Section List of Achievements --}}
<section class="panel">
    <div class="panel-heading" style="padding-bottom:1rem;border-bottom:1px solid var(--border-subtle);display:flex;align-items:center;justify-content:space-between;">
        <div>
            <h3>Daftar Seluruh Prestasi & Penghargaan</h3>
            <p>Rincian riwayat prestasi siswa dari waktu ke waktu.</p>
        </div>
        <span style="font-size:.85rem;color:var(--text-muted);font-weight:600;">
            {{ $achievements->count() }} Catatan
        </span>
    </div>

    <div style="display:flex;flex-direction:column;gap:1.25rem;padding:1.25rem 0 0;">
        @foreach($achievements as $ach)
            @php
                $lc = $levelColors[$ach->achievement_level] ?? '#374151';
                $lb = $levelBg[$ach->achievement_level] ?? '#f9fafb';
            @endphp
            <div style="border:1px solid var(--border-subtle);border-radius:.75rem;padding:1.25rem;background:var(--surface-card);box-shadow:0 1px 3px rgba(0,0,0,.03);display:flex;flex-direction:column;gap:.85rem;">
                {{-- Title & Header --}}
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
                    <div style="flex:1;min-width:240px;">
                        <div style="display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;margin-bottom:.35rem;">
                            <span style="padding:.2rem .65rem;background:{{ $lb }};color:{{ $lc }};border-radius:.35rem;font-size:.75rem;font-weight:700;letter-spacing:.02em;">
                                {{ ucfirst(strtolower($ach->achievement_level)) }}
                            </span>
                            <span style="padding:.2rem .6rem;background:var(--surface-hover);border:1px solid var(--border-subtle);border-radius:.35rem;font-size:.75rem;font-weight:600;color:var(--text-secondary);">
                                {{ $ach->category->name ?? $ach->category_name ?? 'Prestasi' }}
                            </span>
                            <span style="font-size:.8rem;color:var(--text-muted);display:inline-flex;align-items:center;gap:.25rem;">
                                <i data-lucide="calendar" style="width:13px;height:13px;"></i>
                                {{ \Carbon\Carbon::parse($ach->achievement_date)->translatedFormat('d F Y') }}
                            </span>
                        </div>
                        <h4 style="font-size:1.1rem;font-weight:700;color:var(--text-primary);margin:0 0 .25rem 0;line-height:1.35;">
                            {{ $ach->achievement_name }}
                        </h4>
                        @if($ach->organizer)
                            <div style="font-size:.82rem;color:var(--text-muted);display:inline-flex;align-items:center;gap:.3rem;">
                                <i data-lucide="building" style="width:13px;height:13px;"></i>
                                Penyelenggara: <strong style="color:var(--text-secondary);">{{ $ach->organizer }}</strong>
                            </div>
                        @endif
                    </div>

                    {{-- Ranking & Actions --}}
                    <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;">
                        <div style="padding:.35rem .85rem;background:#eff6ff;color:#1e40af;border:1px solid #bfdbfe;border-radius:.5rem;font-weight:700;font-size:.9rem;display:inline-flex;align-items:center;gap:.35rem;">
                            <i data-lucide="award" style="width:15px;height:15px;color:#2563eb;"></i>
                            {{ $ach->ranking }}
                        </div>
                        <a href="{{ route('achievements.show', $ach->id) }}" class="secondary-button" style="padding:.4rem .8rem;font-size:.8rem;display:inline-flex;align-items:center;gap:.3rem;" id="btn-detail-{{ $ach->id }}">
                            <i data-lucide="eye" style="width:14px;height:14px;"></i> Detail
                        </a>
                        @if(session('user_role') === 'COUNSELOR')
                            <a href="{{ route('achievements.edit', $ach->id) }}" class="secondary-button" style="padding:.4rem .75rem;font-size:.8rem;display:inline-flex;align-items:center;gap:.3rem;" id="btn-edit-{{ $ach->id }}">
                                <i data-lucide="pencil" style="width:13px;height:13px;"></i> Edit
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Description if available --}}
                @if($ach->description)
                    <div style="background:var(--surface-hover);padding:.75rem 1rem;border-radius:.5rem;font-size:.85rem;color:var(--text-secondary);line-height:1.5;">
                        {{ $ach->description }}
                    </div>
                @endif

                {{-- Footer Info --}}
                <div style="display:flex;align-items:center;justify-content:space-between;border-top:1px dashed var(--border-subtle);padding-top:.75rem;font-size:.78rem;color:var(--text-muted);flex-wrap:wrap;gap:.75rem;">
                    <div style="display:flex;align-items:center;gap:1rem;">
                        @if($ach->semester_id)
                            <span>Semester Data: <strong>{{ $ach->semester_id }}</strong></span>
                        @endif
                        <span>Dicatat oleh: <strong>{{ $ach->creator?->name ?? 'Guru BK' }}</strong></span>
                    </div>

                    @if($ach->certificate_path)
                        <a href="{{ route('achievements.certificate', $ach->id) }}" style="display:inline-flex;align-items:center;gap:.35rem;color:#16a34a;font-weight:600;text-decoration:none;" title="Unduh Sertifikat">
                            <i data-lucide="file-check" style="width:15px;height:15px;"></i> Unduh Sertifikat
                        </a>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</section>

@endsection
