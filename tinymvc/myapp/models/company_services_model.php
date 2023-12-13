<?php

declare(strict_types=1);

use App\Common\Database\BaseModel;
use App\Common\Exceptions\NotFoundException;
use App\Common\Exceptions\NotUniqueException;
use App\Common\Exceptions\OwnershipException;
use App\Entities\Phones\CountryCode as PhoneCountryCode;

/**
 * Model for company services.
 */
class Company_Services_Model extends BaseModel
{
    /**
     * Name of the company services table.
     *
     * @var string
     */
    private $services_table = 'company_services_contacts';

    /**
     * Name of the companies table.
     *
     * @var string
     */
    private $companies_table = 'company_base';

    public function is_my_service($service, $company)
    {
        $this->db->select('COUNT(*) AS AGGREGATE');
        $this->db->from("{$this->services_table} AS SERVICES");
        $this->db->where('id_service = ?', (int) $service);
        $this->db->where('id_company = ?', (int) $company);

        $counter = $this->db->query_one();
        if (!$counter || empty($counter)) {
            return false;
        }

        return isset($counter['AGGREGATE']) ? (bool) (int) $counter['AGGREGATE'] : false;
    }

    public function is_service_exists($service)
    {
        $this->db->select('COUNT(*) AS AGGREGATE');
        $this->db->from("{$this->services_table} AS SERVICES");
        $this->db->where('id_service = ?', (int) $service);

        $counter = $this->db->query_one();
        if (!$counter || empty($counter)) {
            return false;
        }

        return isset($counter['AGGREGATE']) ? (bool) (int) $counter['AGGREGATE'] : false;
    }

    public function has_service($titles, array $conditions = array())
    {
        $this->db->select('COUNT(*) AS AGGREGATE');
        $this->db->from("{$this->services_table} AS SERVICES");
        $this->db->where('SERVICES.title_service = ?', $titles);

        extract($conditions, EXTR_PREFIX_ALL, 'condition');

        if (!empty($conditions)) {
            if (isset($condition_not) && !empty($condition_not)) {
                if (is_array($condition_not)) {
                    $excluded_records = array_map('intval', $condition_not);
                    $this->db->where_raw(
                        sprintf('SERVICES.id_service NOT IN (%s)', implode(',', array_fill(0, count($excluded_records), '?'))),
                        $excluded_records
                    );
                } elseif (is_string($condition_not) && false !== strpos($condition_not, ',')) {
                    $excluded_records = array_map('intval', explode(',', $condition_not));
                    $this->db->where_raw(
                        sprintf('SERVICES.id_service NOT IN (%s)', implode(',', array_fill(0, count($excluded_records), '?'))),
                        $excluded_records
                    );
                } else {
                    $this->db->where('SERVICES.id_service != ?', (int) $condition_not);
                }
            }
            if (isset($condition_company)) {
                $this->db->where('SERVICES.id_company = ?', (int) $condition_company);
            }
        }

        $counter = $this->db->query_one();
        if (!$counter || empty($counter)) {
            return false;
        }

        return isset($counter['AGGREGATE']) ? (bool) (int) $counter['AGGREGATE'] : false;
    }

    public function get_service($service)
    {
        $this->db->select($this->prepareColumns(array('SERVICES.*')));
        $this->db->from("{$this->services_table} AS SERVICES");
        $this->db->where('id_service = ?', (int) $service);

        return $this->db->query_one();
    }

