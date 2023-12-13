<?php

declare(strict_types=1);

use App\Common\Database\Model;
use App\Common\Database\PortableModel;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\Types\Types;

/**
 * Envelopes_Orders_Pivot model.
 */
final class Envelopes_Orders_Pivot_Model extends Model
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
    protected string $table = 'envelopes_orders_pivot';

    /**
     * The table alias.
     */
    protected string $alias = 'ENVELOPES_ORDERS_PIVOT';

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id'          => Types::INTEGER,
        'id_order'    => Types::INTEGER,
        'id_envelope' => Types::INTEGER,
    ];

    /**
     * Relation with the recipient.
     */
    protected function orders(): RelationInterface
    {
        return $this->belongsTo(new PortableModel($this->getHandler(), 'item_orders', 'id'), 'id_order')->disableNativeCast();
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
