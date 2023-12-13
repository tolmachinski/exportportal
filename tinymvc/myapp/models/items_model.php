<?php

use App\Common\Database\BaseModel;
use App\Common\Database\Concerns\CanSearch;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\ParameterType;

/**
 * items_model.php
 * items system model
 * @author Andrew Litra
 *
 * @deprecated 2.25 in favot of the \Products_Model
 */
class Items_Model extends BaseModel
{
    use CanSearch;

    // hold the current controller instance
    var $obj;
    public $items_list_elasticsearch = array();

    /**
     * {@inheritdoc}
     */
    protected string $table = "items";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "ITEMS";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id";

    /**
     * Returns the table name.
     */
    final public function getTable(): string
    {
        return $this->table;
    }

    public $validation_rule_add = array(
        array(
                'field' => 'price',
                'label' => 'Price',
                'rules' => array('required' => '')
            ),
            array(
                'field' => 'title',
                'label' => 'Title',
                'rules' => array('required' => '')
            ),
            array(
                'field' => 'currency',
                'label' => 'Currency',
                'rules' => array('required' => '')
            ),
            array(
                'field' => 'weight',
                'label' => 'Weight',
                'rules' => array('required' => '')
            ),
            array(
                'field' => 'port_country',
                'label' => 'Country',
                'rules' => array('required' => '')
            ),
            array(
                'field' => 'port_city',
                'label' => 'City',
                'rules' => array('required' => '')
            ),
            array(
                'field' => 'description',
                'label' => 'Description',
                'rules' => array('required' => '')
            ),
            array(
                'field' => 'category',
                'label' => 'Category',
                'rules' => array('required' => '')
            ),
            array(
                'field' => 'quantity',
                'label' => 'Quantity',
                'rules' => array('required' => '')
            )
    );

    /**
     * Method created to get items
     *
     * @param array $conditions
     *
     * @return array List of items
     */
    public function getItems(array $conditions): array
    {
        return $this->findRecords(
            null,
            $this->getTable(),
            null,
            $conditions
        );
    }

    /**
     *
     * @param array $conditions
     *
     * @return array
     */
    function getItemsAvailableOnPublicPage(array $conditions = []): array
    {
        $this->db->from('items i');

        $selectedColumns = $conditions['select'] ?? [
            '`i`.*',
            '`u`.*',
            '`cat`.*',
        ];

        if (!empty($selectedColumns)) {
            $this->db->select(is_array($selectedColumns) ? implode(', ', $selectedColumns) : (string) $selectedColumns);
        }

        $this->db->where('i.visible', 1);
        $this->db->where('i.moderation_is_approved', 1);
        $this->db->where('i.blocked', 0);
        $this->db->in('i.status', [1, 2, 3]);
        $this->db->where('i.draft', 0);
        $this->db->where('u.fake_user', 0);
        $this->db->where('u.status', 'active');

        if (isset($conditions['modelUser'])) {
            $this->db->where('u.is_model', (int) $conditions['modelUser']);
        }

        $this->db->join('users u', 'i.id_seller = u.idu', 'left');
        $this->db->join('item_category cat', 'i.id_cat = cat.category_id', 'left');

        return $this->db->get();
    }

    /**
     *
     * @param array $conditions
     *
     * @return int
     */
    function getCountItemsAvailableOnPublicPage(array $conditions = []): int
    {
        $this->db->from('items i');
        $this->db->select('COUNT(*) as countItems');
        $this->db->where('i.visible', 1);
        $this->db->where('i.moderation_is_approved', 1);
        $this->db->where('i.blocked', 0);
        $this->db->in('i.status', [1, 2, 3]);
        $this->db->where('i.draft', 0);
        $this->db->where('u.fake_user', 0);
        $this->db->where('u.status', 'active');

        if (isset($conditions['modelUser'])) {
            $this->db->where('u.is_model', (int) $conditions['modelUser']);
        }

        $this->db->join('users u', 'i.id_seller = u.idu', 'left');
        $this->db->join('item_category cat', 'i.id_cat = cat.category_id', 'left');

        return (int) ($this->db->get_one()['countItems'] ?? 0);
    }

    /**
     * @param array $conditions
     *
     * @return array
     */
    function getReportItemsPerIndustry(array $conditions = []): array
    {
        $this->db->select("CAST(JSON_EXTRACT(JSON_KEYS(JSON_EXTRACT(CONCAT('[', item_category.breadcrumbs, ']'), '$[0]')), '$[0]') AS UNSIGNED) AS industryId, COUNT(*) AS countItems");
        $this->db->from('items');
        $this->db->join('item_category', 'items.id_cat = item_category.category_id', 'left');
        $this->db->join('users', 'items.id_seller = users.idu', 'left');
        $this->db->where('users.`fake_user`', 0);

        if (isset($conditions['draft'])) {
            $this->db->where('items.`draft`', (int) $conditions['draft']);
        }

        if (isset($conditions['modelUser'])) {
            $this->db->where('users.`is_model`', (int) $conditions['modelUser']);
        }

        if ($conditions['onlyActiveItems'] ?? false) {
            $this->db->where('items.`visible`', 1);
            $this->db->where('items.`moderation_is_approved`', 1);
            $this->db->where('items.`blocked`', 0);
            $this->db->in('items.`status`', [1, 2, 3]);
            $this->db->where('items.`draft`', 0);
            $this->db->where('users.`status`', 'active');
        }

        $this->db->groupby('industryId');
        $this->db->orderby('countItems DESC');

        $queryResult = $this->db->get();

        return array_column($queryResult, null, 'industryId');
    }

    /**
     * demo users and model users items are not considered
     *
     * @param array $conditions
     *
     * @return array
     */
    function getItemsNotAvailableOnPublicPage(array $conditions = []): array
    {
        $this->db->from('items i');

        $selectedColumns = $conditions['select'] ?? [
            '`i`.*',
            '`u`.*',
            '`cat`.*',
        ];

        if (!empty($selectedColumns)) {
            $this->db->select(is_array($selectedColumns) ? implode(', ', $selectedColumns) : (string) $selectedColumns);
        }

        $this->db->or_where('i.visible != ?', 1);
        $this->db->or_where('i.moderation_is_approved != ?', 1);
        $this->db->or_where('i.blocked != ?', 0);
        $this->db->or_where('i.draft != ?', 0);
        $this->db->or_in('i.status', [4, 5, 6]);
        $this->db->or_where('u.status != ?', 'active');
        $this->db->where('u.fake_user = ?', 0);
        $this->db->where('u.is_model = ?', 0);

        $this->db->join('users u', 'i.id_seller = u.idu', 'left');
        $this->db->join('item_category cat', 'i.id_cat = cat.category_id', 'left');

        return $this->db->get();
    }

    function increment_views($id) {
        $sql = "UPDATE items SET views = views + 1 WHERE id = ? LIMIT 1";
        $this->db->query($sql, [$id]);
    }

    function get_currency($only = 'enable'){
        $where = '';
        switch($only){
            case 'enable':
                $where = " WHERE `enable` = '1'";
            break;
            case 'disable':
                $where = " WHERE `enable` = '0'";
            break;
            case 'all':
            default:
            break;
        }
        $sql = "SELECT * FROM currency $where ORDER BY code";

        return $this->db->query_all($sql);
    }

    function get_unit_type($id){
        $sql = "SELECT *
                FROM unit_type
                WHERE id = ?";
        return $this->db->query_one($sql, [$id]);
    }

    function get_unit_types(){
        $sql = "SELECT *
                FROM unit_type
                ORDER BY unit_name";
        return $this->db->query_all($sql);
    }

    function change_product($data){
        $this->db->where('id', $data['id']);
        return $this->db->update('items', $data);
    }

    function get_items_last_id(){
        $sql = "SELECT id
                FROM items
                ORDER BY id DESC
                LIMIT 0,1";

        $rez = $this->db->query_one($sql);

        if(!empty($rez['id']))
            return $rez['id'];
        else
            return 0;
    }

    function get_count_new_items($id_item){
        $sql = "SELECT COUNT(*) as counter
                FROM items
                WHERE id > ? ";

        $rez = $this->db->query_one($sql, array($id_item));
        return $rez['counter'];
    }

    public function get_item($id, $field = false){
        if($field){
            $sql = "SELECT " . $field . " FROM items WHERE id = ?";
        } else {
            $sql = "SELECT
                        it.* ,
                        cat.name AS cat_name, cat.is_restricted, cat.breadcrumbs as cat_breadcrumbs, cat.p_or_m,
                        curr.code AS curr_code, curr.curr_entity,
                        ut.unit_name,
                        pco.country, pco.abr as country_abr,
                        s.state as item_state,
                        pci.city,
                        cb.accreditation,
                        u.fake_user,
                        u.status as `user_status`,
                        u.user_group
                    FROM items it
                    LEFT JOIN item_category cat ON it.id_cat = cat.category_id
                    LEFT JOIN currency curr ON it.currency = curr.id
                    LEFT JOIN unit_type ut ON it.unit_type = ut.id
                    LEFT JOIN port_country pco ON it.p_country = pco.id
                    LEFT JOIN states s ON it.state = s.id
                    LEFT JOIN zips pci ON it.p_city = pci.id
					LEFT JOIN company_base cb ON cb.id_user = it.id_seller AND cb.type_company = 'company'
					LEFT JOIN users u ON it.id_seller = u.idu
                    WHERE it.id = ?";
        }

        return $this->db->query_one($sql, array($id));
    }

    public function get_item_for_snapshot($id_item){
        $sql = "SELECT
                        it.id, it.id_seller, it.title, it.final_price, it.discount, it.description, it.hs_tariff_number,
                        it.item_length, it.item_width, it.item_height, it.weight, it.rev_numb, it.rating, it.origin_country_abr as country_abr,
                        CONCAT_WS(', ',pc.country, s.state, z.city) as item_location, curr.curr_entity,
						ut.unit_name
                FROM items it
                LEFT JOIN currency curr ON it.currency = curr.id
				LEFT JOIN unit_type ut ON it.unit_type = ut.id
                LEFT JOIN port_country pc ON it.p_country = pc.id
                LEFT JOIN states s ON it.state = s.id
                LEFT JOIN zips z ON it.p_city = z.id
                WHERE it.id = ?";

        return $this->db->query_one($sql, array($id_item));
    }

    public function my_item($id_user, $id_item){
        $sql = "SELECT COUNT(*) as counter
                FROM items
                WHERE id_seller = ? AND id = ?";
        $counter = $this->db->query_one($sql, array($id_user, $id_item));
        return $counter['counter'];
    }

    public function my_items(int $user_id, array $items): bool
    {
        if (empty($items) || empty($user_id)) {
            return false;
        }

        $this->db->select('COUNT(*) AS `AGGREGATE`');
        $this->db->from('`items` as `ITEMS`');
        $this->db->where('`ITEMS`.`id_seller` = ?', $user_id);
        $this->db->where_raw("`ITEMS`.`id` IN (" . implode(', ', array_fill(0, count($items), '?')) . ")", $items);

        return count($items) === (int) arrayGet($this->db->query_one(), 'AGGREGATE', 0);
    }

    function item_exist($id_item){
        $sql = "SELECT COUNT(*) as counter
                FROM items
                WHERE id = ?";
        $rez = $this->db->query_one($sql, array($id_item));
        return $rez['counter'];
    }

    public function hasAllItems(array $items): bool
    {
        if (empty($items)) {
            return false;
        }

        $this->db->select("COUNT(*) as `AGGREGATE`");
        $this->db->from('`items` as `ITEMS`');
        $this->db->where_raw(sprintf("`ITEMS`.`id` IN (%s)", implode(', ', array_fill(0, count($items), '?'))), $items);

        return count($items) === (int) arrayGet($this->db->query_one(), 'AGGREGATE', 0);
    }

    public function item_is_accessible(int $item_id): bool
    {
        $this->db->select('COUNT(*) AS `AGGREGATE`');
        $this->db->from('`items` as `ITEMS`');
        $this->db->where('`ITEMS`.`id` = ?', $item_id);
        $this->db->where('`ITEMS`.`visible` = ?', 1);
        $this->db->where('`ITEMS`.`blocked` = ?', 0);

        $counter = $this->db->query_one();
        if (empty($counter)) {
            return false;
        }

        return (bool) (int) ($counter['AGGREGATE'] ?? 0);
    }

    public function items_are_accessible(array $items): bool
    {
        if (empty($items)) {
            return false;
        }

        $this->db->select('COUNT(*) AS `AGGREGATE`');
        $this->db->from('`items` as `ITEMS`');
        $this->db->where_raw(sprintf("`ITEMS`.`id` IN (%s)", implode(', ', array_fill(0, count($items), '?'))), $items);
        $this->db->where('`ITEMS`.`visible` = ?', 1);
        $this->db->where('`ITEMS`.`blocked` = ?', 0);

        return count($items) === (int) arrayGet($this->db->query_one(), 'AGGREGATE', 0);
    }

    public function countBySeller($id_user){
            $sql = "SELECT COUNT(*) as counter
                    FROM items
                    WHERE id_seller = ?
                    AND status = 2";
            $rez = $this->db->query_one($sql, [$id_user]);
            return $rez['counter'];
    }

    public function get_recently_sold_items($conditions){
        $order_by = "io.date_ordered DESC";
        $where = array();
        $params = array();
        $limit = 9;
        extract($conditions);

        if(isset($seller)){
            $where[] = " it.id_seller = ? ";
            $params[] = $seller;
        }

        if(isset($visible)){
            $where[] = " it.visible = ?";
            $params[] = $visible;
        }

        $sql .= "SELECT it.* ,
                    io.*,
                    cat.name AS cat_name,
                    curr.code AS curr_code,curr.curr_entity as curr_entity,
                    ut.unit_name, pco.country, pco.zip,
                    CONCAT_WS(' ',u.fname, u.lname) as buyer_name
                FROM item_ordered io
                LEFT JOIN items it ON it.id = io.id_item
                LEFT JOIN item_category cat ON it.id_cat = cat.category_id
                LEFT JOIN currency curr ON it.currency = curr.id
                LEFT JOIN unit_type ut ON it.unit_type = ut.id
                LEFT JOIN port_country pco ON it.p_country = pco.id
                LEFT JOIN item_orders ios ON io.id_order = ios.id
                LEFT JOIN users u ON u.idu = ios.id_buyer";

        if(count($where))
            $sql .= " WHERE " . implode(" AND", $where);

        $sql .= " GROUP BY it.id";
        $sql .= " ORDER BY " . $order_by;

        $sql .= " LIMIT ".$limit;

        return $this->db->query_all($sql, $params);

    }

