<?php

namespace App\Console\Commands;

use App\Helpers\AppStorage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;

class TestKominfoStorage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:kominfo-test {--upload : Lakukan uji coba unggah, baca, dan hapus berkas dummy di storage Kominfo}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Periksa konfigurasi dan konektivitas Object Storage Kominfo (MinIO)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('=== DIAGNOSTIK OBJECT STORAGE KOMINFO ===');
        $this->newLine();

        $endpoint = (string) config('filesystems.disks.s3.endpoint');
        $bucket = (string) config('filesystems.disks.s3.bucket');
        $key = (string) config('filesystems.disks.s3.key');
        $publicUrl = (string) config('filesystems.disks.s3.url');
        $activeDisk = config('filesystems.default');
        $privateDisk = AppStorage::privateDisk();
        $publicDisk = AppStorage::publicDisk();
        $publicPrefix = config('filesystems.disks.s3-public.root');
        $privatePrefix = config('filesystems.disks.s3-private.root');

        $this->table(
            ['Parameter', 'Nilai'],
            [
                ['APP_ENV', config('app.env')],
                ['Default FILESYSTEM_DISK', $activeDisk],
                ['Resolved Private Disk', $privateDisk],
                ['Resolved Public Disk', $publicDisk],
                ['AWS_ENDPOINT', $endpoint ?: '(belum disetel di .env)'],
                ['AWS_BUCKET', $bucket ?: '(belum disetel di .env)'],
                ['AWS_ACCESS_KEY_ID', $key ? Str::mask($key, '*', 3) : '(belum disetel di .env)'],
                ['AWS_URL (Public)', $publicUrl ?: '(belum disetel di .env)'],
                ['S3 Public Prefix', $publicPrefix ?: '-'],
                ['S3 Private Prefix', $privatePrefix ?: '-'],
            ]
        );

        $this->newLine();

        // 1. Cek Konektivitas Jaringan (Socket Check)
        if (! empty($endpoint)) {
            $parsedUrl = parse_url($endpoint);
            $host = $parsedUrl['host'] ?? null;
            $scheme = $parsedUrl['scheme'] ?? 'http';
            $port = $parsedUrl['port'] ?? ($scheme === 'https' ? 443 : 80);

            if ($host) {
                $this->info("1. Menguji koneksi jaringan ke {$host}:{$port}...");
                $connection = @fsockopen($host, $port, $errno, $errstr, 3.0);

                if (is_resource($connection)) {
                    fclose($connection);
                    $this->info("   [OK] Server storage di {$host}:{$port} dapat dijangkau dari mesin ini.");
                } else {
                    $this->error("   [GAGAL] Tidak dapat terhubung ke {$host}:{$port} (Error: {$errstr} [{$errno}]).");
                    $this->warn('   Pastikan berada di jaringan lokal Kominfo / VPN yang sesuai.');
                }
            } else {
                $this->warn('1. Host pada AWS_ENDPOINT tidak valid.');
            }
        } else {
            $this->warn('1. AWS_ENDPOINT belum terkonfigurasi di file .env.');
        }

        $this->newLine();

        // 2. Cek dependensi Flysystem S3
        $s3AdapterExists = class_exists(AwsS3V3Adapter::class);
        $this->info('2. Memeriksa library driver S3 Laravel (league/flysystem-aws-s3-v3)...');

        if ($s3AdapterExists) {
            $this->info('   [OK] Driver league/flysystem-aws-s3-v3 telah terpasang.');
        } else {
            $this->warn('   [PERHATIAN] Driver league/flysystem-aws-s3-v3 BELUM terpasang.');
            $this->line('   Laravel membutuhkan paket ini agar dapat berinteraksi dengan S3 / MinIO Kominfo.');
            $this->line('   Jalankan: composer require league/flysystem-aws-s3-v3');
        }

        $this->newLine();

        // 3. Status Isolasi Lingkungan
        $this->info('3. Status Keamanan Isolasi Lingkungan:');
        if ($activeDisk === 'local' && $privateDisk === 'local' && $publicDisk === 'public') {
            $this->info('   [AMAN] Aplikasi saat ini beroperasi dalam mode LOCAL.');
            $this->line('   Semua upload dan penyimpanan berkas disimpan di storage lokal (storage/app/).');
            $this->line('   TIDAK ada berkas yang dikirim ke storage server Kominfo.');
        } else {
            $this->warn('   [PERINGATAN] Aplikasi saat ini disetel untuk mengirim langsung ke storage server Kominfo.');
        }

        // 4. Uji Coba Unggah Berkas Dummy jika diminta (--upload)
        if ($this->option('upload')) {
            $this->newLine();
            $this->info('4. Melakukan uji coba Read/Write dummy ke S3 Kominfo...');

            if (! $s3AdapterExists) {
                $this->error('   Gagal: Install league/flysystem-aws-s3-v3 terlebih dahulu.');

                return 1;
            }

            try {
                $testFilename = 'test-ping-'.time().'.txt';
                $testContent = 'Uji koneksi storage Sahabat Sekolah: '.now()->toDateTimeString();

                // Test Private
                $this->line("   - Menulis berkas uji ke s3-private: {$testFilename}...");
                Storage::disk('s3-private')->put($testFilename, $testContent);

                if (Storage::disk('s3-private')->exists($testFilename)) {
                    $this->info('     [OK] Berhasil menulis & memverifikasi s3-private.');
                    Storage::disk('s3-private')->delete($testFilename);
                    $this->info('     [OK] Berhasil menghapus berkas uji s3-private.');
                }

                // Test Public
                $this->line("   - Menulis berkas uji ke s3-public: {$testFilename}...");
                Storage::disk('s3-public')->put($testFilename, $testContent);

                if (Storage::disk('s3-public')->exists($testFilename)) {
                    $publicFileUrl = Storage::disk('s3-public')->url($testFilename);
                    $this->info('     [OK] Berhasil menulis & memverifikasi s3-public.');
                    $this->line("     URL Publik: {$publicFileUrl}");
                    Storage::disk('s3-public')->delete($testFilename);
                    $this->info('     [OK] Berhasil menghapus berkas uji s3-public.');
                }

                $this->info('   [SUKSES] Semua uji coba I/O ke Storage Kominfo berhasil!');
            } catch (\Throwable $e) {
                $this->error('   [GAGAL] Terjadi kesalahan saat komunikasi dengan Storage: '.$e->getMessage());

                return 1;
            }
        } else {
            $this->newLine();
            $this->line('Tip: Tambahkan opsi --upload untuk menguji tulis/baca berkas dummy langsung ke storage server:');
            $this->comment('     php artisan storage:kominfo-test --upload');
        }

        return 0;
    }
}
