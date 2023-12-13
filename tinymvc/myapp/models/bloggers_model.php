<?php

/**
 * Bloggers model class.
 *
 * @author Anton Zencenco
 */
class Bloggers_Model extends TinyMVC_Model
{
    /**
     * PDO instance.
     *
     * @var null|\TinyMVC_PDO
     */
    public $db;

    /**
     * Pointer to application.
     *
     * @var \TinyMVC_Controller
     */
    protected $app;

    /**
     * Name of the table which contains the applicants records.
     *
     * @var string
     */
    protected $applicants_table = 'blogs_applicants';

    /**
     * Name of the table which contains the applicants' articles records.
     *
     * @var string
     */
    protected $articles_table = 'blogs_applicants_articles';

    /**
     * Name of the table which contains the country data.
     *
     * @var string
     */
    protected $countries_table = 'port_country';

    /**
     * Name of the table which contains the country data.
     *
     * @var string
     */
    protected $categories_table = 'blogs_category';

    /**
     * Name of the table which contains the languages data.
     *
     * @var string
     */
    protected $languages_table = 'translations_languages';

    /**
     * List of columns in applicant table which is later used on create or update.
     * Each record must be an array that have filled keys:
     *  - name: is a name of the column
     *  - fillable: indicates that column is fillable in NORMAL mode (columns with this flag set to `false` are fillable in FORCE mode).
     *  - datetime: flag which indicates that field has datetime value.
     *
     * @var array
     */
    protected $applicant_columns = array(
        array('name' => 'id_applicant', 'fillable' => false),
        array('name' => 'id_applicant_country', 'fillable' => true),
        array('name' => 'applicant_email', 'fillable' => true),
        array('name' => 'applicant_lastname', 'fillable' => true),
        array('name' => 'applicant_firstname', 'fillable' => true),
        array('name' => 'applicant_about', 'fillable' => true),
        array('name' => 'applicant_strengths', 'fillable' => true),
        array('name' => 'applicant_hobbies', 'fillable' => true),
        array('name' => 'applicant_portfolio_link', 'fillable' => true),
        array('name' => 'applicant_work_example_link', 'fillable' => true),
        array('name' => 'applicant_has_interview_opportunity', 'fillable' => true),
        array('name' => 'applicant_has_interview_experience', 'fillable' => true),
        array('name' => 'applicant_media_pages', 'fillable' => true),
        array('name' => 'applicant_access_token', 'fillable' => true),
        array('name' => 'applicant_created_at', 'fillable' => false, 'datetime' => true),
        array('name' => 'applicant_updated_at', 'fillable' => false, 'datetime' => true),
        array('name' => 'applicant_access_token_expires_at', 'fillable' => true, 'datetime' => true),
    );