    function getItemsLocation($itemsList){
        $itemsList = getArrayFromString($itemsList);
        $sql = "SELECT it.id, pc.city as item_city
                FROM items it
                INNER JOIN zips pc ON it.p_city = pc.id
                WHERE it.state = 0 AND it.id IN (" . implode(',', array_fill(0, count($itemsList), '?')) . ")
                UNION
                    SELECT it.id, CONCAT(z.city, ', ', st.state) as user_city
                    FROM items it
                    LEFT JOIN zips z ON it.p_city = z.id
                    LEFT JOIN states st ON it.state = st.id
                    WHERE it.state != 0 AND it.id IN (" . implode(',', array_fill(0, count($itemsList), '?')) . ")";

        return arrayByKey($this->db->query_all($sql, array_merge($itemsList, $itemsList)), 'id');
    }

    public function get_items_statistics($itemsList, $conditions = []){
        $itemsList = getArrayFromString($itemsList);

        $commentsWhereParams = $itemsList;
        $commentsWhereClause = [
            "id_item IN (" . implode(',', array_fill(0, count($itemsList), '?')) . ")"
        ];

        if (!empty($conditions['comments']['user'])) {
            $commentsWhereClause[] = 'id_user = ?';
            $commentsWhereParams[] = (int) $conditions['comments']['user'];
        }

        if (!empty($conditions['comments']['not_id_user'])) {
            $commentsWhereClause[] = 'id_user != ?';
            $commentsWhereParams[] = (int) $conditions['comments']['not_id_user'];
        }

        $sql = "SELECT it.id ,ic.count_comments, iq.count_questions, iqa.count_q_answered, ito.count_ordered
                FROM items it
                LEFT JOIN (
                    SELECT id_item, COUNT(*) as  count_comments
                    FROM item_comments
                    WHERe " . implode(' AND ', $commentsWhereClause) . "
                    GROUP BY id_item
                ) ic ON it.id = ic.id_item
                LEFT JOIN (
                    SELECT id_item, COUNT(*) as  count_questions
                    FROM item_questions
                    WHERe id_item IN (" . implode(',', array_fill(0, count($itemsList), '?')) . ")
                    GROUP BY id_item
                ) iq ON it.id = iq.id_item
                LEFT JOIN (
                    SELECT id_item, COUNT(*) as  count_q_answered
                    FROM item_questions
                    WHERe id_item IN (" . implode(',', array_fill(0, count($itemsList), '?')) . ") AND reply != ''
                    GROUP BY id_item
                ) iqa ON it.id = iqa.id_item
                LEFT JOIN (
                    SELECT id_item, COUNT(*) as  count_ordered
                    FROM item_ordered
                    WHERe id_item IN (" . implode(',', array_fill(0, count($itemsList), '?')) . ")
                    GROUP BY id_item
                ) ito ON it.id = ito.id_item
                WHERe it.id IN (" . implode(',', array_fill(0, count($itemsList), '?')) . ")
                GROUP BY it.id ";
        return arrayByKey($this->db->query_all($sql, array_merge($commentsWhereParams, $itemsList, $itemsList, $itemsList, $itemsList)), 'id');
    }

    public function get_items_recently_sold($conditions){
        $order_by = "itor.order_date DESC";
        extract($conditions);

        $start = (int) ($start ?? 0);
        $per_p = (int) ($per_p ?? 20);

        $where = [
            ' ip.main_photo = ? ',
            ' itor.status = ? ',
        ];

        $params = [1, 11];

        if(isset($p_or_m)){
            $where[] = " cat.p_or_m = ? ";
            $params[] = $p_or_m;
        }

        $sql = "SELECT it.id, it.id_cat, it.final_price, it.rating, it.title, pco.country, pco.id as id_country, ip.photo_name, itor.order_date
                FROM item_ordered ito
                LEFT JOIN item_orders itor ON ito.id_order = itor.id
                LEFT JOIN items it ON ito.id_item = it.id
                INNER JOIN item_category cat ON it.id_cat = cat.category_id
                LEFT JOIN port_country pco ON it.p_country = pco.id
                LEFT JOIN item_photo ip ON ip.sale_id = it.id AND ip.main_photo = 1";

        if(count($where))
            $sql .= " WHERE " . implode(" AND ", $where);

        if(isset($group_by))
            $sql .= " GROUP BY " . $group_by;

        $sql .= " ORDER BY " . $order_by;
        $sql .= " LIMIT " . $start . "," . $per_p;

        return $this->db->query_all($sql, $params);
    }

    public function get_items_by_category($conditions){
        $order_by = "it.rating DESC";
        extract($conditions);

        $start = (int) ($start ?? 0);
        $per_p = (int) ($per_p ?? 20);

        $params = [];
        $where = ["ip.main_photo = 1 "];

        if (isset($categories)) {
            $categories = getArrayFromString($categories);
            $where[] = " it.id_cat IN (" . implode(',', array_fill(0, count($categories), '?')) . ") ";
            array_push($params, ...$categories);
        }

        $sql = "SELECT it.id, it.id_cat, it.final_price, it.rating, it.title, pco.country, pco.id as id_country, ip.photo_name
                FROM items it
                INNER JOIN item_category cat ON it.id_cat = cat.category_id
                LEFT JOIN port_country pco ON it.p_country = pco.id
                LEFT JOIN item_photo ip ON ip.sale_id = it.id AND ip.main_photo = 1";

        if(count($where))
            $sql .= " WHERE " . implode(" AND", $where);

        $sql .= " ORDER BY " . $order_by;

        if(isset($group_by))
            $sql .= " GROUP BY " . $group_by;

        $sql .= " LIMIT " . $start . "," . $per_p;

        return $this->db->query_all($sql, $params);
    }

    public function get_items_categories($conditions){
        $p_or_m = 2;
        extract($conditions);

        $where = [' cat.p_or_m = ? '];
        $params = [$p_or_m];

        if(isset($items)){
            $items = getArrayFromString($items);
            $where[] = " it.id IN (" . implode(',', array_fill(0, count($items), '?')) . ") ";
        }

        $sql = "SELECT it.id_cat
                FROM items it
                LEFT JOIN item_category cat ON it.id_cat = cat.category_id";

        if(count($where))
            $sql .= " WHERE " . implode(" AND", $where);

        if(isset($group_by))
            $sql .= " GROUP BY " . $group_by;

        return $this->db->query_all($sql, $params);
    }

    public function get_items_countries_counter($category){
        $sql = "SELECT it.p_country, pco.country, COUNT(it.p_country) as counter
                FROM items it
                LEFT JOIN item_category cat ON it.id_cat = cat.category_id
                LEFT JOIN port_country pco ON it.p_country = pco.id
                WHERE cat.p_or_m = ?
                GROUP BY it.p_country
                ORDER BY pco.country";
        return $this->db->query_all($sql, [$category]);
    }

    function get_items_popular($conditions){
        $order_by = "it.rating DESC";
        $p_or_m = 2;
        extract($conditions);

        $start = (int) ($start ?? 0);
        $per_p = (int) ($per_p ?? 20);

        $sql = "SELECT it.id, it.title, it.rating, ip.photo_name
                FROM items it
                INNER JOIN item_category cat ON it.id_cat = cat.category_id
                LEFT JOIN item_photo ip ON ip.sale_id = it.id
                WHERE cat.p_or_m = ? AND ip.main_photo = 1
                ORDER BY {$order_by}
                LIMIT {$start}, {$per_p}";
        return $this->db->query_all($sql, [$p_or_m]);
    }

    public function get_items($conditions)
    {
        $rel = '';
        $page = 0;
        $per_p = 20;
        $order_by = "it.create_date DESC";
        $where = [];
        $params = [];
        $featured_order = 0;
        $order_featured = "";
        $multi_order_by = [];
        $join_photo = "";
        $join_seller_info = "";
        $select_photo = "";
        $seller_info = false;
        $limit_items = true;
        $select_seller_info = "";
        $item_columns = " it.id, it.title, it.id_cat, it.year, it.price, it.discount, it.final_price, it.draft_expire_date,
                    it.currency, it.weight, it.size, it.quantity, it.min_sale_q, it.unit_type,
                    it.create_date, it.expire_date, it.update_date, it.renew, it.id_seller, it.p_country, it.p_city,
                    it.state, it.video, it.status, it.visible, it.is_archived, it.offers, it.featured, it.highlight, it.is_out_of_stock, it.out_of_stock_quantity,
                    it.rev_numb, it.rating, it.changed, it.is_partners_item, it.blocked , it.views, it.total_sold, it.draft, it.samples, it.moderation_is_approved, it.is_handmade";

        extract($conditions);

        if (!empty($sort_by)) {
            if (!is_array($sort_by)) {
                $sort_by = array($sort_by);
            }

            $multi_order_by = [];
            foreach ($sort_by as $sort_item) {
                $sort_item = explode('-', $sort_item);
                $multi_order_by[] = $sort_item[0].' '.$sort_item[1];
            }

            if (!empty($multi_order_by)) {
                $order_by = implode(',', $multi_order_by);
            }
        }

        if (!empty($list_item)) {
            $list_item = is_array($list_item) ? $list_item : explode(',', (string) $list_item);
            $where[] = " it.id IN (" . implode(',', array_fill(0, count($list_item), '?')) . ")";
            array_push($params, ...$list_item);
        }

        if (isset($not_id_item)) {
            $where[] = " it.id != ? ";
            $params[] = $not_id_item;
        }

        if (isset($categories_list)) {
            $categories_list = getArrayFromString($categories_list);
            $where[] = " it.id_cat IN (" . implode(',', array_fill(0, count($categories_list), '?')) . ") ";
            array_push($params, ...$categories_list);
        } elseif(isset($category)) {
            /** @var Category_Model $categoryModel */
            $categoryModel = model(Category_Model::class);
            $subcats = $categoryModel->get_cat_childrens($category) . $category;
            $subcats = getArrayFromString($subcats);
            $where[] = " it.id_cat IN (" . implode(',', array_fill(0, count($subcats), '?')) . ") ";
            array_push($params, ...$subcats);
        }

        if (isset($draft)) {
            $where[] = " it.draft = ? ";
            $params[] = $draft;
        }

        if (isset($is_archived)) {
            $where[] = " it.is_archived = ? ";
            $params[] = (int) $is_archived;
        }

        if (isset($accreditation)) {
            $where[] = " cb.accreditation = ? ";
            $params[] = $accreditation;
        }

        if (isset($seller)) {
            $where[] = " it.id_seller = ? ";
            $params[] = $seller;
        }

        if (isset($company)) {
            $where[] = " cb.id_company = ? ";
            $params[] = $company;
        }

        if (isset($country)) {
            $where[] = " it.p_country = ? ";
            $params[] = $country;
        }

        if (isset($city)) {
            $where[] = " it.p_city = ? ";
            $params[] = $city;
        }

        if (isset($status)) {
            $status = getArrayFromString($status);
            $where[] = " it.status IN (" . implode(',', array_fill(0, count($status), '?')) . ")";
            array_push($params, ...$status);
        }

        if (isset($price_from)) {
            $where[] = " it.final_price >= ?";
            $params[] = $price_from;
        }

        if (isset($expire)) {
            $where[] = " DATE(it.draft_expire_date) = ?";
            $params[] = $expire;
        }

        if (isset($price_to)) {
            $where[] = " it.final_price <= ?";
            $params[] = $price_to;
        }

        if (isset($year_from)) {
            $where[] = " it.year >= ?";
            $params[] = $year_from;
        }

        if (isset($year_to)){
            $where[] = " it.year <= ?";
            $params[] = $year_to;
        }

        if (isset($featured)) {
            $where[] = " it.featured = ?";
            $params[] = $featured;
        }

        if (isset($highlight)) {
            $where[] = " it.highlight = ?";
            $params[] = $highlight;
        }

        if (isset($partnered_item)) {
            $where[] = " it.is_partners_item = ?";
            $params[] = $partnered_item;
        }

        if (isset($visible)) {
            $where[] = " it.visible = ?";
            $params[] = $visible;
        }

        if (isset($blocked)) {
            if ($blocked == 0) {
                $where[] = " it.blocked = 0 ";
            } else{
                $where[] = " it.blocked > 0 ";
            }
        }

        if (isset($is_moderated)) {
            $where[] = " it.moderation_is_approved = ? ";
            $params[] = $is_moderated;
        }

        if (isset($is_draft)) {
            $where[] = " it.draft = ? ";
            $params[] = $is_draft;
        }

        if (isset($blocked_status)) {
            $where[] = " it.blocked = ? ";
            $params[] = $blocked_status;
        }

        if (isset($start_item)) {
            $where[] = " it.id > ?";
            $params[] = $start_item;
        }

        if (isset($motor)) {
            $where[] = " cat.p_or_m = ?";
            $params[] = $motor;
        }

        if (isset($main_photo)) {
            $join_photo = " LEFT JOIN item_photo ip ON ip.sale_id = it.id AND ip.main_photo = 1 ";
            $select_photo = " ip.photo_name, ip.photo_thumbs, ";
        }

        if (isset($start_from)) {
            $where[] = ' DATE(it.create_date) >= ?';
            $params[] = $start_from;
        }

        if (isset($start_to)) {
            $where[] = ' DATE(it.create_date) <= ?';
            $params[] = $start_to;
        }

        if (isset($end_from)) {
            $where[] = ' DATE(it.expire_date) >= ?';
            $params[] = $end_from;
        }

        if (isset($end_to)) {
            $where[] = ' DATE(it.expire_date) <= ?';
            $params[] = $end_to;
        }

        if (isset($update_from)) {
            $where[] = ' DATE(it.update_date) >= ?';
            $params[] = $update_from;
        }

        if (isset($update_to)) {
            $where[] = ' DATE(it.update_date) <= ?';
            $params[] = $update_to;
        }

        if (isset($is_out_of_stock)) {
            $where[] = '(it.is_out_of_stock = 1 OR it.quantity < it.min_sale_q)';
        }

        if (isset($date_out_of_stock)) {
            $where[] = ' DATE(it.date_out_of_stock) <= DATE_SUB(SYSDATE(), INTERVAL 30 DAY)';
        }

        if (isset($translation_status)) {
            $where[] = ' idt.status = ?';
            $params[] = $translation_status;
        }

        if (isset($search_by_username_email)) {
            $where[] = " it.id_seller IN (SELECT idu FROM users WHERE fname LIKE ? OR lname LIKE ? OR email = ?) ";
            array_push($params, ...['%' . $search_by_username_email . '%', '%' . $search_by_username_email . '%', $search_by_username_email]);
        }

        if (isset($search_by_company)) {
            $where[] = " (it.id_seller IN (SELECT id_user FROM company_base WHERE type_company = ? AND (name_company LIKE ? OR legal_name_company LIKE ?))) ";
            array_push($params, 'company', ...array_fill(0, 2, '%' . $search_by_company . '%'));
        }

        if (!empty($search_by_title)) {
            $where[] = " it.title LIKE ? ";
            $params[] = "%{$search_by_title}%";

        }

        if (!empty($keywords)) {

            $keywordsQuery = "((it.title = ? OR it.description = ? OR it.search_info = ?) OR";
            $params[] = $keywords;
            $params[] = $keywords;
            $params[] = $keywords;

            $keywordsToken = $this->tokenizeSearchText($keywords, true);
            if (!empty($keywordsToken)) {
                $keywordsQuery .= "(MATCH (it.title, it.description, it.search_info) AGAINST (? IN BOOLEAN MODE)))";
                $params[] = implode(' ', $keywordsToken);
            } else {
                $keywordsQuery .= "(it.title LIKE ? OR it.description LIKE ? OR it.search_info LIKE ?))";
                $params[] = "%{$keywords}%";
                $params[] = "%{$keywords}%";
                $params[] = "%{$keywords}%";
            }

            $where[] = $keywordsQuery;
        }

        if (isset($attrs)) {
            foreach ($attrs as $attr => $vals) {
                $str = getArrayFromString($vals);

                $where[] = " (
                        SELECT ia.attr_value
                        FROM item_attr ia
                        WHERE ia.item = it.id AND ia.attr = ?
                        GROUP BY ia.item
                        ) IN (" . implode(', ', array_fill(0, count($str), '?')) . ")";

                $params[] = $attr;
                array_push($params, ...$str);
            }
        }