    public function get_services(array $params = array())
    {
        $skip = null;
        $limit = null;
        $alias = 'SERVICES';
        $with = array();
        $order = array();
        $group = array();
        $columns = array('*');
        $conditions = array();

        extract($params);
        extract($with, EXTR_PREFIX_ALL, 'with');
        extract($conditions, EXTR_PREFIX_ALL, 'condition');

        $this->db->select($this->prepareColumns($columns));
        $this->db->from("{$this->services_table} AS {$alias}");

        //region Joins
        if (!empty($with)) {
            if (isset($with_companies) && $with_companies) {
                $this->with_companies($alias, 'id_company', $with_companies);
            }
        }
        //endregion Joins

        //region Conditions
        if (!empty($conditions)) {
            if (isset($condition_created_from)) {
                $this->db->where("{$alias}.created_at >= ?", $condition_created_from);
            }
            if (isset($condition_created_to)) {
                $this->db->where("{$alias}.created_at <= ?", $condition_created_to);
            }
            if (isset($condition_updated_from)) {
                $this->db->where("{$alias}.updated_at >= ?", $condition_updated_from);
            }
            if (isset($condition_updated_to)) {
                $this->db->where("{$alias}.updated_at <= ?", $condition_updated_to);
            }
            if (isset($condition_search)) {
                if (str_word_count_utf8($condition_search) > 1) {
                    $escaped_search_string = $this->db->getConnection()->quote(trim($condition_search));
                    $search_parts = preg_split("/\b/", trim($escaped_search_string, "'"));
                    $search_parts = array_map('trim', $search_parts);
                    $search_parts = array_filter($search_parts);
                    if (!empty($search_parts)) {
                        // Drop array keys
                        $search_parts = array_values($search_parts);
                        // Unite words - each consecutive word have lesser contribution
                        $search_condition = implode('* <', $search_parts);
                        $search_condition = "{$search_condition}*";
                        $this->db->where_raw("MATCH ({$alias}.title_service, {$alias}.info_service) AGAINST (? IN BOOLEAN MODE)", "'$search_condition'");
                    }
                } else {
                    $this->db->where_raw("({$alias}.title_service LIKE ? OR {$alias}.info_service LIKE ?)", array("%{$condition_search}%", "%{$condition_search}%"));
                }
            }
            if (isset($condition_service)) {
                $this->db->where("{$alias}.id_service = ?", (int) $condition_service);
            }
            if (isset($condition_services) && !empty($condition_services)) {
                if (is_array($condition_services)) {
                    $this->db->in("{$alias}.id_service", array_map('intval', $condition_services));
                } elseif (is_string($condition_services) && false !== strpos($condition_services, ',')) {
                    $this->db->in("{$alias}.id_service", array_map('intval', explode(',', $condition_services)));
                }
            }
            if (isset($condition_not) && !empty($condition_not)) {
                if (is_array($condition_not)) {
                    $excluded_records = array_map('intval', $condition_not);
                    $this->db->where_raw(
                        sprintf("{$alias}.id_service NOT IN (%s)", implode(',', array_fill(0, count($excluded_records), '?'))),
                        $excluded_records
                    );
                } elseif (is_string($condition_not) && false !== strpos($condition_not, ',')) {
                    $excluded_records = array_map('intval', explode(',', $condition_not));
                    $this->db->where_raw(
                        sprintf("{$alias}.id_service NOT IN (%s)", implode(',', array_fill(0, count($excluded_records), '?'))),
                        $excluded_records
                    );
                } else {
                    $this->db->where("{$alias}.id_service != ?", (int) $condition_not);
                }
            }
            if (isset($condition_company)) {
                $this->db->where("{$alias}.id_company = ?", (int) $condition_company);
            }
            if (isset($condition_company_visibility) && $with_companies) {
                $this->db->where('COMPANIES.visible_company = ?', (int) $condition_company_visibility);
            }
            if (isset($condition_company_type) && $with_companies) {
                $this->db->where('COMPANIES.type_company = ?', $condition_company_type);
            }
        }
        //endregion Conditions

        //region GroupBy
        foreach ($group as $column) {
            $this->db->groupby($column);
        }
        //endregion GroupBy

        //region OrderBy
        $ordering = array();
        foreach ($order as $column => $direction) {
            if (!empty($direction) && is_string($direction)) {
                $direction = mb_strtoupper($direction);
                $ordering[] = "{$column} {$direction}";
            } else {
                $ordering[] = $column;
            }
        }
        if (!empty($ordering)) {
            $this->db->orderby(implode(', ', $ordering));
        }
        //endregion OrderBy

        //region Limits
        if (null !== $limit) {
            if (null !== $skip) {
                $this->db->limit($limit, $skip);
            } else {
                $this->db->limit($limit);
            }
        }
        //endregion Limits

        return $this->db->query_all();
    }

    /**
     * Find service.
     *
     * @param int      $service
     * @param null|int $company
     *
     * @throws \App\Common\Exceptions\NotFoundException  if category doesn' exists
     * @throws \App\Common\Exceptions\OwnershipException if company doesn't own the service
     *
     * @return array
     */
    public function find_company_service($service, $company = null)
    {
        if (
            empty($service) ||
            empty($serviceData = $this->get_service($service))
        ) {
            throw new NotFoundException('The service with such ID is not found on this server');
        }
        if (null !== $company) {
            if ((int) $company !== (int) $serviceData['id_company']) {
                throw new OwnershipException('The service does not belong to this company');
            }
        }

        return $serviceData;
    }

