<!DOCTYPE html>
<html>
<head>
    <title>Welcome to Indonet Analytics Hub</title>
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
        .credentials {
            background-color: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to Indonet Analytics Hub</h1>
        </div>
        <div class="content">
            <p>Hello {{ $user->name }},</p>

            <p>You have been invited to join the Indonet Analytics Hub platform. Below are your temporary login credentials:</p>

            <div class="credentials">
                <p><strong>Email:</strong> {{ $user->email }}</p>
                <p><strong>Temporary Password:</strong> {{ $tempPassword }}</p>
            </div>

            <p>For security reasons, you will be required to change your password when you log in for the first time.</p>

            <p>
                <a href="{{ config('app.url') }}/login" class="btn">Login Now</a>
            </p>

            <p>If you have any questions, please contact the administrator.</p>

            <p>Thank you,<br>
            Indonet Analytics Hub Team</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Indonet Analytics Hub. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
