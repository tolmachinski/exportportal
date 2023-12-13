<?php

declare(strict_types=1);

namespace App\Envelope\Bridge\Datatable\Element\Decorate;

use App\Plugins\Datatable\Output\Element\Decorate\Decorator;
use App\Plugins\Datatable\Output\Element\ElementInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class RecipientPassDecorator extends Decorator
{
    /**
     * The current user ID.
     */
    private int $userId;

    /**
     * Creates instance of the sender pass decorator.
     */
    public function __construct(int $userId, ElementInterface $element)
    {
        parent::__construct($element);

        $this->userId = $userId;
    }

    /**
     * {@inheritdoc}
     */
    public function acceptsRow(array $envelope): bool
    {
        $recipients = $envelope['recipients'] ?? $envelope['recipients_routing']['recipients'] ?? null;
        if (null === $recipients) {
            return false;
        }

        if (!$recipients instanceof Collection) {
            $recipients = new ArrayCollection($recipients);
        }

        return $recipients->exists(fn ($index, array $recipient) => $this->userId === $recipient['id_user']) && $this->getElement()->acceptsRow($envelope);
    }
}
