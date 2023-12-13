<?php

declare(strict_types=1);

use App\Common\Database\Model;

/**
 * Users_Calling_Statuses model
 */
final class Users_Calling_Statuses_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "users_calling_statuses";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "USERS_CALLING_STATUSES";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id_status";
}

/* End of file users_calling_statuses_model.php */
/* Location: /tinymvc/myapp/models/users_calling_statuses_model.php */
