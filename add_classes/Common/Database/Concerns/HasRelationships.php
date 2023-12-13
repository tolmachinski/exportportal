<?php

declare(strict_types=1);

namespace App\Common\Database\Concerns;

use App\Common\Database\BaseModel;
use App\Common\Database\Model;
use App\Common\Database\PortableModel;
use App\Common\Database\Relations\BelongsTo;
use App\Common\Database\Relations\HasMany;
use App\Common\Database\Relations\HasManyThrough;
use App\Common\Database\Relations\HasOne;
use App\Common\Database\Relations\HasOneThrough;
use App\Common\Database\Relations\RelationEmptyKeysException;
use App\Common\Database\Relations\RelationInterface;
use App\Common\Database\Relations\RelationNotFoundException;
use App\Common\Database\Relations\Rule\RelationRule;
use App\Common\Database\Relations\Rule\RuleBuilder as RelationsRuleBuilder;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Doctrine\DBAL\Query\QueryBuilder;
use Symfony\Component\String\AbstractString;
use function Symfony\Component\String\u;

/**
 * Allows for the model to DB connections.
 */
trait HasRelationships
{
    /**
     * The list of the relation resolvers.
     *
     * @var Array<string, \Closure>
     */
    private static array $relationResolvers = [];

    /**
     * The instance of bulider for relations rules.
     */
    private ?RelationsRuleBuilder $relationsRuleBuilder;

    /**
     * Get relation by its name.
     *
     * @throws RelationNotFoundException if relation is not found
     */
    public function getRelation(string $name): RelationInterface
    {
        if (null === $resolver = $this->getRelationResolverFromName($name)) {
            throw RelationNotFoundException::create($this, $name);
        }

        return $resolver($this);
    }

    /**
     * Get the instance of relations rule builder.
     */
    public function getRelationsRuleBuilder(): RelationsRuleBuilder
    {
        if (!isset($this->relationsRuleBuilder)) {
            $this->relationsRuleBuilder = new RelationsRuleBuilder($this->getConnection());
        }

        return $this->relationsRuleBuilder;
    }

    /**
     * Parse the nested relationships in a relation.
     */
    protected function parseNestedRelations(array $relations, ?string $segmentPrefix = null): array
    {
        if (empty($relations)) {
            return [];
        }

        // First we will normalize the entries in the
        // relationship list.
        $parsed = [];
        foreach ($relations as $relation => $constraints) {
            if (is_numeric($relation)) {
                // If the relation name is numeric, we must take the constraint as the name.
                if (!is_string($constraints)) {
                    // If relation name is not string, we must skip it.
                    continue;
                }

                $relation = $constraints;
                // Put empty function to preserve the flow.
                $constraints = static function () { /* HC SVNT DRACONES */ };
            }

            // In legacy models we use several tables for the model even for relations.
            // The only way to distinguish between them is to use the prefix per type.
            // Here we need to add prefix to the relation name to make it work properly.
            // In the case of the nested relations we need to add it only to the first
            // level of nesting.
            if (null !== $segmentPrefix) {
                $segments = \explode('.', $relation);
                $originalName = \array_shift($segments);
                $relation = $this->appendPrefixToTheRelationName($relation, $segmentPrefix);
                list($firstSegment, $additionalSegments) = \explode('.', $relation, 2);
                list($name, $alias) = $this->splitRelationNameInParts($firstSegment);
                // If alias is not set for prefixed relation, we will use original name as alias.
                if (null === $alias) {
                    $alias = $originalName;
                }

                $relation = \implode('.', ["{$name} as {$alias}", ...($additionalSegments ?? [])]);
            }

            $parsed[$relation] = $constraints;
        }

        return $parsed;
    }

    /**
     * Adds nested with relations to the query results.
     */
    protected function addNestedWithRelations(array $relationships, array $records): array
    {
        // This aggreagator will used to hold the loaded nested records.
        $aggregator = new ArrayCollection([]);
        foreach ($relationships as $relation => $constraints) {
            $records = $this->addNestedWithRelation($this, $relation, null, $records, $aggregator, $constraints);
        }

        return $records;
    }

