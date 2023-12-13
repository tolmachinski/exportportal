<?php

declare(strict_types=1);

use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use App\Common\Exceptions\NotFoundException;
use App\Common\Exceptions\QueryException;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Comment_Authors model.
 */
class Comment_Authors_Model extends Model
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
     * The table name.
     */
    protected string $table = 'comment_authors';

    /**
     * The table primary key.
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if model uses timestamps.
     */
    protected bool $timestamps = true;

    /**
     * The attributes that aren't mass assignable.
     */
    protected array $guarded = [
        'id',
        self::CREATED_AT,
        self::UPDATED_AT,
    ];

    /**
     * The attributes that are nullable.
     */
    protected array $nullable = [
        'id_user',
        'email',
        'name',
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id'             => Types::INTEGER,
        'id_user'        => Types::INTEGER,
        self::CREATED_AT => Types::DATETIME_IMMUTABLE,
        self::UPDATED_AT => Types::DATETIME_IMMUTABLE,
    ];

    /**
     * Get or create the author.
     */
    public function get_or_create_author(?int $user_id, ?string $email, ?string $name): array
    {
        try {
            if (null !== $user_id) {
                return $this->get_registered_author($user_id);
            }

            return $this->get_anonymous_author($email, $name);
        } catch (NotFoundException | TypeError $e) {
            return [
                'id'            => $this->add_author($user_id, $email, $name),
                'user'          => $user_id,
                'email'         => $email,
                'name'          => $name,
                'is_registered' => null !== $user_id,
            ];
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get resgistered author.
     */
    public function get_registered_author(int $user_id): array
    {
        try {
            $author = $this->findOneBy([
                'columns'    => ['id', 'id_user as user', 'email', 'name', 'is_registered'],
                'conditions' => ['user' => $user_id],
            ]);

            if (null === $author) {
                throw new NotFoundException('The author is not found.');
            }
        } catch (NotFoundException $exception) {
            throw $exception;
        } catch (Exception $exception) {
            throw QueryException::executionFailed($this->getHandler(), $exception);
        }

        return $author;
    }

    /**
     * Get resgistered author.
     */
    public function get_anonymous_author(string $email, ?string $name): array
    {
        try {
            $author = null;
            $conditions = array_filter(['email' => $email, 'name' => $name]);
            if (!empty($conditions)) {
                $author = $this->findOneBy([
                    'columns'    => ['id', 'id_user as user', 'email', 'name', 'is_registered'],
                    'conditions' => $conditions,
                ]);
            }

            if (null === $author) {
                throw new NotFoundException('The author is not found.');
            }
        } catch (NotFoundException $exception) {
            throw $exception;
        } catch (Exception $exception) {
            throw QueryException::executionFailed($this->getHandler(), $exception);
        }

        return $author;
    }

    /**
     * Add an author.
     */
    public function add_author(?int $user_id, ?string $email, ?string $name): int
    {
        if (null !== $user_id) {
            $author_id = $this->add_registered_author($user_id);
        } else {
            $author_id = $this->add_anonymous_author($email, $name);
        }

        return (int) $author_id;
    }

    /**
     * Add registered author.
     */
    public function add_registered_author(int $user_id): int
    {
        return (int) $this->insertOne(['id_user' => $user_id]);
    }

    /**
     * Add one anonymous author.
     */
    public function add_anonymous_author(string $email, string $name): int
    {
        return (int) $this->insertOne(['email' => $email, 'name' => $name]);
    }

    /**
     * Scope query by user ID.
     */
    protected function scopeUser(QueryBuilder $builder, int $user): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'id_user',
                $builder->createNamedParameter($user, ParameterType::INTEGER, $this->nameScopeParameter('userId'))
            )
        );
    }

    /**
     * Scope query by author email.
     */
    protected function scopeEmail(QueryBuilder $builder, string $email): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'email',
                $builder->createNamedParameter($email, ParameterType::STRING, $this->nameScopeParameter('email'))
            )
        );
    }

    /**
     * Scope query by author name.
     */
    protected function scopeName(QueryBuilder $builder, string $name): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'name',
                $builder->createNamedParameter($name, ParameterType::STRING, $this->nameScopeParameter('name'))
            )
        );
    }

    /**
     * Resolves static relationships with comments.
     */
    protected function comments(): RelationInterface
    {
        return $this->hasMany(Comments_Model::class, 'id_author')->disableNativeCast();
    }
}

// End of file comment_authors_model.php
// Location: /tinymvc/myapp/models/comment_authors_model.php
