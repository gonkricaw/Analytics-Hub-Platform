<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserInvitation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * The user instance.
     *
     * @var \App\Models\User
     */
    public $user;

    /**
     * The temporary password.
     *
     * @var string
     */
    public $tempPassword;

    /**
     * The parsed email template.
     *
     * @var array
     */
    protected $parsedTemplate;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, string $tempPassword)
    {
        $this->user = $user;
        $this->tempPassword = $tempPassword;

        // Parse the email template
        $template = EmailTemplate::where('slug', 'welcome-email')
                             ->where('is_active', true)
                             ->first();

        if ($template) {
            $loginUrl = url('/login');

            $data = [
                'name' => $user->name,
                'login_url' => $loginUrl,
                'email' => $user->email,
                'password' => $tempPassword,
                'year' => date('Y')
            ];

            $this->parsedTemplate = $template->parse($data);
        }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->parsedTemplate['subject'] ?? 'Welcome to Indonet Analytics Hub',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // If we have a parsed template, use it directly
        if (!empty($this->parsedTemplate['body'])) {
            return new Content(
                htmlString: $this->parsedTemplate['body']
            );
        }

        // Fallback to the blade template
        return new Content(
            view: 'emails.user-invitation',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
