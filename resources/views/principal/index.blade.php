@extends('layouts.app', [
    'title' => 'Monitoring Kepala Sekolah | Sahabat Sekolah',
    'activeNav' => 'principal',
    'eyebrow' => 'MONITORING SEKOLAH',
    'pageTitle' => 'Ringkasan Perlindungan Siswa',
    'pageSubtitle' => 'Pantau kesehatan penanganan kasus tanpa membuka identitas sensitif.',
])

@section('content')
    <div class="principal-heading-bar">
        <div></div>
        <div class="scope-badge">
            <i data-lucide="building-2"></i> {{ $selectedSchool?->nama_sekolah }}
            <small>School Scope aktif</small>
        </div>
    </div>

    <section class="principal-stat-grid" style="grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));">
        <div class="principal-stat">
            <span class="principal-stat-icon blue-bg"><i data-lucide="files"></i></span>
            <div>
                <small>Total kasus</small>
                <strong>{{ $stats['total'] }}</strong>
                <p>Semua status</p>
            </div>
        </div>
        <div class="principal-stat">
            <span class="principal-stat-icon orange-bg"><i data-lucide="loader-circle"></i></span>
            <div>
                <small>Aktif ditangani</small>
                <strong>{{ $stats['active'] }}</strong>
                <p>Perlu monitoring</p>
            </div>
        </div>
        <div class="principal-stat">
            <span class="principal-stat-icon red-bg"><i data-lucide="shield-alert"></i></span>
            <div>
                <small>Risiko tinggi</small>
                <strong>{{ $stats['highRisk'] }}</strong>
                <p>Perhatian khusus</p>
            </div>
        </div>
        <div class="principal-stat">
            <span class="principal-stat-icon pink-bg"><i data-lucide="clock-alert"></i></span>
            <div>
                <small>SLA overdue</small>
                <strong>{{ $stats['overdue'] }}</strong>
                <p>Eskalasi otomatis</p>
            </div>
        </div>
        <a href="{{ route('achievements.index') }}" class="principal-stat" style="text-decoration:none;" title="Lihat Capaian Prestasi Siswa">
            <span class="principal-stat-icon" style="background:#fef3c7;color:#b45309;"><i data-lucide="trophy"></i></span>
            <div>
                <small>Prestasi Siswa</small>
                <strong style="color:#b45309;">{{ $achievementStats['total'] }}</strong>
                <p>{{ $achievementStats['students'] }} siswa berprestasi</p>
            </div>
        </a>
    </section>

    <section class="principal-grid">
        <div class="panel principal-cases">
            <div class="panel-heading">
                <div>
                    <h3>Kasus dalam monitoring</h3>
                    <p>{{ $selectedSchool?->nama_sekolah }}</p>
                </div>
                <span class="privacy-chip"><i data-lucide="eye-off"></i> Ringkasan saja</span>
            </div>
            <div class="principal-table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Nomor kasus</th>
                            <th>Kategori</th>
                            <th>Status</th>
                            <th>Risiko</th>
                            <th>SLA</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($cases as $case)
                            <tr>
                                <td>
                                    <strong>{{ $case->case_number }}</strong>
                                    <small>Terakhir diperbarui
                                        {{ \Illuminate\Support\Carbon::parse($case->updated_at)->diffForHumans() }}</small>
                                </td>
                                <td>{{ $case->category }}</td>
                                <td><span
                                        class="status {{ strtolower($case->status) }}">{{ str_replace('_', ' ', $case->status) }}</span>
                                </td>
                                <td><span
                                        class="priority {{ strtolower($case->risk_level) }}">{{ $case->risk_level }}</span>
                                </td>
                                <td><span class="sla-{{ strtolower($case->sla_status) }}">{{ $case->sla_status }}</span>
                                </td>
                                <td>
                                    <a class="view-button" href="{{ route('cases.show', $case->case_number) }}">
                                        Ringkasan <i data-lucide="arrow-right"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="empty-table">Belum ada kasus pada sekolah ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <aside class="panel principal-guidance">
            <div class="principal-guidance-icon"><i data-lucide="shield-check"></i></div>
            <h3>Peran monitoring</h3>
            <p>Kepala Sekolah menerima eskalasi dan SLA overdue. Detail identitas pelapor tetap dibatasi untuk pihak yang
                memiliki kewenangan penanganan.</p>
            <div class="guidance-line">
                <i data-lucide="check-circle-2"></i>
                <span>Data ditampilkan dalam lingkup {{ $selectedSchool?->nama_sekolah }}</span>
            </div>
            <div class="guidance-line">
                <i data-lucide="lock-keyhole"></i>
                <span>Akses sensitif tercatat dalam audit log</span>
            </div>
        </aside>
    </section>

    {{-- ======================== PRESTASI SISWA MONITORING ======================== --}}
    <section class="panel" style="margin-top: 20px;">
        <div class="panel-heading" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;padding-bottom:14px;border-bottom:1px solid #edf2f6;">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:36px;height:36px;border-radius:10px;background:#fef3c7;color:#b45309;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i data-lucide="trophy" style="width:20px;height:20px;"></i>
                </div>
                <div>
                    <h3 style="margin:0;font-size:18px;font-weight:700;color:#183b68;">Prestasi & Kebanggaan Siswa</h3>
                    <p style="margin:2px 0 0;font-size:13px;color:#73869e;">Data capaian prestasi siswa pada {{ $selectedSchool?->nama_sekolah ?? 'sekolah Anda' }}</p>
                </div>
            </div>
            <a class="view-button" href="{{ route('achievements.index') }}" style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;font-size:14px;text-decoration:none;">
                Lihat Seluruh Prestasi ({{ $achievementStats['total'] }}) <i data-lucide="arrow-right" style="width:14px;height:14px;"></i>
            </a>
        </div>

        @if ($recentAchievements->isNotEmpty())
            <div class="principal-table-wrap" style="margin-top:10px;">
                <table>
                    <thead>
                        <tr>
                            <th>Siswa</th>
                            <th>Capaian Prestasi</th>
                            <th>Kategori</th>
                            <th>Tingkat</th>
                            <th>Tanggal Perolehan</th>
                            <th>Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentAchievements as $ach)
                            @php
                                $badgeStyles = match ($ach->achievement_level) {
                                    'INTERNASIONAL' => 'background:#f3e8ff;color:#7e22ce;border:1px solid #d8b4fe;',
                                    'NASIONAL' => 'background:#ffe4e6;color:#be123c;border:1px solid #fecdd3;',
                                    'PROVINSI' => 'background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;',
                                    'KOTA' => 'background:#fef3c7;color:#b45309;border:1px solid #fde68a;',
                                    default => 'background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;',
                                };
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ $ach->student_name }}</strong>
                                    <small>Kelas {{ $ach->class_name ?? '-' }} &bull; NISN: {{ $ach->nisn }}</small>
                                </td>
                                <td>
                                    <strong style="color:#1675e8;">{{ $ach->ranking }}</strong>
                                    <small>{{ $ach->achievement_name }}</small>
                                </td>
                                <td>{{ $ach->category?->name ?? 'Prestasi' }}</td>
                                <td>
                                    <span style="display:inline-block;padding:2px 8px;border-radius:6px;font-size:11px;font-weight:700;letter-spacing:.02em;{{ $badgeStyles }}">
                                        {{ $ach->achievement_level }}
                                    </span>
                                </td>
                                <td>{{ \Illuminate\Support\Carbon::parse($ach->achievement_date)->format('d M Y') }}</td>
                                <td>
                                    <a class="view-button" href="{{ route('achievements.student', $ach->nisn) }}">
                                        Portofolio <i data-lucide="arrow-right"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div style="text-align:center;padding:32px 20px;color:#8093a6;">
                <div style="width:42px;height:42px;border-radius:10px;background:#fef3c7;color:#b45309;display:grid;place-items:center;margin:0 auto 10px;">
                    <i data-lucide="trophy" style="width:20px;height:20px;"></i>
                </div>
                <strong style="display:block;color:#204b77;font-size:15px;margin-bottom:3px;">Belum ada prestasi yang tercatat</strong>
                <p style="font-size:13px;color:#8093a6;margin:0;">Data prestasi yang diinputkan oleh Guru BK akan otomatis terpantau di halaman ini.</p>
            </div>
        @endif
    </section>
@endsection
