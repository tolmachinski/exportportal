<?php

namespace App\Logger;

class ActivityLogger extends Logger
{
    /**
     * Activity initiator.
     *
     * @var null|int
     */
    private $initiator;

    /**
     * Activity resource.
     *
     * @var null|int
     */
    private $resource;

    /**
     * Activity resource type.
     *
     * @var null|int
     */
    private $resourceType;

    /**
     * UActivity operation type.
     *
     * @var null|int
     */
    private $operationType;

    /**
     * Set initiator value.
     *
     * @param int $initiator
     *
     * @return self
     */
    public function setInitiator($initiator)
    {
        $this->initiator = $initiator;

        return $this;
    }

    /**
     * Clears the activity initiator value.
     *
     * @return self
     */
    public function clearInitiator()
    {
        $this->initiator = null;

        return $this;
    }

    /**
     * Set resource value.
     *
     * @param int   $initiator
     * @param mixed $resource
     *
     * @return self
     */
    public function setResource($resource)
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * Clears the activity resource value.
     *
     * @return self
     */
    public function clearResource()
    {
        $this->resource = null;

        return $this;
    }

    /**
     * Set resource type value.
     *
     * @param int   $initiator
     * @param mixed $resourceType
     *
     * @return self
     */
    public function setResourceType($resourceType)
    {
        $this->resourceType = $resourceType;

        return $this;
    }

    /**
     * Clears the activity resource type value.
     *
     * @return self
     */
    public function clearResourceType()
    {
        $this->resourceType = null;

        return $this;
    }

    /**
     * Set operation type value.
     *
     * @param int   $initiator
     * @param mixed $operationType
     *
     * @return self
     */
    public function setOperationType($operationType)
    {
        $this->operationType = $operationType;

        return $this;
    }

    /**
     * Clears the activity operation type value.
     *
     * @return self
     */
    public function clearOperationType()
    {
        $this->operationType = null;

        return $this;
    }

    /**
     * Clears all log bindings.
     *
     * @return self
     */
    public function clearBindings()
    {
        $this->clearInitiator();
        $this->clearResource();
        $this->clearResourceType();
        $this->clearOperationType();

        return $this;
    }

    protected function createRecord($level, $message, array $context = array())
    {
        return array_merge(parent::createRecord($level, $message, $context), array(
            'initiator' => array(
                'id'   => $this->initiator,
                'type' => null,
            ),
            'resource'  => array(
                'id'   => $this->resource,
                'type' => $this->resourceType,
            ),
            'operation' => array(
                'id'   => null,
                'type' => $this->operationType,
            ),
        ));
    }
}
