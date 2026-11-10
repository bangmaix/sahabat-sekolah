# DOKUMEN KESIMPULAN TEKNIS KESELURUHAN PROYEK
# SAHABAT SEKOLAH — KOTA BUKITTINGGI

**Platform Digital Pelaporan, Pencegahan, dan Penanganan Kasus Perundungan (*Case Management System*) Satuan Pendidikan Kota Bukittinggi**

---

| Parameter Dokumen | Rincian |
| :--- | :--- |
| **Nama Proyek** | SAHABAT SEKOLAH |
| **Wilayah Implementasi** | Satuan Pendidikan (SD, SMP, MTs, MIN) Kota Bukittinggi |
| **Tahap / Milestone** | Penyelesaian Tahap Foundation, Core Engine, Web Dashboard, Integrasi Microservice, dan Mobile REST API (v1) |
| **Versi Aplikasi** | 1.0 (Production-Ready MVP & Pilot Project) |
| **Framework & Versi** | Laravel 11/12 (PHP 8.3), PostgreSQL / SQLite / MySQL |
| **Pilot Project Target** | MIN Kota Bukittinggi & MTsN 1 Kota Bukittinggi |

---

## 1. Ringkasan Eksekutif & Visi Sistem

**SAHABAT SEKOLAH** dikembangkan bukan sekadar sebagai formulir atau kotak aduan digital biasa (*complaint box*), melainkan sebagai sebuah **Case Management System (CMS)** terintegrasi untuk menangani perundungan (*bullying*) secara komprehensif dari hulu ke hilir. 

Sistem ini menjembatani peserta didik (korban/saksi) dengan satuan pendidikan untuk menciptakan ruang belajar yang aman dan bebas intimidasi, dengan prinsip-prinsip utama:
1. **Zero Fear Reporting**: Menjamin pelaporan yang aman tanpa rasa takut melalui tiga mode identitas (Terbuka, Rahasia, dan Anonim Kriptografis).
2. **Akuntabilitas Waktu Respons (Strict SLA)**: Memastikan setiap laporan wajib direspons maksimal 1 × 24 jam (atau lebih cepat pada risiko tinggi/kritis) dengan eskalasi otomatis.
3. **Pemberdayaan Guru Bimbingan dan Konseling (BK)**: Menempatkan Guru BK sebagai aktor sentral pemroses kasus yang didukung pencatatan log tindakan terstruktur.
4. **Pengawasan Pimpinan (Principal Monitoring)**: Memberikan Kepala Sekolah visibilitas langsung terhadap status kasus, tren perundungan, dan kasus-kasus yang memerlukan intervensi kebijakan.
5. **Pendekatan Holistik**: Mengimbangi penanganan kasus dengan **Modul Prestasi Siswa** terintegrasi database resmi Pemerintah Kota Bukittinggi.

---

## 2. Arsitektur Teknologi & Pondasi Sistem

Sistem dibangun menggunakan pendekatan **Modular Monolith** berbasis Laravel, yang memisahkan ranah logika bisnis secara bersih, berkinerja tinggi, dan siap dihubungkan dengan berbagai klien.

