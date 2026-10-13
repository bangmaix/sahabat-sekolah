@extends('layouts.app', [
    'title' => 'Dashboard Guru BK | Sahabat Sekolah',
    'activeNav' => 'dashboard',
    'pageTitle' => 'Selamat datang, ' . session('user_name', 'Bu Ratna Sari'),
    'pageSubtitle' => 'Kelola laporan dan dampingi siswa dengan penuh kepedulian.'
])

@section('content')
@if (!empty($slaAlertSummary) && $slaAlertSummary['total'] > 0)
    <div class="sla-strip-banner">
        <div class="sla-strip-left">
            <div class="sla-strip-pulse"><i data-lucide="bell-ring"></i></div>
            <div>
                <strong>Peringatan Respons SLA: {{ $slaAlertSummary['total'] }} Laporan Siswa Menunggu Tindakan</strong>
                <p>
                    @if ($slaAlertSummary['overdue'] > 0)
                        <span class="sla-highlight-overdue">{{ $slaAlertSummary['overdue'] }} Overdue (Terlewat)</span>
                    @endif
                    @if ($slaAlertSummary['overdue'] > 0 && $slaAlertSummary['near_overdue'] > 0) &bull; @endif
                    @if ($slaAlertSummary['near_overdue'] > 0)
                        <span class="sla-highlight-warning">{{ $slaAlertSummary['near_overdue'] }} Mendekati Batas (< 6 Jam)</span>
                    @endif
                    &mdash; Segera buka laporan untuk memberikan respons awal kepada siswa.
                </p>
            </div>
        </div>
        <button type="button" class="sla-strip-btn" onclick="openSlaModal()">
            <i data-lucide="alert-triangle"></i> Buka Popup Peringatan
        </button>
    </div>
@endif

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
            <small><em>{{ $stats['reportsTrendSign'] }} {{ abs($stats['reportsTrend']) }}%</em> dari bulan lalu</small>
        </div>
    </div>
    <div class="stat-card orange">
        <div class="stat-icon"><i data-lucide="activity"></i></div>
        <div>
            <span>Dalam Penanganan</span>
            <strong>{{ $stats['handling'] }}</strong>
            <small><em>{{ $stats['handlingTrendSign'] }} {{ abs($stats['handlingTrend']) }}%</em> perlu perhatian</small>
        </div>
    </div>
    <div class="stat-card red">
        <div class="stat-icon"><i data-lucide="triangle-alert"></i></div>
        <div>
            <span>Risiko Tinggi</span>
            <strong>{{ $stats['highRisk'] }}</strong>
            <small><em>{{ $stats['highRiskNewThisWeek'] }} baru</em> minggu ini</small>
        </div>
    </div>
    <div class="stat-card pink {{ !empty($slaAlertSummary['total']) && $slaAlertSummary['total'] > 0 ? 'is-clickable' : '' }}"
        @if(!empty($slaAlertSummary['total']) && $slaAlertSummary['total'] > 0) onclick="openSlaModal()" title="Klik untuk membuka popup peringatan SLA" @endif>
        <div class="stat-icon"><i data-lucide="clock-3"></i></div>
        <div>
            <span>SLA Overdue</span>
            <strong>{{ $stats['overdue'] }}</strong>
            <small>
                @if(!empty($slaAlertSummary['total']) && $slaAlertSummary['total'] > 0)
                    <em>Klik buka popup ({{ $slaAlertSummary['total'] }})</em>
                @else
                    <em>Perlu segera</em> ditindaklanjuti
                @endif
            </small>
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
                <span>{{ $stats['maxTrend'] }}</span>
                <span>{{ round($stats['maxTrend'] * 0.75) }}</span>
                <span>{{ round($stats['maxTrend'] * 0.5) }}</span>
                <span>{{ round($stats['maxTrend'] * 0.25) }}</span>
                <span>0</span>
            </div>
            @php
                $points = [];
                $xStep = 560 / 5;
                foreach($stats['trendData'] as $idx => $val) {
                    $x = $idx * $xStep;
                    // Y: 20 is max, 150 is min (0)
                    $y = 150 - ($val / max(1, $stats['maxTrend'])) * 130;
                    $points[] = "{$x},{$y}";
                }
                $polyline = implode(' ', $points);
                $areaPolyline = "0,180 {$polyline} 560,180";
            @endphp
            <svg viewBox="0 0 560 180" preserveAspectRatio="none">
                <polygon class="area" points="{{ $areaPolyline }}"></polygon>
                <polyline class="line" points="{{ $polyline }}"></polyline>
            </svg>
            <div class="months">
                @foreach($stats['trendMonths'] as $month)
                    <span>{{ $month }}</span>
                @endforeach
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
            <div class="donut" style="background: {{ $stats['donutStyle'] }}"></div>
            <div class="legend">
                @if($stats['categoryData']->isEmpty())
                    <span><i class="dot" style="background: #e6eef7;"></i>Belum ada data <b>0%</b></span>
                @else
                    @foreach($stats['categoryData'] as $cat)
                        <span><i class="dot" style="background: {{ $cat->color }};"></i>{{ $cat->category }} <b>{{ $cat->percentage }}%</b></span>
                    @endforeach
                @endif
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
            @if (!empty($slaAlertSummary) && $slaAlertSummary['total'] > 0)
                <a class="alert-item" href="javascript:void(0)" onclick="openSlaModal()" title="Buka popup laporan SLA">
                    <div class="alert-badge red-bg"><i data-lucide="clock-3"></i></div>
                    <div>
                        <strong>{{ $slaAlertSummary['total'] }} laporan butuh respons segera</strong>
                        <p>{{ $slaAlertSummary['overdue'] }} overdue, {{ $slaAlertSummary['near_overdue'] }} mendekati batas waktu</p>
                    </div>
                    <i data-lucide="chevron-right"></i>
                </a>
            @else
                <a class="alert-item" href="{{ route('reports.inbox', ['status' => 'PENDING_RESPONSE', 'sla_status' => 'OVERDUE']) }}">
                    <div class="alert-badge red-bg"><i data-lucide="clock-3"></i></div>
                    <div>
                        <strong>{{ $stats['overdue'] }} laporan melewati SLA</strong>
                        <p>Segera berikan respons awal</p>
                    </div>
                    <i data-lucide="chevron-right"></i>
                </a>
            @endif
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

