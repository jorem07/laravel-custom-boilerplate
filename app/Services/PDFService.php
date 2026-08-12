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
        
        $issuedTimestamp = $queue->time_start ?? $queue->created_at ?? Carbon::now();
        $issuedAt = Carbon::parse($issuedTimestamp)->format('Y-m-d h:i A');

        $qrCodeData = $this->generateQrCodeData(
            url('/queue/status/' . $queue->uuid)
        );
        $logoBase64 = $this->resolveLogoBase64();

        $pdf = Pdf::loadView('pdf.queue-receipt', [
            'queue' => $queue,
            'displayNumber' => $displayNumber,
            'issuedAt' => $issuedAt,
            'qrCodeBase64' => $qrCodeData['base64'],
            'qrCodeMime' => $qrCodeData['mime'],
            'logoBase64' => $logoBase64,
        ]);

        $pdf->setPaper([0, 0, 226.77, 566.93], 'portrait');

        return $pdf;
    }

    private function findQueue(array $payload): Queue
    {
        $query = Queue::query();

        if (!empty($payload['id'])) {
            return $query->findOrFail($payload['id']);
        }

        return $query->where('uuid', $payload['uuid'])->firstOrFail();
    }

    private function formatDisplayNumber(string $queueNo): string
    {
        if (preg_match('/-(\d+)$/', $queueNo, $matches)) {
            return str_pad($matches[1], 3, '0', STR_PAD_LEFT);
        }

        if (ctype_digit($queueNo)) {
            return str_pad($queueNo, 3, '0', STR_PAD_LEFT);
        }

        return $queueNo;
    }

    private function generateQrCodeData(string $content): array
    {
        try {
            $builder = new Builder(
                writer: new PngWriter(),
                data: $content,
                encoding: new Encoding('UTF-8'),
                errorCorrectionLevel: ErrorCorrectionLevel::Low,
                size: 180,
                margin: 0,
            );

            $result = $builder->build();
            return [
                'base64' => base64_encode($result->getString()),
                'mime' => 'image/png',
            ];
        } catch (\Throwable $e) {
            $builder = new Builder(
                writer: new SvgWriter(),
                data: $content,
                encoding: new Encoding('UTF-8'),
                errorCorrectionLevel: ErrorCorrectionLevel::Low,
                size: 180,
                margin: 0,
            );

            $result = $builder->build();
            return [
                'base64' => base64_encode($result->getString()),
                'mime' => 'image/svg+xml',
            ];
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
