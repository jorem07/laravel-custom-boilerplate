<?php

namespace App\Services;

use App\Models\Queue;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\File;

class PDFService
{
    public function queueReceipt(array $payload)
    {
        $queue = $this->findQueue($payload);

        // $displayNumber = $this->formatDisplayNumber($queue->queue_no);
        $displayNumber = $queue->queue_no;
        
        $issuedAt = Carbon::parse($queue->time_start)->format('Y-m-d H:i:s');
        $qrCodeBase64 = $this->generateQrCodeBase64(
            url('/queue/status/' . $queue->uuid)
        );
        $logoBase64 = $this->resolveLogoBase64();

        $pdf = Pdf::loadView('pdf.queue-receipt', [
            'queue' => $queue,
            'displayNumber' => $displayNumber,
            'issuedAt' => $issuedAt,
            'qrCodeBase64' => $qrCodeBase64,
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

    private function generateQrCodeBase64(string $content): string
    {
        $builder = new Builder(
            writer: new PngWriter(),
            data: $content,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Low,
            size: 180,
            margin: 0,
        );

        $result = $builder->build();

        return base64_encode($result->getString());
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
