<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Currencies model
 */
final class Currencies_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "currency";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "CURRENCY";

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id'        => Types::INTEGER,
        'main'      => Types::INTEGER,
        'enable'    => Types::INTEGER,
    ];

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id";

    /**
     * Scope query by enabled state
     *
     * @param QueryBuilder $builder
     * @param int $isEnabled
     *
     * @return void
     */
    protected function scopeIsEnabled(QueryBuilder $builder, int $isEnabled): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->getTable()}`.`enable`",
                $builder->createNamedParameter($isEnabled, ParameterType::INTEGER, $this->nameScopeParameter('isEnabled'))
            )
        );
    }
}

/* End of file currencies_model.php */
/* Location: /tinymvc/myapp/models/currencies_model.php */