{{-- SLA Alert Modal Partial --}}
@include('partials.sla-alert-modal')

<style>
    .sla-strip-banner {
        background: linear-gradient(135deg, #fff5f5 0%, #fff9eb 100%);
        border: 1px solid #fecaca;
        border-left: 4px solid #ef4444;
        border-radius: 12px;
        padding: 14px 20px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        box-shadow: 0 4px 14px rgba(239, 68, 68, 0.08);
        animation: slaBannerSlide 0.3s ease-out;
    }

    @keyframes slaBannerSlide {
        from { opacity: 0; transform: translateY(-8px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .sla-strip-left {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .sla-strip-pulse {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: #fee2e2;
        color: #dc2626;
        display: grid;
        place-items: center;
        flex-shrink: 0;
    }

    .sla-strip-pulse svg {
        width: 19px;
        height: 19px;
    }

    .sla-strip-left strong {
        display: block;
        font: 800 13px 'Plus Jakarta Sans', sans-serif;
        color: #991b1b;
        margin-bottom: 3px;
    }

    .sla-strip-left p {
        margin: 0;
        font-size: 11px;
        color: #7f1d1d;
        line-height: 1.4;
    }

    .sla-highlight-overdue {
        font-weight: 700;
        color: #b91c1c;
        background: #fecaca;
        padding: 1px 6px;
        border-radius: 4px;
    }

    .sla-highlight-warning {
        font-weight: 700;
        color: #b45309;
        background: #fef3c7;
        padding: 1px 6px;
        border-radius: 4px;
    }

    .sla-strip-btn {
        background: #dc2626;
        color: #ffffff;
        border: 0;
        border-radius: 8px;
        padding: 8px 14px;
        font: 700 11px 'DM Sans', sans-serif;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        white-space: nowrap;
        box-shadow: 0 4px 10px rgba(220, 38, 38, 0.25);
        transition: all 0.15s ease;
    }

    .sla-strip-btn:hover {
        background: #b91c1c;
        transform: translateY(-1px);
        box-shadow: 0 6px 14px rgba(220, 38, 38, 0.35);
    }

    .sla-strip-btn svg {
        width: 14px;
        height: 14px;
    }

    .stat-card.pink.is-clickable {
        cursor: pointer;
        transition: all 0.15s ease;
        position: relative;
    }

    .stat-card.pink.is-clickable:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(239, 107, 112, 0.15);
        border-color: #fca5a5;
    }

    @media (max-width: 768px) {
        .sla-strip-banner {
            flex-direction: column;
            align-items: flex-start;
        }
        .sla-strip-btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endsection