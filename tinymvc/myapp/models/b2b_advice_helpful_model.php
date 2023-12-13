<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\Types\Types;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\ParameterType;

/**
 * B2b_Advice_Helpful model.
 */
final class B2b_Advice_Helpful_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = 'b2b_advice_user_helpful';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'B2B_ADVICE_USER_HELPFUL';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id_help';

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id_help'                     => Types::INTEGER,
        'id_advice'                   => Types::INTEGER,
        'id_user'                     => Types::INTEGER,
        'date'                        => Types::DATETIME_IMMUTABLE,
        'help'                        => Types::BOOLEAN,
    ];

    /**
     * Scope a query to filter by user ID.
     */
    protected function scopeUserId(QueryBuilder $builder, int $userId)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('id_user'),
                $builder->createNamedParameter($userId, ParameterType::INTEGER, $this->nameScopeParameter('userId'))
            )
        );
    }

    /**
     * Scope query for advice IDs.
     *
     * @param int[] $adviceIds
     */
    protected function scopeAdviceIds(QueryBuilder $builder, array $adviceIds): void
    {
        if (empty($requestIds)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->in($this->qualifyColumn('id_advice'), array_map(
                fn (int $i, $id) => $builder->createNamedParameter((int) $id, ParameterType::INTEGER, $this->nameScopeParameter("adviceId{$i}", true)),
                array_keys($requestIds),
                $adviceIds
            ))
        );
    }

}

// End of file b2b_advice_helpful_model.php
// Location: /tinymvc/myapp/models/b2b_advice_helpful_model.php
