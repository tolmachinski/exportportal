<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;
use App\Common\Validation\Legacy\ValidatorAdapter;
use ExportPortal\Contracts\Filesystem\FilesystemOperator;
use League\Flysystem\StorageAttributes;

final class CompanyNameIndexValidator extends Validator
{
    private $company_id;

    private FilesystemOperator $storage;

    /**
     * Creates the phone validator.
     */
    public function __construct(ValidatorAdapter $validator, FilesystemOperator $storage, int $company_id = null)
    {
        $this->company_id = $company_id;
        $this->storage = $storage;

        parent::__construct($validator);
    }

    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        return [
            [
                'field' => 'index_name',
                'label' => 'Personal link',
                'rules' => [
                    'required'                                          => '',
                    'min_length[5]'                                     => '',
                    'max_length[30]'                                    => '',
                    'company_index_name_valid'                          => '',
                    "company_index_name_not_taken[{$this->company_id}]" => '',
                    function ($attr, $value, $fail) {
                        if (empty($value)) {
                            return;
                        }

                        $pattern = \sprintf('/^%s(.*)?\\.php/iu', \preg_quote($value));
                        if (
                            !empty(
                                \array_filter(
                                    \iterator_to_array($this->storage->listContents('/myapp/controllers')),
                                    fn (StorageAttributes $meta) => 'file' === $meta->type() && (bool) \preg_match($pattern, \basename($meta->path()))
                                )
                            )
                        ) {
                            $fail(\translate('systmess_error_company_name_denied'));
                        }
                    },
                ],
            ],
        ];
    }
}
