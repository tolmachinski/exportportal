<?php

declare(strict_types=1);

use App\Common\Database\Model;

/**
 * Model Popups
 *
 */
class Popup_page_Model extends Model
{

    /**
     * {@inheritdoc}
     */
    protected string $table = 'popup_pages_relation';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'popup_page_rel_id';

}

/* End of file popups_model.php */
/* Location: /tinymvc/myapp/models/popups_model.php */
