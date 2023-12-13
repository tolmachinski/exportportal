<?php

declare(strict_types=1);

namespace App\Common\Database\Scope;

use App\Common\Database\Exceptions\DBException;
use App\Common\Database\PortableModel;
use Throwable;

/**
 * Exception thrown for model relations.
 */
class BindingNotFoundException extends DBException
{
    /**
     * The instance of the oaffected model.
     */
    private object $model;

    /**
     * The name of the relation.
     */
    private string $binding;

    /**
     * Get the name of the binding.
     */
    public function getBinding(): string
    {
        return $this->binding;
    }

    /**
     * Get the instance of the oaffected model.
     */
    public function getModel(): object
    {
        return $this->model;
    }

    /**
     * Create instance of the exception.
     */
    public static function create(object $model, string $binding, ?Throwable $prevoius = null): self
    {
        $className = \get_class($model);
        if ($model instanceof PortableModel) {
            $message = "Call to undefined binding \"{$binding}\" on model for table \"{$model->getTable()}\".";
        } else {
            $message = "Call to undefined binding \"{$binding}\" on model \"{$className}\".";
        }

        $exception = new static($message, 0, $prevoius);
        $exception->model = $model;
        $exception->binding = $binding;

        return $exception;
    }
}
