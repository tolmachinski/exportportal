<?php

declare(strict_types=1);

namespace App\Envelope\Bridge\Datatable\Element\Decorate;

use App\Plugins\Datatable\Output\Element\Decorate\Decorator;
use App\Plugins\Datatable\Output\Element\ElementInterface;

class CurrentRecipientPassDecorator extends Decorator
{
    /**
     * The current user ID.
     */
    private int $userId;

    /**
     * Creates instance of the sender pass decorator.
     */
    public function __construct(ElementInterface $element, int $userId)
    {
        parent::__construct($element);

        $this->userId = $userId;
    }

    /**
     * {@inheritdoc}
     */
    public function acceptsRow(array $envelope): bool
    {
        return null !== ($envelope['recipients_routing']['current_routing'][$this->userId] ?? null) && $this->getElement()->acceptsRow($envelope);
    }
}
