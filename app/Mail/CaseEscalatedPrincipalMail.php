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

class CaseEscalatedPrincipalMail extends Mailable implements ShouldQueue
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
     * @param  array<string, mixed>  $caseData
     * @param  array<string, mixed>  $escalationData
     */
    public function __construct(
        public array $caseData,
        public array $escalationData,
        public string $recipientName = 'Kepala Sekolah'
    ) {}

    public function envelope(): Envelope
    {
        $caseNumber = $this->caseData['case_number'] ?? 'Kasus';
        $schoolName = $this->caseData['school_name'] ?? 'Sekolah';

        return new Envelope(
            subject: "[Sahabat Sekolah] Eskalasi Kasus oleh Guru BK: {$caseNumber} - {$schoolName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.case_escalated_principal',
        );
    }

    /**
     * Catat log jika email eskalasi gagal dikirim setelah seluruh percobaan habis.
     */
    public function failed(?Throwable $exception): void
    {
        Log::error('Pengiriman notifikasi email eskalasi kasus ke Kepala Sekolah gagal setelah mencapai batas maksimal percobaan.', [
            'recipient' => $this->recipientName,
            'case_number' => $this->caseData['case_number'] ?? null,
            'error' => $exception?->getMessage(),
        ]);
    }
}
