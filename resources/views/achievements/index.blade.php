@extends('layouts.app', [
    'title' => 'Prestasi Siswa | Sahabat Sekolah',
    'activeNav' => 'achievements',
    'eyebrow' => 'PRESTASI SISWA',
    'pageTitle' => 'Data Prestasi Siswa',
    'pageSubtitle' => 'Data capaian prestasi siswa ' . (session('school_name') ?? 'sekolah Anda') . ' dikelompokkan berdasarkan NISN siswa.',
])

@section('content')

{{-- Flash Messages --}}
@if (session('success'))
    <div class="alert alert-success" style="display:flex;align-items:center;gap:.75rem;padding:1rem 1.25rem;background:#ecfdf5;border:1px solid #6ee7b7;border-radius:.75rem;margin-bottom:1.5rem;color:#065f46;font-size:.9rem;">
        <i data-lucide="check-circle-2" style="width:18px;height:18px;flex-shrink:0;"></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

{{-- Summary Stats Grid --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:1rem;margin-bottom:1.5rem;">
    <div class="panel" style="padding:1.25rem;display:flex;align-items:center;gap:1rem;">
        <div style="width:48px;height:48px;border-radius:.75rem;background:#eff6ff;color:#1d4ed8;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i data-lucide="users" style="width:24px;height:24px;"></i>
        </div>
        <div>
            <div style="font-size:.8rem;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Siswa Berprestasi</div>
            <div style="font-size:1.6rem;font-weight:800;color:var(--text-primary);line-height:1.2;">{{ $totalStudents }}</div>
            <div style="font-size:.78rem;color:var(--text-muted);margin-top:.2rem;">Siswa terdata di sekolah</div>
        </div>
    </div>

    <div class="panel" style="padding:1.25rem;display:flex;align-items:center;gap:1rem;">
        <div style="width:48px;height:48px;border-radius:.75rem;background:#fef3c7;color:#b45309;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i data-lucide="trophy" style="width:24px;height:24px;"></i>
        </div>
        <div>
            <div style="font-size:.8rem;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Total Prestasi</div>
            <div style="font-size:1.6rem;font-weight:800;color:var(--text-primary);line-height:1.2;">{{ $totalAchievements }}</div>
            <div style="font-size:.78rem;color:var(--text-muted);margin-top:.2rem;">Penghargaan tercatat</div>
        </div>
    </div>

    <div class="panel" style="padding:1.25rem;display:flex;align-items:center;gap:1rem;">
        <div style="width:48px;height:48px;border-radius:.75rem;background:#ecfdf5;color:#047857;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i data-lucide="award" style="width:24px;height:24px;"></i>
        </div>
        <div>
            <div style="font-size:.8rem;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Pengelompokan</div>
            <div style="font-size:1.05rem;font-weight:700;color:var(--text-primary);line-height:1.2;">Berdasarkan NISN</div>
            <div style="font-size:.78rem;color:var(--text-muted);margin-top:.2rem;">Portofolio kumulatif siswa</div>
        </div>
    </div>
</div>

{{-- Filter & Actions --}}
<section class="panel" style="margin-bottom:1.5rem;">
    <div class="panel-heading" style="padding-bottom:1rem;border-bottom:1px solid var(--border-subtle);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
        <div>
            <h3>Daftar Prestasi Berdasarkan Siswa</h3>
            <p>Data dikelompokkan per NISN siswa untuk melihat riwayat prestasi secara menyeluruh.</p>
        </div>
        <div style="display:flex;align-items:center;gap:.6rem;">
            @if(!$studentsGrouped->isEmpty())
                <button type="button" class="secondary-button" onclick="toggleAllStudents()" id="btn-toggle-all" style="padding:.5rem .9rem;font-size:.82rem;">
                    <i data-lucide="chevrons-up-down" style="width:14px;height:14px;"></i>
                    <span id="label-toggle-all">Tutup Semua</span>
                </button>
            @endif
            @if(session('user_role') === 'COUNSELOR')
            <a href="{{ route('achievements.create') }}" class="primary-button" id="btn-tambah-prestasi">
                <i data-lucide="plus"></i> Tambah Prestasi
            </a>
            @endif
        </div>
    </div>

    <form method="GET" action="{{ route('achievements.index') }}" class="admin-form" style="display:flex;gap:1rem;flex-wrap:wrap;padding-top:1rem;align-items:flex-end;">
        <div style="flex:1;min-width:200px;">
            <label for="filter-search" style="display:block;margin-bottom:.4rem;font-size:.82rem;font-weight:600;color:var(--text-muted);">Cari Siswa / NISN / Lomba</label>
            <div style="position:relative;">
                <i data-lucide="search" style="position:absolute;left:.75rem;top:50%;transform:translateY(-50%);width:16px;height:16px;color:var(--text-muted);pointer-events:none;"></i>
                <input id="filter-search" name="search" value="{{ $filters['search'] ?? '' }}"
                    placeholder="Nama siswa, NISN, lomba..."
                    style="padding-left:2.5rem;width:100%;">
            </div>
        </div>
        <div style="min-width:160px;">
            <label for="filter-category" style="display:block;margin-bottom:.4rem;font-size:.82rem;font-weight:600;color:var(--text-muted);">Kategori</label>
            <select id="filter-category" name="category_id">
                <option value="">Semua kategori</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(($filters['category_id'] ?? '') == $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div style="min-width:160px;">
            <label for="filter-level" style="display:block;margin-bottom:.4rem;font-size:.82rem;font-weight:600;color:var(--text-muted);">Tingkat</label>
            <select id="filter-level" name="achievement_level">
                <option value="">Semua tingkat</option>
                @foreach(['SEKOLAH','KECAMATAN','KOTA','PROVINSI','NASIONAL','INTERNASIONAL'] as $level)
                    <option value="{{ $level }}" @selected(($filters['achievement_level'] ?? '') === $level)>{{ ucfirst(strtolower($level)) }}</option>
                @endforeach
            </select>
        </div>
        <div style="display:flex;gap:.5rem;">
            <button type="submit" class="primary-button" id="btn-filter-prestasi">
                <i data-lucide="filter"></i> Filter
            </button>
            @if(array_filter($filters))
                <a href="{{ route('achievements.index') }}" class="secondary-button">Reset</a>
            @endif
        </div>
    </form>
</section>

{{-- Grouped Student List --}}
@if($studentsGrouped->isEmpty())
    <section class="panel">
        <div style="text-align:center;padding:4rem 2rem;color:var(--text-muted);">
            <i data-lucide="trophy" style="width:48px;height:48px;margin-bottom:1rem;opacity:.4;display:block;margin-left:auto;margin-right:auto;"></i>
            <p style="font-weight:600;margin-bottom:.5rem;">Belum ada data prestasi.</p>
            <p style="font-size:.875rem;">
                @if(session('user_role') === 'COUNSELOR')
                    Klik tombol di bawah untuk mencatat prestasi siswa pertama.
                    <div style="margin-top:1.25rem;">
                        <a href="{{ route('achievements.create') }}" class="primary-button" style="display:inline-flex;align-items:center;gap:.5rem;" id="btn-tambah-prestasi-empty">
                            <i data-lucide="plus"></i> Tambah Prestasi
                        </a>
                    </div>
                @else
                    Belum ada catatan prestasi siswa di sekolah ini.
                @endif
            </p>
        </div>
    </section>
@else
    <div style="display:flex;flex-direction:column;gap:1.25rem;">
        @php
            $levelColors = ['INTERNASIONAL'=>'#7c3aed','NASIONAL'=>'#1d4ed8','PROVINSI'=>'#0369a1','KOTA'=>'#0f766e','KECAMATAN'=>'#4d7c0f','SEKOLAH'=>'#92400e'];
            $levelBg = ['INTERNASIONAL'=>'#f5f3ff','NASIONAL'=>'#eff6ff','PROVINSI'=>'#f0f9ff','KOTA'=>'#f0fdfa','KECAMATAN'=>'#f7fee7','SEKOLAH'=>'#fffbeb'];
        @endphp

        @foreach($studentsGrouped as $group)
        <div class="panel student-achievement-card" id="student-card-{{ $group->nisn }}" style="padding:0;overflow:hidden;border:1px solid var(--border-subtle);transition:box-shadow .2s;">
            {{-- Student Header Bar --}}
            <div style="padding:1.15rem 1.35rem;background:var(--surface-card);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;border-bottom:1px solid var(--border-subtle);">
                <div style="display:flex;align-items:center;gap:1rem;">
                    {{-- Avatar Inisial --}}
                    <div style="width:46px;height:46px;border-radius:50%;background:linear-gradient(135deg,#4f46e5,#7c3aed);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:1.2rem;flex-shrink:0;box-shadow:0 2px 6px rgba(79,70,229,.25);">
                        {{ mb_substr($group->student_name, 0, 1) }}
                    </div>
                    <div>
                        <div style="display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;">
                            <a href="{{ route('achievements.student', $group->nisn) }}" style="font-weight:700;font-size:1.05rem;color:var(--text-primary);text-decoration:none;" class="hover:underline" title="Lihat Portofolio Siswa">
                                {{ $group->student_name }}
                            </a>
                            @if($group->class_name)
                                <span style="background:var(--surface-hover);border:1px solid var(--border-subtle);color:var(--text-secondary);font-size:.78rem;font-weight:600;padding:.15rem .55rem;border-radius:.35rem;">
                                    Kelas {{ $group->class_name }}
                                </span>
                            @endif
                        </div>
                        <div style="display:flex;align-items:center;gap:.75rem;margin-top:.25rem;font-size:.82rem;color:var(--text-muted);">
                            <span>NISN: <code style="font-family:monospace;font-weight:600;">{{ $group->nisn }}</code></span>
                            @if($group->highest_level)
                                <span>· Capaian Tertinggi:
                                    <strong style="color:{{ $levelColors[$group->highest_level] ?? '#374151' }};">
                                        {{ ucfirst(strtolower($group->highest_level)) }}
                                        @if($group->highest_ranking) ({{ $group->highest_ranking }}) @endif
                                    </strong>
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Badges & Actions --}}
                <div style="display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;">
                    {{-- Total Prestasi Badge --}}
                    <span style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;padding:.35rem .85rem;border-radius:2rem;font-weight:700;font-size:.85rem;display:inline-flex;align-items:center;gap:.4rem;">
                        <i data-lucide="trophy" style="width:15px;height:15px;color:#2563eb;"></i>
                        <span>{{ $group->total_achievements }} Prestasi</span>
                    </span>

                    {{-- Link Portofolio Siswa --}}
                    <a href="{{ route('achievements.student', $group->nisn) }}" class="secondary-button" style="padding:.4rem .8rem;font-size:.8rem;display:inline-flex;align-items:center;gap:.35rem;" title="Portofolio Lengkap">
                        <i data-lucide="user-check" style="width:14px;height:14px;"></i> Portofolio
                    </a>

                    @if(session('user_role') === 'COUNSELOR')
                        {{-- Tambah Prestasi untuk Siswa Ini --}}
                        <a href="{{ route('achievements.create', ['nisn' => $group->nisn]) }}" class="primary-button" style="padding:.4rem .85rem;font-size:.8rem;display:inline-flex;align-items:center;gap:.35rem;" title="Tambah prestasi untuk siswa ini">
                            <i data-lucide="plus" style="width:14px;height:14px;"></i> Tambah Prestasi
                        </a>
                    @endif

                    {{-- Toggle Details Button --}}
                    <button type="button" class="secondary-button btn-toggle-student" onclick="toggleStudent('{{ $group->nisn }}')" style="padding:.4rem .75rem;font-size:.8rem;display:inline-flex;align-items:center;gap:.35rem;" title="Tampilkan/sembunyikan rincian">
                        <i data-lucide="chevron-up" id="icon-toggle-{{ $group->nisn }}" style="width:14px;height:14px;transition:transform .2s;"></i>
                    </button>
                </div>
            </div>

            {{-- Collapsible Details Section --}}
            <div id="student-details-{{ $group->nisn }}" class="student-details-body" style="background:#fafafa;">
                <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;font-size:.85rem;">
                        <thead>
                            <tr style="border-bottom:1px solid var(--border-subtle);background:var(--surface-hover);text-align:left;">
                                <th style="padding:.65rem 1.25rem;font-weight:600;color:var(--text-muted);font-size:.78rem;text-transform:uppercase;letter-spacing:.05em;">Nama Lomba / Penghargaan</th>
                                <th style="padding:.65rem 1rem;font-weight:600;color:var(--text-muted);font-size:.78rem;text-transform:uppercase;letter-spacing:.05em;">Kategori</th>
                                <th style="padding:.65rem 1rem;font-weight:600;color:var(--text-muted);font-size:.78rem;text-transform:uppercase;letter-spacing:.05em;">Tingkat</th>
                                <th style="padding:.65rem 1rem;font-weight:600;color:var(--text-muted);font-size:.78rem;text-transform:uppercase;letter-spacing:.05em;">Peringkat</th>
                                <th style="padding:.65rem 1rem;font-weight:600;color:var(--text-muted);font-size:.78rem;text-transform:uppercase;letter-spacing:.05em;">Tanggal</th>
                                <th style="padding:.65rem 1rem;font-weight:600;color:var(--text-muted);font-size:.78rem;text-transform:uppercase;letter-spacing:.05em;">Sertifikat</th>
                                <th style="padding:.65rem 1.25rem;text-align:right;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($group->achievements as $ach)
                            <tr style="border-bottom:1px solid var(--border-subtle);background:#fff;transition:background .15s;" onmouseover="this.style.background='var(--surface-hover)'" onmouseout="this.style.background='#fff'">
                                <td style="padding:.75rem 1.25rem;">
                                    <div style="font-weight:600;color:var(--text-primary);line-height:1.3;">{{ $ach->achievement_name }}</div>
                                    @if($ach->organizer)
                                        <div style="font-size:.78rem;color:var(--text-muted);margin-top:.15rem;">{{ $ach->organizer }}</div>
                                    @endif
                                </td>
                                <td style="padding:.75rem 1rem;">
                                    <span style="display:inline-block;padding:.2rem .55rem;background:var(--surface-card);border:1px solid var(--border-subtle);border-radius:.35rem;font-size:.76rem;font-weight:600;">
                                        {{ $ach->category->name ?? $ach->category_name ?? '—' }}
                                    </span>
                                </td>
                                <td style="padding:.75rem 1rem;white-space:nowrap;">
                                    @php
                                        $lc = $levelColors[$ach->achievement_level] ?? '#374151';
                                        $lb = $levelBg[$ach->achievement_level] ?? '#f9fafb';
                                    @endphp
                                    <span style="display:inline-block;padding:.2rem .6rem;background:{{ $lb }};color:{{ $lc }};border-radius:.35rem;font-size:.76rem;font-weight:700;">
                                        {{ ucfirst(strtolower($ach->achievement_level)) }}
                                    </span>
                                </td>
                                <td style="padding:.75rem 1rem;font-weight:600;white-space:nowrap;">
                                    {{ $ach->ranking }}
                                </td>
                                <td style="padding:.75rem 1rem;color:var(--text-muted);white-space:nowrap;font-size:.8rem;">
                                    {{ \Carbon\Carbon::parse($ach->achievement_date)->translatedFormat('d M Y') }}
                                </td>
                                <td style="padding:.75rem 1rem;white-space:nowrap;">
                                    @if($ach->certificate_path)
                                        <a href="{{ route('achievements.certificate', $ach->id) }}" style="display:inline-flex;align-items:center;gap:.3rem;color:#16a34a;font-weight:600;font-size:.78rem;text-decoration:none;" title="Unduh Sertifikat">
                                            <i data-lucide="file-check" style="width:14px;height:14px;"></i> Unduh
                                        </a>
                                    @else
                                        <span style="color:var(--text-muted);font-size:.78rem;">—</span>
                                    @endif
                                </td>
                                <td style="padding:.75rem 1.25rem;text-align:right;white-space:nowrap;">
                                    <a href="{{ route('achievements.show', $ach->id) }}" class="secondary-button" style="padding:.3rem .65rem;font-size:.78rem;display:inline-flex;align-items:center;gap:.3rem;" id="btn-detail-{{ $ach->id }}">
                                        <i data-lucide="eye" style="width:13px;height:13px;"></i> Detail
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div style="padding:1.25rem 0;color:var(--text-muted);font-size:.83rem;text-align:center;">
        Menampilkan <strong>{{ $totalStudents }}</strong> siswa dengan total <strong>{{ $totalAchievements }}</strong> catatan prestasi.
    </div>
@endif

<script>
    let allExpanded = true;

    function toggleStudent(nisn) {
        const body = document.getElementById(`student-details-${nisn}`);
        const icon = document.getElementById(`icon-toggle-${nisn}`);
        if (!body || !icon) return;

        if (body.style.display === 'none') {
            body.style.display = 'block';
            icon.style.transform = 'rotate(0deg)';
        } else {
            body.style.display = 'none';
            icon.style.transform = 'rotate(180deg)';
        }
    }

    function toggleAllStudents() {
        allExpanded = !allExpanded;
        const details = document.querySelectorAll('.student-details-body');
        const label = document.getElementById('label-toggle-all');

        details.forEach(el => {
            el.style.display = allExpanded ? 'block' : 'none';
        });

        document.querySelectorAll('.btn-toggle-student i').forEach(icon => {
            icon.style.transform = allExpanded ? 'rotate(0deg)' : 'rotate(180deg)';
        });

        if (label) {
            label.textContent = allExpanded ? 'Tutup Semua' : 'Buka Semua';
        }
    }
</script>

@endsection
