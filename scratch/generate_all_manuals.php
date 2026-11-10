<?php

require_once __DIR__ . '/DocxBuilder.php';

$outputDir = dirname(__DIR__) . '/docs/panduan';
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0777, true);
}

echo "Generating User Manuals for Sahabat Sekolah...\n";

// =========================================================================
// 1. MANUAL GURU BK (COUNSELOR)
// =========================================================================
echo "Building: PANDUAN_PENGGUNAAN_GURU_BK.docx...\n";
$bk = new DocxBuilder(
    "PANDUAN OPERASIONAL & PENGGUNAAN APLIKASI",
    "MODUL GURU BIMBINGAN DAN KONSELING (COUNSELOR)",
    "Guru BK"
);

$bk->addTitle("SAHABAT SEKOLAH — KOTA BUKITTINGGI")
   ->addSubtitle("Panduan Operasional & Tata Cara Penggunaan Sistem bagi Guru Bimbingan dan Konseling (BK)")
   ->addMetaTable([
       "Nama Dokumen" => "Buku Panduan Pengguna — Peran Guru Bimbingan dan Konseling (COUNSELOR)",
       "Platform" => "SAHABAT SEKOLAH (Case Management System Penanganan Kekerasan)",
       "Wilayah Implementasi" => "Satuan Pendidikan (SD, SMP, MTs, MIN) Kota Bukittinggi",
       "Dasar Hukum" => "Permendikbudristek No. 46 Tahun 2023 (PPKSP) & Regulasi Pemkot Bukittinggi",
       "Peruntukan Role" => "Guru Bimbingan dan Konseling (BK) / Tim Pencegahan dan Penanganan Kekerasan (TPPK)",
       "Versi Panduan" => "1.0 (Oktober 2026)",
       "Klasifikasi Keamanan" => "Dokumen Kedinasan & Penanganan Kasus (Terbatas & Rahasia)",
   ]);

$bk->addHeading1("BAB I: GAMBARAN UMUM PLATFORM SAHABAT SEKOLAH");
$bk->addParagraph("Platform Sahabat Sekolah dikembangkan oleh Pemerintah Kota Bukittinggi sebagai Case Management System (CMS) terintegrasi untuk pencegahan dan penanganan kekerasan di lingkungan satuan pendidikan. Sistem ini bukan sekadar kotak aduan digital biasa, melainkan platform komprehensif yang mengawal penanganan kasus dari hulu ke hilir dengan mengedepankan prinsip Zero Fear Reporting, akuntabilitas waktu respons (Service Level Agreement), dan kerahasiaan identitas anak.");

$bk->addHeading2("1.1. Filosofi & Tiga Pilar Utama Sistem");
$bk->addBullet("Zero Fear Reporting (Pelaporan Bebas Rasa Takut):", "Peserta didik dapat memilih 3 mode identitas: Terbuka (Identified), Rahasia (Confidential - hanya Guru BK yang mengetahui), dan Anonim Kriptografis (Anonymous - menggunakan token 4 kata ramah anak tanpa data pribadi).");
$bk->addBullet("Akuntabilitas Waktu Respons (Strict SLA):", "Sistem memberlakukan batas waktu respons awal maksimal 1x24 jam hari kerja (atau maksimal 1 jam untuk situasi kritis) serta target penyelesaian kasus dalam 7 hari kerja yang dipantau secara otomatis oleh SLA scanner.");
$bk->addBullet("Penguatan Prestasi Positif:", "Menyeimbangkan penanganan perundungan dengan modul portofolio prestasi peserta didik di bidang akademik, olahraga, seni, keagamaan, dan kepemimpinan yang terhubung langsung dengan microservice AConnect Pemerintah Kota Bukittinggi.");

$bk->addHeading2("1.2. Keamanan & Isolasi Data Multi-Tenant");
$bk->addParagraph("Setiap sekolah diisolasi secara ketat berdasarkan Nomor Pokok Sekolah Nasional (NPSN). Guru BK hanya memiliki akses melihat, menangani, dan mengelola kasus yang terdaftar pada sekolah tempatnya bertugas. Data kasus antar-sekolah terpisah secara absolut sehingga kerahasiaan data anak terlindungi sepenuhnya.");

$bk->addCallout("Prinsip Utama Perlindungan Korban", "Kerahasiaan identitas anak (korban maupun saksi) adalah prioritas hukum tertinggi sesuai UU Perlindungan Anak dan Permendikbudristek 46/2023. Guru BK dilarang keras menyebarluaskan identitas, rekaman percakapan anonim, maupun bukti kasus di luar mekanisme resmi penanganan kasus.", "danger");

$bk->addHeading1("BAB II: HAK AKSES & WEWENANG GURU BK");
$bk->addParagraph("Dalam arsitektur Sahabat Sekolah, Guru Bimbingan dan Konseling memegang peran sentral sebagai Garda Terdepan (First Responder) penanganan kasus perundungan dan kekerasan.");

$bk->addTable(
    ["Fitur / Modul", "Wewenang Guru BK", "Batasan Akses"],
    [
        ["Laporan Masuk", "Menerima notifikasi instan, meninjau laporan masuk, memverifikasi bukti", "Hanya laporan pada NPSN sekolah sendiri"],
        ["Asesmen Risiko", "Menetapkan tingkat risiko: LOW, MEDIUM, HIGH, CRITICAL", "Disesuaikan dengan matriks indikator objektif"],
        ["Ruang Obrolan Anonim", "Berkomunikasi dua arah dengan pelapor anonim secara terenkripsi", "Identitas pelapor tetap terlindungi kriptografis"],
        ["Catatan Kasus", "Menulis log investigasi, konseling, mediasi, dan tindak lanjut", "Catatan bersifat rahasia dan teraudit"],
        ["Eskalasi Kasus", "Mengangkat kasus ke Kepala Sekolah, Dinas Pendidikan, atau UPTD PPA", "Wajib menyertakan alasan eskalasi objektif"],
        ["Penyelesaian Kasus", "Mengubah status menjadi RESOLVED dan mengunggah dokumen penyelesaian", "Status CLOSED mensyaratkan dokumen resmi"],
        ["Portofolio Prestasi", "Mencatat prestasi siswa dan melakukan sinkronisasi data AConnect", "Hanya siswa aktif di NPSN sekolah sendiri"]
    ],
    [2200, 4800, 2600]
);

