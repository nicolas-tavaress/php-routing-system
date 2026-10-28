<?php

declare(strict_types=1);

namespace Tests\Fixtures\Controllers\Admin;

final class ReportsController
{
    public function show(int $reportId): string
    {
        return "report {$reportId}";
    }
}
