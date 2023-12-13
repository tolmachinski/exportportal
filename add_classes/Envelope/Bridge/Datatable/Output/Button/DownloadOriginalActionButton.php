<?php

declare(strict_types=1);

namespace App\Envelope\Bridge\Datatable\Output\Button;

use App\Envelope\EnvelopeStatuses;
use App\Plugins\Datatable\Output\Button\ActionButton;
use App\Plugins\Datatable\Output\Template\TemplateInterface;

final class DownloadOriginalActionButton extends ActionButton
{
    /**
     * The flag that indicates that envelope status must be ignored.
     */
    private bool $ignoreStatus;

    /**
     * Creates instance of the popup button.
     */
    public function __construct(
        TemplateInterface $template,
        string $text,
        ?string $title = null,
        ?string $className = null,
        ?string $icon = null,
        array $dataAttributes = [],
        bool $ignoreStatus = false
    ) {
        parent::__construct($template, $text, $title, $className, $icon, $dataAttributes);

        $this->ignoreStatus = $ignoreStatus;
    }

    /**
     * {@inheritdoc}
     */
    public function acceptsRow(array $envelope): bool
    {
        if (!$this->ignoreStatus) {
            // Fail if envelope is draft or voided
            if (EnvelopeStatuses::VOIDED === $envelope['status']) {
                return false;
            }
        }

        if (empty($envelope['documents']['original'])) {
            return false;
        }

        return true;
    }
}
