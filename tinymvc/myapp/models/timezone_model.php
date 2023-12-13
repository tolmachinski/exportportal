<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\Types\Types;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Timezone model
 */
final class Timezone_Model extends Model
{
	/**
	 * {@inheritdoc}
	 */
	protected string $table = 'timezones';

	/**
	 * {@inheritdoc}
	 */
	protected string $alias = 'TIMEZONES';

	/**
	 * {@inheritdoc}
	 */
	protected $primaryKey = 'id';

	/**
	* The attributes that should be cast.
	*/
	protected array $casts = [
		'id'             => Types::INTEGER,
		'name_timezone'  => Types::STRING,
		'name_country'   => Types::STRING,
		'hours'          => Types::FLOAT,
	];

    /**
     * Scope a query to filter by hours
     */
    protected function scopeHours(QueryBuilder $builder, float $hours): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->getTable()}.`hours`",
                $builder->createNamedParameter($hours, Types::FLOAT, $this->nameScopeParameter('hours'))
            )
        );
    }
}

/* End of file timezone_model.php */
/* Location: /tinymvc/myapp/models/timezone_model.php */
