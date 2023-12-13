<?php

declare(strict_types=1);

namespace App\Envelope\Message;

/**
 * Message that is send to bind the envelope and order.
 */
final class BindRecipientsToWorkflowMessage
{
    /**
     * The workflow step ID.
     */
    private int $workflowStepId;

    /**
     * The list of recipients.
     */
    private array $recipients;

    /**
     * Creates instance of the message.
     *
     * @param Array<int, Array<int, mixed>> $recipients
     */
    public function __construct(int $workflowStepId, array $recipients = [])
    {
        $this->recipients = $recipients;
        $this->workflowStepId = $workflowStepId;
    }

    /**
     * Get the workflow ID.
     */
    public function getWorkflowStepId(): int
    {
        return $this->workflowStepId;
    }

    /**
     * Get the list of recipients.
     *
     * @return Array<int, Array<int, mixed>>
     */
    public function getRecipients(): array
    {
        return $this->recipients;
    }
}
