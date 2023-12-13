<?php

namespace App\Common\Traits\Items;

use App\Common\Traits\ValidatorAwareTrait;

/**
 * @deprecated
 */
trait TagsValidationTrait
{
    use ValidatorAwareTrait;

    /**
     * Validates the item tags.
     *
     * @param array $tags
     * @param array $errors
     *
     * @return bool
     */
    protected function validateItemTags(array $tags, array &$errors = array())
    {
        if (empty($tags)) {
            $errors['tags_empty'] = 'At least one valid tag is required';

            return false;
        }
        if (count($tags) > 10) {
            $errors['tags_amount'] = 'No more than 10 tags are allowed';
        }

        $tags = array_combine(array_map(function ($key) { return "tag_{$key}"; }, range(0, count($tags) - 1)), $tags);
        $rules = $this->makeItemTagsValidationRules($tags);
        if (empty($rules)) {
            return true;
        }

        $validator = $this->getValidator();
        $validator->reset_postdata();
        $validator->clear_array_errors();
        $validator->validate_data = $tags;
        $validator->set_rules($rules);
        if (!$validator->validate()) {
            $errors = array_merge($errors, $validator->get_array_errors());

            return false;
        }

        return empty($errors) ? true : false;
    }

    /**
     * Makes validation rules for tags.
     *
     * @param array $tags
     *
     * @return array
     */
    private function makeItemTagsValidationRules(array $tags)
    {
        $index = 1;
        $rules = array();
        foreach ($tags as $key => $tag) {
            $rules[] = array(
                'field' => $key,
                'label' => sprintf('Tag #%s', $index),
                'rules' => array(
                    'min_len[3]'  => sprintf('The tag #%s cannot contain less than %d characters', $index, 3),
                    'max_len[30]' => sprintf('The tag #%s cannot contain more than than %d characters', $index, 30),
                    function ($attr, $value, $fail) use ($index) {
                        if (!empty($value) && !model('elasticsearch_badwords')->is_clean($value)) {
                            $fail(sprintf('The tag #%s contains unacceptable content.', $index));
                        }
                    },
                    function ($attr, $value, $fail) use ($index) {
                        if (!empty($value) && mb_strlen($value) !== mb_strlen(cleanInput($value))) {
                            $fail(sprintf('The tag #%s contains unacceptable content.', $index));
                        }
                    },
                ),
            );

            ++$index;
        }

        return $rules;
    }
}
