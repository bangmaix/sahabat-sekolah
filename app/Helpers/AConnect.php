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
        try {
            return $this->client()->timeout(5)->get('/v1/data-sekolah')->json() ?? [];
        } catch (\Throwable $e) {
            return [];
        }
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
     * Mengambil detail daftar siswa dalam satu rombongan belajar (kelas).
     */
    public function getDataSiswaKelas(string $npsn, string $semester, string $rombonganBelajarId): array
    {
        return $this->client()->get("/v1/data-kelas/{$npsn}/{$semester}/{$rombonganBelajarId}")->json() ?? [];
    }

    /**
     * Mengambil seluruh data siswa di suatu sekolah untuk semester tertentu.
     * Mengambil daftar kelas terlebih dahulu, lalu menarik siswa seluruh rombel via parallel HTTP pool.
     * Data di-cache selama 1 jam untuk performa instan.
     *
     * @return list<array{nisn: string, nm_siswa: string, nm_kelas: string|null, rombongan_belajar_id: string|null}>
     */
    public function getSiswaSekolah(string $npsn, string $semester): array
    {
        if (empty($npsn) || empty($semester)) {
            return [];
        }

        $cacheKey = "siswa_sekolah_{$npsn}_{$semester}";

        return Cache::remember($cacheKey, 3600, function () use ($npsn, $semester) {
            $classesData = $this->getDataKelas($npsn, $semester);
            $classes = $classesData['data'] ?? [];

            if (empty($classes)) {
                return [];
            }

            $responses = Http::pool(function ($pool) use ($classes, $npsn, $semester) {
                return collect($classes)->map(function ($cls) use ($pool, $npsn, $semester) {
                    $rombelId = $cls['rombongan_belajar_id'];

                    return $pool->as($rombelId)
                        ->withHeaders(['x-api-key' => $this->apiKey])
                        ->baseUrl($this->baseUrl)
                        ->timeout(10)
                        ->get("/v1/data-kelas/{$npsn}/{$semester}/{$rombelId}");
                })->all();
            });

            $students = [];
            foreach ($classes as $cls) {
                $rombelId = $cls['rombongan_belajar_id'];
                $className = $cls['nm_kelas'] ?? '';
                $resp = $responses[$rombelId] ?? null;

                if ($resp && $resp->successful()) {
                    $json = $resp->json();
                    $members = $json['data'] ?? [];
                    foreach ($members as $m) {
                        if (! empty($m['nisn'])) {
                            $students[] = [
                                'nisn' => trim((string) $m['nisn']),
                                'nm_siswa' => trim((string) ($m['nm_siswa'] ?? '')),
                                'nm_kelas' => $className,
                                'rombongan_belajar_id' => $rombelId,
                            ];
                        }
                    }
                }
            }

            return collect($students)
                ->unique('nisn')
                ->sortBy('nm_siswa', SORT_NATURAL | SORT_FLAG_CASE)
                ->values()
                ->all();
        });
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
