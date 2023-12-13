<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\Types\Types;
use App\Common\Database\Relations\RelationInterface;
use App\Common\Database\Platforms\MySQL\Types\Types as MySQLTypes;
use App\Common\Database\PortableModel;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\ParameterType;

/**
 * Webinar_Requests model
 */
final class Webinar_Requests_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "webinar_requests";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "WEBINAR_REQUESTS";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id";

    /**
     * The attributes that are nullable.
     */
    protected array $nullable = [
        'phone_code',
        'phone_code_id',
        'status',
        'attended_date',
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id'               => Types::INTEGER,
        'id_webinar'       => Types::INTEGER,
        'id_user'          => Types::INTEGER,
        'fname'            => Types::STRING,
        'lname'            => Types::STRING,
        'email'            => Types::STRING,
        'phone'            => Types::STRING,
        'phone_code'       => Types::STRING,
        'phone_code_id'    => Types::INTEGER,
        'id_country'       => Types::INTEGER,
        'user_type'        => MySQLTypes::ENUM,
        'requested_date'   => Types::DATETIME_IMMUTABLE,
        'attended_date'    => Types::DATETIME_IMMUTABLE,
        'request_hash'     => Types::STRING,
    ];

    /**
     * Relation with the user.
     */
    protected function user(): RelationInterface
    {
        return $this->belongsTo(Users_Model::class, 'id_user')->enableNativeCast();
    }

    /**
     * Relation with the webinar.
     */
    protected function webinar(): RelationInterface
    {
        return $this->belongsTo(Webinar_Model::class, 'id_webinar')->enableNativeCast();
    }

    /**
     * Resolves static relationships with country.
     */
    protected function country(): RelationInterface
    {
        /** @var Country_Model $countriesModel */
        $countriesModel = model(Country_Model::class);

        $countriesTable = $countriesModel->get_countries_table();

        $relation = $this->belongsTo(
            new PortableModel($this->getHandler(), $countriesTable, 'id'),
            'id_country'
        );
        $relation->disableNativeCast();
        $builder = $relation->getQuery();

        $builder
            ->select(
                "`{$countriesTable}`.`id`",
                "`{$countriesTable}`.`country` AS 'name'",
            );

        return $relation;
    }

    /**
     * Scope by country
     *
     * @param integer $country
     *
     * @return void
     */
    protected function scopeCountry(QueryBuilder $builder, int $country): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->getTable()}`.`id_country`",
                $builder->createNamedParameter($country, ParameterType::INTEGER, $this->nameScopeParameter('country'))
            )
        );
    }

    /**
     * Scope by webinar
     *
     * @param integer $webinar
     *
     * @return void
     */
    protected function scopeWebinar(QueryBuilder $builder, int $webinar): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->getTable()}`.`id_webinar`",
                $builder->createNamedParameter($webinar, ParameterType::INTEGER, $this->nameScopeParameter('webinar'))
            )
        );
    }

    /**
     * Scope event by registered or not
     *
     * @param integer $registered
     *
     * @return void
     */
    protected function scopeRegistered(QueryBuilder $builder, int $registered): void
    {
        if($registered == 1)
        {
            $builder->andWhere(
                $builder->expr()->isNotNull(
                    $this->qualifyColumn('id_user')
                )
            );
        } else{
            $builder->andWhere(
                $builder->expr()->isNull(
                    $this->qualifyColumn('id_user')
                )
            );
        }
    }

    /**
     * Scope comment query by status
     *
     * @param string $status
     *
     * @return void
     */
    protected function scopeStatus(QueryBuilder $builder, string $status): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->getTable()}.`status`",
                $builder->createNamedParameter($status, ParameterType::STRING, $this->nameScopeParameter('status'))
            )
        );
    }

    /**
     * Scope comment query by status
     *
     * @param string $status
     *
     * @return void
     */
    protected function scopeRequestHash(QueryBuilder $builder, string $hash): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->getTable()}.`request_hash`",
                $builder->createNamedParameter($hash, ParameterType::STRING, $this->nameScopeParameter('hash'))
            )
        );
    }

    /**
     * Scope event by email
     *
     * @param string $email
     *
     * @return void
     */
    protected function scopeEmail(QueryBuilder $builder, string $email): void
    {
        $builder->andWhere(
            $builder->expr()->like(
                'email',
                $builder->createNamedParameter("%{$email}%", ParameterType::STRING, $this->nameScopeParameter('email'))
            )
        );
    }

    /**
     * Scope a query to filter by datetime from.
     *
     * @param \DateTimeInterface|int|string $requestedFrom
     *
     * @return void
     */
    protected function scopeRequestedFrom(QueryBuilder $builder, $requestedFrom)
    {
        if (null === ($requestedFrom = $this->convertDatetimeAttributeToDatabaseValue($requestedFrom))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->gte(
                "requested_date",
                $builder->createNamedParameter($requestedFrom, ParameterType::STRING, $this->nameScopeParameter('requestedFrom'))
            )
        );
    }

    /**
     * Scope a query to filter by datetime to.
     *
     * @param \DateTimeInterface|int|string $requestedTo
     *
     * @return void
     */
    protected function scopeRequestedTo(QueryBuilder $builder, $requestedTo)
    {
        if (null === ($requestedTo = $this->convertDatetimeAttributeToDatabaseValue($requestedTo))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->lte(
                "requested_date",
                $builder->createNamedParameter($requestedTo, ParameterType::STRING, $this->nameScopeParameter('requestedTo'))
            )
        );
    }

}

/* End of file webinar_requests_model.php */
/* Location: /tinymvc/myapp/models/webinar_requests_model.php */