    /**
     * Add nested with relation to the set of data.
     *
     * @param BaseModel|Model $model
     */
    protected function addNestedWithRelation(
        $model,
        string $key,
        ?string $previousLevelKey,
        array &$records,
        Collection $aggregator,
        \Closure $constraints,
        bool $disableCast = false
    ) {
        $pattern = '/^([^\\s]+)(\\s+as\\s+(.+))?$/i';
        $segments = explode('.', $key);
        $firstSegment = \array_shift($segments);
        $remainingKey = empty($segments) ? null : \implode('.', $segments);
        if (!\preg_match($pattern, $firstSegment, $matches, PREG_UNMATCHED_AS_NULL, 0)) {
            throw new \RuntimeException(\sprintf(
                'The relation name "%s" has invalid format',
                \trim("{$previousLevelKey}.{$key}", '.')
            ));
        }

        // First of all, we need to split the name of the realtion into name and alias
        list($name, $alias) = $this->splitRelationNameInParts($firstSegment);
        // Get relation by its name.
        $relation = $model->getRelation((string) $this->createRelationshipName($name));
        // Enforce disabling cast by parameter. Beware, that it can be overriden in constraints.
        if ($disableCast) {
            $relation->disableNativeCast();
        }
        // Make relation field name
        $fieldName = $this->makeRelationNameForEagerLoading($relation, $name, $alias);

        // If $remainingKey is NULL it means that we reached the end of the
        // nested fragment. At this moment we can load the final set of data.
        // This set will be not aggregated becuase it can be changed using
        // constaints.
        if (null === $remainingKey) {
            return $relation->match(
                $records,
                $this->loadNestedWithRecords($relation, $records, $constraints),
                $fieldName
            );
        }

        $currentLevel = \trim("{$previousLevelKey}.{$name}", '.');
        if ($aggregator->containsKey($currentLevel)) {
            $currentRecords = $aggregator->get($currentLevel);
        } else {
            $currentRecords = $this->loadNestedWithRecords($relation, $records, $constraints);
        }

        // Now verey hard and confusing part
        // In the case when we have a lot of nested parts with branching from one of them
        // like this: ['entityLevel1A.entityLevel2A.entityLevel3A', 'entityLevel1A.entityLevel2B.entityLevel3B']
        // We need to override existing values in aggregator to preserve
        // already found records. Otherwise they will be overriden during next iteration
        $aggregator->set(
            $currentLevel,
            $foundNestedRecords = new ArrayCollection(
                $this->addNestedWithRelation($relation->getRelated(), $remainingKey, $currentLevel, $currentRecords->toArray(), $aggregator, $constraints)
            )
        );

        return $relation->match($records, $foundNestedRecords, $fieldName);
    }

    /**
     * Eager load of the relationships for the DB records.
     *
     * @deprecated `v2.39` in favor of the `self::addNestedWithRelations()`
     * @see \App\Common\Database\Concerns\HasRelationships::addNestedWithRelations()
     */
    protected function loadEagerRelations(array $relationships, array $records, ?string $cut = null): array
    {
        foreach ($relationships as list($name, $alias, $constraints)) {
            $records = $this->loadEagerRelation(
                $records,
                $name,
                $alias,
                $constraints,
                $cut,
                // In the cast when nested cast was disabled in the parent model,
                // we need to explicitly disable it in the relation.
                // This would work as well when we introduce nested relations.
                $this instanceof Model ? $this->isNestedCastDisabled() : false
            );
        }

        return $records;
    }

    /**
     * Eager load of the count relationships for the DB records.
     *
     * @deprecated `v2.39` in favor of the `self::addWithCountRelations()`
     * @see \App\Common\Database\Concerns\HasRelationships::addWithCountRelations()
     */
    protected function loadEagerCountRelations(array $relationships, array $records, ?string $cut = null): array
    {
        foreach ($relationships as list($name, $alias, $constraints)) {
            $records = $this->loadEagerCountRelation(
                $records,
                $name,
                $alias,
                $constraints,
                $cut,
                // In the cast when nested cast was disabled in the parent model,
                // we need to explicitly disable it in the relation.
                // This would work as well when we introduce nested relations.
                $this instanceof Model ? $this->isNestedCastDisabled() : false
            );
        }

        return $records;
    }

    /**
     * Add subqueries to count the relations.
     */
    protected function addWithCountRelations(QueryBuilder $query, array $relations, ?string $prefix = null): void
    {
        $this->addAggregatesToQuery($query, $relations, '*', 'count', $prefix);
    }

