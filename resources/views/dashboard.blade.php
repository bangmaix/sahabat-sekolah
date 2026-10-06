@extends('layouts.app', [
    'title' => 'Dashboard Guru BK | Sahabat Sekolah',
    'activeNav' => 'dashboard',
    'pageTitle' => 'Selamat datang, ' . session('user_name', 'Bu Ratna Sari'),
    'pageSubtitle' => 'Kelola laporan dan dampingi siswa dengan penuh kepedulian.'
])

@section('content')
<section class="hero-strip">
    <div>
        <span class="pill">RUANG AMAN SEKOLAH</span>
        <h2>Setiap laporan adalah<br>langkah menuju sekolah aman.</h2>
        <p>Terima kasih sudah hadir untuk mendengar, melindungi, dan mendampingi.</p>
    </div>
    <div class="hero-art">
        <div class="sun"></div>
        <div class="hill hill-one"></div>
        <div class="hill hill-two"></div>
        <div class="school-shape">⌂</div>
    </div>
</section>

<section class="stat-grid">
    <div class="stat-card blue">
        <div class="stat-icon"><i data-lucide="file-text"></i></div>
        <div>
            <span>Total Laporan</span>
            <strong>{{ $stats['reports'] }}</strong>
            <small><em>↑ 12%</em> dari bulan lalu</small>
        </div>
    </div>
    <div class="stat-card orange">
        <div class="stat-icon"><i data-lucide="activity"></i></div>
        <div>
            <span>Dalam Penanganan</span>
            <strong>{{ $stats['handling'] }}</strong>
            <small><em>↑ 8%</em> perlu perhatian</small>
        </div>
    </div>
    <div class="stat-card red">
        <div class="stat-icon"><i data-lucide="triangle-alert"></i></div>
        <div>
            <span>Risiko Tinggi</span>
            <strong>{{ $stats['highRisk'] }}</strong>
            <small><em>2 baru</em> minggu ini</small>
        </div>
    </div>
    <div class="stat-card pink">
        <div class="stat-icon"><i data-lucide="clock-3"></i></div>
        <div>
            <span>SLA Overdue</span>
            <strong>{{ $stats['overdue'] }}</strong>
            <small><em>Perlu segera</em> ditindaklanjuti</small>
        </div>
    </div>
</section>

<section class="content-grid">
    <div class="panel chart-panel">
        <div class="panel-heading">
            <div>
                <h3>Tren Laporan</h3>
                <p>Jumlah laporan masuk selama 6 bulan terakhir</p>
            </div>
            <button class="select-button">6 Bulan <i data-lucide="chevron-down"></i></button>
        </div>
        <div class="chart">
            <div class="chart-labels">
                <span>20</span><span>15</span><span>10</span><span>5</span><span>0</span>
            </div>
            <svg viewBox="0 0 560 180" preserveAspectRatio="none">
                <path class="area" d="M0 152 C40 145 52 124 90 132 S150 150 185 110 S235 120 270 92 S330 108 365 74 S420 85 445 52 S500 60 560 22 L560 180 L0 180Z"></path>
                <path class="line" d="M0 152 C40 145 52 124 90 132 S150 150 185 110 S235 120 270 92 S330 108 365 74 S420 85 445 52 S500 60 560 22"></path>
            </svg>
            <div class="months">
                <span>Jan</span><span>Feb</span><span>Mar</span><span>Apr</span><span>Mei</span><span>Jun</span>
            </div>
        </div>
    </div>
    <div class="panel category-panel">
        <div class="panel-heading">
            <div>
                <h3>Kategori Laporan</h3>
                <p>Distribusi berdasarkan kategori</p>
            </div>
            <i data-lucide="more-horizontal"></i>
        </div>
        <div class="donut-wrap">
            <div class="donut"></div>
            <div class="legend">
                <span><i class="dot cyan"></i>Cyberbullying <b>35%</b></span>
                <span><i class="dot blue-dot"></i>Verbal <b>25%</b></span>
                <span><i class="dot yellow"></i>Sosial <b>15%</b></span>
                <span><i class="dot coral"></i>Fisik <b>15%</b></span>
                <span><i class="dot pink-dot"></i>Seksual <b>5%</b></span>
            </div>
        </div>
    </div>
</section>

