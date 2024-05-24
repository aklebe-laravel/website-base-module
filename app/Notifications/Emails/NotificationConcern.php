<?php

namespace Modules\WebsiteBase\app\Notifications\Emails;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Blade;
use Modules\WebsiteBase\app\Models\NotificationConcern as NotificationConcernModel;

class NotificationConcern extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * True to allow add meta data user id
     *
     * @var bool
     */
    public bool $autoMetaData = true;

    /**
     * public properties are accessible in view template
     * @var User
     */
    public User $user;

    /**
     * @var NotificationConcernModel
     */
    public NotificationConcernModel $notificationConcern;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, NotificationConcernModel $notificationConcern, array $viewData = [],
        array $tags = [], array $metaData = [])
    {
        $this->user = $user;
        $this->notificationConcern = $notificationConcern;
        $this->viewData = $viewData;
        $this->tags = $tags;
        $this->metadata = $metaData;
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

        $this->subject = $this->notificationConcern->notificationTemplate->subject;

        if ($this->subject) {
            $this->subject = $this->renderStringWithBlade($this->subject);
        }

        if ($this->autoMetaData) {
            $this->metadata['user_id'] = $this->user->shared_id;
        }

        return new Envelope(from: $fromAddress, to: $toAddress->address, // object not allowed
            subject: $this->subject, tags: $this->tags, metadata: $this->metadata);
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        if ($html = $this->notificationConcern->getContent()) {

            // Render the content dynamically
            $html = $this->renderStringWithBlade($html);

        }

        return new Content(htmlString: $html);
    }

    /**
     * @param  string  $content
     * @return string
     */
    protected function renderStringWithBlade(string $content): string
    {
        return Blade::render($content, array_merge(['user' => $this->user], $this->viewData));
    }

}
