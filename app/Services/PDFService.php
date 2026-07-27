<?php

namespace App\Services;

use App\Models\Queue;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Support\Facades\File;

class PDFService
{
    public function queueReceipt(array $payload)
    {
        $queue = $this->findQueue($payload);

        $displayNumber = $queue->queue_no;
        $serviceName = $queue->office_service?->name ?? 'General Service';
        $estimatedWait = $queue->office_service?->avg_time ?? null;
        $priority = $queue->priority ?? 'Normal';

        $createdTimeObj = Carbon::parse($queue->created_at ?? $queue->time_start ?? now());
        $createdDate = $createdTimeObj->format('F j, Y');
        $createdTime = $createdTimeObj->format('g:i A');

        // Build QR code content using the frontend URL so customers can track their queue
        $frontendUrl = rtrim(config('app.frontend_url', 'http://localhost:3000'), '/');
        $qrContent = $frontendUrl . '/queue_mobile/status?uuid=' . $queue->uuid;

        $qrCodeBase64 = $this->generateQrCodeBase64($qrContent);
        $logoBase64 = $this->resolveLogoBase64();

        $paper = $payload['paper'] ?? '58mm';

        $pdf = Pdf::loadView('pdf.queue-receipt', [
            'queue'          => $queue,
            'displayNumber'  => $displayNumber,
            'serviceName'    => $serviceName,
            'priority'       => $priority,
            'estimatedWait'  => $estimatedWait,
            'createdDate'    => $createdDate,
            'createdTime'    => $createdTime,
            'qrCodeBase64'   => $qrCodeBase64,
            'logoBase64'     => $logoBase64,
            'paper'          => $paper,
        ]);

        if ($paper === '80mm') {
            // 80mm width = 226.77pt, 148mm height = 420.00pt
            $pdf->setPaper([0, 0, 226.77, 420.00], 'portrait');
        } else {
            // 58mm width = 164.41pt, 100mm height = 283.46pt
            $pdf->setPaper([0, 0, 164.41, 283.46], 'portrait');
        }

        return $pdf;
    }

    private function findQueue(array $payload): Queue
    {
        $query = Queue::with('office_service');

        if (!empty($payload['id'])) {
            return $query->findOrFail($payload['id']);
        }

        return $query->where('uuid', $payload['uuid'])->firstOrFail();
    }

    private function generateQrCodeBase64(string $content): string
    {
        try {
            $builder = new Builder(
                writer: new SvgWriter(),
                data: $content,
                encoding: new Encoding('UTF-8'),
                errorCorrectionLevel: ErrorCorrectionLevel::Low,
                size: 180,
                margin: 0,
            );

            $result = $builder->build();
            return base64_encode($result->getString());
        } catch (\Throwable $e) {
            // Fallback lightweight SVG inline generator
            $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="180" height="180" viewBox="0 0 180 180"><rect width="180" height="180" fill="#fff"/><rect x="10" y="10" width="60" height="60" fill="#000"/><rect x="20" y="20" width="40" height="40" fill="#fff"/><rect x="30" y="30" width="20" height="20" fill="#000"/><rect x="110" y="10" width="60" height="60" fill="#000"/><rect x="120" y="20" width="40" height="40" fill="#fff"/><rect x="130" y="30" width="20" height="20" fill="#000"/><rect x="10" y="110" width="60" height="60" fill="#000"/><rect x="20" y="120" width="40" height="40" fill="#fff"/><rect x="30" y="130" width="20" height="20" fill="#000"/></svg>';
            return base64_encode($svg);
        }
    }

    private function resolveLogoBase64(): ?string
    {
        $logoPath = public_path('images/receipt-logo.png');

        if (!File::exists($logoPath)) {
            return null;
        }

        return base64_encode(File::get($logoPath));
    }
}