    /**
     * Add subqueries to include an aggregate value for a relationship.
     */
    protected function addAggregatesToQuery(QueryBuilder $query, array $relations, string $column = '*', ?string $function = null, ?string $prefix = null): void
    {
        if (empty($relations)) {
            return;
        }

        // First, the name of the function must be transformed to the lower case.
        $function = u($function)->lower()->toString();
        // Next, we will take the name of the table from the first `FROM` entry in the query
        $fromSegment = $query->getQueryPart('from')[0];
        $tableName = $fromSegment['alias'] ?? $fromSegment['table'] ?? null;
        // After that, we will ensure that the columns exist in the query
        $columns = $query->getQueryPart('select');
        if (null === $columns) {
            $query->select('*');
        }

        $relations = is_array($relations) ? $relations : [$relations];
        foreach ($this->parseNestedRelations($relations) as $relationName => $constraints) {
            if (false !== \strpos($relationName, '.')) {
                throw new \RuntimeException('The relation count doesn\'t support nesting.');
            }
            // First we will determine if the name has been aliased using an "as" clause on the name
            // and if it has we will extract the actual relationship name and the desired name of
            // the resulting column. This allows multiple aggregates on the same relationships.
            list($name, $alias) = $this->splitRelationNameInParts($relationName);
            if (null !== $prefix) {
                $name = $this->appendPrefixToTheRelationName($name, $prefix);
            }
            // Get relation by its name.
            $relation = $this->getRelation((string) $this->createRelationshipName($name));
            $relationQuery = $relation->getQuery();
            $related = $relation->getRelated();

            if (null !== $function) {
                $relatedFromSegment = $relationQuery->getQueryPart('from')[0];
                $relatedTableName = $relatedFromSegment['alias'] ?? $relatedFromSegment['table'] ?? null;
                if ($tableName === $relatedTableName) {
                    $hashedColumn = "{$relation->getRelationCountHash(false)}.{$column}";
                } else {
                    $hashedColumn = $column;
                }

                $wrappedColumn = '*' === $column ? $column : $related->qualifyColumn($hashedColumn);
                $expression = 'exists' === $function ? $wrappedColumn : sprintf('%s(%s)', u($function)->upper()->toString(), $wrappedColumn);
            } else {
                $expression = $column;
            }

            $subQuery = $related->createQueryBuilder()->from($related->getTable());
            $countQuery = $relation
                ->getExistenceQuery($subQuery, $query, [$expression])
            ;
            // Run constraints (if exists).
            $constraints($relation, $countQuery);

            // Append constraints and parameters
            $this->mergeWhereConstraintsIntoQuery($countQuery, $relationQuery->getQueryPart('where'));
            $this->mergeSubqueryParamtersIntoQuery($countQuery, $relationQuery);

            // If the query contains certain elements like orderings / more than one column selected
            // then we will remove those elements from the query so that it will execute properly
            // when given to the database. Otherwise, we may receive SQL errors or poor syntax.
            $countQuery->resetQueryPart('orderBy');
            $selectedColumns = (array) $countQuery->getQueryPart('select');
            if (\count($selectedColumns) > 1) {
                $countQuery->select($selectedColumns[0]);
            }

            // On the next steps, we will make the proper column alias to the query and run this sub-select on
            // the query builder.
            $alias = $alias ?? u(preg_replace('/[^[:alnum:][:space:]_]/u', '', "{$name} {$function} {$column}"))->snake()->toString();

            // After that we need to prepare our subquery
            if ('exists' === $function) {
                $newColumn = sprintf('EXISTS(%s) AS %s', $countQuery->getSQL(), "`{$alias}`");
                // In the case of the `EXISTS` query the result will be casted to the boolean value
                $this->mergeCasts([$alias => 'bool']);
            } else {
                if (null === $function) {
                    $countQuery->setMaxResults(1);
                } elseif ('count' === $function) {
                    $this->mergeCasts([$alias => 'int']);
                }

                $newColumn = sprintf('(%s) AS %s', $countQuery->getSQL(), "`{$alias}`");
            }
            // And add it ot the columns of the original query.
            $query->select(...\array_merge((array) $query->getQueryPart('select'), [$newColumn]));
            // In addition, we need to add the parameters from the query to the original query as well.
            $this->mergeSubqueryParamtersIntoQuery($query, $countQuery);
        }
    }

    /**
     * Eager load of the count relationships for the DB records.
     *
     * @param RelationRule[] $existenseRules
     */
    protected function attachExistenceRelations(QueryBuilder $queryBuilder, array $existenseRules, ?string $cut = null): void
    {
        if (empty($existenseRules)) {
            return;
        }

        foreach ($existenseRules as $rule) {
            if (!$rule instanceof RelationRule) {
                throw new \InvalidArgumentException(
                    \sprintf('The rule must be instance of %s class', RelationRule::class)
                );
            }
            // Given that we have a lot of legacy code
            // somewhere the value $cat can be used that is why we need to do some shenanigans with the
            // relation name. But only if rule contains the name of the relation, not relation itself.
            $relationName = $rule->getRelation();
            if (\is_string($relationName)) {
                // First of all, we are splitting it into name and alias.
                // We don't need alias, so we will drop it.
                list($name) = $this->splitRelationNameInParts($relationName);
                // We will add the $cut value to the name to not allow BC breaks.
                if (null !== $cut) {
                    $name = \sprintf('%s%s', (string) u($cut)->camel(), \ucfirst($name) ?: $name);
                }
                // And replace the original rule with new cleaned up name
                $rule = $rule->withRelation($name);
                if (false !== strpos($name, '.')) {
                    $this->addNestedExistenseRelationToQuery($queryBuilder, $rule);

                    continue;
                }
            }

            $this->addExistenseRelationToQuery($queryBuilder, $rule);
        }
    }

