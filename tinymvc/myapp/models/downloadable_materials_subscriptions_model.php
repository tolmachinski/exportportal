<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Downloadable_Materials_Subscriptions model
 */
final class Downloadable_Materials_Subscriptions_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "downloadable_materials_subscriptions";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "dms";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id";

    /**
     * Scope a query to filter records by email.
     *
     * @param string $email
     */
    public function scopeEmail(QueryBuilder $builder, ?string $email): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'email',
                $builder->createNamedParameter($email, ParameterType::STRING, $this->nameScopeParameter('email'))
            )
        );
    }
}

/* End of file downloadable_materials_subscriptions_model.php */
/* Location: /tinymvc/myapp/models/downloadable_materials_subscriptions_model.php */