    /**
     * Creates new service record from th raw data.
     *
     * @param array $data
     */
    public function add_service(array $data)
    {
        return $this->db->insert($this->services_table, $data);
    }

    /**
     * Creates one service record.
     *
     * @param int              $company
     * @param string           $title
     * @param string           $description
     * @param string           $email
     * @param PhoneCountryCode $phone_code
     * @param string           $phone
     *
     * @return bool|int
     */
    public function create_service(
        $company,
        $title,
        $description,
        $email,
        PhoneCountryCode $phone_code = null,
        $phone = null
    ) {
        if ($this->has_service($title, array('company' => (int) $company))) {
            throw new NotUniqueException('The service title is not unique');
        }

        return $this->add_service(array(
            'id_company'    => (int) $company,
            'id_phone_code' => null !== $phone_code ? $phone_code->getId() : null,
            'title_service' => $title,
            'info_service'  => $description,
            'email_service' => $email,
            'phone_service' => $phone,
            'phone_code'    => null !== $phone_code ? $phone_code->getName() : null,
        ));
    }

    /**
     * Update service record with raw data.
     *
     * @param int   $service
     * @param array $data
     *
     * @return bool|int
     */
    public function update_service($service, array $data)
    {
        $this->db->where('id_service = ?', (int) $service);

        return $this->db->update($this->services_table, $data);
    }

    /**
     * Changes one service record.
     *
     * @param int                   $service
     * @param int                   $company
     * @param null|string           $title
     * @param null|string           $description
     * @param null|string           $email
     * @param null|PhoneCountryCode $phone_code
     * @param null|string           $phone
     *
     * @return bool|int
     */
    public function change_service(
        $service,
        $company,
        $title = null,
        $description = null,
        $email = null,
        PhoneCountryCode $phone_code = null,
        $phone = null
    ) {
        if (null !== $title) {
            if ($this->has_service($title, array('company' => (int) $company, 'not' => (int) $service))) {
                throw new NotUniqueException('The service title is not unique');
            }
        }

        $update = array_filter(
            array(
                'id_phone_code' => null !== $phone_code ? $phone_code->getId() : null,
                'title_service' => $title,
                'info_service'  => $description,
                'email_service' => $email,
                'phone_service' => $phone,
                'phone_code'    => $phone_code,
                'phone_code'    => null !== $phone_code ? $phone_code->getName() : null,
            ),
            function ($item) {
                return null !== $item;
            }
        );

        if (empty($update)) {
            return true;
        }

        return $this->update_service($service, $update);
    }

    public function delete_service($service)
    {
        if (empty($service)) {
            return true;
        }

        $this->db->where('id_service = ?', (int) $service);

        return $this->db->delete($this->services_table);
    }

    public function delete_services($services)
    {
        if (empty($services)) {
            return true;
        }

        if (is_array($services)) {
            $this->db->in('id_service', array_map('intval', $services));
        } elseif (is_string($services) && false !== strpos($services, ',')) {
            $this->db->in('id_service', array_map('intval', explode(',', $services)));
        } else{
            $this->db->where('id_service = ?', (int) $services);
        }

        return $this->db->delete($this->services_table);
    }

    /**
     * Drop company service.
     *
     * @param int      $service
     * @param null|int $company
     *
     * @throws \App\Common\Exceptions\NotFoundException  if category doesn' exists
     * @throws \App\Common\Exceptions\OwnershipException if company doesn't own the service
     *
     * @return bool
     */
    public function drop_company_service($service, $company = null)
    {
        if (
            empty($service) ||
            !$this->is_service_exists($service)
        ) {
            throw new NotFoundException('The service with such ID is not found on this server');
        }
        if (null !== $company) {
            if (!$this->is_my_service($service, $company)) {
                throw new OwnershipException('The service does not belong to this company');
            }
        }

        return $this->delete_service($service);
    }