    /**
     * Adds existense realtion to the query.
     */
    protected function addExistenseRelationToQuery(QueryBuilder $query, RelationRule $relationRule): void
    {
        // Get relation by its name.
        $relation = $relationRule->getRelation();
        if (!$relation instanceof RelationInterface) {
            $relation = $this->getRelation($relation);
            $relationRule = $relationRule->withRelation($relation);
        }
        // If we only need to check for the existence of the relation, then we can optimize
        // the subquery to only run a "where exists" clause instead of this full "count"
        // clause. This will make these queries run much faster compared with a count.
        $method = $relationRule->canUseExists() ? 'getExistenceQuery' : 'getExistenceCountQuery';
        // To get the relation query we need to provide the base query and parent query builders.
        // Given that parent query IS NOT changed during this process, we can safely do this.
        /** @var QueryBuilder $hasQuery */
        $hasQuery = $relation->{$method}($relation->getQuery(), $query);
        // Next we will call any given constraints so that we can get the proper logical grouping
        // afterwards when we will apply
        if ($scope = $relationRule->getScope()) {
            $this->addRelationWheresWithinScopeGroup($hasQuery, $relationRule, $scope);
        }

        $relationRule->canUseExists()
            ? $this->addWhereExistsQuery($query, $relation->getQuery(), $relationRule)
            : $this->addWhereCountQuery($query, $relation->getQuery(), $relationRule);
    }

    /**
     * Adds nested existense realtion to the query.
     */
    protected function addNestedExistenseRelationToQuery(QueryBuilder $query, RelationRule $relationRule)
    {
        $relations = $relationRule->getRelation();
        if (!\is_string($relations)) {
            throw new \RuntimeException('Only relations that are not resolved yet can be used as nested ones.');
        }

        $count = $relationRule->getCount();
        $operator = $relationRule->getOperator();
        $callback = $relationRule->getScope();
        $relations = explode('.', $relations);
        $doesntHave = '<' === $operator && 1 === $count;
        if ($doesntHave) {
            $operator = '>=';
            $count = 1;
        }

        $closure = function (QueryBuilder $q, RelationInterface $r) use (&$closure, &$relations, $operator, $count, $callback) {
            $related = $r->getRelated();
            // In order to nest "has", we need to add count relation constraints on the
            // callback Closure. We'll do this by simply passing the Closure its own
            // reference to itself so it calls itself recursively on each segment.
            count($relations) > 1
                ? $related->addExistenseRelationToQuery($q, $this->getRelationsRuleBuilder()->whereHas(array_shift($relations), $closure))
                : $related->addExistenseRelationToQuery(
                    $q,
                    $this->getRelationsRuleBuilder()->has(array_shift($relations), $operator, $count)->withScope($callback)
                )
            ;
        };

        $this->addExistenseRelationToQuery(
            $query,
            $relationRule
                ->withRelation(array_shift($relations))
                ->withOperator($doesntHave ? '<' : '>=')
                ->withCount(1)
                ->withScope($closure)
        );
    }

    /**
     * Add a sub-query count clause to this query.
     */
    protected function addWhereCountQuery(QueryBuilder $query, QueryBuilder $countQuery, RelationRule $relationRule): void
    {
        // Merge the parameters into the main query.
        $this->mergeSubqueryParamtersIntoQuery($query, $countQuery);

        $count = $relationRule->getCount();
        // If operator is empty, then we can use default
        $operator = $relationRule->getOperator() ?? '=';
        // Make expression from count query count and operator.
        $expression = \sprintf('(%s) %s %s', $countQuery->getSQL(), $operator, \is_numeric($count) ? (string) $count : $count);
        // And finally the expression will be added into the query.
        $this->appendExpressionToTheQuery(
            $query,
            $expression,
            $relationRule->getType()
        );
    }

    /**
     * Add an exists clause to the query.
     */
    protected function addWhereExistsQuery(QueryBuilder $query, QueryBuilder $countQuery, RelationRule $relationRule): void
    {
        // Merge the parameters into the main query.
        $this->mergeSubqueryParamtersIntoQuery($query, $countQuery);

        // If operator is empty, then we can use default
        $operator = $operator ?? '=';
        // Make expression from count query count and operator.
        $expression = \sprintf('%s (%s)', $relationRule->isNegation() ? 'NOT EXISTS' : 'EXISTS', $countQuery->getSQL());
        // And finally the expression will be added into the query.
        $this->appendExpressionToTheQuery(
            $query,
            $expression,
            $relationRule->getType()
        );
    }

