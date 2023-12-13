<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;
use Doctrine\Common\Collections\Collection;

final class CompanyCategoriesValidator extends Validator
{
    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        return array(
            array(
                'field' => $this->getFields()->get('categories') ?? 'categories',
                'label' => 'Industries/categories',
                'rules' => array(
                    function ($attr, Collection $categories, $fail) {
                        if ($categories->isEmpty()) {
                            $fail(translate('validation_company_category_required'));
                        }
                    },
                    function ($attr, Collection $categories, $fail) {
                        if ($categories->isEmpty()) {
                            return;
                        }

                        $found_categories = model('category')->getCategories(array('columns' => 'category_id', 'cat_list' => $categories->getValues()));
                        if (count($found_categories) !== $categories->count()) {
                            $fail(translate('validation_company_categories_not_found'));
                        }
                    },
                ),
            ),
        );
    }
}
