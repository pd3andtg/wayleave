<?php

namespace App\Mail;

use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

// Sent to the requester when their company registration is approved by Admin.
// Informs them they can now proceed to register a user account.
class CompanyApproved extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Company $company) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Company Registration Approved — ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.company-approved',
        );
    }
}
