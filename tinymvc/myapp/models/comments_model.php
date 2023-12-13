<?php

declare(strict_types=1);

use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use App\Common\Exceptions\NotFoundException;
use App\Common\Exceptions\QueryException;
use App\Common\Workflow\Comments\CommentStates;
use App\Common\Workflow\Comments\CommentTemplates;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Comments model.
 */
class Comments_Model extends Model
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
    protected string $table = 'comments';

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
        'id_lang',
        'id_author',
        'id_moderator',
        'id_resource',
        'parent',
        'state',
        'date_published',
        'date_deleted',
        'date_blocked',
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id'                  => Types::INTEGER,
        'id_lang'             => Types::INTEGER,
        'id_author'           => Types::INTEGER,
        'id_moderator'        => Types::INTEGER,
        'id_resource'         => Types::INTEGER,
        'parent'              => Types::INTEGER,
        'level'               => Types::INTEGER,
        'upvotes'             => Types::INTEGER,
        'downvotes'           => Types::INTEGER,
        self::CREATED_AT      => Types::DATETIME_IMMUTABLE,
        self::UPDATED_AT      => Types::DATETIME_IMMUTABLE,
        'date_modified'       => Types::DATETIME_IMMUTABLE,
        'date_published'      => Types::DATETIME_IMMUTABLE,
        'date_deleted'        => Types::DATETIME_IMMUTABLE,
        'date_blocked'        => Types::DATETIME_IMMUTABLE,
    ];

    /**
     * Get the list of comments by type, resource and level.
     */
    public function get_list(
        int $resource_id,
        int $level = 0,
        int $page = 1,
        ?int $per_page = null,
        ?string $state = CommentStates::PUBLISHED
    ): array {
        $paginator = $this->paginate(
            [
                'with'       => [
                    'author' => function (RelationInterface $relation) {
                        /** @var User_Model $users */
                        $users = model(User_Model::class);
                        $usersTable = $users->get_users_table();
                        $usersGroupTable = $users->get_user_groups_table();
                        $primaryKey = $relation->getExistenceCompareKey();
                        $builder = $relation->getQuery();
                        $table = $relation->getRelated()->getTable();

                        $builder->select(
                            "`{$table}`.`id`",
                            "`{$table}`.`id_user`",
                            "`{$table}`.`id_user` as `user`",
                            "`{$usersTable}`.`user_photo` AS `photo`",
                            "`{$usersTable}`.`user_group` AS `group`",
                            "IFNULL(`{$table}`.`email`, `{$usersTable}`.`email`) AS `email`",
                            "IFNULL(`{$table}`.`name`, TRIM(CONCAT(`{$usersTable}`.`fname`, ' ', `{$usersTable}`.`lname`))) AS `name`",
                            "`{$table}`.`date_created` AS `created_at`",
                            "`{$table}`.`date_updated` AS `updated_at`",
                            "`{$table}`.`is_registered`",
                            "`{$usersGroupTable}`.`gr_type` AS `group_type`",
                        );

                    // $pdo->join($users_group_table, "`{$users_group_table}`.`idgroup` = `{$users_table}`.`user_group`", 'left');
                    },
                ],
                'columns'    => [
                    '`id`',
                    '`id_lang`',
                    '`id_author`',
                    '`id_moderator`',
                    '`id_resource`',
                    '`id_lang` AS `language`',
                    '`id_moderator` AS `moderator`',
                    '`id_resource` AS `resource`',
                    '`parent`',
                    '`state`',
                    '`text`',
                    '`level`',
                    '`upvotes`',
                    '`downvotes`',
                    '`date_created` AS `created_at`',
                    '`date_updated` AS `updated_at`',
                    '`date_modified` AS `modified_at`',
                    '`date_published` AS `published_at`',
                    '`date_deleted` AS `deleted_at`',
                    '`date_blocked` AS `blocked_at`',
                ],
                'conditions' => [
                    'resource' => $resource_id,
                    'level'    => $level,
                    'state'    => $state ?? CommentStates::PUBLISHED,
                ],
                'order' => [
                    "`{$this->getTable()}`.`id`" => 'DESC',
                ],
            ],
            $per_page,
            $page
        );
        $comments = $paginator['data'] ?? [];
        unset($paginator['data']);

        return [
            'comments'  => new ArrayCollection($comments),
            'paginator' => $paginator,
        ];
    }

    public function get_comments(array $params = []): ?Collection
    {
        $params['order'] = $params['order'] ?? ["`{$this->getTable()}`.`date_published`" => 'DESC'];
        $comments = $this->findRecords(
            null,
            $this->getTable(),
            null,
            $params
        );

        if (empty($comments)) {
            return null;
        }

        return new ArrayCollection($comments);
    }

    /**
     * Get the comment by comment id.
     */
    public function get_comment(int $commentId): ?array
    {
        try {
            $comment = $this->findOneBy([
                'conditions' => [
                    'comment_id' => $commentId,
                ],
                'with' => [
                    'author',
                ],
            ]);

            if (empty($comment)) {
                throw new NotFoundException("The comment with ID '{$commentId}' is not found.");
            }

            return $comment;
        } catch (NotFoundException $exception) {
            throw $exception;
        } catch (Exception $exception) {
            throw QueryException::executionFailed($this->getHandler(), $exception);
        }
    }

    /**
     * Get the comments by comment id.
     */
    public function get_comments_after_id(
        int $comment_id,
        int $resource_id,
        ?string $state = CommentStates::PUBLISHED
    ): ?ArrayCollection {
        try {
            $comments = $this->findAllBy([
                'order'      => ['id' => 'DESC'],
                'with'       => [
                    'author' => function (RelationInterface $relation) {
                        /** @var User_Model $users */
                        $users = model(User_Model::class);
                        $table = $relation->getRelated()->getTable();
                        $builder = $relation->getQuery();
                        $usersTable = $users->get_users_table();
                        $usersGroupTable = $users->get_user_groups_table();
                        $builder
                            ->select(
                                "`{$table}`.`id`",
                                "`{$table}`.`id_user`",
                                "`{$table}`.`id_user` as `user`",
                                "`{$usersTable}`.`user_photo` AS `photo`",
                                "IFNULL(`{$table}`.`email`, `{$usersTable}`.`email`) AS `email`",
                                "IFNULL(`{$table}`.`name`, TRIM(CONCAT(`{$usersTable}`.`fname`, ' ', `{$usersTable}`.`lname`))) AS `name`",
                                "`{$usersGroupTable}`.`gr_type` AS `group_type`",
                            )
                        ;
                    },
                ],
                'conditions' => [
                    'after_id'       => $comment_id,
                    'resource'       => $resource_id,
                    'state'          => $state ?? CommentStates::PUBLISHED,
                ],
            ]);

            return new ArrayCollection($comments ?? []);
        } catch (NotFoundException $exception) {
            throw $exception;
        } catch (Exception $exception) {
            throw QueryException::executionFailed($this->getHandler(), $exception);
        }
    }

    public function get_count_comments(array $params = []): ?int
    {
        $params['columns'] = 'COUNT(*) AS `count_comments`';

        $response = $this->findRecord(
            null,
            $this->getTable(),
            null,
            null,
            null,
            $params
        );

        return empty($response['count_comments']) ? 0 : (int) $response['count_comments'];
    }

    /**
     * Add one comment.
     *
     * @see Comments_Model::insertOne()
     */
    public function add(array $comment): int
    {
        return (int) $this->insertOne($comment, true);
    }

    /**
     * Edit the comment.
     */
    public function edit(int $commentId, array $comment_updates): bool
    {
        return (bool) $this->updateOne($commentId, $comment_updates);
    }

    /**
     * Publish the comment.
     */
    public function publish(int $comment): bool
    {
        return (bool) $this->updateOne($comment, [
            'state'          => CommentStates::PUBLISHED,
            'date_published' => new DateTimeImmutable(),
        ]);
    }

    /**
     * Unublish the comment.
     */
    public function unpublish(int $comment): bool
    {
        return (bool) $this->updateOne($comment, [
            'state'          => CommentStates::UNPUBLISHED,
            'date_published' => null,
        ]);
    }

    /**
     * Block the comment.
     */
    public function block(int $comment): bool
    {
        return (bool) $this->updateOne($comment, [
            'state'        => CommentStates::BLOCKED,
            'date_blocked' => new DateTimeImmutable(),
        ]);
    }

    /**
     * Delete the comment.
     */
    public function delete(int $comment): bool
    {
        return (bool) $this->updateOne($comment, [
            'text'         => CommentTemplates::DELETED_COMMENT_TEMPLATE,
            'state'        => CommentStates::DELETED,
            'date_deleted' => new DateTimeImmutable(),
        ]);
    }

    /**
     * Scope comment query by resource.
     */
    protected function scopeResource(QueryBuilder $builder, int $resource): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'id_resource',
                $builder->createNamedParameter($resource, ParameterType::INTEGER, $this->nameScopeParameter('resource'))
            )
        );
    }

    /**
     * Scope comment query by language.
     */
    protected function scopeLanguage(QueryBuilder $builder, int $language): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'id_lang',
                $builder->createNamedParameter($language, ParameterType::INTEGER, $this->nameScopeParameter('language'))
            )
        );
    }

    /**
     * Scope comment query by top level.
     */
    protected function scopeTopLevel(QueryBuilder $builder): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'level',
                $builder->createNamedParameter(0, ParameterType::INTEGER, $this->nameScopeParameter('topLevel'))
            )
        );
    }

    /**
     * Scope comment query by level.
     */
    protected function scopeLevel(QueryBuilder $builder, int $level): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'level',
                $builder->createNamedParameter($level, ParameterType::INTEGER, $this->nameScopeParameter('level'))
            )
        );
    }

    /**
     * Scope comment query by parent status.
     */
    protected function scopeIsParent(QueryBuilder $builder): void
    {
        $builder->andWhere(
            $builder->expr()->isNull('parent')
        );
    }

    /**
     * Scope comment by state.
     */
    protected function scopeState(QueryBuilder $builder, string $state): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'state',
                $builder->createNamedParameter($state, ParameterType::STRING, $this->nameScopeParameter('state'))
            )
        );
    }

    /**
     * Scope comment by id.
     */
    protected function scopeCommentId(QueryBuilder $builder, int $commentId): void
    {
        $this->scopePrimaryKey($builder, $this->getTable(), $this->getPrimaryKey(), $commentId);
    }

    /**
     * Scope comment by id author.
     */
    protected function scopeIdAuthor(QueryBuilder $builder, int $idAuthor): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'id_author',
                $builder->createNamedParameter($idAuthor, ParameterType::INTEGER, $this->nameScopeParameter('autorId'))
            )
        );
    }

    /**
     * Scope comment by date created.
     */
    protected function scopeCreatedFrom(QueryBuilder $builder, string $dateCreated): void
    {
        $builder->andWhere(
            $builder->expr()->gte(
                "DATE(`{$this->getTable()}`.`date_created`)",
                $builder->createNamedParameter($dateCreated, ParameterType::STRING, $this->nameScopeParameter('createdFrom'))
            )
        );
    }

    /**
     * Scope comment by date created.
     */
    protected function scopeCreatedTo(QueryBuilder $builder, string $dateCreated): void
    {
        $builder->andWhere(
            $builder->expr()->lte(
                "DATE(`{$this->getTable()}`.`date_created`)",
                $builder->createNamedParameter($dateCreated, ParameterType::STRING, $this->nameScopeParameter('createdTo'))
            )
        );
    }

    /**
     * Scope comment by date published.
     */
    protected function scopePublishedFrom(QueryBuilder $builder, string $datePublished): void
    {
        $builder->andWhere(
            $builder->expr()->gte(
                "DATE(`{$this->getTable()}`.`date_created`)",
                $builder->createNamedParameter($datePublished, ParameterType::STRING, $this->nameScopeParameter('publishedFrom'))
            )
        );
    }

    /**
     * Scope comment by date published.
     */
    protected function scopePublishedTo(QueryBuilder $builder, string $datePublished): void
    {
        $builder->andWhere(
            $builder->expr()->lte(
                "DATE(`{$this->getTable()}`.`date_created`)",
                $builder->createNamedParameter($datePublished, ParameterType::STRING, $this->nameScopeParameter('publishedTo'))
            )
        );
    }

    /**
     * Scope comment by resource token.
     */
    protected function scopeResourceToken(QueryBuilder $builder, string $token): void
    {
        /** @var Comment_Resources_Model $resources */
        $resources = model(Comment_Resources_Model::class);

        $builder->andWhere(
            $builder->expr()->eq(
                "`{$resources->getTable()}`.`token`",
                $builder->createNamedParameter($token, ParameterType::STRING, $this->nameScopeParameter('resourceToken'))
            )
        );
    }

    /**
     * Scope comment by resource tokens.
     */
    protected function scopeResourceTokens(QueryBuilder $builder, array $tokens): void
    {
        /** @var Comment_Resources_Model $resources */
        $resources = model(Comment_Resources_Model::class);

        $builder->andWhere(
            $builder->expr()->in(
                "`{$resources->getTable()}`.`token`",
                array_map(
                    fn (int $index, $user) => $builder->createNamedParameter(
                        (string) $user,
                        ParameterType::STRING,
                        $this->nameScopeParameter("resourceTokens{$index}")
                    ),
                    array_keys($tokens),
                    $tokens
                )
            )
        );
    }

    /**
     * Scope comment by id type.
     */
    protected function scopeIdType(QueryBuilder $builder, int $typeId): void
    {
        /** @var Comment_Types_Model $types */
        $types = model(Comment_Types_Model::class);

        $builder->andWhere(
            $builder->expr()->eq(
                "`{$types->getTable()}`.`id`",
                $builder->createNamedParameter($typeId, ParameterType::INTEGER, $this->nameScopeParameter('typeId'))
            )
        );
    }

    /**
     * Scope comment by date published.
     */
    protected function scopeAfterId(QueryBuilder $builder, int $commentId): void
    {
        if ($commentId) {
            $builder->andWhere(
                $builder->expr()->gt(
                    "`{$this->getTable()}`.`id`",
                    $builder->createNamedParameter($commentId, ParameterType::INTEGER, $this->nameScopeParameter('commentId'))
                )
            );
        }
    }

    /**
     * Scope for join with resources.
     */
    protected function bindResources(QueryBuilder $builder): void
    {
        /** @var Comment_Resources_Model $resources */
        $resources = model(Comment_Resources_Model::class);
        $builder
            ->leftJoin(
                $this->getTable(),
                $resources->getTable(),
                $resources->getTable(),
                "`{$resources->getTable()}`.`{$resources->getPrimaryKey()}` = `{$this->getTable()}`.`id_resource`"
            )
        ;
    }

    /**
     * Scope for join with types.
     */
    protected function bindTypes(QueryBuilder $builder): void
    {
        /** @var Comment_Resources_Model $resources */
        $resources = model(Comment_Resources_Model::class);
        $resourcesTable = $resources->getTable();

        /** @var Comment_Types_Model $types */
        $types = model(Comment_Types_Model::class);
        $typesTable = $types->getTable();
        $typesPrimaryKey = $types->getPrimaryKey();

        $builder
            ->leftJoin($this->getTable(), $typesTable, $typesTable, "`{$typesTable}`.`{$typesPrimaryKey}` = `{$resourcesTable}`.`id_type`")
        ;
    }

    /**
     * Resolves static relationships with author.
     */
    protected function author(): RelationInterface
    {
        /** @var User_Model $users */
        $users = model(User_Model::class);
        $usersTable = $users->get_users_table();
        $usersPrimaryKey = $users->get_users_table_primary_key();
        $usersGroupTable = $users->get_user_groups_table();
        $relation = $this->belongsTo(Comment_Authors_Model::class, 'id_author');
        $relation->disableNativeCast();
        $table = $relation->getRelated()->getTable();
        $builder = $relation->getQuery();
        $builder
            ->select(
                "`{$table}`.`id`",
                "`{$table}`.`id_user`",
                "`{$usersTable}`.`user_photo`",
                "`{$usersTable}`.`user_group`",
                "IFNULL(`{$table}`.`email`, `{$usersTable}`.`email`) AS `email`",
                "IFNULL(`{$table}`.`name`, TRIM(CONCAT(`{$usersTable}`.`fname`, ' ', `{$usersTable}`.`lname`))) AS `name`",
                "`{$table}`.`date_created` AS `created_at`",
                "`{$table}`.`date_updated` AS `updated_at`",
                "`{$table}`.`is_registered`",
                "`{$usersGroupTable}`.`gr_type` AS `group_type`",
            )
            ->leftJoin($table, $usersTable, $usersTable, "{$usersTable}.{$usersPrimaryKey} = {$table}.id_user")
            ->leftJoin($table, $usersGroupTable, $usersGroupTable, "`{$usersGroupTable}`.`idgroup` = `{$usersTable}`.`user_group`")
        ;

        return $relation;
    }

    /**
     * Resolves static relationships with resource.
     */
    protected function resource(): RelationInterface
    {
        /** @var Comment_Types_Model $types */
        $types = model(Comment_Types_Model::class);
        $typesTable = $types->getTable();
        $typesPrimaryKey = $types->getPrimaryKey();
        $relation = $this->belongsTo(Comment_Resources_Model::class, 'id_resource');
        $relation->disableNativeCast();
        $table = $relation->getRelated()->getTable();
        $builder = $relation->getQuery();
        $builder
            ->select(
                "`{$table}`.`id`",
                "`{$table}`.`token`",
                "`{$typesTable}`.`id` AS `type_id`",
                "`{$typesTable}`.`name` AS `type_name`",
            )
            ->leftJoin($table, $typesTable, null, "{$typesTable}.{$typesPrimaryKey} = {$table}.id_type")
        ;

        return $relation;
    }

    /**
     * Resolves static relationships with reports.
     */
    protected function reports(): RelationInterface
    {
        return $this->hasMany(Comment_Reports_Model::class, 'id_item')->disableNativeCast();
    }

    /**
     * Resolves static relationships with reports.
     */
    protected function children(): RelationInterface
    {
        return $this->hasMany(static::class, 'parent')->disableNativeCast();
    }
}

// End of file comments_model.php
// Location: /tinymvc/myapp/models/comments_model.php
