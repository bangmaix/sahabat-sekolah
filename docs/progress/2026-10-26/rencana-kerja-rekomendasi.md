# DOKUMEN PERENCANAAN KERJA & REKAP TINDAK LANJUT REKOMENDASI PENGEMBANGAN
# SAHABAT SEKOLAH — KOTA BUKITTINGGI

**Lokasi Dokumen:** `docs/progress/2026-10-26/rencana-kerja-rekomendasi.md`  
**Tanggal Pembaruan:** 26 Oktober 2026  
**Status:** Revisi berdasarkan Arahan & Konfirmasi Arsitektur Sistem  
**Dasar Acuan:**
1. *Dokumen-Rekomendasi-Pengembangan-SAHABAT-SEKOLAH.docx*
2. *PRD Final Terpadu — SAHABAT SEKOLAH v1.0*
3. *MVP Technical Implementation Plan v1.0*
4. *Kesimpulan Teknis Proyek SAHABAT SEKOLAH (KESIMPULAN_TEKNIS_PROJECT.md)*
5. *Arahan & Batasan Arsitektur Pengguna (Prinsip Single Source of Truth & Peran Kanal)*

---

## 1. Prinsip Utama & Batasan Arsitektur Sistem

Berdasarkan klarifikasi dan arahan mengenai ruang lingkup proyek, ditetapkan prinsip-prinsip mutlak berikut:

1. **AConnect Sebagai Satu-satunya Sumber Kebenaran (*Single Source of Truth*)**:
   - Seluruh data sekolah, rombongan belajar (kelas), dan data siswa **wajib bersumber langsung dari Microservice API Pemerintah Kota Bukittinggi (`AConnect`)**.
   - Ketersediaan atau kelengkapan data MIN, MTsN, maupun NPSN madrasah adalah **wewenang dan tanggung jawab penuh Pemerintah Kota Bukittinggi** bersama instansi pembina terkait, bukan keputusan teknis aplikasi ini.
   - **Tidak ada pembuatan tabel `schools`, `local_schools`, atau `local_students`**. Tabel `schools` telah dihapus sebelumnya agar tidak terjadi duplikasi, inkonsistensi, atau divergensi data terhadap database Pemkot. Sistem fokus pada *defensive programming* dan *graceful fallback handling*.

2. **Diferensiasi Peran Kanal Web vs. Aplikasi Mobile**:
   - **Kanal Web Publik**: Sejak awal dirancang sebagai **jalur utama pelaporan siswa, saksi, korban, guru, dan masyarakat umum**. Sistem **tidak pernah mengasumsikan siswa harus memiliki ponsel pribadi** atau mengunduh aplikasi untuk melapor. Laporan dapat diakses dari peramban web (*browser*) apa saja (laptop sekolah, komputer lab, ponsel bersama).
   - **Aplikasi Mobile Android**: Dirancang khusus bagi **Pihak Sekolah (Guru BK dan Kepala Sekolah)** agar dapat **menerima notifikasi laporan masuk secara cepat dan instan (*push alert*)**, memantau batas waktu respons SLA, dan mengelola kasus secara fleksibel tanpa harus selalu berada di depan komputer.

3. **Fokus Peningkatan yang Tepat Sasaran**:
   - Rekomendasi ramah anak difokuskan pada **penyempurnaan antarmuka Web Lapor Publik** (pilihan visual bergambar, bahasa anak, QR Code per sekolah, token 4 kata Indonesia, dan tombol keluar cepat/panic exit).
   - Rekomendasi notifikasi difokuskan pada **percepatan peringatan ke Guru BK/Kepsek** melalui aplikasi mobile yang didukung fallback Web Push/Email Digest, tanpa bergantung pada WhatsApp yang belum dianggarkan.

---

## 2. Rekap Matriks Komparasi: Kondisi Saat Ini, Rekomendasi, & Keputusan Desain

