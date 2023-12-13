<?php

declare(strict_types=1);

use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * User_Followers model.
 */
final class User_Followers_Model extends Model
{
    /**
     * The name of the "created at" column.
     */
    protected const CREATED_AT = 'date_follow';

    /**
     * Indicates if model uses timestamps.
     */
    protected bool $timestamps = true;

    /**
     * {@inheritdoc}
     */
    protected string $table = 'user_followers';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'USER_FOLLOWERS';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id_follow';

    /**
     * The attributes that aren't mass assignable.
     */
    protected array $guarded = [
        'id_follow',
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id_follow'        => Types::INTEGER,
        'id_user'          => Types::INTEGER,
        'id_user_follower' => Types::INTEGER,
        'date_follow'      => Types::DATETIME_IMMUTABLE,
    ];

    /**
     * Scope query for user.
     */
    protected function scopeUser(QueryBuilder $builder, int $userId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'id_user_follower',
                $builder->createNamedParameter($userId, ParameterType::INTEGER, $this->nameScopeParameter('user_id', true))
            )
        );
    }

    /**
     * Scope query for followed user.
     */
    protected function scopeFollowedUser(QueryBuilder $builder, int $followedUserId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'id_user',
                $builder->createNamedParameter($followedUserId, ParameterType::INTEGER, $this->nameScopeParameter('followed_user_id', true))
            )
        );
    }

    /**
     * Relation with the user.
     */
    protected function user(): RelationInterface
    {
        return $this->belongsTo(Users_Model::class, 'id_user_follower')->enableNativeCast();
    }

    /**
     * Relation with the follower.
     */
    protected function followedUser(): RelationInterface
    {
        return $this->belongsTo(Users_Model::class, 'id_user')->enableNativeCast();
    }
}

// End of file user_followers_model.php
// Location: /tinymvc/myapp/models/user_followers_model.php