<section class="content-grid" style="margin-top: 24px;">
    <div class="panel chart-panel" style="grid-column: 1 / -1;">
        <div class="panel-heading">
            <div>
                <h3>Kinerja SLA berdasarkan Risiko</h3>
                <p>Distribusi status penyelesaian kasus berdasarkan tingkat risiko</p>
            </div>
        </div>
        <div class="bar-chart-wrap" style="display: flex; gap: 20px; align-items: flex-end; height: 200px; padding-top: 20px;">
            @foreach(['CRITICAL', 'HIGH', 'MEDIUM', 'LOW'] as $risk)
                @php 
                    $total = $slaStats[$risk]['on_time'] + $slaStats[$risk]['overdue'];
                    $onTimePct = $total > 0 ? ($slaStats[$risk]['on_time'] / $total) * 100 : 0;
                    $overduePct = $total > 0 ? ($slaStats[$risk]['overdue'] / $total) * 100 : 0;
                @endphp
                <div style="flex: 1; display: flex; flex-direction: column; align-items: center; gap: 8px;">
                    <div style="width: 100%; height: 150px; background: #f0f0f0; border-radius: 4px; display: flex; flex-direction: column; justify-content: flex-end; overflow: hidden;">
                        @if($overduePct > 0)
                            <div style="height: {{ $overduePct }}%; background: #ff4d4f; width: 100%; display: flex; align-items: center; justify-content: center; color: white; font-size: 10px;" title="Overdue: {{ $slaStats[$risk]['overdue'] }}">
                                {{ $slaStats[$risk]['overdue'] }}
                            </div>
                        @endif
                        @if($onTimePct > 0)
                            <div style="height: {{ $onTimePct }}%; background: #52c41a; width: 100%; display: flex; align-items: center; justify-content: center; color: white; font-size: 10px;" title="On Time: {{ $slaStats[$risk]['on_time'] }}">
                                {{ $slaStats[$risk]['on_time'] }}
                            </div>
                        @endif
                    </div>
                    <strong style="font-size: 12px; text-align: center;">{{ $risk }}</strong>
                </div>
            @endforeach
        </div>
        <div class="legend" style="margin-top: 15px; display: flex; gap: 15px; justify-content: center; font-size: 12px;">
            <span style="display: flex; align-items: center; gap: 5px;"><i style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: #52c41a;"></i> SLA Terpenuhi (On Time)</span>
            <span style="display: flex; align-items: center; gap: 5px;"><i style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: #ff4d4f;"></i> SLA Terlewati (Overdue)</span>
        </div>
    </div>
</section>

<section class="lower-grid">
    <div class="panel reports-panel" id="laporan">
        <div class="panel-heading">
            <div>
                <h3>Laporan Masuk Terbaru</h3>
                <p>Perlu perhatian dan tindakan Anda</p>
            </div>
            <a class="text-link" href="{{ route('reports.inbox') }}">Lihat semua <i data-lucide="arrow-up-right"></i></a>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>No. Laporan</th>
                        <th>Kategori</th>
                        <th>Ringkasan</th>
                        <th>Status</th>
                        <th>Prioritas</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cases as $case)
                        <tr>
                            <td>
                                <strong>{{ $case->case_number }}</strong>
                                <small>{{ \Illuminate\Support\Carbon::parse($case->opened_at)->format('d M Y') }}</small>
                            </td>
                            <td><span class="category-tag">{{ $case->category }}</span></td>
                            <td class="summary">{{ $case->description }}</td>
                            <td><span class="status {{ strtolower($case->status) }}">{{ str_replace('_', ' ', $case->status) }}</span></td>
                            <td><span class="priority {{ strtolower($case->risk_level) }}">{{ $case->risk_level }}</span></td>
                            <td><a class="view-button" href="{{ route('cases.show', $case->case_number) }}">Lihat <i data-lucide="arrow-right"></i></a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="panel alert-panel" id="kasus">
        <div class="panel-heading">
            <div>
                <h3>Perlu Perhatian</h3>
                <p>Prioritas tindak lanjut hari ini</p>
            </div>
        </div>
        <div class="alert-list">
            <a class="alert-item" href="{{ route('reports.inbox', ['sla_status' => 'OVERDUE']) }}">
                <div class="alert-badge red-bg"><i data-lucide="clock-3"></i></div>
                <div>
                    <strong>{{ $stats['overdue'] }} laporan melewati SLA</strong>
                    <p>Segera berikan respons awal</p>
                </div>
                <i data-lucide="chevron-right"></i>
            </a>
            <a class="alert-item" href="{{ route('reports.inbox', ['risk_level' => 'HIGH']) }}">
                <div class="alert-badge orange-bg"><i data-lucide="shield-alert"></i></div>
                <div>
                    <strong>{{ $stats['highRisk'] }} kasus risiko tinggi</strong>
                    <p>Perlu pemantauan khusus</p>
                </div>
                <i data-lucide="chevron-right"></i>
            </a>

        </div>
    </div>
</section>
@endsection