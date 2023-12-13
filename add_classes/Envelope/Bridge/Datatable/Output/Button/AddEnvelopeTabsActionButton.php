<?php

declare(strict_types=1);

namespace App\Envelope\Bridge\Datatable\Output\Button;

use App\Envelope\EnvelopeStatuses;
use App\Plugins\Datatable\Output\Button\ActionButton;

final class AddEnvelopeTabsActionButton extends ActionButton
{
    /**
     * {@inheritdoc}
     */
    public function acceptsRow(array $envelope): bool
    {
        return EnvelopeStatuses::NOT_PROCESSED === $envelope['status'];
    }
}
