<?php
/**
 * packages_model.php
 *
 * packages model
 *
 * @author Litra Andrei
 */

class Packages_Model extends TinyMVC_Model
{
    protected $packages_table = "ugroup_packages";
    protected $packages_i18n_table = "ugroup_packages_i18n";
    protected $packages_period_table = "packages_period";
    protected $languages_table = "translations_languages";

    public function getPeriodById($id){
        $this->db->select('*');
        $this->db->from($this->packages_period_table);
        $this->db->where('id', $id);

        return $this->db->query_one();
    }

    public function selectPeriods(){
        return $this->db->get('packages_period');
    }

    function get_upgrade_package($id_package = 0){
        return $this->db->query_one('SELECT * FROM ugroup_packages WHERE idpack = ?', array($id_package));
    }

    function getSimpleGrPackage($idpack = 0, $lang = __SITE_LANG){
        $params = [$idpack];

        if ($lang == 'en') {
            $sql = "SELECT
                        p.*,
                        'en' as lang_pack,
                        per.abr,
                        per.full,
                        per.days,
                        per.fixed_end_date,
                        per.period_name
                    FROM ugroup_packages p
                    LEFT JOIN packages_period per ON p.period = per.id
                    WHERE idpack = ?";
        } else {
            $sql = "SELECT
                        p.*,
                        IF(ISNULL(p_i18n.description), p.description, p_i18n.description) as description,
                        IF(ISNULL(p_i18n.lang_pack), 'en', p_i18n.lang_pack) as lang_pack,
                        per.abr,
                        per.full,
                        per.days,
                        per.fixed_end_date,
                        per.period_name
                    FROM ugroup_packages p
                    LEFT JOIN ugroup_packages_i18n p_i18n ON p.idpack = p_i18n.idpack AND p_i18n.lang_pack = ?
                    LEFT JOIN packages_period per ON p.period = per.id
                    WHERE p.idpack = ?";

            array_unshift($params, $lang);
        }

        return $this->db->query_one($sql, $params);
    }

