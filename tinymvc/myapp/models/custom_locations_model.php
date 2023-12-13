<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Location_Requests model.
 */
class Custom_Locations_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = 'users_custom_locations';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id';

    /**
     * {@inheritdoc}
     */
    protected array $guarded = array(
        'id',
    );

    /**
     * {@inheritdoc}
     */
    protected array $casts = array(
        'id'           => Types::INTEGER,
        'id_principal' => Types::INTEGER,
    );

    /**
     * Finds record by list of principals.
     */
    public function find_by_principals(array $principals): array
    {
        if (empty($principals)) {
            return array();
        }

        return $this->findAllBy(array(
            'conditions' => array(
                'principals' => $principals,
            ),
        ));
    }

    /**
     * Scopes the query by principals.
     */
    protected function scopePrincipals(QueryBuilder $builder, array $principals): void
    {
        if (empty($principals)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->in(
                'id_principal',
                array_map(
                    fn (int $index, $id) => $builder->createNamedParameter(
                        (int) $id,
                        ParameterType::INTEGER,
                        $this->nameScopeParameter("principals{$index}")
                    ),
                    array_keys($principals),
                    $principals
                )
            )
        );
    }
}

// End of file location_requests_model.php
// Location: /tinymvc/myapp/models/location_requests_model.php