    public function count_services(array $params = array())
    {
        $multiple = false;
        $alias = 'SERVICES';
        $with = array();
        $group = array();
        $columns = array('COUNT(*) as AGGREGATE');
        $conditions = array();

        extract($params);
        extract($with, EXTR_PREFIX_ALL, 'with');
        extract($conditions, EXTR_PREFIX_ALL, 'condition');

        $this->db->select($this->prepareColumns($columns));
        $this->db->from("{$this->services_table} AS {$alias}");

        //region Joins
        if (!empty($with)) {
            if (isset($with_companies) && $with_companies) {
                $this->with_companies($alias, 'id_company', $with_companies);
            }
        }
        //endregion Joins

        //region Conditions
        if (!empty($conditions)) {
            if (isset($condition_created_from)) {
                $this->db->where("{$alias}.created_at >= ?", $condition_created_from);
            }
            if (isset($condition_created_to)) {
                $this->db->where("{$alias}.created_at <= ?", $condition_created_to);
            }
            if (isset($condition_updated_from)) {
                $this->db->where("{$alias}.updated_at >= ?", $condition_updated_from);
            }
            if (isset($condition_updated_to)) {
                $this->db->where("{$alias}.updated_at <= ?", $condition_updated_to);
            }
            if (isset($condition_search)) {
                if (str_word_count_utf8($condition_search) > 1) {
                    $escaped_search_string = $this->db->getConnection()->quote(trim($condition_search));
                    $search_parts = preg_split("/\b/", trim($escaped_search_string, "'"));
                    $search_parts = array_map('trim', $search_parts);
                    $search_parts = array_filter($search_parts);
                    if (!empty($search_parts)) {
                        // Drop array keys
                        $search_parts = array_values($search_parts);
                        // Unite words - each consecutive word have lesser contribution
                        $search_condition = implode('* <', $search_parts);
                        $search_condition = "{$search_condition}*";
                        $this->db->where_raw("MATCH ({$alias}.title_service, {$alias}.info_service) AGAINST (? IN BOOLEAN MODE)", "'$search_condition'");
                    }
                } else {
                    $this->db->where_raw("({$alias}.title_service LIKE ? OR {$alias}.info_service LIKE ?)", array("%{$condition_search}%", "%{$condition_search}%"));
                }
            }
            if (isset($condition_service)) {
                $this->db->where("{$alias}.id_service = ?", (int) $condition_service);
            }
            if (isset($condition_services) && !empty($condition_services)) {
                if (is_array($condition_services)) {
                    $this->db->in("{$alias}.id_service", array_map('intval', $condition_services));
                } elseif (is_string($condition_services) && false !== strpos($condition_services, ',')) {
                    $this->db->in("{$alias}.id_service", array_map('intval', explode(',', $condition_services)));
                } else{
                    $this->db->where("{$alias}.id_service = ?", (int) $condition_services);
                }
            }
            if (isset($condition_not) && !empty($condition_not)) {
                if (is_array($condition_not)) {
                    $excluded_records = array_map('intval', $condition_not);
                    $this->db->where_raw(
                        sprintf("{$alias}.id_service NOT IN (%s)", implode(',', array_fill(0, count($excluded_records), '?'))),
                        $excluded_records
                    );
                } elseif (is_string($condition_not) && false !== strpos($condition_not, ',')) {
                    $excluded_records = array_map('intval', explode(',', $condition_not));
                    $this->db->where_raw(
                        sprintf("{$alias}.id_service NOT IN (%s)", implode(',', array_fill(0, count($excluded_records), '?'))),
                        $excluded_records
                    );
                } else {
                    $this->db->where("{$alias}.id_service != ?", (int) $condition_not);
                }
            }
            if (isset($condition_company)) {
                $this->db->where("{$alias}.id_company = ?", (int) $condition_company);
            }
            if (isset($condition_company_visibility) && $with_companies) {
                $this->db->where('COMPANIES.visible_company = ?', (int) $condition_company_visibility);
            }
            if (isset($condition_company_type) && $with_companies) {
                $this->db->where('COMPANIES.type_company = ?', $condition_company_type);
            }
        }
        //endregion Conditions

        //region GroupBy
        foreach ($group as $column) {
            $this->db->groupby($column);
        }
        //endregion GroupBy

        if ($multiple) {
            if (!$this->db->query()) {
                return array();
            }

            return $this->db->getQueryResult()->fetchAllAssociative();
        }

        $data = $this->db->query_one();
        if (!$data || empty($data)) {
            return 0;
        }

        return isset($data['AGGREGATE']) ? (int) $data['AGGREGATE'] : 0;
    }

    private function with_companies($table, $binding, $relation, $target = 'id_company')
    {
        $this->db->join("{$this->companies_table} AS COMPANIES", "COMPANIES.{$target} = {$table}.{$binding}", 'left');
        if (is_callable($relation)) {
            $relation($this->db, $this);
        }
    }
}

// End of file company_services_model.php
// Location: /tinymvc/myapp/models/company_services_model.php
