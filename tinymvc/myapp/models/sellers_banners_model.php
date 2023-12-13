<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\Types\Types;

/**
 * Sellers_Banners model
 */
final class Sellers_Banners_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "seller_banners";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "SELLER_BANNERS";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id";

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id'           => Types::INTEGER,
        'id_user'      => Types::INTEGER,
        'date_updated' => Types::DATETIME_IMMUTABLE,
        'date_added'   => Types::DATETIME_IMMUTABLE,
        'page'         => BannerPageType::class,
    ];
}

/* End of file sellers_banners_model.php */
/* Location: /tinymvc/myapp/models/sellers_banners_model.php */
