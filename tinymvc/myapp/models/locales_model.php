<?php

declare(strict_types=1);

use App\Casts\Locale\LocaleUrlTypeCast;
use App\Common\Database\Model;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Locales model.
 */
final class Locales_Model extends Model
{
    /**
     * The name of the "created at" column.
     */
    protected const CREATED_AT = 'lang_created';

    /**
     * The name of the "updated at" column.
     */
    protected const UPDATED_AT = 'lang_updated';

    /**
     * Indicates if model uses timestamps.
     */
    protected bool $timestamps = true;

    /**
     * {@inheritdoc}
     */
    protected string $table = 'translations_languages';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'LOCALES';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id_lang';

    /**
     * The attributes that aren't mass assignable.
     */
    protected array $guarded = [
        'id',
        self::CREATED_AT,
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id_lang'           => Types::INTEGER,
        'lang_url_type'     => LocaleUrlTypeCast::class,
        'lang_url_translit' => Types::BOOLEAN,
        'lang_default'      => Types::BOOLEAN,
        'lang_active'       => Types::BOOLEAN,
        'lang_published'    => Types::BOOLEAN,
        'lang_weight'       => Types::INTEGER,
        'lang_created'      => Types::DATETIME_IMMUTABLE,
        'lang_updated'      => Types::DATETIME_IMMUTABLE,
    ];

    /**
     * Scope query for locales.
     */
    protected function scopeIds(QueryBuilder $builder, ?array $localesIds): void
    {
        if (empty($localesIds)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->in('id_lang', array_map(
                fn (int $i, $id) => $builder->createNamedParameter((int) $id, ParameterType::INTEGER, $this->nameScopeParameter("locale_id_{$i}", true)),
                array_keys($localesIds),
                $localesIds
            ))
        );
    }

    /**
     * Scope by language iso2
     *
     * @param QueryBuilder $builder
     * @param string $langIso2
     *
     * @return void
     */
    protected function scopeIso2(QueryBuilder $builder, string $langIso2): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->table}.`lang_iso2`",
                $builder->createNamedParameter($langIso2, ParameterType::STRING, $this->nameScopeParameter('langIso2'))
            )
        );
    }

    /**
     * Scope query for excluding locales.
     */
    protected function scopeExclude(QueryBuilder $builder, array $excludedLocales): void
    {
        if (empty($excludedLocales)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->notIn(
                'lang_iso2',
                array_map(
                    fn (int $index, string $locale) => $builder->createNamedParameter($locale, ParameterType::STRING, $this->nameScopeParameter("not_locale_{$index}", true)),
                    array_keys($excludedLocales),
                    $excludedLocales
                )
            )
        );
    }
}

// End of file locales_model.php
// Location: /tinymvc/myapp/models/locales_model.php