        if (isset($r_attrs)) {
            foreach ($r_attrs as $r_key => $r_attr) {
                if(isset($r_attr['from'])){
                    $where[] = " (
                            SELECT ia.attr_value
                            FROM item_attr ia
                            WHERE ia.item = it.id AND ia.attr = ?
                            GROUP BY ia.item
                            ) >= ? ";
                    $params[] = $r_key;
                    $params[] = (int) $r_attr['from'];
                }

                if (isset($r_attr['to'])) {
                    $where[] = " (
                            SELECT ia.attr_value
                            FROM item_attr ia
                            WHERE ia.item = it.id AND ia.attr = ?
                            GROUP BY ia.item
                            ) <= ? ";

                    $params[] = $r_key;
                    $params[] = (int) $r_attr['to'];
                }
            }
        }

        if (isset($t_attrs)) {
            foreach ($t_attrs as $t_key => $t_attr) {
                $where[] = " (
                        SELECT ia.attr_value
                        FROM item_attr ia
                        WHERE ia.item = it.id AND ia.attr = ?
                        GROUP BY ia.item
                        ) LIKE ? ";

                $params[] = $t_key;
                $params[] = '%' . $t_attr . '%';
            }
        }

        if ($seller_info) {
            $join_seller_info = " LEFT JOIN users u ON it.id_seller = u.idu
                                  LEFT JOIN user_groups ug ON u.user_group = ug.idgroup";
            $select_seller_info = " CONCAT(u.fname, ' ', u.lname) as user_name, u.status as user_status,
                        ug.gr_name, u.user_group, u.user_type, ug.gr_alias, ug.gr_type, cb.name_company, cb.index_name, cb.id_company, cb.type_company, ";
        }

        if (isset($fake_item)) {
            $where[] = ' u.fake_user = ?';
            $params[] = (int) $fake_item;
        }

        $sql = "SELECT " . $item_columns . " ,
                        " .	$select_photo . "
                        " . $select_seller_info . "
                        cat.name AS cat_name, cat.breadcrumbs,
                        curr.code AS curr_code, curr.curr_entity as curr_entity,
                        ut.unit_name, pco.country, pco.zip,
                        itf.status as featured_status, ith.status as highlight_status, ith.id_highlight, itf.id_featured,
                        itf.extend as extend_feature, ith.extend as extend_highlight,
                        idt.status as translated_status
                        $rel
                FROM items it
                LEFT JOIN item_category cat ON it.id_cat = cat.category_id
                LEFT JOIN currency curr ON it.currency = curr.id
                LEFT JOIN unit_type ut ON it.unit_type = ut.id
                LEFT JOIN port_country pco ON it.p_country = pco.id
                LEFT JOIN item_featured itf ON itf.id_item = it.id
                LEFT JOIN item_highlight ith ON ith.id_item = it.id
				LEFT JOIN company_base cb ON cb.id_user = it.id_seller AND cb.type_company = 'company'
				LEFT JOIN items_descriptions idt ON idt.id_item = it.id
                $join_photo
                $join_seller_info";

        if (isset($fake_item) && !$seller_info) {
            $sql .= " LEFT JOIN users u ON it.id_seller = u.idu";
        }

        if (isset($label)) {
            switch ($label) {
                case 1:
                    $where[] = ' it.draft = 0 AND it.blocked IN (1, 2)';

                    break;
                case 2:
                    $where[] = " it.draft = 0 AND it.blocked = 0 AND (it.visible != 1 OR it.moderation_is_approved != 1 OR u.status != 'active')";

                    break;
                case 3:
                    $where[] = " it.draft = 0 AND it.blocked = 0 AND it.visible = 1 AND it.moderation_is_approved = 1 AND u.status = 'active'";

                    break;
            }
        }

        if(count($where)) {
            $sql .= " WHERE " . implode(" AND", $where);
        }

        if ($featured_order) {
            $order_featured = " it.featured DESC, ";
        }

        $sql .= " GROUP BY it.id ";
        $sql .= " ORDER BY " . $order_featured. $order_by;

        if ($limit_items) {

            if (!isset($count)) {
                $count = $this->count_items($conditions);
            }

            $pages = ceil($count / $per_p);

            if (!isset($start)) {
                if ($page > $pages) {
                    $page = $pages;
                }

                $start = ($page - 1) * $per_p;

                if($start < 0) {
                    $start = 0;
                }
            }

            $sql .= " LIMIT " . $start ;

            if(isset($limit)) {
                $per_p = $limit;
            }

            if ($per_p > 0) {
                $sql .= "," . $per_p;
            }
        }

        return $this->db->query_all($sql, $params);
    }

    public function count_items($conditions){
        $where = array();
        $params = array();

        extract($conditions);

        $needJoinWithUser = false;

		if(isset($accreditation)){
            $where[] = " cb.accreditation = ? ";
            $params[] = $accreditation;
        }

        if(isset($category)){
            /** @var Category_Model $categoryModel */
            $categoryModel = model(Category_Model::class);

            $subcats = $categoryModel->get_cat_childrens($category) .  $category;
            $subcats = getArrayFromString($subcats);

            $where[] = " it.id_cat IN (" . implode(',', array_fill(0, count($subcats), '?')) . ") ";
            array_push($params, ...$subcats);
        }elseif(isset($categories_list)){
            $categories_list = getArrayFromString($categories_list);
            $where[] = " it.id_cat IN (" . implode(',', array_fill(0, count($categories_list), '?')) . ") ";
            array_push($params, ...$categories_list);
        }

        if (!empty($list_item)) {
            $list_item = getArrayFromString($list_item);
            $where[] = " it.id IN (" . implode(',', array_fill(0, count($list_item), '?')) . ")";
            array_push($params, ...$list_item);
        }

        if(isset($draft)){
            $where[] = " it.draft = ? ";
            $params[] = $draft;
        }

        if (isset($is_archived)) {
            $where[] = " it.is_archived = ? ";
            $params[] = (int) $is_archived;
        }

        if(isset($seller)){
            $where[] = " it.id_seller = ? ";
            $params[] = $seller;
        }

        if(isset($country)){
            $where[] = " it.p_country = ? ";
            $params[] = $country;
        }

        if(isset($city)){
            $where[] = " it.p_city = ? ";
            $params[] = $city;
        }

        if(isset($status)){
            $status = getArrayFromString($status);
            $where[] = " it.status IN (" . implode(',', array_fill(0, count($status), '?')) . ")";
            array_push($params, ...$status);
        }

        if(isset($price_from)){
            $where[] = " it.final_price >= ?";
            $params[] = $price_from;
        }

        if(isset($price_to)){
            $where[] = " it.final_price <= ?";
            $params[] = $price_to;
        }

        if(isset($expire)){
            $where[] = " DATE(it.draft_expire_date) = ?";
            $params[] = $expire;
        }

        if(isset($year_from)){
            $where[] = " it.year >= ?";
            $params[] = $year_from;
        }

        if(isset($year_to)){
            $where[] = " it.year <= ?";
            $params[] = $year_to;
        }

        if(isset($featured)){
            $where[] = " it.featured = ?";
            $params[] = $featured;
        }

        if(isset($highlight)){
            $where[] = " it.highlight = ?";
            $params[] = $highlight;
        }

        if(isset($partnered_item)){
            $where[] = " it.is_partners_item = ?";
            $params[] = $partnered_item;
        }

        if(isset($visible)){
            $where[] = " it.visible = ?";
            $params[] = $visible;
        }

        if(isset($blocked)){
            if($blocked == 0){
                $where[] = " it.blocked = 0 ";
            } else{
                $where[] = " it.blocked > 0 ";
            }
        }

        if(isset($is_moderated)){
            $where[] = " it.moderation_is_approved = ? ";
            $params[] = $is_moderated;
        }

        if(isset($is_draft)){
            $where[] = " it.draft = ? ";
            $params[] = $is_draft;
        }

        if(isset($start_item)){
            $where[] = " it.id > ?";
            $params[] = $start_item;
        }

        if(isset($start_from)){
            $where[] = ' DATE(it.create_date) >= ?';
            $params[] = $start_from;
        }

        if(isset($start_to)){
            $where[] = ' DATE(it.create_date) <= ?';
            $params[] = $start_to;
        }

        if(isset($end_from)){
            $where[] = ' DATE(it.expire_date) >= ?';
            $params[] = $end_from;
        }

        if(isset($end_to)){
            $where[] = ' DATE(it.expire_date) <= ?';
            $params[] = $end_to;
        }

        if(isset($update_from)){
            $where[] = ' DATE(it.update_date) >= ?';
            $params[] = $update_from;
        }

        if(isset($update_to)){
            $where[] = ' DATE(it.update_date) <= ?';
            $params[] = $update_to;
        }

        if (!empty($search_by_title)) {
            $where[] = " it.title LIKE ? ";
            $params[] = "%{$search_by_title}%";

        }

        if (!empty($keywords)) {

            $keywordsQuery = "((it.title = ? OR it.description = ? OR it.search_info = ?) OR";
            $params[] = $keywords;
            $params[] = $keywords;
            $params[] = $keywords;

            $keywordsToken = $this->tokenizeSearchText($keywords, true);
            if (!empty($keywordsToken)) {
                $keywordsQuery .= "(MATCH (it.title, it.description, it.search_info) AGAINST (? IN BOOLEAN MODE)))";
                $params[] = implode(' ', $keywordsToken);
            } else {
                $keywordsQuery .= "(it.title LIKE ? OR it.description LIKE ? OR it.search_info LIKE ?))";
                $params[] = "%{$keywords}%";
                $params[] = "%{$keywords}%";
                $params[] = "%{$keywords}%";
            }

            $where[] = $keywordsQuery;
        }

        if(isset($attrs)){
            foreach($attrs as $attr => $vals){
                $str = getArrayFromString($vals);

                $where[] = " (
                    SELECT ia.attr_value
                        FROM item_attr ia
                        WHERE ia.item = it.id
                        AND ia.attr = ?
                        GROUP BY ia.item
                        ) IN (" . implode(', ', array_fill(0, count($str), '?')) . ")";
                array_push($params, $attr, ...$str);
            }
        }

        if (isset($search_by_username_email)) {
            $where[] = " it.id_seller IN (SELECT idu FROM users WHERE email = ? OR fname LIKE ? OR lname LIKE ?) ";
            array_push($params, $search_by_username_email, ...array_fill(0, 2, '%' . $search_by_username_email . '%'));
        }

        if (isset($search_by_company)) {
            $where[] = " (it.id_seller IN (SELECT id_user FROM company_base WHERE type_company = ? AND (name_company LIKE ? OR legal_name_company LIKE ?))) ";
            array_push($params, 'company', ...array_fill(0, 2, '%' . $search_by_company . '%'));
        }

        if(isset($translation_status)){
            $where[] = ' idt.status = ?';
            $params[] = $translation_status;
        }

        $sql = "SELECT COUNT(*) as counter
                FROM items it
                LEFT JOIN company_base cb ON cb.id_user = it.id_seller AND cb.type_company = 'company'
                LEFT JOIN items_descriptions idt ON idt.id_item = it.id";

        if (isset($fake_item)) {
            $needJoinWithUser = true;

            $where[] = ' u.fake_user = ?';
            $params[] = (int) $fake_item;
        }

        if (isset($model_item)) {
            $needJoinWithUser = true;

            $where[] = ' u.is_model = ?';
            $params[] = (int) $model_item;
        }

        if(isset($label)){
            $needJoinWithUser = true;

            if (1 === $label) {
                $where[] = " it.draft = 0 AND it.blocked IN (1, 2)";
            } elseif (2 === $label) {
                $where[] = " it.draft = 0 AND it.blocked = 0 AND (it.visible != 1 OR it.moderation_is_approved != 1 OR u.status != 'active')";
            } elseif (3 === $label) {
                $where[] = " it.draft = 0 AND it.blocked = 0 AND it.visible = 1 AND it.moderation_is_approved = 1 AND u.status = 'active'";
            }
        }

        if ($needJoinWithUser) {
            $sql .= " LEFT JOIN users u ON u.idu = it.id_seller";
        }

        if(count($where))
            $sql .= " WHERE " . implode(" AND", $where);

        $rez = $this->db->query_one($sql, $params);

        return $rez['counter'];
    }

    /**
     * This method is used only for generation report Sellers & Products
     */
    public function getGroupedItemsByIndustries()
    {
        $this->db->select("COUNT(*) AS count_items, GROUP_CONCAT(DISTINCT(port_country.country)) as countries,
                            IF(item_category.breadcrumbs != '', CAST(JSON_EXTRACT(JSON_KEYS(JSON_EXTRACT(CONCAT('[', item_category.breadcrumbs, ']'), '$[0]')), '$[0]') AS UNSIGNED), null) as category_root");
        $this->db->from("items");
        $this->db->join("item_category", "items.id_cat = item_category.category_id", "left");
        $this->db->join("users", "users.idu = items.id_seller", "left");
        $this->db->join("port_country", "items.p_country = port_country.id", "left");

        $this->db->where("users.status", "active");
        $this->db->where("users.fake_user", 0);

        $this->db->where("items.visible", 1);
        $this->db->where("items.blocked", 0);

        $this->db->groupby("category_root");

        return $this->db->get();
    }

    public function get_items_statistics_for_crm(array $sellers_ids){
        $this->db->select('COUNT(*) AS count_items, items.id_seller, items.draft, items.visible, items.blocked, IF(items.draft = 0 AND items.blocked = 0 AND items.visible = 1, 1, 0) AS active');
        $this->db->from('items');
        $this->db->in('items.id_seller', $sellers_ids);
        $this->db->groupby('items.id_seller, items.draft, items.visible, items.blocked');

        $result = $this->db->get();

        return empty($result) ? array() : $result;
    }

    //FEATURED
    function get_featured_items_last_id(){
        $sql = "SELECT id_featured
                FROM item_featured
                ORDER BY id_featured DESC
                LIMIT 0,1";

        $rez = $this->db->query_one($sql);

        return $rez['id_featured'] ?: 0;
    }

    function get_count_new_featured_items($id_featured_item){
        $sql = "SELECT COUNT(*) as counter
                FROM item_featured
                WHERE id_featured > ? ";

        $rez = $this->db->query_one($sql, array($id_featured_item));
        return $rez['counter'];
    }

    public function get_featured_items($conditions){
        $page = 0;
        $per_p = 20;
        $order_by = "itf.id_featured DESC";
        $where = array();
        $params = array();
        $multi_order_by = array();
        $join_photo = "";
        $join_seller_info = "";
        $select_photo = "";
        $seller_info = false;
        extract($conditions);

        if(!empty($sort_by)){
            foreach($sort_by as $sort_item){
                $sort_item = explode('-', $sort_item);
                $multi_order_by[] = $sort_item[0].' '.$sort_item[1];
            }

            $order_by = implode(',', $multi_order_by);
        }

        if(isset($categories_list)){
            $categories_list = getArrayFromString($categories_list);
            $where[] = " it.id_cat IN (" . implode(',', array_fill(0, count($categories_list), '?')) . ") ";
            array_push($params, ...$categories_list);
        }elseif(isset($category)){
            /** @var Category_Model $categoryModel */
            $categoryModel = model(Category_Model::class);

            $subcats = $categoryModel->get_cat_childrens($category) .  $category;
            $subcats = getArrayFromString($subcats);

            $where[] = " it.id_cat IN (" . implode(',', array_fill(0, count($subcats), '?')) . ") ";
            array_push($params, ...$subcats);
        }

        if(isset($seller)){
            $where[] = " it.id_seller = ? ";
            $params[] = $seller;
        }

        if(isset($id_item)){
            $where[] = " itf.id_item = ? ";
            $params[] = $id_item;
        }

        if(isset($country)){
            $where[] = " it.p_country = ? ";
            $params[] = $country;
        }

        if(isset($city)){
            $where[] = " it.p_city = ? ";
            $params[] = $city;
        }

        if(isset($status)){
            $status = getArrayFromString($status);
            $where[] = " it.status IN (" . implode(',', array_fill(0, count($status), '?')) . ") ";
            array_push($params, ...$status);
        }

        if(isset($itf_status)){
            $where[] = " itf.status = ? ";
            $params[] = $itf_status;
        }

        if(isset($auto_extend)){
            $where[] = " itf.auto_extend = ? ";
            $params[] = $auto_extend;
        }

        if(isset($paid)){
            $where[] = " itf.paid = ? ";
            $params[] = $paid;
        }

        if(isset($visible)){
            $where[] = " it.visible = ?";
            $params[] = $visible;
        }

        if(isset($blocked)){
            if($blocked == 0){
                $where[] = " it.blocked = 0 ";
            } else{
                $where[] = " it.blocked > 0 ";
            }
        }

        if(isset($main_photo)){
            $where[] = " ip.main_photo = ? ";
            $params[] = $main_photo;
            $join_photo = " LEFT JOIN item_photo ip ON ip.sale_id = it.id ";
            $select_photo = " ip.photo_name, ";
        }
        if(isset($start_from)){
            $where[] = ' DATE(itf.update_date) >= ?';
            $params[] = $start_from;
        }
        if(isset($start_to)){
            $where[] = ' DATE(itf.update_date) <= ?';
            $params[] = $start_to;
        }

        if(isset($end_from)){
            $where[] = ' DATE(itf.end_date) >= ?';
            $params[] = $end_from;
        }
        if(isset($end_to)){
            $where[] = ' DATE(itf.end_date) <= ?';
            $params[] = $end_to;
        }

        if(isset($seller_info)){
            $join_seller_info = " LEFT JOIN users u ON it.id_seller = u.idu
                                  LEFT JOIN user_groups ug ON u.user_group = ug.idgroup
                                  LEFT JOIN company_base cb ON cb.id_user = it.id_seller AND cb.type_company = 'company'";
            $select_seller_info = " CONCAT(u.fname, ' ', u.lname) as user_name, u.status as user_status,
                        ug.gr_name, cb.name_company, cb.index_name, cb.id_company, cb.type_company, ";
        }

        if(isset($keywords)){
            $order_by =  $order_by . ", REL DESC";
            $where[] = " MATCH (it.title, it.description, it.search_info) AGAINST (?)";
            $params[] = $keywords;
            $rel = " , MATCH (it.title, it.description, it.search_info) AGAINST (?) as REL";
            array_unshift($params, $keywords);
        }

        $sql .= "SELECT itf.id_featured, itf.update_date, itf.end_date, itf.status as itf_status, itf.price as itf_price, itf.paid, itf.extend as itf_extend, itf.auto_extend,
                    it.id, it.title, it.id_cat, it.create_date, it.expire_date, it.id_seller, it.p_country, it.p_city,
                    it.state, it.status, it.visible, it.featured, it.highlight, it.rating, it.changed,
                    $select_photo
                    $select_seller_info
                    cat.name AS cat_name, cat.breadcrumbs,
                    pco.country, pco.zip
                    $rel
                FROM item_featured itf
                LEFT JOIN items it ON itf.id_item = it.id
                LEFT JOIN item_category cat ON it.id_cat = cat.category_id
                LEFT JOIN port_country pco ON it.p_country = pco.id
                $join_photo
                $join_seller_info";

        if(count($where))
            $sql .= " WHERE " . implode(" AND", $where);

        $sql .= " ORDER BY " . $order_by;

        if(!isset($count))
            $count = $this->count_items($conditions);


        if(!isset($start)){
            $pages = ceil($count/$per_p);
            if ($page > $pages)
                $page = $pages;
            $start = ($page-1)*$per_p;

            if($start < 0) $start = 0;
        }

        $sql .= " LIMIT " . $start ;

        if($per_p > 0)
            $sql .= "," . $per_p;

        return $this->db->query_all($sql, $params);

    }

    public function soon_expire_feature($days){
        $sql = "SELECT fi.id_featured, fi.id_item, fi.status, i.title, DATEDIFF(end_date, NOW()) as days, i.id_seller
                FROM item_featured fi
                LEFT JOIN items i ON i.id = fi.id_item
                WHERE (DATEDIFF(end_date, NOW()) = ? OR DATEDIFF(end_date, NOW()) = -1) AND fi.status IN ('active', 'expired')";
        return $this->db->query_all($sql, array($days));
    }

    public function count_featured_items($conditions){
        $where = array();
        $params = array();
        extract($conditions);

        if(isset($categories_list)){
            $categories_list = getArrayFromString($categories_list);
            $where[] = " it.id_cat IN (" . implode(',', array_fill(0, count($categories_list), '?')) . ") ";
            array_push($params, ...$categories_list);
        }elseif(isset($category)){
            /** @var Category_Model $categoryModel */
            $categoryModel = model(Category_Model::class);

            $subcats = $categoryModel->get_cat_childrens($category) .  $category;
            $subcats = getArrayFromString($subcats);

            $where[] = " it.id_cat IN (" . implode(',', array_fill(0, count($subcats), '?')) . ") ";
            array_push($params, ...$subcats);
        }

        if(isset($id_item)){
            $where[] = " itf.id_item = ? ";
            $params[] = $id_item;
        }
        if(isset($seller)){
            $where[] = " it.id_seller = ? ";
            $params[] = $seller;
        }

        if(isset($country)){
            $where[] = " it.p_country = ? ";
            $params[] = $country;
        }

        if(isset($city)){
            $where[] = " it.p_city = ? ";
            $params[] = $city;
        }

        if(isset($status)){
            $status = getArrayFromString($status);
            $where[] = " it.status IN (" . implode(',', array_fill(0, count($status), '?')) . ") ";
            array_push($params, ...$status);
        }

        if(isset($itf_status)){
            $where[] = " itf.status = ? ";
            $params[] = $itf_status;
        }

        if(isset($auto_extend)){
            $where[] = " itf.auto_extend = ? ";
            $params[] = $auto_extend;
        }

        if(isset($paid)){
            $where[] = " itf.paid = ? ";
            $params[] = $paid;
        }

        if(isset($visible)){
            $where[] = " it.visible = ?";
            $params[] = $visible;
        }

        if(isset($start_from)){
            $where[] = ' DATE(itf.update_date) >= ?';
            $params[] = $start_from;
        }
        if(isset($start_to)){
            $where[] = ' DATE(itf.update_date) <= ?';
            $params[] = $start_to;
        }

        if(isset($end_from)){
            $where[] = ' DATE(itf.end_date) >= ?';
            $params[] = $end_from;
        }
        if(isset($end_to)){
            $where[] = ' DATE(itf.end_date) <= ?';
            $params[] = $end_to;
        }

        if(isset($keywords)){
            $order_by =  $order_by . ", REL DESC";
            $where[] = " MATCH (it.title, it.description, it.search_info) AGAINST (?)";
            $params[] = $keywords;
        }

        $sql = "SELECT COUNT(itf.id_item) as count
                FROM item_featured itf
                LEFT JOIN items it ON itf.id_item = it.id
                LEFT JOIN item_category cat ON it.id_cat = cat.category_id
                LEFT JOIN port_country pco ON it.p_country = pco.id
                LEFT JOIN users u ON it.id_seller = u.idu ";

        if(count($where))
            $sql .= " WHERE " . implode(" AND", $where);
        $rez = $this->db->query_one($sql, $params);
        return $rez['count'];
    }

    public function items_feature_expire(){
        $this->db->where('status', 'active');
        $this->db->where_raw('date_end < CURDATE()');
        return $this->db->update('item_featured',array('status'=>'expired'));
    }

    public function items_highlight_expire_by_list($list){
        $this->db->in('id_highlight', $list);
        return $this->db->update('item_highlight',array('status'=>'expired'));
    }

    public function items_feature_expire_by_list($list){
        $this->db->in('id_featured', $list);
        return $this->db->update('item_featured',array('status'=>'expired'));
    }

    public function change_items_feature_by_list($list){
        $this->db->in('id', $list);
        $this->db->update('items',array('featured'=>0));

        model('Elasticsearch_Items_Model')->index($list);
        return true;
    }

    public function change_items_highlight_by_list($list){
        $this->db->in('id', $list);
        $this->db->update('items',array('highlight'=>0));

        model('Elasticsearch_Items_Model')->index($list);
        return true;
    }

    public function change_items_estimate_by_list($list){
        $this->db->in('id', $list);
        return $this->db->update('items',array('estimate'=>0));
    }

    public function change_items_offers_by_list($list){
        $this->db->in('id', $list);
        return $this->db->update('items', array('offers' => 0));
    }

    public function delete_old_feature_by_list($list){
        $this->db->in('id_featured', $list);
        return $this->db->delete('item_featured');
    }

    public function delete_old_highlight_by_list($list){
        $this->db->in('id_highlight', $list);
        return $this->db->delete('item_highlight');
    }

    public function auto_extend_featured_items(){
        return $this->db->query('UPDATE `item_featured` SET `end_date` = DATE_ADD(end_date, INTERVAL 1 DAY) WHERE `auto_extend` = 1 AND `status` = "active"');
    }

    //HIGHLIGHT
    function get_highlight_items_last_id(){
        $sql = "SELECT id_highlight
                FROM item_highlight
                ORDER BY id_highlight DESC
                LIMIT 0,1";

        $rez = $this->db->query_one($sql);

        if(!empty($rez['id_highlight']))
            return $rez['id_highlight'];
        else
            return 0;
    }

    function get_count_new_highlight_items($id_highlight_item){
        $sql = "SELECT COUNT(*) as counter
                FROM item_highlight
                WHERE id_highlight > ? ";

        $rez = $this->db->query_one($sql, array($id_highlight_item));
        return $rez['counter'];
    }

    public function get_highlight_items($conditions){
        $page = 0;
        $per_p = 20;
        $order_by = "ith.id_highlight DESC";
        $where = array();
        $params = array();
        $multi_order_by = array();
        $join_photo = "";
        $join_seller_info = "";
        $select_photo = "";
        $seller_info = false;
        extract($conditions);

        if(!empty($sort_by)){
            foreach($sort_by as $sort_item){
                $sort_item = explode('-', $sort_item);
                $multi_order_by[] = $sort_item[0].' '.$sort_item[1];
            }

            $order_by = implode(',', $multi_order_by);
        }

        if(isset($categories_list)){
            $categories_list = getArrayFromString($categories_list);
            $where[] = " it.id_cat IN (" . implode(',', array_fill(0, count($categories_list), '?')) . ") ";
            array_push($params, ...$categories_list);
        }elseif(isset($category)){
            /** @var Category_Model $categoryModel */
            $categoryModel = model(Category_Model::class);

            $subcats = $categoryModel->get_cat_childrens($category) .  $category;
            $subcats = getArrayFromString($subcats);

            $where[] = " it.id_cat IN (" . implode(',', array_fill(0, count($subcats), '?')) . ") ";
            array_push($params, ...$subcats);
        }

        if(isset($seller)){
            $where[] = " it.id_seller = ? ";
            $params[] = $seller;
        }

        if(isset($id_item)){
            $where[] = " ith.id_item = ? ";
            $params[] = $id_item;
        }

        if(isset($country)){
            $where[] = " it.p_country = ? ";
            $params[] = $country;
        }

        if(isset($city)){
            $where[] = " it.p_city = ? ";
            $params[] = $city;
        }

        if(isset($status)){
            $status = getArrayFromString($status);
            $where[] = " it.status IN (" . implode(',', array_fill(0, count($status), '?')) . ") ";
            array_push($params, ...$status);
        }

        if(isset($ith_status)){
            $where[] = " ith.status = ? ";
            $params[] = $ith_status;
        }

        if(isset($paid)){
            $where[] = " ith.paid = ? ";
            $params[] = $paid;
        }

        if(isset($visible)){
            $where[] = " it.visible = ?";
            $params[] = $visible;
        }

        if(isset($blocked)){
            if($blocked == 0){
                $where[] = " it.blocked = 0 ";
            } else{
                $where[] = " it.blocked > 0 ";
            }
        }

        if(isset($main_photo)){
            $where[] = " ip.main_photo = ? ";
            $params[] = $main_photo;
            $join_photo = " LEFT JOIN item_photo ip ON ip.sale_id = it.id ";
            $select_photo = " ip.photo_name, ";
        }
        if(isset($start_from)){
            $where[] = ' DATE(ith.update_date) >= ?';
            $params[] = $start_from;
        }
        if(isset($start_to)){
            $where[] = ' DATE(ith.update_date) <= ?';
            $params[] = $start_to;
        }

        if(isset($end_from)){
            $where[] = ' DATE(ith.end_date) >= ?';
            $params[] = $end_from;
        }
        if(isset($end_to)){
            $where[] = ' DATE(ith.end_date) <= ?';
            $params[] = $end_to;
        }

        if(isset($seller_info)){
            $join_seller_info = " LEFT JOIN users u ON it.id_seller = u.idu
                                  LEFT JOIN user_groups ug ON u.user_group = ug.idgroup
                                  LEFT JOIN company_base cb ON cb.id_user = it.id_seller AND cb.type_company = 'company'";
            $select_seller_info = " CONCAT(u.fname, ' ', u.lname) as user_name, u.status as user_status,
                        ug.gr_name, cb.name_company, cb.index_name, cb.id_company, cb.type_company, ";
        }

        if(isset($keywords)){
            $order_by =  $order_by . ", REL DESC";
            $where[] = " MATCH (it.title, it.description, it.search_info) AGAINST (?)";
            $params[] = $keywords;
            $rel = " , MATCH (it.title, it.description, it.search_info) AGAINST (?) as REL";
            array_unshift($params, $keywords);
        }

        $sql .= "SELECT ith.id_highlight, ith.update_date, ith.end_date, ith.status as ith_status, ith.price as itf_price, ith.paid,
                    it.id, it.title, it.id_cat, it.create_date, it.expire_date, it.id_seller, it.p_country, it.p_city,
                    it.state, it.status, it.visible, it.featured, it.highlight, it.rating, it.changed,
                    $select_photo
                    $select_seller_info
                    cat.name AS cat_name, cat.breadcrumbs,
                    pco.country, pco.zip
                    $rel
                FROM item_highlight ith
                LEFT JOIN items it ON ith.id_item = it.id
                LEFT JOIN item_category cat ON it.id_cat = cat.category_id
                LEFT JOIN port_country pco ON it.p_country = pco.id
                $join_photo
                $join_seller_info";

        if(count($where))
            $sql .= " WHERE " . implode(" AND", $where);

        $sql .= " ORDER BY " . $order_by;

        if(!isset($count))
            $count = $this->count_items($conditions);


        if(!isset($start)){
            $pages = ceil($count/$per_p);
            if ($page > $pages)
                $page = $pages;
            $start = ($page-1)*$per_p;

            if($start < 0) $start = 0;
        }

        $sql .= " LIMIT " . $start ;

        if(isset($limit))
            $per_p = $limit;

        if($per_p > 0)
            $sql .= "," . $per_p;

        return $this->db->query_all($sql, $params);

    }

    public function count_highlight_items($conditions){
        $where = array();
        $params = array();
        extract($conditions);

        if(isset($categories_list)){
            $categories_list = getArrayFromString($categories_list);
            $where[] = " it.id_cat IN (" . implode(',', array_fill(0, count($categories_list), '?')) . ") ";
            array_push($params, ...$categories_list);
        }elseif(isset($category)){
            /** @var Category_Model $categoryModel */
            $categoryModel = model(Category_Model::class);

            $subcats = $categoryModel->get_cat_childrens($category) .  $category;
            $subcats = getArrayFromString($subcats);

            $where[] = " it.id_cat IN (" . implode(',', array_fill(0, count($subcats), '?')) . ") ";
            array_push($params, ...$subcats);
        }

        if(isset($id_item)){
            $where[] = " ith.id_item = ? ";
            $params[] = $id_item;
        }
        if(isset($seller)){
            $where[] = " it.id_seller = ? ";
            $params[] = $seller;
        }

        if(isset($country)){
            $where[] = " it.p_country = ? ";
            $params[] = $country;
        }

        if(isset($city)){
            $where[] = " it.p_city = ? ";
            $params[] = $city;
        }

        if(isset($status)){
            $status = getArrayFromString($status);
            $where[] = " it.status IN (" . implode(',', array_fill(0, count($status), '?')) . ") ";
            array_push($params, ...$status);
        }

        if(isset($ith_status)){
            $where[] = " ith.status = ? ";
            $params[] = $ith_status;
        }

        if(isset($paid)){
            $where[] = " ith.paid = ? ";
            $params[] = $paid;
        }

        if(isset($visible)){
            $where[] = " it.visible = ?";
            $params[] = $visible;
        }

        if(isset($start_from)){
            $where[] = ' DATE(ith.update_date) >= ?';
            $params[] = $start_from;
        }
        if(isset($start_to)){
            $where[] = ' DATE(ith.update_date) <= ?';
            $params[] = $start_to;
        }

        if(isset($end_from)){
            $where[] = ' DATE(ith.end_date) >= ?';
            $params[] = $end_from;
        }

        if(isset($end_to)){
            $where[] = ' DATE(ith.end_date) <= ?';
            $params[] = $end_to;
        }

        if(isset($keywords)){
            $order_by =  $order_by . ", REL DESC";
            $where[] = " MATCH (it.title, it.description, it.search_info) AGAINST (?)";
            $params[] = $keywords;
        }

        $sql = "SELECT COUNT(ith.id_item) as count
                FROM item_highlight ith
                LEFT JOIN items it ON ith.id_item = it.id
                LEFT JOIN item_category cat ON it.id_cat = cat.category_id
                LEFT JOIN port_country pco ON it.p_country = pco.id
                LEFT JOIN users u ON it.id_seller = u.idu ";

        if(count($where))
            $sql .= " WHERE " . implode(" AND", $where);
        $rez = $this->db->query_one($sql, $params);
        return $rez['count'];
    }

    public function soon_expire_hightlight($days){
        $sql = "SELECT i_h.id_highlight, i_h.id_item, i_h.status, i.title, i.id_seller, DATEDIFF(end_date, NOW()) as days
                FROM item_highlight i_h
                LEFT JOIN items i ON i.id = i_h.id_item
                WHERE (DATEDIFF(end_date, NOW()) <= ? OR DATEDIFF(end_date, NOW()) = -1) AND i_h.`status` IN ('active', 'expired')";
        return $this->db->query_all($sql, array($days));
    }

    //ITEMS
    public function get_items_full_info($conditions){
        $where = array();
        $params = array();

        extract($conditions);

		if(isset($accreditation)){
            $where[] = " cb.accreditation = ? ";
            $params[] = $accreditation;
        }

        if(isset($items_list)){
            $itemsList = getArrayFromString($items_list);
            $where[] = " it.id IN (" . implode(',', array_fill(0, count($itemsList), '?')) . ")";
            array_push($params, ...$itemsList);
        }

        if(isset($visible)){
            $where[] = " it.visible = ?";
            $params[] = $visible;
        }

        $sql = "SELECT it.* , ip.photo_name,
                        curr.code AS curr_code, curr.curr_entity as curr_entity,
                        ut.unit_name,CONCAT(u.fname, ' ', u.lname) as user_name,
                        ug.gr_name, cb.name_company, cb.index_name, cb.id_company, cb.type_company, cb.rating_company, pc.country as company_country
                FROM items it
                LEFT JOIN currency curr ON it.currency = curr.id
                LEFT JOIN unit_type ut ON it.unit_type = ut.id
                LEFT JOIN item_photo ip ON it.id = ip.sale_id AND ip.main_photo = 1
                LEFT JOIN users u ON it.id_seller = u.idu
                LEFT JOIN user_groups ug ON u.user_group = ug.idgroup
                LEFT JOIN company_base cb ON cb.id_user = it.id_seller
                LEFT JOIN port_country pc ON pc.id = cb.id_country ";

        if(count($where))
            $sql .= " WHERE " . implode(" AND", $where);
        $sql .= " GROUP BY it.id";

        return $this->db->query_all($sql, $params);
    }

    public function check_item($id){
        $sql = "SELECT COUNT(*) as counter
                FROM items
                WHERE id = ?";
        $rez = $this->db->query_one($sql, array($id));
        return $rez['counter'];
    }

    public function count_feature_request($conditions=array()){
        $where = array();
        $params = array();

        extract($conditions);

        if(isset($item)){
            $where[] = " id_item = ? ";
            $params[] = $item;
        }

        if(!empty($status_list) && is_array($status_list)){
            $where[] = " status IN (" . implode(',', array_fill(0, count($status_list), '?')) . ") ";
            array_push($params, $status_list);
        }

        $sql = "SELECT COUNT(*) as counter
                FROM item_featured ";

        if(!empty($where)){
            $sql .= " WHERE " . implode(" AND", $where);
        }

        $rez = $this->db->query_one($sql, $params);
        return $rez['counter'];
    }
    public function count_highlight_request($conditions=array()){
        $where = array();
        $params = array();

        extract($conditions);

        if(isset($item)){
            $where[] = " id_item = ? ";
            $params[] = $item;
        }

        if(!empty($status_list) && is_array($status_list)){
            $where[] = " status IN (" . implode(',', array_fill(0, count($status_list), '?')) . ") ";
            array_push($params, $status_list);
        }

        $sql = "SELECT COUNT(*) as counter
                FROM item_highlight ";

        if(count($where))
            $sql .= " WHERE " . implode(" AND", $where);

        $rez = $this->db->query_one($sql, $params);
        return $rez['counter'];
    }

    /**
     * select all childrens category from all levels
     * $cat - id of the parent category
    */
    public function get_cat_childrens($cat){
        $sql = "SELECT category_id FROM item_category WHERE parent = ?";
        $rez = $this->db->query_all($sql, array($cat));
        $childrens = "";
        if(count($rez) > 0){
            foreach($rez as $child){
                $childrens .= $child['category_id'] . ", ";
                $childrens .= $this->get_cat_childrens($child['category_id']);
            }
        }
        return $childrens;
    }

	public function update_items_same_data($conditions = array(), $update = array()){
        extract($conditions);

        if(empty($items_list)){
            return false;
        }

        $this->db->in('id', $items_list);

        return $this->db->update("items", $update);
	}

    public function insert_item($data) {
        /** @var User_Statistic_Model $userStatisticModel */
        $userStatisticModel = model(User_Statistic_Model::class);

        $userStatisticModel->set_users_statistic(array($data['id_seller'] => array('items_total' => 1, 'items_active' => 1)));
        $this->db->insert('items', $data);
        return $this->db->last_insert_id();
    }

    public function insert_many_items(?array $data = null, int $seller_id = null)
    {
        if(empty($data)){
            return;
        }

        $is_inserted = $this->db->insert_batch('items', $data);
        if (!$is_inserted) {
            return false;
        }
        $total_inserted = $this->db->affectedRows();
        if (null !== $seller_id) {
            model(User_Statistic_Model::class)->set_users_statistic(array($seller_id => array('items_total' => $total_inserted)));
        }

        return $total_inserted;
    }

    public function delete_item($id_item = 0){
        $this->db->where("id", $id_item);
        return $this->db->delete('items');
    }

    public function deleteExpiredDraftItems()
    {
        $this->db->where_raw('draft_expire_date = CURDATE()');
        $this->db->where('draft', 1);
        return $this->db->delete('items');
    }

    public function set_feature_request($data) {
        if($this->db->insert('item_featured', $data))
            return $this->db->last_insert_id();
        return false;
    }

    public function update_feature_request($id, $data) {
        $this->db->where('id_featured', $id);
        return $this->db->update('item_featured', $data);
    }

    public function set_highlight_request($data) {
        if( $this->db->insert('item_highlight', $data))
            return $this->db->last_insert_id();
        return false;
    }

    public function sell_item($id, $q){
        $sql = "UPDATE items SET quantity = quantity - ? WHERE id = ?";
        return $this->db->query($sql, [$q, $id]);
    }

    public function soldCounter($id_item){
        $sql = "SELECT SUM(quantity_ordered) as counter
                FROM item_ordered
                WHERE id_item = ?"; //status 11 = end order
        $rez = $this->db->query_one($sql, array($id_item));

        return $rez['counter'] ?: 0;
    }

    public function update_item($info){
        $this->db->where('id', $info['id']);
        return $this->db->update('items', $info);
    }

    public function update_items($info){
        return $this->db->update('items', $info);
    }

    public function get_item_photo($id_photo){
        $sql = "SELECT *
                FROM item_photo
                WHERE id = ?";
        return $this->db->query_one($sql, array($id_photo));
    }

    public function get_item_main_photo($id_item){
        $sql = "SELECT *
                FROM item_photo
                WHERE sale_id = ? AND main_photo = 1";
        return $this->db->query_one($sql, array($id_item));
    }

    /**
     * Get all photos of a item, by default ordered by main photo (means it will be the first in the list)
     *
     * @param int $idItem - the id of the item
     * @param int|null $numb - limit number (null by default)
     * @param string $order - what to order by
     *
     * @return array
     */
    public function get_items_photo($idItem, $numb =  null, $order = 'main_photo DESC')
    {
        $this->db->select('*');
        $this->db->from('item_photo');
        $this->db->where('sale_id', $idItem);
        $this->db->orderby($order);

        if(!is_null($numb)){
            $this->db->limit($numb);
        }

        return $this->db->query_all();
    }

    public function count_items_photo($id_item, $params = array()){
        $this->db->select("COUNT(*) as `counter`");
        $this->db->from("item_photo");
        $this->db->where("sale_id = ?", $id_item);

        extract($params);

        if(isset($is_main)){
            $this->db->where("main_photo = ?", (int) $is_main);
        }

        $result = $this->db->get_one();

        return (int) $result['counter'];
    }

    public function count_photo($conditions){
        extract($conditions);
        $params = [];

        if(isset($items_list)) {
            $itemsList = getArrayFromString($items_list);
            $where[] = " sale_id IN (" . implode(',', array_fill(0, count($itemsList), '?')) . ")";
            array_push($params, ...$itemsList);
        }

        $sql = "SELECT *, COUNT(*) as counter
                FROM item_photo";
        if(count($where))
            $sql .= " WHERE " . implode(' AND ', $where);

        $sql .= " GROUP BY sale_id";
        return $this->db->query_all($sql, $params);
    }

    /**
     * Get the photos for items.
     *
     * @param array $conditions
     *
     * @return array<int,array<string,mixed>>
     *
     * @deprecated in favor of Items_Model::getItemsPhotos()
     * @see Items_Model::getItemsPhotos()
     * @uses Items_Model::getItemsPhotos()
     */
    public function items_main_photo($conditions)
    {
        $onlyMain = $conditions['main_photo'] ?? false;
        $itemsList = $conditions['items_list'] ?? [];

        return $this->getItemsPhotos(
            getArrayFromString($itemsList),
            $onlyMain
        );
    }

    /**
     * Get the photos for items.
     *
     * @param int[]|string[] $itemsIds
     *
     * @return array<int,array<string,mixed>>
     */
    public function getItemsPhotos(array $itemsIds = [], bool $onlyMain = false): array
    {
        $query = $this->createQueryBuilder()
            ->select('sale_id', 'photo_name', 'photo_thumbs')
            ->from('item_photo')
        ;

        if ($onlyMain) {
            $query->andWhere(
                $query->expr()->eq('main_photo', 1)
            );
        }

        if (isset($itemsIds)) {
            $query->andWhere(
                $query->expr()->in(
                    'sale_id',
                    array_map(
                        fn (int $index, $user) => $query->createNamedParameter(
                            (int) $user,
                            ParameterType::INTEGER,
                            $this->nameScopeParameter("itemId{$index}")
                        ),
                        range(0, count($itemsIds)),
                        $itemsIds
                    )
                )
            );
        }

        return $query->execute()->fetchAllAssociative();
    }

    public function set_item_photos($data){
		$this->db->insert_batch('item_photo', $data);
		return array('inserted_rows' => $this->db->getAffectableRowsAmount(), 'first_id' => $this->db->last_insert_id());
	}

    function set_item_photo($data){
        $this->db->insert('item_photo', $data);
        return $this->db->last_insert_id();
    }

    function update_item_photo($id_item, $data){
        $this->db->where('sale_id', $id_item);
        return $this->db->update('item_photo', $data);
    }

    function update_photo_thumbs($id_photo, $data){
        $this->db->where('id', $id_photo);
        return $this->db->update('item_photo', $data);
    }

    function set_multi_photo($data){
		if(empty($data)){
			return 0;
		}

        $this->db->insert_batch('item_photo', $data);
        return $this->db->getAffectableRowsAmount();
    }

    function set_items_photo($data){
        $params = [];

        $sql = "INSERT INTO item_photo
        (`sale_id`, `photo_name`, `photo_thumbs`, `main_photo`) VALUES ";

        foreach ($data as $item) {
            $photos[] = "(?, ?, ?, ?)";
            array_push($params, $item['sale_id'], $item['photo_name'], $item['photo_thumbs'], $item['main_photo']);
        }

        $sql .= implode(',', $photos);

        $this->db->query($sql, $params);
        return $this->db->last_insert_id();
    }

    public function update_main_photo($id_item, $mainPath) {
        return $this->db->query("UPDATE item_photo SET main_photo = IF(photo_name = ?, 1, 0) WHERE sale_id = ?", [$mainPath, $id_item]);
    }

    public function delete_item_photo($id_item, $id_photo){
        $this->db->where("sale_id = ? AND id = ?", [$id_item, $id_photo]);
        return $this->db->delete('item_photo');
    }

    public function delete_item_photos($id_item){
        $this->db->where("sale_id", $id_item);
        return $this->db->delete('item_photo');
    }

    /* operations with attributes */
    public function insert_user_attr($id_item, $data){
        $params = [];
        $attr_names = $data['name'];
        $attr_vals = $data['val'];
        $attrs = array();
        foreach($attr_names as $key => $name){
            if(!empty($name) && !empty($attr_vals[$key])){
                $attributes[$name] = $attr_vals[$key];
            }
        }

        $sql = "INSERT INTO item_user_attr
        (`id_item`, `p_name`, `p_value`) VALUES ";

        foreach($attributes as $n => $val){
            if (!empty($n) && !empty($val)) {
                $attrs[] = "(?, ?, ?')";
                array_push($params, $id_item, filter($n), filter($val));
            }
        }

        if (empty($attrs)) {
            return false;
        }

        $sql .= implode(',', $attrs);
        $this->db->query($sql, $params);
        return $this->db->last_insert_id();
    }

    public function insert_item_user_attr_batch($data){
        $this->db->insert_batch('item_user_attr', $data);
        return $this->db->getAffectableRowsAmount();
    }

    public function update_user_attr($update_info){
        $this->db->where('id', $update_info['id']);
        return $this->db->update('item_user_attr', $update_info);
    }

    public function insert_attr($id_item, $data){
        $sql = "INSERT INTO item_attr (`item`, `attr`, `attr_value`) VALUES ";
        $params = [];

        foreach ($data as $attr => $values) {
            if (is_array($values)) {
                foreach ($values  as $value) {
                    $insert[] = " (?, ?, ?) ";
                    array_push($params, $id_item, $attr, $value);
                }
            } else {
                $insert[] = " (?, ?, ?) ";
                array_push($params, $id_item, $attr, $values);
            }
        }

        if(!count($insert)){
            return false;
        }
        $sql .= implode(', ', $insert);
        $this->db->query($sql, $params);
        return $this->db->last_insert_id();
    }

    public function insert_item_attr_batch($data){
        $this->db->insert_batch('item_attr', $data);
        return $this->db->getAffectableRowsAmount();
    }

    public function update_attr($id_item,$data){
        foreach($data as $id_attr => $value){
            $this->update_cat_attr(array('item' => $id_item,'attr' => $id_attr, 'attr_value' => $value));
        }
    }

    public function update_multiselect_attr($id_item, $data){
        foreach($data as $id_msattr => $ms_value){
            $this->renew_cat_attr($id_item, $id_msattr, $ms_value);
        }
    }

    public function update_cat_attr($update_info){
        $this->db->where('item', $update_info['item']);
        $this->db->where('attr', $update_info['attr']);
        return $this->db->update('item_attr', $update_info);
    }

    public function renew_cat_attr($item, $attr, $new_values){
        $this->delete_cat_attr($item, $attr);
        $params = [];

        $sql = "INSERT INTO item_attr (item, attr, attr_value) VALUES ";
        foreach ($new_values as $value) {
            $insert[] = "(?, ?, ?)";
            array_push($params, $item, $attr, $value);
        }

        if (empty($insert)) {
            return false;
        }

        $sql .= implode(', ', $insert);
        $this->db->query($sql, $params);
        $this->db->last_insert_id();
    }

    public function delete_cat_attr($id_item, $attr){
        $this->db->where('item', $id_item);
        $this->db->where('attr', $attr);
        $this->db->delete('item_attr');
    }

    public function delete_cat_attr_by_id($id_attr){
        $this->db->where('id_attr', $id_attr);
        $this->db->delete('item_attr');
    }

    public function delete_cat_attr_by_item($id_item){
        $this->db->where('item', $id_item);
        $this->db->delete('item_attr');
    }

    public function get_cat_attrs($id_item){
        $sql = "SELECT *
                FROM item_attr
                WHERE item = ?";
        return $this->db->query_all($sql, array($id_item));
    }

    public function get_cat_attrs_full($id_item){
        $sql = "SELECT ia.*, ica.attr_name, ica.attr_type
                FROM item_attr ia
                INNER JOIN item_cat_attr ica ON ia.attr = ica.id
                WHERE item = ?";
        return $this->db->query_all($sql, array($id_item));
    }

    public function exist_cat_attr($conditions){
        $where = array();
        $params = array();

        extract($conditions);

        if(isset($id_item)){
            $where[] = " item = ? ";
            $params[] = $id_item;
        }

        if(isset($id_attr)){
            $where[] = " id_attr = ? ";
            $params[] = $id_attr;
        }

        $sql = "SELECT COUNT(*) as counter
                FROM item_attr ";

        if(count($where))
            $sql .= " WHERE " . implode(" AND", $where);

        $rez = $this->db->query_one($sql, $params);
        return $rez['counter'];
    }

    public function exist_user_attr($conditions){
        $where = array();
        $params = array();

        extract($conditions);

        if(isset($id_attr)){
            $where[] = " id = ? ";
            $params[] = $id_attr;
        }

        if(isset($id_item)){
            $where[] = " id_item = ? ";
            $params[] = $id_item;
        }

        $sql = "SELECT COUNT(*) as counter
                FROM item_user_attr ";

        if(count($where))
            $sql .= " WHERE " . implode(" AND", $where);

        $rez = $this->db->query_one($sql, $params);
        return $rez['counter'];
    }

    public function get_user_attrs($id_item){
        $sql = "SELECT * FROM item_user_attr WHERE id_item = ? ORDER BY id";
        return $this->db->query_all($sql, array($id_item));
    }

    public function delete_user_attrs($id_item, $id_attr){
        $this->db->where('id_item = ? AND id = ?', array($id_item, $id_attr));
        return $this->db->delete('item_user_attr');
    }

    public function delete_user_attrs_by_item($id_item){
        $this->db->where('id_item', $id_item);
        return $this->db->delete('item_user_attr');
    }

    public function set_vin_info($vin_info){
        $this->db->insert('vin_info', $vin_info);
        return $this->db->last_insert_id();
    }

    public function get_vin($id_item){
        $sql = "SELECT * FROM vin_info WHERE id_motor = ?";
        return $this->db->query_one($sql, array($id_item));
    }

    public function get_vin_info($id_item){
        $info = array();
        $sql = "SELECT * FROM vin_info WHERE id_motor = ?";
        $str = $this->db->query_one($sql, array($id_item));
        return json_decode($str['vin_info'], true);
    }

    public function get_vin_info_simple($id_item){
        $sql = "SELECT *
                FROM vin_info
                WHERE id_motor = ?";
        return $this->db->query_one($sql, array($id_item));
    }

    public function update_vin_info($id_item, $data){
        $this->db->where('id_motor', $id_item);
        return $this->db->update("vin_info", $data);
    }

    public function delete_vin($id_item){
        $this->db->where('id_motor', $id_item);
        return $this->db->delete('vin_info');
    }

    public function control_vin($vin_numb){
        $sql = "SELECT COUNT(*) as vin_exist
                FROM vin_info
                WHERE vin_numb = ?";
        $rez = $this->db->query_one($sql, array($vin_numb));
        return $rez['vin_exist'];
    }

    /**
    * functions for saved items
    */
    public function setSavedItem($id_user, $id_item){
        /** @var User_Statistic_Model $userStatisticModel */
        $userStatisticModel = model(User_Statistic_Model::class);

        $userStatisticModel->set_users_statistic(array($id_user => array('items_saved' => 1)));
        $insert = array(
                'id_user' => $id_user,
                'id_item' => $id_item
        );
        $this->db->insert('user_saved_items', $insert);

        return $this->db->last_insert_id();
    }

    public function updateSavedItem($id_save, $data){
        $this->db->where('id_save', $id_save);
        return $this->db->update("user_saved_items", $data);
    }

    public function deleteSavedItem($id_save){
        $this->db->where('id_save', $id_save);
        return $this->db->delete('user_saved_items');
    }

    public function deleteSavedItemByUser($id_user, $id_item){
        /** @var User_Statistic_Model $userStatisticModel */
        $userStatisticModel = model(User_Statistic_Model::class);
        $userStatisticModel->set_users_statistic(array($id_user => array('items_saved' => -1)));

        $this->db->where('id_user = ?', [$id_user]);
        $this->db->where('id_item = ?', [$id_item]);
        return $this->db->delete('user_saved_items');
    }

    public function getSavedCounter($id_user){
            $sql= "SELECT COUNT(*) as counter
                    FROM user_saved_items
                    WHERE id_user = ?";
            $rez = $this->db->query_one($sql, array($id_user));
            return $rez['counter'];
    }

    public function getSavedByUser($id_user, $conditions){
        extract($conditions);

        $page = (int) ($page ?? 1);
        $perPage = (int) ($per_p ?? 3);

        $sql = "SELECT it.title, it.price, it.final_price, it.discount, it.rating, it.views, it.total_sold, it.rev_numb, it.has_variants, curr.curr_entity,
                        us.* , ip.photo_name, count(ir.id_review) as counter_review,
                        CONCAT(u.fname, ' ', u.lname) as user_name,
                        pc.country as country_name
                FROM user_saved_items  us
                LEFT JOIN item_photo ip ON us.id_item = ip.sale_id AND ip.main_photo = 1
                LEFT JOIN item_reviews ir ON us.id_item = ir.id_item
                LEFT JOIN items it ON us.id_item = it.id
                LEFT JOIN users u ON it.id_seller = u.idu
                LEFT JOIN currency curr ON it.currency = curr.id
                LEFT JOIN port_country pc ON it.p_country = pc.id
                WHERE us.id_user = ?
                GROUP BY us.id_item
                ORDER BY it.title ";

        $sql .= " LIMIT " . ($page - 1) * $perPage . ", " . $perPage;

        return $this->db->query_all($sql, [$id_user]);
    }

    public function iSaveIt($id_user, $id_item){
            $sql = "SELECT COUNT(*) as counter
                    FROM user_saved_items
                    WHERE id_user = ?
                    AND id_item = ?";
            $rez = $this->db->query_one($sql, array($id_user, $id_item));
            return $rez['counter'];
    }

    function get_items_saved($id_user){
        $sql = "SELECT GROUP_CONCAT(id_item) as id_saved
            FROM user_saved_items
            WHERE id_user = ?";

        $rez = $this->db->query_one($sql, [$id_user]);

        return $rez['id_saved'];
    }

    /**
    * end functions for saved items
    */

    /**
    * functions for featured items
    */
    public function setFetauredItem($set){
        return $this->db->insert('item_featured', $set);
    }

    public function get_featured($id){
        $sql = "SELECT * FROM item_featured WHERE id_featured = ?";
        return $this->db->query_one($sql, array($id));
    }

    public function get_highlight($id){
        $sql = "SELECT * FROM item_highlight WHERE id_highlight = ?";
        return $this->db->query_one($sql, array($id));
    }

    public function updateFeaturedItem($id_featured, $data){
        $this->db->where('id_featured', $id_featured);

        return $this->db->update('item_featured', $data);
    }

    public function deleteFetauredItem($id_item){
        $this->db->where('id_item', $id_item);
        return $this->db->delete('item_featured');
    }

    public function isFeatured($id_item){
            $sql = "SELECT COUNT(*) as featured
                            FROM item_featured
                            WHERE status = 'active'
                            AND paid = 1
                            AND id_item = ?";
            $res = $this->db->query_one($sql, array($id_item));
            return $res['featured'];
    }

    public function isInitFeatured($id_item){
            $sql = "SELECT COUNT(*) as featured
                            FROM item_featured
                            WHERE status = 'init'
                            AND id_item = ?";
            $res = $this->db->query_one($sql, array($id_item));
            return $res['featured'];
    }

    public function isInitHighlight($id_item){
            $sql = "SELECT COUNT(*) as highlight
                            FROM item_highlight
                            WHERE status = 'init'
                            AND id_item = ?";
            $res = $this->db->query_one($sql, array($id_item));
            return $res['highlight'];
    }

    public function getFeaturedStatus($id_item){
            $sql = "SELECT status
                            FROM item_featured
                            WHERE id_item = ?";
            $rez = $this->db->query_one($sql, array($id_item));
            return $rez['status'];
    }

    public function getFeaturedRemainsDays($id_item){
        $sql = "SELECT end_date
                FROM item_featured
                WHERE id_item = ? AND status = 'active' ";
        $rez = $this->db->query_one($sql, array($id_item));

        if (empty($rez)) {
            return 0;
        }

        $diff = strtotime($rez['end_date']) - strtotime(date('Y-m-d'));

        return $diff < 0 ? 0 : $diff/86400;
    }

    public function get_featured_remains_days($id_featured){
        $sql = "SELECT end_date
                FROM item_featured
                WHERE id_featured = ?";
        $rez = $this->db->query_one($sql, array($id_featured));

        if (empty($rez)) {
            return 0;
        }

        $diff = strtotime($rez['end_date']) - strtotime(date('Y-m-d'));

        return $diff < 0 ? 0 : $diff/86400;
    }

    public function getHighlightedRemainsDays($id_item){
        $sql = "SELECT end_date
                FROM item_highlight
                WHERE id_item = ? AND status='active' ";
        $rez = $this->db->query_one($sql, array($id_item));

        if (empty($rez)) {
            return 0;
        }

        $diff = strtotime($rez['end_date']) - strtotime(date('Y-m-d'));

        return $diff < 0 ? 0 : $diff/86400;
    }

    public function get_highlighted_remains_days($id_highlight){
        $sql = "SELECT end_date
                FROM item_highlight
                WHERE id_highlight = ? ";
        $rez = $this->db->query_one($sql, array($id_highlight));

        if (empty($rez)) {
            return 0;
        }

        $diff = strtotime($rez['end_date']) - strtotime(date('Y-m-d'));

        return $diff < 0 ? 0 : $diff/86400;
    }

    public function counterFeaturedByStatus($status){
        $sql = "SELECT COUNT(*) as counter
                        FROM item_featured
                        WHERE status = ?";
        $rez = $this->db->query_one($sql, array($status));
        return $rez['counter'];
    }

    public function searchFeatured($conditions){
        $status = '';
        $order_by = "update_date ";
        $function_type = 'all'; //also can be count(count by all conditions), page(numebr of page by all conditions)
        extract($conditions);

        $where = $params = [];

        if (!empty($status)) {
            $where[] = " ift.status = ? ";
            $params[] = $status;
        }

        if(!empty($keywords)){
            $words = explode(" ", $keywords);
            foreach($words as $word){
                if (strlen($word) > 3) {
                    $s_word[] = " (fname LIKE ? OR lname LIKE ? OR it.title LIKE ? OR ic.name LIKE ?)";
                    array_push($params, ...array_fill(0, 4, '%' . $word . '%'));
                }
            }

            $where[] = " (". implode(" AND ", $s_word).")";
        }

        $sql = "SELECT ift.*,
                                CONCAT(us.fname, ' ', us.lname) as fullname, us.status as user_status, us.logged,
                                it.title, it.id_seller, ic.name, ic.p_or_m
        FROM item_featured ift
                        INNER JOIN items it ON ift.id_item = it.id
                        INNER JOIN item_category ic ON it.id_cat = ic.category_id
                        INNER JOIN users us ON it.id_seller = us.idu ";
        if(count($where)) $sql .= " WHERE " . implode(" AND", $where);

        $res = $this->db->query_all($sql, $params);

        $count = $this->db->numRows();

        /* block for count all items */
        if($function_type == 'count')
            return $count;

        /* block for count pagination */
        $pages = ceil($count/$per_p);
        if($function_type == 'pages')
            return $pages;

        if(!empty($order)){
            $str = explode("_",$order);
            $ord = $str[0];
            switch($ord){
                case 'name': $order_by = " us.fname"; break;
                case 'item': $order_by = " it.title"; break;
                case 'update': $order_by = " update_date"; break;
                case 'enddate': $order_by = " end_date"; break;
            }
            $order_by .= " " . $str[1];
        }
        $sql .= " ORDER BY ".$order_by;

        if($page > $pages)
            $page = $pages;
        $start = ($page-1)*$per_p;
        if($start < 0)
            $start = 0;

        $sql .=  " LIMIT " . $start . "," . $per_p;

        return  $this->db->query_all($sql, $params);
    }


    /**
    * functions for hightlight items
    */
    public function setHightlight($set){
        return $this->db->insert('item_highlight', $set);
    }

    public function updateHightlight($id_item, $data, $aditional_cond = array()){
        extract($aditional_cond);
        if(isset($status))
            $this->db->where('status', $status);
        $this->db->where('id_item', $id_item);
        return $this->db->update("item_highlight", $data);
    }

    public function deleteHightlight($id_item){
        $this->db->where('id_item', $id_item);
        return $this->db->delete('item_highlight');
    }

    public function counterHightlightByStatus($status){
            $sql = "SELECT COUNT(id_item) as counter
                            FROM item_highlight
                            WHERE status = ?";
            $rez = $this->db->query_one($sql, array($status));
            return $rez['counter'];
    }

    public function searchHightlight($conditions){
        $status = '';
        $order_by = "update_date ";
        $where = array();
        $params = array();

        $function_type = 'all'; //also can be count(count by all conditions), page(numebr of page by all conditions)
        extract($conditions);


        if(!empty($status)){
            $where[] = " ih.status = ? ";
            $params[] = $status;
        }
        if(!empty($keywords)){
            $words = explode(" ", $keywords);
            foreach($words as $word){
                if (strlen($word) > 3) {
                    $s_word[] = " (fname LIKE ? OR lname LIKE ? OR it.title LIKE ? OR ic.name LIKE ?)";
                    array_push($params, ...array_fill(0, 4, '%' . $word . '%'));
                }
            }

            if (!empty($s_word)) {
                $where[] = " (". implode(" AND ", $s_word).")";
            }
        }

        $sql = "SELECT ih.*,
                                CONCAT(us.fname, ' ', us.lname) as fullname, us.status as user_status, us.logged,
                                it.title, it.id_seller, ic.name, ic.p_or_m
        FROM item_highlight ih
                        INNER JOIN items it ON ih.id_item = it.id
                        INNER JOIN item_category ic ON it.id_cat = ic.category_id
                        INNER JOIN users us ON it.id_seller = us.idu ";
        if(count($where)) $sql .= " WHERE " . implode(" AND", $where);

        $res = $this->db->query_all($sql, $params);

        $count = $this->db->numRows();

        /* block for count all items */
        if($function_type == 'count')
            return $count;

        /* block for count pagination */
        $pages = ceil($count/$per_p);
        if($function_type == 'pages')
            return $pages;

        if(!empty($order)){
            $str = explode("_",$order);
            $ord = $str[0];
            switch($ord){
                case 'name': $order_by = " us.fname"; break;
                case 'item': $order_by = " it.title"; break;
                case 'update': $order_by = " update_date"; break;
                case 'enddate': $order_by = " end_date"; break;
            }
            $order_by .= " " . $str[1];
        }
        $sql .= " ORDER BY ".$order_by;

        if($page > $pages)
            $page = $pages;
        $start = ($page-1)*$per_p;
        if($start < 0)
            $start = 0;

        $sql .=  " LIMIT " . $start . "," . $per_p;

        return  $this->db->query_all($sql, $params);
    }

    /**
     * Method for geting the last viewed items for user (either logged or not)
     *
     * @param int|string $idUser - the id of the user that is logged or the unique id from cookie for not logged
     * @param int $idItem - the id of the current viewed item
     * @param bool $logged - is the user logged or not
     * @param int $limit - how many items to return (by default 10)
     *
     * @return array
     */
    function getItemsForLastViewed($idUser, $idItem, $logged = true, $limit = 10)
    {
        $this->db->select('i.id, i.title, i.price, i.final_price, i.discount, i.rating, i.rev_numb, i.views, ip.photo_name, cat.p_or_m, lv.date_updated');
        $this->db->from('items i');
        $this->db->join('item_category cat', 'i.id_cat = cat.category_id', 'inner');
        $this->db->join('item_photo ip', 'i.id = ip.sale_id AND ip.main_photo = 1', 'left');
        $this->db->join('last_viewed_items lv', 'lv.id_item=i.id', 'left');
        $this->db->where('lv.id_item !=', $idItem);
        $this->db->where($logged ? 'id_user' : 'id_not_logged', $idUser);
        $this->db->orderby('lv.date_updated DESC');
        $this->db->limit($limit);

        $items = $this->db->query_all();

        return array_column($items, NULL, 'id');
    }

    /**
     * Insert the newly viewed item into table
     *
     * @param array $data - data to insert
     *
     * @return string
     */
    function insertLastViewed($data)
    {
        return $this->db->insert(
            'last_viewed_items',
            $data
        );
    }

    /**
     * Verifies if the items already exists in the last viewed
     *
     * @param string|null - the id of the not logged user (if so or null) viewing the item
     * @param int $idItem - id of the item viewed
     *
     * @return bool
     */
    public function existsLastViewed($idNotLogged, $idItem)
    {
        $this->db->select('COUNT(*) AS AGGREGATE');
        $this->db->from('last_viewed_items');

        //region Conditions
        $this->db->where('id_not_logged', $idNotLogged);
        $this->db->where('id_item', $idItem);
        //endregion Conditions

        $counter = $this->db->query_one();
        if (empty($counter)) {
            return false;
        }

        return (bool) (int) arrayGet($counter, 'AGGREGATE');
    }

    /**
     * Updates the last viewed item by incrementing the times it was viewed
     *
     * @param string|null - the id of the not logged user (if so or null) viewing the item
     * @param int $idItem - id of the item viewed
     *
     * @return bool
     */
    function updateTimesLastViewed($idNotLogged, $idItem)
    {
        $sql = "UPDATE `last_viewed_items`
                SET `times_viewed` = `times_viewed` + 1,
                `date_updated` = now()
                WHERE `id_item` = ?";

        $params = [$idItem];

        $sql .= " AND `id_not_logged` = ?";
        $params[] = $idNotLogged;

        return $this->db->query($sql, $params);
    }

    /**
     * Updates the last viewed item by adding the id of the user if user has logged in
     *
     * @param int|null $idUser - the id of the logged user (if so or null) viewing the item
     * @param string|null - the id of the not logged user (if so or null) viewing the item
     *
     * @return bool
     */
    function updateIdUserByCookie($idUser, $idNotLogged)
    {
        $sql = "UPDATE `last_viewed_items`
                SET `id_user` = ?
                WHERE `id_user` IS NULL
                AND `id_not_logged` = ?";

        return $this->db->query($sql, [$idUser, $idNotLogged]);
    }

    /**
     * Delete last viewed items that are older than 30 days
     *
     * Used in cron only
     */
    function deleteOldLastViewed()
    {
        $sql = "DELETE FROM last_viewed_items WHERE DATE(date_updated) <= DATE_SUB(SYSDATE(), INTERVAL 30 DAY)";
        return $this->db->query($sql);
    }

    function getMonthlyLastViewedByUser()
    {
        #region get all users to whom to send emails this month
        $this->db->select('DISTINCT(`lv`.`id_user`), `u`.`email`, CONCAT_WS(" ", `u`.`fname`, `u`.`lname`) as user_name');
        $this->db->from('last_viewed_items `lv`');
        $this->db->join('users `u`', '`lv`.`id_user` = `u`.`idu`', 'left');
        $this->db->where_raw('`lv`.`id_user` IS NOT NULL');
        $this->db->where('`u`.`user_group`', 1);
        $this->db->where_raw('MONTH(`lv`.`date_updated`) = MONTH(DATE_SUB(CURRENT_DATE(), INTERVAL 1 DAY))');

        $users = $this->db->query_all();
        #endregion get all users to whom to send emails this month

        #region get items limit 5 by group for each user
        $params = [];
        $idsList = array_column($users, 'id_user');

        $sql = "SELECT lv.id_user, lv.id_item, i.title
                FROM (
                    SELECT ll.*, row_number() over (PARTITION by ll.id_user order by ll.times_viewed DESC) AS row_n
	                FROM last_viewed_items ll
                ) as lv
                LEFT JOIN items i ON i.id = lv.id_item
                WHERE lv.id_user IN (" . implode(',', array_fill(0, count($idsList), '?')) . ")
                AND lv.row_n <= ?";

        array_push($params, ...$idsList);
        $params[] = config('limit_last_viewed_in_email', 5);

        $itemsByUser = $this->db->query_all($sql, $params);
        #endregion get items limit 5 by group for each user

        #region create multidimensional array by user with items
        $lastViewed = array_column($users, NULL, 'id_user');

        foreach($itemsByUser as $item){
            $lastViewed[$item['id_user']]['items'][] = $item;
        }
        #endregion create multidimensional array by user with items

        return $lastViewed;
    }

    function up_item_rating($id_item, $rating){
        $sql = "UPDATE items
                SET rating = ROUND(((rating * rev_numb + ?) / (rev_numb + 1)), 1), rev_numb = rev_numb + 1
                WHERE id = ?";

        return $this->db->query($sql, [$rating, $id_item]);
    }

    function down_item_rating($id_item, $rating){
        $sql = "UPDATE items
                SET
                    rating = ROUND(((rating * rev_numb - ?)/(rev_numb - 1)), 1),
                    rev_numb = rev_numb - 1
                WHERE id = ?";
        return $this->db->query($sql, [$rating, $id_item]);
    }

    /**
    * Items snapshot functions
    */
    public function get_item_snapshot($id_s){
        $sql = "SELECT *
                FROM item_snapshots
                WHERE id_snapshot = ?";
        return $this->db->query_one($sql, array($id_s));
    }

    public function get_items_snapshot($conditions){
        extract($conditions);
        $where = $params = [];

        if (isset($snapshots_list)) {
            $snapshotsList = getArrayFromString($snapshots_list);
            $where[] = " id_snapshot IN (" . implode(',', array_fill(0, count($snapshotsList), '?')) . ")";
            array_push($params, ...$snapshotsList);
        }

        $sql = "SELECT *, CONCAT(u.fname, ' ', u.lname) as user_name,
                        ug.gr_name, cb.name_company, cb.index_name, cb.id_company
                FROM item_snapshots its
                LEFT JOIN items it ON its.id_item = it.id
                LEFT JOIN users u ON it.id_seller = u.idu
                LEFT JOIN user_groups ug ON u.user_group = ug.idgroup
                LEFT JOIN company_base cb ON cb.id_user = it.id_seller AND cb.type_company = 'company'";

        if(count($where)) $sql .= " WHERE " . implode(" AND", $where);

        return $this->db->query_all($sql, $params);
    }

    /**
    * Items ordered functions
    */
    public function get_items_ordered($conditions){
        $order_by = "io.date_ordered ";
        $function_type = 'all';

        extract($conditions);

        $where = $params = [];

        if(!empty($item)){
            $where[] = " io.id_item = ? ";
            $params[] = $item;
        }

        if(!empty($buyer)){
            $where[] = " ios.id_buyer = ? ";
            $params[] = $buyer;
        }

        $sql = "SELECT io.*, ic.*, it.*, cur.curr_entity
            FROM item_ordered io
            INNER JOIN item_orders ios ON io.id_order = ios.id
            INNER JOIN items it ON io.id_item = it.id
            INNER JOIN item_category ic ON it.id_cat = ic.category_id
            INNER JOIN users us ON it.id_seller = us.idu
            INNER JOIN currency cur ON cur.id = it.currency ";

        if(count($where)) $sql .= " WHERE " . implode(" AND", $where);

        $res = $this->db->query_all($sql, $params);

        $count = $this->db->numRows();

        /* block for count all items */
        if($function_type == 'count')
            return $count;

        /* block for count pagination */
        $pages = ceil($count/$per_p);
        if($function_type == 'pages')
            return $pages;

        $sql .= " GROUP BY io.id_item ";
        $sql .= " ORDER BY ".$order_by;

        if($page > $pages)
            $page = $pages;
        $start = ($page-1)*$per_p;
        if($start < 0)
            $start = 0;

        $sql .=  " LIMIT " . $start . "," . $per_p;

        return  $this->db->query_all($sql, $params);
    }

    public function get_items_ordered_list_by_user($id_buyer){
        $sql = "SELECT io.id_snapshot
                FROM item_ordered io
                INNER JOIN item_orders ios ON io.id_order = ios.id
                WHERE ios.id_buyer = ?
                LIMIT 10";

        return  $this->db->query_all($sql, [$id_buyer]);
    }

    public function get_item_simple($id_item = 0, $columns = '*'){
        $this->db->select($columns);
        $this->db->from("items");
        $this->db->where("id = ?", $id_item);
        $record = $this->db->get_one();

        return empty($record) ? [] : $record;
    }

    public function get_items_simple($ids, $columns = '*'){
        $sql = "SELECT {$columns}
                FROM items
                WHERE id IN (" . implode(',', array_fill(0, count($ids), '?')) . ")";
        return $this->db->query_all($sql, $ids);
    }

    public function update_items_by_seller($id, $update){
        $this->db->where('id_seller', $id);
        return $this->db->update('item_user_attr', $update);
    }

    public function update_by_seller($id, $update){
        $this->db->where('id_seller', $id);
        return $this->db->update('items', $update);
    }

	function get_items_for_thumbs_actualize($conditions = array()){
        $columns = "*";
		$order_by = " id ASC ";

		extract($conditions);

		$where = $params = [];

		if(isset($thumbs_actualized)){
			$where[] = " thumbs_actualized = ? ";
			$params[] = $thumbs_actualized;
		}

		$sql = "SELECT $columns
				FROM items";

		if(!empty($where)){
			$sql .= " WHERE " . implode(" AND ", $where);
		}

		$sql .= " ORDER BY $order_by ";

		if(isset($limit)){
			$sql .= " LIMIT $limit ";
		}

		return $this->db->query_all($sql, $params);
	}

    public function get_items_photos($conditions = array()){
		$order_by = " sale_id ASC, main_photo DESC ";
		extract($conditions);

		$where = $params = [];

		if(isset($items_list)){
            $itemsList = getArrayFromString($items_list);
			$where[] = " sale_id IN (" . implode(',', array_fill(0, count($itemsList), '?')) . ") ";
            array_push($params, ...$itemsList);
        }

        if(isset($id_item)){
			$where[] = " sale_id = ? ";
            $params[] = $id_item;
        }

        if(isset($images_list)){
            $imagesList = getArrayFromString($images_list);
			$where[] = " id IN (" . implode(',', array_fill(0, count($imagesList), '?')) . ") ";
            array_push($params, ...$imagesList);
        }

        if(isset($exist_photo_thumbs)){
			$where[] = " photo_thumbs != '' ";
        }

        if(isset($main_photo)){
			$where[] = " main_photo = ? ";
            $params[] = $main_photo;
		}

        $sql = "SELECT *
                FROM item_photo";

		if(!empty($where)){
			$sql .= " WHERE " . implode(" AND ", $where);
		}

        $sql .= " ORDER BY $order_by ";

        if(isset($limit)){
			$sql .= " LIMIT $limit ";
		}

        return $this->db->query_all($sql, $params);
    }

	public function get_search_info($id_item){
        /** @var Catattributes_Model $catAttributesModel */
        $catAttributesModel = model(Catattributes_Model::class);

		$item_info = $this->get_item($id_item);
		$search_info = array(
			$item_info['country'],
			$item_info['item_state'],
			$item_info['city']
		);

		$item_info['cat_breadcrumbs'] = json_decode('['.$item_info['cat_breadcrumbs'].']', true);
		foreach($item_info['cat_breadcrumbs'] as $breadcrumbs){
			foreach($breadcrumbs as $cat_title){
				$search_info[] = $cat_title;
			}
		}

		$cat_attr = $catAttributesModel->get_item_attr_full_values($id_item);
		if (!empty($cat_attr)) {
			foreach ($cat_attr as $attribute) {
				if (in_array($attribute['attr_type'], array('select', 'multiselect'))) {
					$search_info[] = $attribute['attr_name'].' '.$attribute['attr_values'];
				}else {
					$search_info[] = $attribute['attr_name'].' '.$attribute['attr_value'];
				}
			}
		}

		$user_attrs = $this->get_user_attrs($id_item);
		if(!empty($user_attrs)){
			foreach($user_attrs as $user_attr){
				$search_info[] = $user_attr['p_name'].' '.$user_attr['p_value'];
			}
		}

		$vin_info = $this->get_vin_info_simple($id_item);
		if(!empty($vin_info)){
			$search_info[] = $vin_info['vin_search_info'];
		}

		return implode(' ', $search_info);
	}

    public function get_item_location(int $item_id): array
	{
		$this->db->select(
			<<<COLUMNS
			`ITEMS`.`item_zip`, `ITEMS`.`p_country` as `country_id`, `ITEMS`.`state` as `region_id`, `ITEMS`.`p_city` as `city_id`,
			`COUNTRIES`.`country` as `country`, `REGIONS`.`state` as `region`, `CITIES`.`city`
			COLUMNS
		);
		$this->db->from('`items` as `ITEMS`');
		$this->db->join('`zips` AS `CITIES`', '`ITEMS`.`p_city` = `CITIES`.`id`', 'left');
		$this->db->join('`states` AS `REGIONS`', '`ITEMS`.`state` = `REGIONS`.`id`', 'left');
		$this->db->join('`port_country` AS `COUNTRIES`', '`ITEMS`.`p_country` = `COUNTRIES`.`id`', 'left');
		$this->db->where('`ITEMS`.`id` = ?', $item_id);

		return array_filter((array) $this->db->query_one());
    }

    public function isItemOutOfStock($idItem)
    {
        $this->db->select("COUNT(*) as `AGGREGATE`");
		$this->db->from('`items` as `ITEMS`');
        $this->db->where('`ITEMS`.`id` = ?', $idItem);
        $this->db->where('`ITEMS`.`is_out_of_stock`', 1);

        $data = $this->db->query_one();

        return !isset($data['AGGREGATE']) ? false : (bool) $data['AGGREGATE'];
    }

    public function markOutOfStockNotified($idItem)
    {
        $this->db->where('id_item', $idItem);
        return $this->db->update('notify_out_of_stock',[
            'was_notified'  => 1,
            'date_notified' => date('Y-m-d H:i:s')
        ]);
    }

    public function getOutOfStockNotifyByItem($idItem)
    {
        $this->db->select("id_user");
		$this->db->from('notify_out_of_stock');
        $this->db->where('id_item', $idItem);
        $this->db->where('was_notified', 0);

        return $this->db->query_all();
    }

    public function existsOutOfStockNotify($idUser, $idItem)
    {
        $this->db->select('COUNT(*) AS AGGREGATE');
        $this->db->from('notify_out_of_stock');

        //region Conditions
        $this->db->where('id_user', $idUser);
        $this->db->where('id_item', $idItem);
        $this->db->where('was_notified', 0);
        //endregion Conditions

        $counter = $this->db->query_one();
        if (empty($counter)) {
            return false;
        }

        return (bool) (int) arrayGet($counter, 'AGGREGATE');
    }

    public function insertOutOfStockNotify($idUser, $idItem)
    {
        $this->db->insert('notify_out_of_stock', array('id_user' => $idUser, 'id_item' => $idItem));

        return $this->db->last_insert_id();
    }

    public function updateItems($ids, $data)
    {
        $this->db->in('id', $ids);

        return $this->db->update('items', $data);
    }

    public function getNewDrafts($days = 10, $idUser = null)
    {
        $this->db->select('GROUP_CONCAT(id) as items, id_seller, draft_expire_date, COUNT(id) as counter, u.fname, u.email');
        $this->db->from('items');
        $this->db->join('users u', 'id_seller = u.idu', 'left');
        $this->db->where_raw('draft_expire_date = CURDATE() + interval ? DAY', [$days]);
        $this->db->where('draft', 1);
        if(isset($idUser)){
            $this->db->where('id_seller', (int) $idUser);
        }

        $this->db->groupby('id_seller');

        if(isset($idUser)){
            return $this->db->query_one();
        }
        return $this->db->query_all();
    }

    public function extendDraftExpiration(int $idUser, DateTimeImmutable $expires, DateTimeImmutable $extend)
    {
        $update = [
            'draft_expire_date' => $extend->format('Y-m-d')
        ];

        $this->db->where('id_seller', $idUser);
        $this->db->where('DATE(draft_expire_date)', $expires->format('Y-m-d'));

        return $this->db->update('items', $update);

    }

    /**
     * Scope query by item seller id
     *
     * @param QueryBuilder $builder
     * @param int $sellerId
     *
     * @return void
     */
    protected function scopeSellerId(QueryBuilder $builder, int $sellerId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->getTable()}`.`id_seller`",
                $builder->createNamedParameter($sellerId, ParameterType::INTEGER, $this->nameScopeParameter('sellerId'))
            )
        );
    }

    /**
     * Scope by draft items
     *
     * @param QueryBuilder $builder
     * @param int $isDraft
     *
     * @return void
     */
    protected function scopeIsDraft(QueryBuilder $builder, int $isDraft): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->getTable()}`.`draft`",
                $builder->createNamedParameter($isDraft, ParameterType::INTEGER, $this->nameScopeParameter('draftItem'))
            )
        );
    }

    /**
     * Scope by visible items
     *
     * @param QueryBuilder $builder
     * @param int $isVisible
     *
     * @return void
     */
    protected function scopeIsVisible(QueryBuilder $builder, int $isVisible): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->getTable()}`.`visible`",
                $builder->createNamedParameter($isVisible, ParameterType::INTEGER, $this->nameScopeParameter('visibleItem'))
            )
        );
    }

    /**
     * Scope by blocked items
     *
     * @param QueryBuilder $builder
     * @param int $isBlocked 0, 1 or 2
     *
     * @return void
     */
    protected function scopeIsBlocked(QueryBuilder $builder, int $isBlocked): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->getTable()}`.`blocked`",
                $builder->createNamedParameter($isBlocked, ParameterType::INTEGER, $this->nameScopeParameter('blockedItem'))
            )
        );
    }

    /**
     * Scope for join with categories
     */
    protected function bindCategories(QueryBuilder $builder): void
    {
        /** @var Category_Model $categoryModel */
        $categoryModel = model(Category_Model::class);
        $categoryTable = $categoryModel->get_categories_table();

        $builder->leftJoin(
            $this->getTable(),
            $categoryTable,
            $categoryTable,
            "`{$categoryTable}`.`category_id` = `{$this->getTable()}`.`id_cat`"
        );
    }
}
