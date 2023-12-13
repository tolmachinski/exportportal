<?php

declare(strict_types=1);

namespace App\Common\Database\Relations\Rule;

use App\Common\Database\Relations\RelationInterface;

/**
 * RelationRule is responsible to build an rule for relations.
 */
class RelationRule
{
    /**
     * Constant that represents an AND composite rule.
     */
    public const TYPE_AND = 'AND';

    /**
     * Constant that represents an OR composite rule.
     */
    public const TYPE_OR = 'OR';

    /**
     * The instance type of composite rule.
     */
    private string $type;

    /**
     * Tthe name of the relation.
     *
     * @var RelationInterface|string
     */
    private $relation;

    /**
     * The relation scope closure.
     */
    private ?\Closure $scope;

    /**
     * The existense operator.
     */
    private ?string $operator;

    /**
     * The count statement or number.
     *
     * @var null|int|string
     */
    private $count;

    /**
     * @internal use the and() / or() factory methods
     *
     * @param string                   $type     instance type of relation rule
     * @param RelationInterface|string $relation the name of the relation
     * @param \Closure                 $scope    relation scope closure
     * @param null|string              $operator the existense operator
     * @param null|int|string          $count    the count statement or number
     */
    protected function __construct(string $type, $relation, \Closure $scope = null, ?string $operator = null, $count = null)
    {
        $this->type = $type;
        $this->count = $count;
        $this->scope = $scope;
        $this->operator = $operator;
        $this->relation = $relation;
    }

    /**
     * Returns the rule with 'AND' type.
     *
     * @param RelationInterface|string $relation the name of the relation
     * @param \Closure                 $scope    relation scope closure
     * @param null|string              $operator the existense operator
     * @param null|int|string          $count    the count statement or number
     */
    public static function and($relation, \Closure $scope = null, ?string $operator = null, $count = null): self
    {
        return new self(self::TYPE_AND, $relation, $scope, $operator, $count);
    }

    /**
     * Returns the rule with 'OR' type.
     *
     * @param RelationInterface|string $relation the name of the relation
     * @param \Closure                 $scope    relation scope closure
     * @param null|string              $operator the existense operator
     * @param null|int|string          $count    the count statement or number
     */
    public static function or($relation, \Closure $scope = null, ?string $operator = null, $count = null): self
    {
        return new self(self::TYPE_OR, $relation, $scope, $operator, $count);
    }

    /**
     * Returns a new CompositeExpression with the given relation.
     *
     * @param RelationInterface|string $relation
     */
    public function withRelation($relation): self
    {
        $that = clone $this;
        $that->relation = $relation;

        return $that;
    }

    /**
     * Returns a new CompositeExpression with the given scope.
     */
    public function withScope(\Closure $scope = null): self
    {
        $that = clone $this;
        $that->scope = $scope;

        return $that;
    }

    /**
     * Returns a new CompositeExpression with the given operator.
     */
    public function withOperator(?string $operator = null): self
    {
        $that = clone $this;
        $that->operator = $operator;

        return $that;
    }

    /**
     * Returns a new CompositeExpression with the given parts added.
     *
     * @param null|int|string $count
     */
    public function withCount($count = null): self
    {
        $that = clone $this;
        $that->count = $count;

        return $that;
    }

    /**
     * Returns the type of this composite rule (AND/OR).
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get tthe name of the relation.
     *
     * @return RelationInterface|string
     */
    public function getRelation()
    {
        return $this->relation;
    }

    /**
     * Get the relation scope closure.
     */
    public function getScope(): ?\Closure
    {
        return $this->scope;
    }

    /**
     * Get the existense operator.
     */
    public function getOperator(): ?string
    {
        return $this->operator;
    }

    /**
     * Get the count statement or number.
     *
     * @return null|int|string
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * Determine if this rule can use for existene querries.
     */
    public function canUseExists(): bool
    {
        return ('>=' === $this->operator || '<' === $this->operator) && (1 === $this->count || null === $this->count);
    }

    /**
     * Determine if the rule describes the negation.
     */
    public function isNegation(): bool
    {
        return '<' === $this->operator && (1 === $this->count || null === $this->count);
    }
}
