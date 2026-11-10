@extends('emails.layout', ['title' => 'Ringkasan Kasus Harian - Sahabat Sekolah'])

@section('content')
    <h2 style="font-size: 20px; color: #12386a; margin: 0 0 10px; font-weight: 800;">
        Selamat Pagi, {{ $recipientName }}
    </h2>
    <p style="font-size: 15px; color: #526b84; line-height: 1.6; margin: 0 0 18px;">
        Berikut adalah ikhtisar harian status penanganan laporan perundungan untuk <strong>{{ $digestData['school_name'] }}</strong> per tanggal <strong>{{ $digestData['date'] }}</strong>:
    </p>

    {{-- Ringkasan Angka --}}
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin: 16px 0 24px;">
        <tr>
            <td width="32%" style="background: #fef2f2; border: 1px solid #fee2e2; border-radius: 8px; padding: 14px; text-align: center;">
                <span style="font-size: 26px; font-weight: 800; color: #dc2626; display: block;">{{ count($digestData['overdue_cases']) }}</span>
                <span style="font-size: 13px; font-weight: 600; color: #991b1b; text-transform: uppercase;">SLA Terlewati</span>
            </td>
            <td width="2%"></td>
            <td width="32%" style="background: #fffbeb; border: 1px solid #fef3c7; border-radius: 8px; padding: 14px; text-align: center;">
                <span style="font-size: 26px; font-weight: 800; color: #d97706; display: block;">{{ count($digestData['pending_cases']) }}</span>
                <span style="font-size: 13px; font-weight: 600; color: #92400e; text-transform: uppercase;">Menunggu Respons</span>
            </td>
            <td width="2%"></td>
            <td width="32%" style="background: #eff6ff; border: 1px solid #dbeafe; border-radius: 8px; padding: 14px; text-align: center;">
                <span style="font-size: 26px; font-weight: 800; color: #2563eb; display: block;">{{ $digestData['in_handling_count'] }}</span>
                <span style="font-size: 13px; font-weight: 600; color: #1e40af; text-transform: uppercase;">Dalam Penanganan</span>
            </td>
        </tr>
    </table>

    {{-- Daftar Kasus Butuh Tindakan --}}
    @if (!empty($digestData['overdue_cases']))
        <div style="margin: 20px 0;">
            <h3 style="font-size: 16px; color: #b91c1c; margin: 0 0 10px; font-weight: 700; display: flex; align-items: center; gap: 6px;">
                ⚠️ Kasus Melewati Batas Waktu (SLA Overdue)
            </h3>
            <table width="100%" cellpadding="8" cellspacing="0" style="font-size: 14px; border: 1px solid #fecaca; border-radius: 6px; border-collapse: collapse;">
                <tr style="background: #fef2f2; text-align: left; font-weight: 600; color: #991b1b;">
                    <th style="border-bottom: 1px solid #fecaca; padding: 8px;">No. Kasus</th>
                    <th style="border-bottom: 1px solid #fecaca; padding: 8px;">Kategori</th>
                    <th style="border-bottom: 1px solid #fecaca; padding: 8px;">Risiko</th>
                    <th style="border-bottom: 1px solid #fecaca; padding: 8px;">Penanggung Jawab</th>
                </tr>
                @foreach ($digestData['overdue_cases'] as $case)
                    <tr style="border-bottom: 1px solid #fecaca;">
                        <td style="padding: 8px; font-weight: 700;">{{ $case['case_number'] }}</td>
                        <td style="padding: 8px;">{{ $case['category'] }}</td>
                        <td style="padding: 8px;"><span class="badge badge-danger">{{ $case['risk_level'] }}</span></td>
                        <td style="padding: 8px;">{{ $case['assigned_to'] ?? '-' }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    @endif

    @if (!empty($digestData['pending_cases']))
        <div style="margin: 20px 0;">
            <h3 style="font-size: 16px; color: #d97706; margin: 0 0 10px; font-weight: 700;">
                ⏳ Kasus Baru Menunggu Respons Awal
            </h3>
            <table width="100%" cellpadding="8" cellspacing="0" style="font-size: 14px; border: 1px solid #fde68a; border-radius: 6px; border-collapse: collapse;">
                <tr style="background: #fffbeb; text-align: left; font-weight: 600; color: #92400e;">
                    <th style="border-bottom: 1px solid #fde68a; padding: 8px;">No. Kasus</th>
                    <th style="border-bottom: 1px solid #fde68a; padding: 8px;">Kategori</th>
                    <th style="border-bottom: 1px solid #fde68a; padding: 8px;">Risiko</th>
                    <th style="border-bottom: 1px solid #fde68a; padding: 8px;">Batas Respons</th>
                </tr>
                @foreach ($digestData['pending_cases'] as $case)
                    <tr style="border-bottom: 1px solid #fde68a;">
                        <td style="padding: 8px; font-weight: 700;">{{ $case['case_number'] }}</td>
                        <td style="padding: 8px;">{{ $case['category'] }}</td>
                        <td style="padding: 8px;"><span class="badge {{ $case['risk_level'] === 'HIGH' || $case['risk_level'] === 'CRITICAL' ? 'badge-danger' : 'badge-warning' }}">{{ $case['risk_level'] }}</span></td>
                        <td style="padding: 8px;">{{ !empty($case['response_deadline']) ? \Carbon\Carbon::parse($case['response_deadline'])->format('d M H:i') : '-' }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    @endif

    @if (empty($digestData['overdue_cases']) && empty($digestData['pending_cases']))
        <div class="alert-box alert-info">
            <strong>✅ Luar Biasa!</strong><br>
            Tidak ada laporan tertunda atau melewati batas waktu SLA di sekolah Anda hari ini. Terima kasih atas komitmen menjaga lingkungan sekolah yang aman dan suportif bagi anak-anak Kota Bukittinggi.
        </div>
    @endif

    <div style="text-align: center; margin: 28px 0 20px;">
        <a href="{{ $digestData['dashboard_url'] }}" class="btn-primary" style="font-size: 16px; padding: 13px 32px;">
            Buka Dashboard Sahabat Sekolah &rarr;
        </a>
    </div>
@endsection
