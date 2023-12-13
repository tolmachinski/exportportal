<?php

declare(strict_types=1);

use App\Common\Database\BaseModel;
use App\Common\Database\Model;
use Doctrine\Common\Collections\ArrayCollection;
use App\Common\Exceptions\NotFoundException;
use App\Common\Exceptions\QueryException;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\ParameterType;
use Mpdf\Utils\Arrays;

/**
 * Model Ep_news_archive.
 */
class Ep_news_archive_Model extends Model
{

/**
     * The name of the "created at" column.
     *
     * @var null|string
     */
    protected const CREATED_AT = 'created_at';

    /**
     * The name of the "updated at" column.
     *
     * @var null|string
     */
    protected const UPDATED_AT = 'updated_at';

    /**
     * The name of the "published on" column.
     *
     * @var null|string
     */
    protected const PUBLISHED_ON = 'published_on';

    /**
     * The table name.
     */
    protected string $table = 'ep_news_archive';

    /**
     * The table primary key.
     */
    protected $primaryKey = 'id_archive';

    /**
     * Indicates if model uses timestamps.
     */
    protected bool $timestamps = true;

    private $ep_news_archive_table = 'ep_news_archive';

    public function get_one($id_archive)
    {
        $this->db->select('*');
        $this->db->from($this->ep_news_archive_table);
        $this->db->where('id_archive', $id_archive);

        return $this->db->query_one();
    }

    public function getNewsArchives($params): ?ArrayCollection
    {
        $conditions = !empty($params['year']) ? ['published_date' => ['from' => "{$params['year']}-00-00", 'to' => ($params['year'] + 1)."-00-00"] ] : [];

        try {
            $archives = $this->paginate([
                'limit'      => $params['limit'],
                'skip'       => $params['offset'],
                'order'      => ['published_on' => 'DESC'],
                'conditions' => $conditions,
            ],
            $params['per_p'] ? $params['per_p'] : 10,
            $params['page']);

            return new ArrayCollection($archives ?? []);
        } catch (NotFoundException $exception) {
            throw $exception;
        } catch (Exception $exception) {
            QueryException::executionFailed($this->getHandler(), $exception);
        }
    }

    public function getArchivesGroupedByYears(): ?ArrayCollection
    {
        try {
            $archives = $this->findAllBy([
                'group' => ['YEAR(published_on)'],
                'order' => ['published_on' => 'DESC']
            ]);

            return new ArrayCollection($archives ?? []);
        } catch (NotFoundException $exception) {
            throw $exception;
        } catch (Exception $exception) {
            QueryException::executionFailed($this->getHandler(), $exception);
        }
    }

    public function getPreviousOrNextRecords(string $date, bool $next = false): Array
    {
        $conditions = $next ? ['next_published' => $date] : ['prev_published' => $date];
        $order = $next ? [] : ["published_on" => "DESC"];

        try {
            $archives = $this->findAllBy([
                'limit'      => 2,
                'order'      => $order,
                'conditions' => $conditions
            ]);

            return $archives ?? [];
        } catch (NotFoundException $exception) {
            throw $exception;
        } catch (Exception $exception) {
            QueryException::executionFailed($this->getHandler(), $exception);
        }
    }

    public function get_news_archive($conditions = array())
    {
        $limit = 10;
        $offset = 0;
        $order_by = 'id_archive DESC';

        extract($conditions);

        if (isset($sort_by)) {
            foreach ($sort_by as $sort_item) {
                $sort_item = explode('-', $sort_item);
                $multi_order_by[] = $sort_item[0] . ' ' . $sort_item[1];
            }

            $order_by = implode(',', $multi_order_by);
        }

        $this->db->select('*');
        $this->db->from($this->ep_news_archive_table);
        $this->db->orderby($order_by);

        if (!empty($limit)) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->query_all();
    }

    public function get_count_news_archive()
    {
        $this->db->select('COUNT(*) AS AGGREGATE');
        $this->db->from($this->ep_news_archive_table);

        $data = $this->db->query_one();
        if (!$data || empty($data)) {
            return 0;
        }

        return isset($data['AGGREGATE']) ? (int) $data['AGGREGATE'] : 0;
    }

    public function check_exist_archive($id_archive)
    {
        $this->db->select('COUNT(*) AS AGGREGATE');
        $this->db->from($this->ep_news_archive_table);

        $this->db->where('id_archive', $id_archive);

        $counter = $this->db->query_one();
        if (empty($counter)) {
            return false;
        }

        return (bool) (int) arrayGet($counter, 'AGGREGATE');
    }

    public function update($id_archive, $data)
    {
        $this->db->where('id_archive', $id_archive);
        return $this->db->update($this->ep_news_archive_table, $data);
    }

    public function insert($data)
    {
        $this->db->insert($this->ep_news_archive_table, $data);
        return $this->db->last_insert_id();
    }

    public function delete($id_archive)
    {
        $this->db->where('id_archive', $id_archive);
        return $this->db->delete($this->ep_news_archive_table);
    }

    /**************************************** Scopes ****************************************/

    protected function scopeId(QueryBuilder $builder, int $id): void
    {
        $builder->where(
            $builder->expr()->eq(
                "{$this->getTable()}.id_archive",
                $builder->createNamedParameter($id, ParameterType::INTEGER, $this->nameScopeParameter('id'))
            )
        );
    }

    /**
     * Scope archives by date published between selected range.
     */
    protected function scopePublishedDate(QueryBuilder $builder, array $params): void
    {
        $builder->where(
            $builder->expr()->gte(
                "DATE({$this->getTable()}.published_on)",
                $builder->createNamedParameter($params['from'], ParameterType::STRING, $this->nameScopeParameter('from'))
            )
        )->andWhere(
            $builder->expr()->lte(
                "DATE({$this->getTable()}.published_on)",
                $builder->createNamedParameter($params['to'], ParameterType::STRING, $this->nameScopeParameter('to'))
            )
        );
    }

    protected function scopePrevPublished(QueryBuilder $builder, string $date): void
    {
        $builder->where(
            $builder->expr()->lt(
                "{$this->getTable()}.published_on",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('date'))
            )
        );
    }

    protected function scopeNextPublished(QueryBuilder $builder, string $date): void
    {
        $builder->where(
            $builder->expr()->gt(
                "{$this->getTable()}.published_on",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('date'))
            )
        );
    }

}

// End of file ep_news_archive_model.php
// Location: /tinymvc/myapp/models/ep_news_archive_model.php
