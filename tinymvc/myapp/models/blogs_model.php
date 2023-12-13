<?php

declare(strict_types=1);

use App\Common\Contracts\Blogs\BlogsAuthorTypes;
use App\Common\Contracts\Blogs\BlogsStatus;
use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\Types\Types;

/**
 * Blogs model
 */
final class Blogs_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "blogs";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "BLOGS";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id";

    /**
     * The attributes that aren't mass assignable.
     */
    protected array $guarded = [
        'id',
    ];

    /**
     * The attributes that are nullable.
     */
    protected array $nullable = [
        'inline_images',
        'inline_images_delete_queue',
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'inline_images_delete_queue'    => Types::JSON,
        'images_limit_exceeded'         => Types::INTEGER,
        'has_external_images'           => Types::INTEGER,
        'inline_images'                 => Types::JSON,
        'id_category'                   => Types::INTEGER,
        'author_type'                   => BlogsAuthorTypes::class,
        'id_country'                    => Types::INTEGER,
        'publish_on'                    => Types::DATE_MUTABLE,
        'published'                     => Types::INTEGER,
        'id_user'                       => Types::INTEGER,
        'visible'                       => Types::INTEGER,
        'status'                        => BlogsStatus::class,
        'views'                         => Types::INTEGER,
        'date'                          => Types::DATETIME_MUTABLE,
        'id'                            => Types::INTEGER,
    ];

    /**
     * Relation with blogs EN category
     */
    protected function category(): RelationInterface
    {
        return $this->hasOne(Blogs_Categories_Model::class, 'id_category', 'id_category')
            ->enableNativeCast()
        ;
    }
}

/* End of file blogs_model.php */
/* Location: /tinymvc/myapp/models/blogs_model.php */
