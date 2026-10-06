<?php

namespace App\Http\Controllers;

use App\Helpers\AConnect;
use Illuminate\Support\Facades\DB;
use Throwable;

class SystemController extends Controller
{
    public function healthDetails()
    {
        try {
            DB::select('select 1');
            $database = 'ok';
        } catch (Throwable) {
            $database = 'failed';
        }

        return response()->json([
            'status' => $database === 'ok' && is_writable(storage_path()) ? 'ok' : 'degraded',
            'checks' => [
                'database' => $database,
                'storage' => is_writable(storage_path()) ? 'ok' : 'failed',
            ],
            'timestamp' => now()->toIso8601String(),
        ], $database === 'ok' ? 200 : 503);
    }

    public function apiTest()
    {
        $connect = new AConnect;

        return $connect->getDataSiswa('12345678', '20241', '1234567890');
    }
}
