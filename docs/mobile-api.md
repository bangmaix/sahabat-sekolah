# Dokumentasi API Mobile Sahabat Sekolah (v1)

Dokumentasi ini ditujukan bagi pengembang aplikasi mobile (Android / iOS / Flutter / React Native) untuk mengintegrasikan seluruh fitur platform **Sahabat Sekolah**, mencakup:
1. **Publik & Pelapor**: Pengaduan perundungan (Anonim, Rahasia, Terbuka), pelacakan laporan, dan saluran komunikasi anonim 2 arah.
2. **Pihak Sekolah (Guru BK & Kepala Sekolah)**: Autentikasi, ringkasan dashboard & statistik, manajemen kasus & aksi penanganan, pelibatan orang tua & pihak terlibat, notifikasi, serta pencatatan prestasi siswa.

---
## 1. Arsitektur, Pengamanan & Standar Request / Response

### 1.1. Base URL & Transport Security
```http
https://sahabatsekolah.example.com/api/v1
```
*(Seluruh komunikasi API wajib menggunakan protokol HTTPS terenkripsi TLS 1.2+ untuk mencegah penyadapan data di jaringan publik / sekolah)*

### 1.2. Format Header Standar
Untuk semua request:
```http
Accept: application/json
Content-Type: application/json
```
*(Khusus endpoint unggah file gambar/dokumen, gunakan `Content-Type: multipart/form-data`)*

Untuk endpoint yang membutuhkan autentikasi pihak sekolah:
```http
Authorization: Bearer <TOKEN_ANDA>
```

---

### 1.3. Sistem Pengamanan API (Security Mechanisms)
API Sahabat Sekolah menerapkan arsitektur pengamanan berlapis (*Defense-in-Depth*) untuk menjamin kerahasiaan identitas siswa dan integritas data penanganan kasus:

