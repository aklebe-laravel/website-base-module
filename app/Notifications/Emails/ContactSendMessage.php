<?php

namespace Modules\WebsiteBase\app\Notifications\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class ContactSendMessage extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * public properties are accessible in view template
     * @var array
     */
    public array $contactData;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(array $contactData)
    {
        $this->contactData = $contactData;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        $toAddress = new Address(config('mail.from.address'), 'Contact Form');
        $fromAddress = new Address(config('mail.from.address'), config('mail.from.name'));

        return new Envelope(from: $fromAddress, to: $toAddress->address, // object not allowed
            subject: __('Contact Request '.config('app.name')), tags: ['contact'], metadata: [
                'user_id' => Auth::id(),
            ],);
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(view: 'notifications.emails.contact-request');
    }

}
