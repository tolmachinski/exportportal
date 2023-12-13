<?php

declare(strict_types=1);

use App\Common\Database\Concerns;
use App\Common\Database\Exceptions\QueryException;
use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use App\Common\Database\Types\Types as CustomTypes;
use App\Common\Exceptions\NotFoundException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;
use App\Common\Database\Platforms\MySQL\Types\Types as MySQLTypes;
use App\Common\Database\PortableModel;
use Symfony\Component\String\UnicodeString;

/**
 * Promo_Banners model
 */
final class Promo_Banners_Model extends Model
{
    use Concerns\CanSearch;

    /**
     * The name of the "created at" column.
     *
     * @var null|string
     */
    protected const CREATED_AT = 'date_added';

    /**
     * The name of the "updated at" column.
     *
     * @var null|string
     */
    protected const UPDATED_AT = 'date_updated';

    /**
     * {@inheritdoc}
     */
    protected string $table = "promo_banners";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "PROMO_BANNERS";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id_promo_banners";

    /**
     * {@inheritdoc}
     */
    protected array $casts = [
        'id_promo_banners' => Types::INTEGER,
        'id_page_position' => Types::INTEGER,
        'order_banner'     => Types::INTEGER,
        'is_visible'       => Types::BOOLEAN,
    ];

    /**
     * Indicates if model uses timestamps.
     */
    protected bool $timestamps = true;
    /**
     * Get the list of banners.
     */

    public function get_banners(array $params = []): ?Collection
    {
        $params['order'] = $params['order'] ?? ["`{$this->getTable()}`.`date_updated`" => 'DESC'];
        $banners = $this->findRecords(
            null,
            $this->getTable(),
            null,
            $params
        );

        if (empty($banners)) {
            return null;
        }

        return new ArrayCollection($banners);
    }

    public function get_count_banners(array $params = []): ?int
    {
        $params['columns'] = 'COUNT(*) AS `count_banners`';

        $response = $this->findRecord(
            null,
            $this->getTable(),
            null,
            null,
            null,
            $params
        );

        return empty($response['count_banners']) ? 0 : (int) $response['count_banners'];
    }

    /**
     * Get the banner.
     */
    public function get_banner(int $idBanner): ?array
    {
        try {
            $banner = $this->findOneBy([
                'conditions' => [
                    'id_banners' => $idBanner,

                ],
                'with' => [ 'position' ],
            ]);

            if (empty($banner)) {
                throw new NotFoundException("The banner with id '{$idBanner}' is not found.");
            }

            return $banner;
        } catch (NotFoundException $exception) {
            throw $exception;
        } catch (Exception $exception) {
            throw QueryException::executionFailed($this->getHandler(), $exception);
        }
    }

    /**
     * Add one banners.
     *
     * @see Promo_Banners_Model::insertOne()
     */
    public function add(array $banner): int
    {
        return (int) $this->insertOne($banner, true);
    }

    /**
     * Edit the banner.
     */
    public function edit(int $bannerId, array $bannerUpdates): bool
    {
        return (bool) $this->updateOne($bannerId, $bannerUpdates);
    }

    /**
     * Removes the records filtered by given params.
     *
     * @return bool
     */
    public function deleteRecord(array $params = array())
    {
        return $this->removeRecords(
            null,
            $this->getTable(),
            null,
            $params
        );
    }

    /**
     * Scope a query to filter by text search.
     */
    protected function scopeSearch(QueryBuilder $builder, string $text): void
    {
        $search_text = (new UnicodeString($text))->trim();

        $expressions = $builder->expr();
        $builder->andWhere(
            $expressions->or(
                $expressions->eq(
                    "{$this->getTable()}.title",
                    $builder->createNamedParameter(
                        (string) $search_text,
                        ParameterType::STRING,
                        $this->nameScopeParameter('searchText')
                    )
                ),
                $expressions->like(
                    "{$this->getTable()}.title",
                    $builder->createNamedParameter(
                        (string) $search_text->prepend('%')->append('%'),
                        ParameterType::STRING,
                        $this->nameScopeParameter('searchTextToken')
                    )
                ),
            )
        );
    }