$bk->addHeading1("BAB III: PANDUAN PENGGUNAAN LANGKAH DEMI LANGKAH");

$bk->addHeading2("3.1. Masuk ke Akun Guru BK");
$bk->addStep(1, "Akses Halaman Login", "Buka peramban (browser) dan ketik alamat portal Sahabat Sekolah, lalu klik menu 'Masuk Petugas' di pojok kanan atas.");
$bk->addStep(2, "Masukkan Kredensial Resmi", "Ketikkan alamat email kedinasan Anda (contoh: bk@sahabat.test) dan kata sandi yang telah diberikan.");
$bk->addStep(3, "Periksa Beranda Kasus", "Setelah berhasil masuk, Anda akan langsung diarahkan ke Dashboard Manajemen Kasus Sekolah Anda.");

$bk->addHeading2("3.2. Memahami Indikator Dashboard Guru BK");
$bk->addParagraph("Dashboard menyajikan gambaran cepat beban kerja dan kondisi penanganan kasus di sekolah:");
$bk->addBullet("Kartu Ringkasan Status:", "Menampilkan total kasus Menunggu Respons, Dalam Penanganan, Selesai, dan Kasus Berisiko Tinggi.");
$bk->addBullet("Indikator SLA (On-Time / Warning / Overdue):", "Warna hijau menandakan respons tepat waktu, kuning (Warning) menandakan sisa waktu respons kurang dari 4 jam, dan merah (Overdue) menandakan batas SLA telah terlampaui.");
$bk->addBullet("Filter Kasus Cepat:", "Memudahkan memfilter daftar kasus berdasarkan Kategori (Verbal, Fisik, Sosial, Siber, Seksual), Tingkat Risiko, dan Status.");

$bk->addHeading2("3.3. Alur Lengkap Penanganan Kasus Baru");
$bk->addStep(1, "Buka Rincian Kasus", "Klik tombol 'Buka Kasus' pada daftar laporan dengan status PENDING_RESPONSE.");
$bk->addStep(2, "Verifikasi Kronologi & Bukti", "Pelajari deskripsi laporan, periksa lampiran bukti tangkapan layar/foto, dan telusuri identitas pelapor (jika pelapor memilih mode Terbuka/Rahasia).");
$bk->addStep(3, "Respons Awal & Hentikan Penghitung SLA", "Klik tombol 'Mulai Verifikasi' untuk mengubah status menjadi UNDER_VERIFICATION. Tindakan ini secara otomatis mencatat waktu respons awal resmi sekolah.");
$bk->addStep(4, "Gunakan Ruang Obrolan Rahasia (Kasus Anonim)", "Jika pelapor berstatus anonim, kirimkan pesan sapaan hangat melalui tab Obrolan Anonim untuk menggali keterangan lebih mendalam tanpa menanyakan identitas aslinya.");
$bk->addStep(5, "Tulis Catatan Kasus Berkala", "Setiap tindakan konseling individual, pemanggilan saksi, atau mediasi wajib dicatat melalui panel 'Tambah Catatan Kasus' dengan memilih tipe catatan yang sesuai.");

$bk->addCallout("Status Menunggu Tanggapan Pelapor (Pause SLA)", "Bila Guru BK membutuhkan informasi tambahan dari pelapor anonim, ubah status ke 'WAITING_REPORTER_INFO'. Sistem akan menjeda penghitungan SLA sehingga sekolah tidak terkena penalti waktu saat menunggu balasan.", "info");

$bk->addHeading2("3.4. Tata Cara Eskalasi Kasus (Jenjang 2 & Jenjang 3)");
$bk->addParagraph("Jika kasus memerlukan perhatian pimpinan sekolah atau kewenangan lintas instansi di tingkat kota, Guru BK dapat menggunakan formulir Eskalasi Kasus:");
$bk->addStep(1, "Pilih Tujuan Eskalasi", "Tersedia 4 pilihan rujukan eskalasi resmi:");
$bk->addBullet("Jenjang 2: Kepala Sekolah", "Untuk penanganan internal sekolah, pelanggaran berat siswa yang butuh sanksi kepala sekolah, atau kasus yang melibatkan pendidik.", 2);
$bk->addBullet("Jenjang 3: Dinas Pendidikan Kota Bukittinggi", "Untuk masalah manajerial guru/sekolah, pembinaan kedinasan, kurikulum, atau sanksi administratif.", 2);
$bk->addBullet("Jenjang 3: UPTD PPA - DP3APPKB", "Khusus kasus kekerasan fisik berat, kekerasan/pelecehan seksual anak, serta kebutuhan pendampingan hukum dan rehabilitasi psikologis.", 2);
$bk->addBullet("Jenjang 3: Satgas PPKSP Kota Bukittinggi", "Untuk situasi darurat/kritis yang memerlukan penanganan gabungan Dinas Pendidikan dan UPTD PPA secara terpadu.", 2);
$bk->addStep(2, "Tulis Alasan Eskalasi Secara Objektif", "Jelaskan fakta kasus, asesmen risiko, dan pertimbangan mengapa eskalasi dibutuhkan (minimal 5 karakter).");
$bk->addStep(3, "Kirim Eskalasi", "Klik 'Eskalasikan Kasus'. Sistem akan otomatis mengirimkan email prioritas resmi ke pejabat terkait dan mencatatnya ke audit trail.");

$bk->addHeading2("3.5. Penutupan Kasus & Berita Acara Penyelesaian");
$bk->addParagraph("Sesuai standar operasional Sahabat Sekolah, kasus tidak dapat ditutup (CLOSED) secara sembarangan tanpa dokumen pendukung:");
$bk->addStep(1, "Ubah Status ke RESOLVED", "Setelah seluruh tahapan penanganan, konseling, dan mediasi tuntas, ubah status kasus ke RESOLVED.");
$bk->addStep(2, "Unggah Dokumen Berita Acara", "Pada panel Dokumen Penyelesaian, unggah pindaian (PDF/JPG) berita acara mediasi atau surat komitmen damai yang ditandatangani pihak terkait.");
$bk->addStep(3, "Tutup Kasus Resmi", "Setelah dokumen terverifikasi valid, status kasus dapat diubah menjadi CLOSED.");

