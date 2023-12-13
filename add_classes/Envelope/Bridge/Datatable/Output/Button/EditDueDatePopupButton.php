<?php

declare(strict_types=1);

namespace App\Envelope\Bridge\Datatable\Output\Button;

use App\Envelope\EnvelopeStatuses;
use App\Plugins\Datatable\Output\Button\PopupButton;

final class EditDueDatePopupButton extends PopupButton
{
    /**
     * {@inheritdoc}
     */
    public function acceptsRow(array $envelope): bool
    {
        // Fail if current user is sender or envelope is not active
        if (in_array($envelope['status'], [...EnvelopeStatuses::FINISHED])) {
            return false;
        }

        return true;
    }
}
