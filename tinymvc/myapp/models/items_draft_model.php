<?php

declare(strict_types=1);

use App\Common\Database\BaseModel;
use App\Common\Database\Concerns;
use App\Common\Database\Model;
use App\Common\Database\PortableModel;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * items_model.php
 * items system model.
 *
 * @author Andrew Litra
 */
class Items_draft_Model extends BaseModel
{
    use Concerns\ConvertsAttributes;

    public $upload_columns = array(
        'xls_columns' => array(
            'discount' => array(
                'label'     => 'Discount',
                'rules'     => array('min[0]' => '', 'max[99]' => '', 'integer' => ''),
                'db_column' => 'discount',
            ),
            'min_sale_quantity' => array(
                'label'     => 'Minimal sale quantity',
                'rules'     => array('natural' => '', 'min[1]' => ''),
                'db_column' => 'min_sale_q',
            ),
            'max_sale_quantity' => array(
                'label'     => 'Maximal sale quantity',
                'rules'     => array('natural' => '', 'min[1]' => ''),
                'db_column' => 'max_sale_q',
            ),
            'price' => array(
                'label'     => 'Price in USD',
                'rules'     => array('positive_number' => ''),
                'db_column' => 'price',
            ),
            'discount_price' => array(
                'label'     => 'Discount Price in USD',
                'rules'     => array('positive_number' => ''),
                'db_column' => 'final_price',
            ),
            'quantity' => array(
                'label'     => 'Quantity',
                'rules'     => array('natural' => ''),
                'db_column' => 'quantity',
            ),
            'sizes' => array(
                'label'     => 'Sizes, (cm) LxWxH',
                'rules'     => array('item_sizes' => ''),
                'db_column' => 'size',
            ),
            'title' => array(
                'label'     => 'Title',
                'rules'     => array('required' => '', 'valide_title' => '', 'min_len[4]' => '', 'max_len[255]' => ''),
                'db_column' => 'title',
            ),
            'video' => array(
                'label'     => 'Video',
                'rules'     => array('valid_url' => '', 'max_len[200]' => ''),
                'db_column' => 'video',
            ),
            'weight' => array(
                'label'     => 'Weight',
                'rules'     => array('positive_number' => ''),
                'db_column' => 'weight',
            ),
        ),
        'ep_columns' => array(
            'category' => array(
                'label'     => 'Category',
                'rules'     => array('integer' => ''),
                'db_column' => 'id_cat',
            ),
            'categories' => array(
                'label'     => 'Product(s) category',
                'rules'     => array(),
                'db_column' => 'id_cat',
            ),
            'country_of_origin' => array(
                'label'     => 'Country of origin',
                'rules'     => array('integer' => ''),
                'db_column' => 'origin_country',
            ),
            'product_city' => array(
                'label'     => 'Product(s) location City',
                'rules'     => array('integer' => ''),
                'db_column' => 'p_city',
            ),
            'product_country' => array(
                'label'     => 'Product(s) location Country',
                'rules'     => array('integer' => ''),
                'db_column' => 'p_country',
            ),
            'product_state' => array(
                'label'     => 'Product(s) location State or Province',
                'rules'     => array('integer' => ''),
                'db_column' => 'state',
            ),
        ),
    );
    /**
     * The item drafts table.
     *
     * @var string
     */
    private $items_draft_table = 'items_draft';

    /**
     * The item drafts alias.
     *
     * @var string
     */
    private $items_draft_table_alias = 'DRAFTS';

    /**
     * The item drafts bulk uploads table.
     *
     * @var string
     */
    private $items_bulk_uploads_table = 'items_bulk_uploads';

    /**
     * The item drafts uploads configs table.
     */
    private $items_bulk_uploads_config_table = 'items_bulk_uploads_config';

    /**
     * Name of the users table.
     *
     * @var string
     */
    private $users_table = 'users';

    /**
     * Name of the item categories table;.
     *
     * @var string
     */
    private $item_categories_table = 'item_category';

    /**
     * Name of the countries table.
     *
     * @var string
     */
    private $countries_table = 'port_country';

    /**
     * Name of the states table.
     *
     * @var string
     */
    private $states_table = 'states';

