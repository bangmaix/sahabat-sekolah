<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

echo "=== TESTING FORM PEMBUATAN LAPORAN ===\n";

$school_npsn = '10307412';
$category = DB::table('bullying_categories')->first();

// Test Case 1: Form valid (Mode Rahasia dengan Nama Pelapor)
$session = $app->make('session')->driver();
$session->start();
$token = $session->token();

$reqDataValid = [
    '_token' => $token,
    'school_npsn' => $school_npsn,
    'reporter_role' => 'VICTIM',
    'identity_mode' => 'CONFIDENTIAL',
    'reporter_name' => 'Ahmad Siswa Test',
    'reporter_contact' => '08123456789',
    'category' => $category->name,
    'description' => 'Saya mengalami ancaman lewat aplikasi WA berkali-kali di lingkungan sekolah.',
];

$request = Request::create('/reports', 'POST', $reqDataValid);
$request->setLaravelSession($session);

$response = $kernel->handle($request);
echo '[TEST 1] Form Valid (CONFIDENTIAL) -> Status: '.$response->getStatusCode().' | Redirect: '.$response->headers->get('Location')."\n";

// Test Case 2: Form Anonim valid
$session2 = $app->make('session')->driver();
$session2->start();
$token2 = $session2->token();

$reqDataAnon = [
    '_token' => $token2,
    'school_npsn' => $school_npsn,
    'reporter_role' => 'WITNESS',
    'identity_mode' => 'ANONYMOUS',
    'category' => $category->name,
    'description' => 'Melihat kejadian pemerasan uang jajan di dekat kantin sekolah jam istirahat.',
];

$request2 = Request::create('/reports', 'POST', $reqDataAnon);
$request2->setLaravelSession($session2);

$response2 = $kernel->handle($request2);
echo '[TEST 2] Form Valid (ANONYMOUS) -> Status: '.$response2->getStatusCode().' | Redirect: '.$response2->headers->get('Location')."\n";

echo "=== TEST REPORT CREATION COMPLETED ===\n";
