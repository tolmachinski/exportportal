<?php

declare(strict_types=1);

namespace App\Envelope\Bridge\Datatable\Output\Badge;

use App\Plugins\Datatable\Output\Badge\Badge;

final class SignableBadge extends Badge
{
    /**
     * {@inheritdoc}
     */
    public function acceptsRow(array $envelope): bool
    {
        return $envelope['signing_enabled'];
    }
}
