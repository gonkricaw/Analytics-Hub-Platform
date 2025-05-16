<?php

namespace App\Services;

use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;

class EmailService
{
    /**
     * Send an email using a template.
     *
     * @param string $templateSlug The slug of the email template
     * @param string|array $to Recipient email address or array of addresses
     * @param array $data Data to populate the template placeholders
     * @param array $attachments Optional array of file attachments
     * @return bool Whether the email was sent successfully
     */
    public function sendTemplateEmail(string $templateSlug, $to, array $data, array $attachments = [])
    {
        try {
            // Find the active template by slug
            $template = EmailTemplate::where('slug', $templateSlug)
                                   ->where('is_active', true)
                                   ->first();

            if (!$template) {
                \Log::error("Email template not found or not active: {$templateSlug}");
                return false;
            }

            // Add the current year to data if not provided
            if (!isset($data['year'])) {
                $data['year'] = date('Y');
            }

            // Parse the template with the provided data
            $parsedTemplate = $template->parse($data);

            // Set up sender information
            $senderName = $template->sender_name ?? config('mail.from.name');
            $senderEmail = $template->sender_email ?? config('mail.from.address');

            // Send the email
            $sent = Mail::send([], [], function (Message $message) use ($to, $parsedTemplate, $senderName, $senderEmail, $attachments) {
                $message->to($to)
                        ->subject($parsedTemplate['subject'])
                        ->from($senderEmail, $senderName);

                // Set HTML content
                $message->setBody($parsedTemplate['body'], 'text/html');

                // Add plain text version if available
                if (!empty($parsedTemplate['plain_text'])) {
                    $message->addPart($parsedTemplate['plain_text'], 'text/plain');
                }

                // Add attachments if any
                foreach ($attachments as $attachment) {
                    if (is_array($attachment) && isset($attachment['path']) && isset($attachment['name'])) {
                        $message->attach($attachment['path'], ['as' => $attachment['name']]);
                    } elseif (is_string($attachment) && file_exists($attachment)) {
                        $message->attach($attachment);
                    }
                }
            });

            if ($sent === 0) {
                \Log::info("Email sent successfully to: " . (is_array($to) ? implode(', ', $to) : $to));
                return true;
            } else {
                \Log::error("Failed to send email to: " . (is_array($to) ? implode(', ', $to) : $to));
                return false;
            }
        } catch (\Exception $e) {
            \Log::error("Error sending email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send a welcome email to a new user.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function sendWelcomeEmail($user)
    {
        $loginUrl = url('/login');

        $data = [
            'name' => $user->name,
            'login_url' => $loginUrl
        ];

        return $this->sendTemplateEmail('welcome-email', $user->email, $data);
    }

    /**
     * Send a password reset email.
     *
     * @param \App\Models\User $user
     * @param string $token
     * @return bool
     */
    public function sendPasswordResetEmail($user, $token)
    {
        $resetUrl = url('/password/reset/' . $token . '?email=' . urlencode($user->email));

        $data = [
            'name' => $user->name,
            'reset_url' => $resetUrl
        ];

        return $this->sendTemplateEmail('password-reset', $user->email, $data);
    }

    /**
     * Send a terms acceptance confirmation email.
     *
     * @param \App\Models\User $user
     * @param \App\Models\TermAndCondition $terms
     * @param string $acceptanceDate
     * @return bool
     */
    public function sendTermsAcceptanceEmail($user, $terms, $acceptanceDate)
    {
        $data = [
            'name' => $user->name,
            'acceptance_date' => $acceptanceDate,
            'terms_version' => $terms->version
        ];

        return $this->sendTemplateEmail('terms-acceptance', $user->email, $data);
    }
}
