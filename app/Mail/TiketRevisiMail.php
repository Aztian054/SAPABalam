<?php

namespace App\Mail;

use App\Models\Tiket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class TiketRevisiMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Tiket $tiket, public ?string $catatan = null) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Permohonan Anda Memerlukan Perbaikan (Revisi) - '.$this->tiket->kode_tiket,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.tiket.revisi',
            with: [
                'tiket' => $this->tiket,
                'catatan' => $this->catatan,
                'urlLoket' => URL::route('loket.show', $this->tiket->id),
            ],
        );
    }
}
