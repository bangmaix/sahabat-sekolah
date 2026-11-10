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

### A. Tampilan Kategori Kejadian (Tombol Ringkas dalam Grid 2 Kolom)
Elemen `<select name="category">` dan kartu tebal (*card*) dihapus, digantikan oleh tombol ringkas (*compact buttons*) dalam grid 2 kolom (`grid grid-cols-1 sm:grid-cols-2 gap-2`):
- `<input type="hidden" name="category" id="category-input" value="{{ old('category') }}" required>`
- `<input type="hidden" name="category_id" id="category-id-input" value="{{ old('category_id') }}">`
- Setiap kategori resmi dari database (`$categories`) berwujud **tombol ramping** dengan ikon emoji dan nama kategori:
  - 🥊 `Perundungan Fisik` (ID #1)
  - 🗣️ `Perundungan Verbal` (ID #2)
  - 👥 `Perundungan Sosial` (ID #3)
  - 📱 `Cyberbullying` (ID #4)
  - ⚠️ `Perundungan Bernuansa Seksual` (ID #5)
  - 📌 `Lainnya` (ID #6)
- Saat dipilih, tombol langsung berubah warna biru aktif (`bg-blue-600 text-white border-blue-600`) secara instan.

### B. Tampilan Subkategori Menyesuaikan Kategori (Terkait ID Database)
Subkategori tidak lagi dirender rata (*flat list*) bercampur dalam satu baris, melainkan dikelompokkan secara hierarkis per `category_id` dari database:
- `<input type="hidden" name="subcategory" id="subcategory-input" value="{{ old('subcategory') }}">`
- `<input type="hidden" name="subcategory_id" id="subcategory-id-input" value="{{ old('subcategory_id') }}">`
- Begitu salah satu tombol kategori diklik, muncul panel subkategori yang hanya menampilkan grup tombol subkategori milik kategori tersebut (`#subcat-group-{category_id}` dalam 2 kolom).
- Hanya subkategori milik kategori yang dipilih (`category_id` sesuai database) yang akan ditampilkan.
- Tombol subkategori mendukung fitur *toggle* (klik sekali untuk memilih, klik lagi untuk membatalkan jika pelapor hanya ingin melaporkan kategori umum).

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

