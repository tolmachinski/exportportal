<?php

use App\Common\Database\Model;
use Doctrine\DBAL\Types\Types;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Model Monolog_Logs_Model
 */
class Monolog_Logs_Model extends Model
{
	/**
	 * Name of the table
	 *
	 * @var string
	 */
	protected string $table = "monolog_logs";

	/**
	 * The attributes that are nullable.
	 */
	protected array $nullable = [
		'id_user',
		'id_resource',
		'message',
		'context',
	];

	/**
	 * {@inheritdoc}
	 */
	protected array $casts = [
		'id_user'     => Types::INTEGER,
		'id_resource' => Types::INTEGER,
		'date_log'    => Types::DATETIME_IMMUTABLE,
		'message'     => Types::TEXT,
		'context'     => Types::JSON,
	];

	public function findByUser(array $params = []): ?Collection
	{
        $params['order'] = $params['order'] ?? ["`{$this->getTable()}`.`date_log`" => 'DESC'];
        $logs = $this->findRecords(
            null,
            $this->table,
            null,
            $params
        );

        if (empty($logs)) {
            return null;
        }

        return new ArrayCollection($logs);
    }


    /**
     * Scope logs by user id
     */
    protected function scopeUser(QueryBuilder $builder, int $idUser): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'id_user',
                $builder->createNamedParameter($idUser, ParameterType::INTEGER, $this->nameScopeParameter('id_user'))
            )
        );
    }

    /**
     * Scope logs by date
     */
    protected function scopeDate(QueryBuilder $builder, string $dateLog): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "DATE(`{$this->getTable()}`.`date_log`)",
                $builder->createNamedParameter($dateLog, ParameterType::STRING, $this->nameScopeParameter('date_log'))
            )
        );
    }
}
