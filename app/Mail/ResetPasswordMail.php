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

class ResetPasswordMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Jumlah percobaan maksimal pengiriman email jika terjadi kendala jaringan/SMTP.
     */
    public int $tries = 5;

    /**
     * Waktu tunggu (detik) sebelum percobaan ulang.
     */
    public int $backoff = 60;

    /**
     * @param  array{reset_url: string, expires_minutes: int, email: string}  $resetData
     */
    public function __construct(
        public array $resetData,
        public string $recipientName = 'Pengguna'
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Sahabat Sekolah] Atur Ulang Kata Sandi Akun Anda',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reset_password',
        );
    }

    /**
     * Catat log jika pengiriman gagal setelah mencapai batas percobaan.
     */
    public function failed(?Throwable $exception): void
    {
        Log::error('Pengiriman email reset password gagal setelah mencapai batas percobaan.', [
            'recipient' => $this->recipientName,
            'email' => $this->resetData['email'] ?? null,
            'error' => $exception?->getMessage(),
        ]);
    }
}
