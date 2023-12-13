<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Textual_Block model
 */
final class Textual_Block_Model extends Model
{
	/**
	 * {@inheritdoc}
	 */
	protected string $table = 'textual_blocks';

	/**
	 * {@inheritdoc}
	 */
	protected string $alias = 'TEXTUAL_BLOCKS';

	/**
	 * {@inheritdoc}
	 */
	protected $primaryKey = 'id_block';

	/**
	 * The attributes that should be cast.
	 */
	protected array $casts = [
		'id_block'          => Types::INTEGER,
		'short_name'        => Types::STRING,
		'description_block' => Types::STRING,
		'title_block'       => Types::STRING,
		'text_block'        => Types::TEXT,
		'translations_data' => Types::JSON,
	];

	/**
	 * Scope query by short name
	 *
	 * @param QueryBuilder $builder
	 * @param string $name
	 *
	 * @return void
	 */
	protected function scopeShortName(QueryBuilder $builder, string $name): void
	{
		$builder->andWhere(
			$builder->expr()->eq(
				"{$this->getTable()}.`short_name`",
				$builder->createNamedParameter($name, ParameterType::STRING, $this->nameScopeParameter('name'))
			)
		);
	}
}

/* End of file textual_block_model.php */
/* Location: /tinymvc/myapp/models/textual_block_model.php */
