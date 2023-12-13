<?php

use App\Common\Database\BaseModel;
use App\Common\Database\Concerns;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Statement;
use Symfony\Component\String\UnicodeString;

class Dispute_Model extends BaseModel
{
    use Concerns\CanSearch;

    public $disput_table = 'orders_disputes';
    public $item_orders_table = 'item_orders';
    public $item_ordered_table = 'item_ordered';
    public $orders_status_table = 'orders_status';
    public $item_snapshots_table = 'item_snapshots';
    public $users_table = 'users';
    public $company_base_table = 'company_base';
    public $orders_shippers_table = 'orders_shippers';
    public $temp_basepath = 'temp' . DS . 'disputes';
    public $statuses = array(
        'init' => array(
            'title'  => 'New',
            'icon'   => 'ep-icon_new-stroke txt-green',
            'rights' => 'buy_item,dispute_administration',
        ),
        'processing' => array(
            'title'  => 'Processing',
            'icon'   => 'ep-icon_hourglass-processing txt-orange',
            'rights' => 'manage_disputes,dispute_administration',
        ),
        'closed' => array(
            'title'  => 'Closed',
            'icon'   => 'ep-icon_ok-circle txt-green',
            'rights' => 'manage_disputes,dispute_administration',
        ),
        'resolved' => array(
            'title'  => 'Closing',
            'icon'   => 'ep-icon_ok-circle txt-blue',
            'rights' => 'manage_disputes,dispute_administration',
        ),
        'canceled' => array(
            'title'  => 'Canceled',
            'icon'   => 'ep-icon_remove-circle txt-red',
            'rights' => 'buy_item,dispute_administration',
        ),
    );

    // USED FUNCTIONS
    public function scopeDisputesList(QueryBuilder $query, $conditions = array())
    {
        $query
            ->from($this->disput_table, 'od')
            ->innerJoin('od', $this->item_orders_table, 'io', 'io.id = od.id_order')
            ->leftJoin('od', $this->item_ordered_table, 'iod', 'iod.id_ordered_item = od.id_ordered')
            ->leftJoin('od', $this->item_snapshots_table, 'iss', 'iss.id_snapshot = iod.id_snapshot')
        ;

        extract($conditions);

        if (isset($id_disput)) {
            $query->andWhere(
                $query->expr()->eq('od.id', $query->createNamedParameter((int) $id_disput, ParameterType::INTEGER, ':disputeId'))
            );
        }

        if (isset($id_seller)) {
            $query->andWhere(
                $query->expr()->eq('od.id_seller', $query->createNamedParameter((int) $id_seller, ParameterType::INTEGER, ':sellerId'))
            );
        }

        if (isset($id_shipper)) {
            $query->andWhere(
                $query->expr()->and(
                    $query->expr()->eq('od.id_shipper', $query->createNamedParameter((int) $id_shipper, ParameterType::INTEGER, ':shipperId')),
                    $query->expr()->eq('io.shipper_type', $query->createNamedParameter('ep_shipper', ParameterType::STRING, ':shipperType'))
                )
            );
        }

        if (isset($id_buyer)) {
            $query->andWhere(
                $query->expr()->eq('od.id_buyer', $query->createNamedParameter((int) $id_buyer, ParameterType::INTEGER, ':buyerId'))
            );
        }

        if (isset($ep_manager)) {
            $query->andWhere(
                $query->expr()->eq('od.id_ep_manager', $query->createNamedParameter((int) $ep_manager, ParameterType::INTEGER, ':managerId'))
            );
        }

        if (isset($status)) {
            if (!is_array($status)) {
                $status = explode(',', $status);
            }

            $query->andWhere(
                $query->expr()->in("od.status", array_map(
                    fn (int $index, string $status) => $query->createNamedParameter($status, ParameterType::STRING, ":status{$index}"),
                    range(0, max(count($status) - 1, 0)),
                    (array) $status
                ))
            );
        }

        if (isset($start_date)) {
            $query->andWhere(
                $query->expr()->gte('DATE(od.date_time)', $query->createNamedParameter($start_date, ParameterType::STRING, ':startedFrom'))
            );
        }

        if (isset($finish_date)) {
            $query->andWhere(
                $query->expr()->lte('DATE(od.date_time)', $query->createNamedParameter($finish_date, ParameterType::STRING, ':startedTo'))
            );
        }

        if (isset($start_date_changed)) {
            $query->andWhere(
                $query->expr()->gte('DATE(od.change_date)', $query->createNamedParameter($start_date_changed, ParameterType::STRING, ':updatedFrom'))
            );
        }

        if (isset($finish_date_changed)) {
            $query->andWhere(
                $query->expr()->lte('DATE(od.change_date)', $query->createNamedParameter($finish_date_changed, ParameterType::STRING, ':updatedTo'))
            );
        }

        if (isset($id_snapshot)) {
            $query->andWhere(
                $query->expr()->eq('iss.id_snapshot', $query->createNamedParameter((int) $id_snapshot, ParameterType::INTEGER, ':snapshotId'))
            );
        }

        if (isset($search)) {
            $searchText = (new UnicodeString($search))->trim();
            $searchTokens = $this->tokenizeSearchText($searchText, true);
            $useMatchSearch = !empty($searchTokens) && !empty($matchColumns);

            if ($useMatchSearch) {
                $parameter = $query->createNamedParameter(
                    $this->getConnection()->quote(implode(' ', $searchTokens)),
                    ParameterType::STRING,
                    ":searchMatchedText"
                );

                $query->andWhere(
                    $query->expr()->or(
                        "MATCH (`od`.`comment`, `od`.`timeline`, `od`.`reason`) AGAINST ({$parameter})",
                        "MATCH (`io`.`search_info`) AGAINST ({$parameter})"
                    )
                );
            } else {
                $textParameter = $query->createNamedParameter((string) $searchText, ParameterType::STRING, ":searchText");
                $textTokenParameter = $query->createNamedParameter(
                    (string) $searchText->prepend('%')->append('%'),
                    ParameterType::STRING,
                    ":searchTextToken"
                );

                $columns = ["`od`.`comment`", "`od`.`timeline`", "`od`.`reason`", "`io`.`search_info`"];
                $query->andWhere(
                    $query->expr()->or(
                        ...\array_map(fn (string $column) => $query->expr()->eq($column, $textParameter), $columns),
                        ...\array_map(fn (string $column) => $query->expr()->like($column, $textTokenParameter), $columns),
                    )
                );
            }
        }

        if (isset($id_order)) {
            $query->andWhere(
                $query->expr()->eq('od.id_order', $query->createNamedParameter((int) $id_order, ParameterType::INTEGER, ':orderId'))
            );
        }
    }

