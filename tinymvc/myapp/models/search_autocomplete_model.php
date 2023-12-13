<?php

declare(strict_types=1);

use App\Common\Database\Model;
use App\Common\Exceptions\QueryException;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

use const App\Common\Autocomplete\RECORDS_PER_TYPE;
use const App\Common\DB_DATE_FORMAT;

/**
 * Search_Autocomplete model.
 */
class Search_Autocomplete_Model extends Model
{
    /**
     * The name of the "created at" column.
     *
     * @var null|string
     */
    protected const CREATED_AT = 'date_created';

    /**
     * The name of the "updated at" column.
     *
     * @var null|string
     */
    protected const UPDATED_AT = 'date_updated';

    /**
     * {@inheritdoc}
     */
    protected string $table = 'search_autocomplete';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id';

    /**
     * {@inheritdoc}
     */
    protected bool $timestamps = true;

    /**
     * {@inheritdoc}
     */
    protected array $guarded = array(
        'id',
        self::CREATED_AT,
        self::UPDATED_AT,
    );

    /**
     * {@inheritdoc}
     */
    protected array $casts = array(
        'id'             => Types::INTEGER,
        'id_user'        => Types::INTEGER,
        'type'           => Types::INTEGER,
        'hits'           => Types::INTEGER,
        'date_hit'       => Types::DATETIME_IMMUTABLE,
        self::CREATED_AT => Types::DATETIME_IMMUTABLE,
        self::UPDATED_AT => Types::DATETIME_IMMUTABLE,
    );

    /**
     * Hit the autocomplete record.
     */
    public function hit(int $id): bool
    {
        $date = new DateTimeImmutable();
        $table = $this->getTable();
        $dateColumn = static::UPDATED_AT;

        return $this->getHandler()->query(
            <<<QUERY
            UPDATE {$table}
            SET {$table}.`hits` = {$table}.`hits` + 1, {$table}.`{$dateColumn}` = ?, {$table}.`date_hit` = ?
            WHERE {$table}.`id` = ?
            QUERY,
            array($date->format(DB_DATE_FORMAT), $date->format(DB_DATE_FORMAT), $id)
        );
    }

    /**
     * Write one autocomplete record to the table.
     */
    public function write(string $text, ?int $type, ?string $user_ref, ?int $user_id, ?string &$token = null)
    {
        try {
            return $this->insertOne(array(
                'type'     => $type,
                'id_user'  => $user_id,
                'user_ref' => $user_ref,
                'text'     => $text,
                'token'    => $token = $token ?? bin2hex(random_bytes(32)),
                'date_hit' => new DateTimeImmutable(),
            ));
        } finally {
            try {
                $this->drop_excess($user_ref, $type);
            } catch (QueryException $e) {
                // Your logger is in another castle.
            }
        }
    }

    /**
     * Drop excessive amount or records for given user.
     */
    public function drop_excess(?string $user_ref, ?int $type): bool
    {
        if (null === $user_ref) {
            throw new BadMethodCallException('The user REF value must be defined.');
        }

        $params = array_filter(
            array(
                'type'     => array($type, PDO::PARAM_INT),
                'user_ref' => array($user_ref, PDO::PARAM_STR),
                'len'      => array(RECORDS_PER_TYPE, PDO::PARAM_INT),
            ),
            function ($value) { return null !== $value[0]; }
        );
        $conditions = implode(' AND ', array_filter(
            array($user_ref ? 'user_ref = :user_ref' : null),
            function ($value) { return null !== $value; }
        ));

        try {
            $connection = $this->getHandler()->getConnection();
            $query = <<<QUERY
            DELETE FROM {$this->getTable()}
            WHERE id IN (
                SELECT id
                FROM (
                    SELECT
                        id,
                        date_created,
                        @rownum := @rownum + 1,
                        @rownum AS `rownum`,
                        @prev := id
                    FROM {$this->getTable()}
                    JOIN (SELECT @rownum := 0, @prev := 0) AS r
                    WHERE type = :type AND {$conditions}
                    ORDER BY date_created DESC
                ) AS tmp
                WHERE rownum > :len
                ORDER BY rownum
            )
            QUERY;

            $statement = $connection->prepare($query);
            foreach ($params as $key => list($value, $type)) {
                $statement->bindValue($key, $value, $type);
            }

            return $statement->execute();
        } catch (\Throwable $th) {
            throw new QueryException($this->getHandler(), $th, sprintf('The DB request failed due to reason: %s', $th->getMessage()));
        }
    }

    /**
     * Syncs user reference value.
     */
    public function sync_user_ref(int $user_id, string $user_ref)
    {
        return $this->updateMany(
            array(
                'user_ref'         => $user_ref,
                static::UPDATED_AT => new DateTimeImmutable(),
            ),
            array(
                'conditions' => array(
                    'user_id'      => $user_id,
                    'not_user_ref' => $user_ref,
                ),
            )
        );
    }

    /**
     * Scopes query by the autocomplete type.
     */
    public function scopeType(QueryBuilder $builder, int $type): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'type',
                $builder->createNamedParameter($type, ParameterType::INTEGER, $this->nameScopeParameter('type'))
            )
        );
    }

    /**
     * Scopes query by the user ID.
     */
    public function scopeUserId(QueryBuilder $builder, ?int $user_id): void
    {
        if (null === $user_id) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->eq(
                'id_user',
                $builder->createNamedParameter($user_id, ParameterType::INTEGER, $this->nameScopeParameter('userId'))
            )
        );
    }

    /**
     * Scopes query by the user reference.
     */
    public function scopeUserRef(QueryBuilder $builder, ?string $user_ref): void
    {
        if (null === $user_ref) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->eq(
                'user_ref',
                $builder->createNamedParameter($user_ref, ParameterType::STRING, $this->nameScopeParameter('userRef'))
            )
        );
    }

    /**
     * Scopes query by the user reference.
     */
    public function scopeNotUserRef(QueryBuilder $builder, ?string $user_ref): void
    {
        if (null === $user_ref) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->neq(
                'user_ref',
                $builder->createNamedParameter($user_ref, ParameterType::STRING, $this->nameScopeParameter('notUserRef'))
            )
        );
    }

    /**
     * Scopes query by token.
     */
    public function scopeToken(QueryBuilder $builder, ?string $token): void
    {
        if (null === $token) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->eq(
                'token',
                $builder->createNamedParameter($token, ParameterType::STRING, $this->nameScopeParameter('token'))
            )
        );
    }
}

// End of file search_autocomplete_model.php
// Location: /tinymvc/myapp/models/search_autocomplete_model.php
