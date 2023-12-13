<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Mail_Messages_View model
 */
final class Mail_Messages_Content_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "mail_messages_content";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "MAIL_MESSAGES_CONTENT";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id";

     /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id'            => Types::INTEGER,
        'message'       => Types::STRING,
    ];
}

/* End of file mail_messages_view_model.php */
/* Location: /tinymvc/myapp/models/mail_messages_view_model.php */