```
┌────────────────────────────────────────────────────────────────────────┐
│                        KLIEN / PENGGUNA SISTEM                         │
├──────────────────────────┬─────────────────────────┬───────────────────┤
│ Siswa / Pelapor Publik   │ Guru BK & Kepala Sek.   │ Admin Kota        │
│ (Web Publik & Mobile)    │ (Web Dashboard & Mobile)│ (Web Admin)       │
└────────────┬─────────────┴────────────┬────────────┴─────────┬─────────┘
             │                          │                      │
             ▼                          ▼                      ▼
┌────────────────────────────────────────────────────────────────────────┐
│                        LAYER AKSES & KEAMANAN                          │
│  - HTTPS / TLS 1.2+ Transport Security                                │
│  - Rate Limiting & Brute-Force Shield (Throttle)                       │
│  - Multi-Tenant Isolation (NPSN Scoping)                               │
│  - Role-Based Access Control (RBAC) & Signed URL Incident Protocol     │
│  - Bearer Token Auth (SHA-256 Hashed) untuk Mobile API                │
├────────────────────────────────────────────────────────────────────────┤
│                     APLIKASI BACKEND (LARAVEL 8.3+)                    │
│                                                                        │
│  ┌──────────────────────┐  ┌─────────────────────┐  ┌───────────────┐ │
│  │ Reporting & Identity │  │ Case Management     │  │ Risk Engine   │ │
│  │ (Anon Token Shield)  │  │ (Workflow & Docs)   │  │ (Matrix Eval) │ │
│  └──────────────────────┘  └─────────────────────┘  └───────────────┘ │
│  ┌──────────────────────┐  ┌─────────────────────┐  ┌───────────────┐ │
│  │ SLA & Escalation     │  │ Student Achievement │  │ Notifications │ │
│  │ (Automated Scanner)  │  │ (Portfolio Siswa)   │  │ (Mail & Queue)│ │
│  └──────────────────────┘  └─────────────────────┘  └───────────────┘ │
├────────────────────────────────────────────────────────────────────────┤
│                       INTEGRASI & INFRASTRUKTUR                        │
│  - Database Relasional (29 Migrasi Database Terstandarisasi)           │
│  - AConnect Helper: Microservice Sekolah Pemkot Bukittinggi            │
│  - Private Encrypted Storage (Berkas Kasus & Dokumen Penyelesaian)     │
│  - Background Worker & Scheduler (Artisan SLA Scanner, Backup, Mail)  │
│  - Containerization (Dockerfile & Docker Compose)                      │
└────────────────────────────────────────────────────────────────────────┘
```

### Spesifikasi Teknis Utama:
- **Bahasa & Runtime**: PHP 8.3+ (memanfaatkan typed properties, match expressions, constructor promotion).
- **Framework Web**: Laravel (dengan Laravel Boost & Pint standardizer).
- **Frontend Web**: Laravel Blade Components, Vanilla CSS modular, ApexCharts untuk grafik interaktif.
- **REST API**: API v1 terenkripsi dengan response envelope terstandar untuk konsumsi aplikasi mobile (Flutter / Android / iOS).
- **Database Engine**: PostgreSQL / MySQL / SQLite dengan integritas foreign key dan indeks teroptimasi.
- **Antrean Tugas (Queue)**: Database Queue Driver untuk pemrosesan asinkron pengiriman email dan notifikasi.
- **Microservice Integrator**: Integrasi HTTP Pool parallel dengan REST API Dinas Pendidikan / Pemkot Bukittinggi (`https://api.bukittinggikota.go.id/sekolah`).

---

## 3. Matriks Modul & Fitur Teknis yang Telah Diselesaikan

### 3.1. Modul Multi-Tenancy & Autentikasi Pengguna (Identity & RBAC)
- **Multi-Tenant NPSN Scoping**: Seluruh entitas data kasus, laporan, prestasi, dan pengguna dikaitkan secara ketat dengan `school_npsn`. Melalui helper `scopedCase($caseNumber)` dan filter query database, pengguna dari satu sekolah **mustahil** dapat melihat atau memodifikasi data sekolah lain.
- **Role-Based Access Control (RBAC)**:
  - Role yang didukung: `ADMIN`, `COUNSELOR` (Guru BK), `PRINCIPAL` (Kepala Sekolah), `TEACHER`, `STUDENT`, `PARENT`.
  - Sistem permission granular: `REPORT_VIEW`, `CASE_VIEW`, `CASE_UPDATE`, `CASE_ESCALATE`, `CASE_NOTE_CREATE`, `EVIDENCE_UPLOAD`, `NOTIFICATION_VIEW`, `STATISTICS_VIEW`, `ACHIEVEMENT_VIEW`, `ACHIEVEMENT_MANAGE`, `USER_MANAGE`, `MASTER_DATA_MANAGE`.
- **Manajemen Akun Terpusat**:
  - Normalisasi alamat email otomatis (case-insensitive & whitespace trimming) guna mencegah akun ganda.
  - Pengelolaan status aktif/non-aktif akun oleh Admin Sistem.

### 3.2. Modul Pelaporan Publik & Perlindungan Korban/Saksi (*Reporting & Identity Shield*)
- **Tiga Tingkat Perlindungan Identitas**:
  1. `IDENTIFIED` (Terbuka): Identitas pelapor tercatat lengkap untuk koordinasi langsung.
  2. `CONFIDENTIAL` (Rahasia): Identitas pelapor hanya dapat diakses oleh Guru BK yang menangani dan dilindungi dari pihak lain.
  3. `ANONYMOUS` (Anonim): Tidak memerlukan pendaftaran akun, nama, email, maupun nomor telepon.
