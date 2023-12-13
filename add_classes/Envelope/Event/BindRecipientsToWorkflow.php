<?php

declare(strict_types=1);

namespace App\Envelope\Event;

use App\Common\Database\Model;
use App\Envelope\Command\CommandInterface;
use App\Envelope\Message\BindRecipientsToWorkflowMessage;
use Doctrine\Common\Collections\ArrayCollection;

final class BindRecipientsToWorkflow implements CommandInterface
{
    /**
     * The relation pivot between envelope and order.
     */
    private Model $pivot;

    public function __construct(Model $pivot)
    {
        $this->pivot = $pivot;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(BindRecipientsToWorkflowMessage $message)
    {
        //region Bind
        $bindings = new ArrayCollection();
        $workflowStepId = $message->getWorkflowStepId();
        $recipients = $message->getRecipients();
        if (empty($recipients)) {
            return;
        }

        foreach ($recipients as $recipientId) {
            $bindings->add([
                'id_recipient'     => $recipientId,
                'id_workflow_step' => $workflowStepId,
            ]);
        }

        $this->pivot->insertMany($bindings->getValues());
        //endregion Bind
    }
}
