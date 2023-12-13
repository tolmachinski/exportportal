<?php

namespace App\Plugins\EPDocs;

class Util
{
    /**
     * Creates the context .
     *
     * @param null|int|string $userId
     */
    public static function createContext($userId, ?string $httpOrigin): array
    {
        return [
            'id'     => $userId,
            'origin' => $httpOrigin,
        ];
    }

    /**
     * Returns class serialization metadata.
     *
     * @param string $className
     *
     * @return \Traversable
     */
    public static function getSerializationMetadata($className)
    {
        if (class_exists($className)) {
            $reflection = new \ReflectionClass($className);
            foreach ($reflection->getProperties() as $propertyName) {
                yield $propertyName->getName();
            }
        }
    }
}