    /**
     * Scope banner by is visible.
     */
    protected function scopeIsVisible(QueryBuilder $builder, int $isVisible): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'is_visible',
                $builder->createNamedParameter($isVisible, ParameterType::INTEGER, $this->nameScopeParameter('isVisible'))
            )
        );
    }

    /**
     * Scope banner by id banners.
     */
    protected function scopeIdBanners(QueryBuilder $builder, int $idBanner): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'id_promo_banners',
                $builder->createNamedParameter($idBanner, ParameterType::INTEGER, $this->nameScopeParameter('idBanners'))
            )
        );
    }

    /**
     * Scope banner by id banners.
     */
    protected function scopeIdBannersPosition(QueryBuilder $builder, int $idPagePosition): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'id_page_position',
                $builder->createNamedParameter($idPagePosition, ParameterType::INTEGER, $this->nameScopeParameter('idPagePosition'))
            )
        );
    }

    /**
     * Scope a query to filter records by added date.
     *
     * @param mixed $dateAdded
     */
    protected function scopeAddedFrom(QueryBuilder $builder, $dateAdded): void
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($dateAdded, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->gte(
                "DATE({$this->getTable()}.date_added)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('addedFrom'))
            )
        );
    }

    /**
     * Scope a query to filter records by added date.
     *
     * @param mixed $dateAdded
     */
    protected function scopeAddedTo(QueryBuilder $builder, $dateAdded): void
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($dateAdded, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->lte(
                "DATE({$this->getTable()}.date_added)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('addedTo'))
            )
        );
    }

    /**
     * Scope a query to filter records by update date.
     *
     * @param mixed $dateUpdated
     */
    protected function scopeUpdatedFrom(QueryBuilder $builder, $dateUpdated): void
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($dateUpdated, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->gte(
                "DATE({$this->getTable()}.date_updated)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('updatedFrom'))
            )
        );
    }

    /**
     * Scope a query to filter records by update date.
     *
     * @param mixed $dateUpdated
     */
    protected function scopeUpdatedTo(QueryBuilder $builder, $dateUpdated): void
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($dateUpdated, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->lte(
                "DATE({$this->getTable()}.date_updated)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('updatedTo'))
            )
        );
    }

    /**
     * Scope banner by id page.
     */
    protected function scopeIdPage(QueryBuilder $builder, int $idPage): void
    {
        /** @var Promo_Banners_Page_Position_Model $resources */
        $resources = model(Promo_Banners_Page_Position_Model::class);

        $builder->andWhere(
            $builder->expr()->eq(
                "`{$resources->getTable()}`.`id_page`",
                $builder->createNamedParameter($idPage, ParameterType::INTEGER, $this->nameScopeParameter('idPage'))
            )
        );
    }

    /**
     * Scope banner by alias.
     */
    protected function scopePageAlias(QueryBuilder $builder, string $pageAlias): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'page_alias',
                $builder->createNamedParameter($pageAlias, ParameterType::STRING, $this->nameScopeParameter('pageAlias'))
            )
        );
    }

    /**
     * Scope for join with resources.
     */
    protected function bindPagePosition(QueryBuilder $builder): void
    {
        /** @var Promo_Banners_Page_Position_Model $resources */
        $resources = model(Promo_Banners_Page_Position_Model::class);
        $builder
            ->leftJoin(
                $this->getTable(),
                $resources->getTable(),
                $resources->getTable(),
                "`{$resources->getTable()}`.`{$resources->getPrimaryKey()}` = `{$this->getTable()}`.`id_page_position`"
            )
        ;
    }

    protected function bindPage(QueryBuilder $builder): void
    {
        /** @var Pages_Model $pages */
        $pages = model(Pages_Model::class);
        /** @var Promo_Banners_Page_Position_Model $position */
        $position = model(Promo_Banners_Page_Position_Model::class);

        $builder
            ->leftJoin(
                $position->getTable(),
                $pages->getPagesTable(),
                $pages->getPagesTable(),
                "`{$pages->getPagesTable()}`.`{$pages->getPagesTablePrimaryKey()}` = `{$position->getTable()}`.`id_page`"
            );
    }

    /**
      * Resolves static relationships withbanner position.
     */
    protected function position(): RelationInterface
    {
        $relation = $this->belongsTo(Promo_Banners_Page_Position_Model::class, 'id_page_position');
        $relation->enableNativeCast();
        $positions = $relation->getRelated();
        $builder = $relation->getQuery();
        /** @var Pages_Model $pages */
        $pages = model(Pages_Model::class);
        $pagesTable = $pages->getPagesTable();
        $pagesPrimaryKey = $pages->getPagesTablePrimaryKey();

        $builder
            ->select('*')
            ->innerJoin(
                $positions->getTable(),
                $pagesTable,
                null,
                "{$pagesTable}.{$pagesPrimaryKey} = {$positions->getTable()}.id_page"
            )
        ;
        $positions->mergeCasts([
            'id_page' => 'int',
            'position_name' => 'string',
        ]);

        return $relation;
        /** @var Country_Model $cities */
        $cities = model(Country_Model::class);

        return $this->belongsTo(
            new PortableModel($this->getHandler(), $cities->get_cities_table(), $cities->get_cities_table_primary_key()),
            'ship_to_city'
        );
    }
}

/* End of file promo_banners_model.php */
/* Location: /tinymvc/myapp/models/promo_banners_model.php */
