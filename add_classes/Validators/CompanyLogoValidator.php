<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Exceptions\NotFoundException;
use App\Common\Validation\ConstraintViolationInterface;
use App\Common\Validation\Legacy\Standalone\Validator;
use App\Common\Validation\Legacy\ValidatorAdapter;
use App\Common\Validation\ValidationException;
use ExportPortal\Contracts\Filesystem\Exception\ReadException;
use ExportPortal\Contracts\Filesystem\FilesystemOperator;
use Intervention\Image\Image;
use League\Flysystem\PathPrefixer;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\ParameterBag;
use TinyMVC_Library_Image_intervention as LegacyImageHandler;

/**
 * @author Anton Zencenco
 */
final class CompanyLogoValidator extends Validator
{
    /**
     * The image handler.
     */
    private LegacyImageHandler $imageHandler;

    /**
     * The filesystem storage instance.
     */
    private FilesystemOperator $storage;

    /**
     * The filesystem prefixer instance.
     */
    private PathPrefixer $prefixer;

    /**
     * The logo image rules.
     */
    private array $logoRules;

    /**
     * The image instance.
     *
     * @var Image[]
     */
    private array $images;

    public function __construct(
        ValidatorAdapter $validatorAdapter,
        LegacyImageHandler $imageHandler,
        FilesystemOperator $storage,
        PathPrefixer $prefixer,
        array $logoRules,
        ?array $messages = null,
        ?array $labels = null,
        ?array $fields = null
    ) {
        $this->logoRules = $logoRules;
        $this->storage = $storage;
        $this->prefixer = $prefixer;
        $this->imageHandler = $imageHandler;

        parent::__construct($validatorAdapter, $messages, $labels, $fields);
    }

    /**
     * {@inheritdoc}
     */
    protected function fields(): array
    {
        return [
            'logo' => 'logo',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function labels(): array
    {
        return [
            'logo' => 'Logo',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function messages(): array
    {
        return [
            'logo.notFound' => translate('validation_images_upload_fail'),
            'logo.invalid'  => translate('validation_invalid_file_provided'),
        ];
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
            [
                'field' => $fields->get('logo'),
                'label' => $labels->get('logo'),
                'rules' => $this->getLogoRules($messages),
            ],
        ];
    }

    /**
     * Get the logo validation rules.
     */
    protected function getLogoRules(ParameterBag $messages): array
    {
        $rules = [
            'not_blank' => '',
            function (string $attr, $value, \Closure $fail) use ($messages) {
                if (null === $value) {
                    return;
                }

                try {
                    $this->resolveImageFromPath($value);
                } catch (NotFoundException $e) {
                    $fail(sprintf($messages->get('logo.notFound', ''), $attr));

                    return;
                } catch (ReadException $e) {
                    $fail(sprintf($messages->get('logo.invalid', ''), $attr));

                    return;
                }
            },
        ];
        foreach ($this->logoRules as $key => $rule) {
            $rules[] = $this->makeImageRule($key, $rule);
        }

        return $rules;
    }

    /**
     * Makes rule for one specific constraint.
     *
     * @param mixed $constraintValue
     */
    private function makeImageRule(string $name, $constraintValue): \Closure
    {
        return function (string $attr, $value, \Closure $fail) use ($name, $constraintValue) {
            if (null === $value) {
                return;
            }

            try {
                $image = $this->resolveImageFromPath($value);
            } catch (ReadException $e) {
                return;
            }

            try {
                $this->imageHandler->assertImageIsValid(
                    $image,
                    [$name => $constraintValue],
                    \trim("logo.{$image->extension}", '.')
                );
            } catch (ValidationException $e) {
                /** @var ConstraintViolationInterface */
                $violation = $e->getValidationErrors()->get(0);
                $fail($violation->getMessage());
            }
        };
    }

    /**
     * Resolves the image instance from provided path.
     *
     * @throws NotFoundException when file is not found
     */
    private function resolveImageFromPath(?string $path): ?Image
    {
        if (!isset($this->images[$path])) {
            $fullPath = $this->prefixer->prefixPath($path);
            if (!$this->storage->fileExists($path)) {
                throw new NotFoundException('File is not found in the provided path');
            }

            $this->images[$path] = $this->imageHandler->makeImageFromFile(new File($fullPath));
        }

        return $this->images[$path];
    }
}
