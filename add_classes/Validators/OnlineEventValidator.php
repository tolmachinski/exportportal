<?php

declare(strict_types=1);

namespace App\Validators;

use TinyMVC_Library_validator;
use Ep_Events_Categories_Model;
use App\Common\Validation\Legacy\Standalone\Validator;
use App\Common\Validation\Legacy\ValidatorAdapter;
use App\Common\Validation\NestedValidationData;
use Symfony\Component\HttpFoundation\ParameterBag;

class OnlineEventValidator extends Validator
{
    protected const MAX_DESCRIPTION_LENGTH = 20000;
    protected const MAX_WHY_ATTEND_LENGTH = 20000;
    protected const MAX_TITLE_LENGTH = 255;
    protected const INCOMING_DATE_FORMAT = 'm/d/Y H:i';

    protected $isActionAddEvent;

    public function __construct(
        ValidatorAdapter $validatorAdapter,
        bool $isActionAddEvent = true,
        ?array $messages = null,
        ?array $labels = null,
        ?array $fields = null
    ) {
        $this->isActionAddEvent = $isActionAddEvent;

        parent::__construct($validatorAdapter, $messages, $labels, $fields);
    }

    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        $fields = $this->getFields();
        $labels = $this->getLabels();
        $messages = $this->getMessages();

