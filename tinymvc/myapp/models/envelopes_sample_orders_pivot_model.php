<?php

declare(strict_types=1);

use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\Types\Types;

/**
 * Envelopes_Sample_Orders_Pivot model.
 */
final class Envelopes_Sample_Orders_Pivot_Model extends Model
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
    protected string $table = 'envelopes_sample_orders_pivot';

    /**
     * The table alias.
     */
    protected string $alias = 'ENVELOPES_SAMPLE_ORDERS_PIVOT';

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id'              => Types::INTEGER,
        'id_envelope'     => Types::INTEGER,
        'id_sample_order' => Types::INTEGER,
    ];

    /**
     * Relation with the recipient.
     */
    protected function sampleOrders(): RelationInterface
    {
        return $this->belongsTo(Sample_Orders_Model::class, 'id_sample_order')->disableNativeCast();
    }

    /**
     * Relation with the document.
     */
    protected function envelope(): RelationInterface
    {
        return $this->belongsTo(Envelopes_Model::class, 'id_envelope')->disableNativeCast();
    }
}

// End of file envelopes_orders_pivot_model.php
// Location: /tinymvc/myapp/models/envelopes_orders_pivot_model.php
