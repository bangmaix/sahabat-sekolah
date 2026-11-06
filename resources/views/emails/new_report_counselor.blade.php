@extends('emails.layout', ['title' => 'Laporan Baru Masuk - Sahabat Sekolah'])

@section('content')
    <h2 style="font-size: 20px; color: #12386a; margin: 0 0 10px; font-weight: 800;">
        Halo, {{ $recipientName }}
    </h2>
    <p style="font-size: 15px; color: #526b84; line-height: 1.6; margin: 0 0 18px;">
        Terdapat laporan baru dugaan perundungan yang masuk untuk <strong>{{ $reportData['school_name'] }}</strong>. Sesuai Standar Layanan (SLA), laporan ini memerlukan tindakan atau respons awal maksimal dalam <strong>1 × 24 jam</strong>.
    </p>

    <div class="alert-box alert-warning">
        <strong>⚠️ Perhatian SLA Respons Awal:</strong><br>
        Segera tinjau laporan ini melalui dashboard Guru BK sebelum batas waktu SLA terlampaui.
    </div>

    <div class="info-card">
        <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
            <tr class="info-row">
                <td class="info-label">Nomor Laporan</td>
                <td class="info-value">
                    <span style="font-family: monospace; font-size: 16px; color: #1878e3; background: #eaf3fd; padding: 2px 7px; border-radius: 4px;">
                        {{ $reportData['case_number'] }}
                    </span>
                </td>
            </tr>
            <tr class="info-row">
                <td class="info-label">Sekolah</td>
                <td class="info-value">{{ $reportData['school_name'] }} (NPSN: {{ $reportData['school_npsn'] }})</td>
            </tr>
            <tr class="info-row">
                <td class="info-label">Kategori</td>
                <td class="info-value">
                    <strong>{{ $reportData['category'] }}</strong>
                    @if(!empty($reportData['subcategory']))
                        <span style="color: #64748b; font-weight: normal;"> - {{ $reportData['subcategory'] }}</span>
                    @endif
                </td>
            </tr>
            <tr class="info-row">
                <td class="info-label">Tingkat Risiko</td>
                <td class="info-value">
                    @php
                        $risk = strtoupper($reportData['risk_level'] ?? 'MEDIUM');
                        $badgeClass = match($risk) {
                            'CRITICAL', 'HIGH' => 'badge-danger',
                            'MEDIUM' => 'badge-warning',
                            default => 'badge-info',
                        };
                    @endphp
                    <span class="badge {{ $badgeClass }}">{{ $risk }}</span>
                </td>
            </tr>
            <tr class="info-row">
                <td class="info-label">Mode Identitas</td>
                <td class="info-value">
                    @php
                        $mode = $reportData['identity_mode'] ?? 'CONFIDENTIAL';
                        $modeText = match($mode) {
                            'ANONYMOUS' => 'Anonim (Rahasia Penuh)',
                            'CONFIDENTIAL' => 'Rahasia (Hanya BK)',
                            default => 'Terbuka (Identified)',
                        };
                    @endphp
                    {{ $modeText }}
                </td>
            </tr>
            <tr class="info-row">
                <td class="info-label">Waktu Masuk</td>
                <td class="info-value">
                    {{ \Illuminate\Support\Carbon::parse($reportData['submitted_at'])->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
                </td>
            </tr>
        </table>
    </div>

    <div style="margin: 22px 0 10px;">
        <span style="font-size: 13px; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; color: #64748b;">
            Ringkasan Cerita Kejadian:
        </span>
        <div class="quote-box">
            "{{ \Illuminate\Support\Str::limit($reportData['description'], 280) }}"
        </div>
    </div>

    @php
        $caseUrl = $reportData['case_url'] ?? (isset($reportData['case_number']) ? route('cases.show', $reportData['case_number']) : '#');
    @endphp

    <div style="text-align: center; margin: 30px 0 16px;">
        <a href="{{ $caseUrl }}" class="btn-primary" target="_blank">
            Buka & Tindak Lanjuti Laporan
        </a>
    </div>

    <p style="font-size: 13px; color: #8295a8; text-align: center; margin: 0;">
        Atau buka link: <a href="{{ $caseUrl }}" style="color: #1878e3; word-break: break-all;">{{ $caseUrl }}</a>
    </p>
@endsection
