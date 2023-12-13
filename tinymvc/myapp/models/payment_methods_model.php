<?php

declare(strict_types=1);

use App\Common\Database\BaseModel;
use App\Common\Database\Concerns;

/**
 * Model Payment_methods.
 */
class Payment_Methods_Model extends BaseModel
{
    use Concerns\CanTransformValues;

    /**
     * List of columns the table which is later used on create or update.
     * Each record must be an array that have filled keys:
     *  - name: is a name of the column
     *  - type: indicates the type of the column
     *  - fillable: indicates that column is fillable in NORMAL mode (columns with this flag set to `false` are fillable in FORCE mode).
     *
     * @var array
     */
    protected $payment_methods_columns_metadata = array(
        array('name' => 'id',              'fillable' => false, 'type' => 'int'),
        array('name' => 'method',          'fillable' => true,  'type' => 'string'),
        array('name' => 'alias',           'fillable' => true,  'type' => 'string'),
        array('name' => 'instructions',    'fillable' => true,  'type' => 'string'),
        array('name' => 'enable',          'fillable' => true,  'type' => 'int'),
        array('name' => 'updated_at',      'fillable' => false, 'type' => 'datetime'),
        array('name' => 'text_updated_at', 'fillable' => true,  'type' => 'datetime'),
        array('name' => 'constraints',     'fillable' => true,  'type' => 'array', 'nullable' => true),
    );

    /**
     * Name of the payment methods table.
     *
     * @var string
     */
    private $payment_methods_table = 'payment_methods';

    /**
     * Alias of the payment methods table.
     *
     * @var string
     */
    private $payment_methods_table_alias = 'PAYMENT_METHODS';

    public function get_method($methods_id, array $params = array())
    {
        return $this->findRecord(
            'method',
            $this->payment_methods_table,
            $this->payment_methods_table_alias,
            'id',
            $methods_id,
            $params
        );
    }

    public function find_method(array $params = array())
    {
        return $this->findRecord(
            'method',
            $this->payment_methods_table,
            $this->payment_methods_table_alias,
            null,
            null,
            $params
        );
    }

    public function get_methods(array $params = array())
    {
        return $this->findRecords(
            'method',
            $this->payment_methods_table,
            $this->payment_methods_table_alias,
            $params
        );
    }

    public function create_method(array $method, $force = false)
    {
        return $this->db->insert(
            $this->payment_methods_table,
            $this->recordAttributesToDatabaseValues(
                $method,
                $this->payment_methods_columns_metadata,
                $force
            )
        );
    }

    public function create_methods(array $methods, $force = false)
    {
        return $this->db->insert_batch(
            $this->payment_methods_table,
            $this->recordsListToDatabaseValues(
                $methods,
                $this->payment_methods_columns_metadata,
                $force
            )
        );
    }

    public function update_method($methods_id, array $method, $force = false)
    {
        $this->db->where("`{$this->payment_methods_table}`.`id` = ?", (int) $methods_id);

        return $this->db->update(
            $this->payment_methods_table,
            $this->recordAttributesToDatabaseValues(
                $method,
                $this->payment_methods_columns_metadata,
                $force
            )
        );
    }

    public function delete_method($methods_id)
    {
        $this->db->where("`{$this->payment_methods_table}`.`id` = ?", (int) $methods_id);

        return $this->db->delete($this->payment_methods_table);
    }
}

// End of file payment_methods_model.php
// Location: /tinymvc/myapp/models/payment_methods_model.php
