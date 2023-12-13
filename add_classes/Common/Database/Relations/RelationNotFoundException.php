<?php

declare(strict_types=1);

namespace App\Common\Database\Relations;

use App\Common\Database\BaseModel;
use App\Common\Database\Exceptions\DBException;
use App\Common\Database\Model;
use App\Common\Database\PortableModel;
use Throwable;

/**
 * Exception thrown for model relations.
 */
class RelationNotFoundException extends DBException
{
    /**
     * The instance of the oaffected model.
     *
     * @var BaseModel|Model
     */
    private object $model;

    /**
     * The name of the relation.
     */
    private string $relation;

    /**
     * Get the name of the relation.
     */
    public function getRelation(): string
    {
        return $this->relation;
    }

    /**
     * Get the instance of the oaffected model.
     *
     * @return BaseModel|Model
     */
    public function getModel(): object
    {
        return $this->model;
    }

    /**
     * Create instance of the exception.
     *
     * @param BaseModel|Model $model
     */
    public static function create(object $model, string $relation, ?Throwable $prevoius = null): self
    {
        $className = \get_class($model);
        if ($model instanceof PortableModel) {
            $message = "Call to undefined relationship \"{$relation}\" on model for table \"{$model->getTable()}\".";
        } else {
            $message = "Call to undefined relationship \"{$relation}\" on model \"{$className}\".";
        }

        $exception = new static($message, 0, $prevoius);
        $exception->model = $model;
        $exception->relation = $relation;

        return $exception;
    }
}
