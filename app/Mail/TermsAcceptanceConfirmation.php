<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use App\Models\TermAndCondition;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TermsAcceptanceConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * The user instance.
     *
     * @var \App\Models\User
     */
    public $user;

    /**
     * The terms and conditions instance.
     *
     * @var \App\Models\TermAndCondition
     */
    public $terms;

    /**
     * The acceptance date.
     *
     * @var string
     */
    public $acceptanceDate;

    /**
     * The parsed email template.
     *
     * @var array
     */
    protected $parsedTemplate;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, TermAndCondition $terms, string $acceptanceDate)
    {
        $this->user = $user;
        $this->terms = $terms;
        $this->acceptanceDate = $acceptanceDate;

        // Parse the email template
        $template = EmailTemplate::where('slug', 'terms-acceptance')
                             ->where('is_active', true)
                             ->first();

        if ($template) {
            $data = [
                'name' => $user->name,
                'acceptance_date' => $acceptanceDate,
                'terms_version' => $terms->version,
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
            subject: $this->parsedTemplate['subject'] ?? 'Terms and Conditions Acceptance Confirmation',
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

        // Fallback to a simple text version
        return new Content(
            htmlString: '
            <h1>Terms and Conditions Acceptance</h1>
            <p>Hello ' . $this->user->name . ',</p>
            <p>This email confirms that you have accepted the Terms and Conditions for the Indonet Analytics Hub Platform on ' . $this->acceptanceDate . '.</p>
            <p>Terms and Conditions Version: ' . $this->terms->version . '</p>
            <p>If you did not perform this action, please contact our support team immediately.</p>
            '
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
