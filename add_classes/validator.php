<?php

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;

class Validator
{
    public $validate_data = [];
    private $_field_data = [];
    private $_error_messages = [];
    private $_error_prefix = '<p>';
    private $_error_suffix = '</p>';

    private $_errors_lang_file = '/validator_messages_en.php';

    private $_messages = [];
    private $patterns = [
        'index_name' => '/^[\\w\\d\\-_]*$/',
    ];

    public function __construct()
    {
        if (file_exists(__DIR__ . $this->_errors_lang_file)) {
            require __DIR__ . $this->_errors_lang_file;
            $this->_messages = $messages;
        }

        if (empty($this->validate_data)) {
            $this->validate_data = $_POST;
        }
    }

    /**
     *set messages file.
     *
     * @param mixed $file_path
     */
    public function _set_messages_lang_file($file_path)
    {
        if (file_exists($file_path)) {
            $this->_errors_lang_file = $file_path;

            require $this->_errors_lang_file; 	//upload language messages
            $this->_messages = $messages;
        }
    }

    /**
     *set rules.
     *
     * @param mixed $field
     * @param mixed $label
     * @param mixed $rules
     */
    public function set_rules($field, $label = '', $rules = '')
    {
        //if is not validate_data
        // if (0 == sizeof($this->validate_data)) {
        //     return;
        // }

        //if rules is in array
        if (is_array($field)) {
            foreach ($field as $row) {
                if (!isset($row['field']) or !isset($row['rules'])) {
                    continue;
                }

                //if label is not set we use the field
                $label = (!isset($row['label'])) ? $row['field'] : $row['label'];

                $this->set_rules($row['field'], $label, $row['rules']);
            }

            return;
        }

        //ules need to be in array but fiealds as string
        if ($rules instanceof \Traversable) {
            $rules = \iterator_to_array($rules);
        }
        if (!is_string($field) or !is_array($rules) or '' == $field) {
            return;
        }

        //if label is not set we use the field
        $label = ('' == $label) ? $field : $label;

        $this->_field_data[$field] = [
            'field'		  => $field,
            'label'		  => $label,
            'rules'		  => $rules,
            'postdata'	=> null,
            'error'		  => '',
        ];
    }

    /**
     * do validation.
     */
    public function validate()
    {
        //if is not POST
        // if (0 == sizeof($this->validate_data)) {
        //     return false;
        // }

        //if is not field data
        if (0 == sizeof($this->_field_data)) {
            return false;
        }

        foreach ($this->_field_data as $field => $row) {
            $this->_field_data[$field]['postdata'] = (isset($this->validate_data[$field])) ? $this->validate_data[$field] : null;

            //check rules
            $this->checkrule($row, $this->_field_data[$field]['postdata']);
        }

        $total_errors = sizeof($this->_error_messages);

        if (0 == $total_errors) {
            return true;
        }

        return false;
    }

    /**
     * check validation rule.
     *
     * @param mixed $field
     * @param mixed $postdata
     */
    public function checkrule($field, $postdata)
    {
        // $postdata = is_array($postdata) && empty($postdata) ? '' : $postdata;

        if (is_array($postdata)) {
            foreach ($postdata as $key => $val) {
                $this->checkrule($field, $val);
            }

            return;
        }

        foreach ($field['rules'] as $rule => $message) {
            $param = false;

            if (!is_string($message) && $message instanceof \Closure) {
                $message(
                    $field['label'],
                    $postdata,
                    function ($error) use ($field) {
                        $field_name = $field['field'];
                        $this->_field_data[$field_name]['error'] = $error;
                        if (!isset($this->_error_messages[$field_name])) {
                            $this->_error_messages[$field_name] = $error;
                        }
                    },
                    $this
                );

                continue;
            }

            if (!is_string($rule)) {
                continue;
            }

            if (preg_match('/(.*?)\\[(.*?)\\]/', $rule, $match)) {
                $rule = $match[1];
                $field['param'] = $param = $match[2];
            }

            if (!method_exists($this, $rule)) {
                if (function_exists($rule)) {
                    $result = $rule($postdata);

                    $postdata = (is_bool($result)) ? $postdata : $result;
                    $this->set_field_postdata($field['field'], $postdata);

                    continue;
                }
            } else {
                $result = $this->{$rule}($postdata, $param);
            }
            $postdata = (is_bool($result)) ? $postdata : $result;
            $this->set_field_postdata($field['field'], $postdata);

            //if is not valid
            if (false === $result && '' != $message) {
                //set error message
                $error = sprintf($message, $field['label']);

                $this->_field_data[$field['field']]['error'] = $error;

                if (!isset($this->_error_messages[$field['field']])) {
                    $this->_error_messages[$field['field']] = $error;
                }
            } elseif (false === $result && '' == $message) {
                if (empty($this->_error_messages[$field['field']])) {
                    $this->_error_messages[$field['field']] = $this->_get_message($rule, $field);
                }
            }

            continue;
        }
    }

