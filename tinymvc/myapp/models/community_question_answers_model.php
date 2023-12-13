<?php

declare(strict_types=1);

use App\Common\Database\Concerns;
use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Community_Question_Answers model.
 *
 * @author Anton Zencenco
 */
final class Community_Question_Answers_Model extends Model
{
    use Concerns\CanSearch;

    /**
     * The name of the "created at" column.
     */
    protected const CREATED_AT = 'date_answer';

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
    protected string $table = 'questions_answers';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'COMMUNITY_QUESTION_ANSWERS';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id_answer';

    /**
     * The attributes that aren't mass assignable.
     */
    protected array $guarded = [
        'id_answer',
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
        'id_answer'      => Types::INTEGER,
        'id_user'        => Types::INTEGER,
        'id_question'    => Types::INTEGER,
        'count_plus'     => Types::INTEGER,
        'count_minus'    => Types::INTEGER,
        'count_comments' => Types::INTEGER,
        'has_bad_words'  => Types::BOOLEAN,
        'moderated'      => Types::BOOLEAN,
    ];

    /**
     * Scope a query to filter by document user ID.
     */
    protected function scopeUser(QueryBuilder $builder, int $userId)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('id_user'),
                $builder->createNamedParameter((int) $userId, ParameterType::INTEGER, $this->nameScopeParameter('answer_user_id', true))
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
                    fn (int $i, $id) => $builder->createNamedParameter((int) $id, ParameterType::INTEGER, $this->nameScopeParameter("answer_user_id_{$i}", true)),
                    array_keys($userIds),
                    $userIds
                )
            )
        );
    }

    /**
     * Relation with the question.
     */
    protected function question(): RelationInterface
    {
        return $this->belongsTo(Community_Questions_Model::class, 'id_question');
    }

    /**
     * Relation with the comments.
     */
    protected function comments(): RelationInterface
    {
        return $this->hasMany(Community_Question_Comments_Model::class, 'id_answer');
    }
}

// End of file community_question_answers_model.php
// Location: /tinymvc/myapp/models/community_question_answers_model.php
