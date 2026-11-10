<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class AnonymousTokenHelper
{
    /**
     * Daftar kata benda / hewan / alam ramah anak (Kata 1).
     *
     * @var array<int, string>
     */
    protected static array $nouns = [
        'kucing', 'kelinci', 'rusa', 'kancil', 'lumba', 'merpati', 'elang', 'bintang',
        'pelangi', 'mentari', 'sungai', 'danau', 'ombak', 'gunung', 'hutan', 'pohon',
        'bunga', 'mawar', 'melati', 'daun', 'awan', 'langit', 'buku', 'pensil',
        'sepeda', 'kapal', 'kereta', 'layang', 'taman', 'kupu',
    ];

    /**
     * Daftar warna dan kata sifat positif (Kata 2).
     *
     * @var array<int, string>
     */
    protected static array $adjectives = [
        'biru', 'merah', 'kuning', 'hijau', 'putih', 'emas', 'perak', 'cerah',
        'hebat', 'pintar', 'berani', 'ramah', 'tenang', 'sejuk', 'hangat', 'manis',
        'bersih', 'indah', 'kuat', 'gagah', 'gesit', 'lincah', 'ceria', 'santun',
        'teduh', 'damai', 'segar', 'sejati', 'setia', 'terang',
    ];

    /**
     * Daftar kata kerja dan aktivitas positif (Kata 3).
     *
     * @var array<int, string>
     */
    protected static array $verbs = [
        'terbang', 'berlari', 'melompat', 'tersenyum', 'berkicau', 'menari', 'melangkah',
        'berlayar', 'menjelajah', 'bernyanyi', 'bersinar', 'tumbuh', 'berkembang', 'membaca',
        'menulis', 'belajar', 'berbagi', 'menjaga', 'merawat', 'menatap', 'menggapai',
        'berkarya', 'berkelana', 'menyapa', 'bermain', 'berteduh', 'mengayuh', 'meluncur',
        'tertawa', 'memandang',
    ];

    /**
     * Daftar waktu, tempat, dan elemen ikonik Bukittinggi / Minangkabau (Kata 4).
     *
     * @var array<int, string>
     */
    protected static array $placesOrTimes = [
        'pagi', 'siang', 'sore', 'senja', 'fajar', 'surya', 'samudera', 'angkasa',
        'cakrawala', 'sahabat', 'negeri', 'nusantara', 'sekolah', 'pelataran', 'dermaga',
        'pualam', 'gemintang', 'rimba', 'lembah', 'pantai', 'pesisir', 'telaga',
        'puncak', 'singgalang', 'marapi', 'ngarai', 'jamgadang', 'ranah', 'tarantang', 'harmoni',
    ];

    /**
     * Generate token 4-kata ramah anak berbahasa Indonesia.
     * Contoh: "kucing-biru-melompat-pagi" atau "elang-gagah-terbang-singgalang".
     */
    public static function generate(): string
    {
        $w1 = self::$nouns[array_rand(self::$nouns)];
        $w2 = self::$adjectives[array_rand(self::$adjectives)];
        $w3 = self::$verbs[array_rand(self::$verbs)];
        $w4 = self::$placesOrTimes[array_rand(self::$placesOrTimes)];

        return "{$w1}-{$w2}-{$w3}-{$w4}";
    }

    /**
     * Menghasilkan hash SHA-256 dari token.
     * Menggunakan uppercase dan trim agar kompatibel 100% dengan token alfanumerik lama maupun token 4 kata.
     */
    public static function hash(string $token): string
    {
        return hash('sha256', Str::upper(trim($token)));
    }

    /**
     * Menormalisasi tampilan token untuk pengguna.
     */
    public static function format(string $token): string
    {
        return Str::lower(trim($token));
    }
}
