<?php

declare(strict_types=1);

namespace App\Envelope\Bridge\Datatable\Output\Button;

use App\Envelope\EnvelopeStatuses;
use App\Plugins\Datatable\Output\Button\PopupButton;

final class ViewHistoryPopupButton extends PopupButton
{
    /**
     * {@inheritdoc}
     */
    public function acceptsRow(array $envelope): bool
    {
        return EnvelopeStatuses::VOIDED !== $envelope['status'];
    }
}
