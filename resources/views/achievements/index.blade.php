@extends('layouts.app', [
    'title' => 'Prestasi Siswa | Sahabat Sekolah',
    'activeNav' => 'achievements',
    'eyebrow' => 'PRESTASI SISWA',
    'pageTitle' => 'Data Prestasi Siswa',
    'pageSubtitle' => 'Catatan capaian dan penghargaan siswa ' . (session('school_name') ?? 'sekolah Anda') . '.',
])

@section('content')

{{-- Flash Messages --}}
@if (session('success'))
    <div class="alert alert-success" style="display:flex;align-items:center;gap:.75rem;padding:1rem 1.25rem;background:#ecfdf5;border:1px solid #6ee7b7;border-radius:.75rem;margin-bottom:1.5rem;color:#065f46;font-size:.9rem;">
        <i data-lucide="check-circle-2" style="width:18px;height:18px;flex-shrink:0;"></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

{{-- Filter & Actions --}}
<section class="panel" style="margin-bottom:1.5rem;">
    <div class="panel-heading" style="padding-bottom:1rem;border-bottom:1px solid var(--border-subtle);">
        <div>
            <h3>Daftar Prestasi</h3>
            <p>Filter dan cari catatan prestasi siswa.</p>
        </div>
        @if(session('user_role') === 'COUNSELOR')
        <a href="{{ route('achievements.create') }}" class="primary-button" id="btn-tambah-prestasi">
            <i data-lucide="plus"></i> Tambah Prestasi
        </a>
        @endif
    </div>

    <form method="GET" action="{{ route('achievements.index') }}" class="admin-form" style="display:flex;gap:1rem;flex-wrap:wrap;padding-top:1rem;align-items:flex-end;">
        <div style="flex:1;min-width:180px;">
            <label for="filter-search" style="display:block;margin-bottom:.4rem;font-size:.82rem;font-weight:600;color:var(--text-muted);">Cari Siswa / Lomba</label>
            <div style="position:relative;">
                <i data-lucide="search" style="position:absolute;left:.75rem;top:50%;transform:translateY(-50%);width:16px;height:16px;color:var(--text-muted);pointer-events:none;"></i>
                <input id="filter-search" name="search" value="{{ $filters['search'] ?? '' }}"
                    placeholder="Nama siswa, NISN, nama lomba..."
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

{{-- Table --}}
<section class="panel">
    @if($achievements->isEmpty())
        <div style="text-align:center;padding:4rem 2rem;color:var(--text-muted);">
            <i data-lucide="trophy" style="width:48px;height:48px;margin-bottom:1rem;opacity:.4;display:block;margin-left:auto;margin-right:auto;"></i>
            <p style="font-weight:600;margin-bottom:.5rem;">Belum ada data prestasi.</p>
            <p style="font-size:.875rem;">
                @if(session('user_role') === 'COUNSELOR')
                    Klik tombol di bawah untuk mencatat prestasi pertama.
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
    @else
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:.875rem;">
                <thead>
                    <tr style="border-bottom:2px solid var(--border-subtle);text-align:left;">
                        <th style="padding:.75rem 1rem;font-weight:600;color:var(--text-muted);white-space:nowrap;">Siswa</th>
                        <th style="padding:.75rem 1rem;font-weight:600;color:var(--text-muted);white-space:nowrap;">Kategori</th>
                        <th style="padding:.75rem 1rem;font-weight:600;color:var(--text-muted);">Nama Lomba / Penghargaan</th>
                        <th style="padding:.75rem 1rem;font-weight:600;color:var(--text-muted);white-space:nowrap;">Tingkat</th>
                        <th style="padding:.75rem 1rem;font-weight:600;color:var(--text-muted);white-space:nowrap;">Peringkat</th>
                        <th style="padding:.75rem 1rem;font-weight:600;color:var(--text-muted);white-space:nowrap;">Tanggal</th>
                        <th style="padding:.75rem 1rem;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($achievements as $achievement)
                    <tr style="border-bottom:1px solid var(--border-subtle);transition:background .15s;" onmouseover="this.style.background='var(--surface-hover)'" onmouseout="this.style.background=''">
                        <td style="padding:.85rem 1rem;">
                            <div style="font-weight:600;line-height:1.3;">{{ $achievement->student_name }}</div>
                            <div style="color:var(--text-muted);font-size:.8rem;">{{ $achievement->nisn }} @if($achievement->class_name) · {{ $achievement->class_name }} @endif</div>
                        </td>
                        <td style="padding:.85rem 1rem;">
                            <span style="display:inline-block;padding:.2rem .6rem;background:var(--surface-card);border:1px solid var(--border-subtle);border-radius:.375rem;font-size:.78rem;font-weight:600;">{{ $achievement->category_name }}</span>
                        </td>
                        <td style="padding:.85rem 1rem;max-width:220px;">
                            <div style="font-weight:500;line-height:1.4;">{{ $achievement->achievement_name }}</div>
                            @if($achievement->organizer)
                                <div style="color:var(--text-muted);font-size:.8rem;">{{ $achievement->organizer }}</div>
                            @endif
                        </td>
                        <td style="padding:.85rem 1rem;white-space:nowrap;">
                            @php
                                $levelColors = ['INTERNASIONAL'=>'#7c3aed','NASIONAL'=>'#1d4ed8','PROVINSI'=>'#0369a1','KOTA'=>'#0f766e','KECAMATAN'=>'#4d7c0f','SEKOLAH'=>'#92400e'];
                                $levelBg = ['INTERNASIONAL'=>'#f5f3ff','NASIONAL'=>'#eff6ff','PROVINSI'=>'#f0f9ff','KOTA'=>'#f0fdfa','KECAMATAN'=>'#f7fee7','SEKOLAH'=>'#fffbeb'];
                                $lc = $levelColors[$achievement->achievement_level] ?? '#374151';
                                $lb = $levelBg[$achievement->achievement_level] ?? '#f9fafb';
                            @endphp
                            <span style="display:inline-block;padding:.2rem .65rem;background:{{ $lb }};color:{{ $lc }};border-radius:.375rem;font-size:.78rem;font-weight:700;letter-spacing:.02em;">
                                {{ ucfirst(strtolower($achievement->achievement_level)) }}
                            </span>
                        </td>
                        <td style="padding:.85rem 1rem;font-weight:600;white-space:nowrap;">{{ $achievement->ranking }}</td>
                        <td style="padding:.85rem 1rem;color:var(--text-muted);white-space:nowrap;font-size:.83rem;">
                            {{ \Carbon\Carbon::parse($achievement->achievement_date)->translatedFormat('d M Y') }}
                        </td>
                        <td style="padding:.85rem 1rem;text-align:right;white-space:nowrap;">
                            <a href="{{ route('achievements.show', $achievement->id) }}" class="secondary-button" style="padding:.35rem .75rem;font-size:.8rem;" id="btn-detail-{{ $achievement->id }}">
                                <i data-lucide="eye" style="width:14px;height:14px;"></i> Detail
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="padding:1rem 1.25rem;color:var(--text-muted);font-size:.83rem;border-top:1px solid var(--border-subtle);">
            Total: <strong>{{ $achievements->count() }}</strong> catatan prestasi
        </div>
    @endif
</section>

@endsection
