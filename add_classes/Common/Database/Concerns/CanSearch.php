<?php

declare(strict_types=1);

namespace App\Common\Database\Concerns;

use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Symfony\Component\String\AbstractString;
use Symfony\Component\String\UnicodeString;

/**
 * Allows for the model to perform searches.
 */
trait CanSearch
{
    /**
     * Creates a set of tokens for search from the provided text.
     *
     * @param AbstractString|string $searchText
     *
     * @return string[]
     */
    protected function tokenizeSearchText($searchText, bool $inBooleanMode = false): array
    {
        if (\preg_match_all('/(?<!\pL|\pN|\pM)(\pL|\pN|\pM)+(?!\pL|\pN|\pM)/u', (string) $searchText, $matching)) {
            $words = $matching[0];
        } else {
            $words = [(string) $searchText];
        }

        $totalWords = \count($words);
        $searchTokens = [];
        foreach ($words as $index => $word) {
            $word = \trim($word);
            $word_length = \mb_strlen($word);
            if ($word_length < 4 || $word_length > 84) {
                continue;
            }

            if ($inBooleanMode) {
                $word = \preg_replace('/([\\~\\*\\(\\)\\>\\<\\-\\+])/', '', $word);
                if ($index > 0) {
                    $word = "<{$word}";
                }
                if ($index === ($totalWords - 1)) {
                    $word = "{$word}*";
                }
            }

            $searchTokens[] = $word;
        }

        return $searchTokens;
    }

    /**
     * Appeds the saerch conditions to the provided query.
     */
    protected function appendSearchConditionsToQuery(QueryBuilder $builder, string $text, array $matchColumns = [], array $searchableColumns = []): void
    {
        $searchText = (new UnicodeString($text))->trim();
        $searchTokens = $this->tokenizeSearchText($searchText, true);
        $useMatchSearch = !empty($searchTokens) && !empty($matchColumns);

        if ($useMatchSearch) {
            $parameter = $builder->createNamedParameter(
                $this->getConnection()->quote(implode(' ', $searchTokens)),
                ParameterType::STRING,
                $this->nameScopeParameter('searchMatchedText')
            );

            $builder->andWhere(
                \sprintf(
                    'MATCH (%s) AGAINST (%s IN BOOLEAN MODE)',
                    \implode(', ', \array_map(fn (string $column) => "{$this->getTable()}.{$column}", $matchColumns)),
                    $parameter
                )
            );

            return;
        }

        $searchableColumns = empty($searchableColumns) ? $matchColumns : $searchableColumns;
        if (empty($searchableColumns)) {
            return;
        }
        $searchText = $searchText->replace('\\', '\\\\')->replace('%', '\\%')->replace('_', '\\_');
        $textParameter = $builder->createNamedParameter((string) $searchText, ParameterType::STRING, $this->nameScopeParameter('searchText'));
        $textTokenParameter = $builder->createNamedParameter(
            (string) $searchText->prepend('%')->append('%'),
            ParameterType::STRING,
            $this->nameScopeParameter('searchTextToken')
        );

        $expressions = $builder->expr();
        $builder->andWhere(
            $expressions->or(
                ...\array_map(fn (string $column) => $expressions->eq("{$this->getTable()}.{$column}", $textParameter), $searchableColumns),
                ...\array_map(fn (string $column) => $expressions->like("{$this->getTable()}.{$column}", $textTokenParameter), $searchableColumns),
            )
        );
    }
}
