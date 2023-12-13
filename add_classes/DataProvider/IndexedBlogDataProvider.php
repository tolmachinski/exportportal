<?php

declare(strict_types=1);

namespace App\DataProvider;

use App\Common\Database\Model;
use App\Common\DependencyInjection\ServiceLocator\ModelLocator;
use App\Common\Transformers\BlogListForBaiduTransformer;
use Elasticsearch_Blogs_Model;
use Spatie\Fractalistic\Fractal;

/**
 * The blogs data provider service.
 */
final class IndexedBlogDataProvider
{
    private Elasticsearch_Blogs_Model $blogElasticStorage;

    /**
     * Create the company data provider service class.
     *
     * @param ModelLocator $modelLocator the models locator
     */
    public function __construct(ModelLocator $modelLocator)
    {
        $this->blogElasticStorage = $modelLocator->get(\Elasticsearch_Blogs_Model::class);
    }

    public function getBlogsForHomePage()
    {
        return $this->blogElasticStorage->get_blogs([
            'published' => 1,
            'visible'   => 1,
            'status'    => 'moderated',
            'per_p'     => 3,
        ]) ?? [];
    }

    public function getBlogsForHomePageBaidu()
    {
        return Fractal::create()
            ->collection($this->getBlogsForHomePage())
            ->transformWith(new BlogListForBaiduTransformer())
            ->toArray()['data'];
    }

}
