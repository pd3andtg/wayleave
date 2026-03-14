<?php

namespace App\Mail;

use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

// Sent to the requester when their company registration is rejected by Admin.
class CompanyRejected extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Company $company) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Company Registration Update — ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.company-rejected',
        );
    }
}
