<?php

declare(strict_types=1);

use App\Common\Database\Concerns;
use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Community_Questions model.
 *
 * @author Anton Zencenco
 */
final class Community_Questions_Model extends Model
{
    use Concerns\CanSearch;

    /**
     * The name of the "created at" column.
     */
    protected const CREATED_AT = 'date_question';

    /**
     * The name of the "updated at" column.
     */
    protected const UPDATED_AT = null;

    /**
     * Indicates if model uses timestamps.
     */
    protected bool $timestamps = true;

    /**
     * {@inheritdoc}
     */
    protected string $table = 'questions';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'COMMUNITY_QUESTIONS';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id_question';

    /**
     * The attributes that aren't mass assignable.
     */
    protected array $guarded = [
        'id_question',
        self::CREATED_AT,
    ];

    /**
     * The attributes that are nullable.
     */
    protected array $nullable = [];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id_question'   => Types::INTEGER,
        'id_user'       => Types::INTEGER,
        'id_category'   => Types::INTEGER,
        'id_country'    => Types::INTEGER,
        'moderated'     => Types::BOOLEAN,
        'has_bad_words' => Types::BOOLEAN,
        'count_answers' => Types::INTEGER,
        'date_question' => Types::DATETIME_IMMUTABLE,
        'views'         => Types::INTEGER,
        'was_searched'  => Types::INTEGER,
    ];

    /**
     * Scope a query to filter by user ID.
     */
    protected function scopeUser(QueryBuilder $builder, int $userId)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('id_user'),
                $builder->createNamedParameter((int) $userId, ParameterType::INTEGER, $this->nameScopeParameter('questtion_user_id', true))
            )
        );
    }

    /**
     * Scope a query to filter by list of user IDs.
     */
    protected function scopeUsers(QueryBuilder $builder, array $userIds): void
    {
        if (empty($userIds)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->in(
                $this->qualifyColumn('id_user'),
                array_map(
                    fn (int $i, $id) => $builder->createNamedParameter((int) $id, ParameterType::INTEGER, $this->nameScopeParameter("questtion_user_id_{$i}", true)),
                    array_keys($userIds),
                    $userIds
                )
            )
        );
    }

    /**
     * Relation with the answers.
     */
    protected function answers(): RelationInterface
    {
        return $this->hasMany(Community_Question_Answers_Model::class, 'id_question');
    }

    /**
     * Relation with questions categories
     */
    protected function category(): RelationInterface
    {
        return $this->hasOne(Community_Questions_Categories_Model::class, 'idcat');
    }
}

// End of file community_questions_model.php
// Location: /tinymvc/myapp/models/community_questions_model.php