$bk->addHeading1("BAB IV: PROTOKOL DARURAT & PENANGANAN KENDALA");
$bk->addCallout("Peringatan Situasi Kritis (Red-Flag)", "Jika deskripsi laporan memuat indikasi bahaya jiwa, senjata tajam, atau percobaan bunuh diri, sistem otomatis menaikkan risiko ke CRITICAL dengan SLA 1 jam. Guru BK wajib segera mengamankan korban, berkoordinasi dengan Kepala Sekolah, dan jika perlu menghubungi Layanan Darurat 112 atau SAPA 129 UPTD PPA.", "danger");

$bk->addTable(
    ["Permasalahan", "Penyebab Umum", "Solusi / Tindakan"],
    [
        ["Lupa Kata Sandi Akun BK", "Salah input / lupa password", "Gunakan fitur Lupa Password di halaman login atau hubungi Administrator Sekolah/Kota untuk reset token aman."],
        ["Pelapor Anonim Tidak Merespons", "Pelapor belum mengecek kanal", "Kirimkan pesan penegasan empati. Gunakan status WAITING_REPORTER_INFO agar SLA dijeda secara adil."],
        ["Data Siswa Tidak Muncul di Modul Prestasi", "Microservice AConnect dalam pemeliharaan", "Sistem menyediakan opsi pencatatan manual sementara. Data akan otomatis disinkronkan saat koneksi pulih."]
    ],
    [2600, 2600, 4400]
);

$bk->save($outputDir . '/PANDUAN_PENGGUNAAN_GURU_BK.docx');
echo "Completed: PANDUAN_PENGGUNAAN_GURU_BK.docx\n\n";

// =========================================================================
// 2. MANUAL KEPALA SEKOLAH (PRINCIPAL)
// =========================================================================
echo "Building: PANDUAN_PENGGUNAAN_KEPALA_SEKOLAH.docx...\n";
$ks = new DocxBuilder(
    "PANDUAN OPERASIONAL & PENGAWASAN PIMPINAN",
    "MODUL KEPALA SEKOLAH (PRINCIPAL)",
    "Kepala Sekolah"
);

$ks->addTitle("SAHABAT SEKOLAH — KOTA BUKITTINGGI")
   ->addSubtitle("Buku Panduan Pengawasan Eksekutif & Penanganan Eskalasi Kasus bagi Kepala Sekolah")
   ->addMetaTable([
       "Nama Dokumen" => "Buku Panduan Pengguna — Peran Kepala Sekolah (PRINCIPAL)",
       "Platform" => "SAHABAT SEKOLAH (Case Management System Penanganan Kekerasan)",
       "Wilayah Implementasi" => "Satuan Pendidikan (SD, SMP, MTs, MIN) Kota Bukittinggi",
       "Dasar Hukum" => "Permendikbudristek No. 46 Tahun 2023 & SK TPPK Satuan Pendidikan",
       "Peruntukan Role" => "Kepala Sekolah / Penanggung Jawab TPPK Sekolah",
       "Versi Panduan" => "1.0 (Oktober 2026)",
       "Klasifikasi Keamanan" => "Dokumen Pengawasan Pimpinan & Penanganan Kasus (Terbatas)",
   ]);

$ks->addHeading1("BAB I: TANGGUNG JAWAB KEPALA SEKOLAH DALAM PPKSP");
$ks->addParagraph("Berdasarkan Permendikbudristek No. 46 Tahun 2023, Kepala Sekolah bertanggung jawab penuh atas terciptanya iklim lingkungan belajar yang aman, inklusif, dan ramah anak di satuan pendidikan. Melalui platform Sahabat Sekolah, Kepala Sekolah tidak hanya bertindak sebagai pengawas pasif, melainkan pengambil keputusan strategis dalam penanganan kasus yang dieskalasikan.");

$ks->addHeading2("1.1. Peran Kunci Kepala Sekolah pada Sahabat Sekolah");
$ks->addBullet("Supervisi Kepatuhan SLA Guru BK:", "Memastikan setiap pengaduan yang masuk dari peserta didik mendapatkan respons awal dalam waktu kurang dari 24 jam.");
$ks->addBullet("Penanganan Kasus Eskalasi Jenjang 2:", "Memberikan arahan pimpinan, memimpin rapat mediasi tingkat sekolah, dan memutuskan sanksi edukatif atau tindakan disiplin.");
$ks->addBullet("Evaluasi Kebijakan Berbasis Data:", "Menganalisis tren perundungan per bulan, kategori terbanyak, serta tingkat risiko kasus guna menyusun program pencegahan yang tepat sasaran.");
$ks->addBullet("Fallback Notifikasi Madrasah/Sekolah:", "Pada madrasah/sekolah yang belum memiliki Guru BK terdaftar, sistem secara otomatis meneruskan seluruh laporan masuk langsung ke akun Kepala Sekolah.");

$ks->addHeading1("BAB II: FITUR DASHBOARD PENGAWASAN KEPALA SEKOLAH");
$ks->addParagraph("Kepala Sekolah memiliki halaman khusus pengawasan yang dirancang dengan antarmuka ringkas, bersih, dan berorientasi keputusan:");

$ks->addTable(
    ["Komponen Dashboard", "Fungsi & Informasi yang Disajikan", "Tindakan Kepala Sekolah"],
    [
        ["Kartu Metrik Utama", "Total kasus sekolah, kasus butuh atensi, kasus risiko tinggi/kritis, dan rasio penyelesaian", "Mengevaluasi kecepatan penanganan kasus di sekolah"],
        ["Panel Kasus Dieskalasikan", "Daftar kasus yang diteruskan khusus oleh Guru BK ke meja Kepala Sekolah", "Wajib segera diklik untuk membaca kronologi dan memberikan disposisi"],
        ["Grafik Kepatuhan SLA", "Statistik persentase kasus On-Time vs Warning vs Terlambat (Overdue)", "Mengidentifikasi apakah Guru BK mengalami kendala beban kerja"],
        ["Ringkasan Harian (Daily Digest)", "Notifikasi otomatis harian ke email dinas Kepala Sekolah memuat rekap status kasus", "Tinjau email ringkasan setiap pagi hari sebelum jam operasional"]
    ],
    [2400, 4400, 2800]
);

