<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Activity_Log_Messages model.
 */
class Activity_Log_Messages_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected const CREATED_AT = 'created_at';

    /**
     * {@inheritdoc}
     */
    protected const UPDATED_AT = 'updated_at';

    /**
     * {@inheritdoc}
     */
    protected string $table = 'activity_log_message_templates';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id_message';

    /**
     * {@inheritdoc}
     */
    protected array $guarded = array(
        'id',
        self::CREATED_AT,
        self::UPDATED_AT,
    );

    /**
     * {@inheritdoc}
     */
    protected array $casts = array(
        'id_message'        => Types::INTEGER,
        'id_resource_type'  => Types::INTEGER,
        'id_operation_type' => Types::INTEGER,
        self::CREATED_AT    => Types::DATETIME_IMMUTABLE,
        self::UPDATED_AT    => Types::DATETIME_IMMUTABLE,
    );

    /**
     * {@inheritdoc}
     */
    protected array $nullable = array(
        'id_resource_type',
        'id_operation_type',
        self::CREATED_AT,
        self::UPDATED_AT,
    );

    /**
     * Get message template.
     */
    public function get_message(?int $resource = null, ?int $operation = null, string $default_message = 'Unrecognized operation')
    {
        if (null === $resource && null === $operation) {
            return $default_message;
        }

        return $this->findOneBy(array(
            'columns'    => array('template as message'),
            'conditions' => array('resource' => $resource, 'operation' => $operation),
        ))['message'] ?? $default_message;
    }

    /**
     * Scope query by resource ID.
     */
    protected function scopeResource(QueryBuilder $builder, int $resource): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'id_resource_type',
                $builder->createNamedParameter($resource, ParameterType::INTEGER, $this->nameScopeParameter('resourceTypeId'))
            )
        );
    }

    /**
     * Scope query by operation ID.
     */
    protected function scopeOperation(QueryBuilder $builder, int $operation): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'id_operation_type',
                $builder->createNamedParameter($operation, ParameterType::INTEGER, $this->nameScopeParameter('operationTypeId'))
            )
        );
    }
}

// End of file activity_log_messages_model_model.php
// Location: /tinymvc/myapp/models/activity_log_messages_model_model.php
