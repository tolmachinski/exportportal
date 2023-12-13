<?php

declare(strict_types=1);

namespace App\Common\Database\Relations\Rule;

use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\Connection;

/**
 * RuleBuilder class is responsible to dynamically create SQL query parts.
 */
class RuleBuilder
{
    public const EQ = '=';
    public const NEQ = '<>';
    public const LT = '<';
    public const LTE = '<=';
    public const GT = '>';
    public const GTE = '>=';

    /**
     * The DBAL Connection.
     *
     * @var Connection
     */
    private $connection;

    /**
     * Initializes a new <tt>RuleBuilder</tt>.
     *
     * @param Connection $connection the DBAL Connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Create the 'HAS' relation rule.
     *
     * @param RelationInterface|string $relation
     * @param int|string               $count
     */
    public function has($relation, ?string $operator = self::GTE, $count = 1): RelationRule
    {
        return RelationRule::and($relation, null, $operator, $count);
    }

    /**
     * Create the 'OR_HAS' relation rule.
     *
     * @param RelationInterface|string $relation
     * @param int|string               $count
     */
    public function orHas($relation, ?string $operator = self::GTE, $count = 1): RelationRule
    {
        return RelationRule::or($relation, null, $operator, $count);
    }

    /**
     * Create the 'HAS_NOT' relation rule.
     *
     * @param RelationInterface|string $relation
     */
    public function hasNot($relation, ?\Closure $scope = null): RelationRule
    {
        return RelationRule::and($relation, $scope, RuleBuilder::LT, 1);
    }

    /**
     * Create the 'OR_HAS_NOT' relation rule.
     *
     * @param RelationInterface|string $relation
     */
    public function orHasNot($relation, \Closure $scope = null): RelationRule
    {
        return RelationRule::or($relation, $scope, RuleBuilder::LT, 1);
    }

    /**
     * Create the 'WHERE_HAS' relation rule.
     *
     * @param RelationInterface|string $relation
     * @param int|string               $count
     */
    public function whereHas($relation, \Closure $scope = null, ?string $operator = self::GTE, $count = 1): RelationRule
    {
        return RelationRule::and($relation, $scope, $operator, $count);
    }

    /**
     * Create the 'OR_WHERE_HAS' relation rule.
     *
     * @param RelationInterface|string $relation
     * @param int|string               $count
     */
    public function orWhereHas($relation, \Closure $scope = null, ?string $operator = self::GTE, $count = 1): RelationRule
    {
        return RelationRule::or($relation, $scope, $operator, $count);
    }

    /**
     * Create the 'WHERE_HAS_NOT' relation rule.
     *
     * @param RelationInterface|string $relation
     */
    public function whereHasNot($relation, \Closure $scope = null): RelationRule
    {
        return RelationRule::and($relation, $scope, RuleBuilder::LT, 1);
    }

    /**
     * Create the 'OR_WHERE_HAS_NOT' relation rule.
     *
     * @param RelationInterface|string $relation
     */
    public function orWhereHasNot($relation, \Closure $scope = null): RelationRule
    {
        return RelationRule::or($relation, $scope, RuleBuilder::LT, 1);
    }

    /**
     * Create the 'OR_WHERE_HAS_NOT' relation rule.
     *
     * @param RelationInterface|string $relation
     */
    public function with($relation, \Closure $scope = null): RelationRule
    {
        return RelationRule::and($relation, $scope);
    }
}
