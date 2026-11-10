<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Mail;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

try {
    echo 'Mencoba mengirim email via '.config('mail.mailers.smtp.host').':'.config('mail.mailers.smtp.port')."...\n";
    Mail::raw('Tes koneksi email dari Sahabat Sekolah', function ($message) {
        $message->to('uum1612@gmail.com')
            ->subject('Tes SMTP Sahabat Sekolah');
    });
    echo "BERHASIL TERKIRIM!\n";
} catch (Throwable $e) {
    echo 'GAGAL: '.$e->getMessage()."\n";
    if ($e->getPrevious()) {
        echo 'PREVIOUS: '.$e->getPrevious()->getMessage()."\n";
    }
}
