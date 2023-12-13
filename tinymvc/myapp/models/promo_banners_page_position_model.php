<?php

declare(strict_types=1);

use App\Common\Database\Exceptions\QueryException;
use App\Common\Database\Model;
use App\Common\Exceptions\NotFoundException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Promo_Banners_Page_Position model
 */
final class Promo_Banners_Page_Position_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "promo_banners_page_position";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "PROMO_BANNERS_PAGE_POSITION";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id_promo_banners_page_position";

    /**
     * Get the list of position.
     */

    public function get_banners_position(array $params = []): ?Collection
    {
        // $params['order'] = $params['order'] ?? ["`{$this->getTable()}`.`date_published`" => 'DESC'];
        $position = $this->findRecords(
            null,
            $this->getTable(),
            null,
            $params
        );

        if (empty($position)) {
            return null;
        }

        return new ArrayCollection($position);
    }

    /**
     * Get the banner by page alias.
     */
    public function get_banner_page_by_alias(string $pageAlias): ?array
    {
        try {
            $banner = $this->findOneBy([
                'conditions' => [
                    'page_alias' => $pageAlias,
                ],
            ]);

            if (empty($banner)) {
                throw new NotFoundException("The banner with page alias '{$pageAlias}' is not found.");
            }

            return $banner;
        } catch (NotFoundException $exception) {
            throw $exception;
        } catch (Exception $exception) {
            throw QueryException::executionFailed($this->getHandler(), $exception);
        }
    }

    /**
     * Get the banner by page id.
     */
    public function get_banner_page_by_id(string $pageId): ?array
    {
        try {
            $banner = $this->findOneBy([
                'conditions' => [
                    'page_id' => $pageId,
                ],
            ]);

            if (empty($banner)) {
                throw new NotFoundException("The banner with page id '{$pageId}' is not found.");
            }

            return $banner;
        } catch (NotFoundException $exception) {
            throw $exception;
        } catch (Exception $exception) {
            throw QueryException::executionFailed($this->getHandler(), $exception);
        }
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
     * Scope banner by page id.
     */
    protected function scopePageId(QueryBuilder $builder, string $pageId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'id_promo_banners_page_position',
                $builder->createNamedParameter($pageId, ParameterType::STRING, $this->nameScopeParameter('pageId'))
            )
        );
    }

    protected function bindPage(QueryBuilder $builder): void
    {
        /** @var Pages_Model $pages */
        $pages = model(Pages_Model::class);

        $builder
            ->leftJoin(
                $this->getTable(),
                $pages->getPagesTable(),
                $pages->getPagesTable(),
                "`{$pages->getPagesTable()}`.`{$pages->getPagesTablePrimaryKey()}` = `{$this->getTable()}`.`id_page`"
            );
    }
}

/* End of file promo_banners_page_position_model.php */
/* Location: /tinymvc/myapp/models/promo_banners_page_position_model.php */
