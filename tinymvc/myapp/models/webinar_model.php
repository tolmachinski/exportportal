<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\Types\Types;
use Doctrine\DBAL\Query\QueryBuilder;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\ParameterType;

/**
 * Webinar model
 */
final class Webinar_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "webinars";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "WEBINARS";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id";

    /**
     * The attributes that are nullable.
     */
    protected array $nullable = [
        'link',
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id'          => Types::INTEGER,
        'title'       => Types::STRING,
        'start_date'  => Types::DATETIME_IMMUTABLE,
        'link'        => Types::STRING,
    ];

    /**
     * Join with count of requests
     */
    protected function bindRequestsCount(QueryBuilder $builder): void
    {
        $requestsRelation = $this->requests();
        $requestsRelationRepository = $requestsRelation->getRelated();
        $subquery = $this->createQueryBuilder();
        $subquery
            ->select("id_webinar, COUNT(`id`) AS requested, SUM(CASE WHEN status = 'attended' THEN 1 ELSE 0 END) as attended")
            ->from($requestsRelationRepository->getTable())
            ->groupBy('id_webinar')
        ;

        $builder->leftJoin(
            $this->getTable(),
            "({$subquery->getSQL()})",
            'requests',
            "`requests`.`id_webinar` = `{$this->getTable()}`.`{$this->getPrimaryKey()}`"
        );
    }

    /**
     * Join with count of requests
     */
    protected function bindLeadsCount(QueryBuilder $builder): void
    {
        $requestsRelation = $this->requests();
        $requestsRelationRepository = $requestsRelation->getRelated();
        $subquery = $this->createQueryBuilder();
        $subquery
            ->select("id_webinar, COUNT(DISTINCT email) as leads")
            ->where('converted_to_lead <> 0')
            ->from($requestsRelationRepository->getTable())
            ->groupBy('id_webinar');

        $builder->leftJoin(
            $this->getTable(),
            "({$subquery->getSQL()})",
            'leads',
            "`leads`.`id_webinar` = `{$this->getTable()}`.`{$this->getPrimaryKey()}`"
        );
    }

    /**
     * Resolves static relationships with Webinar_Requests_Model
     */
    protected function requests(): RelationInterface
    {
        return $this->hasMany(Webinar_Requests_Model::class, 'id_webinar')->disableNativeCast();
    }

    /**
     * Scope a query to filter by datetime from.
     *
     * @param \DateTimeInterface|int|string $startFrom
     *
     * @return void
     */
    protected function scopeStartFrom(QueryBuilder $builder, $startFrom)
    {
        if (null === ($startFrom = $this->convertDatetimeAttributeToDatabaseValue($startFrom))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->gte(
                "start_date",
                $builder->createNamedParameter($startFrom, ParameterType::STRING, $this->nameScopeParameter('startFrom'))
            )
        );
    }

    /**
     * Scope a query to filter by datetime to.
     *
     * @param \DateTimeInterface|int|string $startTo
     *
     * @return void
     */
    protected function scopeStartTo(QueryBuilder $builder, $startTo)
    {
        if (null === ($startTo = $this->convertDatetimeAttributeToDatabaseValue($startTo))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->lte(
                "start_date",
                $builder->createNamedParameter($startTo, ParameterType::STRING, $this->nameScopeParameter('startTo'))
            )
        );
    }

    /**
     * Scope a query to filter by datetime.
     *
     * @param QueryBuilder $builder
     * @param string $startDate - format Y-m-d
     *
     * @return void
     */
    protected function scopeStartDate(QueryBuilder $builder, string $startDate)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "DATE(start_date)",
                $builder->createNamedParameter($startDate, ParameterType::STRING, $this->nameScopeParameter('startDate'))
            )
        );
    }


    /**
     * Scope for ID.
     *
     * @param QueryBuilder $builder
     * @param int $webinarId
     *
     * @return void
     */
    protected function scopeId(QueryBuilder $builder, int $webinarId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->getPrimaryKey(),
                $builder->createNamedParameter($webinarId, ParameterType::INTEGER, $this->nameScopeParameter('id'))
            )
        );
    }

    /**
     * Resolves static relationships with Webinar_Requests_Model
     */
    protected function requestsLeadsRegistered(): RelationInterface
    {
        /** @var Users_Model $usersModel */
        $usersModel = model(Users_Model::class);
        $usersTable = $usersModel->getTable();

        $relation = $this->hasMany(Webinar_Requests_Model::class, 'id_webinar')->disableNativeCast();
        $related = $relation->getRelated();

        $builder = $relation->getQuery();

        $subqueryBuilder = $this->createQueryBuilder();
        $subqueryBuilder->select('email')->from($usersTable);

        $builder
            ->select($related->getTable().".*")
            ->where("`{$related->getTable()}`.`converted_to_lead` <> 0")
            ->where("`{$related->getTable()}`.`email` NOT IN ({$subqueryBuilder->getSQL()})");

        return $relation;
    }

}

/* End of file webinar_model.php */
/* Location: /tinymvc/myapp/models/webinar_model.php */
