<?php

declare(strict_types=1);

use App\Common\Database\Relations\RelationInterface;

use const App\Common\Complaints\TYPE_COMMENT;

/**
 * Comment_Reports model.
 */
class Comment_Reports_Model extends Complaints_Model
{
    /**
     * {@inheritdoc}
     */
    final public function has($id): bool
    {
        $counter = $this->findRecord(
            null,
            $this->getTable(),
            null,
            $this->getPrimaryKey(),
            $id,
            [
                'columns'    => ['COUNT(*) AS `AGGREGATE`'],
                'conditions' => ['type' => TYPE_COMMENT],
            ]
        );

        return (bool) (int) ($counter['AGGREGATE'] ?? 0);
    }

    /**
     * {@inheritdoc}
     */
    final public function find($id): ?array
    {
        return $this->castAttributesToNative(
            $this->findRecord(
                null,
                $this->getTable(),
                null,
                $this->getPrimaryKey(),
                $id,
                ['conditions' => ['type' => TYPE_COMMENT]]
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    final public function findAll(array $params = []): array
    {
        $params['conditions']['type'] = TYPE_COMMENT;

        return parent::findAll($params);
    }

    /**
     * {@inheritdoc}
     */
    final public function findOneBy(array $params = []): ?array
    {
        $params['conditions']['type'] = TYPE_COMMENT;

        return parent::findOneBy($params);
    }

    /**
     * {@inheritdoc}
     */
    final public function findAllBy(array $params = []): array
    {
        $params['conditions']['type'] = TYPE_COMMENT;

        return parent::findAllBy($params);
    }

    /**
     * {@inheritdoc}
     */
    final public function paginate(array $params = [], ?int $per_page = null, ?int $page = 1): array
    {
        $params['conditions']['type'] = TYPE_COMMENT;

        return parent::paginate($params, $per_page, $page);
    }

    /**
     * {@inheritdoc}
     */
    final public function getPaginator(array $params = [], ?int $per_page = null, ?int $page = 1): array
    {
        $params['conditions']['type'] = TYPE_COMMENT;

        return parent::getPaginator($params, $per_page, $page);
    }

    /**
     * {@inheritdoc}
     */
    final public function countAll(): int
    {
        $counter = $this->findRecord(
            null,
            $this->getTable(),
            null,
            null,
            null,
            [
                'columns'    => ['COUNT(*) AS `AGGREGATE`'],
                'conditions' => ['type' => TYPE_COMMENT],
            ]
        );

        return (int) ($counter['AGGREGATE'] ?? 0);
    }

    /**
     * {@inheritdoc}
     */
    final public function countBy(array $params = [])
    {
        $params['conditions']['type'] = TYPE_COMMENT;

        return parent::countBy($params);
    }

    /**
     * {@inheritdoc}
     */
    final public function insertOne(array $record): string
    {
        return parent::insertOne(
            array_merge(
                ['id_type' => TYPE_COMMENT],
                $record
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    final public function insertMany(array $records, bool $force = false): int
    {
        return parent::insertMany(
            array_map(
                function ($record) {
                    return array_merge(
                        ['id_type' => TYPE_COMMENT],
                        $record
                    );
                },
                $records
            ),
            $force
        );
    }

    /**
     * {@inheritdoc}
     */
    final public function updateOne($id, array $record): bool
    {
        $this->updateMany($record, [
            'conditions' => [
                'type'        => TYPE_COMMENT,
                'primary_key' => fn () => [$this->getTable(), $this->getPrimaryKey(), $id],
            ],
        ]);

        return true;
    }

    /**
     * Resolves static relationships with comment.
     */
    protected function comment(): RelationInterface
    {
        return $this->belongsTo(Comments_Model::class, 'id_item')->disableNativeCast();
    }
}

// End of file comment_reports_model.php
// Location: /tinymvc/myapp/models/comment_reports_model.php
