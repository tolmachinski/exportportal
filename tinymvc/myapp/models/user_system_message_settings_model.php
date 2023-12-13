<?php

declare(strict_types=1);

use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * User_System_Message_Settings_Model model.
 */
final class User_System_Message_Settings_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = 'users_systmess_settings';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'USERS_MODULES_PIVOT';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = ['id_user', 'module'];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id_user' => Types::INTEGER,
        'module'  => Types::INTEGER,
    ];

    /**
     * Scope query for user ID.
     */
    protected function scopeUser(QueryBuilder $builder, int $userId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'id_user',
                $builder->createNamedParameter($userId, ParameterType::INTEGER, $this->nameScopeParameter('user'))
            )
        );
    }

    /**
     * Scope query for users IDs.
     */
    protected function scopeUsers(QueryBuilder $builder, array $userIds): void
    {
        if (empty($userIds)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->in('id_user', array_map(
                fn (int $i, $id) => $builder->createNamedParameter((int) $id, ParameterType::INTEGER, $this->nameScopeParameter("user_id_{$i}")),
                array_keys($userIds),
                $userIds
            ))
        );
    }

    /**
     * Scope query for module ID.
     */
    protected function scopeModule(QueryBuilder $builder, int $moduleId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'module',
                $builder->createNamedParameter($moduleId, ParameterType::INTEGER, $this->nameScopeParameter('module'))
            )
        );
    }

    /**
     * Relation with the user.
     */
    protected function user(): RelationInterface
    {
        return $this->belongsTo(Users_Model::class, 'id_user');
    }

    /**
     * Relation with the module.
     */
    protected function appModule(): RelationInterface
    {
        return $this->belongsTo(Modules_Model::class, 'module');
    }
}

// End of file user_system_message_settings_model.php
// Location: /tinymvc/myapp/models/user_system_message_settings_model.php
