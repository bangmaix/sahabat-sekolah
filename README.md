# SAHABAT SEKOLAH

**Platform Pelaporan, Pencegahan, dan Penanganan Perundungan Sekolah Kota Bukittinggi**

SAHABAT SEKOLAH merupakan platform digital (*Case Management System*) untuk pencegahan, pelaporan, penanganan, pendampingan, dan pemantauan kasus perundungan pada satuan pendidikan di wilayah Kota Bukittinggi.

Sistem ini tidak sekadar berfungsi sebagai kotak pengaduan, melainkan sebagai sistem manajemen kasus secara menyeluruh mulai dari laporan masuk hingga kasus ditutup (resolved & closed).

## Arsitektur Sistem

Platform ini terdiri dari dua bagian utama:
1. **Mobile Application (Flutter)**: Digunakan oleh siswa (sebagai korban, saksi, atau pelapor umum) untuk membuat laporan perundungan dengan aman.
2. **Web Dashboard (Laravel)**: Digunakan oleh Guru BK (untuk manajemen kasus), Kepala Sekolah (untuk pemantauan), dan Administrator.

## Fitur Utama

- **Pelaporan & Identitas Aman**: Pelapor dapat memilih mode identitas pelaporan (Terbuka, Rahasia, atau Anonim). Laporan anonim dilengkapi dengan token khusus untuk komunikasi dua arah tanpa mengetahui identitas pelapor.
- **Risk Management Engine**: Penilaian tingkat risiko otomatis dan manual (Rendah, Sedang, Tinggi, Kritis) berdasarkan parameter keparahan, keselamatan, dampak, dan pengulangan.
- **SLA Engine**: Batas waktu penanganan kasus (SLA) dipantau secara otomatis untuk memastikan Guru BK segera merespons laporan.
- **Case Management**: Mengatur alur penanganan kasus dengan transisi status yang jelas (*Pending Response -> Under Verification -> In Handling -> Resolved -> Closed*).
- **Escalation Engine**: Eskalasi kasus (manual/otomatis) dari Guru BK kepada Kepala Sekolah jika memerlukan tindakan lebih lanjut atau SLA terlewati.
- **Audit Trail & Notifikasi**: Pencatatan aktivitas di dalam sistem serta notifikasi pengingat/update status kasus.

## Persyaratan Lingkungan (Requirements)

- PHP 8.3 atau lebih baru
- Composer
- Node.js & NPM
- PostgreSQL atau MySQL / MariaDB (Direkomendasikan PostgreSQL sesuai Blueprint)

## Instalasi dan Setup

1. **Clone repositori ini:**
   ```bash
   git clone <repository-url>
   cd sahabat-sekolah
   ```

2. **Install dependensi PHP dan Node.js:**
   ```bash
   composer install
   npm install
   ```

3. **Salin file `.env` dan atur konfigurasi database:**
   ```bash
   cp .env.example .env
   ```
   *Buka file `.env` dan sesuaikan kredensial `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` Anda.*

4. **Generate Application Key:**
   ```bash
   php artisan key:generate
   ```

5. **Jalankan Migrasi Database:**
   ```bash
   php artisan migrate
   ```

6. **Build Asset Frontend:**
   ```bash
   npm run build
   # atau untuk mode development:
   # npm run dev
   ```

7. **Jalankan Local Development Server:**
   ```bash
   php artisan serve
   ```

Aplikasi web sekarang dapat diakses melalui `http://localhost:8000`.
