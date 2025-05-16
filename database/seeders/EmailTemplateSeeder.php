<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Welcome email template
        EmailTemplate::create([
            'name' => 'Welcome Email',
            'slug' => 'welcome-email',
            'subject' => 'Welcome to Indonet Analytics Hub Platform',
            'body' => '
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #003366; padding: 20px; color: white; text-align: center; }
        .content { padding: 20px; background-color: #f9f9f9; }
        .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
        .btn { display: inline-block; padding: 10px 20px; background-color: #003366; color: white; text-decoration: none; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to Indonet Analytics Hub Platform</h1>
        </div>
        <div class="content">
            <h2>Hello {{name}},</h2>
            <p>Thank you for joining the Indonet Analytics Hub Platform. We\'re excited to have you on board!</p>
            <p>Your account has been created successfully. You can now login using your email address and password.</p>
            <p>To get started, click the button below:</p>
            <p style="text-align: center;">
                <a href="{{login_url}}" class="btn">Login to Your Account</a>
            </p>
            <p>If you have any questions or need assistance, please don\'t hesitate to contact our support team.</p>
            <p>Best regards,<br>The Indonet Analytics Hub Team</p>
        </div>
        <div class="footer">
            <p>&copy; {{year}} Indonet Analytics Hub. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
            ',
            'plain_text' => '
Hello {{name}},

Thank you for joining the Indonet Analytics Hub Platform. We\'re excited to have you on board!

Your account has been created successfully. You can now login using your email address and password.

To get started, visit: {{login_url}}

If you have any questions or need assistance, please don\'t hesitate to contact our support team.

Best regards,
The Indonet Analytics Hub Team

© {{year}} Indonet Analytics Hub. All rights reserved.
            ',
            'placeholders' => ['name', 'login_url', 'year'],
            'is_active' => true,
            'sender_name' => 'Indonet Analytics Hub',
            'sender_email' => 'noreply@indonet-analytics.com'
        ]);

        // Password reset email template
        EmailTemplate::create([
            'name' => 'Password Reset',
            'slug' => 'password-reset',
            'subject' => 'Reset Your Indonet Analytics Hub Password',
            'body' => '
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #003366; padding: 20px; color: white; text-align: center; }
        .content { padding: 20px; background-color: #f9f9f9; }
        .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
        .btn { display: inline-block; padding: 10px 20px; background-color: #003366; color: white; text-decoration: none; border-radius: 4px; }
        .warning { color: #cc0000; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Password Reset Request</h1>
        </div>
        <div class="content">
            <h2>Hello {{name}},</h2>
            <p>We received a request to reset your password for your Indonet Analytics Hub account.</p>
            <p>To reset your password, click the button below:</p>
            <p style="text-align: center;">
                <a href="{{reset_url}}" class="btn">Reset Password</a>
            </p>
            <p class="warning">This link will expire in 60 minutes.</p>
            <p>If you didn\'t request a password reset, you can ignore this email or contact our support team if you have concerns.</p>
            <p>Best regards,<br>The Indonet Analytics Hub Team</p>
        </div>
        <div class="footer">
            <p>&copy; {{year}} Indonet Analytics Hub. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
            ',
            'plain_text' => '
Hello {{name}},

We received a request to reset your password for your Indonet Analytics Hub account.

To reset your password, please visit this link:
{{reset_url}}

This link will expire in 60 minutes.

If you didn\'t request a password reset, you can ignore this email or contact our support team if you have concerns.

Best regards,
The Indonet Analytics Hub Team

© {{year}} Indonet Analytics Hub. All rights reserved.
            ',
            'placeholders' => ['name', 'reset_url', 'year'],
            'is_active' => true,
            'sender_name' => 'Indonet Analytics Hub',
            'sender_email' => 'noreply@indonet-analytics.com'
        ]);

        // Terms acceptance confirmation email template
        EmailTemplate::create([
            'name' => 'Terms Acceptance Confirmation',
            'slug' => 'terms-acceptance',
            'subject' => 'Terms and Conditions Acceptance Confirmation',
            'body' => '
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #003366; padding: 20px; color: white; text-align: center; }
        .content { padding: 20px; background-color: #f9f9f9; }
        .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Terms and Conditions Acceptance</h1>
        </div>
        <div class="content">
            <h2>Hello {{name}},</h2>
            <p>This email confirms that you have accepted the Terms and Conditions for the Indonet Analytics Hub Platform on {{acceptance_date}}.</p>
            <p>Terms and Conditions Version: {{terms_version}}</p>
            <p>If you did not perform this action, please contact our support team immediately.</p>
            <p>You can view the Terms and Conditions at any time by visiting your account settings.</p>
            <p>Best regards,<br>The Indonet Analytics Hub Team</p>
        </div>
        <div class="footer">
            <p>&copy; {{year}} Indonet Analytics Hub. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
            ',
            'plain_text' => '
Hello {{name}},

This email confirms that you have accepted the Terms and Conditions for the Indonet Analytics Hub Platform on {{acceptance_date}}.

Terms and Conditions Version: {{terms_version}}

If you did not perform this action, please contact our support team immediately.

You can view the Terms and Conditions at any time by visiting your account settings.

Best regards,
The Indonet Analytics Hub Team

© {{year}} Indonet Analytics Hub. All rights reserved.
            ',
            'placeholders' => ['name', 'acceptance_date', 'terms_version', 'year'],
            'is_active' => true,
            'sender_name' => 'Indonet Analytics Hub',
            'sender_email' => 'noreply@indonet-analytics.com'
        ]);
    }
}