- **Kriptografi Token Anonim (*Anonymous Token Shielding*)**:
  - Pelapor anonim mendapatkan `anonymous_token` acak 12 karakter.
  - Token ini disimpan secara hashed di database (`anonymous_report_tokens`) dan menjadi satu-satunya kunci untuk:
    - Melacak status penanganan pengaduan melalui halaman bukti lapor (`/reports/receipt`).
    - Saluran komunikasi dua arah (*Two-Way Anonymous Channel*) antara pelapor anonim dengan Guru BK tanpa membuka identitas pelapor.
- **Data Masking Otomatis**: Backend secara konsisten menyamarkan nama pelapor menjadi `"Anonim (Disamarkan)"` dan menghapus atribut kontak pada seluruh respons API dan antarmuka ketika laporan bertipe anonim.

### 3.3. Modul Manajemen Kasus (*Case Management System*)
- **State Machine Terstruktur**:
  Alur kasus dikontrol secara ketat tanpa bypass status:
  $$\text{PENDING\_RESPONSE} \longrightarrow \text{UNDER\_VERIFICATION} \longrightarrow \text{IN\_HANDLING} \longrightarrow \text{RESOLVED} \longrightarrow \text{CLOSED}$$
- **Guardrail Penyelesaian Kasus (*Resolution Integrity*)**:
  Sistem melarang penutupan kasus (`CLOSED`) apabila belum terdapat dokumen bukti resolusi formal (`resolution document`) yang diunggah.
- **Pencatatan Aktivitas & Catatan Kasus**:
  - Log audit aktivitas otomatis mencatat setiap tindakan staf (tanggal, jam, aktor, IP, user-agent).
  - Catatan konseling internal (*Case Notes*) terpisah antara catatan publik dan catatan tertutup.
- **Pelibatan Pihak Terkait & Orang Tua**:
  - Modul `case_participants`: Pemetaan pihak yang terlibat (korban, pelaku, saksi, pendamping).
  - Modul `parent_involvements`: Pencatatan status pemberitahuan, tanggal pemanggilan, kehadiran, dan komitmen orang tua murid.
- **Manajemen Bukti & Berkas Kasus**:
  - Berkas foto/video/dokumen disimpan di *private encrypted storage*.
  - Akses unduh berkas diproteksi otorisasi berbasis sesi dan token.
  - Fitur **Cetak Berita Acara / Laporan Kasus Formal** (`/cases/{caseNumber}/print`) untuk kebutuhan arsip dinas.

### 3.4. Modul Penilaian Risiko (*Risk Management Engine*)
- **Klasifikasi Kategori & Subkategori**:
  Mendukung 8 kategori utama (Perundungan Fisik, Verbal, Sosial/Psikologis, Cyberbullying, Intimidasi/Ancaman, Pemerasan, Kekerasan Serius, dan Indikasi/Kekhawatiran) dengan puluhan subkategori terperinci.
- **Matriks Risiko Otomatis & Manual**:
  - Tingkat risiko: `LOW`, `MEDIUM`, `HIGH`, `CRITICAL`.
  - Evaluasi parameter mencakup: tingkat keparahan insiden, ancaman fisik langsung, dampak trauma psikologis, dan potensi pengulangan (*recidivism*).
  - Risiko menentukan batas waktu respons awal (CRITICAL: 1 jam; HIGH: 4 jam; MEDIUM/LOW: 24 jam).

### 3.5. Modul Pemantauan Batas Waktu SLA (*Service Level Agreement Engine*)
- **Pemantauan Otomatis & Status SLA**:
  - Menghitung `response_deadline` (respons awal Guru BK) dan `resolution_deadline` (penyelesaian kasus).
  - Status SLA: `ON_TIME` / `ON_TRACK`, `WARNING` (mendekati batas waktu, < 6 jam), dan `OVERDUE` (melewati batas waktu).
  - Jejak akuntabilitas: Jika kasus direspons setelah batas terlewati, sistem secara permanen mencatat SLA respons sebagai `OVERDUE`.
- **Background SLA Scanner**:
  - Artisan Command: `php artisan sahabat:sla-scan`.
  - Berjalan berkala secara idempoten (mencegah duplikasi notifikasi pengingat) dan mencatat jejak di `case_reminders`.