$ks->addHeading1("BAB III: PANDUAN PENGOPERASIAN APLIKASI");

$ks->addHeading2("3.1. Masuk & Pengenalan Akun Kepala Sekolah");
$ks->addStep(1, "Akses Login", "Masuk ke portal melalui tombol 'Masuk Petugas' menggunakan email resmi Kepala Sekolah (contoh: kepala@sahabat.test).");
$ks->addStep(2, "Buka Menu Khusus Pimpinan", "Kepala Sekolah otomatis diarahkan ke menu '/principal' yang berfokus pada pemantauan dan kasus eskalasi sekolah.");

$ks->addHeading2("3.2. Menangani Kasus yang Dieskalasikan");
$ks->addParagraph("Ketika Guru BK melakukan eskalasi kasus, Kepala Sekolah akan menerima email notifikasi '🚨 Eskalasi Kasus oleh Guru BK' serta kemunculan kasus di tab eskalasi dashboard:");
$ks->addStep(1, "Tinjau Alasan Eskalasi", "Baca catatan alasan eskalasi dari Guru BK (misal: penolakan mediasi orang tua, situasi darurat, atau kekerasan melibatkan pendidik).");
$ks->addStep(2, "Periksa Kronologi & Bukti", "Buka detail kasus untuk melihat laporan awal, dokumen pendukung, dan catatan internal konseling yang sudah dilakukan Guru BK.");
$ks->addStep(3, "Berikan Tindak Lanjut / Disposisi", "Panggil pihak terkait untuk mediasi pimpinan, terbitkan surat keputusan penanganan, atau koordinasikan bantuan medis/psikologis.");
$ks->addStep(4, "Eskalasi ke Jenjang 3 (Tingkat Kota)", "Jika kasus melampaui kewenangan satuan pendidikan, Kepala Sekolah bersama Guru BK dapat mengeskalasikan kasus ke Dinas Pendidikan atau UPTD PPA DP3APPKB.");

$ks->addHeading2("3.3. Mengawasi Fitur Cetak Rekapitulasi Kasus");
$ks->addParagraph("Untuk kebutuhan pelaporan kedinasan, rapat komite, atau pemeriksaan pengawas sekolah, Kepala Sekolah dapat mencetak Berkas Rekapitulasi Kasus Resmi (Watermarked) melalui tombol 'Cetak Berkas Kasus' di halaman detail kasus.");

$ks->addCallout("Penting: Perlindungan Privasi Berkas Fisik", "Dokumen fisik yang dicetak memuat watermark resmi dan kode penelusuran audit. Simpan berkas fisik di lemari terkunci dan jangan menduplikasi dokumen tanpa persetujuan resmi tim penanganan kekerasan.", "warning");

$ks->addHeading1("BAB IV: REKOMENDASI MANAJERIAL PENCEGAHAN PERUNDUNGAN");
$ks->addBullet("Analisis Lokasi Rawan (Hotspots):", "Tinjau kategori dan waktu kejadian perundungan. Tempatkan pengawasan guru piket pada jam-jam istirahat di area rawan (kantin, lorong belakang, toilet, lapangan).");
$ks->addBullet("Sosialisasi Kanal Lapor Siswa:", "Pastikan poster QR Code Sahabat Sekolah terpasang di setiap ruang kelas, mading, dan perpustakaan agar siswa tahu cara melapor yang aman.");
$ks->addBullet("Evaluasi Berkala:", "Jadikan metrik Sahabat Sekolah sebagai bahan evaluasi bulanan rapat dewan guru untuk mewujudkan sekolah ramah anak.");

$ks->save($outputDir . '/PANDUAN_PENGGUNAAN_KEPALA_SEKOLAH.docx');
echo "Completed: PANDUAN_PENGGUNAAN_KEPALA_SEKOLAH.docx\n\n";

// =========================================================================
// 3. MANUAL INSTANSI KOTA / SATGAS PPKSP (CITY_ADMIN)
// =========================================================================
echo "Building: PANDUAN_PENGGUNAAN_INSTANSI_KOTA_SATGAS_PPKSP.docx...\n";
$city = new DocxBuilder(
    "PANDUAN OPERASIONAL MONITORING TINGKAT KOTA",
    "MODUL SATGAS PPKSP / INSTANSI TINGKAT KOTA (CITY_ADMIN)",
    "Instansi Kota"
);

$city->addTitle("SAHABAT SEKOLAH — KOTA BUKITTINGGI")
     ->addSubtitle("Panduan Operasional Monitoring Kota & Penanganan Eskalasi Jenjang 3 bagi Dinas Pendidikan dan UPTD PPA (DP3APPKB)")
     ->addMetaTable([
         "Nama Dokumen" => "Buku Panduan Pengguna — Peran Instansi Kota / Satgas PPKSP (CITY_ADMIN)",
         "Platform" => "SAHABAT SEKOLAH (Case Management System Penanganan Kekerasan)",
         "Wilayah Implementasi" => "Tingkat Kota Bukittinggi (Lintas Satuan Pendidikan SD, SMP, MTs, MIN)",
         "Dasar Hukum" => "Permendikbudristek 46/2023, UU Perlindungan Anak, & SK Satgas PPKSP Kota Bukittinggi",
         "Instansi Terkait" => "1. Dinas Pendidikan & Kebudayaan Kota Bukittinggi\n2. UPTD PPA - DP3APPKB Kota Bukittinggi",
         "Peruntukan Role" => "Pejabat Dinas Pendidikan, Petugas UPTD PPA, dan Anggota Satgas PPKSP Kota",
         "Versi Panduan" => "1.0 (Oktober 2026)",
         "Klasifikasi Keamanan" => "Dokumen Kedinasan Strategis & Koordinasi Lintas Sektor (Terbatas)",
     ]);