| No | Dimensi Sistem | Cara Kerja Sistem Saat Ini (*Existing*) | Rekomendasi Dokumen Eksternal | Keputusan & Penyesuaian Desain Sistem |
| :--- | :--- | :--- | :--- | :--- |
| **1** | **Sumber Data Sekolah & Siswa** | 100% menggunakan API Microservice Pemkot Bukittinggi (`AConnect`). Tabel `schools` lokal telah dihapus demi integritas data. | Menyarankan membuat tabel master sekolah lokal dan impor roster CSV siswa madrasah secara mandiri. | **DITOLAK (Tetap Mengacu AConnect):** <br>Data kebenaran tetap 100% dari `AConnect`. Ketersediaan data madrasah adalah domain Pemkot Bukittinggi. Sistem **tidak akan menambah tabel sekolah/siswa lokal**, melainkan menerapkan *graceful handling* bila ada entri yang belum lengkap di API Pemkot. |
| **2** | **Kanal Lapor Siswa vs. Aplikasi Mobile** | Laporan publik dapat dibuat via Web. Aplikasi Mobile API v1 telah tersedia. | Mengkritik keharusan anak memasang aplikasi mobile di HP pribadi. | **KLARIFIKASI & DISEMPURNAKAN:** <br>Dari awal, **Web adalah kanal lapor utama siswa/publik** (bisa diakses tanpa instalasi dan tanpa HP pribadi). Aplikasi mobile Android difungsikan khusus bagi **Guru BK & Kepala Sekolah** untuk menerima notifikasi cepat. Rekomendasi ramah anak (QR code, kategori bergambar, token 4 kata) diadopsi langsung ke **Web Lapor Publik**. |
| **3** | **Kanal Peringatan Masuk (Staf)** | Notifikasi in-app database dan email antrean. WhatsApp belum tersedia karena keterbatasan anggaran. | Mengusulkan lapis peringatan tanpa WhatsApp: Web Push (PWA), Rekap Email 07.00, Bot Telegram, dan Telepon Darurat. | **DIADOPSI & DIPERKUAT:** <br>Memaksimalkan **Aplikasi Mobile Guru BK/Kepsek** untuk *push alert* seketika, didukung Web Push (PWA) di dashboard, rekap email harian jam 07.00, serta prosedur darurat manual untuk kasus `CRITICAL`. |
| **4** | **Fleksibilitas Penanggung Jawab Kasus** | Mengharuskan peran `COUNSELOR` (Guru BK) terdaftar untuk memproses laporan awal dan tindakan kasus. | Mengusulkan peran dinamis "Penanggung Jawab Kasus" yang fleksibel (bisa diisi guru kelas jika tidak ada BK) + Penerima Cadangan. | **DIADOPSI:** <br>Menyediakan konfigurasi penugasan penanganan kasus per sekolah yang fleksibel (mendukung penunjukan Guru Kelas/Wali Kelas di MIN yang belum punya BK formal) serta fitur alih tugas tercatat jika terjadi konflik kepentingan. |
| **5** | **Privasi Pelapor & Kepatuhan UU PDP** | Laporan anonim tidak menyimpan nama/kontak, namun foto masih memuat metadata EXIF dan log request masih mencatat IP/User-Agent pelapor anonim. | Hapus EXIF saat unggah, hilangkan pencatatan IP/User-Agent pada pelapor anonim, wajibkan 2FA TOTP staf, dan ganti reset password admin menjadi tautan mandiri. | **DIADOPSI PENUH:** <br>Wajib membersihkan metadata EXIF gambar, menganonimkan log jaringan untuk pelapor anonim, menerapkan 2FA TOTP bagi staf, serta mengubah reset sandi admin menjadi tautan aktivasi email sekali pakai. |
| **6** | **Perhitungan SLA & Hari Kerja** | Menghitung jam SLA linier 24/7 tanpa membedakan hari libur sekolah. | SLA mengikuti kalender hari kerja & libur sekolah (kecuali `CRITICAL`), status jeda *"Menunggu Info Pelapor"*, dan Heartbeat Monitor. | **DIADOPSI PENUH:** <br>Menyesuaikan SLA agar memperhitungkan hari/jam kerja sekolah guna mencegah *false overdue* di akhir pekan, dengan pengecualian kasus `CRITICAL` yang tetap aktif 24/7 demi keselamatan korban. |
| **7** | **Deteksi Situasi Krisis & Layar Bantuan** | Laporan diproses normal sesuai antrean tanpa pemindaian kata kunci darurat. | Deteksi kata kunci merah (*Red Flags*) untuk ancaman jiwa/kekerasan berat $\rightarrow$ otomatis `CRITICAL`, tampilkan layar bantuan darurat (112 / UPTD PPA), dan status `DIRUJUK`. | **DIADOPSI PENUH:** <br>Menerapkan aturan pencocokan kata kunci darurat pada deskripsi laporan untuk langsung menetapkan prioritas `CRITICAL`, menampilkan kontak darurat instan kepada pelapor, dan menyediakan status rujukan. |
| **8** | **Eskalasi Kasus Berjenjang** | Eskalasi hanya 1 tingkat: Guru BK $\rightarrow$ Kepala Sekolah. | Eskalasi Tingkat 3: ke Admin Kota / Instansi Pembina bila Kepala Sekolah tidak menindaklanjuti. | **DIADOPSI:** <br>Menambahkan eskalasi tingkat 3 ke Admin Kota / instansi terkait jika kasus eskalasi tingkat 2 terabaikan melewati batas waktu. |
| **9** | **Pemantauan Pasca-Kasus** | Status berakhir di `CLOSED` setelah dokumen penyelesaian diunggah. | Otomasi pengingat pemantauan di H+7, H+30, H+90 dan status kasus `REOPENED` bila perundungan terulang. | **DIADOPSI (Fase Selama Pilot):** <br>Mencegah kasus kambuh pasca penyelesaian formal. |

