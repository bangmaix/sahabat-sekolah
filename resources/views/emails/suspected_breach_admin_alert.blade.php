@extends('emails.layout', ['title' => 'Peringatan Darurat: Dugaan Peretasan Akun'])

@section('content')
    <div style="background-color: #fee2e2; border-left: 4px solid #ef4444; padding: 14px 18px; margin-bottom: 20px; border-radius: 6px;">
        <strong style="color: #991b1b; font-size: 15px;">🚨 PERINGATAN INSIDEN KEAMANAN (URGENT)</strong>
        <p style="margin: 4px 0 0; color: #b91c1c; font-size: 14px; line-height: 1.5;">
            Pengguna telah melaporkan perubahan kata sandi tidak sah. Akun telah <strong>dinonaktifkan secara otomatis</strong> oleh sistem untuk mencegah peretasan lebih lanjut.
        </p>
    </div>

    <h2 style="font-size: 19px; color: #12386a; margin: 0 0 10px; font-weight: 800;">
        Halo, {{ $recipientName }}
    </h2>
    <p style="font-size: 15px; color: #526b84; line-height: 1.6; margin: 0 0 18px;">
        Sistem <strong>Sahabat Sekolah</strong> mendeteksi bahwa pengguna berikut mengonfirmasi bahwa mereka <strong>tidak melakukan perubahan kata sandi</strong> yang baru saja terjadi. Akses login akun ini telah dinonaktifkan secara langsung.
    </p>

    <div class="info-card">
        <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
            <tr class="info-row">
                <td class="info-label">Nama Pengguna</td>
                <td class="info-value"><strong>{{ $incidentData['victim_name'] }}</strong></td>
            </tr>
            <tr class="info-row">
                <td class="info-label">Alamat Email</td>
                <td class="info-value">
                    <span style="font-family: monospace; font-size: 15px; color: #1e3a58;">
                        {{ $incidentData['victim_email'] }}
                    </span>
                </td>
            </tr>
            <tr class="info-row">
                <td class="info-label">Peran (Role)</td>
                <td class="info-value"><span class="badge badge-danger">{{ $incidentData['victim_role'] }}</span></td>
            </tr>
            <tr class="info-row">
                <td class="info-label">Sekolah</td>
                <td class="info-value">{{ $incidentData['victim_school'] ?? 'Dinas / Pusat Kota Bukittinggi' }}</td>
            </tr>
            <tr class="info-row">
                <td class="info-label">Status Akun Sekarang</td>
                <td class="info-value"><span class="badge badge-danger">NONAKTIF (INACTIVE)</span></td>
            </tr>
            <tr class="info-row">
                <td class="info-label">Waktu Laporan</td>
                <td class="info-value">{{ $incidentData['reported_at'] }} WIB</td>
            </tr>
            <tr class="info-row">
                <td class="info-label">IP Pelapor</td>
                <td class="info-value">{{ $incidentData['ip_address'] }}</td>
            </tr>
        </table>
    </div>

    <div style="text-align: center; margin: 26px 0 20px;">
        <a href="{{ $incidentData['admin_users_url'] }}" class="btn-primary" style="background-color: #1878e3; font-size: 15px; padding: 12px 26px;">
            Buka Manajemen Pengguna &rarr;
        </a>
    </div>

    <div class="alert-box alert-warning">
        <strong>📋 Rekomendasi Tindakan Administrator:</strong>
        <ol style="margin: 8px 0 0; padding-left: 18px; font-size: 14px; line-height: 1.6;">
            <li>Hubungi pengguna secara langsung melalui saluran resmi sekolah/telepon untuk memverifikasi identitas pemilik sah.</li>
            <li>Periksa catatan log audit aktivitas terakhir untuk memastikan tidak ada data laporan siswa yang diubah atau disalahgunakan.</li>
            <li>Gunakan fitur <strong>Reset Password</strong> di menu Manajemen Pengguna untuk menerbitkan kredensial acak baru setelah diverifikasi.</li>
        </ol>
    </div>
@endsection