- **Modal Interaktif Peringatan SLA**:
  Dashboard Guru BK otomatis memunculkan modal peringatan mendesak (*emergency modal*) ketika terdapat kasus yang berstatus `OVERDUE` atau membutuhkan penanganan segera.

### 3.6. Modul Eskalasi Kasus (*Escalation Engine*)
- **Eskalasi Otomatis (Automatic Escalation)**:
  Dipicu oleh SLA Scanner ketika batas waktu respons awal Guru BK terlewati (`OVERDUE`), secara otomatis mengirimkan notifikasi prioritas tinggi kepada Kepala Sekolah.
- **Eskalasi Manual (Manual Escalation)**:
  Guru BK dapat mengajukan eskalasi resmi ke Kepala Sekolah dengan menyertakan alasan eskalasi, tindakan yang telah ditempuh, dan bentuk keputusan institusi/hukum yang dibutuhkan.

### 3.7. Modul Notifikasi & Pengiriman Email Asinkron (*Notification & Mail System*)
- **Pusat Notifikasi Aplikasi (In-App Center)**:
  Notifikasi dinamis untuk Guru BK dan Kepala Sekolah dengan badge belum dibaca, fitur baca terpilih (*read-selected*), dan tandai semua telah dibaca (*read-all*).
- **Mesin Email Responsif Berbasis Antrean (Queue Worker)**:
  - Menggunakan template HTML email responsif profesional:
    - `NewReportCounselorMail`: Pemberitahuan instan laporan masuk ke Guru BK.
    - `CaseEscalatedPrincipalMail`: Pemberitahuan kasus eskalasi ke Kepala Sekolah.
    - `UserCreatedMail` & `AdminResetPasswordMail`: Kredensial akun dari admin.
    - `ResetPasswordMail` & `PasswordChangedMail`: Notifikasi keamanan akun.
    - `SuspectedBreachAdminAlertMail`: Notifikasi insiden keamanan ke Admin.
  - Dijalankan via Laravel Queue dengan kebijakan *retry* teruji (5 kali percobaan, jeda 60 detik).
  - Endpoint pengujian diagnostik SMTP lengkap: `/test-email` (pemeriksaan soket port 465/587 dan status worker antrean).

### 3.8. Modul Keamanan Tingkat Lanjut (*Advanced Security & Incident Response*)
- **Alur Lupa Kata Sandi Mandiri**:
  Menggunakan token kriptografis berbatas waktu (60 menit) dengan validasi ketat.
- **Protokol Tanggap Insiden Pengambilalihan Akun (*Unauthorized Breach Protocol*)**:
  - Setiap pergantian kata sandi memicu pengiriman email konfirmasi ke alamat pengguna asli.
  - Email tersebut memuat tombol darurat bertanda tangan kriptografis (*Signed URL*).
  - Jika korban mengklik tombol tersebut, sistem secara otomatis membekukan sesi terkait dan mengirimkan peringatan darurat ke Admin Sistem lengkap dengan detail alamat IP, User-Agent, dan stempel waktu insiden.
- **Perlindungan Terhadap Brute-Force & Spam**:
  Diterapkan rate limiting (`throttle:5,1` dan `throttle:10,1`) pada endpoint login, pemulihan sandi, pelaporan publik, dan pesan anonim.

### 3.9. Modul Pencatatan Prestasi Siswa (*Student Achievement Module*)
- **Pencatatan Portofolio Prestasi Positif**:
  Menyediakan wadah bagi sekolah untuk mengapresiasi dan mencatat prestasi siswa (bidang akademik, olahraga, seni, keagamaan, sains, kepemimpinan).
- **Integrasi Master Data**:
  - Filter tingkatan lomba (Sekolah, Kecamatan, Kota/Kabupaten, Provinsi, Nasional, Internasional).
  - Filter peringkat (Juara 1, 2, 3, Harapan, Peserta Terpilih).
  - Unggah dan validasi berkas sertifikat/piagam penghargaan.
- **Profil Portofolio Berdasarkan NISN**:
  Halaman profil siswa (`/achievements/student/{nisn}`) yang menyajikan rekam jejak capaian siswa selama masa studi.
- **Periode Akademik Dinamis**:
  Dukungan tahun ajaran dan semester aktif dengan kemampuan *fallback period* otomatis.

