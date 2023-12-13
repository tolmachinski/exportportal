<?php

declare(strict_types=1);

use App\Common\Exceptions\NotFoundException;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use App\Common\Database\Model;

/**
 * Chat_Themes model
 */

final class Chat_Themes_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "chat_themes";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "CHAT_THEMES";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id_chat_themes";

    /**
     * Add one chat.
     *
     * @see Chat_Themes::insertOne()
     */
    public function add(array $chat): int
    {
        return (int) $this->insertOne($chat, true);
    }

    /**
     * Get the chat by chat id.
     */
    public function get_chat(int $userId, int $recipientId, int $moduleId, int $itemId): ?array
    {
        return $this->findOneBy([
            'conditions' => [
                'id_module' => $moduleId,
                'id_item'   => $itemId,
                'id_user'   => [$userId, $recipientId],
            ],
        ]);
    }

    /**
     * Scope chat by id.
     */
    protected function scopeChatId(QueryBuilder $builder, int $chatId): void
    {
        $this->scopePrimaryKey($builder, $this->getTable(), $this->getPrimaryKey(), $chatId);
    }

    /**
     * Scope chat by id module.
     */
    protected function scopeIdUser(QueryBuilder $builder, array $userId): void
    {
        $builder->andWhere(
            $builder->expr()->orX(
                $builder->expr()->eq(
                    'id_sender',
                    $builder->createNamedParameter($userId[0], ParameterType::INTEGER, $this->nameScopeParameter('userId'))
                ),
                $builder->expr()->eq(
                    'id_recipient',
                    $builder->createNamedParameter($userId[1], ParameterType::INTEGER, $this->nameScopeParameter('recipientId'))
                )
            )
        );
    }

    /**
     * Scope chat by id module.
     */
    protected function scopeIdModule(QueryBuilder $builder, int $moduleId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'id_module',
                $builder->createNamedParameter($moduleId, ParameterType::INTEGER, $this->nameScopeParameter('module'))
            )
        );
    }

    /**
     * Scope chat by id item.
     */
    protected function scopeIdItem(QueryBuilder $builder, int $itemId): void
    {

        $builder->andWhere(
            $builder->expr()->eq(
                'id_item',
                $builder->createNamedParameter($itemId, ParameterType::INTEGER, $this->nameScopeParameter('item'))
            )
        );
    }
}

/* End of file chat_themes_model.php */
/* Location: /tinymvc/myapp/models/chat_themes_model.php */
