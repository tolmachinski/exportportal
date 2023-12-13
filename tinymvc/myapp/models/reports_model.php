<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\Types\Types;

/**
 * Reports model.
 */
class Reports_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = 'reports';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id_report';

    /**
     * {@inheritdoc}
     */
    protected array $guarded = [
        'id_report',
    ];

    /**
     * {@inheritdoc}
     */
    protected array $casts = [
        'id_report' => Types::INTEGER,
    ];

    public function get_report(int $id_report, array $params = [])
    {
        return $this->findRecord(
            null,
            $this->getTable(),
            null,
            $this->getPrimaryKey(),
            $id_report,
            $params
        );
    }

    public function get_reports(array $params = [])
    {
        return $this->findRecords(
            null,
            $this->getTable(),
            null,
            $params
        );
    }
}

// End of file reports_model.php
// Location: /tinymvc/myapp/models/reports_model.php