    /**
     * Appends the expression to the end of query without wrapping it into the group.
     *
     * @param CompositeExpression|string $expression
     */
    protected function appendExpressionToTheQuery(QueryBuilder $query, $expression, string $type = CompositeExpression::TYPE_AND): void
    {
        // And here we will append the expression to the query where clauses
        // with the proper type.
        if (!$expression instanceof CompositeExpression) {
            // Wrap string expressions into the composite expression using provided type
            switch ($type) {
                case CompositeExpression::TYPE_AND:
                    $query->andWhere(CompositeExpression::and($expression));

                    break;
                case CompositeExpression::TYPE_OR:
                    $query->orWhere(CompositeExpression::or($expression));

                    break;

                default:
                    // If boolean opeartion is not supported then we will exit rigth here.
                    throw new \InvalidArgumentException(
                        \sprintf('The type "%s" for WHERE clause is not supported', $type)
                    );
            }
        }
    }

    /**
     * Merges the subquery parameters into the one query.
     */
    protected function mergeSubqueryParamtersIntoQuery(QueryBuilder $originalQuery, QueryBuilder ...$attachedQueries): void
    {
        $originalTypes = $originalQuery->getParameterTypes();
        $originalParameters = $originalQuery->getParameters();
        foreach ($attachedQueries as $attachedQuery) {
            $newTypes = $attachedQuery->getParameterTypes();
            $newParameters = $attachedQuery->getParameters() ?? [];
            // Reset counter
            $k = count($originalParameters);
            foreach ($newParameters as $key => $value) {
                if (!\is_string($key) && \is_int($key)) {
                    $paramKey = $k;
                    ++$k;
                } else {
                    $paramKey = $key;
                }

                $originalParameters[$paramKey] = $value;
                if (isset($newTypes[$key])) {
                    $originalTypes[$paramKey] = $newTypes[$key];
                }
            }
        }

        $originalQuery->setParameters($originalParameters, $originalTypes);
    }

    /**
     * Adds the wehere conditions to properly nest them in the query.
     */
    protected function mergeWhereConstraintsIntoQuery(QueryBuilder $query, ?CompositeExpression $addedWhereParts = null): void
    {
        if (null === $addedWhereParts) {
            return;
        }

        // Here we will rebuild all the where clauses in the query to properly group scope where
        // clauses and prevetnts something like this (<original where>) OR (...).
        // First let's get the all existing at this time where clauses
        /** @var null|CompositeExpression $originalWhereCount */
        $originalWherePart = $query->getQueryPart('where');
        // And reset query. Why? Because Doctrine DBAL creates nested expressions and we cannot find the original
        // in this tree.
        $query->resetQueryPart('where');
        // And finally, we collect and re-group the clauses to properly wrap them in the group.
        $clauses = [$originalWherePart];
        if ($addedWhereParts) {
            $clauses[] = $addedWhereParts;
        }

        $query->where(...$clauses);
    }

    /**
     * Create HAS ONE relationship.
     *
     * @param mixed $related
     */
    protected function hasOne($related, ?string $foreignKey = null, ?string $localKey = null, ?string $relation = null): RelationInterface
    {
        // If no relation name was given, we will use the calling method's name as the relationship name
        if (null === $relation) {
            $relation = $this->guessRelationName();
        }

        // Get the parent instance
        $parent = $this->resolveRelatedModel($this);
        // Get the related instance.
        $instance = $this->resolveRelatedModel($related);
        // If foreign key is not supplied we can use relation name to guess the foreign key name
        $foreignKey = $foreignKey ?? $parent->getForeignKey();
        // If owner key is not supplied we can use the related instance key
        $localKey = $localKey ?? $parent->getPrimaryKey();

        return new HasOne(
            $this->createQueryBuilder(),
            $instance,
            $parent,
            $foreignKey,
            $localKey,
            $relation
        );
    }

    /**
     * Create HAS ONE THROUGH relationship.
     *
     * @param mixed $related
     * @param mixed $through
     */
    protected function hasOneThrough(
        $related,
        $through,
        ?string $firstKey = null,
        ?string $secondKey = null,
        ?string $localKey = null,
        ?string $secondLocalKey = null,
        ?string $relation = null
    ): RelationInterface {
        // If no relation name was given, we will use the calling method's name as the relationship name.
        if (null === $relation) {
            $relation = $this->guessRelationName();
        }

        // Get the parent instance.
        $parent = $this->resolveRelatedModel($this);
        // Get the related instance.
        $related = $this->resolveRelatedModel($related);
        // Get the through instance.
        $through = $this->resolveRelatedModel($through);

        return new HasOneThrough(
            $this->createQueryBuilder(),
            $related,
            $parent,
            $through,
            $firstKey ?: $parent->getForeignKey(),
            $secondKey ?: $through->getForeignKey(),
            $localKey ?: $parent->getPrimaryKey(),
            $secondLocalKey ?: $through->getPrimaryKey(),
            $relation
        );
    }

