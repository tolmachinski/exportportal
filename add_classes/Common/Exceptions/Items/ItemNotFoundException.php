<?php

namespace App\Common\Exceptions\Items;

use App\Common\Exceptions\NotFoundException;
use Throwable;

/**
 * The exception thrown when item is not found.
 *
 * @author Vlad A.
 */
class ItemNotFoundException extends NotFoundException
{
    /**
     * The item's ID.
     *
     * @var mixed
     */
    private $itemId;

    /**
     * {@inheritdoc}
     */
    public function __construct($itemId = null, string $message = 'The item with provided ID is not found', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->itemId = $itemId;
    }

    /**
     * Get the item's ID.
     *
     * @return mixed
     */
    public function getItemId()
    {
        return $this->itemId;
    }

    /**
     * Set the item's ID.
     *
     * @param mixed $itemId the item's ID
     *
     * @return self
     */
    public function setItemtId($itemId)
    {
        $this->itemId = $itemId;

        return $this;
    }
}