    /**
     * List of columns in applicants' articles table which is later used on create or update.
     * Each record must be an array that have filled keys:
     *  - name: is a name of the column
     *  - fillable: indicates that column is fillable in NORMAL mode (columns with this flag set to `false` are fillable in FORCE mode).
     *  - datetime: flag which indicates that field has datetime value.
     *
     * @var array
     */
    protected $article_columns = array(
        array('name' => 'id_article', 'fillable'=> false),
        array('name' => 'id_applicant', 'fillable'=> true),
        array('name' => 'id_article_lang', 'fillable'=> true),
        array('name' => 'id_article_country', 'fillable'=> true),
        array('name' => 'id_article_category', 'fillable'=> true),
        array('name' => 'applicant_article_slug', 'fillable'=> true),
        array('name' => 'applicant_article_tags', 'fillable'=> true),
        array('name' => 'applicant_article_title', 'fillable'=> true),
        array('name' => 'applicant_article_photo', 'fillable'=> true),
        array('name' => 'applicant_article_status', 'fillable'=> true),
        array('name' => 'applicant_article_content', 'fillable'=> true),
        array('name' => 'applicant_article_lang_code', 'fillable'=> true),
        array('name' => 'applicant_article_description', 'fillable'=> true),
        array('name' => 'applicant_article_is_imported', 'fillable'=> true),
        array('name' => 'applicant_article_created_at', 'fillable'=> false, 'datatime' => true),
        array('name' => 'applicant_article_updated_at', 'fillable'=> false, 'datatime' => true),
    );

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        // Saving pointer to the controller instance
        $this->app = tmvc::instance()->controller;
    }

    /**
     * Checks if article record with provided ID exists in table.
     *
     * @param int|string $id record ID
     *
     * @return bool
     */
    public function is_article_exists($id)
    {
        $this->db->select('COUNT(*) as is_exsist');
        $this->db->from($this->articles_table);
        $this->db->where('id_article = ?', $id);
        if (!$this->db->query()) {
            return false;
        }

        return filter_var(((object) $this->db->getQueryResult()->fetchAssociative())->is_exsist, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Checks if applicant record with provided ID exists in table.
     *
     * @param int|string $id record ID
     *
     * @return bool
     */
    public function is_applicant_exists($id)
    {
        $this->db->select('COUNT(*) as is_exsits');
        $this->db->from($this->applicants_table);
        $this->db->where('id_applicant = ?', $id);
        if (!$this->db->query()) {
            return false;
        }

        return filter_var(((object) $this->db->getQueryResult()->fetchAssociative())->is_exsits, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Checks if applicant record with provided ID exists in table.
     *
     * @param int|string $id    record ID
     * @param mixed      $token
     *
     * @return bool
     */
    public function is_access_token_active($token)
    {
        if (empty($token)) {
            return false;
        }

        $now = new \DateTime();
        $this->db->select('COUNT(*) as is_active');
        $this->db->from($this->applicants_table);
        $this->db->where('applicant_access_token = ?', $token);
        $this->db->where('applicant_access_token_expires_at >= ?', $now->format('Y-m-d H:i:s'));
        if (!$this->db->query()) {
            return false;
        }

        return filter_var(((object) $this->db->getQueryResult()->fetchAssociative())->is_active, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Check if applicant record with provded email exists in table.
     *
     * @param string $email applicant email address
     *
     * @return bool
     */
    public function is_applicant_registered($email)
    {
        $this->db->select('COUNT(*) as is_registered');
        $this->db->from($this->applicants_table);
        $this->db->where('applicant_email = ?', $email);
        if (!$this->db->query()) {
            return false;
        }

        return filter_var(((object) $this->db->getQueryResult()->fetchAssociative())->is_registered, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Returns one applicant record that match provided ID
     * Using {@link $columns} one can return a projection of the record.
     *
     * @param int|string        $id      record ID
     * @param null|array|string $columns comma-separated string or array of column names
     *
     * @return null|array
     */
    public function find_applicant($id, $columns = null)
    {
        $this->db->select($this->resolve_columns($columns));
        $this->db->from($this->applicants_table);
        $this->db->where('id_applicant = ?', $id);
        if (!$this->db->query()) {
            return null;
        }
        $data = $this->db->getQueryResult()->fetchAssociative();

        return $data ? $data : null;
    }

    /**
     * Returns one article record that match provided ID
     * Using {@link $columns} one can return a projection of the record.
     *
     * @param int|string        $id      record ID
     * @param null|array|string $columns comma-separated string or array of column names
     *
     * @return null|array
     */
    public function find_article($id, $columns = null)
    {
        $this->db->select($this->resolve_columns($columns));
        $this->db->from($this->articles_table);
        $this->db->where('id_article = ?', $id);
        if (!$this->db->query()) {
            return null;
        }
        $data = $this->db->getQueryResult()->fetchAssociative();

        return $data ? $data : null;
    }

    /**
     * Get the first applicant that match the condition
     * Using {@link $columns} one can return a projection of the record.
     *
     * @param array             $conditions an array of conditions that filters the records
     * @param null|array|string $columns    comma-separated string or array of column names
     *
     * @return null|array
     */
    public function get_first_applicant(array $conditions = array(), $columns = null)
    {
        $this->resolve_column_ambiguity($columns, 'id_applicant', $this->applicants_table);

        extract($conditions);

        $this->db->select($this->resolve_columns($columns));
        $this->db->from($this->applicants_table);

        if (isset($email)) {
            $this->db->where('applicant_email = ?', $email);
        }

        if (isset($token)) {
            $this->db->where('applicant_access_token = ?', $token);
        }

        if (isset($active_token)) {
            $now = new \DateTime();
            $this->db->where('applicant_access_token = ?', $active_token);
            $this->db->where('applicant_access_token_expires_at > ?', $now->format('Y-m-d H:i:s'));
        }

        if (isset($firstname)) {
            $this->db->where('applicant_firstname = ?', $firstname);
        }

        if (isset($lastname)) {
            $this->db->where('applicant_lastname = ?', $lastname);
        }

        if (isset($country)) {
            $this->db->where('id_applicant_country = ?', $country);
        }
        if (isset($name)) {
            $escaped_search_string = $this->db->getConnection()->quote(trim($name));
            $search_parts = preg_split("/\b/", trim($escaped_search_string, "'"));
            $search_parts = array_map('trim', $search_parts);
            $search_parts = array_filter($search_parts);
            if (!empty($search_parts)) {
                // Drop array keys
                $search_parts = array_values($search_parts);
                // Unite words - each consecutive word have lesser contribution
                $search_condition = implode('* <', $search_parts);
                $search_condition = "'{$search_condition}*'";
                // Because tinyMVC PDO is a total mess we put WHERE condition directly
                // Property is public anyway
                $this->db->where_raw("MATCH (applicant_firstname, applicant_lastname, applicant_email) AGAINST (? IN BOOLEAN MODE)", [$search_condition]);
            }
        }

        if (isset($article)) {
            $with_article = true;
            $this->db->where('id_article = ?', $article);
        }

        if (isset($with_article) && $with_article) {
            $this->db->join($this->articles_table, "{$this->articles_table}.id_applicant = {$this->applicants_table}.id_applicant", 'LEFT');
        }

        $this->db->limit(1);
        if (!$this->db->query()) {
            return null;
        }
        $data = $this->db->getQueryResult()->fetchAssociative();

        return $data ? $data : null;
    }

    /**
     * Returns a list of articles which correspond a provided set of parameters.
     *
     * @param array $params
     */
    public function get_articles(array $params = array())
    {
        $columns = null;
        $conditions = array();
        $with = array();
        $order = array();
        $group = array();
        $limit = null;
        $skip = null;

        extract($params);
        extract($with, EXTR_PREFIX_ALL, 'with');
        extract($conditions, EXTR_PREFIX_ALL, 'condition');

        $this->resolve_column_ambiguity($columns, 'id_applicant', $this->articles_table);

        $this->db->select($this->resolve_columns($columns));
        $this->db->from($this->articles_table);

        // Preparing the conditions
        if (isset($condition_category)) {
            $condition_category = (int) $condition_category;
            if(!empty($condition_category)) {
                $this->db->where('id_article_category = ?', $condition_category);
            } else {
                $this->db->where_raw("id_article_category IS NULL");
            }
        }
        if (isset($condition_country)) {
            $with_applicant = true;
            $condition_country = (int) $condition_country;
            if(!empty($condition_country)) {
                $this->db->where('id_applicant_country = ?', $condition_country);
            } else {
                $this->db->where_raw("id_applicant_country IS NULL");
            }
        }
        if (isset($condition_lang)) {
            $condition_lang = (int) $condition_lang;
            if(!empty($condition_lang)) {
                $this->db->where('id_article_lang = ?', $condition_lang);
            } else {
                $this->db->where_raw("id_article_lang IS NULL");
            }
        }
        if (isset($condition_lang_code)) {
            $this->db->where('applicant_article_lang_code = ?', $condition_lang_code);
        }
        if (isset($condition_title)) {
            $this->db->where('applicant_article_title LIKE ?', $condition_title . '%');
        }
        if (isset($condition_created_from)) {
            $this->db->where('DATE(applicant_article_created_at) >= ?', $condition_created_from);
        }
        if (isset($condition_created_to)) {
            $this->db->where('DATE(applicant_article_created_at) <= ?', $condition_created_to);
        }
        if (isset($condition_status)) {
            $this->db->where('applicant_article_status = ?', $condition_status);
        }
        if (isset($condition_search)) {
            $escaped_search_string = $this->db->getConnection()->quote(trim($condition_search));
            $search_parts = preg_split("/\b/", trim($escaped_search_string, "'"));
            $search_parts = array_map('trim', $search_parts);
            $search_parts = array_filter($search_parts);
            if (!empty($search_parts)) {
                // Drop array keys
                $search_parts = array_values($search_parts);
                // Unite words - each consecutive word have lesser contribution
                $search_condition = implode('* <', $search_parts);
                $search_condition = "'{$search_condition}*'";
                $this->db->where_raw(
                    'MATCH (applicant_article_title, applicant_article_description, applicant_article_content, applicant_article_tags) ' .
                    "AGAINST (? IN BOOLEAN MODE)",
                    $search_condition
                );
            }
        }

        // Resolve joins
        if (isset($with_applicant) || isset($with_country)) {
            $this->db->join("{$this->applicants_table}", "{$this->applicants_table}.id_applicant = {$this->articles_table}.id_applicant", 'left');
        }
        if (isset($with_country)) {
            $this->db->join("{$this->countries_table}", "{$this->countries_table}.id = {$this->applicants_table}.id_applicant_country", 'left');
        }
        if (isset($with_category)) {
            $this->db->join("{$this->categories_table}", "{$this->categories_table}.id_category = {$this->articles_table}.id_article_category", 'left');
        }
        if (isset($with_lang)) {
            $this->db->join("{$this->languages_table}", "{$this->languages_table}.id_lang = {$this->articles_table}.id_article_lang", 'left');
        }

        // Resolve group by
        foreach ($group as $column) {
            $this->db->groupby($column);
        }

        foreach($order as $ordinance) {
            $split = explode("-", $ordinance);
            if (!empty($split[1])) {
                $this->db->orderby("{$split[0]} {$split[1]}");
            } else {
                $this->db->orderby($split[0]);
            }
        }

        // Resolve limit
        if (null !== $limit) {
            if (null !== $skip) {
                $this->db->limit($limit, $skip);
            } else {
                $this->db->limit($limit);
            }
        }

        // Fetch data
        if (!$this->db->query()) {
            return array();
        }
        $data = $this->db->getQueryResult()->fetchAllAssociative();

        return $data ? $data : array();
    }

    /**
     * Get the first article that match the condition
     * Using {@link $columns} one can return a projection of the record.
     *
     * @param array             $conditions an array of conditions that filters the records
     * @param null|array|string $columns    comma-separated string or array of column names
     *
     * @return null|array
     */
    public function get_first_article(array $conditions = array(), $columns = null)
    {
        $this->resolve_column_ambiguity($columns, 'id_applicant', $this->articles_table);

        extract($conditions);

        $this->db->select($this->resolve_columns($columns));
        $this->db->from($this->articles_table);

        // Preparing the conditions
        if (isset($applicant)) {
            $this->db->where("{$this->articles_table}.id_applicant = ?", $applicant);
        }
        if (isset($condition_category)) {
            $condition_category = (int) $condition_category;
            if(!empty($condition_category)) {
                $this->db->where('id_article_category = ?', $condition_category);
            } else {
                $this->db->where_raw("id_article_category IS NULL");
            }
        }
        if (isset($condition_country)) {
            $with_applicant = true;
            $condition_country = (int) $condition_country;
            if(!empty($condition_country)) {
                $this->db->where('id_applicant_country = ?', $condition_country);
            } else {
                $this->db->where_raw("id_applicant_country IS NULL");
            }
        }
        if (isset($lang)) {
            if(!empty($lang)) {
                $this->db->where('id_article_lang = ?', $lang);
            } else {
                $this->db->where_raw("id_article_lang IS NULL");
            }
        }
        if (isset($lang_code)) {
            $this->db->where('applicant_article_lang_code = ?', $lang_code);
        }
        if (isset($title)) {
            $this->db->where('applicant_article_title LIKE ?', $title . '%');
        }
        if (isset($status)) {
            $this->db->where('applicant_article_status = ?', $status);
        }
        if (isset($tags)) {
            $tags = explode(',', $tags);
            $tags_condition = '';
            $tags = array_map(function ($entry) use ($tags_condition) {
                $prefix = '';
                $entry = trim($entry);
                if (!empty($tags_condition)) {
                    $prefix = 'OR';
                }
                $tags_condition = "{$tags_condition} {$prefix} applicant_article_tags LIKE %?%";

                return $entry;
            }, $tags);

            if (!empty($tags)) {
                $this->db->where($tags_condition, $tags);
            }
        }
        if (isset($search)) {
            $escaped_search_string = $this->db->getConnection()->quote(trim($search));
            $search_parts = preg_split("/\b/", trim($escaped_search_string, "'"));
            $search_parts = array_map('trim', $search_parts);
            $search_parts = array_filter($search_parts);
            if (!empty($search_parts)) {
                // Drop array keys
                $search_parts = array_values($search_parts);
                // Unite words - each consecutive word have lesser contribution
                $search_condition = implode('* <', $search_parts);
                $search_condition = "'{$search_condition}*'";
                $this->db->where_raw(
                    'MATCH (applicant_article_title, applicant_article_description, applicant_article_content, applicant_article_tags) ' .
                    "AGAINST (? IN BOOLEAN MODE)",
                    $search_condition
                );
            }
        }

        // Resolve joins
        if (isset($with_applicant) && $with_applicant) {
            $this->db->join($this->applicants_table, "{$this->applicants_table}.id_applicant = {$this->articles_table}.id_applicant", 'LEFT');
        }

        $this->db->limit(1);
        if (!$this->db->query()) {
            return null;
        }
        $data = $this->db->getQueryResult()->fetchAssociative();

        return $data ? $data : null;
    }

    public function get_article_status_map()
    {
        $this->db->select('COUNT(`applicant_article_status`) as `amount`, `applicant_article_status` as `name`');
        $this->db->from($this->articles_table);
        $this->db->groupby('applicant_article_status');
        if (!$this->db->query()) {
            return array();
        }
        $data = $this->db->getQueryResult()->fetchAllAssociative();
        $data = $data ? array_column($data, 'amount', 'name') : array();

        return array_map('intval', $data);
    }

    public function get_last_article_id()
    {
        $this->db->select('id_article');
        $this->db->from($this->articles_table);
        $this->db->orderby('id_article desc');
        $this->db->limit(1);
        if (!$this->db->query()) {
            return null;
        }

        return (int) ((object) $this->db->getQueryResult()->fetchAssociative())->id_article;
    }

    /**
     * Counts articles which correspond a provided set of parameters.
     *
     * @param array $params
     */
    public function count_articles(array $params = array())
    {
        $conditions = array();
        $group = array();
        $with = array();

        extract($params);
        extract($with, EXTR_PREFIX_ALL, 'with');
        extract($conditions, EXTR_PREFIX_ALL, 'condition');

        $this->db->select('COUNT(*) as counter');
        $this->db->from($this->articles_table);

        // Preparing the conditions
        if (isset($condition_category)) {
            $condition_category = (int) $condition_category;
            if(!empty($condition_category)) {
                $this->db->where('id_article_category = ?', $condition_category);
            } else {
                $this->db->where_raw("id_article_category IS NULL");
            }
        }
        if (isset($condition_country)) {
            $with_applicant = true;
            $condition_country = (int) $condition_country;
            if(!empty($condition_country)) {
                $this->db->where('id_applicant_country = ?', $condition_country);
            } else {
                $this->db->where_raw("id_applicant_country IS NULL");
            }
        }
        if (isset($condition_lang)) {
            $condition_lang = (int) $condition_lang;
            if(!empty($condition_lang)) {
                $this->db->where('id_article_lang = ?', $condition_lang);
            } else {
                $this->db->where_raw("id_article_lang IS NULL");
            }
        }
        if (isset($condition_lang_code)) {
            $this->db->where('applicant_article_lang_code = ?', $condition_lang_code);
        }
        if (isset($condition_title)) {
            $this->db->where('applicant_article_title LIKE ?', $condition_title . '%');
        }
        if (isset($condition_created_from)) {
            $this->db->where('DATE(applicant_article_created_at) >= ?', $condition_created_from);
        }
        if (isset($condition_created_to)) {
            $this->db->where('DATE(applicant_article_created_at) <= ?', $condition_created_to);
        }
        if (isset($condition_status)) {
            $this->db->where('applicant_article_status = ?', $condition_status);
        }
        if (isset($condition_search)) {
            $escaped_search_string = $this->db->getConnection()->quote(trim($condition_search));
            $search_parts = preg_split("/\b/", trim($escaped_search_string, "'"));
            $search_parts = array_map('trim', $search_parts);
            $search_parts = array_filter($search_parts);
            if (!empty($search_parts)) {
                // Drop array keys
                $search_parts = array_values($search_parts);
                // Unite words - each consecutive word have lesser contribution
                $search_condition = implode('* <', $search_parts);
                $search_condition = "'{$search_condition}*'";
                $this->db->where_raw(
                    'MATCH (applicant_article_title, applicant_article_description, applicant_article_content, applicant_article_tags) ' .
                    "AGAINST (? IN BOOLEAN MODE)",
                    $search_condition
                );
            }
        }

        // Resolve joins
        if (isset($with_applicant) || isset($with_country)) {
            $this->db->join("{$this->applicants_table}", "{$this->applicants_table}.id_applicant = {$this->articles_table}.id_applicant", 'left');
        }
        if (isset($with_country)) {
            $this->db->join("{$this->countries_table}", "{$this->countries_table}.id = {$this->applicants_table}.id_applicant_country", 'left');
        }
        if (isset($with_category)) {
            $this->db->join("{$this->categories_table}", "{$this->categories_table}.id_category = {$this->articles_table}.id_article_category", 'left');
        }
        if (isset($with_lang)) {
            $this->db->join("{$this->languages_table}", "{$this->languages_table}.id_lang = {$this->articles_table}.id_article_lang", 'left');
        }

        // Resolve group by
        foreach ($group as $column) {
            $this->db->groupby($column);
        }

        // Fetch data
        if (!$this->db->query()) {
            return 0;
        }

        return (int) ((object) $this->db->getQueryResult()->fetchAssociative())->counter;
    }

    public function count_new_articles($last_id = null)
    {
        $this->db->select('COUNT(*) as counter');
        $this->db->from($this->articles_table);
        $this->db->where('applicant_article_status = ?', 'new');
        if (null !== $last_id) {
            $this->db->where('id_article >', (int) $last_id);
        }

        // Fetch data
        if (!$this->db->query()) {
            return 0;
        }

        return (int) ((object) $this->db->getQueryResult()->fetchAssociative())->counter;
    }

    /**
     * Create one applicant record with provided data.
     * By deafult only columns activated (value is set to `true`) in {@link self::$applicant_columns} will be used.
     * When force mode is activated all columns specified in {@link self::$applicant_columns} will be used.
     *
     * @param array|\ArrayObject|\IteratorAggregate|\stdClass $data  a set of applicant data
     * @param bool                                            $force a flag that indicates if force mode is enabled/disabled
     *
     * @return bool|int
     */
    public function create_applicant($data, $force = false)
    {
        return $this->db->insert(
            $this->applicants_table,
            $this->prepare_data(
                $data,
                $this->applicant_columns,
                $force
            )
        );
    }

    /**
     * Create one article record with provided data.
     * By deafult only columns activated (value is set to `true`) in {@link self::$article_columns} will be used.
     * When force mode is activated all columns specified in {@link self::$article_columns} will be used.
     *
     * @param array|\ArrayObject|\IteratorAggregate|\stdClass $data  a set of article data
     * @param bool                                            $force a flag that indicates if force mode is enabled/disabled
     *
     * @return bool|int
     */
    public function create_article($data, $force = false)
    {
        return $this->db->insert(
            $this->articles_table,
            $this->prepare_data(
                $data,
                $this->article_columns,
                $force
            )
        );
    }

    /**
     * Update one specific applicant record with provided data.
     * By deafult only columns activated (value is set to `true`) in {@link self::$applicant_columns} will be used.
     * When force mode is activated all columns specified in {@link self::$applicant_columns} will be used.
     *
     * @param int                                             $id    is a record ID
     * @param array|\ArrayObject|\IteratorAggregate|\stdClass $data  a set of applicant data
     * @param bool                                            $force a flag that indicates if force mode is enabled/disabled
     *
     * @return bool
     */
    public function update_applicant($id, $data, $force = false)
    {
        $this->db->where('id_applicant = ?', $id);

        return $this->db->update(
            $this->applicants_table,
            $this->prepare_data(
                $data,
                $this->applicant_columns,
                $force
            )
        );
    }

    /**
     * Update one specific article record with provided data.
     * By deafult only columns activated (value is set to `true`) in {@link self::$article_columns} will be used.
     * When force mode is activated all columns specified in {@link self::$article_columns} will be used.
     *
     * @param int                                             $id    is a record ID
     * @param array|\ArrayObject|\IteratorAggregate|\stdClass $data  a set of article data
     * @param bool                                            $force a flag that indicates if force mode is enabled/disabled
     *
     * @return bool
     */
    public function update_article($id, $data, $force = false)
    {
        $this->db->where('id_article = ?', $id);

        return $this->db->update(
            $this->articles_table,
            $this->prepare_data(
                $data,
                $this->article_columns,
                $force
            )
        );
    }

    public function remove_applicant($id)
    {
        $this->db->where('id_applicant', (int) $id);

        return $this->db->delete($this->applicants_table);
    }

    public function remove_article($id)
    {
        $this->db->where('id_article', (int) $id);

        return $this->db->delete($this->articles_table);
    }

    public function remove_application($applicant = null, $article = null)
    {
        $params = array();
        $conditions = array();
        if (null !== $applicant) {
            $params[] = (int) $applicant;
            $conditions[] = "{$this->prefix_column('applicant', 'id_applicant')} = ?";
        }
        if (null !== $article) {
            $params[] = (int) $article;
            $conditions[] = "{$this->prefix_column('article', 'id_article')} = ?";
        }

        $conditions = implode('AND', $conditions);
        $conditions = !empty($conditions) ? "WHERE {$conditions}" : '';

        return $this->db->query("
            DELETE blogs_applicants, blogs_applicants_articles
            FROM blogs_applicants
            INNER JOIN blogs_applicants_articles ON blogs_applicants.id_applicant = blogs_applicants_articles.id_applicant
            {$conditions}
        ", $params);
    }

    public function prefix_column($relation, $column)
    {
        switch ($relation) {
            case 'applicant':
                return "{$this->applicants_table}.{$column}";
            case 'article':
                return "{$this->articles_table}.{$column}";
            case 'country':
                return "{$this->countries_table}.{$column}";
            case 'category':
                return "{$this->categories_table}.{$column}";
            case 'lang':
                return "{$this->languages_table}.{$column}";
            default:
                $property_name = "{$relation}_table";
                if (isset($this->{$property_name}) && is_string($this->{$property_name})) {
                    $relation = $this->{$property_name};
                }

                return "{$relation}.{$column}";
        }
    }

    /**
     * Transforms provided {@link $columns} variable into a valid string.
     *
     * @param null|array|string $columns comma-separated string or array of column names
     *
     * @return string
     */
    private function resolve_columns($columns)
    {
        if (null === $columns) {
            return '*';
        }

        if (!(is_string($columns) || is_array($columns))) {
            $current_type = gettype($columns);

            throw new \InvalidArgumentException(
                "Invalid argument for column projection provided - string or array expected, got {$current_type}"
            );
        }

        return is_string($columns) ? $columns : implode(', ', $columns);
    }

    /**
     * Prepends column in the FROM caluse with preferred table name to escape columns name ambiguity in the queries.
     *
     * @param null|array|string $from_clause     is a non-escaped FROM clause
     * @param string            $column          a column that must be prefixed
     * @param string            $preferred_table table name which will be used as prefix
     */
    private function resolve_column_ambiguity(&$from_clause, $column, $preferred_table)
    {
        if (empty($from_clause)) {
            return;
        }

        if (
            is_string($from_clause) &&
            false !== strpos($from_clause, $column)
        ) {
            $from_clause = preg_replace("/([^\.]({$column})\b)/", "{$preferred_table}.$2", $from_clause);
        }

        if (is_array($from_clause) && false !== array_search($column, $from_clause)) {
            $keys = array_keys($from_clause, $column);
            foreach ($keys as $key) {
                if (!isset($from_clause[$key])) {
                    continue;
                }

                if ($column === $from_clause[$key]) {
                    $from_clause[$key] = "{$preferred_table}.{$column}";

                    continue;
                }

                if (is_string($from_clause[$key]) && false !== strpos($from_clause[$key], $column)) {
                    $from_clause[$key] = preg_replace("/([^\.]({$column})\b)/", "{$preferred_table}.$2", $from_clause[$key]);
                }
            }
        }
    }

    /**
     * Prepares raw applicant data be used on create or update.
     *
     * @param array|\ArrayObject|\IteratorAggregate|\stdClass $data    a set of applicant data
     * @param bool                                            $force   a flag that indicates if force mode is enabled/disabled
     * @param mixed                                           $columns
     *
     * @return array
     */
    private function prepare_data($data, $columns, $force = false)
    {
        $allowed_columns = $columns;
        if (!$force) {
            $allowed_columns = array_filter($allowed_columns, function ($column) {
                return isset($column['fillable']) && $column['fillable'];
            });
        }
        $allowed_columns_list = array_column($allowed_columns, 'name');
        $processed_data = array_intersect_key($this->morph_to_array($data), array_flip($allowed_columns_list));

        // Ensure that all datetime fields
        $date_checks = array_filter($allowed_columns, function ($column) {
            return isset($column['datetime']) && $column['datetime'];
        });
        if (!empty($date_checks)) {
            foreach ($date_checks as $column) {
                $name = $column['name'];
                if (isset($processed_data[$name]) && !is_string($processed_data[$name])) {
                    $processed_data[$name] = $this->morph_to_datetime($processed_data[$name]);
                }
            }
        }

        // Replace/Add article slug
        if (isset($processed_data['applicant_article_title'])) {
            $processed_data['applicant_article_slug'] = strForURL($processed_data['applicant_article_title']);
        }
        // Encode media pages object
        if (isset($processed_data['applicant_media_pages'])) {
            $processed_data['applicant_media_pages'] = json_encode($processed_data['applicant_media_pages'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        return $processed_data;
    }

    /**
     * Trying to morph a value to array.
     *
     * @param array|\ArrayObject|\IteratorAggregate|\stdClass $raw_array a set or arrayable data
     *
     * @return array
     */
    private function morph_to_array($raw_array)
    {
        if (is_array($raw_array)) {
            return $raw_array;
        }
        if (is_object($raw_array) && method_exists($raw_array, 'toArray')) {
            return $raw_array->toArray();
        }
        if ($raw_array instanceof \ArrayObject) {
            return $raw_array->getArrayCopy();
        }

        return  $raw_array instanceof \IteratorAggregate ? iterator_to_array($raw_array) : (array) $raw_array;
    }

    /**
     * Morphs datetime value into DB compatible form
     * Returns null if cannot transform value or on failure.
     *
     * @param \DateTime|int $datetime
     *
     * @return null|string
     */
    private function morph_to_datetime($datetime)
    {
        if ($datetime instanceof \DateTime) {
            return $datetime->format('Y-m-d H:i:s');
        }

        if ($datetime instanceof \DateTimeImmutable) {
            return $datetime->format('Y-m-d H:i:s');
        }

        if (is_int($datetime)) {
            try {
                $morphed_date = new \DateTime($datetime);
            } catch (\Exception $exception) {
                return null;
            }

            return $morphed_date->format('Y-m-d H:i:s');
        }

        return null;
    }
}
