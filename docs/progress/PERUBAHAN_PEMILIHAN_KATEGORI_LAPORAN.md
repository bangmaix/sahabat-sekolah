# Dokumentasi Perubahan UI/UX: Pemilihan Kategori & Subkategori Laporan Perundungan

**Tanggal**: 9 November 2026  
**Modul**: Formulir Pengaduan Publik (`reports.create`)  
**Berkas Terkait**: `resources/views/reports/create.blade.php`  
**Status**: Diimplementasikan & Diverifikasi  

---

## 1. Latar Belakang & Identifikasi Masalah

Sebelum pembaruan ini dilakukan, formulir pembuatan laporan perundungan memiliki dua kelemahan utama pada bagian penentuan kategori:

1. **Ketidaksesuaian Data dengan Database (*Data Mismatch*):**
   - Tombol pilihan cepat (*quick chips*) sebelumnya ditulis secara manual (*hardcoded*) dengan nama-nama yang **tidak terdaftar** di tabel `bullying_categories`, seperti `Sosial/Psikologis` *(di database: `Perundungan Sosial`)*, `Pemerasan`, dan `Indikasi/Kekhawatiran`.
   - Kategori resmi seperti `Perundungan Bernuansa Seksual` dan `Lainnya` justru tidak memiliki tombol pilihan cepat.
   - Akibatnya, saat pelapor mengklik tombol yang tidak sesuai, sistem tidak memilih data apa pun atau berisiko gagal saat divalidasi oleh backend (`exists:bullying_categories,name`).

2. **Redundansi Elemen Formulir (*Double Controls*):**
   - Di formulir terdapat tombol pilihan cepat di bagian atas, namun di bawahnya tetap terdapat elemen dropdown `<select name="category">` dan `<select name="subcategory">`.
   - Adanya dua kontrol untuk fungsi yang sama membingungkan pengguna (siswa), memakan ruang vertikal layar HP, dan menambah beban kognitif bagi siswa yang sedang dalam kondisi cemas atau tertekan saat ingin melapor.

---

## 2. Tujuan Pembaruan

- **Menghilangkan Redundansi**: Menghapus elemen dropdown `<select>` ganda dan menggantikannya dengan tombol kartu/chips interaktif yang langsung mengisi `<input type="hidden">`.
- **100% Sinkron dengan Database**: Menjamin seluruh pilihan cepat yang tampil bersumber langsung dari data aktif di tabel `bullying_categories` dan `bullying_subcategories`.
- **Pengalaman Pengguna Ramah Anak (*Child-Friendly UX*)**: Mempermudah siswa mengenali jenis perundungan yang dialaminya cukup dengan **1 kali klik (*one-tap selection*)** didampingi ikon emoji dan bahasa yang mudah dipahami.
- **Konsistensi Backend**: Tetap mengirim parameter `category` dan `subcategory` dalam format string yang identik sehingga tidak memerlukan perubahan controller, validasi, maupun struktur database.

---

## 3. Analisis: Mengapa Tombol/Chips + `<input type="hidden">` Lebih Masuk Akal?

| Kriteria | Dropdown `<select>` Konvensional | Tombol / Chips + Input Hidden (Terpilih) |
| :--- | :--- | :--- |
| **Psikologi Siswa** | Kaku, formal, terasa seperti tes/ujian birokrasi. | Ramah, suportif, dan intuitif melalui ikon visual. |
| **Langkah Interaksi** | Memerlukan 3–4 langkah (Tap dropdown &rarr; Buka wheel/modal picker native HP &rarr; Cari opsi &rarr; Tap selesai). | **Cukup 1 langkah (One-Tap Selection)** langsung memilih kategori. |
| **Keterbacaan Pilihan** | Opsi tersembunyi di balik menu tarik-turun (*hidden by default*). | Seluruh jenis kejadian terlihat langsung dalam satu pandangan (*scannable*). |
| **Karakteristik Data** | Cocok untuk opsi yang sangat banyak (puluhan hingga ratusan pilihan). | **Sangat ideal untuk kategori terbatas (6 kategori)** seperti data perundungan di Sahabat Sekolah. |
| **Kerapian Antarmuka** | Terlihat redundan jika berdampingan dengan tombol cepat. | Tampilan formulir menjadi bersih, modern, dan tidak ada elemen ganda. |

---

## 4. Rincian Perubahan Teknis

### A. Tampilan Kategori Kejadian
Elemen `<select name="category">` dihapus dan digantikan oleh:
- `<input type="hidden" name="category" id="category-input" value="{{ old('category') }}" required>`
- Daftar tombol interaktif yang di-*loop* langsung dari database (`$categories`):
  - 🥊 **Fisik** *(Pukulan / Kekerasan)* &rarr; `Perundungan Fisik`
  - 🗣️ **Verbal** *(Ejekan / Hinaan)* &rarr; `Perundungan Verbal`
  - 👥 **Sosial** *(Dikucilkan / Dijauhi)* &rarr; `Perundungan Sosial`
  - 📱 **Cyberbullying** *(Medsos / Chat)* &rarr; `Cyberbullying`
  - ⚠️ **Seksual** *(Pelecehan / Tidak Senonoh)* &rarr; `Perundungan Bernuansa Seksual`
  - 📌 **Lainnya** *(Bentuk Lainnya)* &rarr; `Lainnya`
- Label indikator realtime: Menampilkan teks `Terpilih: [Nama Kategori]` di sebelah label judul.

### B. Tampilan Subkategori Kejadian (Adaptif)
Elemen `<select name="subcategory">` dihapus dan digantikan oleh:
- `<input type="hidden" name="subcategory" id="subcategory-input" value="{{ old('subcategory') }}">`
- Wadah subkategori tersembunyi secara default, dan **otomatis terbuka hanya jika kategori yang dipilih memiliki subkategori**.
- Tombol subkategori mendukung fitur *toggle* (dapat diklik ulang untuk membatalkan pilihan jika pelapor merasa bentuk kejadian kurang spesifik).

### C. Validasi Sisi Klien & Restorasi Data Lama (*Old Input*)
- **Validasi Submit**: Jika pelapor menekan tombol *Kirim laporan* tanpa memilih kategori, form dicegah terkirim, pesan peringatan merah dimunculkan, dan layar otomatis bergulir (*smooth scroll*) ke area pemilihan kategori.
- **Dukungan `old()`**: Jika terjadi kesalahan validasi lain (misal panjang cerita kejadian kurang dari 20 karakter), JavaScript secara otomatis membaca nilai `old('category')` dan `old('subcategory')` lalu mengaktifkan kembali tombol yang sebelumnya dipilih pelapor.

---

## 5. Hasil Verifikasi

1. **Uji Render Template Blade**: Lolos tanpa sintaks error (`Rendered successfully`).
2. **Uji Validasi Backend**: Payload form tetap mengirim field `category` dan `subcategory`, sesuai aturan validasi `ReportController@store`:
   ```php
   'category' => ['required', 'string', 'max:255', 'exists:bullying_categories,name'],
   'subcategory' => ['nullable', 'string', 'max:255', 'exists:bullying_subcategories,name'],
   ```
3. **Standar Kode Laravel**: Lolos pemformatan kode Laravel Pint (`vendor/bin/pint --dirty --format agent`).

