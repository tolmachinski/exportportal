<?php

declare(strict_types=1);

use App\Common\Database\Model;

/**
 * Model Popups
 *
 */
class Page_Model extends Model
{

    /**
     * {@inheritdoc}
     */
    protected string $table = 'pages';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id_page';

}

/* End of file popups_model.php */
/* Location: /tinymvc/myapp/models/popups_model.php */
