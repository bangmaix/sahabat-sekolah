@extends('emails.layout', ['title' => 'Atur Ulang Kata Sandi - Sahabat Sekolah'])

@section('content')
    <h2 style="font-size: 20px; color: #12386a; margin: 0 0 10px; font-weight: 800;">
        Halo, {{ $recipientName }}
    </h2>
    <p style="font-size: 15px; color: #526b84; line-height: 1.6; margin: 0 0 18px;">
        Kami menerima permintaan untuk mengatur ulang kata sandi (reset password) akun Anda pada platform <strong>Sahabat Sekolah</strong>.
    </p>

    <div style="text-align: center; margin: 28px 0;">
        <a href="{{ $resetData['reset_url'] }}" class="btn-primary" style="font-size: 16px; padding: 13px 32px; letter-spacing: 0.02em;">
            Atur Ulang Kata Sandi
        </a>
    </div>

    <div class="alert-box alert-warning">
        <strong>⏱️ Batas Waktu Tautan:</strong><br>
        Tautan pengaturan ulang kata sandi ini hanya berlaku selama <strong>{{ $resetData['expires_minutes'] ?? 60 }} menit</strong> sejak email ini dikirimkan.
    </div>

    <div class="info-card">
        <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
            <tr class="info-row">
                <td class="info-label">Alamat Email</td>
                <td class="info-value">{{ $resetData['email'] }}</td>
            </tr>
            <tr class="info-row">
                <td class="info-label">Waktu Permintaan</td>
                <td class="info-value">{{ now()->translatedFormat('d F Y, H:i') }} WIB</td>
            </tr>
        </table>
    </div>

    <div class="alert-box alert-info">
        <strong>🔒 Pemberitahuan Keamanan:</strong><br>
        Jika Anda tidak pernah meminta pengaturan ulang kata sandi, abaikan email ini. Kata sandi akun Anda tidak akan berubah dan akun Anda tetap aman.
    </div>

    <p style="font-size: 13px; color: #8fa0b3; line-height: 1.5; margin: 22px 0 0; word-break: break-all;">
        Jika tombol di atas tidak berfungsi, salin dan tempel tautan berikut ke peramban (browser) Anda:<br>
        <a href="{{ $resetData['reset_url'] }}" style="color: #1878e3; text-decoration: underline;">{{ $resetData['reset_url'] }}</a>
    </p>
@endsection