    public function get_disputes($conditions = array())
    {
        $query = $this->createQueryBuilder();
        $query->select("od.*", "od.id_ordered as ordered_id", "iss.title as item_title", "iss.id_snapshot", "iss.main_image");
        $this->scopeDisputesList($query, $conditions);
        if (isset($conditions['sort_by'])) {
            foreach ($conditions['sort_by'] as $item) {
                list($column, $direction) = explode('-', $item);
                $query->addOrderBy($column, $direction);
            }
        } else {
            $query->orderBy('od.status', 'ASC');
        }

        if (isset($conditions['per_p'])) {
            $this->scopeLimitOrSkip($query, (int) $conditions['per_p'], (int) $conditions['start']);
        }

        /** @var Statement $statement */
        $statement = $query->execute();
        $records = $statement->fetchAllAssociative();

        return empty($records) ? [] : $records;
    }

    public function get_disputes_count($conditions)
    {
        $query = $this->createQueryBuilder();
        $query->select("COUNT(od.id) as total_rows");
        $this->scopeDisputesList($query, $conditions);

        /** @var Statement $statement */
        $statement = $query->execute();
        $records = $statement->fetchAllAssociative();

        return (int) ($records[0]['total_rows'] ?? 0);
    }

    public function get_disput($id_disput = 0, $rows = 'od.*, io.final_price, io.shipper_type')
    {
        $this->db->select($rows);
        $this->db->from("{$this->disput_table} od");
        $this->db->join("{$this->item_orders_table} io", 'io.id = od.id_order', 'inner');
        $this->db->where('od.id = ?', (int) $id_disput);

        return $this->db->query_one();
    }

    public function dispute_update($id, $update)
    {
        $this->db->where('id', $id);

        return $this->db->update($this->disput_table, $update);
    }

    public function append_timeline($id, $json)
    {
        $sql = "UPDATE $this->disput_table
				SET timeline = CONCAT_WS(',',?,timeline)
				WHERE id = ?";

        return $this->db->query($sql, array($json, $id));
    }

