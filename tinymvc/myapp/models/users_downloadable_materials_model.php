<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Users_Downloadable_Materials model
 */
final class Users_Downloadable_Materials_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "users_downloadable_materials";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "udm";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id";

    /**
     * Name of the countries table.
     *
     * @var string
     */
    public $portCountryTable = 'port_country';

    /**
     * Alias of the countries table.
     *
     * @var string
     */
    public $portCountryTableAlias = 'COUNTRIES';

    /**
     * Statistics counts by conditions
     *
     * @param int $id - id of the material
     */
    public function getStatistics(int $id)
    {
        $query = $this->createQueryBuilder();
        $query->select('COUNT(CASE WHEN dwm.is_registered = 0
                            THEN id END) total_not_registered,
                        COUNT(CASE WHEN dwm.is_registered = 1
                            THEN id END) total_registered,
                        SUM(CASE WHEN dwm.is_registered = 0
                            THEN count END) download_not_registered,
                        SUM(CASE WHEN dwm.is_registered = 1
                            THEN count END) download_registered')
              ->from("{$this->getTable()} as dwm")
              ->where($query->expr()->eq("dwm.id_material", $query->createNamedParameter(
                            (int) $id,
                            ParameterType::INTEGER,
                            $this->nameScopeParameter('id_material')
                    ))
                );

        /** @var Statement $statement */
        $statement = $query->execute();

        return $statement->fetchAssociative() ?: [];
    }

    /**
     * Scope a query to filter records by signature.
     *
     * @param string $signature
     */
    protected function scopeSignature(QueryBuilder $builder, ?string $signature): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'signature',
                $builder->createNamedParameter($signature, ParameterType::STRING, $this->nameScopeParameter('signature'))
            )
        );
    }

    /**
     * Scope a query to filter records by id user.
     *
     * @param integer $user
     */
    protected function scopeIdUser(QueryBuilder $builder, int $user): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'id_user',
                $builder->createNamedParameter($user, ParameterType::INTEGER, $this->nameScopeParameter('id_user'))
            )
        );
    }

    /**
     * Scope a query to filter records by id material.
     *
     * @param integer $material
     */
    protected function scopeIdMaterial(QueryBuilder $builder, int $material): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'id_material',
                $builder->createNamedParameter($material, ParameterType::INTEGER, $this->nameScopeParameter('id_material'))
            )
        );
    }

    /**
     * Scope a query to filter records by email.
     *
     * @param string $email
     */
    protected function scopeEmail(QueryBuilder $builder, ?string $email): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'email',
                $builder->createNamedParameter($email, ParameterType::STRING, $this->nameScopeParameter('email'))
            )
        );
    }

    /**
     * Scope a query to bind countries to the query.
     */
    protected function bindCountries(QueryBuilder $builder)
    {
        $builder->leftJoin(
            $this->getTable(),
            $this->portCountryTable,
            $this->portCountryTableAlias,
            "`{$this->getTable()}`.country = `{$this->portCountryTableAlias}`.id"
        );
    }
}

/* End of file users_downloadable_materials_model.php */
/* Location: /tinymvc/myapp/models/users_downloadable_materials_model.php */
