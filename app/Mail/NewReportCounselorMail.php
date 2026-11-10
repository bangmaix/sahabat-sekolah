<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class NewReportCounselorMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Jumlah percobaan maksimal pengiriman email jika terjadi error/penolakan.
     */
    public int $tries = 5;

    /**
     * Waktu tunggu (detik) sebelum pengiriman ulang jika server SMTP menolak (denied) atau gagal.
     * Jeda 60 detik mencegah server terkena banned atau rate limit dari penyedia SMTP.
     */
    public int $backoff = 60;

    /**
     * @param  array<string, mixed>  $reportData
     */
    public function __construct(
        public array $reportData,
        public string $recipientName = 'Guru BK'
    ) {}

    public function envelope(): Envelope
    {
        $caseNumber = $this->reportData['case_number'] ?? 'Kasus';
        $schoolName = $this->reportData['school_name'] ?? 'Sekolah';

        return new Envelope(
            subject: "[Sahabat Sekolah] Laporan Baru Masuk: {$caseNumber} - {$schoolName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new_report_counselor',
        );
    }

    /**
     * Catat log jika email gagal dikirim setelah seluruh percobaan habis.
     */
    public function failed(?Throwable $exception): void
    {
        Log::error('Pengiriman notifikasi email laporan baru gagal setelah mencapai batas maksimal percobaan.', [
            'recipient' => $this->recipientName,
            'case_number' => $this->reportData['case_number'] ?? null,
            'error' => $exception?->getMessage(),
        ]);
    }
}
