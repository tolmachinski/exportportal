<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Modules model.
 */
final class Modules_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = 'ep_modules';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'MODULES';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id_module';

    /**
     * The attributes that aren't mass assignable.
     */
    protected array $guarded = [
        'id_module',
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id_module'          => Types::INTEGER,
        'in_calendar'        => Types::BOOLEAN,
        'group_module'       => Types::INTEGER,
        'position_module'    => Types::INTEGER,
        'email_notification' => Types::BOOLEAN,
    ];

    /**
     * Scope query for enabled/disabled emails.
     */
    protected function scopeHasEmailsEnabled(QueryBuilder $builder, bool $emailsEnabled): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'email_notification',
                $builder->createNamedParameter($emailsEnabled, ParameterType::BOOLEAN, $this->nameScopeParameter('emails_enabled'))
            )
        );
    }
}

// End of file modules_model.php
// Location: /tinymvc/myapp/models/modules_model.php
