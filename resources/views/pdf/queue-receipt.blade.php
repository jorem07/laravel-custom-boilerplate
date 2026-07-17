<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Queue Receipt</title>
    <style>
        @page {
            margin: 8mm 4mm;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, sans-serif;
            color: #111;
            text-align: center;
        }

        .receipt {
            width: 100%;
        }

        .logo {
            width: 42px;
            height: auto;
            margin: 0 auto 8px;
        }

        .heading {
            font-size: 11px;
            letter-spacing: 0.5px;
            margin: 0 0 10px;
            font-weight: normal;
        }

        .queue-number {
            font-family: DejaVu Sans Mono, monospace;
            font-size: 50pt;
            font-weight: bold;
            letter-spacing: 4px;
            line-height: 1;
            margin: 8px 0 14px;
        }

        .qr-label {
            font-size: 10px;
            letter-spacing: 0.4px;
            margin: 0 0 8px;
        }

        .qr-code {
            width: 120px;
            height: 120px;
            margin: 0 auto 14px;
        }

        .welcome {
            font-size: 11px;
            margin: 0 0 8px;
        }

        .timestamp {
            font-size: 10px;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="receipt">
        @if ($logoBase64)
            <img class="logo" src="data:image/png;base64,{{ $logoBase64 }}" alt="Logo">
        @else
            <svg class="logo" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg">
                <ellipse cx="32" cy="40" rx="18" ry="14" fill="none" stroke="#111" stroke-width="2"/>
                <circle cx="32" cy="22" r="10" fill="none" stroke="#111" stroke-width="2"/>
                <path d="M22 18 C18 8, 10 8, 12 18" fill="none" stroke="#111" stroke-width="2"/>
                <path d="M42 18 C46 8, 54 8, 52 18" fill="none" stroke="#111" stroke-width="2"/>
            </svg>
        @endif

        <p class="heading">PLEASE WAIT FOR YOUR SERVICE</p>

        <div class="queue-number">{{ $displayNumber }}</div>

        <p class="qr-label">PLS SCAN THIS QR CODE</p>

        <img
            class="qr-code"
            src="data:image/png;base64,{{ $qrCodeBase64 }}"
            alt="Queue QR Code"
        >

        <p class="welcome">WELCOME</p>
        <p class="timestamp">{{ $issuedAt }}</p>
    </div>
</body>
</html>
