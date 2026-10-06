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

    <section class="principal-stat-grid">
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
@endsection
