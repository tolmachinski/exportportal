<?php

declare(strict_types=1);

use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * User contacts model.
 */
final class User_Contacts_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = 'contact_user';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'CONTACT_USER';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id_contact';

    /**
     * The attributes that aren't mass assignable.
     */
    protected array $guarded = [
        'id_contact',
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id_contact'      => Types::INTEGER,
        'id_user'         => Types::INTEGER,
        'id_contact_user' => Types::INTEGER,
    ];

    /**
     * Scope query for user.
     */
    protected function scopeUser(QueryBuilder $builder, int $userId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'id_user',
                $builder->createNamedParameter($userId, ParameterType::INTEGER, $this->nameScopeParameter('user_id', true))
            )
        );
    }

    /**
     * Scope query for contact.
     */
    protected function scopeContact(QueryBuilder $builder, int $contactId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'id_contact_user',
                $builder->createNamedParameter($contactId, ParameterType::INTEGER, $this->nameScopeParameter('contact_id', true))
            )
        );
    }

    /**
     * Relation with the user.
     */
    protected function user(): RelationInterface
    {
        return $this->belongsTo(Users_Model::class, 'id_user')->enableNativeCast();
    }

    /**
     * Relation with the contact.
     */
    protected function contact(): RelationInterface
    {
        return $this->belongsTo(Users_Model::class, 'id_contact_user')->enableNativeCast();
    }
}

// End of file user_contacts_model.php
// Location: /tinymvc/myapp/models/user_contacts_model.php
