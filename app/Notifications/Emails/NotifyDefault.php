<?php

namespace Modules\WebsiteBase\app\Notifications\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Blade;
use Modules\WebsiteBase\app\Models\NotificationEvent;
use Modules\WebsiteBase\app\Models\User;
use Shipu\Themevel\Facades\Theme;

class NotifyDefault extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * public properties are accessible in view template
     * @var User
     */
    public User $user;

    /**
     * @var string
     */
    public string $channel = '';

    /**
     * @var NotificationEvent
     */
    public NotificationEvent $notifyEvent;

    /**
     * View parameters added to content_data
     *
     * @var array
     */
    public array $customContentData = [];

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, string $channel, NotificationEvent $notifyEvent,
        array $customContentData = [])
    {
        $this->user = $user;
        $this->channel = $channel;
        $this->notifyEvent = $notifyEvent;
        $this->customContentData = $customContentData;

        // Need to set active theme explicit because we do Blade::render() here
        Theme::set(config('theme.active'));
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        $toAddress = new Address($this->user->email, $this->user->name);
        $fromAddress = new Address(config('mail.from.address'), config('mail.from.name'));

        $this->subject = $this->notifyEvent->getSubject($this->channel);
        if ($this->subject) {
            $this->subject = Blade::render($this->subject, $this->getContentData());
        } else {
            $this->subject = 'Notification from "'.config('app.name').'"!';
        }

        return new Envelope(from: $fromAddress, to: $toAddress->address, // object not allowed
            subject: $this->subject, tags: [
                'welcome',
                'greetings',
                'shop'
            ], metadata: [
                'user_id' => $this->user->shared_id ?? '', // string needed
            ],);
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        $html = $this->notifyEvent->getContent($this->channel);

        if ($html) {

            // Render the content dynamically
            $html = Blade::render($html, $this->getContentData());

        }

        return new Content(htmlString: $html);
    }

    /**
     * @return User[]
     */
    protected function getContentData(): array
    {
        return [
                'user' => $this->user
            ] + ($this->notifyEvent->content_data ?? []) + $this->customContentData;
    }

}