    /**
     * get POST for each element.
     *
     * @param mixed $field
     */
    public function postdata($field)
    {
        if (isset($this->_field_data[$field]['postdata'])) {
            return $this->_field_data[$field]['postdata'];
        }

        return false;
    }

    /**
     * reset postdata.
     */
    public function reset_postdata()
    {
        $this->_field_data = [];
    }

    /**
     * return messages in string.
     *
     * @param mixed $prefix
     * @param mixed $suffix
     */
    public function get_string_errors($prefix = '', $suffix = '')
    {
        if (0 === count($this->_error_messages)) {
            return '';
        }

        if ('' == $prefix) {
            $prefix = $this->_error_prefix;
        }

        if ('' == $suffix) {
            $suffix = $this->_error_suffix;
        }

        //concat errors messages
        $str = '';
        foreach ($this->_error_messages as $val) {
            if ('' != $val) {
                $str .= $prefix . $val . $suffix . "\n";
            }
        }

        return $str;
    }

    /**
     * return messages in array.
     */
    public function get_array_errors()
    {
        return $this->_error_messages;
    }

    /**
     * return messages in array.
     */
    public function clear_array_errors()
    {
        $this->_error_messages = [];
    }

    /**
     * return error for one field.
     *
     * @param string
     * @param mixed $field
     */
    public function form_error($field)
    {
        if (isset($this->_error_messages[$field])) {
            return $this->_error_prefix . $this->_error_messages[$field] . $this->_error_suffix;
        }

        return false;
    }

    /**
     * set delimiters for return of errors.
     *
     * @params strings
     *
     * @param mixed $prefix
     * @param mixed $suffix
     */
    public function set_error_delimiters($prefix = '<p>', $suffix = '</p>')
    {
        $this->_error_prefix = $prefix;
        $this->_error_suffix = $suffix;
    }

    /**
     * Значение не может быть пустым
     *
     * @param	string
     * @param mixed $str
     *
     * @return bool
     *
     * @deprecated
     */
    public function required($str)
    {
        if (!is_array($str) && !is_object($str)) {
            // remove HTML TAGs
            $str = preg_replace('(<([^>]+)>)', '', $str);

            // remove control characters
            // replace with empty space
            $str = str_replace('&nbsp;', ' ', $str);
            // replace with empty space
            $str = str_replace("\r", ' ', $str);
            // replace with space
            $str = str_replace("\n", ' ', $str);
            // replace with space
            $str = str_replace("\t", ' ', $str);
            // remove multiple spaces
            $str = trim(preg_replace('/ {2,}/', ' ', $str));

            return ('' == $str) ? false : true;
        }

        return !empty($str);
    }

    /**
     * The value most not be empty.
     *
     * @param mixed $value
     */
    public function not_empty($value): bool
    {
        if (
            is_string($value)
            || $value instanceof Stringable
            || (is_object($value) && method_exists($value, '__toString'))
        ) {
            $value = trim((string) $value);
        }

        return !empty($value);
    }

