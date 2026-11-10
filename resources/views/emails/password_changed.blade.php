@extends('emails.layout', ['title' => 'Peringatan Keamanan: Kata Sandi Diubah - Sahabat Sekolah'])

@section('content')
    <h2 style="font-size: 20px; color: #12386a; margin: 0 0 10px; font-weight: 800;">
        Halo, {{ $recipientName }}
    </h2>
    <p style="font-size: 15px; color: #526b84; line-height: 1.6; margin: 0 0 18px;">
        Kami memberitahukan bahwa kata sandi untuk akun <strong>Sahabat Sekolah</strong> Anda ({{ $changeData['email'] }}) baru saja berhasil diubah.
    </p>

    <div class="info-card">
        <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
            <tr class="info-row">
                <td class="info-label">Alamat Email</td>
                <td class="info-value">
                    <span style="font-family: monospace; font-size: 15px; color: #1e3a58;">
                        {{ $changeData['email'] }}
                    </span>
                </td>
            </tr>
            <tr class="info-row">
                <td class="info-label">Waktu Perubahan</td>
                <td class="info-value">{{ $changeData['changed_at'] }} WIB</td>
            </tr>
            <tr class="info-row">
                <td class="info-label">Alamat IP</td>
                <td class="info-value">{{ $changeData['ip_address'] }}</td>
            </tr>
            @if(!empty($changeData['user_agent']))
            <tr class="info-row">
                <td class="info-label">Perangkat / Browser</td>
                <td class="info-value" style="font-size: 13px; color: #64748b; word-break: break-all;">
                    {{ $changeData['user_agent'] }}
                </td>
            </tr>
            @endif
        </table>
    </div>

    <div class="alert-box alert-info">
        <strong>ℹ️ Jika Anda yang melakukan perubahan ini:</strong><br>
        Anda dapat mengabaikan email ini. Akun Anda telah aman terlindungi dengan kata sandi baru.
    </div>

    {{-- Emergency Security Box --}}
    <div style="background-color: #fff1f2; border: 2px solid #fecdd3; border-radius: 10px; padding: 22px 24px; margin: 26px 0; text-align: center;">
        <div style="font-size: 15px; font-weight: 800; color: #be123c; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.05em;">
            ⚠️ Bukan Anda yang Melakukan Perubahan Ini?
        </div>
        <p style="font-size: 14px; color: #9f1239; line-height: 1.6; margin: 0 0 18px;">
            Jika Anda <strong>tidak pernah mengubah kata sandi Anda</strong>, ada kemungkinan akun Anda telah disusupi atau diretas oleh pihak lain.
            Segera klik tombol di bawah ini. Sistem akan <strong>secara otomatis menonaktifkan akun Anda</strong> demi mencegah penyalahgunaan data serta mengirimkan laporan darurat kepada Administrator.
        </p>

        <a href="{{ $changeData['unauthorized_url'] }}" style="display: inline-block; background-color: #dc2626; color: #ffffff !important; font-size: 15px; font-weight: 700; text-decoration: none; padding: 13px 28px; border-radius: 8px; box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25);">
            🚨 Amankan Akun Saya (Bukan Saya)
        </a>
    </div>

    <p style="font-size: 13px; color: #8fa0b3; line-height: 1.5; margin: 22px 0 0; word-break: break-all;">
        Jika tombol di atas tidak dapat diklik, salin tautan pengamanan akun berikut ke browser Anda:<br>
        <a href="{{ $changeData['unauthorized_url'] }}" style="color: #dc2626; text-decoration: underline;">{{ $changeData['unauthorized_url'] }}</a>
    </p>
@endsection
