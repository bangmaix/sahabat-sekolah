<?php

namespace App\Helpers;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class AConnect
{
    public function __construct(
        protected string $baseUrl = 'https://api.bukittinggikota.go.id/sekolah',
        protected string $apiKey = 'l5j4zTkz3xMV35pSqTkc9Epe800y1BoB'
    ) {}

    /**
     * Get the configured HTTP client.
     */
    protected function client(): PendingRequest
    {
        return Http::withHeaders([
            'x-api-key' => $this->apiKey,
        ])->baseUrl($this->baseUrl);
    }

    /**
     * Mengambil detail data siswa berdasarkan NPSN, Semester, dan NISN.
     */
    public function getDataSiswa(string $npsn, string $semester, string $nisn): array
    {
        return $this->client()->get("/v1/data-siswa/{$npsn}/{$semester}/{$nisn}")->json() ?? [];
    }

    /**
     * Mengambil daftar kelas (rombongan belajar) berdasarkan NPSN dan Semester.
     */
    public function getDataKelas(string $npsn, string $semester): array
    {
        return $this->client()->get("/v1/data-kelas/{$npsn}/{$semester}")->json() ?? [];
    }

    public function getDataSekolah()
    {
        return $this->client()->get('/v1/data-sekolah')->json() ?? [];
    }

    /**
     * Get a sorted list of school objects.
     */
    public function getSekolahList()
    {
        $data = Cache::remember('sekolah_list', 3600, function () {
            return $this->getDataSekolah()['data'] ?? [];
        });

        return collect($data)
            ->map(fn ($item) => (object) $item)
            ->sortBy('nama_sekolah')
            ->values();
    }

    /**
     * Get a map of NPSN => nama_sekolah.
     */
    public function getSekolahMap()
    {
        return Cache::remember('sekolah_map', 3600, function () {
            return $this->getSekolahList()->pluck('nama_sekolah', 'npsn')->toArray();
        });
    }

    /**
     * Mengambil data siswa dalam satu rombongan belajar.
     */
    public function getDataSiswaKelas(string $npsn, string $semester, string $rombonganBelajarId): array
    {
        return $this->client()->get("/v1/data-siswa-kelas/{$npsn}/{$semester}/{$rombonganBelajarId}")->json() ?? [];
    }

    /**
     * Health check endpoint.
     */
    public function healthCheck(): array
    {
        return $this->client()->get('/v1/health-check')->json() ?? [];
    }

    /**
     * Mengambil rekap data siswa untuk semua sekolah.
     */
    public function rekapDataSiswaAll(string $semester): array
    {
        return $this->client()->get("/v1/data-siswa/rekap/all/{$semester}")->json() ?? [];
    }

    /**
     * Mengambil rekap data siswa per sekolah.
     */
    public function rekapDataSiswa(string $npsn, string $semester): array
    {
        return $this->client()->get("/v1/data-siswa/rekap/{$npsn}/{$semester}")->json() ?? [];
    }
}
