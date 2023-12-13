<?php

declare(strict_types=1);

use App\Common\Database\Model;

/**
 * Model Popups
 *
 */
class Popup_not_pages_Model extends Model
{

    /**
     * {@inheritdoc}
     */
    protected string $table = 'popup_not_pages_relation';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id_not_page_rel';

}

/* End of file popups_model.php */
/* Location: /tinymvc/myapp/models/popups_model.php */
