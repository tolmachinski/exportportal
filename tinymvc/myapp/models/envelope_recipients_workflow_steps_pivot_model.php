<?php

declare(strict_types=1);

use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\Types\Types;

/**
 * Envelopes_Orders_Pivot model.
 */
final class Envelope_Recipients_Workflow_Steps_Pivot_Model extends Model
{
    /**
     * The table primary key.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The table name.
     */
    protected string $table = 'envelope_recipients_workflow_steps_pivot';

    /**
     * The table alias.
     */
    protected string $alias = 'ENVELOPE_RECIPIENTS_WORKFLOW_STEPS_PIVOT';

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id'               => Types::INTEGER,
        'id_recipient'     => Types::INTEGER,
        'id_workflow_step' => Types::INTEGER,
    ];

    /**
     * Relation with the recipient.
     */
    protected function recipient(): RelationInterface
    {
        return $this->belongsTo(Envelope_Recipients_Model::class, 'id_recipient')->disableNativeCast();
    }

    /**
     * Relation with the document.
     */
    protected function workflowStep(): RelationInterface
    {
        return $this->belongsTo(Envelope_Workflow_Steps_Model::class, 'id_workflow_step')->disableNativeCast();
    }
}

// End of file envelopes_orders_pivot_model.php
// Location: /tinymvc/myapp/models/envelopes_orders_pivot_model.php
