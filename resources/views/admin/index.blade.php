@extends('layouts.app', [
    'title' => 'Dashboard Admin | Sahabat Sekolah',
    'activeNav' => 'admin-dashboard',
    'eyebrow' => 'SYSTEM ADMINISTRATION',
    'pageTitle' => 'Dashboard Admin',
    'pageSubtitle' => 'Ringkasan keseluruhan platform Sahabat Sekolah.'
])

@section('content')
{{-- ======================== STAT CARDS ======================== --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">

    {{-- Total Users --}}
    <div class="bg-white border border-[#e8eef5] rounded-xl p-5 flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl bg-[#e3f0ff] text-[#277fe0] flex items-center justify-center flex-none">
            <i data-lucide="users" class="w-5 h-5"></i>
        </div>
        <div>
            <p class="text-[10px] text-[#73869e] font-semibold uppercase tracking-wide">Total Pengguna</p>
            <strong class="block text-2xl font-extrabold text-[#183b68] leading-tight" style="font-family:'Plus Jakarta Sans'">{{ $totalUsers }}</strong>
            <span class="text-[10px] text-[#73869e]">Terdaftar di platform</span>
        </div>
    </div>

    {{-- Total Laporan --}}
    <div class="bg-white border border-[#e8eef5] rounded-xl p-5 flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl bg-[#fff0d9] text-[#e69b27] flex items-center justify-center flex-none">
            <i data-lucide="file-text" class="w-5 h-5"></i>
        </div>
        <div>
            <p class="text-[10px] text-[#73869e] font-semibold uppercase tracking-wide">Total Pengaduan</p>
            <strong class="block text-2xl font-extrabold text-[#183b68] leading-tight" style="font-family:'Plus Jakarta Sans'">{{ $totalReports }}</strong>
            <span class="text-[10px] text-[#e69b27] font-bold">{{ $inHandlingCases }} sedang ditangani</span>
        </div>
    </div>

    {{-- Resolved --}}
    <div class="bg-white border border-[#e8eef5] rounded-xl p-5 flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl bg-[#e3fff4] text-[#24906a] flex items-center justify-center flex-none">
            <i data-lucide="circle-check" class="w-5 h-5"></i>
        </div>
        <div>
            <p class="text-[10px] text-[#73869e] font-semibold uppercase tracking-wide">Kasus Selesai</p>
            <strong class="block text-2xl font-extrabold text-[#183b68] leading-tight" style="font-family:'Plus Jakarta Sans'">{{ $resolvedCases }}</strong>
            @php $resolveRate = $totalCases > 0 ? round(($resolvedCases / $totalCases) * 100) : 0; @endphp
            <span class="text-[10px] text-[#24906a] font-bold">{{ $resolveRate }}% tingkat resolusi</span>
        </div>
    </div>

    {{-- SLA Overdue --}}
    <div class="bg-white border border-[#e8eef5] rounded-xl p-5 flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl bg-[#ffe2e3] text-[#e6636a] flex items-center justify-center flex-none">
            <i data-lucide="clock-alert" class="w-5 h-5"></i>
        </div>
        <div>
            <p class="text-[10px] text-[#73869e] font-semibold uppercase tracking-wide">SLA Overdue</p>
            <strong class="block text-2xl font-extrabold text-[#183b68] leading-tight" style="font-family:'Plus Jakarta Sans'">{{ $overdueSla }}</strong>
            <span class="text-[10px] text-[#e6636a] font-bold">{{ $highRiskCases }} risiko tinggi</span>
        </div>
    </div>
</div>

{{-- ======================== SECONDARY STAT ROW ======================== --}}
<div class="grid grid-cols-3 gap-4 mb-5">
    <div class="bg-gradient-to-br from-[#1675e8] to-[#0d54c4] rounded-xl p-5 text-white flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl bg-white/20 flex items-center justify-center flex-none">
            <i data-lucide="building-2" class="w-5 h-5"></i>
        </div>
        <div>
            <p class="text-[10px] font-semibold uppercase tracking-wide text-blue-100">Total Sekolah</p>
            <strong class="block text-2xl font-extrabold leading-tight" style="font-family:'Plus Jakarta Sans'">{{ $totalSchools }}</strong>
            <span class="text-[10px] text-blue-200">Terintegrasi via API</span>
        </div>
    </div>
    <div class="bg-white border border-[#e8eef5] rounded-xl p-5 flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl bg-[#f3e8ff] text-[#9333ea] flex items-center justify-center flex-none">
            <i data-lucide="briefcase-business" class="w-5 h-5"></i>
        </div>
        <div>
            <p class="text-[10px] text-[#73869e] font-semibold uppercase tracking-wide">Kasus Aktif</p>
            <strong class="block text-2xl font-extrabold text-[#183b68] leading-tight" style="font-family:'Plus Jakarta Sans'">{{ $totalCases }}</strong>
            <span class="text-[10px] text-[#9333ea] font-bold">{{ $inHandlingCases }} dalam penanganan</span>
        </div>
    </div>
    <div class="bg-white border border-[#e8eef5] rounded-xl p-5 flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl bg-[#fff0d9] text-[#e69b27] flex items-center justify-center flex-none">
            <i data-lucide="triangle-alert" class="w-5 h-5"></i>
        </div>
        <div>
            <p class="text-[10px] text-[#73869e] font-semibold uppercase tracking-wide">Risiko Tinggi</p>
            <strong class="block text-2xl font-extrabold text-[#183b68] leading-tight" style="font-family:'Plus Jakarta Sans'">{{ $highRiskCases }}</strong>
            <span class="text-[10px] text-[#e69b27] font-bold">High & Critical</span>
        </div>
    </div>
</div>

{{-- ======================== CHARTS ROW ======================== --}}
<div class="grid grid-cols-5 gap-4 mb-5">

    {{-- Monthly Bar Chart (3/5) --}}
    <div class="col-span-3 bg-white border border-[#e8eef5] rounded-xl p-5">
        <div class="flex items-start justify-between mb-1">
            <div>
                <h3 class="font-bold text-[14px] text-[#193d6c]" style="font-family:'Plus Jakarta Sans'">Pengaduan per Bulan</h3>
                <p class="text-[10px] text-[#8494a9] mt-1">12 bulan terakhir</p>
            </div>
            <div class="flex items-center gap-2 text-[10px] text-[#73869e]">
                <span class="w-3 h-3 rounded-sm bg-[#1675e8] inline-block"></span> Kasus baru
            </div>
        </div>
        <div id="chart-monthly" class="mt-2" style="min-height:220px"></div>
    </div>

    {{-- SLA Donut (2/5) --}}
    <div class="col-span-2 bg-white border border-[#e8eef5] rounded-xl p-5">
        <div class="mb-1">
            <h3 class="font-bold text-[14px] text-[#193d6c]" style="font-family:'Plus Jakarta Sans'">Distribusi SLA</h3>
            <p class="text-[10px] text-[#8494a9] mt-1">Status kepatuhan waktu</p>
        </div>
        <div id="chart-sla" style="min-height:200px"></div>
    </div>
</div>

{{-- ======================== RECENT USERS + STATUS CHART ======================== --}}
<div class="grid grid-cols-3 gap-4">

    {{-- Recent Users Table (2/3) --}}
    <div class="col-span-2 bg-white border border-[#e8eef5] rounded-xl overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-[#edf2f7]">
            <div>
                <h3 class="font-bold text-[14px] text-[#193d6c]" style="font-family:'Plus Jakarta Sans'">Pengguna Terbaru</h3>
                <p class="text-[10px] text-[#8494a9] mt-0.5">5 pengguna terakhir ditambahkan</p>
            </div>
            <a href="{{ route('admin.users') }}" class="text-[10px] text-[#1675e8] flex items-center gap-1 hover:underline">
                Lihat semua <i data-lucide="arrow-right" class="w-3 h-3"></i>
            </a>
        </div>
        <table class="w-full text-[11px]">
            <thead>
                <tr class="bg-[#f8fafc]">
                    <th class="text-left text-[9px] font-medium text-[#9aa9ba] px-5 py-3">PENGGUNA</th>
                    <th class="text-left text-[9px] font-medium text-[#9aa9ba] px-3 py-3">ROLE</th>
                    <th class="text-left text-[9px] font-medium text-[#9aa9ba] px-3 py-3">SEKOLAH</th>
                    <th class="text-left text-[9px] font-medium text-[#9aa9ba] px-3 py-3">BERGABUNG</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentUsers as $user)
                <tr class="border-t border-[#edf2f7] hover:bg-[#fafcff] transition-colors">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-7 h-7 rounded-full bg-[#dbeafe] text-[#1d4ed8] flex items-center justify-center text-[10px] font-bold flex-none">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>
                            <div>
                                <strong class="block text-[#1d4c7e]">{{ $user->name }}</strong>
                                <small class="text-[#a1adbb] text-[9px]">{{ $user->email }}</small>
                            </div>
                        </div>
                    </td>
                    <td class="px-3 py-3">
                        @php
                            $roleColors = ['ADMIN' => 'bg-purple-100 text-purple-700', 'PRINCIPAL' => 'bg-blue-100 text-blue-700', 'COUNSELOR' => 'bg-emerald-100 text-emerald-700'];
                            $roleLabels = ['ADMIN' => 'Admin', 'PRINCIPAL' => 'Kepsek', 'COUNSELOR' => 'Guru BK'];
                        @endphp
                        <span class="px-2 py-0.5 rounded text-[9px] font-semibold {{ $roleColors[$user->role] ?? 'bg-gray-100 text-gray-600' }}">
                            {{ $roleLabels[$user->role] ?? $user->role }}
                        </span>
                    </td>
                    <td class="px-3 py-3 text-[#5f7890] max-w-[160px] truncate">
                        {{ $user->school_name !== '-' ? $user->school_name : '<em class="text-[#a1adbb]">Platform</em>' }}
                    </td>
                    <td class="px-3 py-3 text-[#9aaaba] text-[9px] whitespace-nowrap">
                        {{ \Carbon\Carbon::parse($user->created_at)->locale('id')->isoFormat('D MMM Y') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Cases by Status Donut (1/3) --}}
    <div class="bg-white border border-[#e8eef5] rounded-xl p-5">
        <div class="mb-1">
            <h3 class="font-bold text-[14px] text-[#193d6c]" style="font-family:'Plus Jakarta Sans'">Status Kasus</h3>
            <p class="text-[10px] text-[#8494a9] mt-1">Distribusi berdasarkan status</p>
        </div>
        <div id="chart-case-status" style="min-height:200px"></div>
    </div>
</div>

{{-- ======================== APEXCHARTS ======================== --}}
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.54.0/dist/apexcharts.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ---- Monthly Bar Chart ----
    @php
        $months = $casesByMonth->pluck('month_label')->toArray();
        $monthlyCounts = $casesByMonth->pluck('total')->toArray();
    @endphp

    const monthlyLabels = @json($months);
    const monthlyCounts = @json($monthlyCounts);

    // Fill in zeros for months with no data (show all 12 months)
    @php
        $allMonths = [];
        $allCounts = [];
        $indexedByMonth = [];
        foreach ($casesByMonth as $row) {
            $indexedByMonth[(int)$row->month_num] = ['label' => $row->month_label, 'total' => (int)$row->total];
        }
        for ($i = 11; $i >= 0; $i--) {
            $monthNum = (int)\Carbon\Carbon::now()->subMonths($i)->format('n');
            $label = \Carbon\Carbon::now()->subMonths($i)->locale('en')->isoFormat('MMM');
            $allMonths[] = $label;
            $allCounts[] = $indexedByMonth[$monthNum]['total'] ?? 0;
        }
    @endphp

    new ApexCharts(document.getElementById('chart-monthly'), {
        series: [{ name: 'Kasus Baru', data: @json($allCounts) }],
        chart: { type: 'bar', height: 220, toolbar: { show: false }, fontFamily: 'DM Sans, sans-serif' },
        colors: ['#1675e8'],
        plotOptions: { bar: { borderRadius: 5, columnWidth: '55%' } },
        dataLabels: { enabled: false },
        xaxis: { categories: @json($allMonths), labels: { style: { colors: '#9aa9ba', fontSize: '10px' } }, axisBorder: { show: false }, axisTicks: { show: false } },
        yaxis: { labels: { style: { colors: '#9aa9ba', fontSize: '10px' } }, tickAmount: 4 },
        grid: { borderColor: '#edf2f7', strokeDashArray: 4, yaxis: { lines: { show: true } }, xaxis: { lines: { show: false } } },
        tooltip: { style: { fontSize: '11px' } },
    }).render();

    // ---- SLA Donut ----
    @php
        $slaLabels = ['ON_TRACK' => 'On Track', 'ON_TIME' => 'Tepat Waktu', 'WARNING' => 'Peringatan', 'OVERDUE' => 'Overdue', 'COMPLETED' => 'Selesai'];
        $slaColors = ['ON_TRACK' => '#22c55e', 'ON_TIME' => '#3b82f6', 'WARNING' => '#f59e0b', 'OVERDUE' => '#ef4444', 'COMPLETED' => '#8b5cf6'];
        $slaSeriesData = [];
        $slaSeriesLabels = [];
        $slaSeriesColors = [];
        foreach ($slaStats as $key => $stat) {
            $slaSeriesData[] = (int) $stat->total;
            $slaSeriesLabels[] = $slaLabels[$key] ?? $key;
            $slaSeriesColors[] = $slaColors[$key] ?? '#9aa9ba';
        }
        if (empty($slaSeriesData)) {
            $slaSeriesData = [1];
            $slaSeriesLabels = ['Tidak ada data'];
            $slaSeriesColors = ['#e8eef5'];
        }
    @endphp

    new ApexCharts(document.getElementById('chart-sla'), {
        series: @json($slaSeriesData),
        chart: { type: 'donut', height: 200, fontFamily: 'DM Sans, sans-serif' },
        labels: @json($slaSeriesLabels),
        colors: @json($slaSeriesColors),
        dataLabels: { enabled: false },
        legend: { position: 'bottom', fontSize: '10px', labels: { colors: '#73869e' }, itemMargin: { horizontal: 6 } },
        stroke: { width: 0 },
        plotOptions: { pie: { donut: { size: '65%', labels: { show: true, total: { show: true, label: 'Total SLA', fontSize: '10px', color: '#73869e', formatter: (w) => w.globals.seriesTotals.reduce((a, b) => a + b, 0) } } } } },
        tooltip: { style: { fontSize: '11px' } },
    }).render();

    // ---- Cases by Status Donut ----
    @php
        $statusLabels = ['PENDING_RESPONSE' => 'Pending', 'UNDER_VERIFICATION' => 'Verifikasi', 'IN_HANDLING' => 'Ditangani', 'RESOLVED' => 'Selesai', 'CLOSED' => 'Ditutup'];
        $statusColors = ['PENDING_RESPONSE' => '#f59e0b', 'UNDER_VERIFICATION' => '#3b82f6', 'IN_HANDLING' => '#f97316', 'RESOLVED' => '#22c55e', 'CLOSED' => '#6b7280'];
        $statusSeriesData = [];
        $statusSeriesLabels = [];
        $statusSeriesColors = [];
        foreach ($casesByStatus as $key => $stat) {
            $statusSeriesData[] = (int) $stat->total;
            $statusSeriesLabels[] = $statusLabels[$key] ?? $key;
            $statusSeriesColors[] = $statusColors[$key] ?? '#9aa9ba';
        }
        if (empty($statusSeriesData)) {
            $statusSeriesData = [1];
            $statusSeriesLabels = ['Tidak ada data'];
            $statusSeriesColors = ['#e8eef5'];
        }
    @endphp

    new ApexCharts(document.getElementById('chart-case-status'), {
        series: @json($statusSeriesData),
        chart: { type: 'donut', height: 200, fontFamily: 'DM Sans, sans-serif' },
        labels: @json($statusSeriesLabels),
        colors: @json($statusSeriesColors),
        dataLabels: { enabled: false },
        legend: { position: 'bottom', fontSize: '10px', labels: { colors: '#73869e' }, itemMargin: { horizontal: 6 } },
        stroke: { width: 0 },
        plotOptions: { pie: { donut: { size: '65%', labels: { show: true, total: { show: true, label: 'Total Kasus', fontSize: '10px', color: '#73869e', formatter: (w) => w.globals.seriesTotals.reduce((a, b) => a + b, 0) } } } } },
        tooltip: { style: { fontSize: '11px' } },
    }).render();
});
</script>
@endsection
