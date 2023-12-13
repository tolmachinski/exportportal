<?php

declare(strict_types=1);

namespace App\Common\Database\Relations;

use App\Common\Database\Exceptions\DBException;
use App\Common\Database\Model;

/**
 * Exception thrown for model relations.
 */
class RelationEmptyKeysException extends DBException
{
    /**
     * The model relation.
     */
    private RelationInterface $relation;

    /**
     * Get the name of the relation.
     */
    public function getRelation(): RelationInterface
    {
        return $this->relation;
    }

    /**
     * Get the instance of the oaffected model.
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Create instance of the exception.
     */
    public static function create(RelationInterface $relation): self
    {
        $exception = new static('The constaints with the empty kyes are not accepted by this model.');
        $exception->relation = $relation;

        return $exception;
    }
}
