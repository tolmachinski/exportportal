<?php

declare(strict_types=1);

namespace App\Common\Traits\Elasticsearch;

use RuntimeException;

trait AutocompleteAnalyzerTrait
{
    /**
     * Method for analyze autocomplete suggestions for one or more words
     */
    private function analyzeAutocompleteText(string $text): array
    {
        $result = $this->elasticsearchLibrary->analyze($this->type, [
            'analyzer' => str_word_count($text) < 2 ? 'autocomplete_single_word_analyzer' : 'autocomplete_shigle_analyzer',
            'text'     => $text,
        ]);

        if (isset($result['error'])) {
            throw new RuntimeException($result['error']['reason'], 500);
        }

        return $result['tokens'];
    }
}