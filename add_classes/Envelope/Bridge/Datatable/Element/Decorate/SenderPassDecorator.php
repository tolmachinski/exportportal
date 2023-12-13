<?php

declare(strict_types=1);

namespace App\Envelope\Bridge\Datatable\Element\Decorate;

use App\Plugins\Datatable\Output\Element\Decorate\Decorator;
use App\Plugins\Datatable\Output\Element\ElementInterface;

class SenderPassDecorator extends Decorator
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
        return (int) $envelope['id_sender'] === $this->userId && $this->getElement()->acceptsRow($envelope);
    }
}
