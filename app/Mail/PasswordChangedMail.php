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

class PasswordChangedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Jumlah percobaan maksimal pengiriman email jika terjadi kendala jaringan/SMTP.
     */
    public int $tries = 5;

    /**
     * Waktu tunggu (detik) sebelum pengiriman ulang jika terjadi kegagalan sementara.
     */
    public int $backoff = 60;

    /**
     * @param  array{
     *     name: string,
     *     email: string,
     *     changed_at: string,
     *     ip_address: string,
     *     user_agent: ?string,
     *     unauthorized_url: string
     * }  $changeData
     */
    public function __construct(
        public array $changeData,
        public string $recipientName = 'Pengguna'
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Sahabat Sekolah] Peringatan Keamanan: Kata Sandi Akun Anda Telah Diubah',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.password_changed',
        );
    }

    /**
     * Catat log jika email gagal dikirim setelah seluruh percobaan habis.
     */
    public function failed(?Throwable $exception): void
    {
        Log::error('Pengiriman notifikasi email perubahan password gagal setelah mencapai batas maksimal percobaan.', [
            'recipient' => $this->recipientName,
            'email' => $this->changeData['email'] ?? null,
            'error' => $exception?->getMessage(),
        ]);
    }
}