$city->addHeading1("BAB I: PERAN INSTANSI TINGKAT KOTA DALAM SATGAS PPKSP");
$city->addParagraph("Berdasarkan regulasi pencegahan dan penanganan kekerasan, penanganan kasus di satuan pendidikan memerlukan sinergi lintas sektoral di tingkat kota. Peran CITY_ADMIN dirancang untuk mengakomodir dua instansi pilar utama Pemerintah Kota Bukittinggi:");

$city->addBullet("Dinas Pendidikan & Kebudayaan Kota Bukittinggi (Akun: dinas@sahabat.test):", "Berfokus pada pemantauan pembinaan sekolah, kepatuhan satuan pendidikan, penegakan disiplin pendidik/tenaga kependidikan, mutasi administratif, dan pengawasan kurikulum.");
$city->addBullet("UPTD PPA - DP3APPKB Kota Bukittinggi (Akun: ppa@sahabat.test):", "Unit Pelaksana Teknis Daerah Perlindungan Perempuan dan Anak berfokus pada pendampingan korban kekerasan fisik berat, kekerasan/pelecehan seksual, pendampingan hukum terpadu, pemulihan psikologis anak, dan rujukan ke rumah aman.");
$city->addBullet("Satgas PPKSP Kota Bukittinggi (Gabungan Disdik & UPTD PPA):", "Wadah koordinasi terpadu untuk menyelesaikan kasus darurat, situasi krisis lintas sektor, atau kasus yang memerlukan intervensi multi-pihak.");

$city->addHeading1("BAB II: FITUR & HAK AKSES LINTAS SEKOLAH (CITY-WIDE SCOPE)");
$city->addParagraph("Berbeda dengan staf sekolah yang terisolasi pada 1 NPSN, akun CITY_ADMIN memiliki parameter khusus 'school_npsn = null' yang membuka akses pemantauan ke seluruh satuan pendidikan (SD, SMP, MTs, MIN) se-Kota Bukittinggi:");

$city->addTable(
    ["Modul / Fitur", "Hak Akses Instansi Kota", "Manfaat Operasional"],
    [
        ["Dashboard Monitoring Kota", "Melihat grafik tren perundungan per bulan, rasio SLA kota, dan sebaran status kasus se-Bukittinggi", "Pemetaan zona kerawanan dan evaluasi performa penanganan antar-sekolah"],
        ["Penanganan Eskalasi Jenjang 3", "Menerima notifikasi email prioritas khusus untuk kasus yang dieskalasikan oleh Guru BK ke instansi kota", "Intervensi langsung sebelum kasus membesar atau viral di masyarakat"],
        ["Detail Kasus Terbuka", "Membuka rincian kasus sekolah mana pun tanpa hambatan sekat NPSN", "Pemeriksaan kronologi, catatan mediasi, dan bukti pendukung secara akurat"],
        ["Direktori Data Sekolah", "Memantau daftar seluruh sekolah terdaftar dan keterhubungan AConnect", "Memastikan seluruh sekolah memiliki perwakilan petugas BK/TPPK aktif"]
    ],
    [2400, 4400, 2800]
);

$city->addHeading1("BAB III: TATA CARA PENGGUNAAN APLIKASI TINGKAT KOTA");

$city->addHeading2("3.1. Masuk ke Akun Instansi");
$city->addStep(1, "Akses Portal", "Buka halaman login petugas pada sistem Sahabat Sekolah.");
$bkStepDesc = "Gunakan akun resmi instansi Anda:\n• Dinas Pendidikan: dinas@sahabat.test\n• UPTD PPA DP3APPKB: ppa@sahabat.test";
$city->addStep(2, "Gunakan Email Instansi", $bkStepDesc);
$city->addStep(3, "Masuk ke Dashboard Kota", "Sistem otomatis mengarahkan Anda ke Dashboard Monitoring Kota (/admin).");

$city->addHeading2("3.2. Menerima & Menindaklanjuti Kasus Eskalasi Jenjang 3");
$city->addParagraph("Ketika Guru BK mengeskalasikan kasus ke tingkat kota, sistem melakukan penyaringan sasaran cerdas:");
$city->addBullet("Kasus untuk Dinas Pendidikan (CITY_DISDIK):", "Email dan notifikasi terkirim ke dinas@sahabat.test. Pejabat Disdik dapat meninjau pelanggaran tata tertib sekolah, kelalaian guru, atau kebutuhan mediasi dinas.");
$city->addBullet("Kasus untuk UPTD PPA (CITY_PPA):", "Email dan notifikasi terkirim ke ppa@sahabat.test. Petugas UPTD PPA dapat langsung menyiapkan tim pekerja sosial (peksos), psikolog anak, atau pendampingan visum dan hukum.");
$city->addBullet("Kasus untuk Satgas Gabungan (CITY_SATGAS):", "Notifikasi terkirim ke kedua instansi sekaligus untuk agenda rapat koordinasi darurat dan intervensi gabungan.");

$city->addStep(1, "Buka Tautan Kasus", "Klik tautan kasus pada email notifikasi atau pilih nomor kasus pada tabel 'Kasus Memerlukan Perhatian Kota' di dashboard.");
$city->addStep(2, "Pelajari Rekam Jejak Kasus", "Tinjau riwayat asesmen risiko, bukti foto/tangkapan layar, dan riwayat catatan yang telah dibuat Guru BK.");
$city->addStep(3, "Lakukan Koordinasi / Disposisi Luar Jaringan", "Hubungi sekolah, jadwalkan kunjungan lapangan, atau siapkan perlindungan korban sesuai SOP masing-masing dinas.");

$city->addCallout("Pemisahan Wewenang Penutupan Kasus", "Meskipun memiliki hak pantau penuh, penutupan resmi kasus (status CLOSED) tetap menjadi tanggung jawab Guru BK sekolah bersama dokumen penyelesaian resmi, guna menjaga rantai akuntabilitas satuan pendidikan.", "info");

