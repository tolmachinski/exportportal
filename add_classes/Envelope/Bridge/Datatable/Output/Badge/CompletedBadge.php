<?php

declare(strict_types=1);

namespace App\Envelope\Bridge\Datatable\Output\Badge;

use App\Envelope\EnvelopeStatuses;
use App\Plugins\Datatable\Output\Badge\Badge;

final class CompletedBadge extends Badge
{
    /**
     * {@inheritdoc}
     */
    public function acceptsRow(array $envelope): bool
    {
        return EnvelopeStatuses::COMPLETED === $envelope['status'];
    }
}