        return [
            'dateEnd' => [
                'field' => $fields->get('dateEnd'),
                'label' => $labels->get('dateEnd'),
                'rules' => $this->getDateEndRules($messages),
            ],
            'dateStart' => [
                'field' => $fields->get('dateStart'),
                'label' => $labels->get('dateStart'),
                'rules' => $this->getDateStartRules($messages),
            ],
            'gallery' => [
                'field' => $fields->get('gallery'),
                'label' => $labels->get('gallery'),
                'rules' => $this->getGalleryRules($messages),
            ],
            'description' => [
                'field' => $fields->get('description'),
                'label' => $labels->get('description'),
                'rules' => $this->getDescriptionRules(static::MAX_DESCRIPTION_LENGTH, $messages),
            ],
            'shortDescription' => [
                'field' => $fields->get('shortDescription'),
                'label' => $labels->get('shortDescription'),
                'rules' => $this->getShortDescriptionRules($messages),
            ],
            'whyAttend' => [
                'field' => $fields->get('whyAttend'),
                'label' => $labels->get('whyAttend'),
                'rules' => $this->getWhyAttendRules(static::MAX_WHY_ATTEND_LENGTH, $messages),
            ],
            'category' => [
                'field' => $fields->get('category'),
                'label' => $labels->get('category'),
                'rules' => $this->getCategoryRules($messages),
            ],
            'title' => [
                'field' => $fields->get('title'),
                'label' => $labels->get('title'),
                'rules' => $this->getTitleRules(static::MAX_TITLE_LENGTH, $messages),
            ],
            'tags' => [
                'field' => $fields->get('tags'),
                'label' => $labels->get('tags'),
                'rules' => $this->getTagsRules($messages),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function fields(): array
    {
        return [
            'shortDescription'  => 'short_description',
            'description'       => 'description',
            'whyAttend'         => 'why_attend',
            'dateStart'         => 'date_start',
            'category'          => 'category',
            'gallery'           => 'images_multiple',
            'dateEnd'           => 'date_end',
            'title'             => 'title',
            'tags'              => 'tags',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function labels(): array
    {
        return [
            'shortDescription'  => 'Short description',
            'description'       => 'Description',
            'whyAttend'         => 'Why Attend',
            'dateStart'         => 'Start date',
            'category'          => 'Category',
            'gallery'           => 'Event gallery',
            'dateEnd'           => 'End date',
            'title'             => 'Title',
            'tags'              => 'Tags',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function messages(): array
    {
        return [
            'tags.notAllAreValid'   => translate('validation_online_ep_event_tags_not_valid'),
            'category.notNumber'    => translate('validation_online_ep_event_category_id_not_number', ['{{FIELD_NAME}}' => '%s']),
            'gallery.wrongData'     => translate('validation_online_ep_event_gallery_wrong_data', ['{{FIELD_NAME}}' => '%s']),
            'category.exist'        => translate('validation_online_ep_event_category_not_exist', ['{{FIELD_NAME}}' => '%s']),
        ];
    }

    /**
     * Get the description validation rules.
     */
    protected function getDescriptionRules(int $maxLength, ParameterBag $messages): array
    {
        return [
            'required'                      => $messages->get('description.required') ?? '',
            "html_max_len[{$maxLength}]"    => $messages->get('description.maxLength') ?? '',
        ];
    }

    /**
     * Get the short description validation rules.
     */
    protected function getShortDescriptionRules(ParameterBag $messages): array
    {
        return [
            'required'      => $messages->get('shortDescription.required') ?? '',
            "max_len[255]"  => $messages->get('shortDescription.maxLength') ?? '',
        ];
    }

    /**
     * Get the why attend field validation rules.
     */
    protected function getWhyAttendRules(int $maxLength, ParameterBag $messages): array
    {
        return [
            "html_max_len[{$maxLength}]"  => $messages->get('whyAttend.maxLength') ?? '',
        ];
    }

    /**
     * Get the category field validation rules.
     */
    protected function getCategoryRules(ParameterBag $messages): array
    {
        return [
            'required'  => $messages->get('category.required') ?? '',
            'is_number' => $messages->get('category.notNumber') ?? '',
            function (string $attr, $value, callable $fail) use ($messages) {
                if (empty($value)) {
                    return;
                }

                /** @var Ep_Events_Categories_Model $epEventsCategoriesModel */
                $epEventsCategoriesModel = model(Ep_Events_Categories_Model::class);
                if (!$epEventsCategoriesModel->has((int) $value)) {
                    $fail(sprintf($messages->get('category.exist'), $attr));
                }
            },
        ];
    }

    /**
     * Get the title field validation rules.
     */
    protected function getTitleRules(int $maxLength, ParameterBag $messages): array
    {
        return [
            'required'               => $messages->get('title.required') ?? '',
            "max_len[{$maxLength}]"  => $messages->get('title.maxLength') ?? '',
        ];
    }

    /**
     * Get the dateEnd field validation rules.
     */
    protected function getDateEndRules(ParameterBag $messages): array
    {
        return [
            'required'                                          => $messages->get('dateEnd.required') ?? '',
            'valid_date[' . static::INCOMING_DATE_FORMAT . ']'  => $messages->get('dateEnd.invalidDate') ?? '',
        ];
    }

    /**
     * Get the dateStart field validation rules.
     */
    protected function getDateStartRules(ParameterBag $messages): array
    {
        return [
            'required'                                          => $messages->get('dateStart.required') ?? '',
            'valid_date[' . static::INCOMING_DATE_FORMAT . ']'  => $messages->get('dateStart.invalidDate') ?? '',
        ];
    }

    /**
     * Get the tags field validation rules.
     */
    protected function getTagsRules(ParameterBag $messages): array
    {
        return [
            'required' => $messages->get('tags.required') ?? '',
            function (string $attr, $tags, callable $fail, TinyMVC_Library_validator $validator) use ($messages) {
                foreach ($tags as $tag) {
                    $tag = \cleanInput($tag);
                    if ($validator->valid_tag($tag)) {
                        $validTags[] = $tag;
                    }
                }

                if (count($tags) != count($validTags)){
                    $fail($messages->get('tags.notAllAreValid'));
                }
            },
        ];
    }

    /**
     * Get the gallery field validation rules.
     */
    protected function getGalleryRules(ParameterBag $messages): array
    {
        return [
            function (string $attr, $gallery, callable $fail) use ($messages) {
                if (empty($gallery)) {
                    return;
                }

                if (!$gallery instanceof NestedValidationData) {
                    $fail(sprintf($messages->get('gallery.wrongData'), $attr));
                    return;
                }
            }
        ];
    }
}
