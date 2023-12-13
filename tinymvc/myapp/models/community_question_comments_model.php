<?php

declare(strict_types=1);

use App\Common\Database\Concerns;
use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\Types\Types;

/**
 * Community_Question_Comments model.
 *
 * @author Anton Zencenco
 */
final class Community_Question_Comments_Model extends Model
{
    use Concerns\CanSearch;

    /**
     * The name of the "created at" column.
     */
    protected const CREATED_AT = 'date_comment';

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
    protected string $table = 'questions_answers_comments';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'COMMUNITY_QUESTION_COMMENTS';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id_comment';

    /**
     * The attributes that aren't mass assignable.
     */
    protected array $guarded = [
        'id_comment',
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
        'id_comment'     => Types::INTEGER,
        'id_answer'      => Types::INTEGER,
        'id_user'        => Types::INTEGER,
        'has_bad_words'  => Types::BOOLEAN,
        'moderated'      => Types::BOOLEAN,
    ];

    /**
     * Relation with the answer.
     */
    protected function answer(): RelationInterface
    {
        return $this->belongsTo(Community_Question_Answers_Model::class, 'id_answer');
    }
}

// End of file community_question_comments_model.php
// Location: /tinymvc/myapp/models/community_question_comments_model.php