    public function get_simple_disputes($conditions)
    {
        $where = array();
        $params = array();
        $columns = '*';
        extract($conditions);

        if (isset($id_orders)) {
            $id_orders = getArrayFromString($id_orders);
            $where[] = ' id_order IN (' . implode(',', array_fill(0, count($id_orders), '?')) . ') ';
            array_push($params, ...$id_orders);
        }

        $sql = "SELECT $columns
                FROM $this->disput_table";

        if (count($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        return $this->db->query_all($sql, $params);
    }

    public function insert_disput($reg)
    {
        $this->db->insert($this->disput_table, $reg);

        return $this->db->last_insert_id();
    }

    public function is_in_disput($id_user = 0, $id_disput = 0, $role_need = array())
    {
        $this->db->select('COUNT(*) as total_records');
        $this->db->from("{$this->disput_table}");
        $this->db->where('id = ?', $id_disput);

        if (!empty($role_need)) {
            $user_where = array();
            if (in_array('buyer', $role_need)) {
                $user_where[] = 'id_buyer = ?';
                $user_params[] = $id_user;
            }

            if (in_array('seller', $role_need)) {
                $user_where[] = 'id_seller = ?';
                $user_params[] = $id_user;
            }

            if (in_array('shipper', $role_need)) {
                $user_where[] = 'id_shipper = ?';
                $user_params[] = $id_user;
            }

            if (in_array('manager', $role_need)) {
                $user_where[] = 'id_ep_manager = ?';
                $user_params[] = $id_user;
            }

            if (!empty($user_where)) {
                $this->db->where_raw("(" . implode(' OR ', $user_where) . ")", $user_params);
            }
        }

        $result = $this->db->query_one();

        return (int) $result['total_records'];
    }

    public function get_disput_details($id_disput, $select = "od.id_order, od.money_back as returned_sum, od.id as disput_id, CONCAT(u.fname, ' ', u.lname) as user_name, u.idu as user_id, u.email, od.status, od.id_ordered, od.timeline, od.id_buyer, od.id_seller, od.id_shipper, od.id_ep_manager")
    {
        $sql = "SELECT $select
				FROM $this->disput_table od
				LEFT JOIN $this->item_orders_table io ON io.id=od.id_order
				LEFT JOIN $this->users_table u ON io.id_buyer=u.idu
				WHERE od.id=?";

        return $this->db->query_one($sql, array($id_disput));
    }

    public function can_open_disput($id_order)
    {
        $sql = "SELECT COUNT(*) as counter, order_status
				FROM $this->disput_table
				WHERE id_order = ?";
        $temp = $this->db->query_one($sql, array($id_order));

        return ($temp['counter'] > 1) || (1 == $temp['counter'] && 10 == $temp['order_status']);
    }

    public function get_users_emails($users = array())
    {
        if (empty($users)) {
            return array();
        }

        $this->db->select('email, idu');
        $this->db->from($this->users_table);
        $this->db->in('idu', $users);

        return $this->db->query_all();
    }

    public function is_disputed_order($id_order, $conditions = array())
    {
        extract($conditions);

        $this->db->select('COUNT(*) as counter');
        $this->db->where('id_order', $id_order);

        if (isset($id_user)) {
            $this->db->where_raw('(id_seller = ? OR id_shipper = ? OR id_buyer = ? OR id_ep_manager = ?)', array_fill(0, 4, $id_user));
        }

        if (isset($status)) {
            $status = getArrayFromString($status);
            $this->db->in('status', $status);
        }

        if (isset($order_status)) {
            $order_status = getArrayFromString($order_status);
            $this->db->in('order_status', $order_status);
        }

        if (isset($id_ordered)) {
            $id_ordered = getArrayFromString($id_ordered);
            $this->db->in('id_ordered', $id_ordered);
        }

        $result = $this->db->get_one($this->disput_table);

        return $result['counter'];
    }

    public function get_order_disputes($id_order, $conditions = array())
    {
        extract($conditions);

        $this->db->where('id_order', $id_order);
        if (isset($id_user)) {
            $this->db->where_raw("(id_seller = ? OR id_shipper = ? OR id_buyer = ? OR id_ep_manager = ?)", array_fill(0, 4, $id_user));
        }

        if (isset($status)) {
            $status = getArrayFromString($status);
            $this->db->in('status', $status);
        }

        if (isset($order_status)) {
            $order_status = getArrayFromString($order_status);
            $this->db->in('order_status', $order_status);
        }

        if (isset($id_ordered)) {
            $id_ordered = getArrayFromString($id_ordered);
            $this->db->in('id_ordered', $id_ordered);
        }

        return $this->db->get($this->disput_table);
    }

    public function get_ordered_item($id_ordered, $select = 'iod.id_ordered_item, iod.id_order, io.discount, ios.title, iod.price_ordered, iod.quantity_ordered, io.id_buyer, io.id_seller, io.id_shipper, io.ep_manager')
    {
        $sql = "SELECT $select
				FROM $this->item_ordered_table iod
				LEFT JOIN $this->item_orders_table io ON iod.id_order=io.id
				LEFT JOIN $this->item_snapshots_table ios ON iod.id_snapshot=ios.id_snapshot
				WHERE iod.id_ordered_item = ?";

        return $this->db->query_one($sql, array($id_ordered));
    }

    public function get_ordered_items_titles($id_order)
    {
        $sql = "SELECT GROUP_CONCAT(iss.title) as title_items
				FROM $this->item_snapshots_table iss
				LEFT JOIN $this->item_ordered_table iod ON iod.id_snapshot=iss.id_snapshot
				WHERE iod.id_order=?";

        return $this->db->query_one($sql, array($id_order));
    }

    public function get_users($users = array())
    {
        if (empty($users)) {
            return array();
        }

        $this->db->select("idu, CONCAT(fname, ' ', lname) as user_name, status as user_status");
        $this->db->from($this->users_table);
        $this->db->in('idu', $users);

        return $this->db->query_all();
    }

    public function get_sellers_companies($sellers_list = array())
    {
        if (empty($sellers_list)) {
            return [];
        }

        $this->db->where('type_company', 'company');
        $this->db->where('parent_company', 0);
        $this->db->in('id_user', $sellers_list);

        return $this->db->get($this->company_base_table);
    }

    public function get_shippers_companies($shippers_list = array())
    {
        if (empty($shippers_list)) {
            return array();
        }

        $this->db->in('id_user', $shippers_list);

        return $this->db->get($this->orders_shippers_table);
    }
}