    /**
     * Create HAS MANY relationship.
     *
     * @param mixed $related
     */
    protected function hasMany($related, ?string $foreignKey = null, ?string $localKey = null, ?string $relation = null): RelationInterface
    {
        // If no relation name was given, we will use the calling method's name as the relationship name
        if (null === $relation) {
            $relation = $this->guessRelationName();
        }

        // Get the parent instance
        $parent = $this->resolveRelatedModel($this);
        // Get the related instance.
        $instance = $this->resolveRelatedModel($related);
        // If foreign key is not supplied we can use relation name to guess the foreign key name
        $foreignKey = $foreignKey ?? $parent->getForeignKey();
        // If owner key is not supplied we can use the related instance key
        $localKey = $localKey ?? $parent->getPrimaryKey();

        return new HasMany(
            $this->createQueryBuilder(),
            $instance,
            $parent,
            $foreignKey,
            $localKey,
            $relation
        );
    }

    /**
     * Create HAS MANY THROUGH relationship.
     *
     * @param mixed $related
     * @param mixed $through
     */
    protected function hasManyThrough(
        $related,
        $through,
        ?string $firstKey = null,
        ?string $secondKey = null,
        ?string $localKey = null,
        ?string $secondLocalKey = null,
        ?string $relation = null
    ): RelationInterface {
        // If no relation name was given, we will use the calling method's name as the relationship name.
        if (null === $relation) {
            $relation = $this->guessRelationName();
        }

        // Get the parent instance.
        $parent = $this->resolveRelatedModel($this);
        // Get the related instance.
        $related = $this->resolveRelatedModel($related);
        // Get the through instance.
        $through = $this->resolveRelatedModel($through);

        return new HasManyThrough(
            $this->createQueryBuilder(),
            $related,
            $parent,
            $through,
            $firstKey ?: $parent->getForeignKey(),
            $secondKey ?: $through->getForeignKey(),
            $localKey ?: $parent->getPrimaryKey(),
            $secondLocalKey ?: $through->getPrimaryKey(),
            $relation
        );
    }

    /**
     * Creates BELONG TO relationship.
     *
     * @param mixed $related
     */
    protected function belongsTo($related, ?string $foreignKey = null, ?string $ownerKey = null, ?string $relation = null): RelationInterface
    {
        // If no relation name was given, we will use the calling method's name as the relationship name
        if (null === $relation) {
            $relation = $this->guessRelationName();
        }

        // Get the child instance
        $child = $this->resolveRelatedModel($this);
        // Get the related instance.
        $instance = $this->resolveRelatedModel($related);
        // If foreign key is not supplied we can use relation name to guess the foreign key name
        $foreignKey = $foreignKey ?? $instance->getForeignKey();
        // If owner key is not supplied we can use the related instance key
        $ownerKey = $ownerKey ?? $instance->getPrimaryKey();

        return new BelongsTo(
            $this->createQueryBuilder(),
            $instance,
            $child,
            $foreignKey,
            $ownerKey,
            $relation
        );
    }

    /**
     * Parses the list of the relations.
     *
     * @deprecated `v2.39` in favor of the `self::parseNestedRelations()`
     * @see \App\Common\Database\Concerns\HasRelationships::parseNestedRelations()
     */
    protected function parseRelations(array $relations): array
    {
        if (empty($relations)) {
            return [];
        }

        $parsed = [];
        foreach ($relations as $relation => $constraints) {
            if (is_numeric($relation)) {
                // If the relation name is numeric, we must take the constraint as the name.
                if (!is_string($constraints)) {
                    // If relation name is not string, we must skip it.
                    continue;
                }

                $relation = $constraints;
                // Put empty function to preserve the flow.
                $constraints = function () { /* Here be dragons */ };
            }
            // Allow to rename relations usign 'AS'
            list($name, $alias) = $this->splitRelationNameInParts($relation);

            $parsed[$relation] = [$name ?? $relation, $alias ?: null, $constraints];
        }

        return $parsed;
    }