    function getGrPackageByCondition($conditions = [], $lang = __SITE_LANG){
        extract($conditions);

        $where = [];
        $params = [$lang];

        if(isset($idpack)){
            $where[] = 'p.idpack = ?';
            $params[] = $idpack;
        }

        if(isset($gr_from)){
            $where[] = 'p.gr_from = ?';
            $params[] = $gr_from;
        }

        if(isset($gr_to)){
            $where[] = 'p.gr_to = ?';
            $params[] = $gr_to;
        }

        $sql = "SELECT
                    p.*,
                    IF(ISNULL(p_i18n.description), p.description, p_i18n.description) as description,
                    IF(ISNULL(p_i18n.lang_pack), 'en', p_i18n.lang_pack) as lang_pack,
                    gf.gr_name as gf_name,
                    gf.stamp_pic as gf_stamp_pic,
                    gt.gr_name as gt_name,
                    gt.stamp_pic as gt_stamp_pic,
                    gd.gr_name as gd_name,
                    gd.stamp_pic as gd_stamp_pic,
                    gt.gr_priority,
                    per.abr,
                    per.full,
                    per.days,
                    per.fixed_end_date,
                    per.period_name
                FROM ugroup_packages p
                LEFT JOIN ugroup_packages_i18n p_i18n ON p.idpack = p_i18n.idpack AND p_i18n.lang_pack = ?
                LEFT JOIN packages_period per ON p.period = per.id
                LEFT JOIN user_groups gf ON gf.idgroup = p.gr_from
                LEFT JOIN user_groups gt ON gt.idgroup = p.gr_to
                LEFT JOIN user_groups gd ON gd.idgroup = p.downgrade_gr_to";

        if (!empty($where)){
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        return $this->db->query_one($sql, $params);
    }

    function getGrPackage($idpack = 0, $lang = __SITE_LANG){
        $sql = "SELECT
                    p.*,
                    IF(ISNULL(p_i18n.description), p.description, p_i18n.description) as description,
                    IF(ISNULL(p_i18n.lang_pack), 'en', p_i18n.lang_pack) as lang_pack,
                    gf.gr_name as gf_name,
                    gf.stamp_pic as gf_stamp_pic,
                    gt.gr_name as gt_name,
                    gt.stamp_pic as gt_stamp_pic,
                    gd.gr_name as gd_name,
                    gd.stamp_pic as gd_stamp_pic,
                    gt.gr_priority,
                    per.abr,
                    per.full,
                    per.days,
                    per.fixed_end_date,
                    per.period_name
                FROM ugroup_packages p
                LEFT JOIN ugroup_packages_i18n p_i18n ON p.idpack = p_i18n.idpack AND p_i18n.lang_pack = ?
                LEFT JOIN packages_period per ON p.period = per.id
                LEFT JOIN user_groups gf ON gf.idgroup = p.gr_from
                LEFT JOIN user_groups gt ON gt.idgroup = p.gr_to
                LEFT JOIN user_groups gd ON gd.idgroup = p.downgrade_gr_to
                WHERE p.idpack = ?";
        return $this->db->query_one($sql, [$lang, $idpack]);
    }

    function getGrPackages($conditions = array()){
        $lang = __SITE_LANG;

        extract($conditions);

        $where = $params = [];

        if (isset($period)) {
			$where[] = 'p.period = ?';
            $params[] = $period;
		}

		if (isset($gr_from)) {
            $where[] = 'p.gr_from = ?';
            $params[] = $gr_from;
        }

		if (isset($gr_to)) {
            $where[] = 'p.gr_to = ?';
            $params[] = $gr_to;
        }

        if (isset($not_gr_from)) {
            $where[] = 'p.gr_from <> ?';
            $params[] = $not_gr_from;
        }

        if (isset($not_gr_to)) {
            $where[] = 'p.gr_to <> ?';
            $params[] = $not_gr_to;
        }

        if (isset($is_active)) {
            $where[] = 'p.is_active = ?';
            $params[] = $is_active;
        }

        if (isset($is_disabled)) {
            $where[] = 'p.is_disabled = ?';
            $params[] = $is_disabled;
        }

        if (isset($package_active)) {
            $where[] = 'p.package_active = ?';
            $params[] = $package_active;
        }

        if ($lang == 'en') {
            $sql = "SELECT
                        p.*,
                        'en' as lang_pack,
                        gf.gr_name as gf_name,
                        gt.gr_name as gt_name,
                        gf.stamp_pic as gf_stamp_pic,
                        gt.stamp_pic as gt_stamp_pic,
                        gt.gr_priority,
                        per.abr,
                        per.full,
                        per.days,
                        per.fixed_end_date,
                        per.period_name
                    FROM ugroup_packages p
                    LEFT JOIN packages_period per ON p.period = per.id
                    LEFT JOIN user_groups gf ON gf.idgroup = p.gr_from
                    LEFT JOIN user_groups gt ON gt.idgroup = p.gr_to";
        } else {
            $sql = "SELECT
                        p.*,
                        IF(ISNULL(p_i18n.description), p.description, p_i18n.description) as description,
                        IF(ISNULL(p_i18n.lang_pack), 'en', p_i18n.lang_pack) as lang_pack,
                        gf.gr_name as gf_name,
                        gt.gr_name as gt_name,
                        gf.stamp_pic as gf_stamp_pic,
                        gt.stamp_pic as gt_stamp_pic,
                        gt.gr_priority,
                        per.abr,
                        per.full,
                        per.days,
                        per.fixed_end_date,
                        per.period_name
                    FROM ugroup_packages p
                    LEFT JOIN ugroup_packages_i18n p_i18n ON p.idpack = p_i18n.idpack AND p_i18n.lang_pack = ?
                    LEFT JOIN packages_period per ON p.period = per.id
                    LEFT JOIN user_groups gf ON gf.idgroup = p.gr_from
                    LEFT JOIN user_groups gt ON gt.idgroup = p.gr_to";

            array_unshift($params, $lang);
        }

        if (!empty($where)) {
        	$sql .= ' WHERE ' . implode(' AND ', $where);
        }

        if (!empty($group_by)) {
        	$sql .= ' GROUP BY ' . implode(', ', $group_by);
        }

        $sql .= ' ORDER BY gt.gr_priority ASC, gf.gr_name DESC, gt.gr_name ASC, p.price ASC';

        return $this->db->query_all($sql, $params);
    }

    function getRightPackage($id){
        $sql = 'SELECT
                    rp.*,
                    pp.abr,
                    pp.full,
                    pp.days,
                    gr.gr_name,
                    gr.gr_priority,
                    r.r_name,
                    r.r_alias,
                    r.r_descr
                FROM uright_packages rp
                INNER JOIN packages_period pp ON rp.id_period = pp.id
                LEFT JOIN user_groups gr ON gr.idgroup = rp.group_for
                LEFT JOIN rights r ON rp.id_right = r.idright
                WHERE rp.idrpack = ?';
        return $this->db->query_one($sql, array($id));
    }

    function getRightPackages($gr_for = 0){
        $where = $params = [];
        $sql = 'SELECT
                    rp.*,
                    gr.gr_name,
                    gr.gr_priority,
                    r.r_name,
                    r.r_descr,
                    r.r_alias,
                    pp.abr,
                    pp.full,
                    pp.days
                FROM uright_packages rp
                INNER JOIN packages_period pp ON rp.id_period = pp.id
                LEFT JOIN user_groups gr ON gr.idgroup = rp.group_for
                INNER JOIN rights r ON rp.id_right = r.idright';

        if ($gr_for) {
        	$where[] = ' rp.group_for = ?';
            $params[] = $gr_for;
        }

        if (!empty($where)) {
        	$sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY gr.gr_priority ASC, gr.gr_name DESC, r.r_name ASC';

        return $this->db->query_all($sql, $params);
    }

    public function setGrPackage($package_info){
        return $this->db->insert('ugroup_packages',  $package_info);
    }

    public function setGrPackage_i18n($package_info_i18n){
        return $this->db->insert('ugroup_packages_i18n',  $package_info_i18n);
    }

    public function hasGrPackageI18n($package, $lang_code)
    {
        $this->db->select('COUNT(*) as AGGREGATE');
        $this->db->from($this->packages_i18n_table);
        $this->db->where('idpack = ?', (int) $package);
        $this->db->where('lang_pack = ?', (string) $lang_code);
        return (bool) (int) $this->db->query_one()['AGGREGATE'];
    }

    public function getGrPackageI18n(array $params = array())
    {
        $with = array();
        $columns = array();
        $conditions = array();

        extract($params);
		extract($conditions, EXTR_PREFIX_ALL, 'condition');
        extract($with, EXTR_PREFIX_ALL, 'with');

        $this->db->select(empty($columns) ? "*" : (is_string($columns) ? $columns : implode(', ', $columns)));
        $this->db->from("{$this->packages_i18n_table} I18N");

        if(isset($with_package) && $with_package) {
            $this->db->join("{$this->packages_table} P", "P.idpack = I18N.idpack");
            if(is_callable($with_package)) {
                $with_package($this->db, $this);
            }
        }
        if(isset($with_language) && $with_language) {
            $this->db->join("{$this->languages_table} L", "L.lang_iso2 = I18N.lang_pack");
            if(is_callable($with_language)) {
                $with_language($this->db, $this);
            }
        }

		if(isset($condition_package)){
            $this->db->where('I18N.idpack = ?', $condition_package);
		}
		if(isset($condition_packages) && is_array($condition_packages) && !empty($condition_packages)){
            $this->db->in('I18N.idpack', $condition_packages);
		}
		if(isset($condition_language)){
            $this->db->where('I18N.lang_pack = ?', $condition_language);
        }
		if(isset($condition_languages) && is_array($condition_languages) && !empty($condition_languages)){
            $this->db->in('I18N.lang_pack', $condition_languages);
        }

        return $this->db->query_one() ?: null;
    }

    public function getGrPackageI18nList(array $params = array())
    {
        $with = array();
        $columns = array();
        $conditions = array();

        extract($params);
		extract($conditions, EXTR_PREFIX_ALL, 'condition');
        extract($with, EXTR_PREFIX_ALL, 'with');

        $this->db->select(empty($columns) ? "*" : (is_string($columns) ? $columns : implode(', ', $columns)));
        $this->db->from("{$this->packages_i18n_table} I18N");

        if(isset($with_package) && $with_package) {
            $this->db->join("{$this->packages_table} P", "P.idpack = I18N.idpack");
            if(is_callable($with_package)) {
                $with_package($this->db, $this);
            }
        }
        if(isset($with_language) && $with_language) {
            $this->db->join("{$this->languages_table} L", "L.lang_iso2 = I18N.lang_pack");
            if(is_callable($with_language)) {
                $with_language($this->db, $this);
            }
        }

		if(isset($condition_package)){
            $this->db->where('I18N.idpack = ?', $condition_package);
		}
		if(isset($condition_packages) && is_array($condition_packages) && !empty($condition_packages)){
            $this->db->in('I18N.idpack', $condition_packages);
		}
		if(isset($condition_language)){
            $this->db->where('I18N.lang_pack = ?', $condition_language);
        }
		if(isset($condition_languages) && is_array($condition_languages) && !empty($condition_languages)){
            $this->db->in('I18N.lang_pack', $condition_languages);
        }

        return $this->db->query_all();
	}

    public function updateGrPackage_i18n($idpack_i18n, $package_info_i18n){
        $this->db->where('idpack_i18n', $idpack_i18n);
        return $this->db->update('ugroup_packages_i18n',  $package_info_i18n);
    }

    public function setRightPackage($package_info){
        return $this->db->insert('uright_packages',  $package_info);
    }

    public function updateGrPackage($idpack, $package_info){
        $this->db->where('idpack', $idpack);
        return $this->db->update('ugroup_packages',  $package_info);
    }

    function deleteGrPackage_i18n($idpack_i18n){
        $this->db->where('idpack_i18n', $idpack_i18n);
        return $this->db->delete('ugroup_packages_i18n');
    }

    function deleteGrPackage($idpack){
        $this->db->where('idpack', $idpack);
        return $this->db->delete('ugroup_packages');
    }

    public function updateRightPackage($idrpack, $rpackage_info){
        $this->db->where('idrpack', $idrpack);
        return $this->db->update('uright_packages',  $rpackage_info);
    }

    function deleteRightPackage($idrpack){
        $this->db->where('idrpack', $idrpack);
        return $this->db->delete('uright_packages');
    }

    function disponiblesUpgrade($user_group){
        $sql1 = 'SELECT COUNT(*) as gr_upgrade
                FROM ugroup_packages
                WHERE gr_from = ?';
        $gr_up = $this->db->query_one($sql1, array($user_group));
        $sql2 = 'SELECT COUNT(*) as right_upgrade
                FROM uright_packages
                WHERE group_for = ?';
        $r_up = $this->db->query_one($sql2, array($user_group));
        return $gr_up['gr_upgrade'] + $r_up['right_upgrade'];
    }

    public function get_gr_packages($conditions = array())
    {
        $order_by = ' idpack ASC ';
		extract($conditions);

        $where = $params = [];

        if(isset($sort_by)){
			foreach($sort_by as $sort_item){
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
			}

			$order_by = implode(',', $multi_order_by);
		}

        $sql = 'SELECT
                    p.*,
                    gf.gr_name as gf_name,
                    gt.gr_name as gt_name,
                    gt.gr_priority,
                    dgt.gr_name as downgrade_gr_name,
                    gf.stamp_pic as gf_stamp_pic,
                    gt.stamp_pic as gt_stamp_pic,
                    gt.gr_name as gt_name,
                    per.abr,
                    per.full,
                    per.days,
                    per.fixed_end_date,
                    per.period_name
                FROM ugroup_packages p
                LEFT JOIN packages_period per ON p.period = per.id
                LEFT JOIN user_groups gf ON gf.idgroup = p.gr_from
                LEFT JOIN user_groups gt ON gt.idgroup = p.gr_to
                LEFT JOIN user_groups dgt ON dgt.idgroup = p.downgrade_gr_to ';

		if (isset($gr_from)) {
			$where[] = 'p.gr_from = ?';
			$params[] = $gr_from;
		}

		if (isset($gr_to)) {
			$where[] = 'p.gr_to = ?';
			$params[] = $gr_to;
		}

		if (isset($period)) {
			$where[] = 'p.period = ?';
			$params[] = $period;
		}

		if (isset($default)) {
			$where[] = 'p.def = ?';
			$params[] = $default;
		}

        if (isset($en_updated_to)) {
            $where[] = 'p.en_updated_at < ?';
            $params[] = $en_updated_to;
        }

        if (isset($en_updated_from)) {
            $where[] = 'p.en_updated_at > ?';
            $params[] = $en_updated_from;
        }

        if (isset($translated_in)) {
            $where[] = 'JSON_CONTAINS_PATH(p.translations_data, \'one\', ?)';
            $params[] = '$.'.$translated_in;
        }

        if (isset($not_translated_in)) {
            $where[] = '(p.translations_data IS NULL OR NOT(JSON_CONTAINS_PATH(p.translations_data, \'one\', ?)))';
            $params[] = '$.'.$not_translated_in;
        }

        if (!empty($where)) {
        	$sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY ' . $order_by;

		if (isset($start, $per_p)) {
            $start = (int) $start;
            $per_p = (int) $per_p;

            $sql .= ' LIMIT ' . $start . ',' . $per_p;
		}

        return $this->db->query_all($sql, $params);
    }

    public function get_gr_packages_count($conditions = array())
    {
        extract($conditions);

        $where = $params = [];

        $sql = 'SELECT COUNT(*) as counter FROM ugroup_packages p ';

		if (isset($gr_from)) {
			$where[] = 'p.gr_from = ?';
			$params[] = $gr_from;
		}

		if (isset($gr_to)) {
			$where[] = 'p.gr_to = ?';
			$params[] = $gr_to;
		}

		if (isset($period)) {
			$where[] = 'p.period = ?';
			$params[] = $period;
		}

		if (isset($default)) {
			$where[] = 'p.def = ?';
			$params[] = $default;
        }

        if (isset($en_updated_to)) {
            $where[] = 'p.en_updated_at < ?';
            $params[] = $en_updated_to;
        }

        if (isset($en_updated_from)) {
            $where[] = 'p.en_updated_at > ?';
            $params[] = $en_updated_from;
        }

        if (isset($translated_in)) {
            $where[] = 'JSON_CONTAINS_PATH(p.translations_data, \'one\', ?)';
            $params[] = '$.'.$translated_in;
        }

        if (isset($not_translated_in)) {
            $where[] = '(p.translations_data IS NULL OR NOT(JSON_CONTAINS_PATH(p.translations_data, \'one\', ?)))';
            $params[] = '$.'.$not_translated_in;
        }

        if (!empty($where)) {
        	$sql .= ' WHERE ' . implode(' AND ', $where);
        }

        return $this->db->query_one($sql, $params)['counter'];
    }

	public function update_package($idpack, $update){
        $this->db->where('idpack', $idpack);
        return $this->db->update('ugroup_packages',  $update);
    }

	public function insert_package($insert){
        return $this->db->insert('ugroup_packages',  $insert);
    }

	public function get_rights_packages($conditions){
        extract($conditions);

        $where = $params = [];

        if (isset($sort_by)) {
			foreach ($sort_by as $sort_item) {
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
			}

			$order_by = implode(',', $multi_order_by);
		}

        if (isset($gr_from)) {
			$where[] = ' rp.group_for = ? ';
			$params[] = $gr_from;
		}

		if (isset($right)) {
			$where[] = ' rp.id_right = ?';
			$params[] = $right;
		}

        $sql = 'SELECT
					rp.*,
                    pp.abr,
                    pp.full,
                    pp.days,
                    gr.gr_name,
                    gr.gr_priority,
                    r.r_name,
                    r.r_descr
                FROM uright_packages rp
                INNER JOIN packages_period pp ON rp.id_period = pp.id
                INNER JOIN user_groups gr ON gr.idgroup = rp.group_for
                INNER JOIN rights r ON rp.id_right = r.idright';

        if (!empty($where)) {
        	$sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY ' . $order_by;
		$sql .= ' LIMIT ' . $start . ',' . $per_p;

        return $this->db->query_all($sql, $params);
    }

	public function get_rights_packages_count($conditions){
        extract($conditions);

        $where = $params = [];

        $sql = 'SELECT COUNT(*) as counter FROM uright_packages rp ';

        if (isset($gr_from)) {
			$where[] = 'rp.group_for = ?';
			$params[] = $gr_from;
		}

		if (isset($right)) {
			$where[] = 'rp.id_right = ?';
			$params[] = $right;
		}

        if (!empty($where)) {
        	$sql .= ' WHERE ' . implode(' AND ', $where);
        }

        return $this->db->query_one($sql, $params)['counter'];
    }

	public function clear_default(){
		$this->db->where('def', 1);
        return $this->db->update('ugroup_packages',  array('def' => 0));
	}

	function get_used_right_pack($gr_for = 0){
        $sql = 'SELECT GROUP_CONCAT(id_right) as ids
                FROM uright_packages
				WHERE group_for = ? ';

        return $this->db->query_one($sql, array($gr_for));
    }
}

