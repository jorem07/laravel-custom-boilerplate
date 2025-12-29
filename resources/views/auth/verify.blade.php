<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
    body {
        font-family: 'Segoe UI', Helvetica, Arial, sans-serif;
        background-color: #f3f2f8;
        margin: 0;
        padding: 0;
    }
    .email-wrapper {
        width: 100%;
        padding: 30px 0;
        background-color: #f3f2f8;
    }
    .email-container {
        max-width: 640px;
        margin: 0 auto;
        background: #ffffff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 6px 16px rgba(0,0,0,0.08);
    }

    /* Header */
    .email-header {
        background-color: rgb(103, 58, 183);
        padding: 30px;
        text-align: center;
        color: #fff;
    }
    .email-header h2 {
        margin: 0;
        font-size: 28px;
        font-weight: 700;
    }

    /* Body */
    .email-body {
        padding: 35px;
        color: #333;
        line-height: 1.7;
    }
    .email-body h1 {
        margin-top: 0;
        color: #650d77;
        font-size: 26px;
        font-weight: 600;
    }
    .email-body p {
        margin: 16px 0;
        font-size: 16px;
    }
    .highlight {
        background: #f0e6ff;
        padding: 15px;
        border-left: 5px solid #650d77;
        margin: 20px 0;
        border-radius: 6px;
    }
    .btn {
        display: inline-block;
        padding: 14px 32px;
        background: rgb(103, 58, 183);
        color: #fff !important;
        text-decoration: none;
        border-radius: 8px;
        font-weight: bold;
        font-size: 16px;
        margin-top: 25px;
        transition: all 0.3s ease;
    }
    .btn:hover {
        background: linear-gradient(135deg, #9500c2, #650d77);
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }

    /* Footer */
    .email-footer {
        padding: 25px;
        text-align: center;
        font-size: 13px;
        color: #fff;
        background-color: rgb(103, 58, 183);
    }
    .email-footer a {
        color: #ffd700;
        text-decoration: none;
        font-weight: 500;
    }
</style>
</head>
<body>
<div class="email-wrapper">
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <h2>AppName</h2>
        </div>

        <!-- Body -->
        <div class="email-body">
            <h1>Welcome to AppName 🎉</h1>
            <p>We’re thrilled to have you join our community of food lovers.</p>
            
            <div class="highlight">
                <p><strong>Here’s what you can look forward to:</strong></p>
                <ul>
                    <li>Wide selection of home-cooked meals</li>
                    <li>Easy bookings with top local chefs</li>
                    <li>Fast delivery right to your doorstep</li>
                </ul>
            </div>

            <p>To get started, please verify your email address by clicking the button below:</p>
            <a href="{{ $url }}" class="btn">Verify Email Address</a>

            <p>If the button doesn’t work, copy and paste this link into your browser:</p>
            <p><a href="{{ $url }}">{{ $url }}</a></p>

            <p>We can’t wait to see what you’ll cook up with AppName. Let’s make every meal memorable together!</p>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <p>&copy; {{ date('Y') }} AppName. All rights reserved.</p>
            <p><a href="{{ $url }}">Visit our website</a></p>
        </div>
    </div>
</div>
</body>
</html>