    /**
     * Creates new related instance of the model for relation.
     *
     * @param BaseModel|Model|string $source
     */
    protected function resolveRelatedModel($source): Model
    {
        if (\is_object($source)) {
            switch (true) {
                case $source instanceof Model:
                    return $source;

                    break;
                case $source instanceof BaseModel:
                    return new PortableModel($this->getHandler(), (string) u(classBasename($source))->snake());

                    break;

                default:
                    throw new \InvalidArgumentException(
                        \sprintf(
                            'The relationship target must be a table name or instance of %s or %s classes.',
                            BaseModel::class,
                            Model::class,
                        )
                    );
            }
        }

        if (\is_string($source)) {
            $className = $source;
            if (PortableModel::class === $className) {
                throw new \InvalidArgumentException(
                    \sprintf('The provided source cannot be instance of %s', $className)
                );
            }

            if (
                \class_exists($className) && \is_subclass_of($source, Model::class)
            ) {
                return new $source($this->getHandler());
            }

            return new PortableModel($this->getHandler(), $source);
        }
    }

    /**
     * Appends the prefix to the relation name. Works only for legacy realtions.
     */
    private function appendPrefixToTheRelationName(string $relationName, string $prefix): string
    {
        return \sprintf(
            '%s%s%s',
            $this->createRelationshipName($prefix),
            \mb_strtoupper(\mb_substr($relationName, 0, 1)),
            \mb_substr($relationName, 1)
        );
    }

    /**
     * Loads the eager records from the nested `with` relationship.
     */
    private function loadNestedWithRecords(RelationInterface $relation, array $records, \Closure $constraints)
    {
        try {
            // Add constraints for the records
            $relation->addEagerConstraints($records);
            // Run constraints (if exists).
            $constraints($relation);
        } catch (RelationEmptyKeysException $e) {
            return new ArrayCollection();
        }

        return $relation->getEager();
    }

    /**
     * Adds the wehere conditions to properly nest them in the query.
     */
    private function addRelationWheresWithinScopeGroup(QueryBuilder $query, RelationRule $relationRule, \Closure $scope)
    {
        // Here we will rebuild all the where clauses in the query to properly group scope where
        // clauses and prevetnts something like this (<original where>) OR (...).
        // First let's get the all existing at this time where clauses
        /** @var null|CompositeExpression $originalWhereCount */
        $originalWherePart = $query->getQueryPart('where');
        // And reset query. Why? Because Doctrine DBAL creates nested expressions and we cannot find the original
        // in this tree.
        $query->resetQueryPart('where');
        // Next, run the scope to add other clauses and do the same things as with original clauses.
        $scope($query, $relationRule->getRelation());
        /** @var null|CompositeExpression $scopeQueryParts */
        $scopeQueryParts = $query->getQueryPart('where');
        $query->resetQueryPart('where');
        // And finally, we collect and re-group the clauses to properly wrap them in the group.
        $clauses = [$originalWherePart];
        if ($scopeQueryParts) {
            $clauses[] = $scopeQueryParts;
        }
        $query->where(...$clauses);
    }

    /**
     * Guess the relationship name.
     */
    private function guessRelationName(): string
    {
        list(, , $caller) = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);

