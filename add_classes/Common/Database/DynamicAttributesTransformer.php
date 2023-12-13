<?php

declare(strict_types=1);

namespace App\Common\Database;

use TinyMVC_PDO as Handler;

final class DynamicAttributesTransformer implements ValuesTransformerInterface
{
    use Concerns\HasConnection;
    use Concerns\AppendsTimestamps;
    use Concerns\CastsAttributes;
    use Concerns\ClearsNullables;
    use Concerns\ConvertsAttributes;
    use Concerns\GuardsAttributes;
    use Concerns\NormalizesTypes;

    /**
     * Creates instance of the transformer.
     */
    public function __construct(Handler $db, array $guarded, array $casts, array $nullable)
    {
        $this->db = $db;
        $this->casts = $this->normalizeAttribuesCasts($casts);
        $this->guarded = $guarded;
        $this->nullable = $nullable;
    }

    /**
     * {@inheritdoc}
     */
    public function transformToDatabaseValues(array $attributes, bool $forced = false): array
    {
        return $forced
            ? $this->forcePreFillAttributesProcessing($attributes)
            : $this->preFillAttributesProcessing($attributes);
    }

    /**
     * {@inheritdoc}
     */
    public function transformToPhpValues(array $attributes): array
    {
        return $this->castAttributesToNative($attributes);
    }
}
