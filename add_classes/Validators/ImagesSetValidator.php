<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Database\Model;
use App\Common\Validation\Legacy\Standalone\Validator;
use App\Common\Validation\Legacy\ValidatorAdapter;
use App\Filesystem\FilePathGenerator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;
use ParagonIE\Halite\Asymmetric\Crypto;
use ParagonIE\Halite\Asymmetric\SignaturePublicKey;
use Symfony\Component\HttpFoundation\ParameterBag;

final class ImagesSetValidator extends Validator
{
    /**
     * The flag that determines if empty fields allowed.
     *
     * @var bool
     */
    private $allowEmpty;

    /** @var FilesystemProviderInterface $storageProvider */
    private $storageProvider;

    /** @var SignaturePublicKey $publicKey */
    private $publicKey;

    /** @var Model $productPhotosModel */
    private $productPhotosModel;

    public function __construct(
        ValidatorAdapter $validatorAdapter,
        FilesystemProviderInterface $storageProvider,
        SignaturePublicKey $publicKey,
        Model $productPhotosModel,
        ?bool $allowEmpty = false,
        ?array $messages = null,
        ?array $labels = null,
        ?array $fields = null
    ) {
        $this->allowEmpty = $allowEmpty ?? false;
        $this->storageProvider = $storageProvider;
        $this->publicKey = $publicKey;
        $this->productPhotosModel = $productPhotosModel;

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

        return array(
            'mainImage'  => array(
                'field' => $fields->get('mainImage'),
                'label' => $labels->get('mainImage'),
                'rules' => $this->getMainImageRules($messages, $fields),
            ),
            'otherImages'  => array(
                'field' => $fields->get('otherImages'),
                'label' => $labels->get('otherImages'),
                'rules' => $this->getOtherImagesRules($messages),
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function fields(): array
    {
        return [
            'mainImage'         => 'main_image',
            'otherImages'       => 'other_images',
            'mainImageNonce'    => 'images_main_nonce',
            'parent'            => 'parent',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function labels(): array
    {
        return array(
            'mainImage'   => 'Main image',
            'otherImages' => 'Other images',
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function messages(): array
    {
        return array(
            'mainImage.notEmpty'     => 'The main image must not be empty.',
            'mainImage.notFound'     => 'The main image is not found',
            'otherImages.notEmpty'   => 'Please upload at least one image.',
            'otherImages.atLeastOne' => 'Please upload at least one image.',
        );
    }

    /**
     * Determines if empty fields allowed.
     */
    protected function isEmptyAllowed(): bool
    {
        return $this->allowEmpty;
    }

    /**
     * Get the MainImages validation rule.
     */
    protected function getMainImageRules(ParameterBag $messages, ParameterBag $fields): array
    {
        $isEmptyAllowed = $this->isEmptyAllowed();
        $storageProvider = $this->storageProvider;
        $publicKey = $this->publicKey;
        $productPhotosModel = $this->productPhotosModel;

        return [
            function (string $attr, $value, callable $fail) use ($messages, $isEmptyAllowed) {
                if (!$isEmptyAllowed && empty($value)) {
                    $fail(sprintf($messages->get('mainImage.notEmpty'), $attr));
                }
            },
            function (string $attr, $tempImageUrl, callable $fail) use ($messages, $storageProvider) {
                if (empty($tempImageUrl)) {
                    return;
                }

                $tempDisk = $storageProvider->storage('temp.storage');
                $fileName = pathinfo($tempImageUrl, PATHINFO_BASENAME);
                $pathToFile = FilePathGenerator::makePathToUploadedFile($fileName);

                try {
                    if (!$tempDisk->fileExists($pathToFile)) {
                        $fail(sprintf($messages->get('mainImage.notFound'), $attr));
                    }
                } catch (\TypeError $e) {
                    $fail(sprintf($messages->get('mainImage.notFound'), $attr));
                }
            },
            function (string $attr, $pathToMainImage, callable $fail) use ($messages, $fields, $storageProvider, $publicKey, $productPhotosModel) {
                if (!empty($pathToMainImage)) {
                    //region check file nonce
                    if (empty($nonce = (string) $this->getValidationData()->get($fields->get('mainImageNonce')))) {
                        $fail(sprintf($messages->get('mainImage.wrongNonce'), $attr));

                        return;
                    }

                    $fileName = pathinfo($pathToMainImage, PATHINFO_BASENAME);
                    $pathToFile = FilePathGenerator::makePathToUploadedFile($fileName);
                    $tempPrefixer = $storageProvider->prefixer('temp.storage');

                    if (!Crypto::verify($tempPrefixer->prefixPath($pathToFile), $publicKey, $nonce)) {
                        $fail(sprintf($messages->get('mainImage.wrongNonce'), $attr));

                        return;
                    }
                    //endregion check file nonce

                    //region check parent
                    if (empty($parent = (string) $this->getValidationData()->get($fields->get('parent')))) {
                        $fail(sprintf($messages->get('mainImage.emptyParent'), $attr));

                        return;
                    }

                    $possibleParents = [];
                    $newUploadedImages = $this->getValidationData()->get($fields->get('otherImages')) ?: [];
                    foreach ($newUploadedImages as $newImage) {
                        $possibleParents[] = pathinfo($newImage, PATHINFO_BASENAME);
                    }

                    if (!empty($itemId = (int) $this->getValidationData()->get('item'))) {
                        $removedImages = array_flip($this->getValidationData()->get('images_remove') ?: []);

                        $itemPhotos = $productPhotosModel->findAllBy([
                            'scopes' => [
                                'itemId'        => $itemId,
                                'isMainPhoto'   => 0,
                            ],
                        ]);

                        foreach ($itemPhotos as $itemPhoto) {
                            if (!isset($removedImages[$itemPhoto['id']])) {
                                $possibleParents[] = $itemPhoto['photo_name'];
                            }
                        }
                    }

                    if (!in_array($parent, $possibleParents)) {
                        $fail(sprintf($messages->get('mainImage.wrongParent'), $attr));
                        return;
                    }
                }
            }
        ];
    }

    /**
     * Get the OtherImages validation rule.
     */
    protected function getOtherImagesRules(ParameterBag $messages): array
    {
        $isEmptyAllowed = $this->isEmptyAllowed();
        $storageProvider = $this->storageProvider;
        return array(
            function (string $attr, $value, callable $fail) use ($messages, $isEmptyAllowed) {
                if (
                    !$isEmptyAllowed
                    && (
                        empty($value)
                        || (
                            $value instanceof Collection && 0 === $value->count()
                        )
                    )
                ) {
                    $fail(sprintf($messages->get('otherImages.notEmpty'), $attr));
                }
            },
            function (string $attr, $value, callable $fail) use ($messages, $storageProvider) {
                $list = $value instanceof Collection ? $value : new ArrayCollection(is_array($value) ? $value : array());
                if (0 === $list->count()) {
                    return;
                }

                $tempDisk = $storageProvider->storage('temp.storage');

                $validImages = 0;

                try {
                    foreach ($list as $tempImageUrl) {
                        $fileName = pathinfo($tempImageUrl, PATHINFO_BASENAME);
                        $pathToFile = FilePathGenerator::makePathToUploadedFile($fileName);

                        if ($tempDisk->fileExists($pathToFile)) {
                            $validImages++;
                        }
                    }
                } catch (\TypeError $e) {
                    $fail(sprintf($messages->get('otherImages.atLeastOne'), $attr));
                }

                if (0 === $validImages) {
                    $fail(sprintf($messages->get('otherImages.atLeastOne'), $attr));
                }
            },
        );
    }
}
