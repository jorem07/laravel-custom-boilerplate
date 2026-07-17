<?php

namespace App\Http\Controllers;

use App\Http\Requests\PDF\Queue as QueueReceiptRequest;
use App\Services\PDFService;
use Symfony\Component\HttpFoundation\Response;

class PDFController extends Controller
{
    public function __construct(
        protected PDFService $pdfService,
    ) {}

    public function queue(QueueReceiptRequest $request): Response
    {
        $payload = $request->validated();
        $pdf = $this->pdfService->queueReceipt($payload);

        $filename = 'queue-receipt-' . ($payload['id'] ?? $payload['uuid']) . '.pdf';
        $disposition = $payload['disposition'] ?? 'inline';

        if ($disposition === 'download') {
            return $pdf->download($filename);
        }

        return $pdf->stream($filename);
    }
}