$city->addHeading1("BAB IV: ANALISIS STATISTIK & PERUMUSAN KEBIJAKAN KOTA");
$city->addParagraph("Dashboard kota menyajikan grafik ApexCharts interaktif yang diperbarui secara real-time:");
$city->addBullet("Grafik Tren Bulanan:", "Memantau lonjakan laporan pada bulan-bulan tertentu (misalnya masa orientasi peserta didik baru/MPLS atau menjelang ujian).");
$city->addBullet("Donut Chart Kepatuhan SLA:", "Memonitor apakah sekolah-sekolah di Bukittinggi responsif dalam mematuhi standar pelayanan minimal penanganan perundungan.");
$city->addBullet("Sebaran Kategori Kekerasan:", "Melihat dominasi kasus apakah perundungan siber (cyberbullying), kekerasan fisik, verbal, atau relasional sosial untuk dasar alokasi program penyuluhan kota.");

$city->save($outputDir . '/PANDUAN_PENGGUNAAN_INSTANSI_KOTA_SATGAS_PPKSP.docx');
echo "Completed: PANDUAN_PENGGUNAAN_INSTANSI_KOTA_SATGAS_PPKSP.docx\n\n";

// =========================================================================
// 4. MANUAL ADMINISTRATOR SISTEM (ADMIN)
// =========================================================================
echo "Building: PANDUAN_PENGGUNAAN_ADMINISTRATOR_SISTEM.docx...\n";
$adm = new DocxBuilder(
    "PANDUAN TEKNIS & ADMINISTRASI SISTEM",
    "MODUL ADMINISTRATOR SISTEM (ADMIN)",
    "Administrator Sistem"
);

$adm->addTitle("SAHABAT SEKOLAH — KOTA BUKITTINGGI")
    ->addSubtitle("Panduan Pengelolaan Pengguna, Konfigurasi SLA, Master Data, dan Audit Keamanan")
    ->addMetaTable([
        "Nama Dokumen" => "Buku Panduan Pengguna — Peran Administrator Sistem (ADMIN)",
        "Platform" => "SAHABAT SEKOLAH (Case Management System Penanganan Kekerasan)",
        "Wilayah Implementasi" => "Pusat Pengelolaan Sistem Kota Bukittinggi",
        "Peruntukan Role" => "Administrator TI Dinas Pendidikan / Tim Pengembang Sistem",
        "Versi Panduan" => "1.0 (Oktober 2026)",
        "Klasifikasi Keamanan" => "Dokumen Konfigurasi Teknis & Manajemen Akun (Superuser / Rahasia)",
    ]);

$adm->addHeading1("BAB I: RUANG LINGKUP & TANGGUNG JAWAB ADMINISTRATOR");
$adm->addParagraph("Administrator Sistem memegang hak akses tertinggi (Superuser) dalam ekosistem Sahabat Sekolah. Tanggung jawab Administrator berfokus pada kelangsungan layanan, integritas basis data, manajemen hak akses seluruh pengguna, serta pemeliharaan kepatuhan protokol keamanan siber.");

$adm->addHeading2("1.1. Fitur Utama Panel Administrator");
$adm->addBullet("Manajemen Pengguna Terpusat:", "Menambah, memperbarui, menonaktifkan, dan mengatur ulang kata sandi seluruh peran pengguna (Counselor, Principal, City Admin, Admin).");
$adm->addBullet("Dukungan Multi-Instansi Kota:", "Mengonfigurasi akun tingkat kota dengan preset instansi spesifik (Dinas Pendidikan & Kebudayaan, UPTD PPA DP3APPKB, atau Satgas PPKSP).");
$adm->addBullet("Sistem Kata Sandi Acak & Reset Berbatas Waktu:", "Pembuatan akun otomatis menghasilkan kata sandi acak 8 karakter yang dikirim ke email pengguna. Reset kata sandi menggunakan tautan token kriptografis 64-karakter dengan masa kedaluwarsa 60 menit.");
$adm->addBullet("Konfigurasi Matriks SLA & Jam Kerja:", "Mengatur parameter respons berdasarkan tingkat risiko dan waktu kerja efektif.");
$adm->addBullet("Audit Log Trail:", "Memantau setiap tindakan kritis pengguna (login, ubah status kasus, eskalasi, buat catatan) untuk akuntabilitas hukum.");

$adm->addHeading1("BAB II: PANDUAN MANAJEMEN PENGGUNA (/admin/users)");

$adm->addHeading2("2.1. Membuat Pengguna Baru");
$adm->addStep(1, "Buka Menu Manajemen Pengguna", "Pilih menu 'Pengguna' pada bilah navigasi admin (/admin/users).");
$adm->addStep(2, "Klik 'Tambah Pengguna'", "Tekan tombol biru bertuliskan '+ Tambah Pengguna' di bagian atas tabel.");
$adm->addStep(3, "Isi Data Akun", "Ketik Nama Lengkap dan Alamat Email yang valid. Sistem secara otomatis menormalkan email menjadi huruf kecil tanpa spasi liar.");
$adm->addStep(4, "Pilih Peran (Role)", "Tentukan peran pengguna:");
$adm->addBullet("Guru BK (COUNSELOR) atau Kepala Sekolah (PRINCIPAL):", "Wajib memilih sekolah asal dari dropdown pencarian AConnect.", 2);
$adm->addBullet("Instansi Kota (CITY_ADMIN):", "Kolom sekolah disembunyikan dan muncul dropdown 'Instansi Tingkat Kota' (Dinas Pendidikan & Kebudayaan Kota Bukittinggi atau UPTD PPA - DP3APPKB Kota Bukittinggi).", 2);
$adm->addBullet("Administrator (ADMIN):", "Akun superuser sistem tanpa keterikatan sekolah.", 2);
$adm->addStep(5, "Simpan Pengguna", "Klik 'Buat Pengguna'. Sistem otomatis mengenkripsi kata sandi acak 8 karakter dan memasukkan pengiriman email selamat datang ke antrean (queue).");

