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
            <p class="text-[12px] text-[#73869e] font-semibold uppercase tracking-wide">Total Pengguna</p>
            <strong class="block text-2xl font-extrabold text-[#183b68] leading-tight" style="font-family:'Plus Jakarta Sans'">{{ $totalUsers }}</strong>
            <span class="text-[12px] text-[#73869e]">Terdaftar di platform</span>
        </div>
    </div>

    {{-- Total Laporan --}}
    <div class="bg-white border border-[#e8eef5] rounded-xl p-5 flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl bg-[#fff0d9] text-[#e69b27] flex items-center justify-center flex-none">
            <i data-lucide="file-text" class="w-5 h-5"></i>
        </div>
        <div>
            <p class="text-[12px] text-[#73869e] font-semibold uppercase tracking-wide">Total Pengaduan</p>
            <strong class="block text-2xl font-extrabold text-[#183b68] leading-tight" style="font-family:'Plus Jakarta Sans'">{{ $totalReports }}</strong>
            <span class="text-[12px] text-[#e69b27] font-bold">{{ $inHandlingCases }} sedang ditangani</span>
        </div>
    </div>

    {{-- Resolved --}}
    <div class="bg-white border border-[#e8eef5] rounded-xl p-5 flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl bg-[#e3fff4] text-[#24906a] flex items-center justify-center flex-none">
            <i data-lucide="circle-check" class="w-5 h-5"></i>
        </div>
        <div>
            <p class="text-[12px] text-[#73869e] font-semibold uppercase tracking-wide">Kasus Selesai</p>
            <strong class="block text-2xl font-extrabold text-[#183b68] leading-tight" style="font-family:'Plus Jakarta Sans'">{{ $resolvedCases }}</strong>
            @php $resolveRate = $totalCases > 0 ? round(($resolvedCases / $totalCases) * 100) : 0; @endphp
            <span class="text-[12px] text-[#24906a] font-bold">{{ $resolveRate }}% tingkat resolusi</span>
        </div>
    </div>

    {{-- SLA Overdue --}}
    <div class="bg-white border border-[#e8eef5] rounded-xl p-5 flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl bg-[#ffe2e3] text-[#e6636a] flex items-center justify-center flex-none">
            <i data-lucide="clock-alert" class="w-5 h-5"></i>
        </div>
        <div>
            <p class="text-[12px] text-[#73869e] font-semibold uppercase tracking-wide">SLA Overdue</p>
            <strong class="block text-2xl font-extrabold text-[#183b68] leading-tight" style="font-family:'Plus Jakarta Sans'">{{ $overdueSla }}</strong>
            <span class="text-[12px] text-[#e6636a] font-bold">{{ $highRiskCases }} risiko tinggi</span>
        </div>
    </div>
</div>