        return $caller['function'];
    }

    /**
     * Eagerly load the relationship on a set of records.
     *
     * @deprecated `v2.39` no replacement provided
     */
    private function loadEagerRelation(
        array $records,
        string $name,
        ?string $alias,
        \Closure $constraints,
        ?string $cut = null,
        bool $disableCast = false
    ): array {
        try {
            // First of all, normalize $cut value
            $cut = null !== $cut ? (string) $this->createRelationshipName($cut) : null;
            // Get relation by its name.
            $relation = $this->getRelation((string) $this->createRelationshipName($cut, $name));
            // Enforce disabling cast by parameter. Beware, that it can be overriden in constraints.
            if ($disableCast) {
                $relation->disableNativeCast();
            }
            // Make relation count name
            $relationName = $this->makeRelationNameForEagerLoading($relation, $name, $alias, $cut);
            // Add constraints for the records
            $relation->addEagerConstraints($records);
            // Run constraints (if exists).
            $constraints($relation);
        } catch (RelationEmptyKeysException $e) {
            return $relation->match($records, new ArrayCollection(), $relationName);
        }

        return $relation->match($records, $relation->getEager(), $relationName);
    }

    /**
     * Eagerly load the relationship as count on a set of records.
     *
     * @deprecated `v2.39` no replacement provided
     */
    private function loadEagerCountRelation(
        array $records,
        string $name,
        ?string $alias,
        \Closure $constraints,
        ?string $cut = null,
        bool $disableCast = false
    ): array {
        try {
            // First of all, normalize $cut value
            $cut = null !== $cut ? (string) $this->createRelationshipName($cut) : null;
            // Get relation by its name.
            $relation = $this->getRelation((string) $this->createRelationshipName($cut, $name));
            // Enforce disabling cast by parameter. Beware, that it can be overriden in constraints.
            if ($disableCast) {
                $relation->disableNativeCast();
            }
            // Make relation count name
            $relationName = $this->makeRelationNameForEagerLoading($relation, $name, $alias, $cut);
            // Only in the cases when alias is null we add the prefix `count_` to the name
            if (null === $alias) {
                $relationName = "count_{$relationName}";
            }
            // Add constraints for the records
            $relation->addEagerConstraints($records);
            // Run constraints (if exists).
            $constraints($relation, $relation->getQuery());
        } catch (RelationEmptyKeysException $e) {
            return (new ArrayCollection($relation->match($records, new ArrayCollection(), $relationName)))
                ->map(function (array $record) use ($relationName) {
                    $record[$relationName] = 0;

                    return $record;
                })
                ->toArray()
            ;
        }

        return (new ArrayCollection($relation->match($records, $relation->getEagerCount(), $relationName)))
            ->map(function (array $record) use ($relationName) {
                // Skip if empty
                if (empty($record[$relationName])) {
                    $record[$relationName] = 0;

                    return $record;
                }

                // If record is a Collection - make it an array.
                if ($record[$relationName] instanceof Collection) {
                    $record[$relationName] = $record[$relationName]->toArray();
                }

                if (empty($record[$relationName]) || !is_array($record[$relationName])) {
                    $record[$relationName] = 0;

                    return $record;
                }

                if (is_array($record[$relationName][0] ?? null)) {
                    $record[$relationName] = array_sum(array_map('intval', array_column($record[$relationName], 'AGGREGATE')));
                } else {
                    $record[$relationName] = (int) ($record[$relationName]['AGGREGATE'] ?? 0);
                }

                return $record;
            })
            ->toArray()
        ;
    }

    /**
     * Creates relationship name.
     */
    private function createRelationshipName(?string ...$parts): AbstractString
    {
        return u(\implode('_', \array_filter([...$parts], fn ($p) => null !== $p)))->camel();
    }

    /**
     * Returns relation from name.
     */
    private function getRelationResolverFromName(string $name): ?\Closure
    {
        $modelName = \get_class($this);
        if (!isset(static::$relationResolvers[$modelName][$name])) {
            if (!\method_exists($this, $name)) {
                return null;
            }

            $modelName = \get_class($this);
            $returnType = (new \ReflectionMethod($this, $name))->getReturnType();
            $returnTypeName = \method_exists($returnType, 'getName') ? $returnType->getName() : (string) $returnType;
            if (RelationInterface::class === $returnTypeName || \is_subclass_of($returnTypeName, RelationInterface::class)) {
                static::addRelationResolver($name, fn ($model) => $model->{$name}());
            }
        }

        return static::$relationResolvers[$modelName][$name] ?? null;
    }

    /**
     * Splits the name of the relation in the anme and alias (if exists).
     *
     * @return string[]
     */
    private function splitRelationNameInParts(string $relationName): array
    {
        $name = null;
        $alias = null;
        if (\preg_match('/^(.+)\s+as\s+(.+)$/i', $relationName, $matches, PREG_UNMATCHED_AS_NULL, 0)) {
            list(, $name, $alias) = $matches;
        }

        return [$name ?? $relationName, $alias ?: null];
    }

    /**
     * Define a dynamic relation resolver.
     */
    private static function addRelationResolver(string $name, \Closure $resolver): void
    {
        static::$relationResolvers = array_replace_recursive(
            static::$relationResolvers,
            [static::class => [$name => $resolver]]
        );
    }

    /**
     * Make the name for eager loading methods.
     */
    private function makeRelationNameForEagerLoading(RelationInterface $relation, string $name, ?string $alias, ?string $cut = null): string
    {
        // Make relation count name
        // The precendence here is the following: alias > explicit name > name from call
        // The biggest problem here is the $cut value that works only for limited
        // amount of cases in legacy models
        // That is why we need to adopt different stragtegies based on the fact,
        // do we have $cut or not

        // If we have alias, then down with everything else we can use alias right away
        if (null !== $alias) {
            return u($alias)->snake()->toString();
        }

        // Else we need to do some namimg magic.
        // Given the fact that the name of the relation can be changed explicitly, we will use it.
        // Otherwise - the name parameter.
        $relationName = u($relation->getName() ?? $name);
        // If the $cut is not empty, that measn that we deal with legacy model
        // and we need to remove the $cut value from the beginning of the name
        // when it is present
        if (null !== $cut && $relationName->startsWith($cut)) {
            $relationName = $relationName->slice(u($cut)->length());
        }

        // And finally, we transform the string into the snake case.
        return $relationName->snake()->toString();
    }
}