#### A. Pengamanan Autentikasi Pihak Sekolah (Guru BK & Kepala Sekolah)
1. **Bearer Token Authentication**:
   - Seluruh endpoint manajemen kasus, dashboard, notifikasi, dan data siswa diproteksi oleh middleware [`RequireApiToken`](file:///d:/DATA%20UUM/Project/www/sahabat-sekolah/app/Http/Middleware/RequireApiToken.php).
   - Pengguna wajib menyertakan header `Authorization: Bearer <TOKEN>`.
2. **Kriptografi Token (SHA-256 Hashing)**:
   - Server tidak pernah menyimpan token dalam bentuk teks polos (*plaintext*).
   - Token di-hash menggunakan algoritma `SHA-256` sebelum dicocokkan ke database (`api_tokens.token_hash`), sehingga jika terjadi kebocoran database, token tidak dapat dibaca oleh pihak lain.
3. **Masa Berlaku & Pencabutan Token (Token Expiration & Revocation)**:
   - Token memiliki masa berlaku (`expires_at`) dan mencatat waktu akses terakhir (`last_used_at`).
   - Token dapat langsung dibatalkan/dihapus seketika saat pengguna melakukan logout via endpoint `POST /api/v1/auth/logout`.
4. **Isolasi Data Sekolah (Multi-Tenant NPSN Scoping)**:
   - Setiap token secara ketat terikat dengan `school_npsn` akun pengguna.
   - Server secara otomatis memfilter query database (`scopedCase()`). Guru BK atau Kepala Sekolah dari suatu sekolah **mustahil dapat melihat, mengedit, atau mengakses kasus dari sekolah lain**.
5. **Role-Based Access Control (RBAC)**:
   - Terdapat pemisahan hak akses antara Guru BK (`COUNSELOR`), Kepala Sekolah (`PRINCIPAL`), dan Admin (`ADMIN`).

#### B. Pengamanan Pelapor & Pelaporan Publik (Perlindungan Korban/Saksi)
1. **Anonymous Token Shielding**:
   - Siswa atau saksi yang memilih mode `ANONYMOUS` tidak perlu membuat akun/password, sehingga tidak meninggalkan jejak identitas pribadi (email/nomor HP).
   - Sistem menerbitkan `anonymous_token` acak berkekuatan tinggi (12 karakter alfanumerik) yang menjadi satu-satunya kunci kriptografis untuk pelacakan laporan dan ruang chat 2 arah dengan Guru BK.
2. **Penyamaran Identitas Otomatis (Data Masking)**:
   - Pada semua respon API detail kasus untuk laporan bertipe `ANONYMOUS`, backend secara otomatis menyamarkan nama pelapor (`reporter_name: "Anonim (Disamarkan)"`) dan menghapus kontak (`reporter_contact: null`).
3. **Proteksi Saluran Pesan Anonim (Anonymous Channel Protection)**:
   - Komunikasi obrolan anonim hanya dapat diakses melalui URL unik bertoken (`/api/v1/public/anonymous/{token}/messages`), sehingga pihak luar tidak dapat mengintip atau menyusup ke obrolan.
4. **Proteksi Unggahan Berkas (Secure File Upload)**:
   - Unggahan berkas bukti dan dokumen resolusi divalidasi dengan whitelist MIME type (`jpg`, `jpeg`, `png`, `pdf`, `mp4`, `docx`) dan ukuran maksimal 10MB.
   - Berkas disimpan di storage privat yang terproteksi dan tidak dapat mengeksekusi skrip berbahaya.

#### C. Proteksi Serangan Jaringan (Abuse & Brute-Force Prevention)
1. **Rate Limiting**: Endpoint login dan pengaduan publik dilindungi pembatasan frekuensi request untuk mencegah serangan brute-force kata sandi dan spamming bot.
2. **Validasi Input Terpusat (Form Validation)**: Seluruh input divalidasi ketat (HTTP 422) sebelum diproses oleh database untuk mencegah serangan injeksi (SQLi / XSS).

---

### Struktur Respons Standar (Response Envelope)

#### 1. Respons Berhasil (`HTTP 200 / 201`)
```json
{
  "success": true,
  "message": "Pesan deskripsi jika relevan (opsional)",
  "data": {
    ...
  },
  "meta": {
    "total": 50,
    "page": 1,
    "per_page": 15,
    "last_page": 4
  }
}
```

#### 2. Respons Validasi Gagal (`HTTP 422 Unprocessable Content`)
```json
{
  "success": false,
  "message": "Data yang dikirim tidak valid.",
  "errors": {
    "category": [
      "Kategori perundungan wajib dipilih."
    ],
    "description": [
      "Deskripsi laporan minimal 10 karakter."
    ]
  }
}
```

#### 3. Respons Tidak Terautentikasi / Token Kedaluwarsa (`HTTP 401 Unauthorized`)
```json
{
  "success": false,
  "message": "Token tidak valid atau telah kedaluwarsa. Silakan login kembali."
}
```

#### 4. Respons Dilarang Akses (`HTTP 403 Forbidden`)
```json
{
  "success": false,
  "message": "Anda tidak memiliki hak akses untuk tindakan ini."
}
```

#### 5. Data Tidak Ditemukan (`HTTP 404 Not Found`)
```json
{
  "success": false,
  "message": "Kasus tidak ditemukan atau berada di luar sekolah Anda."
}
```

---

## 2. Ringkasan Endpoint

| Modul | Method | Endpoint | Akses | Keterangan |
| :--- | :--- | :--- | :--- | :--- |
| **Publik** | `GET` | `/public/schools` | Publik | Daftar sekolah (NPSN & nama sekolah) |
| **Publik** | `GET` | `/public/categories` | Publik | Daftar kategori & subkategori perundungan |
| **Publik** | `POST` | `/public/reports` | Publik | Kirim laporan pengaduan baru (Anonim / Rahasia / Terbuka) |
| **Publik** | `POST` | `/public/reports/track` | Publik | Lacak status laporan via kode tiket atau token anonim |
| **Publik** | `GET` | `/public/anonymous/{token}/messages` | Publik | Ambil riwayat chat anonim dengan Guru BK |
| **Publik** | `POST` | `/public/anonymous/{token}/messages` | Publik | Kirim pesan dari pelapor anonim ke Guru BK |
| **Auth** | `POST` | `/auth/login` | Publik | Login Guru BK / Kepala Sekolah (dapat token) |
| **Auth** | `POST` | `/auth/logout` | Auth | Hapus token aktif / Logout |
| **Auth** | `GET` | `/me` | Auth | Info profil dan role pengguna aktif |
| **Auth** | `POST` | `/auth/forgot-password` | Publik | Kirim tautan reset kata sandi ke email |
| **Auth** | `POST` | `/auth/reset-password` | Publik | Atur ulang kata sandi baru menggunakan token |
| **Dashboard**| `GET` | `/dashboard` | Auth | Ringkasan metrik dashboard, SLA & kasus terbaru |
| **Dashboard**| `GET` | `/dashboard/statistics` | Auth | Data agregasi statistik perundungan & tren |
| **Kasus** | `GET` | `/cases` | Auth | Daftar kasus sekolah dengan filter dan pagination |
| **Kasus** | `GET` | `/cases/{caseNumber}` | Auth | Detail kasus lengkap, dokumen, pihak terkait & log |
| **Kasus** | `POST` | `/cases/{caseNumber}/actions` | Auth | Proses aksi penanganan kasus (ubah status, SLA, bukti) |
| **Kasus** | `POST` | `/cases/{caseNumber}/notes` | Auth | Tambah catatan kasus / konseling mandiri |
| **Kasus** | `POST` | `/cases/{caseNumber}/escalate` | Auth | Eskalasi kasus ke Kepala Sekolah |
| **Kasus** | `POST` | `/cases/{caseNumber}/parent-involvement` | Auth | Catat atau perbarui pelibatan orang tua |
| **Kasus** | `POST` | `/cases/{caseNumber}/participants` | Auth | Tambah korban/pelaku/saksi (auto-lookup NISN) |
| **Kasus** | `POST` | `/cases/{caseNumber}/anonymous-reply` | Auth | Balas chat ke pelapor anonim |
| **Notifikasi**| `GET` | `/notifications` | Auth | Daftar notifikasi masuk (terurut waktu terbaru) |
| **Notifikasi**| `POST` | `/notifications/{id}/read` | Auth | Tandai 1 notifikasi telah dibaca |
| **Notifikasi**| `POST` | `/notifications/read-selected` | Auth | Tandai notifikasi terpilih telah dibaca |
| **Notifikasi**| `POST` | `/notifications/read-all` | Auth | Tandai semua notifikasi telah dibaca |
| **Siswa** | `GET` | `/students/search` | Auth | Cari siswa sekolah berdasarkan nama / NISN |
| **Prestasi** | `GET` | `/achievements` | Auth | Daftar catatan prestasi siswa sekolah |
| **Prestasi** | `GET` | `/achievements/{id}` | Auth | Detail pencatatan prestasi siswa |
| **Prestasi** | `POST` | `/achievements` | Auth | Simpan data pencapaian prestasi siswa baru |

---

## 3. Detail Endpoint: Pengaduan Publik

### 3.1. Daftar Sekolah
Mengambil daftar sekolah yang terdaftar dalam sistem untuk pilihan pada dropdown pelapor.

- **URL**: `GET /api/v1/public/schools`
- **Akses**: Publik
- **Contoh Response (`200 OK`)**:
```json
{
  "success": true,
  "data": [
    {
      "npsn": "10307412",
      "name": "SMP Negeri 2 Bukittinggi",
      "status": "NEGERI",
      "total_students": 720
    },
    {
      "npsn": "10307413",
      "name": "SMP Negeri 1 Bukittinggi",
      "status": "NEGERI",
      "total_students": 850
    }
  ]
}
```

---

### 3.2. Kategori Perundungan
Mengambil taksonomi kategori perundungan beserta subkategori dan deskripsinya.

- **URL**: `GET /api/v1/public/categories`
- **Akses**: Publik
- **Contoh Response (`200 OK`)**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "code": "PHYSICAL",
      "name": "Perundungan Fisik",
      "description": "Tindakan fisik yang menyebabkan rasa sakit atau intimidasi",
      "status": true,
      "subcategories": [
        {
          "id": 1,
          "code": "PUSHING",
          "name": "Mendorong atau menjegal",
          "default_risk_level": "MEDIUM",
          "risk_score": 35
        },
        {
          "id": 2,
          "code": "HITTING",
          "name": "Memukul atau menendang",
          "default_risk_level": "HIGH",
          "risk_score": 60
        }
      ]
    },
    {
      "id": 4,
      "code": "CYBER",
      "name": "Cyberbullying",
      "description": "Perundungan melalui media digital",
      "status": true,
      "subcategories": [
        {
          "id": 6,
          "code": "DIGITAL_THREAT",
          "name": "Ancaman digital",
          "default_risk_level": "HIGH",
          "risk_score": 60
        }
      ]
    }
  ]
}
```

---

### 3.3. Kirim Pengaduan Baru (Submit Report)
Digunakan oleh siswa, saksi, orang tua, atau warga sekolah untuk mengirim laporan pengaduan perundungan baru.

- **URL**: `POST /api/v1/public/reports`
- **Akses**: Publik
- **Content-Type**: `multipart/form-data` (jika melampirkan file) atau `application/json`

#### Parameter Body:
| Field | Tipe | Wajib | Keterangan |
| :--- | :--- | :--- | :--- |
| `school_npsn` | string | **Ya** | NPSN Sekolah tujuan pengaduan |
| `reporter_role` | string | **Ya** | Pilihan: `STUDENT`, `VICTIM`, `WITNESS`, `PARENT`, `TEACHER`, `OTHER` |
| `identity_mode` | string | **Ya** | Pilihan: `ANONYMOUS`, `CONFIDENTIAL`, `OPEN` |
| `reporter_name` | string | Kondisional | Wajib jika mode `OPEN` atau `CONFIDENTIAL` |
| `reporter_contact`| string | Tidak | Kontak pelapor (nomor WhatsApp/telepon) |
| `category` | string | **Ya** | Nama atau kode kategori perundungan (mis: `Cyberbullying`, `Perundungan Fisik`) |
| `subcategory` | string | Tidak | Subkategori perundungan |
| `description` | string | **Ya** | Uraian kronologi kejadian (min 10 karakter) |
| `incident_date` | string (date) | Tidak | Tanggal peristiwa terjadi (`YYYY-MM-DD`) |
| `incident_location`| string | Tidak | Lokasi kejadian (misal: "Kantin belakang", "Grup WhatsApp kelas") |
| `attachments[]` | file[] | Tidak | Berkas bukti foto/dokumen (Maksimal 5 file, maks 10MB per file: jpg, jpeg, png, pdf, mp4) |

#### Contoh Request (JSON Payload tanpa file):
```json
{
  "school_npsn": "10307412",
  "reporter_role": "STUDENT",
  "identity_mode": "ANONYMOUS",
  "category": "Cyberbullying",
  "description": "Ada grup chat yang menyebarkan editan foto saya dan menjelek-jelekkan di media sosial.",
  "incident_location": "Instagram & WhatsApp"
}
```

#### Contoh Response (`201 Created` - Mode ANONYMOUS):
```json
{
  "success": true,
  "message": "Pengaduan Anda telah kami terima dengan aman.",
  "data": {
    "case_number": "SS-2026-000108",
    "status": "PENDING_RESPONSE",
    "identity_mode": "ANONYMOUS",
    "category": "Cyberbullying",
    "anonymous_token": "a1b2c3d4e5f6",
    "anonymous_channel_url": "/api/v1/public/anonymous/a1b2c3d4e5f6/messages",
    "note": "SIMPAN KODE INI DENGAN AMAN. Gunakan kode ini untuk memantau status atau bertukar pesan dengan Guru BK tanpa mengungkap identitas Anda."
  }
}
```

> **PENTING UNTUK DEVELOPER MOBILE**:
> Jika `identity_mode` adalah `ANONYMOUS`, simpan `anonymous_token` di local storage aplikasi mobile (mis: EncryptedSharedPreferences / Keychain) agar pelapor dapat terus membuka ruang chat konsultasi anonim.

---

### 3.4. Lacak Pengaduan (Tracking)
Melacak status terkini penanganan laporan berdasarkan nomor tiket (`case_number`) atau token anonim (`anonymous_token`).

- **URL**: `POST /api/v1/public/reports/track`
- **Akses**: Publik
- **Body Request**:
```json
{
  "tracking_code": "a1b2c3d4e5f6"
}
```
*(Bisa diisi token anonim 12-karakter atau nomor tiket kasus seperti `SS-2026-000108`)*

- **Contoh Response (`200 OK`)**:
```json
{
  "success": true,
  "data": {
    "case_number": "SS-2026-000108",
    "status": "UNDER_VERIFICATION",
    "category": "Cyberbullying",
    "is_anonymous": true,
    "has_anonymous_channel": true,
    "timeline": [
      {
        "status": "PENDING_RESPONSE",
        "description": "Laporan berhasil diterima oleh sistem.",
        "time": "2026-10-22 08:30:00"
      },
      {
        "status": "UNDER_VERIFICATION",
        "description": "Laporan sedang diverifikasi oleh Guru BK.",
        "time": "2026-10-22 08:45:00"
      }
    ]
  }
}
```

---

### 3.5. Saluran Obrolan Anonim (Anonymous Channel)

#### A. Mengambil Daftar Pesan Chat
- **URL**: `GET /api/v1/public/anonymous/{token}/messages`
- **Akses**: Publik (dengan token pelapor)
- **Contoh Response (`200 OK`)**:
```json
{
  "success": true,
  "data": {
    "case_number": "SS-2026-000108",
    "status": "UNDER_VERIFICATION",
    "messages": [
      {
        "id": 1,
        "sender": "REPORTER",
        "message": "Halo Bu Guru BK, saya sangat takut pergi ke sekolah.",
        "created_at": "2026-10-22T08:35:10+07:00"
      },
      {
        "id": 2,
        "sender": "COUNSELOR",
        "message": "Halo Nak, tenang ya. Identitas kamu aman bersama kami. Apakah kamu memiliki tangkapan layarnya?",
        "created_at": "2026-10-22T08:40:22+07:00"
      }
    ]
  }
}
```

#### B. Mengirim Pesan dari Pelapor ke Guru BK
- **URL**: `POST /api/v1/public/anonymous/{token}/messages`
- **Akses**: Publik (dengan token pelapor)
- **Body Request**:
```json
{
  "message": "Iya bu, nanti saya kirimkan buktinya."
}
```
- **Contoh Response (`201 Created`)**:
```json
{
  "success": true,
  "message": "Pesan Anda berhasil dikirim ke Guru BK.",
  "data": {
    "id": 3,
    "sender": "REPORTER",
    "message": "Iya bu, nanti saya kirimkan buktinya.",
    "created_at": "2026-10-22T08:42:00+07:00"
  }
}
```

---

## 4. Detail Endpoint: Autentikasi Pihak Sekolah

### 4.1. Login Guru BK / Kepala Sekolah
Mengautentikasi pengguna dan mengembalikan token API (Bearer).

- **URL**: `POST /api/v1/auth/login`
- **Akses**: Publik
- **Body Request**:
```json
{
  "email": "guru.bk@smpn2bukittinggi.sch.id",
  "password": "password123",
  "device_name": "Xiaomi Redmi Note 13 (Android 14)"
}
```
*(Parameter `device_name` bersifat opsional)*

- **Contoh Response (`200 OK`)**:
```json
{
  "success": true,
  "message": "Login berhasil.",
  "data": {
    "token": "4f89d31a5b8824e4d5885fbe7c...",
    "token_type": "Bearer",
    "user": {
      "id": 5,
      "name": "Ratna Sari, S.Pd",
      "email": "guru.bk@smpn2bukittinggi.sch.id",
      "role": "COUNSELOR",
      "school_npsn": "10307412",
      "school_name": "SMP Negeri 2 Bukittinggi"
    }
  }
}
```

---

### 4.2. Profil Pengguna Aktif (Me)
- **URL**: `GET /api/v1/me`
- **Akses**: Membutuhkan `Authorization: Bearer <TOKEN>`
- **Contoh Response (`200 OK`)**:
```json
{
  "success": true,
  "data": {
    "id": 5,
    "name": "Ratna Sari, S.Pd",
    "email": "guru.bk@smpn2bukittinggi.sch.id",
    "role": "COUNSELOR",
    "school_npsn": "10307412",
    "school_name": "SMP Negeri 2 Bukittinggi"
  }
}
```

---

### 4.3. Logout
Menghapus token yang sedang digunakan.

- **URL**: `POST /api/v1/auth/logout`
- **Akses**: Membutuhkan `Authorization: Bearer <TOKEN>`
- **Contoh Response (`200 OK`)**:
```json
{
  "success": true,
  "message": "Logout berhasil. Token telah dicabut."
}
```

---

### 4.4. Minta Tautan Reset Kata Sandi (Forgot Password)
Mengirimkan tautan reset kata sandi ke alamat email terdaftar melalui antrean pengiriman email (Laravel Queue).

- **URL**: `POST /api/v1/auth/forgot-password`
- **Akses**: Publik
- **Body Request**:
```json
{
  "email": "guru.bk@smpn2bukittinggi.sch.id"
}
```
- **Contoh Response (`200 OK`)**:
```json
{
  "success": true,
  "message": "Jika alamat email terdaftar, tautan reset password telah dikirimkan ke email Anda."
}
```

---

### 4.5. Atur Ulang Kata Sandi (Reset Password)
Mengubah kata sandi akun dengan token verifikasi yang diterima melalui email (masa berlaku 60 menit).

- **URL**: `POST /api/v1/auth/reset-password`
- **Akses**: Publik
- **Body Request**:
```json
{
  "email": "guru.bk@smpn2bukittinggi.sch.id",
  "token": "64_karakter_token_dari_email",
  "password": "PasswordBaru123#",
  "password_confirmation": "PasswordBaru123#"
}
```
- **Contoh Response (`200 OK`)**:
```json
{
  "success": true,
  "message": "Kata sandi berhasil diperbarui. Silakan login kembali dengan kata sandi baru Anda."
}
```

---

## 5. Detail Endpoint: Dashboard & Analitik

### 5.1. Dashboard Overview
Menampilkan data ringkasan kartu statistik di halaman beranda Guru BK / Kepala Sekolah.

- **URL**: `GET /api/v1/dashboard`
- **Akses**: Membutuhkan `Authorization: Bearer <TOKEN>`
- **Contoh Response (`200 OK`)**:
```json
{
  "success": true,
  "data": {
    "school": {
      "npsn": "10307412",
      "name": "SMP Negeri 2 Bukittinggi"
    },
    "counts": {
      "total_cases": 24,
      "active_cases": 5,
      "resolved_cases": 18,
      "critical_risk_cases": 1,
      "unread_notifications": 3
    },
    "sla": {
      "compliance_rate": 91.6,
      "breached_count": 2,
      "average_response_hours": 3.4
    },
    "categories": [
      { "name": "Cyberbullying", "total": 10 },
      { "name": "Perundungan Fisik", "total": 8 },
      { "name": "Perundungan Verbal", "total": 6 }
    ],
    "recent_cases": [
      {
        "case_number": "SS-2026-000108",
        "category": "Cyberbullying",
        "risk_level": "HIGH",
        "status": "UNDER_VERIFICATION",
        "created_at": "2026-10-22 08:30:00"
      }
    ]
  }
}
```

---

### 5.2. Statistik Analitik Lanjutan
- **URL**: `GET /api/v1/dashboard/statistics`
- **Akses**: Membutuhkan `Authorization: Bearer <TOKEN>`
- **Query Parameter (Opsional)**:
  - `start_date`: Tanggal awal filter (`YYYY-MM-DD`)
  - `end_date`: Tanggal akhir filter (`YYYY-MM-DD`)

- **Contoh Response (`200 OK`)**:
```json
{
  "success": true,
  "data": {
    "monthly_trend": [
      { "month": "2026-08", "total": 7 },
      { "month": "2026-09", "total": 11 },
      { "month": "2026-10", "total": 6 }
    ],
    "risk_distribution": {
      "LOW": 4,
      "MEDIUM": 12,
      "HIGH": 7,
      "CRITICAL": 1
    },
    "status_distribution": {
      "PENDING_RESPONSE": 1,
      "UNDER_VERIFICATION": 2,
      "IN_HANDLING": 2,
      "RESOLVED": 15,
      "CLOSED": 4
    }
  }
}
```

---

## 6. Detail Endpoint: Kasus Perundungan

### 6.1. Daftar Kasus (List Cases)
- **URL**: `GET /api/v1/cases`
- **Akses**: Membutuhkan `Authorization: Bearer <TOKEN>`
- **Query Parameter (Opsional)**:
  - `search`: Pencarian nomor kasus, deskripsi, atau nama pelapor
  - `status`: Filter status (`PENDING_RESPONSE`, `UNDER_VERIFICATION`, `IN_HANDLING`, `RESOLVED`, `CLOSED`)
  - `risk_level`: Filter risiko (`LOW`, `MEDIUM`, `HIGH`, `CRITICAL`)
  - `category`: Filter kategori perundungan
  - `identity_mode`: Filter mode identitas (`ANONYMOUS`, `CONFIDENTIAL`, `OPEN`)
  - `per_page`: Jumlah data per halaman (default 15, max 100)
  - `page`: Halaman ke-N

- **Contoh Response (`200 OK`)**:
```json
{
  "success": true,
  "data": [
    {
      "id": 14,
      "case_number": "SS-2026-000108",
      "category": "Cyberbullying",
      "risk_level": "HIGH",
      "status": "UNDER_VERIFICATION",
      "identity_mode": "ANONYMOUS",
      "reporter_name": "Anonim (Disamarkan)",
      "opened_at": "2026-10-22 08:30:00",
      "response_status": "COMPLIED",
      "resolution_status": "ON_TRACK",
      "overdue_at": "2026-10-25 08:30:00"
    }
  ],
  "meta": {
    "total": 1,
    "page": 1,
    "per_page": 15,
    "last_page": 1
  }
}
```

---

### 6.2. Detail Kasus Lengkap (Case Details)
- **URL**: `GET /api/v1/cases/{caseNumber}`
- **Akses**: Membutuhkan `Authorization: Bearer <TOKEN>`
- **Contoh Response (`200 OK`)**:
```json
{
  "success": true,
  "data": {
    "case": {
      "id": 14,
      "case_number": "SS-2026-000108",
      "category": "Cyberbullying",
      "risk_level": "HIGH",
      "status": "UNDER_VERIFICATION",
      "identity_mode": "ANONYMOUS",
      "reporter_role": "STUDENT",
      "reporter_name": "Anonim (Disamarkan)",
      "reporter_contact": null,
      "description": "Ada grup chat yang menyebarkan editan foto saya...",
      "incident_date": "2026-10-21",
      "incident_location": "Instagram & WhatsApp",
      "opened_at": "2026-10-22 08:30:00"
    },
    "status_history": [
      {
        "from_status": "PENDING_RESPONSE",
        "to_status": "UNDER_VERIFICATION",
        "changed_by": "Ratna Sari, S.Pd",
        "reason": "Mulai verifikasi akun penyebar",
        "created_at": "2026-10-22 08:45:00"
      }
    ],
    "notes": [
      {
        "id": 21,
        "note_type": "INTERNAL",
        "content": "Akun target teridentifikasi milik siswa kelas 8B.",
        "created_by": "Ratna Sari, S.Pd",
        "created_at": "2026-10-22 09:00:00"
      }
    ],
    "evidences": [
      {
        "id": 4,
        "file_name": "screenshot-chat.jpg",
        "file_path": "case-documents/abc1234.jpg",
        "uploaded_by": "Ratna Sari, S.Pd",
        "created_at": "2026-10-22 09:05:00"
      }
    ],
    "parent_involvements": [],
    "participants": [],
    "escalations": [],
    "anonymous_messages": [
      {
        "id": 1,
        "sender_type": "REPORTER",
        "message": "Halo Bu Guru BK...",
        "created_at": "2026-10-22 08:35:10"
      }
    ]
  }
}
```

---

### 6.3. Eksekusi Aksi Penanganan Kasus (Process Action)
Mengubah alur status penanganan, mencatat riwayat tindakan SLA, menyertakan catatan, dan melampirkan berkas resolusi/bukti.

- **URL**: `POST /api/v1/cases/{caseNumber}/actions`
- **Akses**: Membutuhkan `Authorization: Bearer <TOKEN>`
- **Content-Type**: `multipart/form-data` atau `application/json`

#### Parameter Body:
| Field | Tipe | Wajib | Keterangan |
| :--- | :--- | :--- | :--- |
| `status` | string | **Ya** | Status tujuan: `UNDER_VERIFICATION`, `IN_HANDLING`, `RESOLVED`, `CLOSED` |
| `reason` | string | Tidak | Alasan perubahan status / justifikasi SLA |
| `note_type` | string | Tidak | Jenis catatan (default `INTERNAL`): `VERIFICATION`, `HANDLING`, `COUNSELING`, `FOLLOW_UP`, `INTERNAL` |
| `note_content` | string | Tidak | Teks catatan penanganan |
| `document_file` | file | Tidak | Berkas lampiran bukti / berita acara resolusi (pdf, jpg, png, docx maks 10MB) |
| `document_type` | string | Tidak | `EVIDENCE` atau `RESOLUTION` (otomatis jika status `RESOLVED`/`CLOSED`) |

#### Contoh Request:
```json
{
  "status": "IN_HANDLING",
  "reason": "Bukti telah diverifikasi dan siap dilakukan mediasi antar pihak.",
  "note_type": "HANDLING",
  "note_content": "Pihak orang tua kedua belah pihak diundang untuk mediasi besok pagi pukul 09.00 WIB."
}
```

- **Contoh Response (`200 OK`)**:
```json
{
  "success": true,
  "message": "Aksi penanganan kasus berhasil diproses.",
  "data": {
    "case_number": "SS-2026-000108",
    "status": "IN_HANDLING",
    "updated_at": "2026-10-22 09:15:00"
  }
}
```

---

### 6.4. Tambah Catatan Kasus / Konseling (Add Note)
- **URL**: `POST /api/v1/cases/{caseNumber}/notes`
- **Akses**: Membutuhkan `Authorization: Bearer <TOKEN>`
- **Body Request**:
```json
{
  "note_type": "COUNSELING",
  "content": "Siswa korban telah diberikan pendampingan psikologis awal dan merasa lebih tenang."
}
```
*(Pilihan `note_type`: `INTERNAL`, `COUNSELING`, `PARENT_COMMUNICATION`, `MEDIATION`, `OTHER`)*

- **Contoh Response (`201 Created`)**:
```json
{
  "success": true,
  "message": "Catatan kasus berhasil disimpan.",
  "data": {
    "id": 22,
    "note_type": "COUNSELING",
    "content": "Siswa korban telah diberikan pendampingan psikologis awal...",
    "created_at": "2026-10-22T09:20:00+07:00"
  }
}
```

---

### 6.5. Eskalasi ke Kepala Sekolah (Escalate Case)
- **URL**: `POST /api/v1/cases/{caseNumber}/escalate`
- **Akses**: Membutuhkan `Authorization: Bearer <TOKEN>`
- **Body Request**:
```json
{
  "reason": "Dampak perundungan menyebabkan luka fisik dan membutuhkan penindakan tingkat pimpinan sekolah."
}
```
- **Contoh Response (`200 OK`)**:
```json
{
  "success": true,
  "message": "Kasus berhasil dieskalasikan ke Kepala Sekolah."
}
```

---

### 6.6. Catat / Perbarui Pelibatan Orang Tua (Parent Involvement)
- **URL**: `POST /api/v1/cases/{caseNumber}/parent-involvement`
- **Akses**: Membutuhkan `Authorization: Bearer <TOKEN>`
- **Body Request**:
```json
{
  "parent_name": "Bapak Hendra (Orang Tua Korban)",
  "parent_contact": "081234567890",
  "status": "CONTACTED",
  "reason": "Telah dihubungi via telepon dan bersedia hadir untuk mediasi di sekolah."
}
```
*(Pilihan `status`: `NOT_REQUIRED`, `PENDING`, `APPROVED`, `CONTACTED`, `COMPLETED`)*

- **Contoh Response (`200 OK`)**:
```json
{
  "success": true,
  "message": "Pelibatan orang tua berhasil diperbarui."
}
```

---

### 6.7. Tambah Pihak Terlibat (Participants)
Menghubungkan siswa (korban, terlapor, saksi) ke dalam berkas kasus dengan auto-lookup NISN siswa.

- **URL**: `POST /api/v1/cases/{caseNumber}/participants`
- **Akses**: Membutuhkan `Authorization: Bearer <TOKEN>`
- **Body Request**:
```json
{
  "participant_type": "ALLEGED_PERPETRATOR",
  "display_name": "Deni Pratama",
  "student_nisn": "0081234567",
  "identity_visibility": "CASE_RESTRICTED"
}
```
*(Pilihan `participant_type`: `VICTIM`, `ALLEGED_PERPETRATOR`, `WITNESS`, `REPORTER`, `OTHER`)*  
*(Pilihan `identity_visibility`: `CASE_RESTRICTED`, `CASE_FULL`, `CASE_SENSITIVE`)*

- **Contoh Response (`201 Created`)**:
```json
{
  "success": true,
  "message": "Pihak terlibat berhasil ditambahkan.",
  "data": {
    "id": 8,
    "display_name": "Deni Pratama",
    "participant_type": "ALLEGED_PERPETRATOR"
  }
}
```

---

### 6.8. Balas Pesan ke Pelapor Anonim
Memungkinkan Guru BK mengirim pesan tanggapan langsung ke pelapor yang melapor secara anonim tanpa membuka identitasnya.

- **URL**: `POST /api/v1/cases/{caseNumber}/anonymous-reply`
- **Akses**: Membutuhkan `Authorization: Bearer <TOKEN>`
- **Body Request**:
```json
{
  "message": "Terima kasih informasinya Nak. Kami telah memproses laporanmu dan situasi di kelas sekarang dipantau ketat oleh guru."
}
```
- **Contoh Response (`201 Created`)**:
```json
{
  "success": true,
  "message": "Pesan tanggapan berhasil dikirim ke pelapor anonim.",
  "data": {
    "id": 9,
    "sender_type": "COUNSELOR",
    "message": "Terima kasih informasinya Nak...",
    "created_at": "2026-10-22T09:30:00+07:00"
  }
}
```

---

## 7. Detail Endpoint: Notifikasi

### 7.1. Daftar Notifikasi (List Notifications)
Menampilkan daftar notifikasi sekolah, diurutkan berdasarkan `created_at` menurun (terbaru berada di atas).

- **URL**: `GET /api/v1/notifications`
- **Akses**: Membutuhkan `Authorization: Bearer <TOKEN>`
- **Query Parameter (Opsional)**:
  - `unread_only`: Set `1` atau `true` untuk hanya menampilkan notifikasi yang belum dibaca.
  - `per_page`: Jumlah data per halaman (default 20).
  - `page`: Nomor halaman.

- **Contoh Response (`200 OK`)**:
```json
{
  "success": true,
  "data": [
    {
      "id": 45,
      "type": "NEW_REPORT",
      "priority": "HIGH",
      "title": "Laporan Baru Masuk",
      "message": "Laporan pengaduan baru SS-2026-000108 (Cyberbullying) membutuhkan respons awal.",
      "case_number": "SS-2026-000108",
      "is_read": false,
      "read_at": null,
      "created_at": "2026-10-22 08:30:00"
    },
    {
      "id": 44,
      "type": "MANUAL_ESCALATION",
      "priority": "URGENT",
      "title": "Kasus dieskalasikan",
      "message": "Kasus SS-2026-000092 dieskalasikan ke Kepala Sekolah.",
      "case_number": "SS-2026-000092",
      "is_read": true,
      "read_at": "2026-10-22 08:40:00",
      "created_at": "2026-10-21 14:15:00"
    }
  ],
  "meta": {
    "unread_count": 1,
    "total": 2,
    "page": 1,
    "per_page": 20,
    "last_page": 1
  }
}
```

---

### 7.2. Tandai 1 Notifikasi Dibaca
- **URL**: `POST /api/v1/notifications/{id}/read`
- **Akses**: Membutuhkan `Authorization: Bearer <TOKEN>`
- **Contoh Response (`200 OK`)**:
```json
{
  "success": true,
  "message": "Notifikasi telah ditandai dibaca."
}
```

---

### 7.3. Tandai Notifikasi Terpilih Dibaca (Read Selected)
Memungkinkan pengguna menandai beberapa notifikasi yang dipilih (checkbox di mobile) sebagai telah dibaca dalam satu panggilan.

- **URL**: `POST /api/v1/notifications/read-selected`
- **Akses**: Membutuhkan `Authorization: Bearer <TOKEN>`
- **Body Request**:
```json
{
  "ids": [45, 46, 47]
}
```
- **Contoh Response (`200 OK`)**:
```json
{
  "success": true,
  "message": "3 notifikasi terpilih telah ditandai dibaca.",
  "data": {
    "updated_count": 3
  }
}
```

---

### 7.4. Tandai Semua Notifikasi Dibaca (Read All)
- **URL**: `POST /api/v1/notifications/read-all`
- **Akses**: Membutuhkan `Authorization: Bearer <TOKEN>`
- **Contoh Response (`200 OK`)**:
```json
{
  "success": true,
  "message": "Semua notifikasi telah ditandai dibaca.",
  "data": {
    "updated_count": 5
  }
}
```

---

## 8. Detail Endpoint: Pencarian Siswa & Prestasi

### 8.1. Cari Data Siswa (Search Students)
- **URL**: `GET /api/v1/students/search`
- **Akses**: Membutuhkan `Authorization: Bearer <TOKEN>`
- **Query Parameter**:
  - `q`: Kata kunci pencarian nama atau NISN (minimal 2 karakter)

- **Contoh Response (`200 OK`)**:
```json
{
  "success": true,
  "data": [
    {
      "id": 102,
      "student_number": "0081234567",
      "full_name": "Ahmad Fauzi",
      "grade_level": "8",
      "class_name": "VIII-A",
      "gender": "L"
    }
  ]
}
```

---

### 8.2. Daftar Catatan Prestasi Siswa
- **URL**: `GET /api/v1/achievements`
- **Akses**: Membutuhkan `Authorization: Bearer <TOKEN>`
- **Query Parameter (Opsional)**:
  - `search`: Pencarian nama siswa, NISN, atau judul prestasi
  - `level`: Tingkat lomba (`SEKOLAH`, `KECAMATAN`, `KOTA`, `PROVINSI`, `NASIONAL`, `INTERNASIONAL`)
  - `field`: Bidang (`AKADEMIK`, `OLAHRAGA`, `SENI`, `KEPEMIMPINAN`, `LAINNYA`)
  - `per_page`: Jumlah data per halaman (default 15)

- **Contoh Response (`200 OK`)**:
```json
{
  "success": true,
  "data": [
    {
      "id": 12,
      "student_name": "Ahmad Fauzi",
      "student_nisn": "0081234567",
      "title": "Juara 1 Olimpiade Matematika Sains",
      "level": "KOTA",
      "field": "AKADEMIK",
      "rank": "JUARA_1",
      "event_date": "2026-10-15",
      "academic_period": "2026/2027 Ganjil"
    }
  ],
  "meta": {
    "total": 1,
    "page": 1,
    "per_page": 15,
    "last_page": 1
  }
}
```

---

### 8.3. Simpan Prestasi Siswa Baru
- **URL**: `POST /api/v1/achievements`
- **Akses**: Membutuhkan `Authorization: Bearer <TOKEN>`
- **Content-Type**: `multipart/form-data` atau `application/json`

#### Parameter Body:
| Field | Tipe | Wajib | Keterangan |
| :--- | :--- | :--- | :--- |
| `student_name` | string | **Ya** | Nama lengkap siswa |
| `student_nisn` | string | Tidak | NISN siswa (opsional) |
| `title` | string | **Ya** | Judul / nama kejuaraan prestasi |
| `field` | string | **Ya** | Pilihan: `AKADEMIK`, `OLAHRAGA`, `SENI`, `KEPEMIMPINAN`, `LAINNYA` |
| `level` | string | **Ya** | Pilihan: `SEKOLAH`, `KECAMATAN`, `KOTA`, `PROVINSI`, `NASIONAL`, `INTERNASIONAL` |
| `rank` | string | **Ya** | Pilihan: `JUARA_1`, `JUARA_2`, `JUARA_3`, `HARAPAN_1`, `HARAPAN_2`, `FINALIS`, `PESERTA`, `LAINNYA` |
| `organizer` | string | Tidak | Penyelenggara kegiatan |
| `event_date` | string (date) | **Ya** | Tanggal perolehan prestasi (`YYYY-MM-DD`) |
| `certificate_file`| file | Tidak | Berkas scan piagam/sertifikat (pdf, jpg, png maks 5MB) |

- **Contoh Response (`201 Created`)**:
```json
{
  "success": true,
  "message": "Prestasi siswa berhasil dicatat.",
  "data": {
    "id": 13,
    "student_name": "Ahmad Fauzi",
    "title": "Juara 1 Lomba Pidato Bahasa Inggris",
    "rank": "JUARA_1",
    "created_at": "2026-10-22T09:40:00+07:00"
  }
}
```

---

## 9. Tips & Rekomendasi Integrasi Aplikasi Mobile (Security & Best Practices)

1. **Penyimpanan Kredensial yang Aman (Secure Storage)**:
   - Simpan Bearer Token Guru BK dan Anonymous Token Pelapor pada media penyimpanan terenkripsi:
     - **Flutter**: `flutter_secure_storage`
     - **Android (Kotlin/Java)**: `EncryptedSharedPreferences` / `Android KeyStore`
     - **iOS (Swift)**: `Keychain Services`
     - **React Native**: `react-native-keychain`
   - Hindari menyimpan token pada `SharedPreferences` biasa, `UserDefaults`, atau `AsyncStorage` tanpa enkripsi.

2. **Penanganan Token Kedaluwarsa (HTTP 401 Interceptor)**:
   - Pasang HTTP Client Interceptor (pada Axios / Dio / Retrofit). Jika backend merespons dengan kode `HTTP 401 Unauthorized`, aplikasi mobile harus segera:
     - Menghapus Bearer Token dari penyimpanan lokal.
     - Membersihkan state pengguna.
     - Menampilkan pemberitahuan sesi telah berakhir dan mengarahkan pengguna ke layar Login.

3. **Perlindungan Privasi Pelapor di Perangkat Mobile**:
   - Untuk layar chat anonim dan form laporan kasus sensitif, disarankan mengaktifkan `FLAG_SECURE` pada Android (atau `isSecureTextEntry` / pencegahan screenshot pada iOS) guna mencegah perekaman layar atau tangkapan layar yang tidak diinginkan di perangkat pengguna.

4. **Pembersihan Cache Saat Logout**:
   - Saat pengguna menekan tombol Logout, selain memanggil `POST /api/v1/auth/logout`, pastikan aplikasi mobile mengosongkan *in-memory cache* data kasus dan riwayat notifikasi lokal.

5. **Polling Saluran Pesan Anonim**:
   - Pada halaman chat anonim (`/public/anonymous/{token}/messages`), gunakan teknik polling berkala (interval 10–15 detik) hanya ketika layar chat sedang aktif berada di latar depan (*foreground*). Hentikan polling saat layar berpindah atau aplikasi masuk ke latar belakang (*background*).

6. **Offline & Network Error Handling**:
   - Tangani kondisi koneksi terputus dengan menampilkan UI ramah pengguna (*Empty State / Retry Button*) tanpa menampilkan *raw stack trace* teknis backend kepada pengguna akhir.