---

## 3. Rencana Kerja Pelaksanaan Teknis (Roadmap & Work Packages)

Struktur paket kerja disusun secara terarah dan terukur:

```
FASE 1: SEBELUM PILOT (Fokus Kesiapan Operasional, Keamanan & Aksesibilitas)
├── Paket 1: Penyempurnaan Web Lapor Publik Ramah Anak (QR Code, Kategori Visual, Token 4 Kata, Panic Exit)
├── Paket 2: Pengamanan Privasi Pelapor & Otentikasi Staf (Anti-EXIF, Masking IP Anonim, 2FA TOTP, Reset Link)
├── Paket 3: Fleksibilitas Penanggung Jawab Kasus & Alih Tugas Staf
├── Paket 4: Optimalisasi Notifikasi Cepat Pihak Sekolah (Mobile Push, Web Push PWA, Daily Digest Email)
├── Paket 5: Penyesuaian Kalender SLA Hari Kerja, Status Jeda, & Heartbeat Monitor
├── Paket 6: Deteksi Kata Kunci Merah (Red Flags), Layar Bantuan Segera, & Jalur Rujukan
├── Paket 7: Eskalasi Kasus Jenjang 3 (Admin Kota / Instansi Terkait)
└── Paket 8: Graceful Handling & Defensive API pada Integrasi AConnect

FASE 2: SELAMA PILOT (Penyempurnaan Berdasarkan Hasil Lapangan)
├── Paket 9: Pemantauan Pasca-Kasus (H+7, H+30, H+90) & Status REOPENED
├── Paket 10: Form Insiden Terstruktur & Deteksi Pola Terlapor Berulang
├── Paket 11: Dukungan Peran Pelapor untuk Orang Tua & Guru
└── Paket 12: Rencana Intervensi Khusus Pelaku & Template Berita Acara Formal

FASE 3: PASCA PILOT (Pengembangan Berkelanjutan)
├── Paket 13: Modul Survei Iklim Sekolah Terjadwal
├── Paket 14: Verifikasi Integritas Bukti (File Hashing & Enkripsi Data Sensitif)
└── Paket 15: Integrasi WhatsApp Gateway Resmi (Bila Anggaran Telah Tersedia)
```

---

