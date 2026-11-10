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

class TestQueueMail extends Mailable implements ShouldQueue
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

    public function __construct(
        public string $contentMessage = 'Uji coba antrean email SMTP Laravel'
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Test Queue Pengiriman Email SMTP - '.config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: '<div style="font-family: sans-serif; padding: 20px; line-height: 1.6;">'
                .'<h2 style="color: #1e3a8a;">Sahabat Sekolah - Test Queue SMTP</h2>'
                .'<p>'.htmlspecialchars($this->contentMessage).'</p>'
                .'<hr style="border: none; border-top: 1px solid #e2e8f0; margin: 20px 0;">'
                .'<p style="font-size: 12px; color: #64748b;">Waktu dijadwalkan: '.now()->toDateTimeString().' | Driver: '.config('queue.default').'</p>'
                .'</div>',
        );
    }

    /**
     * Catat log jika email pengujian gagal dikirim setelah seluruh percobaan habis.
     */
    public function failed(?Throwable $exception): void
    {
        Log::error('TestQueueMail gagal dikirim setelah mencapai batas maksimal percobaan.', [
            'error' => $exception?->getMessage(),
        ]);
    }
}