    /**
     * Name of the cities table.
     *
     * @var string
     */
    private $cities_table = 'zips';

    // UPLOAD CONFIG FUNCTIONS
    public function insert_upload_config($data = array())
    {
        if (empty($data)) {
            return false;
        }

        $this->db->insert($this->items_bulk_uploads_config_table, $data);

        return $this->db->last_insert_id();
    }

    public function update_upload_config($id_config = 0, $update = array())
    {
        if (empty($update)) {
            return false;
        }

        $this->db->where('id_config', $id_config);

        return $this->db->update($this->items_bulk_uploads_config_table, $update);
    }

    public function get_user_upload_config($id_user)
    {
        $sql = "SELECT *
				FROM {$this->items_bulk_uploads_config_table}
				WHERE id_user = ?";

        return $this->db->query_one($sql, array($id_user));
    }

    public function get_upload_config($id_config)
    {
        $sql = "SELECT *
				FROM {$this->items_bulk_uploads_config_table}
				WHERE id_config = ?";

        return $this->db->query_one($sql, array($id_config));
    }

    // UPLOAD FILE FUNCTIONS
    public function insert_upload_data($data = array())
    {
        if (empty($data)) {
            return false;
        }

        $this->db->insert($this->items_bulk_uploads_table, $data);

        return $this->db->last_insert_id();
    }

    public function update_upload_data($id_upload = 0, $update = array())
    {
        if (empty($update)) {
            return false;
        }

        $this->db->where('id_upload', $id_upload);

        return $this->db->update($this->items_bulk_uploads_table, $update);
    }

    public function get_upload_data($id_upload)
    {
        $sql = "SELECT *
				FROM {$this->items_bulk_uploads_table}
				WHERE id_upload = ?";

        return $this->db->query_one($sql, array($id_upload));
    }

    // ITEMS DRAFT FUNCTIONS
    public function insert_items_draft($data)
    {
        if (empty($data)) {
            return;
        }

        $this->db->insert_batch($this->items_draft_table, $data);

        return $this->db->getAffectableRowsAmount();
    }

    public function update_item_draft($id_draft, $data = array())
    {
        if (empty($data)) {
            return false;
        }
        $this->db->where('id_draft', $id_draft);

        return $this->db->update($this->items_draft_table, $data);
    }

    public function update_items_draft_same_info($id_draft, $data = array())
    {
        if (empty($data)) {
            return false;
        }

        $this->db->in('id_draft', $id_draft);

        return $this->db->update($this->items_draft_table, $data);
    }

    public function get_item_draft($id_draft)
    {
        $sql = "SELECT *
				FROM {$this->items_draft_table}
				WHERE id_draft = ?";

        return $this->db->query_one($sql, array($id_draft));
    }

    public function delete_item_draft($id_draft)
    {
        $this->db->in('id_draft', $id_draft);

        return $this->db->delete($this->items_draft_table);
    }

    public function get_item_drafts(array $params = array())
    {
        return $this->findRecords(
            'item_drafts',
            $this->items_draft_table,
            $this->items_draft_table_alias,
            $params
        );
    }

    public function count_item_drafts(array $params = array())
    {
        unset($params['order'], $params['with'], $params['limit'], $params['skip']);
        if (!isset($params['columns'])) {
            $params['columns'] = array('COUNT(*) AS AGGREGATE');
        }

        $is_multiple = isset($params['multiple']) ? (bool) $params['multiple'] : false;
        $counters = $this->get_item_drafts($params);
        if ($is_multiple) {
            return $counters;
        }

        return (int) arrayGet($counters, '0.AGGREGATE', 0);
    }

    // HELPERS FUNCTIONS
    public function get_unit_types()
    {
        $sql = 'SELECT *
                FROM unit_type
                ORDER BY unit_name';

        return $this->db->query_all($sql);
    }

