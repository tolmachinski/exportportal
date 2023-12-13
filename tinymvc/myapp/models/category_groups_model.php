<?php

declare(strict_types=1);

use App\Common\Database\BaseModel;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Model Category_groups.
 *
 * @author Usinevici Alexandr
 */
class Category_groups_Model extends BaseModel
{
    /**
     * List of columns the table which is later used on create or update.
     * Each record must be an array that have filled keys:
     *  - name: is a name of the column
     *  - type: indicates the type of the column
     *  - fillable: indicates that column is fillable in NORMAL mode (columns with this flag set to `false` are fillable in FORCE mode).
     *
     * @var array
     */
    protected $category_groups_columns_metadata = [
        ['name' => 'id_group',     'fillable' => false,    'type' => 'int'],
        ['name' => 'title',        'fillable' => true,     'type' => 'string'],
    ];

    /**
     * Name of the item categories table.
     *
     * @var string
     */
    private $category_table = 'item_category';

    /**
     * Alias of the item categories table.
     *
     * @var string
     */
    private $category_table_alias = 'CATEGORIES';

    /**
     * Name of the item category groups table.
     *
     * @var string
     */
    private $category_groups_table = 'item_category_groups';

    /**
     * Alias of the item category groups table.
     *
     * @var string
     */
    private $category_groups_table_alias = 'GROUPS';

    /**
     * Name of the category group relation table.
     *
     * @var string
     */
    private $categories_group_relation_table = 'item_categories_group_relation';

    /**
     * Alias of the category group relation table.
     *
     * @var string
     */
    private $categories_group_relation_table_alias = 'GROUP_RELATIONS';

    public function get_category_groups(array $params = [])
    {
        return $this->findRecords(
            'category_groups',
            $this->category_groups_table,
            "`{$this->category_groups_table_alias}`",
            $params
        );
    }

    public function get_group_categories(array $params = [])
    {
        return $this->findRecords(
            'category',
            $this->category_table,
            $this->category_table_alias,
            $params
        );
    }

    public function get_golden_category($id_group)
    {
        $this->db->select('c.*');
        $this->db->from("{$this->category_groups_table} c");
        $this->db->where('id_group = ?', $id_group);

        return $this->db->query_one();
    }

    public function get_child_golden_category($id_category)
    {
        $this->db->select('c.*');
        $this->db->from("{$this->categories_group_relation_table} c");
        $this->db->where('id_category = ?', $id_category);

        return $this->db->query_one();
    }

    public function find_groups(array $params = [])
    {
        return $this->findRecords(
            'category_groups',
            $this->category_groups_table,
            $this->category_groups_table_alias,
            $params
        );
    }

    public function find_categories(array $params = [])
    {
        return $this->findRecords(
            'category',
            $this->category_table,
            $this->category_table_alias,
            $params
        );
    }

    public function get_categories_by_group($group_id)
    {
        return $this->find_categories([
            'columns'    => ["`{$this->category_table_alias}`.*"],
            'joins'      => ['groups_relation'],
            'order'      => ['name' => 'ASC'],
            'conditions' => [
                'id_group' => $group_id,
            ],
        ]);
    }

    // public function get_group_categories($id_group)
    // {
    //     $this->db->select($this->category_table . '.*');
    //     $this->db->from($this->category_table);
    //     $this->db->join($this->category_groups_relation_table, $this->category_groups_relation_table . '.id_category = ' . $this->category_table . '.category_id', 'left');
    //     $this->db->where($this->category_groups_relation_table . '.id_group', $id_group);

    //     return $this->db->get();
    // }

    // public function exist_category_group($id_group)
    // {
    //     $this->db->select('COUNT(*) AS counter');
    //     $this->db->from($this->category_groups_table);
    //     $this->db->where($this->category_groups_table . '.id_group', $id_group);

    //     $result = $this->db->get_one();

    //     return (bool) $result['counter'];
    // }

    /**
     * @deprecated
     *
     * @param mixed $data
     */
    public function append_children_to_golden($data)
    {
        if (empty($data)) {
            return false;
        }

        return $this->db->insert($this->categories_group_relation_table, $data);

        return $this->db->last_insert_id();
    }

    /**
     * @deprecated
     *
     * @param mixed $idCategory
     * @param mixed $data
     */
    public function update_children_to_golden($idCategory, $data)
    {
        $this->db->where('id_category', $idCategory);

        return $this->db->update($this->categories_group_relation_table, $data);
    }

    /**
     * @deprecated
     *
     * @param mixed $idCategory
     */
    public function delete_groups_relation($idCategory)
    {
        $this->db->where('id_category', $idCategory);

        return $this->db->delete($this->categories_group_relation_table);
    }

    /**
     * @deprecated
     *
     * @param mixed $idCategory
     */
    public function is_restricted_category($idCategory)
    {
        $this->db->select('c.is_restricted');
        $this->db->from("{$this->category_table} c");
        $this->db->where('category_id = ?', $idCategory);

        return $this->db->query_one();
    }

    /**
     * Scope a query to filter by category group id.
     *
     * @param int $id_group
     */
    protected function scopeCategoryGroupsIdGroup(QueryBuilder $builder, $id_group)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->category_groups_table_alias . '.id_group',
                $builder->createNamedParameter((int) $id_group, ParameterType::INTEGER, $this->nameScopeParameter('categoryGroupsToGroups'))
            )
        );
    }

    /**
     * Scope a query to filter by category group id from $categories_group_relation_table.
     *
     * @param int $id_group
     */
    protected function scopeCategoryIdGroup(QueryBuilder $builder, $id_group)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->categories_group_relation_table_alias . '.id_group',
                $builder->createNamedParameter((int) $id_group, ParameterType::INTEGER, $this->nameScopeParameter('categoryToGroups'))
            )
        );
    }

    /**
     * Scope a query to bind relations to the query.
     */
    protected function bindCategoryGroupsRelation(QueryBuilder $builder)
    {
        $builder->join(
            $this->category_table_alias,
            $this->categories_group_relation_table,
            $this->categories_group_relation_table_alias,
            "`{$this->category_table_alias}`.`category_id` = `{$this->categories_group_relation_table_alias}`.`id_category`"
        );
    }
}

// End of file category_groups_model.php
// Location: /tinymvc/myapp/models/category_groups_model.php
