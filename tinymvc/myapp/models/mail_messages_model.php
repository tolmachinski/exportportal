<?php

declare(strict_types=1);

use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Mail_Messages model
 */
final class Mail_Messages_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "mail_messages";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "MAIL_MESSAGES";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id";

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id'            => Types::INTEGER,
        'is_sent'       => Types::INTEGER,
        'is_verified'   => Types::INTEGER,
        'failure_log'   => Types::JSON,
    ];

    /**
     * Scope query by message id
     *
     * @param QueryBuilder $builder
     * @param int $messageId
     *
     * @return void
     */
    protected function scopeId(QueryBuilder $builder, int $messageId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->getTable()}`.`id`",
                $builder->createNamedParameter($messageId, ParameterType::INTEGER, $this->nameScopeParameter('messageId'))
            )
        );
    }

    /**
     * Scope query by is_sent column value
     *
     * @param QueryBuilder $builder
     * @param int $isSent
     *
     * @return void
     */
    protected function scopeIsSent(QueryBuilder $builder, int $isSent): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->getTable()}`.`is_sent`",
                $builder->createNamedParameter($isSent, ParameterType::INTEGER, $this->nameScopeParameter('isSent'))
            )
        );
    }

    /**
     * Scope query by is_verified column value
     *
     * @param QueryBuilder $builder
     * @param int $isVerified
     *
     * @return void
     */
    protected function scopeIsVerified(QueryBuilder $builder, int $isVerified): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->getTable()}`.`is_verified`",
                $builder->createNamedParameter($isVerified, ParameterType::INTEGER, $this->nameScopeParameter('isVerified'))
            )
        );
    }

    /**
     * Scopes query by emails ids.
     */
    protected function scopeEmailsIds(QueryBuilder $builder, array $emailsIds): void
    {
        $builder->andWhere(
            $builder->expr()->in(
                "`{$this->getTable()}`.`id`",
                array_map(
                    fn (int $index, $id) => $builder->createNamedParameter(
                        (int) $id,
                        ParameterType::INTEGER,
                        $this->nameScopeParameter("emailId{$index}")
                    ),
                    array_keys($emailsIds),
                    $emailsIds
                )
            )
        );
    }

     /**
     * LT scope by sent_date.
     */
    protected function scopeSendedBeforeDateTime(QueryBuilder $builder, DateTimeInterface $sendingDate): void
    {
        $builder->andWhere(
            $builder->expr()->lt(
                "`{$this->table}`.`sent_date`",
                $builder->createNamedParameter($sendingDate->format('Y-m-d H:i:s'), ParameterType::STRING, $this->nameScopeParameter('emailDateTimeLt'))
            ),
        );
    }

     /**
     * Scope query by email_key_link column value
     *
     * @param QueryBuilder $builder
     * @param string $key
     *
     * @return void
     */
    protected function scopeKey(QueryBuilder $builder, string $key): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->getTable()}`.`email_key_link`",
                $builder->createNamedParameter($key, ParameterType::STRING, $this->nameScopeParameter('key'))
            )
        );
    }

    /**
     * Relation with the mail message content.
     */
    protected function content(): RelationInterface
    {
        return $this->hasOne(Mail_Messages_Content_Model::class, 'message_id')->enableNativeCast();
    }
}

/* End of file mail_messages_model.php */
/* Location: /tinymvc/myapp/models/mail_messages_model.php */