$adm->addHeading2("2.2. Mengatur Ulang (Reset) Kata Sandi Pengguna");
$adm->addParagraph("Jika pengguna lupa kata sandi atau mengalami kendala login, Administrator dapat menginisiasi reset yang aman:");
$adm->addStep(1, "Temukan Pengguna", "Gunakan kotak pencarian atau filter peran di tabel pengguna.");
$adm->addStep(2, "Klik Tombol 'Reset'", "Tekan tombol berwarna kuning berikon kunci pada baris pengguna yang dituju.");
$adm->addStep(3, "Konfirmasi Reset", "Periksa nama dan email pengguna pada jendela konfirmasi, lalu klik 'Kirim Tautan Reset'.");
$adm->addStep(4, "Pengiriman Token Aman", "Sistem akan membuat token reset 64-karakter yang berlaku selama 60 menit dan mengirimkannya langsung ke email pengguna.");

$adm->addHeading2("2.3. Memperbarui Data & Menonaktifkan Akun");
$adm->addParagraph("Klik tombol 'Edit' untuk memperbarui nama, peran, instansi, atau mengubah status akun menjadi NONAKTIF (INACTIVE) jika petugas telah berpindah tugas atau purna bakti.");

$adm->addHeading1("BAB III: PENGATURAN SLA & MASTER DATA");
$adm->addParagraph("Pada menu '/admin/sla-configurations', Administrator dapat meninjau dan mengonfigurasi batas waktu pelayanan:");
$adm->addTable(
    ["Tingkat Risiko", "Target Respons Awal", "Target Resolusi Kasus", "Aturan Jam Kerja"],
    [
        ["LOW", "24 Jam Kerja", "7 Hari Kerja", "Hanya menghitung hari Senin-Jumat pukul 08:00 - 16:00 WIB"],
        ["MEDIUM", "24 Jam Kerja", "7 Hari Kerja", "Hanya menghitung hari Senin-Jumat pukul 08:00 - 16:00 WIB"],
        ["HIGH", "4 Jam Kerja", "3 Hari Kerja", "Dipercepat untuk potensi ancaman berkelanjutan"],
        ["CRITICAL", "1 Jam (Instant)", "24 Jam", "Berlaku 24 jam non-stop (termasuk akhir pekan/malam)"]
    ],
    [2000, 2400, 2400, 2800]
);

$adm->addHeading1("BAB IV: PEMELIHARAAN SISTEM & DIAGNOSTIK KEAMANAN");
$adm->addBullet("Pemantauan Antrean Email (Queue Worker):", "Pastikan worker 'php artisan queue:work' berjalan di server agar pengiriman email sambutan, eskalasi, dan daily digest tidak tertunda.");
$adm->addBullet("Pemindaian Otomatis SLA (Scheduler):", "Command 'php artisan sahabat:sla-scan' dijadwalkan berjalan berkala melalui crontab untuk mendeteksi kasus yang mendekati batas waktu respons.");
$adm->addBullet("Cadangan Basis Data (Database Backup):", "Lakukan pencadangan berkala tabel kasus, laporan, pengguna, dan berkas penyelesaian yang tersimpan di storage terenkripsi.");

$adm->save($outputDir . '/PANDUAN_PENGGUNAAN_ADMINISTRATOR_SISTEM.docx');
echo "Completed: PANDUAN_PENGGUNAAN_ADMINISTRATOR_SISTEM.docx\n\n";

// =========================================================================
// 5. MANUAL SISWA / PELAPOR PUBLIK (STUDENT & REPORTER)
// =========================================================================
echo "Building: PANDUAN_PENGGUNAAN_PELAPOR_DAN_SISWA.docx...\n";
$usr = new DocxBuilder(
    "PANDUAN PENGGUNAAN KANAL PELAPORAN PUBLIK",
    "MODUL PESERTA DIDIK, SAKSI, & MASYARAKAT",
    "Pelapor Publik"
);

$usr->addTitle("SAHABAT SEKOLAH — KOTA BUKITTINGGI")
    ->addSubtitle("Panduan Ramah Anak: Cara Melapor dengan Aman, Melacak Laporan, dan Berkomunikasi Rahasia")
    ->addMetaTable([
        "Nama Dokumen" => "Buku Panduan Pengguna — Kanal Pelapor Publik & Peserta Didik",
        "Platform" => "SAHABAT SEKOLAH (Ruang Aman Lapor Perundungan & Kekerasan)",
        "Wilayah Implementasi" => "Seluruh Satuan Pendidikan Kota Bukittinggi",
        "Peruntukan Pengguna" => "Siswa (Korban/Saksi), Orang Tua Murid, dan Masyarakat",
        "Versi Panduan" => "1.0 (Oktober 2026)",
        "Bahasa" => "Bahasa Indonesia Ramah Anak & Mudah Dipahami",
    ]);

$usr->addHeading1("BAB I: RUANG AMAN UNTUK BERSUARA");
$usr->addParagraph("Halo Teman-Teman dan Sahabat Sekolah! Apakah kamu pernah melihat temanmu diejek terus-menerus, dipukul, dikucilkan, atau diancam di media sosial? Atau mungkin kamu sendiri yang mengalaminya dan merasa bingung harus cerita ke siapa?");
$usr->addParagraph("Sahabat Sekolah dibuat khusus oleh Pemerintah Kota Bukittinggi agar setiap anak di Bukittinggi bisa belajar dengan tenang dan gembira tanpa rasa takut. Di sini, kamu bisa bercerita secara aman tanpa khawatir dimarahi, diejek, atau diketahui oleh pelaku.");

$usr->addHeading2("1.1. Tiga Pilihan Kejujuran Identitas Kamu");
$usr->addBullet("Mode 1: Lapor Terbuka (Identified)", "Jika kamu ingin Guru BK langsung menghubungi kamu dan orang tuamu untuk mengobrol bersama.");
$usr->addBullet("Mode 2: Lapor Rahasia (Confidential)", "Nama dan kelasmu hanya diketahui oleh Guru BK yang menangani. Teman lain atau pelaku tidak akan pernah tahu siapa yang melapor.");
$usr->addBullet("Mode 3: Lapor Anonim (Anonymous)", "Kamu sama sekali TIDAK PERLU menuliskan nama, email, nomor HP, atau membuat akun! Kamu akan mendapatkan 'Kunci Rahasia 4 Kata' untuk melacak laporanmu.");

