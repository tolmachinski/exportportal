<?php

namespace App\Plugins\EPDocs\Rest;

use App\Plugins\EPDocs\Util;

class RestObject
{
    /**
     * Casts the object to string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * Fills object from array.
     *
     * @return self
     */
    public static function fromArray(array $data)
    {
        $object = new static();
        foreach (Util::getSerializationMetadata(static::class) as $propertyName) {
            $key = $object->denormalizePropertyName($propertyName);
            $mutator = 'set' . ucfirst($propertyName);
            if (
                (isset($data[$propertyName]) || isset($data[$key]))
                && method_exists($object, $mutator)) {
                $object->{$mutator}($data[$key] ?? $data[$propertyName] ?? null);
            }
        }

        return $object;
    }

    /**
     * Serializes the object to array.
     *
     * @return array
     */
    public function toArray()
    {
        $export = [];
        foreach (Util::getSerializationMetadata(static::class) as $propertyName) {
            $accessor = 'get' . ucfirst($propertyName);
            if (method_exists($this, $accessor)) {
                $export[$this->denormalizePropertyName($propertyName)] = $this->{$accessor}();
            }
        }

        return $export;
    }

    /**
     * Serializes the object to JSON.
     *
     * @param int $options
     *
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Serializes the object to a value that can be serialized natively by json_encode().
     *
     * @return string
     */
    public function jsonSerialize()
    {
        return $this->toJson();
    }

    /**
     * Returns denormalized property name.
     *
     * @param string $name
     *
     * @return string
     */
    private function denormalizePropertyName($name)
    {
        if (!ctype_lower($name)) {
            $name = preg_replace('/\s+/u', '', ucwords($name));
            $name = mb_strtolower(preg_replace('/(.)(?=[A-Z])/u', '$1_', $name), 'UTF-8');
        }

        return $name;
    }
}