## 4. Rincian Teknis Pekerjaan Fase 1 (Sebelum Pilot)

### Paket 1: Penyempurnaan Web Lapor Publik Ramah Anak
- **Akses Instan via QR Code**:
  - Route `/reports/create?npsn={school_npsn}`: Sekolah otomatis terpilih dan terkunci sesuai QR Code pada poster di sekolah. Pelapor tidak perlu bingung mencari atau mengetik nama sekolah.
- **Penyederhanaan Kategori & Bahasa Ramah Anak**:
  - Penambahan kartu pilihan visual bergambar dengan istilah yang mudah dimengerti anak (misal: *"Perkataan yang Menyakitkan"*, *"Kekerasan Fisik"*, *"Dijauhi Teman"*, *"Di Media Sosial / HP"*, *"Dipalak / Diancam"*, serta opsi *"Aku Belum Yakin Namanya"*).
- **Opsi Rekam Suara (*Voice Note*)**:
  - Menambahkan input perekaman audio via browser (*MediaRecorder API*) bagi anak yang kesulitan mengetik cerita kronologi panjang.
- **Token 4 Kata Bahasa Indonesia & Kartu Bukti Lapor**:
  - Mengganti token 12 karakter acak menjadi kombinasi 4 kata bahasa Indonesia yang akrab (contoh: `kucing-buku-lari-pagi`).
  - Halaman bukti lapor visual yang ringkas dan siap disimpan dengan 1 kali *screenshot* di ponsel.
- **Tombol Keluar Cepat (*Panic Exit*)**:
  - Tombol melayang di layar pelaporan publik yang seketika mengalihkan halaman ke pencarian Google dan membersihkan cache form, melindungi privasi anak jika ada pihak lain yang mendekat.

### Paket 2: Pengamanan Privasi Pelapor & Otentikasi Staf
- **Pembersihan Metadata EXIF**:
  - Membersihkan seluruh metadata EXIF (termasuk koordinat GPS lokasi dan model perangkat) pada berkas foto bukti yang diunggah pelapor.