    public function is_draft_owner($draft_id, $owner_id)
    {
        $this->db->select('COUNT(*) AS AGGREGATE');
        $this->db->from("{$this->items_draft_table} AS DRAFTS");
        $this->db->where('`DRAFTS`.`id_draft` = ?', $draft_id);
        $this->db->where('`DRAFTS`.`id_seller` = ?', $owner_id);
        $counter = $this->db->query_one();
        if (!$counter || empty($counter)) {
            return false;
        }

        return isset($counter['AGGREGATE']) ? (bool) (int) $counter['AGGREGATE'] : false;
    }

    /**
     * Scope a query to filter by draft ID.
     *
     * @param int $draft_id
     */
    protected function scopeItemDraftsId(QueryBuilder $builder, $draft_id)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->items_draft_table_alias}.id_draft",
                $builder->createNamedParameter((int) $draft_id, ParameterType::INTEGER, $this->nameScopeParameter('draftId'))
            )
        );
    }

    /**
     * Scope a query to filter by draft ID.
     *
     * @see Items_draft_Model::scopeItemDraftsId()
     *
     * @param int $draft_id
     */
    protected function scopeItemDraftsDraft(QueryBuilder $builder, $draft_id)
    {
        $this->scopeItemDraftsId($builder, $draft_id);
    }

    /**
     * Scope a query to filter by draft ID list.
     *
     * @param mixed $drafts
     */
    protected function scopeItemDraftsList(QueryBuilder $builder, $drafts)
    {
        if (empty($drafts)) {
            return;
        }

        if (is_array($drafts)) {
            $drafts = array_map('intval', $drafts);
        } elseif (is_string($drafts) && false !== strpos($drafts, ',')) {
            $drafts = array_map('intval', explode(',', $drafts));
        } else {
            $drafts = array((int) $drafts);
        }

        $builder->andWhere(
            $builder->expr()->in(
                "{$this->items_draft_table_alias}.id_draft",
                array_map(
                    fn (int $index, $draft) => $builder->createNamedParameter(
                        (int) $draft,
                        ParameterType::INTEGER,
                        $this->nameScopeParameter("draftIds{$index}")
                    ),
                    array_keys($drafts),
                    $drafts
                )
            )
        );
    }

    /**
     * Scope a query to filter by categories ID list.
     *
     * @param mixed $categories
     */
    protected function scopeItemDraftsCategories(QueryBuilder $builder, $categories)
    {
        if (empty($categories)) {
            return;
        }
        if (is_array($categories)) {
            $categories = array_map('intval', $categories);
        } elseif (is_string($categories) && false !== strpos($categories, ',')) {
            $categories = array_map('intval', explode(',', $categories));
        } else {
            $categories = array((int) $categories);
        }

        $builder->andWhere(
            $builder->expr()->in(
                "{$this->items_draft_table_alias}.id_cat",
                array_map(
                    fn (int $index, $category) => $builder->createNamedParameter(
                        (int) $category,
                        ParameterType::INTEGER,
                        $this->nameScopeParameter("draftCategoriesIds{$index}")
                    ),
                    array_keys($categories),
                    $categories
                )
            )
        );
    }

    /**
     * Scope a query to filter by seller ID.
     *
     * @param int $seller_id
     */
    protected function scopeItemDraftsSeller(QueryBuilder $builder, $seller_id)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->items_draft_table_alias}.id_seller",
                $builder->createNamedParameter((int) $seller_id, ParameterType::INTEGER, $this->nameScopeParameter('sellerId'))
            )
        );
    }

    /**
     * Scope a query to filter by origin country ID.
     *
     * @param int $country_id
     */
    protected function scopeItemDraftsOriginCountry(QueryBuilder $builder, $country_id)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->items_draft_table_alias}.origin_country",
                $builder->createNamedParameter((int) $country_id, ParameterType::INTEGER, $this->nameScopeParameter('originCountryId'))
            )
        );
    }

    /**
     * Scope a query to filter by country ID.
     *
     * @param int $country_id
     */
    protected function scopeItemDraftsCountry(QueryBuilder $builder, $country_id)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->items_draft_table_alias}.p_country",
                $builder->createNamedParameter((int) $country_id, ParameterType::INTEGER, $this->nameScopeParameter('countryId'))
            )
        );
    }

    /**
     * Scope a query to filter by state ID.
     *
     * @param int $state_id
     */
    protected function scopeItemDraftsState(QueryBuilder $builder, $state_id)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->items_draft_table_alias}.state",
                $builder->createNamedParameter((int) $state_id, ParameterType::INTEGER, $this->nameScopeParameter('stateId'))
            )
        );
    }

    /**
     * Scope a query to filter by city ID.
     *
     * @param int $city_id
     */
    protected function scopeItemDraftsCity(QueryBuilder $builder, $city_id)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->items_draft_table_alias}.p_city",
                $builder->createNamedParameter((int) $city_id, ParameterType::INTEGER, $this->nameScopeParameter('cityId'))
            )
        );
    }

    /**
     * Scope a query to filter draft by the price grater or equal to the provided value.
     *
     * @param mixed $price
     */
    protected function scopeItemDraftsFromPrice(QueryBuilder $builder, $price)
    {
        $builder->andWhere(
            $builder->expr()->gte(
                "{$this->items_draft_table_alias}.price",
                $builder->createNamedParameter(
                    moneyToDecimal(priceToUsdMoney($price)),
                    ParameterType::STRING,
                    $this->nameScopeParameter('fromPrice')
                )
            )
        );
    }

    /**
     * Scope a query to filter draft by the price less or equal to the provided value.
     *
     * @param mixed $price
     */
    protected function scopeItemDraftsToPrice(QueryBuilder $builder, $price)
    {
        $builder->andWhere(
            $builder->expr()->lte(
                "{$this->items_draft_table_alias}.price",
                $builder->createNamedParameter(
                    moneyToDecimal(priceToUsdMoney($price)),
                    ParameterType::STRING,
                    $this->nameScopeParameter('toPrice')
                )
            )
        );
    }

    /**
     * Scope a query to filter by creation datetime.
     *
     * @param \DateTimeInterface|int|string $created_at
     */
    protected function scopeItemDraftsCreatedAt(QueryBuilder $builder, $created_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($created_at))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->items_draft_table_alias}.create_date",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('draftCreatedAt'))
            )
        );
    }

    /**
     * Scope a query to filter by creation datetime from.
     *
     * @param \DateTimeInterface|int|string $created_at
     */
    protected function scopeItemDraftsCreatedFrom(QueryBuilder $builder, $created_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($created_at))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->gte(
                "{$this->items_draft_table_alias}.create_date",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('draftCreatedFrom'))
            )
        );
    }

    /**
     * Scope a query to filter by creation datetime to.
     *
     * @param \DateTimeInterface|int|string $created_at
     */
    protected function scopeItemDraftsCreatedTo(QueryBuilder $builder, $created_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($created_at))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->lte(
                "{$this->items_draft_table_alias}.create_date",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('draftCreatedTo'))
            )
        );
    }

    /**
     * Scope a query to filter by creation date.
     *
     * @param \DateTimeInterface|int|string $created_at
     */
    protected function scopeItemDraftsCreatedAtDate(QueryBuilder $builder, $created_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($created_at, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->eq(
                "DATE({$this->items_draft_table_alias}.create_date)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('draftCreatedAtDate'))
            )
        );
    }

    /**
     * Scope a query to filter by creation date from.
     *
     * @param \DateTimeInterface|int|string $created_at
     */
    protected function scopeItemDraftsCreatedFromDate(QueryBuilder $builder, $created_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($created_at, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->gte(
                "DATE({$this->items_draft_table_alias}.create_date)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('draftCreatedFromDate'))
            )
        );
    }

    /**
     * Scope a query to filter by creation date to.
     *
     * @param \DateTimeInterface|int|string $created_at
     */
    protected function scopeItemDraftsCreatedToDate(QueryBuilder $builder, $created_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($created_at, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->lte(
                "DATE({$this->items_draft_table_alias}.create_date)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('draftCreatedToDate'))
            )
        );
    }

    /**
     * Scope a query to filter by update datetime.
     *
     * @param \DateTimeInterface|int|string $updated_at
     */
    protected function scopeItemDraftsUpdatedAt(QueryBuilder $builder, $updated_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($updated_at))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->items_draft_table_alias}.update_date",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('draftUpdatedAt'))
            )
        );
    }

    /**
     * Scope a query to filter by update datetime from.
     *
     * @param \DateTimeInterface|int|string $updated_at
     */
    protected function scopeItemDraftsUpdatedFrom(QueryBuilder $builder, $updated_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($updated_at))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->gte(
                "{$this->items_draft_table_alias}.update_date",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('draftUpdatedFrom'))
            )
        );
    }

    /**
     * Scope a query to filter by update datetime to.
     *
     * @param \DateTimeInterface|int|string $updated_at
     */
    protected function scopeItemDraftsUpdatedTo(QueryBuilder $builder, $updated_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($updated_at))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->lte(
                "{$this->items_draft_table_alias}.update_date",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('draftUpdatedTo'))
            )
        );
    }

    /**
     * Scope a query to filter by update date.
     *
     * @param \DateTimeInterface|int|string $updated_at
     */
    protected function scopeItemDraftsUpdatedAtDate(QueryBuilder $builder, $updated_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($updated_at, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->eq(
                "DATE({$this->items_draft_table_alias}.update_date)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('draftUpdatedAtDate'))
            )
        );
    }

    /**
     * Scope a query to filter by update date from.
     *
     * @param \DateTimeInterface|int|string $updated_at
     */
    protected function scopeItemDraftsUpdatedFromDate(QueryBuilder $builder, $updated_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($updated_at, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->gte(
                "DATE({$this->items_draft_table_alias}.update_date)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('draftUpdatedFromDate'))
            )
        );
    }

    /**
     * Scope a query to filter by update date to.
     *
     * @param \DateTimeInterface|int|string $updated_at
     */
    protected function scopeItemDraftsUpdatedToDate(QueryBuilder $builder, $updated_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($updated_at, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->lte(
                "DATE({$this->items_draft_table_alias}.update_date)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('draftUpdatedToDate'))
            )
        );
    }

    /**
     * Scope a query to filter by expiration datetime.
     *
     * @param \DateTimeInterface|int|string $updated_at
     */
    protected function scopeItemDraftsExpiredAt(QueryBuilder $builder, $updated_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($updated_at))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->items_draft_table_alias}.expire_date",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('draftExpiresAt'))
            )
        );
    }

    /**
     * Scope a query to filter by expiration datetime from.
     *
     * @param \DateTimeInterface|int|string $updated_at
     */
    protected function scopeItemDraftsExpiredFrom(QueryBuilder $builder, $updated_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($updated_at))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->gte(
                "{$this->items_draft_table_alias}.expire_date",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('draftExpiresFrom'))
            )
        );
    }

    /**
     * Scope a query to filter by expiration datetime to.
     *
     * @param \DateTimeInterface|int|string $updated_at
     */
    protected function scopeItemDraftsExpiredTo(QueryBuilder $builder, $updated_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($updated_at))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->lte(
                "{$this->items_draft_table_alias}.expire_date",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('draftExpiresTo'))
            )
        );
    }

    /**
     * Scope a query to filter by expiration date.
     *
     * @param \DateTimeInterface|int|string $updated_at
     */
    protected function scopeItemDraftsExpiredAtDate(QueryBuilder $builder, $updated_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($updated_at, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->eq(
                "DATE({$this->items_draft_table_alias}.expire_date)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('draftExpiresAtDate'))
            )
        );
    }

    /**
     * Scope a query to filter by expiration date from.
     *
     * @param \DateTimeInterface|int|string $updated_at
     */
    protected function scopeItemDraftsExpiredFromDate(QueryBuilder $builder, $updated_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($updated_at, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->gte(
                "DATE({$this->items_draft_table_alias}.expire_date)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('draftExpiresFromDate'))
            )
        );
    }

    /**
     * Scope a query to filter by expiration date to.
     *
     * @param \DateTimeInterface|int|string $updated_at
     */
    protected function scopeItemDraftsExpiredToDate(QueryBuilder $builder, $updated_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($updated_at, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->lte(
                "DATE({$this->items_draft_table_alias}.expire_date)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('draftExpiresToDate'))
            )
        );
    }

    /**
     * Scope a query to filter by keywords.
     *
     * @param string $keywords
     */
    protected function scopeItemDraftsSearch(QueryBuilder $builder, $keywords)
    {
        if (str_word_count_utf8($keywords) > 1) {
            $escaped_search_string = $this->db->getConnection()->quote(trim($keywords));
            $search_parts = preg_split('/\\b/', trim($escaped_search_string, "'"));
            $search_parts = array_map('trim', $search_parts);
            $search_parts = array_filter($search_parts);
            if (!empty($search_parts)) {
                // Drop array keys
                $search_parts = array_values($search_parts);
                $builder->andWhere(
                    sprintf(
                        'MATCH (%s.title, %s.description, %s.search_info) AGAINST (%s IN BOOLEAN MODE)',
                        $this->items_draft_table_alias,
                        $this->items_draft_table_alias,
                        $this->items_draft_table_alias,
                        $builder->createNamedParameter(
                            $builder->expr()->literal(implode('* <', $search_parts) . '*'),
                            ParameterType::STRING,
                            $this->nameScopeParameter('eventSearchMatchedText')
                        )
                    )
                );
            }
        } else {
            $text_parameter = $builder->createNamedParameter(
                $keywords,
                ParameterType::STRING,
                $this->nameScopeParameter('eventSearchText')
            );
            $text_token_parameter = $builder->createNamedParameter(
                "%{$keywords}%",
                ParameterType::STRING,
                $this->nameScopeParameter('eventSearchTextToken')
            );

            $expressions = $builder->expr();
            $builder->andWhere(
                $expressions->or(
                    $expressions->eq("{$this->items_draft_table_alias}.title", $text_parameter),
                    $expressions->eq("{$this->items_draft_table_alias}.description", $text_parameter),
                    $expressions->eq("{$this->items_draft_table_alias}.search_info", $text_parameter),
                    $expressions->like("{$this->items_draft_table_alias}.title", $text_token_parameter),
                    $expressions->like("{$this->items_draft_table_alias}.description", $text_token_parameter),
                    $expressions->like("{$this->items_draft_table_alias}.search_info", $text_token_parameter)
                )
            );
        }
    }

    /**
     * Resolves static relationships with seller.
     */
    protected function itemDraftsSeller(): RelationInterface
    {
        return $this->belongsTo(
            new PortableModel($this->getHandler(), $this->users_table, 'idu'),
            'id_seller'
        )->disableNativeCast();
    }

    /**
     * Resolves static relationships with category.
     */
    protected function itemDraftsCategory(): RelationInterface
    {
        return $this->belongsTo(
            new PortableModel($this->getHandler(), $this->item_categories_table, 'category_id'),
            'id_cat'
        )->disableNativeCast();
    }

    /**
     * Resolves static relationships with origin country.
     */
    protected function itemDraftsOriginCountry(): RelationInterface
    {
        return $this->belongsTo(
            new PortableModel($this->getHandler(), $this->countries_table, 'id'),
            'origin_country'
        )->disableNativeCast();
    }

    /**
     * Resolves static relationships with country.
     */
    protected function itemDraftsCountry(): RelationInterface
    {
        return $this->belongsTo(
            new PortableModel($this->getHandler(), $this->countries_table, 'id'),
            'p_country'
        )->disableNativeCast();
    }

    /**
     * Resolves static relationships with state.
     */
    protected function itemDraftsState(): RelationInterface
    {
        return $this->belongsTo(
            new PortableModel($this->getHandler(), $this->states_table, 'id'),
            'state'
        )->disableNativeCast();
    }

    /**
     * Resolves static relationships with city.
     */
    protected function itemDraftsCity(): RelationInterface
    {
        return $this->belongsTo(
            new PortableModel($this->getHandler(), $this->cities_table, 'id'),
            'p_city'
        )->disableNativeCast();
    }

    /**
     * Creates new related instance of the model for relation.
     *
     * @param BaseModel|Model|string $source
     */
    protected function resolveRelatedModel($source): Model
    {
        if ($source === $this) {
            return new PortableModel($this->getHandler(), $this->items_draft_table_alias, 'id_draft');
        }

        return parent::resolveRelatedModel($source);
    }
}