$usr->addCallout("Tombol Keluar Cepat (Panic Exit)", "Jika tiba-tiba ada orang yang mendekat saat kamu sedang membuka website atau menulis laporan, segera klik tombol merah 'Keluar Cepat' di pojok kanan atas. Layar akan langsung beralih ke halaman Google atau Wikipedia dalam sekejap!", "warning");

$usr->addHeading1("BAB II: LANGKAH-LANGKAH MEMBUAT LAPORAN");
$usr->addStep(1, "Buka Halaman Lapor", "Buka website Sahabat Sekolah di HP atau komputermu, lalu klik tombol besar bertuliskan 'Buat Laporan Sekarang'. Kamu juga bisa memindai (scan) QR Code yang ditempel di mading sekolahmu.");
$usr->addStep(2, "Pilih Sekolah", "Ketik nama sekolahmu (contoh: SMP Negeri 2 Bukittinggi atau MTsN 1 Bukittinggi). Jika kamu scan QR Code di sekolah, nama sekolahmu akan terpilih otomatis.");
$usr->addStep(3, "Pilih Siapa Kamu", "Pilih apakah kamu adalah Korban langsung, Saksi yang melihat kejadian, Teman korban, atau Orang tua.");
$usr->addStep(4, "Pilih Jenis Kejadian", "Pilih kategori kejadian: Perundungan Verbal (diejek/dihina), Fisik (dipukul/didorong), Sosial (dikucilkan/difitnah), Siber (diteror di medsos/grup chat), atau Kekerasan Seksual.");
$usr->addStep(5, "Ceritakan Kejadiannya", "Tuliskan apa yang terjadi dengan jujur: kapan kejadiannya, di mana tempatnya, dan siapa saja yang terlibat. Jangan takut, ceritakan apa adanya.");
$usr->addStep(6, "Unggah Bukti (Jika Ada)", "Jika kamu punya foto luka, foto lokasi, atau tangkapan layar (screenshot) pesan ancaman, kamu bisa melampirkannya. Lokasi foto kamu akan otomatis dirahasiakan oleh sistem demi keamananmu.");
$usr->addStep(7, "Kirim Laporan", "Klik tombol 'Kirim Laporan'. Laporanmu akan langsung masuk ke ruang Guru BK.");

$usr->addHeading1("BAB III: KARTU BUKTI LAPOR & KUNCI RAHASIA 4 KATA");
$usr->addParagraph("Setelah mengirim laporan anonim, layar akan menampilkan Kartu Bukti Lapor yang memuat Nomor Laporan dan Kunci Rahasia 4 Kata (contoh: elang-pandai-rimba-sungai):");

$usr->addCallout("Sangat Penting: Simpan Kunci Rahasiamu!", "Catat atau ambil foto (screenshot) Kunci Rahasia 4 Kata tersebut! Kunci ini adalah satu-satunya alat rahasia kamu untuk membuka obrolan dengan Guru BK dan melihat kemajuan penanganan kasusmu tanpa perlu login.", "danger");

$usr->addHeading1("BAB IV: CARA MELACAK LAPORAN KAMU (/track)");
$usr->addParagraph("Kamu bisa melihat sudah sampai mana laporanmu diproses oleh Guru BK secara transparan:");
$usr->addStep(1, "Buka Menu Lacak Posisi Laporan", "Buka alamat website Sahabat Sekolah dan pilih menu 'Lacak Laporan' (/track).");
$usr->addStep(2, "Ketik Kunci Rahasia atau Nomor Laporan", "Masukkan Kunci Rahasia 4 Kata kamu (atau nomor laporanmu).");
$usr->addStep(3, "Lihat Stepper 5 Tahap Penanganan", "Sistem akan menampilkan posisi laporanmu:");
$usr->addBullet("1. Laporan Masuk:", "Laporanmu sudah diterima dengan selamat oleh sistem.", 2);
$usr->addBullet("2. Verifikasi & Asesmen Awal:", "Guru BK sedang membaca cerita dan memeriksa buktimu.", 2);
$usr->addBullet("3. Dalam Penanganan:", "Guru BK sedang melakukan tindakan aktif, memanggil saksi, atau konseling.", 2);
$usr->addBullet("4. Kasus Selesai:", "Masalah sudah berhasil diselesaikan dan didamaikan.", 2);
$usr->addBullet("5. Kasus Ditutup:", "Penyelesaian kasus telah didokumentasikan resmi.", 2);
$usr->addStep(4, "Buka Ruang Obrolan Rahasia Dua Arah", "Pada halaman pelacak tersebut, kamu bisa langsung mengirimkan pesan atau membaca balasan dari Guru BK tanpa membocorkan siapa dirimu.");

$usr->addHeading1("BAB V: KONTAK BANTUAN DARURAT KOTA BUKITTINGGI");
$usr->addParagraph("Jika kamu atau temanmu sedang dalam bahaya fisik mendesak atau membutuhkan perlindungan segera, jangan menunggu! Segera hubungi:");
$usr->addTable(
    ["Layanan Darurat", "Nomor Kontak", "Keterangan"],
    [
        ["Panggilan Darurat Kota Bukittinggi", "112 (Bebas Pulsa)", "Layanan tanggap darurat 24 jam Pemerintah Kota Bukittinggi"],
        ["Layanan SAPA UPTD PPA", "129 / WhatsApp 08111-129-129", "Layanan Sahabat Perempuan dan Anak Kementerian PPPA & UPTD PPA"],
        ["UPTD PPA Kota Bukittinggi", "Dinas DP3APPKB Bukittinggi", "Pendampingan hukum, psikologis, dan penampungan aman anak"]
    ],
    [3200, 3000, 3400]
);

$usr->save($outputDir . '/PANDUAN_PENGGUNAAN_PELAPOR_DAN_SISWA.docx');
echo "Completed: PANDUAN_PENGGUNAAN_PELAPOR_DAN_SISWA.docx\n\n";

echo "ALL 5 USER MANUALS CREATED SUCCESSFULLY IN: " . $outputDir . "\n";
