<!DOCTYPE html>
<html>
<head>
    <title>Reset Your Password - Indonet Analytics Hub</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #8C3EFF;
            padding: 20px;
            text-align: center;
            color: white;
        }
        .content {
            padding: 20px;
            background-color: #f9f9f9;
        }
        .footer {
            padding: 10px 20px;
            text-align: center;
            font-size: 12px;
            color: #666666;
        }
        .btn {
            display: inline-block;
            background-color: #8C3EFF;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            margin: 20px 0;
        }
        .note {
            background-color: #fff3cd;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #ffc107;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Reset Your Password</h1>
        </div>
        <div class="content">
            <p>Hello,</p>

            <p>We received a request to reset the password for your account at Indonet Analytics Hub. Click the button below to set a new password:</p>

            <p>
                <a href="{{ config('app.url') }}/auth/reset-password/{{ $token }}?email={{ urlencode($email) }}" class="btn">Reset Password</a>
            </p>

            <div class="note">
                <p>If you didn't request a password reset, you can safely ignore this email. The link will expire in 60 minutes.</p>
            </div>

            <p>Thank you,<br>
            Indonet Analytics Hub Team</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Indonet Analytics Hub. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
