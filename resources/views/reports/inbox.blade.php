@extends('layouts.app', [
    'title' => 'Inbox Laporan | Sahabat Sekolah',
    'activeNav' => 'inbox',
    'eyebrow' => 'CASE MANAGEMENT',
    'pageTitle' => 'Inbox Laporan Masuk',
    'pageSubtitle' => 'Kelola laporan dalam lingkup sekolah Anda secara responsif dan rahasia.'
])

@section('content')
<div class="inbox-heading-bar">
    <div>
        <strong class="inbox-count-text">{{ $cases->count() }} laporan ditemukan</strong>
        <span class="inbox-subtext">Diurutkan berdasarkan pembaruan terbaru</span>
    </div>
    <a class="primary-button inbox-new-link" href="{{ route('reports.create') }}">
        <i data-lucide="plus"></i> Buat laporan baru
    </a>
</div>

<form class="inbox-filters" method="GET">
    <div class="search-field">
        <i data-lucide="search"></i>
        <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Cari nomor, kategori, atau kronologi...">
    </div>
    <select name="status">
        <option value="">Kasus aktif</option>
        <option value="ALL" @selected(($filters['status'] ?? '') === 'ALL')>Semua status</option>
        <option value="PENDING_RESPONSE" @selected(($filters['status'] ?? '') === 'PENDING_RESPONSE')>Menunggu respons</option>
        <option value="UNDER_VERIFICATION" @selected(($filters['status'] ?? '') === 'UNDER_VERIFICATION')>Verifikasi</option>
        <option value="IN_HANDLING" @selected(($filters['status'] ?? '') === 'IN_HANDLING')>Penanganan</option>
        <option value="RESOLVED" @selected(($filters['status'] ?? '') === 'RESOLVED')>Selesai</option>
        <option value="CLOSED" @selected(($filters['status'] ?? '') === 'CLOSED')>Ditutup</option>
    </select>
    <select name="risk_level">
        <option value="">Semua risiko</option>
        @foreach (['LOW','MEDIUM','HIGH','CRITICAL'] as $risk)
            <option value="{{ $risk }}" @selected(($filters['risk_level'] ?? '') === $risk)>{{ $risk }}</option>
        @endforeach
    </select>
    <select name="sla_status">
        <option value="">Semua SLA</option>
        @foreach (['ON_TIME','WARNING','OVERDUE','COMPLETED'] as $sla)
            <option value="{{ $sla }}" @selected(($filters['sla_status'] ?? '') === $sla)>{{ $sla }}</option>
        @endforeach
    </select>
    <button class="filter-button" type="submit">
        <i data-lucide="filter"></i> Terapkan
    </button>
</form>

<section class="panel inbox-panel">
    <div class="inbox-table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Nomor kasus</th>
                    <th>Kategori</th>
                    <th>Pelapor</th>
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
                            <small>{{ \Illuminate\Support\Carbon::parse($case->updated_at)->diffForHumans() }}</small>
                        </td>
                        <td><span class="category-tag">{{ $case->category }}</span></td>
                        <td>{{ $case->identity_mode === 'ANONYMOUS' ? 'Anonim' : $case->reporter_role }}</td>
                        <td><span class="status {{ strtolower($case->status) }}">{{ str_replace('_', ' ', $case->status) }}</span></td>
                        <td><span class="priority {{ strtolower($case->risk_level) }}">{{ $case->risk_level }}</span></td>
                        <td>
                            @php
                                $displaySla = 'ON_TIME';
                                if ($case->status === 'PENDING_RESPONSE') {
                                    $displaySla = $case->sla_status ?: 'ON_TIME';
                                    if ($displaySla !== 'OVERDUE' && $case->response_deadline && \Illuminate\Support\Carbon::parse($case->response_deadline)->isPast()) {
                                        $displaySla = 'OVERDUE';
                                    }
                                } elseif ($case->sla_status === 'OVERDUE') {
                                    // If initial response was overdue, SLA must remain OVERDUE
                                    $displaySla = 'OVERDUE';
                                } elseif ($case->resolution_status === 'OVERDUE' || ($case->resolution_deadline && \Illuminate\Support\Carbon::parse($case->resolution_deadline)->isPast() && in_array($case->status, ['UNDER_VERIFICATION', 'IN_HANDLING']))) {
                                    $displaySla = 'OVERDUE';
                                } elseif (in_array($case->status, ['RESOLVED', 'CLOSED'])) {
                                    $displaySla = 'COMPLETED';
                                } else {
                                    $displaySla = in_array($case->resolution_status, ['WARNING', 'OVERDUE']) ? $case->resolution_status : ($case->sla_status ?: 'ON_TIME');
                                }
                            @endphp
                            <span class="sla-{{ strtolower($displaySla) }}">{{ $displaySla }}</span>
                        </td>
                        <td>
                            <a class="view-button" href="{{ route('cases.show', $case->case_number) }}">
                                Buka Kasus <i data-lucide="arrow-right"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="empty-table">Tidak ada laporan yang sesuai dengan filter pencarian.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
