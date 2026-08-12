<?php

namespace App\Services;

/**
 * @deprecated Queue estimated wait service has been removed in favor of central dashboard queue monitoring.
 */
class QueueEstimatedWaitService
{
    public function refreshAll(): void
    {
    }

    public function refreshForOfficeService(int $officeServiceId): void
    {
    }
}
