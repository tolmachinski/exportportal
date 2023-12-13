<?php

declare(strict_types=1);

namespace App\Envelope\Bridge\Datatable\Output\Badge;

use App\Envelope\EnvelopeStatuses;
use App\Plugins\Datatable\Output\Badge\Badge;

final class DeclinedBadge extends Badge
{
    /**
     * {@inheritdoc}
     */
    public function acceptsRow(array $envelope): bool
    {
        return EnvelopeStatuses::DECLINED === $envelope['status'];
    }
}
