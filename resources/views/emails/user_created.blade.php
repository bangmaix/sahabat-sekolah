@extends('emails.layout', ['title' => 'Informasi Akun Baru - Sahabat Sekolah'])

@section('content')
    <h2 style="font-size: 20px; color: #12386a; margin: 0 0 10px; font-weight: 800;">
        Halo, {{ $recipientName }}
    </h2>
    <p style="font-size: 15px; color: #526b84; line-height: 1.6; margin: 0 0 18px;">
        Akun Anda telah didaftarkan oleh Administrator ke dalam platform <strong>Sahabat Sekolah</strong> Kota Bukittinggi. Berikut adalah rincian informasi akun dan kredensial akses Anda:
    </p>

    <div class="info-card">
        <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
            <tr class="info-row">
                <td class="info-label">Nama Lengkap</td>
                <td class="info-value">{{ $userData['name'] }}</td>
            </tr>
            <tr class="info-row">
                <td class="info-label">Alamat Email</td>
                <td class="info-value">
                    <span style="font-family: monospace; font-size: 15px; color: #1e3a58;">
                        {{ $userData['email'] }}
                    </span>
                </td>
            </tr>
            <tr class="info-row">
                <td class="info-label">Peran (Role)</td>
                <td class="info-value">
                    @php
                        $roleLabels = [
                            'ADMIN' => 'Administrator',
                            'CITY_ADMIN' => 'Dinas Pendidikan',
                            'PRINCIPAL' => 'Kepala Sekolah',
                            'COUNSELOR' => 'Guru BK',
                        ];
                        $roleBadgeClasses = [
                            'ADMIN' => 'badge-info',
                            'CITY_ADMIN' => 'badge-primary',
                            'PRINCIPAL' => 'badge-warning',
                            'COUNSELOR' => 'badge-success',
                        ];
                        $userRole = strtoupper($userData['role'] ?? '');
                    @endphp
                    <span class="badge {{ $roleBadgeClasses[$userRole] ?? 'badge-info' }}">
                        {{ $roleLabels[$userRole] ?? $userRole }}
                    </span>
                </td>
            </tr>
            <tr class="info-row">
                <td class="info-label">Instansi / Sekolah</td>
                <td class="info-value">
                    {{ !empty($userData['school_name']) && $userData['school_name'] !== '-' ? $userData['school_name'] : 'Dinas / Pusat Kota Bukittinggi' }}
                </td>
            </tr>
        </table>
    </div>

    {{-- Highlighted Temporary Password Card --}}
    <div style="background: linear-gradient(135deg, #0f274a 0%, #153e75 100%); border-radius: 10px; padding: 22px 24px; margin: 24px 0; text-align: center; color: #ffffff; box-shadow: 0 4px 14px rgba(15, 39, 74, 0.12);">
        <p style="font-size: 13px; text-transform: uppercase; letter-spacing: 0.12em; color: #93c5fd; margin: 0 0 8px; font-weight: 700;">
            KATA SANDI SEMENTARA (PASSWORD)
        </p>
        <div style="display: inline-block; background: rgba(255, 255, 255, 0.12); border: 1px dashed rgba(255, 255, 255, 0.35); border-radius: 8px; padding: 10px 24px; margin: 6px 0;">
            <span style="font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, Courier, monospace; font-size: 24px; font-weight: 800; letter-spacing: 4px; color: #ffffff;">
                {{ $userData['password'] }}
            </span>
        </div>
        <p style="font-size: 13px; color: #bfdbfe; margin: 10px 0 0; line-height: 1.4;">
            Kata sandi acak ini dibuat khusus untuk akses pertama Anda.
        </p>
    </div>

    <div style="text-align: center; margin: 28px 0 20px;">
        <a href="{{ $userData['login_url'] }}" class="btn-primary" style="font-size: 16px; padding: 13px 32px; letter-spacing: 0.02em;">
            Masuk ke Sahabat Sekolah &rarr;
        </a>
    </div>

    <div class="alert-box alert-warning">
        <strong>🔒 Perhatian Keamanan Akun:</strong><br>
        Demi menjaga keamanan akun dan privasi data siswa, <strong>segera perbarui kata sandi Anda</strong> setelah berhasil masuk ke sistem melalui menu pengaturan profil atau tautan Lupa Kata Sandi. Jangan pernah membagikan kata sandi ini kepada siapa pun.
    </div>

    <p style="font-size: 13px; color: #8fa0b3; line-height: 1.5; margin: 22px 0 0; word-break: break-all;">
        Jika tombol di atas tidak dapat diklik, salin dan tempel tautan berikut ke peramban (browser) Anda:<br>
        <a href="{{ $userData['login_url'] }}" style="color: #1878e3; text-decoration: underline;">{{ $userData['login_url'] }}</a>
    </p>
@endsection