{{-- ======================== SECONDARY STAT ROW ======================== --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
    <div class="bg-gradient-to-br from-[#1675e8] to-[#0d54c4] rounded-xl p-5 text-white flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl bg-white/20 flex items-center justify-center flex-none">
            <i data-lucide="building-2" class="w-5 h-5"></i>
        </div>
        <div>
            <p class="text-[12px] font-semibold uppercase tracking-wide text-blue-100">Total Sekolah</p>
            <strong class="block text-2xl font-extrabold leading-tight" style="font-family:'Plus Jakarta Sans'">{{ $totalSchools }}</strong>
            <span class="text-[12px] text-blue-200">Terintegrasi via API</span>
        </div>
    </div>
    <div class="bg-white border border-[#e8eef5] rounded-xl p-5 flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl bg-[#f3e8ff] text-[#9333ea] flex items-center justify-center flex-none">
            <i data-lucide="briefcase-business" class="w-5 h-5"></i>
        </div>
        <div>
            <p class="text-[12px] text-[#73869e] font-semibold uppercase tracking-wide">Kasus Aktif</p>
            <strong class="block text-2xl font-extrabold text-[#183b68] leading-tight" style="font-family:'Plus Jakarta Sans'">{{ $totalCases }}</strong>
            <span class="text-[12px] text-[#9333ea] font-bold">{{ $inHandlingCases }} dalam penanganan</span>
        </div>
    </div>
    <div class="bg-white border border-[#e8eef5] rounded-xl p-5 flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl bg-[#fff0d9] text-[#e69b27] flex items-center justify-center flex-none">
            <i data-lucide="triangle-alert" class="w-5 h-5"></i>
        </div>
        <div>
            <p class="text-[12px] text-[#73869e] font-semibold uppercase tracking-wide">Risiko Tinggi</p>
            <strong class="block text-2xl font-extrabold text-[#183b68] leading-tight" style="font-family:'Plus Jakarta Sans'">{{ $highRiskCases }}</strong>
            <span class="text-[12px] text-[#e69b27] font-bold">High & Critical</span>
        </div>
    </div>
    <a href="{{ route('achievements.index') }}" class="bg-white border border-[#e8eef5] rounded-xl p-5 flex items-center gap-4 hover:border-amber-300 hover:shadow-sm transition-all text-inherit">
        <div class="w-11 h-11 rounded-xl bg-[#fef3c7] text-[#b45309] flex items-center justify-center flex-none">
            <i data-lucide="trophy" class="w-5 h-5"></i>
        </div>
        <div>
            <p class="text-[12px] text-[#73869e] font-semibold uppercase tracking-wide">Prestasi Siswa</p>
            <strong class="block text-2xl font-extrabold text-[#183b68] leading-tight" style="font-family:'Plus Jakarta Sans'">{{ $totalAchievements }}</strong>
            <span class="text-[12px] text-[#b45309] font-bold">{{ $totalStudentsWithAchievements }} siswa terdata</span>
        </div>
    </a>
</div>

{{-- ======================== CHARTS ROW ======================== --}}
<div class="grid grid-cols-1 lg:grid-cols-5 gap-4 mb-5">

    {{-- Monthly Bar Chart (3/5) --}}
    <div class="lg:col-span-3 bg-white border border-[#e8eef5] rounded-xl p-5 flex flex-col justify-between">
        <div class="flex items-start justify-between mb-1">
            <div>
                <h3 class="font-bold text-[16px] text-[#193d6c]" style="font-family:'Plus Jakarta Sans'">Pengaduan per Bulan</h3>
                <p class="text-[12px] text-[#8494a9] mt-1">12 bulan terakhir</p>
            </div>
            <div class="flex items-center gap-2 text-[12px] text-[#73869e]">
                <span class="w-3 h-3 rounded-sm bg-[#1675e8] inline-block"></span> Kasus baru
            </div>
        </div>
        <div id="chart-monthly" class="mt-2" style="min-height:220px"></div>
    </div>

    {{-- SLA Donut (2/5) --}}
    <div class="lg:col-span-2 bg-white border border-[#e8eef5] rounded-xl p-5 flex flex-col justify-between">
        <div class="mb-1">
            <h3 class="font-bold text-[16px] text-[#193d6c]" style="font-family:'Plus Jakarta Sans'">Distribusi SLA</h3>
            <p class="text-[12px] text-[#8494a9] mt-1">Status kepatuhan waktu</p>
        </div>
        @if(($slaChartData['total'] ?? 0) > 0)
            <div id="chart-sla" style="min-height:220px"></div>
        @else
            <div class="flex flex-col items-center justify-center py-10 text-[#8494a9] my-auto">
                <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400 mb-2 border border-gray-100">
                    <i data-lucide="pie-chart" class="w-5 h-5"></i>
                </div>
                <p class="text-[13px] font-medium text-[#73869e]">Belum ada data SLA</p>
                <span class="text-[12px] text-[#9aa9ba]">Data kepatuhan akan muncul setelah laporan diproses</span>
            </div>
        @endif
    </div>
</div>

{{-- ======================== RECENT USERS + STATUS CHART ======================== --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-5">

    {{-- Recent Users Table (2/3) --}}
    <div class="lg:col-span-2 bg-white border border-[#e8eef5] rounded-xl overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-[#edf2f7]">
            <div>
                <h3 class="font-bold text-[16px] text-[#193d6c]" style="font-family:'Plus Jakarta Sans'">Pengguna Terbaru</h3>
                <p class="text-[12px] text-[#8494a9] mt-0.5">5 pengguna terakhir ditambahkan</p>
            </div>
            <a href="{{ route('admin.users') }}" class="text-[12px] text-[#1675e8] flex items-center gap-1 hover:underline">
                Lihat semua <i data-lucide="arrow-right" class="w-3 h-3"></i>
            </a>
        </div>
        <table class="w-full text-[13px]">
            <thead>
                <tr class="bg-[#f8fafc]">
                    <th class="text-left text-[11px] font-medium text-[#9aa9ba] px-5 py-3">PENGGUNA</th>
                    <th class="text-left text-[11px] font-medium text-[#9aa9ba] px-3 py-3">ROLE</th>
                    <th class="text-left text-[11px] font-medium text-[#9aa9ba] px-3 py-3">SEKOLAH</th>
                    <th class="text-left text-[11px] font-medium text-[#9aa9ba] px-3 py-3">BERGABUNG</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentUsers as $user)
                <tr class="border-t border-[#edf2f7] hover:bg-[#fafcff] transition-colors">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-7 h-7 rounded-full bg-[#dbeafe] text-[#1d4ed8] flex items-center justify-center text-[12px] font-bold flex-none">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>
                            <div>
                                <strong class="block text-[#1d4c7e]">{{ $user->name }}</strong>
                                <small class="text-[#a1adbb] text-[11px]">{{ $user->email }}</small>
                            </div>
                        </div>
                    </td>
                    <td class="px-3 py-3">
                        @php
                            $roleColors = ['ADMIN' => 'bg-purple-100 text-purple-700', 'PRINCIPAL' => 'bg-blue-100 text-blue-700', 'COUNSELOR' => 'bg-emerald-100 text-emerald-700'];
                            $roleLabels = ['ADMIN' => 'Admin', 'PRINCIPAL' => 'Kepsek', 'COUNSELOR' => 'Guru BK'];
                        @endphp
                        <span class="px-2 py-0.5 rounded text-[11px] font-semibold {{ $roleColors[$user->role] ?? 'bg-gray-100 text-gray-600' }}">
                            {{ $roleLabels[$user->role] ?? $user->role }}
                        </span>
                    </td>
                    <td class="px-3 py-3 text-[#5f7890] max-w-[160px] truncate">
                        {{ $user->school_name !== '-' ? $user->school_name : '<em class="text-[#a1adbb]">Platform</em>' }}
                    </td>
                    <td class="px-3 py-3 text-[#9aaaba] text-[11px] whitespace-nowrap">
                        {{ \Carbon\Carbon::parse($user->created_at)->locale('id')->isoFormat('D MMM Y') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Cases by Status Donut (1/3) --}}
    <div class="bg-white border border-[#e8eef5] rounded-xl p-5 flex flex-col justify-between">
        <div class="mb-1">
            <h3 class="font-bold text-[16px] text-[#193d6c]" style="font-family:'Plus Jakarta Sans'">Status Kasus</h3>
            <p class="text-[12px] text-[#8494a9] mt-1">Distribusi berdasarkan status</p>
        </div>
        @if(($statusChartData['total'] ?? 0) > 0)
            <div id="chart-case-status" style="min-height:220px"></div>
        @else
            <div class="flex flex-col items-center justify-center py-10 text-[#8494a9] my-auto">
                <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400 mb-2 border border-gray-100">
                    <i data-lucide="pie-chart" class="w-5 h-5"></i>
                </div>
                <p class="text-[13px] font-medium text-[#73869e]">Belum ada data status</p>
                <span class="text-[12px] text-[#9aa9ba]">Data status akan muncul seiring penanganan kasus</span>
            </div>
        @endif
    </div>
</div>

{{-- ======================== RECENT ACHIEVEMENTS TABLE ======================== --}}
<div class="bg-white border border-[#e8eef5] rounded-xl p-5 mb-5">
    <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-[#fef3c7] text-[#b45309] flex items-center justify-center flex-none">
                <i data-lucide="trophy" class="w-4 h-4"></i>
            </div>
            <div>
                <h3 class="font-bold text-[16px] text-[#193d6c]" style="font-family:'Plus Jakarta Sans'">Prestasi Siswa Terbaru (Lintas Sekolah)</h3>
                <p class="text-[12px] text-[#8494a9] mt-0.5">Capaian prestasi siswa se-Kota Bukittinggi yang baru dicatat</p>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.master-data') }}" class="text-[12px] font-semibold text-[#73869e] hover:text-[#1675e8] flex items-center gap-1">
                <i data-lucide="settings" class="w-3 h-3"></i> Kelola Kategori
            </a>
            <a href="{{ route('achievements.index') }}" class="text-[13px] font-bold text-[#1675e8] flex items-center gap-1 hover:underline">
                Lihat Semua Prestasi ({{ $totalAchievements }}) <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>
    </div>

    @if ($recentAchievements->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="w-full text-[13px]">
                <thead>
                    <tr class="bg-[#f8fafc]">
                        <th class="text-left text-[11px] font-medium text-[#9aa9ba] px-4 py-3">SISWA</th>
                        <th class="text-left text-[11px] font-medium text-[#9aa9ba] px-3 py-3">SEKOLAH</th>
                        <th class="text-left text-[11px] font-medium text-[#9aa9ba] px-3 py-3">PRESTASI & JUARA</th>
                        <th class="text-left text-[11px] font-medium text-[#9aa9ba] px-3 py-3">KATEGORI</th>
                        <th class="text-left text-[11px] font-medium text-[#9aa9ba] px-3 py-3">TINGKAT</th>
                        <th class="text-left text-[11px] font-medium text-[#9aa9ba] px-3 py-3">TANGGAL</th>
                        <th class="text-right text-[11px] font-medium text-[#9aa9ba] px-4 py-3">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentAchievements as $ach)
                        @php
                            $badgeClass = match ($ach->achievement_level) {
                                'INTERNASIONAL' => 'bg-purple-100 text-purple-700 border-purple-200',
                                'NASIONAL' => 'bg-rose-100 text-rose-700 border-rose-200',
                                'PROVINSI' => 'bg-blue-100 text-blue-700 border-blue-200',
                                'KOTA' => 'bg-amber-100 text-amber-800 border-amber-200',
                                default => 'bg-slate-100 text-slate-700 border-slate-200',
                            };
                        @endphp
                        <tr class="border-t border-[#edf2f7] hover:bg-[#fafcff] transition-colors">
                            <td class="px-4 py-3">
                                <strong class="block text-[#1d4c7e]">{{ $ach->student_name }}</strong>
                                <small class="text-[#a1adbb] text-[11px]">Kelas {{ $ach->class_name ?? '-' }} &bull; NISN: {{ $ach->nisn }}</small>
                            </td>
                            <td class="px-3 py-3 text-[#5f7890] font-medium max-w-[170px] truncate" title="{{ $ach->school_name }}">
                                {{ $ach->school_name }}
                            </td>
                            <td class="px-3 py-3">
                                <strong class="block text-[#1675e8]">{{ $ach->ranking }}</strong>
                                <span class="text-[#475569] text-[12px]">{{ $ach->achievement_name }}</span>
                            </td>
                            <td class="px-3 py-3">
                                <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 text-[11px] font-semibold">
                                    {{ $ach->category?->name ?? 'Prestasi' }}
                                </span>
                            </td>
                            <td class="px-3 py-3">
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold border {{ $badgeClass }}">
                                    {{ $ach->achievement_level }}
                                </span>
                            </td>
                            <td class="px-3 py-3 text-[#9aaaba] text-[11px] whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($ach->achievement_date)->locale('id')->isoFormat('D MMM Y') }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('achievements.student', $ach->nisn) }}" class="inline-flex items-center gap-1 text-[12px] font-semibold text-[#1675e8] hover:underline bg-[#eff6ff] px-2.5 py-1 rounded-md">
                                    Portofolio <i data-lucide="chevron-right" class="w-3 h-3"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-8 text-[#8494a9]">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center mx-auto mb-2">
                <i data-lucide="trophy" class="w-5 h-5"></i>
            </div>
            <strong class="block text-[15px] text-[#193d6c] mb-1">Belum Ada Prestasi Siswa Terdata</strong>
            <p class="text-[13px] text-[#8494a9] max-w-sm mx-auto">Prestasi yang dicatat oleh masing-masing sekolah akan tampil secara terpusat di sini.</p>
        </div>
    @endif
</div>

{{-- ======================== APEXCHARTS ======================== --}}
<script src="{{ asset('js/apexcharts.min.js') }}"></script>
<script>
    if (typeof ApexCharts === 'undefined') {
        document.write('<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.54.0/dist/apexcharts.min.js"><\/script>');
    }
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof ApexCharts === 'undefined') {
        console.error('ApexCharts library could not be loaded.');
        return;
    }

    // ---- 1. Monthly Bar Chart ----
    (function () {
        const el = document.getElementById('chart-monthly');
        if (!el) return;

        const categories = @json($monthlyLabels ?? []);
        const dataSeries = @json($monthlyCounts ?? []);

        try {
            const chart = new ApexCharts(el, {
                series: [{ name: 'Kasus Baru', data: dataSeries }],
                chart: {
                    type: 'bar',
                    height: 220,
                    toolbar: { show: false },
                    fontFamily: 'DM Sans, sans-serif'
                },
                colors: ['#1675e8'],
                plotOptions: {
                    bar: {
                        borderRadius: 4,
                        columnWidth: '50%',
                        distributed: false
                    }
                },
                dataLabels: { enabled: false },
                xaxis: {
                    categories: categories,
                    labels: {
                        style: { colors: '#9aa9ba', fontSize: '10px' },
                        rotate: -45,
                        rotateAlways: false
                    },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    labels: {
                        style: { colors: '#9aa9ba', fontSize: '10px' },
                        formatter: function (val) { return Math.round(val); }
                    },
                    tickAmount: 4,
                    min: 0,
                    forceNiceScale: true
                },
                grid: {
                    borderColor: '#edf2f7',
                    strokeDashArray: 4,
                    yaxis: { lines: { show: true } },
                    xaxis: { lines: { show: false } }
                },
                tooltip: {
                    theme: 'light',
                    style: { fontSize: '11px' },
                    y: {
                        formatter: function (val) {
                            return val + ' pengaduan';
                        }
                    }
                }
            });
            chart.render();
        } catch (err) {
            console.error('Failed to initialize Monthly Chart:', err);
        }
    })();

    // ---- 2. SLA Donut Chart ----
    (function () {
        const el = document.getElementById('chart-sla');
        if (!el) return;

        const slaSeries = @json($slaChartData['series'] ?? []);
        const slaLabels = @json($slaChartData['labels'] ?? []);
        const slaColors = @json($slaChartData['colors'] ?? []);
        const slaTotal = {{ (int) ($slaChartData['total'] ?? 0) }};

        if (!slaSeries.length || slaTotal <= 0) return;

        try {
            const chart = new ApexCharts(el, {
                series: slaSeries,
                chart: {
                    type: 'donut',
                    height: 220,
                    fontFamily: 'DM Sans, sans-serif'
                },
                labels: slaLabels,
                colors: slaColors,
                dataLabels: { enabled: false },
                legend: {
                    position: 'bottom',
                    fontSize: '10px',
                    labels: { colors: '#73869e' },
                    itemMargin: { horizontal: 6, vertical: 3 }
                },
                stroke: { width: 2, colors: ['#ffffff'] },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '68%',
                            labels: {
                                show: true,
                                name: {
                                    show: true,
                                    fontSize: '11px',
                                    color: '#73869e'
                                },
                                value: {
                                    show: true,
                                    fontSize: '18px',
                                    fontWeight: '700',
                                    color: '#183b68',
                                    formatter: function (val) { return val; }
                                },
                                total: {
                                    show: true,
                                    label: 'Total SLA',
                                    fontSize: '10px',
                                    color: '#73869e',
                                    formatter: function (w) {
                                        return w.globals.seriesTotals.reduce(function (a, b) { return a + b; }, 0);
                                    }
                                }
                            }
                        }
                    }
                },
                tooltip: {
                    style: { fontSize: '11px' }
                }
            });
            chart.render();
        } catch (err) {
            console.error('Failed to initialize SLA Chart:', err);
        }
    })();

    // ---- 3. Cases by Status Donut Chart ----
    (function () {
        const el = document.getElementById('chart-case-status');
        if (!el) return;

        const statusSeries = @json($statusChartData['series'] ?? []);
        const statusLabels = @json($statusChartData['labels'] ?? []);
        const statusColors = @json($statusChartData['colors'] ?? []);
        const statusTotal = {{ (int) ($statusChartData['total'] ?? 0) }};

        if (!statusSeries.length || statusTotal <= 0) return;

        try {
            const chart = new ApexCharts(el, {
                series: statusSeries,
                chart: {
                    type: 'donut',
                    height: 220,
                    fontFamily: 'DM Sans, sans-serif'
                },
                labels: statusLabels,
                colors: statusColors,
                dataLabels: { enabled: false },
                legend: {
                    position: 'bottom',
                    fontSize: '10px',
                    labels: { colors: '#73869e' },
                    itemMargin: { horizontal: 6, vertical: 3 }
                },
                stroke: { width: 2, colors: ['#ffffff'] },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '68%',
                            labels: {
                                show: true,
                                name: {
                                    show: true,
                                    fontSize: '11px',
                                    color: '#73869e'
                                },
                                value: {
                                    show: true,
                                    fontSize: '18px',
                                    fontWeight: '700',
                                    color: '#183b68',
                                    formatter: function (val) { return val; }
                                },
                                total: {
                                    show: true,
                                    label: 'Total Kasus',
                                    fontSize: '10px',
                                    color: '#73869e',
                                    formatter: function (w) {
                                        return w.globals.seriesTotals.reduce(function (a, b) { return a + b; }, 0);
                                    }
                                }
                            }
                        }
                    }
                },
                tooltip: {
                    style: { fontSize: '11px' }
                }
            });
            chart.render();
        } catch (err) {
            console.error('Failed to initialize Case Status Chart:', err);
        }
    })();
});
</script>
@endsection
