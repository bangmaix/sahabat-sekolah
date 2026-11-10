@extends('emails.layout', ['title' => 'Eskalasi Kasus oleh Guru BK - Sahabat Sekolah'])

@section('content')
    <h2 style="font-size: 20px; color: #12386a; margin: 0 0 10px; font-weight: 800;">
        Yth. Bapak/Ibu {{ $recipientName }}
    </h2>
    <p style="font-size: 15px; color: #526b84; line-height: 1.6; margin: 0 0 18px;">
        Guru Bimbingan dan Konseling telah melakukan <strong>eskalasi kasus dugaan perundungan</strong> di <strong>{{ $caseData['school_name'] }}</strong> kepada Kepala Sekolah untuk mendapatkan atensi dan arahan pimpinan.
    </p>

    <div class="alert-box alert-warning">
        <strong style="color: #b45309; font-size: 15px;">🚨 Alasan Eskalasi oleh Guru BK ({{ $escalationData['escalated_by'] }}):</strong>
        <p style="margin: 6px 0 0; color: #78350f; font-size: 15px; font-style: italic; line-height: 1.5;">
            "{{ $escalationData['reason'] }}"
        </p>
        <small style="display: block; margin-top: 6px; color: #92400e; font-size: 12px;">
            Waktu Eskalasi: {{ \Illuminate\Support\Carbon::parse($escalationData['escalated_at'])->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
        </small>
    </div>

    <div class="info-card">
        <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
            <tr class="info-row">
                <td class="info-label">Nomor Kasus</td>
                <td class="info-value">
                    <span style="font-family: monospace; font-size: 16px; color: #1878e3; background: #eaf3fd; padding: 2px 7px; border-radius: 4px;">
                        {{ $caseData['case_number'] }}
                    </span>
                </td>
            </tr>
            <tr class="info-row">
                <td class="info-label">Sekolah</td>
                <td class="info-value">{{ $caseData['school_name'] }} (NPSN: {{ $caseData['school_npsn'] }})</td>
            </tr>
            <tr class="info-row">
                <td class="info-label">Kategori Kasus</td>
                <td class="info-value">
                    <strong>{{ $caseData['category'] }}</strong>
                    @if(!empty($caseData['subcategory']))
                        <span style="color: #64748b; font-weight: normal;"> - {{ $caseData['subcategory'] }}</span>
                    @endif
                </td>
            </tr>
            <tr class="info-row">
                <td class="info-label">Tingkat Risiko</td>
                <td class="info-value">
                    @php
                        $risk = strtoupper($caseData['risk_level'] ?? 'MEDIUM');
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
                <td class="info-label">Status Kasus</td>
                <td class="info-value">
                    <span style="font-weight: 700; color: #2563eb;">
                        {{ str_replace('_', ' ', $caseData['status'] ?? 'IN_HANDLING') }}
                    </span>
                </td>
            </tr>
        </table>
    </div>

    @if(!empty($caseData['description']))
        <div style="margin: 22px 0 10px;">
            <span style="font-size: 13px; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; color: #64748b;">
                Ringkasan Laporan:
            </span>
            <div class="quote-box">
                "{{ \Illuminate\Support\Str::limit($caseData['description'], 250) }}"
            </div>
        </div>
    @endif

    <div style="text-align: center; margin: 30px 0 16px;">
        <a href="{{ $caseData['case_url'] }}" class="btn-primary" target="_blank">
            Lihat Kasus di Dashboard Kepala Sekolah
        </a>
    </div>

    <p style="font-size: 13px; color: #8295a8; text-align: center; margin: 0;">
        Atau buka link: <a href="{{ $caseData['case_url'] }}" style="color: #1878e3; word-break: break-all;">{{ $caseData['case_url'] }}</a>
    </p>
@endsection