    /**
     * The value most not be blank (empty string, zero etc.).
     *
     * @param mixed $value
     * @param mixed $allowNull
     */
    public function not_blank($value, $allowNull = false): bool
    {
        if (null === $value && filter_var($allowNull, FILTER_VALIDATE_BOOL)) {
            return true;
        }

        if (is_numeric($value)) {
            return 0 != $value;
        }

        if (
            is_string($value)
            || $value instanceof Stringable
            || (is_object($value) && method_exists($value, '__toString'))
        ) {
            $value = trim((string) $value);
        }

        if ($value instanceof stdClass) {
            $value = (array) $value;
        }

        if (is_array($value)) {
            $value = array_filter($value, __METHOD__);
        }

        return !empty($value);
    }

    /**
     * check integer.
     *
     * @param string
     * @param mixed $str
     */
    public function integer($str)
    {
        if (empty($str)) {
            return true;
        }

        return false !== filter_var($str, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    }

    /**
     * check integer.
     *
     * @param string
     * @param mixed $str
     */
    public function is_number($str)
    {
        if (empty($str)) {
            return true;
        }

        return is_numeric($str);
    }

    /**
     * check natural.
     *
     * @param string
     * @param mixed $str
     */
    public function natural($str)
    {
        if (empty($str)) {
            return true;
        }

        if ((int) $str >= 0) {
            return true;
        }

        return false;
    }

    /**
     * Is a Natural number, but not a zero  (1,2,3, etc.).
     *
     * @param	string
     * @param mixed $str
     *
     * @return bool
     */
    public function is_natural_no_zero($str)
    {
        return 0 != $str && ctype_digit((string) $str);
    }

    /**
     * check float.
     *
     * @param string
     * @param mixed $str
     */
    public function float($str)
    {
        if (empty($str)) {
            return true;
        }

        return false !== filter_var($str, FILTER_VALIDATE_FLOAT);
    }

    /**
     * check float.
     *
     * @param string
     * @param mixed $str
     */
    public function positive_number($str)
    {
        if (empty($str)) {
            return true;
        }

        return (!preg_match('/^(([0]{1})|([1-9]{1}[0-9]{0,11}))(\\.([0-9]{1,2}))?$/', $str)) ? false : true;
    }

    /**
     * check float.
     *
     * @param string
     * @param mixed $str
     */
    public function item_size($str)
    {
        if (empty($str)) {
            return true;
        }
        //old validation, allows 0
        //return (!preg_match("/^(([0]{1})|([1-9]{1}[0-9]{0,3}))(\.([0-9]{1,2}))?$/", $str)) ? false : true;
        return (!preg_match('/^(?=.*[1-9])\\d{0,4}(?:\\.\\d{0,2})?$/', $str)) ? false : true;
    }

    public function item_sizes($str)
    {
        if (empty($str)) {
            return true;
        }

        list($length, $width, $height) = explode('x', (string) $str);
        foreach ([$length, $width, $height] as $dimension) {
            $dimension = trim($dimension);
            if (
                !$this->is_number($dimension)
                || !$this->float($dimension)
                || !$this->min($dimension, '0.01')
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * check URL.
     *
     * @param string
     * @param mixed $str
     */
    public function valid_url($str)
    {
        if (empty($str)) {
            return true;
        }

        return (!preg_match('/^\\b(?:(?:https?):\\/\\/|www\\.)[-a-z0-9+&@#\\/%?=~_|!:,.;]*[-a-z0-9+&@#\\/%=~_|]/i', $str)) ? false : true;
    }

    /**
     * check email.
     *
     * @param string
     * @param mixed $str
     */
    public function valid_email($str)
    {
        $pattern = '/^(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){255,})(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){65,}@)(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22))(?:\.(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-[a-z0-9]+)*\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-[a-z0-9]+)*)|(?:\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\]))$/iD';
        if (empty($str)) {
            return true;
        }

        return (bool) preg_match($pattern, $str);
    }

    /**
     * Check if no whitespaces.
     *
     * @param string
     * @param mixed $str
     */
    public function no_whitespaces($str)
    {
        return trim($str) === $str;
    }

    /**
     * check email.
     *
     * @param string
     * @param mixed $str
     */
    public function valid_emails($str)
    {
        if (empty($str)) {
            return true;
        }

        $parts = explode(',', $str);
        foreach ($parts as $email) {
            if (!$this->valid_email(trim($email))) {
                return false;
            }
        }

        return true;
    }

    /**
     * check email.
     *
     * @param string
     * @param mixed      $str
     * @param null|mixed $count
     */
    public function max_emails_count($str, $count = null)
    {
        if (null == $count) {
            return false;
        }

        $str = explode(',', $str);
        $str = array_filter($str);

        return (count($str) > $count) ? false : true;
    }

    /**
     * check IP.
     *
     * @param string
     * @param mixed $str
     */
    public function valid_ip($str)
    {
        if (empty($str)) {
            return true;
        }

        return filter_var($str, FILTER_VALIDATE_IP);
    }

    /**
     * Match one field to another.
     *
     * @param	string
     * @param	field
     * @param mixed $str
     * @param mixed $field
     *
     * @return bool
     */
    public function matches($str, $field)
    {
        if (!isset($this->validate_data[$field])) {
            return false;
        }

        $field = $this->validate_data[$field];

        return ($str !== $field) ? false : true;
    }

    /**
     * only alpha and space (for full name).
     *
     * @param	string
     * @param mixed $str
     *
     * @return bool
     */
    public function valide_title($str)
    {
        if (empty($str)) {
            return true;
        }

        return (!preg_match('/^([0-9A-Z\/\+\-_\.\,\:\'\ \;\(\)\#\%]?)+$/i', $str)) ? false : true;
    }

    /**
     * Check if tag is valid.
     *
     * @param	string
     * @param mixed $str
     *
     * @return bool
     */
    public function valid_tag($str)
    {
        if (empty($str)) {
            return true;
        }

        return !preg_match('/^([A-Za-z0-9\-\s]){3,30}+$/i', $str) ? false : true;
    }

    public function company_title($str)
    {
        if (empty($str)) {
            return true;
        }

        return (!preg_match("/^[A-Za-z0-9][0-9A-Za-z-_., '&()]+$/", $str)) ? false : true;
    }

    public function company_index_name($str)
    {
        if (empty($str)) {
            return true;
        }

        if (!preg_match($this->patterns['index_name'], $str)) {
            return false;
        }

        if (not_allowed_url($str)) {
            return false;
        }

        $instance = tmvc::instance()->controller;
        $instance->load->model('Company_Model', 'company');
        if ((int) $instance->company->exist_company(['index_name' => $str])) {
            return false;
        }

        return true;
    }

    public function company_index_name_valid($str, $company_id)
    {
        if (empty($str)) {
            return true;
        }

        if (!preg_match($this->patterns['index_name'], $str)) {
            return false;
        }

        if (not_allowed_url($str)) {
            return false;
        }

        return true;
    }

    public function company_index_name_not_taken($str, $company_id)
    {
        if (empty($str)) {
            return true;
        }

        if (model('company')->exist_company(['index_name' => $str, 'index_name_temp' => $str, 'not_company' => $company_id])) {
            return false;
        }

        return true;
    }

    public function alpha($str)
    {
        return (!preg_match('/^([A-Za-z\\s])+$/i', $str)) ? false : true;
    }

    public function for_url($str)
    {
        return (!preg_match('/^([A-Za-z_\\s])+$/i', $str)) ? false : true;
    }

    public function iframe($str)
    {
        if (empty($str)) {
            return;
        }

        return (!preg_match('/^<iframe.*src="(.*)".*><\\/iframe>$/', $str)) ? false : true;
    }

    /**
     * max number of chars, in simple text.
     *
     * @param	int
     * @param mixed      $str
     * @param null|mixed $count
     *
     * @return bool
     */
    public function max_len($str, $count = null)
    {
        if (empty($str)) {
            return true;
        }

        if (null == $count) {
            return false;
        }

        $str = preg_replace('/\s*\n|\r|\r\n+/', ' ', $str);
        $str = str_replace('&nbsp;', ' ', $str);

        return (mb_strlen($str) > (int) $count) ? false : true;
    }

    /**
     * max number of chars, in html text without html tags.
     *
     * @param	int
     * @param mixed      $html
     * @param null|mixed $count
     *
     * @return bool
     */
    public function html_max_len($html, $count = null)
    {
        $str = strip_tags($html);

        return $this->max_len($str, $count);
    }

    /**
     * min number of chars, in simple text.
     *
     * @param	int
     * @param mixed      $str
     * @param null|mixed $count
     *
     * @return bool
     */
    public function min_len($str, $count = null)
    {
        if (null == $count) {
            return false;
        }

        return (mb_strlen($str) < $count) ? false : true;
    }

    /**
     * min number of chars, in html text without html tags.
     *
     * @param	int
     * @param mixed      $html
     * @param null|mixed $count
     *
     * @return bool
     */
    public function html_min_len($html, $count = null)
    {
        $str = strip_tags($html);

        return $this->min_len($str, $count);
    }

    /**
     * required preg_match.
     *
     * @param	int
     * @param mixed      $str
     * @param null|mixed $option
     *
     * @return bool
     */
    public function pattern_match($str, $option = null)
    {
        if (null == $option) {
            return false;
        }

        return (!preg_match($this->patterns[$option], $str)) ? false : true;
    }

    /**
     * only alpha, bynver, space and some (for address).
     *
     * @param mixed $str
     *
     * @return bool
     */
    public function alpha_numeric($str)
    {
        if (empty($str)) {
            return true;
        }

        return (!preg_match("/^([A-Za-z0-9 '-.])+$/i", $str)) ? false : true;
    }

    public function valid_password($str)
    {
        $valid_password = preg_match('/^(?=.*\d)(?=.*[A-Z])(?=.*[a-z]).{6,30}$/', $str);
        if (!$valid_password || mb_strlen($str) < 6 || mb_strlen($str) > 30) {
            return false;
        }

        return true;
    }

    public function numeric($str)
    {
        if (empty($str)) {
            return true;
        }

        return (!preg_match('/^([\\d])+$/i', $str)) ? false : true;
    }

    public function zip_code($str)
    {
        if (empty($str)) {
            return true;
        }

        return (!preg_match('/^[0-9A-Za-z\\-\\. ]{3,20}$/i', $str)) ? false : true;
    }

    public function min($str, $min)
    {
        if (!is_numeric($str)) {
            return empty($str);
        }

        if (false === filter_var($str, FILTER_VALIDATE_INT) || false === filter_var($min, FILTER_VALIDATE_INT)) {
            if (false !== filter_var($str, FILTER_VALIDATE_FLOAT)) {
                return compareFloatNumbers($str, $min, '>=', 1e-18);
            }

            return false;
        }

        return $str >= (int) $min;
    }

    public function less_than($str, $min)
    {
        if (empty($str) && !is_numeric($str)) {
            return true;
        }

        if (!is_numeric($str)) {
            return false;
        }

        if (
            false === filter_var($str, FILTER_VALIDATE_INT)
            || false === filter_var($min, FILTER_VALIDATE_INT)
        ) {
            if (false !== filter_var($str, FILTER_VALIDATE_FLOAT)) {
                return compareFloatNumbers($str, $min, '<', 1e-18);
            }

            return false;
        }

        return $str < (int) $min;
    }

    public function less_than_or_equal($str, $min)
    {
        return $this->max($str, $min);
    }

    public function less_than_field($number, $field)
    {
        if (!isset($this->validate_data[$field])) {
            return false;
        }

        return $this->less_than($number, $this->validate_data[$field]);
    }

    public function less_than_or_equal_to_field($number, $field)
    {
        if (!isset($this->validate_data[$field])) {
            return false;
        }

        return $this->less_than_or_equal($number, $this->validate_data[$field]);
    }

    public function greater_than($str, $min)
    {
        if (empty($str) && !is_numeric($str)) {
            return true;
        }

        if (!is_numeric($str)) {
            return false;
        }

        if (
            false === filter_var($str, FILTER_VALIDATE_INT)
            || false === filter_var($min, FILTER_VALIDATE_INT)
        ) {
            if (false !== filter_var($str, FILTER_VALIDATE_FLOAT)) {
                return compareFloatNumbers($str, $min, '>', 1e-18);
            }

            return false;
        }

        return $str > (int) $min;
    }

    public function greater_than_or_equal($str, $min)
    {
        return $this->min($str, $min);
    }

    public function greater_than_field($number, $field)
    {
        if (!isset($this->validate_data[$field])) {
            return false;
        }

        return $this->greater_than($number, $this->validate_data[$field]);
    }

    public function greater_than_or_equal_to_field($number, $field)
    {
        if (!isset($this->validate_data[$field])) {
            return false;
        }

        return $this->greater_than_or_equal($number, $this->validate_data[$field]);
    }

    public function max($str, $min)
    {
        if (empty($str)) {
            return true;
        }

        if (!is_numeric($str)) {
            return false;
        }

        if (
            false === filter_var($str, FILTER_VALIDATE_INT)
            || false === filter_var($min, FILTER_VALIDATE_INT)
        ) {
            if (false !== filter_var($str, FILTER_VALIDATE_FLOAT)) {
                return compareFloatNumbers($str, $min, '<=', 1e-18);
            }

            return false;
        }

        return $str <= (int) $min;
    }

    public function exact_len($str, $val)
    {
        if (empty($str)) {
            return true;
        }

        if (preg_match('/[^0-9]/', $val)) {
            return false;
        }

        return (mb_strlen($str) != $val) ? false : true;
    }

    /**
     * check date.
     *
     * @param	string
     * @param mixed       $date
     * @param bool|string $format
     *
     * @return bool
     */
    public function valid_date($date, $format = 'm/d/Y H:i A')
    {
        if (empty($date)) {
            return true;
        }

        $format = !is_string($format) ? 'm/d/Y H:i A' : $format;
        $obtainedDate = \DateTime::createFromFormat($format, (string) $date);

        return false !== $obtainedDate && $obtainedDate->format($format) == $date;
    }

    public function valid_date_future($date, $format = 'm/d/Y')
    {
        $now = new \DateTime();
        $post_date = \DateTime::createFromFormat($format, $date);
        if (false === $post_date) {
            return false;
        }

        if (PHP_MAJOR_VERSION >= 7) {
            $now->setTime(0, 0, 0, 0);
            $post_date->setTime(0, 0, 0, 0);
        } else {
            $now->setTime(0, 0, 0);
            $post_date->setTime(0, 0, 0);
        }

        // the function must return TRUE
        return $now < $post_date;
    }

    /**
     *variable in format bla_bla_bla.
     *
     * @param	string
     * @param mixed $str
     *
     * @return bool
     */
    public function valid_var_name($str)
    {
        if (empty($str)) {
            return true;
        }

        return (!preg_match('/^[a-z][a-z0-9_]+$/', $str)) ? false : true;
    }

    /**
     *variable in format O'broa-na Adan.
     *
     * @param	string
     * @param mixed $str
     *
     * @return bool
     */
    public function valid_user_name($str)
    {
        if (empty($str)) {
            return true;
        }

        return (!preg_match("/^[a-zA-Z][a-zA-Z '-]{1,}$/", $str)) ? false : true;
    }

    /**
     *variable in format O'broa-na Adan.
     *
     * @param string $str
     *
     * @return bool
     */
    public function valid_user_unicode_name($str)
    {
        if (empty($str)) {
            return true;
        }

        return (!preg_match("/^([\\p{L}\\p{N}\\p{Sk}\\p{M}\\-_\\. ']+)$/u", $str)) ? false : true;
    }

    /**
     *variable in format +(373) 0259 - 2 - 78 - 64, 068-79-75-74.
     *
     * @param	string
     * @param mixed $str
     *
     * @return bool
     */
    public function valid_phone_number($str)
    {
        if (empty($str)) {
            return true;
        }

        return (!preg_match('/^[1-9]\\d{0,24}$/', $str)) ? false : true;
    }

    public function in($str, $list)
    {
        $allowed = explode(',', $list);
        $allowed = array_filter($allowed);
        if (empty($allowed) || !in_array($str, $allowed)) {
            return false;
        }

        return true;
    }

    public function valid_facebook_link($link)
    {
        $pattern = '/^(?:https?:\/\/)?(?:www\.)?(mbasic\.facebook|m\.facebook|facebook|fb)\.(com|me)\/(?:(?:\w\.)*#!\/)?(?:pages\/)?(?:[\w\-\.]*\/)?(?:profile\.php\?id=(?=\d.*))?([\w\-\.]*)/i';
        if (empty($link)) {
            return true;
        }

        return (bool) preg_match($pattern, $link);
    }

    public function valid_instagram_link($link)
    {
        $pattern = '/^(?:https?:\/\/)?(?:www\.)?(instagram\.com|instagr\.am)\/(p\/)?@?([a-z0-9_](?:(?:[a-z0-9_]|(?:\.(?!\.))){0,28}(?:[a-z0-9_]))?)\/?(\/?(?:\?.*))?$/i';
        if (empty($link)) {
            return true;
        }

        return (bool) preg_match($pattern, $link);
    }

    public function valid_twitter_link($link)
    {
        $pattern = '/^(?:https?:\/\/)?(?:www\.)?(twitter\.com)\/[a-z0-9\ _]+\/?(\/?(?:\?.*))?$/i';
        if (empty($link)) {
            return true;
        }

        return (bool) preg_match($pattern, $link);
    }

    public function valid_linkedin_link($link)
    {
        $pattern = '/^(?:https?:\/\/)?(?:www\.)?(linkedin\.com)\/(in\/)?[a-z0-9\ _-]+\/?(\/?(?:\?.*))?$/i';
        if (empty($link)) {
            return true;
        }

        return (bool) preg_match($pattern, $link);
    }

    public function valid_youtube_link($link)
    {
        $pattern = '/^(?:https?:\/\/)?(www|m).youtube.com\/((channel|c)\/)?(?!feed|user\/|watch\?)([a-zA-Z0-9-_.])*.*?$/i';
        if (empty($link)) {
            return true;
        }

        return (bool) preg_match($pattern, $link);
    }

    public function valid_skype_name($link)
    {
        $pattern = '/^[a-zA-Z][a-zA-Z0-9\.,\-_]{5,31}$/i';
        if (empty($link)) {
            return true;
        }

        return (bool) preg_match($pattern, $link);
    }

    public function hs_tarif_number($number)
    {
        $pattern = '/^$|^[\d]{4}\.?[\d]{2}(\.?[\d]{2}(\.?[\d]{2})?)?$/i';
        if (empty($number)) {
            return true;
        }

        return (bool) preg_match($pattern, $number);
    }

    public function valid_social_media_link($link, $service)
    {
        $patterns = [
            'Twitter'   => '/^(?:https?:\/\/)?(?:www\.)?(twitter\.com)\/[a-z0-9\ _]+\/?(\/?(?:\?.*))?$/i',
            'Facebook'  => '/^(?:https?:\/\/)?(?:www\.)?(mbasic\.facebook|m\.facebook|facebook|fb)\.(com|me)\/(?:(?:\w\.)*#!\/)?(?:pages\/)?(?:[\w\-\.]*\/)?(?:profile\.php\?id=(?=\d.*))?([\w\-\.]*)/i',
            'Instagram' => '/^(?:https?:\/\/)?(?:www\.)?(instagram\.com|instagr\.am)\/(p\/)?@?([a-z0-9_](?:(?:[a-z0-9_]|(?:\.(?!\.))){0,28}(?:[a-z0-9_]))?)\/?(\/?(?:\?.*))?$/i',
            'Linkedin'  => '/^(?:https?:\/\/)?(?:www\.)?(linkedin\.com)\/(in\/)?[a-z0-9\ _-]+\/?(\/?(?:\?.*))?$/i',
        ];

        if (empty($link) || !isset($patterns[$service])) {
            return true;
        }

        return (bool) preg_match($patterns[$service], $link);
    }

    public function valid_phone($str)
    {
        $phoneUtil = PhoneNumberUtil::getInstance();

        try {
            return $phoneUtil->isValidNumber($phoneUtil->parse($str));
        } catch (NumberParseException $exception) {
            return false;
        }
    }

    public function valid_phone_for_region($str, $region)
    {
        $phoneUtil = PhoneNumberUtil::getInstance();

        try {
            return $phoneUtil->isValidNumberForRegion($phoneUtil->parse($str), preg_replace('/[^0-9]/', '', $region));
        } catch (NumberParseException $exception) {
            return false;
        }
    }

    public function viable_phone($str)
    {
        return PhoneNumberUtil::getInstance()->isViablePhoneNumber($str);
    }

    public function possible_duns($str)
    {
        if (empty($str)) {
            return true;
        }

        return (bool) preg_match('/^(((\\d{9}|(\\d{2}-(\\d{7}|(\\d{3}-\\d{4}))))(-?\\d{4})?)|\\d{8}|\\d{7}|\\d{13})$/', $str);
    }

    /**
     * Match one field to another.
     *
     * @param mixed $number1
     * @param mixed $number2Field
     *
     * @return bool
     */
    public function matchFromToValue($number1, $number2Field)
    {
        if (!isset($this->validate_data[$number2Field])) {
            return false;
        }

        $number1 = (int) $number1;
        $number2 = (int) $this->validate_data[$number2Field];

        return $number1 >= $number2 ? false : true;
    }

    public function matchToFromValue($number1, $number2Field)
    {
        if (!isset($this->validate_data[$number2Field])) {
            return false;
        }

        $number1 = (int) $number1;
        $number2 = (int) $this->validate_data[$number2Field];

        return $number1 <= $number2 ? false : true;
    }

    /**
     * Check if ids are integer in list separated by comma.
     *
     * @param string
     * @param mixed $str
     */
    public function valid_ids($str)
    {
        if (empty($str)) {
            return true;
        }

        $parts = explode(',', $str);
        foreach ($parts as $id) {
            if (!$this->integer(trim($id))) {
                return false;
            }
        }

        return true;
    }

    public function get_rule_message(string $rule): string
    {
        return translate($this->_messages[$rule]) ?? translate($this->_messages['default']);
    }

    /**
     * get message as text.
     *
     * @param mixed $type
     * @param mixed $field
     */
    private function _get_message($type, $field)
    {
        if (isset($this->_messages[$type])) {
            $str = translate($this->_messages[$type]);
        } else {
            $str = translate($this->_messages['default']);
        }

        $message = str_replace('%s', $field['label'], $str);

        if (isset($field['param']) && '' != $field['param']) {
            $message = str_replace('%d', $field['param'], $message);
        }

        return $message;
    }

    /**
     * set field postdata.
     *
     * @param mixed $field
     * @param mixed $postdata
     */
    private function set_field_postdata($field, $postdata)
    {
        if (isset($this->_field_data[$field]['postdata'])) {
            $this->_field_data[$field]['postdata'] = $postdata;
        }
    }
}
