<?php

namespace App\Helpers;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AppStorage
{
    /**
     * Dapatkan nama disk untuk penyimpanan privat (dokumen kasus, bukti kasus, sertifikat).
     */
    public static function privateDisk(): string
    {
        return (string) config('filesystems.default_private', 'local');
    }

    /**
     * Dapatkan nama disk untuk penyimpanan publik (foto profil/prestasi, aset umum).
     */
    public static function publicDisk(): string
    {
        return (string) config('filesystems.default_public', 'public');
    }

    /**
     * Dapatkan instance storage untuk disk privat.
     */
    public static function private(): Filesystem
    {
        return Storage::disk(static::privateDisk());
    }

    /**
     * Dapatkan instance storage untuk disk publik.
     */
    public static function public(): Filesystem
    {
        return Storage::disk(static::publicDisk());
    }

    /**
     * Simpan file unggahan ke disk privat atau publik.
     */
    public static function store(UploadedFile $file, string $directory, string $type = 'private'): string|false
    {
        $disk = $type === 'public' ? static::publicDisk() : static::privateDisk();

        return $file->store($directory, $disk);
    }

    /**
     * Cari disk tempat suatu berkas privat tersimpan (cek disk aktif, fallback ke local & public).
     * Berguna agar berkas yang pernah diunggah di local sebelum migrasi tetap dapat diakses di server.
     */
    public static function resolveDiskForFile(?string $path, string $preferredType = 'private'): ?string
    {
        if (empty($path)) {
            return null;
        }

        $preferredDisk = $preferredType === 'public' ? static::publicDisk() : static::privateDisk();

        // 1. Cek disk utama yang terkonfigurasi
        try {
            if (Storage::disk($preferredDisk)->exists($path)) {
                return $preferredDisk;
            }
        } catch (\Throwable $e) {
            // Abaikan jika driver disk belum siap atau gagal terhubung
        }

        // 2. Fallback disk untuk backward-compatibility (berkas lama di local/public)
        $fallbackDisks = array_unique(array_filter([
            'local',
            'public',
            's3-private',
            's3-public',
            's3',
        ], fn ($disk) => $disk !== $preferredDisk));

        foreach ($fallbackDisks as $disk) {
            try {
                if (config("filesystems.disks.{$disk}") && Storage::disk($disk)->exists($path)) {
                    return $disk;
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        return null;
    }

    /**
     * Download berkas dengan deteksi disk otomatis (mendukung local maupun remote S3 Kominfo).
     */
    public static function download(string $path, ?string $name = null, array $headers = []): StreamedResponse
    {
        $disk = static::resolveDiskForFile($path, 'private') ?? static::privateDisk();

        abort_unless(Storage::disk($disk)->exists($path), 404, 'Berkas tidak ditemukan.');

        return Storage::disk($disk)->download($path, $name, $headers);
    }

    /**
     * Stream response berkas untuk preview/inline browser dengan deteksi disk otomatis.
     */
    public static function response(string $path, ?string $name = null, array $headers = []): StreamedResponse
    {
        $disk = static::resolveDiskForFile($path, 'private') ?? static::privateDisk();

        abort_unless(Storage::disk($disk)->exists($path), 404, 'Berkas tidak ditemukan.');

        return Storage::disk($disk)->response($path, $name, $headers);
    }

    /**
     * Hapus berkas dari disk terkait.
     */
    public static function delete(?string $path, string $type = 'private'): bool
    {
        if (empty($path)) {
            return false;
        }

        $disk = static::resolveDiskForFile($path, $type) ?? ($type === 'public' ? static::publicDisk() : static::privateDisk());

        try {
            if (Storage::disk($disk)->exists($path)) {
                return Storage::disk($disk)->delete($path);
            }
        } catch (\Throwable $e) {
            return false;
        }

        return false;
    }

    /**
     * Dapatkan URL publik untuk berkas publik.
     */
    public static function url(string $path): string
    {
        $disk = static::resolveDiskForFile($path, 'public') ?? static::publicDisk();

        return Storage::disk($disk)->url($path);
    }
}