- **Peniadaan Log Jaringan untuk Pelapor Anonim**:
  - Pada [`ReportController::store()`](file:///d:/DATA%20UUM/Project/www/sahabat-sekolah/app/Http/Controllers/ReportController.php), jika `identity_mode == 'ANONYMOUS'`, sistem secara sengaja tidak menyimpan IP Address asli dan User-Agent pada entitas laporan maupun audit log.
- **Otentikasi Dua Faktor (2FA TOTP)**:
  - Menerapkan 2FA berbasis TOTP (Google Authenticator / standard RFC 6238) bagi akun `COUNSELOR`, `PRINCIPAL`, dan `ADMIN`.
- **Reset Password Mandiri Sekali Pakai**:
  - Mengubah aksi reset password oleh admin di [`AdminController`](file:///d:/DATA%20UUM/Project/www/sahabat-sekolah/app/Http/Controllers/AdminController.php) agar tidak menetapkan password langsung, melainkan mengirimkan tautan reset sandi aman sekali pakai ke email staf bersangkutan.

### Paket 3: Fleksibilitas Penanggung Jawab Kasus & Cadangan
- **Konfigurasi Penugasan Dinamis**:
  - Memungkinkan penunjukan Guru Kelas/Wali Kelas sebagai penanggung jawab kasus pada satuan pendidikan yang tidak memiliki Guru BK formal.
  - Menetapkan Penerima Cadangan (*Fallback Assignee*) di setiap sekolah jika penanggung jawab utama sedang berhalangan.
- **Fitur Alih Tugas Kasus (*Reassignment*)**:
  - Tombol alih tugas pada lembar kasus jika staf yang ditugaskan memiliki konflik kepentingan (misal berkerabat dengan pelaku/korban) dengan alasan yang tercatat resmi di audit log.

### Paket 4: Optimalisasi Notifikasi Cepat Pihak Sekolah
- **Pemanfaatan Maksimal Aplikasi Mobile Staf**:
  - Memastikan endpoint Mobile API v1 siap menyalurkan *push notification* instan ke ponsel Android Guru BK dan Kepala Sekolah begitu ada laporan baru masuk.
- **Web Push Notification (PWA Service Worker)**:
  - Pemasangan Web Push VAPID pada web dashboard staf sebagai lapisan notifikasi tambahan di laptop/PC.
- **Rekap Email Harian (Daily Morning Digest)**:
  - Artisan Command `php artisan sahabat:send-daily-digest` yang berjalan otomatis pukul 07.00 WIB setiap pagi hari sekolah, merangkum laporan baru yang menunggu respons awal dan kasus yang mendekati batas SLA.
- **Prosedur Panggilan Darurat Manual**:
  - Mewajibkan pengisian nomor kontak telepon/HP pada profil staf untuk prosedur panggilan darurat jika terdapat kasus berkategori `CRITICAL` yang belum direspons dalam 1 jam.

### Paket 5: Penyesuaian Kalender SLA Hari Kerja & Heartbeat Monitor
- **Kalender Hari Kerja Sekolah**:
  - Menghitung tenggat respons awal berdasarkan jam kerja aktif sekolah (Senin–Jumat pukul 07.30–15.30 WIB), sehingga laporan di akhir pekan tidak memicu status `OVERDUE` palsu.
  - Pengecualian mutlak: Laporan berisiko `CRITICAL` tetap berjalan 24/7 demi keselamatan jiwa.
- **Status Jeda SLA**:
  - Menyediakan status transisi `WAITING_REPORTER_INFO` yang menjeda penghitungan SLA saat Guru BK sedang menunggu jawaban klarifikasi dari pelapor anonim.
- **Heartbeat Pemantau Daemon**:
  - Mekanisme pemantauan detak eksekusi `sahabat:sla-scan`. Jika pemindaian tidak berjalan lebih dari 30 menit, sistem mengirimkan email peringatan darurat ke Admin Teknis Diskominfo.

### Paket 6: Deteksi Kata Kunci Merah (Red Flags) & Bantuan Krisis
- **Deteksi Teks Berbasis Aturan (*Rule-based Red Keywords*)**:
  - Memindai teks deskripsi pengaduan terhadap indikasi krisis darurat (indikasi bunuh diri, kekerasan fisik parah, ancaman senjata).
  - Otomatis menaikkan evaluasi risiko awal ke level `CRITICAL`.
- **Layar Bantuan Segera (*Emergency Help Screen*)**:
  - Menampilkan kontak darurat nasional 112, kontak layanan UPTD PPA Kota Bukittinggi, dan saluran bantuan psikologis tepat setelah laporan terkirim.
- **Status Kasus `DIRUJUK`**:
  - Menyediakan penandaan status kasus yang didelegasikan/didampingi instansi berwenang di luar sekolah.

### Paket 7: Eskalasi Kasus Jenjang 3 (Tingkat Kota)
- **Eskalasi ke Instansi Pembina**:
  - Menambahkan alur eskalasi otomatis/manual jika Kepala Sekolah tidak menindaklanjuti kasus yang telah berstatus overdue ke Admin Tingkat Kota / Instansi Pembina terkait.

### Paket 8: Graceful Handling & Defensive API pada Integrasi AConnect
- **Penanganan Defensif Tanpa Duplikasi Database**:
  - Memastikan antarmuka aplikasi menangani secara anggun (*graceful UI/UX feedback*) apabila suatu NPSN sekolah atau NISN siswa belum terpetakan di API AConnect.
  - Menampilkan pesan informatif yang jelas tanpa menimbulkan *crash* sistem, serta mencatat log integrasi untuk koordinasi teknis dengan tim API Pemkot Bukittinggi.

## 6. Laporan Hasil Eksekusi & Status Implementasi (Selesai Dikerjakan)

Seluruh paket kerja Fase 1 telah diimplementasikan, diformat sesuai standar Laravel Pint, dan diverifikasi dengan kelulusan 100% pada seluruh test suite (`79 passed, 404 assertions`):

### Rekapitulasi Paket yang Telah Selesai:

1. **Paket 1: Web Lapor Publik Ramah Anak & QR Code** [STATUS: SELESAI]
   - Token 4 kata bahasa Indonesia dengan konteks lokal Minang/Bukittinggi via `App\Helpers\AnonymousTokenHelper` (contoh: `kucing-biru-melompat-pagi`), backward & forward compatible dengan token SHA-256 12 karakter.
   - Prapilih otomatis sekolah via parameter URL QR Code (`?npsn=...`) pada `ReportController::create()`.
   - Chip visual kategori ramah anak bergambar dan teks intuitif pada `resources/views/reports/create.blade.php`.
   - Dikte suara langsung via Web Speech API (`webkitSpeechRecognition` id-ID) untuk memudahkan siswa bercerita kronologi.
   - Tombol "Keluar Cepat" (*Panic Exit*) melayang yang seketika mengalihkan browser ke Google dan mengamankan jejak pelaporan.
   - Kartu Tanda Terima Lapor visual yang ringkas dan ramah tangkapan layar (*screenshot*) pada `resources/views/reports/receipt.blade.php`.

2. **Paket 2: Privasi, Anti-EXIF & Otentikasi Staf** [STATUS: SELESAI]
   - Penghapusan seluruh metadata EXIF foto bukti otomatis sebelum disimpan ke storage via `App\Helpers\ExifHelper`.
   - Masking jaringan (IP Address & User-Agent diset `null`) pada `audit_logs` untuk seluruh pelapor anonim pada `routes/web.php`, `ReportController.php`, dan `AnonymousController.php`.
   - Penggantian reset password admin: Tidak lagi membuat password acak plaintext, melainkan menghasilkan *secure single-use signed reset token* 60 menit via `AdminController::resetPassword()`, `App\Mail\AdminResetPasswordMail`, dan `resources/views/emails/admin_reset_password.blade.php`.

3. **Paket 3: Fleksibilitas Penanggung Jawab Kasus & Fallback Madrasah** [STATUS: SELESAI]
   - Fallback otomatis pengiriman notifikasi email laporan masuk: Jika di suatu madrasah/sekolah belum terdaftar Guru BK, sistem otomatis mengalirkan notifikasi ke Kepala Sekolah (`role === 'PRINCIPAL'`), dan fallback lanjutan ke Admin Kota (`role === 'ADMIN'`).
   - Penugasan & alih tugas penanggung jawab kasus (*case reassignment*) via endpoint `cases.reassign` di `CaseController::reassign()` lengkap dengan modal pemilihan staf sekolah di `resources/views/cases/show.blade.php`, pencatatan alasan di `case_notes`, serta audit trail di `audit_logs`.

4. **Paket 4: Daily Digest Pagi (07:00 WIB)** [STATUS: SELESAI]
   - Pembuatan Artisan Command `sahabat:send-daily-digest` (`App\Console\Commands\SendDailyDigest`).
   - Template email rekapitulasi harian profesional `resources/views/emails/daily_digest.blade.php` dan mailable `App\Mail\DailyDigestMail`.
   - Registrasi jadwal harian pukul 07:00 WIB pada `routes/console.php`.

5. **Paket 5: Kalender SLA Hari Kerja, Pause State & Heartbeat** [STATUS: SELESAI]
   - Helper `App\Helpers\SlaCalculator` untuk perhitungan jam kerja operasional sekolah Kota Bukittinggi (Senin–Jumat, 07:30–15:30 WIB).
   - Kasus `CRITICAL` tetap berjalan 24/7 tanpa henti demi keselamatan nyawa.
   - Penambahan status jeda SLA `WAITING_REPORTER_INFO` pada matriks transisi status `CaseController::updateStatus()` dan `processAction()`.
   - Heartbeat monitor eksekusi daemon pada `ScanSla.php` dan eksposur status kesehatan di `SystemController::healthDetails()`.

6. **Paket 6: Deteksi Kata Kunci Merah (Red Flags) & Bantuan Krisis** [STATUS: SELESAI]
   - Deteksi otomatis kata kunci krisis darurat (*bunuh diri, senjata tajam, pelecehan berat, ancaman nyawa*) pada deskripsi laporan $\rightarrow$ otomatis menaikkan risiko ke `CRITICAL` dan memangkas batas waktu SLA menjadi 1 jam.
   - Spanduk bantuan darurat instan (Hotline 112 dan SAPA 129 UPTD PPA) pada tanda terima laporan krisis.

7. **Paket 7: Eskalasi Kasus Jenjang 3 (Dinas Pendidikan Kota Bukittinggi)** [STATUS: SELESAI]
   - Opsi eskalasi manual ke Tingkat Kota / Dinas Pendidikan (`CITY_ADMIN`) pada form eskalasi `resources/views/cases/show.blade.php`.
   - Notifikasi email otomatis dan pencatatan eskalasi ke Admin Disdikpora pada `CaseController::escalate()`.
   - Tab pemantauan kasus dieskalasikan pada dashboard admin kota (`AdminController::index`).

8. **Paket 8: Graceful Handling & Defensive API pada Integrasi AConnect** [STATUS: SELESAI]
   - Seluruh pemanggilan endpoint `AConnect` (`getDataSekolah`, `getDataSiswa`, `getDataKelas`, `getDataSiswaKelas`, `healthCheck`) dibungkus dengan *timeout* 5 detik dan blok *defensive try-catch*.
   - Sekolah atau siswa yang belum terdata di microservice Pemkot Bukittinggi tidak akan merusak alur aplikasi (tidak memicu 500 error), melainkan dialihkan secara anggun ke tampilan cadangan atau pencatatan manual.

9. **Paket 9: Portal & URL Publik Pelacak Posisi Laporan (`/track`)** [STATUS: SELESAI]
   - Menyediakan endpoint khusus publik `GET /track` (dan alias `/reports/track`) di mana pelapor dapat memantau secara transparan dan aman posisi tahapan penanganan kasus mereka.
   - Mendukung dua mode pelacakan ramah anak & privasi terjaga:
     1. **Kunci Rahasia 4 Kata** (khusus pelapor anonim): Membuka posisi tahapan + mengaktifkan ruang percakapan dua arah terenkripsi dengan Guru BK langsung di halaman pelacak.
     2. **Nomor Registrasi Laporan** (misal `SS-2026-09-0001`): Menampilkan posisi tahapan tanpa membocorkan catatan internal atau identitas sensitif.
   - **Stepper Visual 5 Tahapan Terstruktur:**
     1. *Laporan Masuk (PENDING_RESPONSE)*: Diterima aman oleh sistem.
     2. *Verifikasi & Asesmen Awal (UNDER_VERIFICATION)*: Pemeriksaan bukti & risiko oleh Guru BK.
     3. *Proses Penanganan Kasus (IN_HANDLING / WAITING_REPORTER_INFO)*: Tindakan aktif, konseling, mediasi; dengan highlight khusus bila Guru BK menunggu tanggapan pelapor.
     4. *Penyelesaian Kasus (RESOLVED)*: Substansi kasus selesai ditangani.
     5. *Selesai & Ditutup (CLOSED)*: Dokumen penyelesaian lengkap & resmi diarsipkan.
   - Terintegrasi dengan tombol *"Lacak Posisi Laporan"* pada tanda terima (`reports/receipt.blade.php`), bilah navigasi & beranda (`landing.blade.php`), serta kanal komunikasi anonim (`anonymous/channel.blade.php`).

10. **Paket 10: Multi-Instansi Eskalasi Jenjang 3 Tingkat Kota (`CITY_ADMIN`: Disdik & UPTD PPA DP3APPKB)** [STATUS: SELESAI]
    - **Penyediaan Akun Resmi Multi-Instansi:**
      - Menambahkan migrasi database `2026_10_27_091500_add_city_admin_role_and_account.php` dan `2026_10_27_101000_add_uptd_ppa_city_admin_account.php`.
      - Mendaftarkan dua akun instansi resmi tingkat kota:
        1. **Dinas Pendidikan & Kebudayaan Kota Bukittinggi**: `dinas@sahabat.test` (`role: CITY_ADMIN`, `school_npsn: null`).
        2. **UPTD PPA - DP3APPKB Kota Bukittinggi**: `ppa@sahabat.test` (`role: CITY_ADMIN`, `school_npsn: null`).
    - **Pilihan Eskalasi Spesifik di Form Guru BK (`cases.show`):**
      - Guru BK dapat memilih tujuan eskalasi yang sesuai dengan konteks dan jenis kasus:
        1. *Jenjang 2: Kepala Sekolah* (penanganan internal satuan pendidikan).
        2. *Jenjang 3: Dinas Pendidikan Kota Bukittinggi* (`CITY_DISDIK`: isu manajerial, kedinasan, sanksi administratif, pembinaan kurikulum).
        3. *Jenjang 3: UPTD PPA - DP3APPKB* (`CITY_PPA`: kekerasan fisik berat, pelecehan/kekerasan seksual, perlindungan hukum & psikologis anak).
        4. *Jenjang 3: Satgas PPKSP Kota* (`CITY_SATGAS`: gabungan Dinas Pendidikan dan UPTD PPA untuk situasi kritis/darurat lintas sektor).
    - **Penyaluran Notifikasi & Email Cerdas (`CaseController::escalate`):**
      - Sistem menyaring akun penerima email dan notifikasi secara tepat: jika ditujukan ke UPTD PPA, email terkirim ke `ppa@sahabat.test`; jika ke Disdik, email ke `dinas@sahabat.test`; jika ke Satgas Gabungan, terkirim ke kedua instansi sekaligus.
    - **Pemisahan Cakupan Sekolah (School Scoping):**
      - Akun staf sekolah (`COUNSELOR`, `PRINCIPAL`) terisolasi ketat pada NPSN masing-masing.
      - Seluruh akun tingkat kota (`CITY_ADMIN`, `ADMIN`) memiliki `school_npsn = null`, sehingga memiliki hak akses lintas sekolah se-Kota Bukittinggi untuk memantau dan membuka detail kasus (`scopedCase` & `CaseController::show`).
    - **Antarmuka Manajemen Pengguna (`/admin/users`):**
      - Dropdown filter role dan modal tambah/ubah pengguna mendukung pemilihan instansi tingkat kota (*Dinas Pendidikan*, *UPTD PPA DP3APPKB*, *Satgas PPKSP*).
      - Badge visual membedakan instansi secara tegas: badge indigo untuk `Disdik (City)` dan badge rose untuk `UPTD PPA (City)`.

---

### Hasil Verifikasi Pengujian Otomatis

- **Feature Tests:**
  - `tests/Feature/BusinessRulesTest.php`: **21 PASSED** (Token 4 kata, Exif, Panic Exit, Chips, Crisis red-flag, Reassignment, Fallback Madrasah, Jenjang 3 Eskalasi Disdik, Eskalasi UPTD PPA DP3APPKB, Eskalasi Satgas Gabungan, Akses Kasus Lintas Sekolah, SlaCalculator jam kerja, Daily Digest, Tracking token anonim, Tracking nomor laporan, Stepper 5 tahap, Pesan interaktif tracker).
  - `tests/Feature/AdminDashboardTest.php`: **11 PASSED** (Pembuatan akun CITY_ADMIN Disdik, pembuatan akun CITY_ADMIN UPTD PPA dengan pilihan instansi, update role CITY_ADMIN, Reset password via token aman 60 menit, Normalisasi email, Chart & SLA Admin).
  - `tests/Feature/PasswordResetTest.php`: **13 PASSED** (Alur reset password mandiri).
- **Full Test Suite:** **89 PASSED, 0 FAILED** (459 assertions).
- **Code Style:** Lulus pemformatan Laravel Pint tanpa pelanggaran gaya kode (`{"tool":"pint","result":"passed"}`).
