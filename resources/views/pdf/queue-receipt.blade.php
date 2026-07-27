<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PAO Queue Ticket</title>
    <style>
        @page {
            size: {{ ($paper ?? '58mm') === '80mm' ? '80mm 148mm' : '58mm 100mm' }};
            margin: 0;
        }

        html, body {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, sans-serif;
            color: #000;
            text-align: center;
            background: #fff;
            width: 100%;
        }

        .receipt {
            width: 100%;
            box-sizing: border-box;
            padding: 3mm 3.5mm;
            margin: 0 auto;
        }

        .header-table {
            width: 100%;
            margin-bottom: 2px;
        }

        .header-title {
            font-size: 11pt;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-align: center;
            vertical-align: middle;
        }

        .divider-thick {
            border-top: 2px solid #000;
            margin: 4px 0;
            width: 100%;
        }

        .divider-thin {
            border-top: 1px dashed #555;
            margin: 4px 0;
            width: 100%;
        }

        .queue-number {
            font-family: DejaVu Sans, sans-serif;
            font-size: 38pt;
            font-weight: bold;
            letter-spacing: 1px;
            line-height: 1;
            margin: 3px 0;
        }

        .service-name {
            font-size: 12pt;
            font-weight: bold;
            margin: 0 0 4px;
            color: #000;
        }

        /* Info table rows (label: value) */
        .info-table {
            width: 100%;
            margin: 0 0 4px;
            border-collapse: collapse;
        }

        .info-label {
            font-size: 8.5pt;
            font-weight: bold;
            text-align: left;
            vertical-align: top;
            color: #333;
            width: 42%;
            padding: 1px 2px 1px 0;
        }

        .info-sep {
            font-size: 8.5pt;
            text-align: center;
            vertical-align: top;
            width: 6%;
            padding: 1px 1px;
        }

        .info-value {
            font-size: 8.5pt;
            text-align: left;
            vertical-align: top;
            width: 52%;
            padding: 1px 0 1px 2px;
        }

        /* Priority badge — only visible for PWD / Senior */
        .priority-badge {
            display: inline-block;
            font-size: 8pt;
            font-weight: bold;
            padding: 1px 5px;
            border: 1px solid #000;
            border-radius: 3px;
            letter-spacing: 0.5px;
        }

        .footer-table {
            width: 100%;
            margin-top: 4px;
        }

        .qr-cell {
            width: 45%;
            text-align: left;
            vertical-align: middle;
        }

        .qr-code {
            width: 72px;
            height: 72px;
            display: block;
        }

        .footer-text-cell {
            width: 55%;
            text-align: left;
            vertical-align: middle;
            font-size: 8.5pt;
            font-weight: bold;
            line-height: 1.3;
            color: #000;
            padding-left: 4px;
        }

        .footer-text-light {
            font-weight: normal;
            font-size: 7.5pt;
            color: #333;
            margin-top: 2px;
        }
    </style>
</head>
<body>
    <div class="receipt">
        <!-- Header Row -->
        <table class="header-table" cellpadding="0" cellspacing="0">
            <tr>
                <td style="width: 32px; text-align: left; vertical-align: middle;">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="11" fill="#000"/>
                        <path d="M12 5L17 7.5V11.5C17 14.8 14.9 17.8 12 19C9.1 17.8 7 14.8 7 11.5V7.5L12 5Z" stroke="#fff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M10 11.5L11.5 13L14.5 10" stroke="#fff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </td>
                <td class="header-title">
                    PAO QUEUE TICKET
                </td>
            </tr>
        </table>

        <!-- Thick Top Divider -->
        <div class="divider-thick"></div>

        <!-- Big Ticket Queue Number -->
        <div class="queue-number">{{ $displayNumber }}</div>

        <!-- Service Name -->
        <div class="service-name">{{ $serviceName }}</div>

        <!-- Info Table: Priority (PWD / Senior only), Date, Time, Estimated Wait -->
        <table class="info-table" cellpadding="0" cellspacing="0">
            @php
                $showPriority = isset($priority) && in_array(strtolower(trim($priority)), [
                    'pwd', 'senior citizen', 'senior'
                ]);
            @endphp

            @if ($showPriority)
            <tr>
                <td class="info-label">Priority</td>
                <td class="info-sep">:</td>
                <td class="info-value">
                    <span class="priority-badge">{{ strtoupper($priority) }}</span>
                </td>
            </tr>
            @endif

            <tr>
                <td class="info-label">Issued</td>
                <td class="info-sep">:</td>
                <td class="info-value">{{ $createdDate }}<br>{{ $createdTime }}</td>
            </tr>

            @if (!empty($estimatedWait))
            <tr>
                <td class="info-label">Est. Wait</td>
                <td class="info-sep">:</td>
                <td class="info-value">{{ $estimatedWait }}</td>
            </tr>
            @endif
        </table>

        <!-- Thick Bottom Divider -->
        <div class="divider-thick"></div>

        <!-- Footer Section (QR Code & Instruction Text) -->
        <table class="footer-table" cellpadding="0" cellspacing="0">
            <tr>
                <td class="qr-cell">
                    <img
                        class="qr-code"
                        src="data:image/svg+xml;base64,{{ $qrCodeBase64 }}"
                        alt="Queue QR Code"
                    >
                </td>
                <td class="footer-text-cell">
                    Scan QR Code<br>
                    <span class="footer-text-light">
                        Track your queue<br>
                        status using your<br>
                        mobile phone.
                    </span>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