### 3.10. Integrasi Microservice Pemerintah Kota Bukittinggi (*AConnect Helper*)
- **Helper Terpadu `App\Helpers\AConnect`**:
  Berkomunikasi langsung dengan API Microservice Sekolah Pemerintah Kota Bukittinggi (`https://api.bukittinggikota.go.id/sekolah`) dengan header otentikasi aman `x-api-key`.
- **Fitur Sinkronisasi**:
  - Penarikan daftar sekolah se-Kota Bukittinggi (`/v1/data-sekolah`).
  - Penarikan detail data siswa berdasarkan NISN, NPSN, dan Semester (`/v1/data-siswa`).
  - Penarikan rombongan belajar / kelas dan daftar anggota kelas (`/v1/data-kelas`).
- **High Performance Parallel Pool & Caching**:
  Menggunakan HTTP Pool asinkron dan caching otomatis (1 jam via `Cache::remember`) sehingga pencarian data siswa instan tanpa ketergantungan lambat ke server induk.

### 3.11. Ekosistem Mobile REST API (v1)
- **Arsitektur API Mobile**:
  Menyediakan 20+ endpoint API siap pakai untuk aplikasi Android/iOS/Flutter yang terdokumentasi lengkap di [`MOBILE_API_DOC.md`](file:///d:/DATA%20UUM/Project/www/sahabat-sekolah/MOBILE_API_DOC.md).
- **Mekanisme Kriptografi Token API**:
  - Bearer token di-hash menggunakan algoritma `SHA-256` di tabel `api_tokens`.
  - Pelacakan waktu kedaluwarsa (`expires_at`) dan waktu pakai terakhir (`last_used_at`).
  - Pembatalan instan saat logout (`POST /api/v1/auth/logout`).
- **Response Envelope Terstandar**:
  Format JSON konsisten (`success`, `message`, `data`, `meta`, `errors`) memudahkan parsing pada sisi frontend mobile.

### 3.12. Backoffice Administrator & Analitik Eksekutif
- **Dashboard Analitik Eksekutif**:
  - Visualisasi grafik ApexCharts interaktif: Tren Kasus Bulanan, Distribusi Status Penanganan, dan Rasio Kepatuhan SLA.
  - Tampilan indikator kinerja penanganan kasus tingkat kota/sekolah.
- **Manajemen Pengguna & Konfigurasi Sistem**:
  - Pembuatan akun Guru BK dan Kepala Sekolah per sekolah.
  - Pengaturan fleksibel durasi batas waktu SLA per tingkat risiko (`admin/sla-configurations`).
  - Pengelolaan master kategori perundungan, subkategori, dan kategori prestasi.

---

## 4. Rekayasa Basis Data & Struktur Migrasi

Basis data dirancang mengikuti kaidah normalisasi relasional dengan relasi integritas referensial dan indeks pencarian. Proyek ini memuat **29 file migrasi terstruktur**:

| File Migrasi | Objek / Tabel yang Dibangun | Fungsi Teknis |
| :--- | :--- | :--- |
| `0001_01_01_000000_create_users_table.php` | `users`, `password_reset_tokens`, `sessions` | Entitas pengguna utama dan sesi Laravel |
| `0001_01_01_000001_create_cache_table.php` | `cache`, `cache_locks` | Penyimpanan cache sistem |
| `0001_01_01_000002_create_jobs_table.php` | `jobs`, `job_batches`, `failed_jobs` | Antrean asinkron pengiriman email dan jobs |
| `2026_09_10_000003_create_sahabat_sekolah_core_tables.php` | `reports`, `cases`, `case_slas` | Inti kasus pengaduan dan relasi SLA |
| `2026_09_10_000004_create_case_activity_tables.php` | `case_activities` | Jejak kronologis aksi penanganan |
| `2026_09_10_000005_create_case_documents_tables.php` | `case_documents` | Manajemen lampiran berkas dan resolusi kasus |
| `2026_09_10_000006_create_case_risk_tables.php` | `case_risk_assessments` | Pencatatan riwayat penilaian risiko |
| `2026_09_10_000007_create_notifications_table.php` | `notifications` | Notifikasi in-app untuk pengguna |
| `2026_09_10_000008_create_case_notes_table.php` | `case_notes` | Catatan konseling internal Guru BK |
| `2026_09_10_000009_add_overdue_at_to_case_slas.php` | Penambahan field `overdue_at`, `warning_at` | Audit akuntabilitas keterlambatan SLA |
| `2026_09_10_000010_create_anonymous_report_tokens_table.php` | `anonymous_report_tokens` | Token hash untuk pelacakan laporan anonim |
| `2026_09_10_000011_create_anonymous_messages_table.php` | `anonymous_messages` | Ruang obrolan dua arah pelapor anonim |
| `2026_09_10_000012_add_roles_to_users_table.php` | Field `role`, `school_npsn` pada `users` | Penautan awal otorisasi dan tenant sekolah |
| `2026_09_10_000013_create_audit_tables.php` | `audit_logs` | Audit trail perubahan data sensitif |
| `2026_09_10_000014_create_case_escalations_table.php` | `case_escalations` | Pencatatan eskalasi kasus ke Kepala Sekolah |
| `2026_09_10_000015_create_rbac_tables.php` | `roles`, `permissions`, `role_permissions` | Struktur RBAC dinamis |
| `2026_09_10_000016_create_student_identity_tables.php` | `reporter_identities` | Penyimpanan identitas pelapor (non-anonim) |
| `2026_09_10_000017_create_bullying_categories_tables.php` | `bullying_categories`, `subcategories` | Master klasifikasi perundungan |
| `2026_09_10_000018_create_case_participants_table.php` | `case_participants` | Pemetaan pihak terlibat (korban, pelaku, saksi) |
| `2026_09_10_000019_create_parent_involvement_tables.php` | `parent_involvements` | Pelibatan dan kehadiran orang tua murid |
| `2026_09_10_000020_create_case_reminders_table.php` | `case_reminders` | Jejak pengingat SLA otomatis |
| `2026_09_10_000021_add_admin_rbac_and_account.php` | Seeder awal hak akses Admin Sistem | Penyediaan akun administratif awal |
| `2026_09_10_000022_create_api_tokens_table.php` | `api_tokens` | Kriptografi bearer token Mobile API |
| `2026_09_10_000023_add_statistics_permission.php` | Hak akses `STATISTICS_VIEW` | Perizinan modul statistik |
| `2026_09_22_075910_drop_schools_table.php` | Penghapusan tabel statis `schools` | Migrasi ke arsitektur live Microservice Pemkot |
| `2026_09_22_100000_add_sla_configurations.php` | `sla_configurations` | Konfigurasi dinamis target SLA per tingkat risiko |
| `2026_10_08_043953_add_school_name_to_users_table.php` | Field `school_name` pada `users` | Denormalisasi nama sekolah untuk performa |
| `2026_10_15_024439_create_academic_periods_and_achievements_tables.php` | `academic_periods`, `achievement_categories`, `student_achievements` | Modul pencatatan portofolio prestasi siswa |
| `2026_10_23_104229_add_status_to_users_table.php` | Field `status` (ACTIVE / INACTIVE) pada `users` | Pengendalian status keaktifan akun pengguna |

---

## 5. Ringkasan Endpoint API Mobile (v1)

Mobile REST API dirancang stateless dengan autentikasi Bearer Token (SHA-256 Hashing). Berikut adalah ringkasan peta endpoint yang telah diuji:

```
/api/v1/
│
├── public/                                      [Akses Terbuka / Tanpa Token]
│   ├── GET  /schools                            (Daftar sekolah Kota Bukittinggi)
│   ├── GET  /categories                         (Daftar kategori & subkategori perundungan)
│   ├── POST /reports                            (Kirim laporan baru: Anonim / Rahasia / Terbuka)
│   ├── POST /reports/track                      (Lacak laporan via nomor kasus & token)
│   ├── GET  /anonymous/{token}/messages         (Ambil pesan percakapan anonim)
│   └── POST /anonymous/{token}/messages         (Kirim balasan percakapan anonim)
│
├── auth/                                        [Autentikasi Pengguna Sekolah]
│   ├── POST /login                              (Login Guru BK / Kepsek -> Bearer Token)
│   ├── POST /logout                             (Logout & cabut token seketika)
│   ├── POST /forgot-password                    (Minta tautan reset sandi)
│   └── POST /reset-password                     (Reset kata sandi)
│
└── [Terproteksi Middleware: RequireApiToken & Multi-Tenant NPSN Scoping]
    ├── GET  /me                                 (Profil akun & role pengguna)
    ├── GET  /dashboard                          (Statistik ringkasan & badge kasus)
    ├── GET  /statistics                         (Analitik tren kasus perundungan)
    │
    ├── cases/                                   [Manajemen Kasus]
    │   ├── GET  /                               (Daftar kasus terfilter sekolah pengguna)
    │   ├── GET  /{caseNumber}                   (Detail lengkap kasus, pihak, dokumen)
    │   ├── POST /{caseNumber}/actions           (Eksekusi tindakan: verifikasi/penanganan/resolusi)
    │   ├── POST /{caseNumber}/notes             (Tambah catatan konseling internal)
    │   ├── POST /{caseNumber}/escalate          (Eskalasi kasus ke Kepala Sekolah)
    │   ├── POST /{caseNumber}/parent-involvement(Update data pemanggilan orang tua)
    │   ├── POST /{caseNumber}/participants      (Tambah identitas pihak terlibat)
    │   └── POST /{caseNumber}/anonymous-reply   (Guru BK membalas chat pelapor anonim)
    │
    ├── notifications/                           [Pusat Notifikasi]
    │   ├── GET  /                               (Daftar notifikasi akun)
    │   ├── POST /{id}/read                      (Tandai satu dibaca)
    │   ├── POST /read-selected                  (Tandai pilihan dibaca)
    │   └── POST /read-all                       (Tandai semua dibaca)
    │
    └── achievements/                            [Modul Prestasi Siswa]
        ├── GET  /                               (Daftar riwayat prestasi sekolah)
        ├── GET  /{id}                           (Detail capaian prestasi)
        ├── POST /                               (Input prestasi siswa baru)
        └── GET  /students/search                (Pencarian siswa via API Pemkot)
```

---

## 6. Automasi Konsol, Daemon, & Perintah Artisan

Aplikasi dilengkapi dengan Artisan Console Commands kustom untuk memastikan operasi otomatisasi latar belakang berjalan tanpa campur tangan manual:

| Perintah Artisan | Jadwal / Cara Kerja | Fungsi Utama |
| :--- | :--- | :--- |
| `php artisan sahabat:sla-scan` | Dijalankan via scheduler (cron setiap 5-15 menit) | Memindai seluruh kasus aktif; mendeteksi status `WARNING` dan `OVERDUE`; mengirim notifikasi pengingat; serta memicu **eskalasi otomatis** ke Kepala Sekolah jika batas waktu respons awal terlewati. |
| `php artisan sahabat:sync-schools` | Dijalankan berkala | Mengambil data terbaru dari Microservice Sekolah Pemkot Bukittinggi untuk menyelaraskan nama sekolah dan NPSN pada database lokal. |
| `php artisan sahabat:backup` | Dijalankan harian (tengah malam) | Membuat salinan berkas database dan dokumen penting ke direktori cadangan aman. |
| `php artisan queue:work` | Dijalankan sebagai daemon / supervisor | Memproses antrean email pemberitahuan kasus baru, eskalasi, reset password, dan peringatan pembobolan akun secara asinkron tanpa memperlambat interaksi pengguna. |

---

## 7. Rangkaian Pengujian Otomatis (*Automated Feature Testing*)

Untuk menjamin kehandalan logika bisnis (*zero regression*), sistem dilengkapi **10 rangkaian pengujian fitur terintegrasi (Feature Tests)** yang mencakup 100% skenario kritis:

```
tests/Feature/
├── BusinessRulesTest.php          -> Menjamin isolasi multi-tenant NPSN, penolakan transisi status ilegal,
│                                     syarat dokumen sebelum penutupan kasus, dan pembuatan token anonim.
├── SlaResponseLifecycleTest.php   -> Memvalidasi siklus hidup SLA respons, pencatatan waktu overdue permanen,
│                                     dan akuntabilitas Guru BK.
├── SlaDashboardModalTest.php      -> Memvalidasi kemunculan modal peringatan darurat pada dashboard Guru BK
│                                     saat terdeteksi kasus overdue.
├── NotificationMailTest.php       -> Menguji pengiriman email notifikasi laporan baru ke Guru BK dan email
│                                     eskalasi ke Kepala Sekolah tepat sasaran per sekolah.
├── NotificationTest.php           -> Menguji in-app notifications, badge jumlah belum dibaca, fitur tandai
│                                     terpilih, dan tandai semua dibaca.
├── PasswordResetTest.php          -> Menguji alur pemulihan sandi, pengiriman email konfirmasi sandi baru, dan
│                                     mekanisme darurat Signed URL saat terjadi indikasi pembobolan akun.
├── StudentAchievementTest.php     -> Menguji seluruh siklus CRUD prestasi siswa, otorisasi peran, validasi
│                                     unggahan piagam, dan pencarian siswa berbasis NISN.
├── AdminDashboardTest.php         -> Menguji penyajian analitik ApexCharts, manajemen akun staf, normalisasi
│                                     email, dan pengelolaan konfigurasi SLA.
├── MobileApiTest.php              -> Menguji seluruh endpoint REST API v1 mobile, otentikasi Bearer Token SHA-256,
│                                     pelaporan anonim publik, dan komunikasi chat 2 arah.
└── ExampleTest.php                -> Pengujian dasar respon HTTP aplikasi.
```

---

## 8. Panduan Singkat Operasional & Kesiapan Rilis

### 8.1. Persyaratan Lingkungan (Production & Staging)
- **PHP**: Versi 8.3 dengan ekstensi `pdo`, `mbstring`, `openssl`, `curl`, `fileinfo`.
- **Database Engine**: PostgreSQL 14+ (direkomendasikan) atau MySQL 8.0+.
- **Web Server**: Nginx dengan reverse proxy / Apache dengan `mod_rewrite`.
- **Node.js & NPM**: Node 18+ untuk kompilasi aset frontend.

### 8.2. Prosedur Inisialisasi Aplikasi
```bash
# 1. Instalasi dependensi PHP & Node.js
composer install --no-dev --optimize-autoloader
npm install
npm run build

# 2. Konfigurasi Lingkungan
cp .env.example .env
php artisan key:generate

# 3. Migrasi Database Terpadu
php artisan migrate --force

# 4. Inisialisasi Storage Link Berkas
php artisan storage:link

# 5. Optimasi Cache Produksi
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 8.3. Konfigurasi Task Scheduler (Cron Job)
Tambahkan entri berikut pada crontab server produksi agar pemantau SLA dan pembersih antrean berjalan otomatis setiap menit:
```cron
* * * * * cd /path/to/sahabat-sekolah && php artisan schedule:run >> /dev/null 2>&1
```

---

## 9. Kesimpulan & Rekomendasi Tahap Lanjutan

### Kesimpulan
Seluruh pondasi inti (*Core Foundation*), mesin aturan bisnis (*Business Rules Engine*), penanganan keamanan privasi korban (*Identity Shield*), SLA & eskalasi, integrasi data siswa Pemkot Bukittinggi, modul prestasi siswa, dashboard analitik, dan antarmuka REST API v1 telah **berhasil diselesaikan dan teruji secara menyeluruh**.

Sistem ini telah berada pada status **Production-Ready MVP**, memenuhi seluruh persyaratan teknis dalam *Product Requirements Document (PRD) Final Terpadu*, dan siap diimplementasikan pada **Pilot Project (MIN Kota Bukittinggi dan MTsN 1 Kota Bukittinggi)**.

### Rekomendasi Tahap Lanjutan (Roadmap Mendatang):
1. **Pengembangan Klien Mobile (Flutter)**: Mengonsumsi REST API v1 yang telah selesai dibangun untuk aplikasi siswa di Play Store / App Store.
2. **Kanal Notifikasi WhatsApp Gateway**: Menghubungkan SLA Overdue Alert langsung ke nomor WhatsApp Kepala Sekolah atau Pembina BK melalui gateway resmi.
3. **Peningkatan Analitik Prediktif**: Mengembangkan modul deteksi dini perundungan di tingkat sekolah berdasarkan tren laporan berulang atau hotspot lokasi sekolah.
4. **Sosialisasi & Workshop Pengguna**: Pelatihan operasional bagi Guru BK dan Kepala Sekolah pada sekolah-sekolah pilot project di Kota Bukittinggi.

---
*Dokumen ini disusun sebagai ringkasan teknis komprehensif atas seluruh arsitektur, kode, dan fitur yang telah berhasil diimplementasikan dalam repositori proyek SAHABAT SEKOLAH.*
