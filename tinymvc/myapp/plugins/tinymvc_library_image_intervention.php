<?php

use App\Common\Validation\ConstraintViolation;
use App\Common\Validation\ConstraintViolationList;
use App\Common\Validation\ValidationException;
use ExportPortal\Contracts\Filesystem\Exception\ReadException;
use Symfony\Component\Mime\MimeTypes;
use Intervention\Image\Exception\NotWritableException;
use Intervention\Image\ImageManager;
use Intervention\Image\Image;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [02.12.2021]
 * library refactoring code style
 */
class TinyMVC_Library_Image_intervention
{
	private $use_original_name;

	private $original_image_name;

	private $image_new_base_name;

	private $image_new_full_name;

	private $image_new_extension;

	private $save_with_extension;

	private $destination;

	private $withWatermark;

	private $watermarkConfig;

    private ImageManager $imageManager;

    public function __construct(ContainerInterface $container)
    {
        $this->imageManager = $container->get(ImageManager::class);
    }

	private function code_to_message($code)
	{
		switch ($code) {
			case 1:
			case UPLOAD_ERR_INI_SIZE:
				$message = 'The uploaded file exceeds the ' . ini_get('upload_max_filesize');
				break;
			case 2:
			case UPLOAD_ERR_FORM_SIZE:
				$message = 'The uploaded file exceeds the ' . ini_get('upload_max_filesize');
				break;
			case 3:
			case UPLOAD_ERR_PARTIAL:
				$message = 'The uploaded file was only partially uploaded';
				break;
			case 4:
			case UPLOAD_ERR_NO_FILE:
				$message = 'No file was uploaded';
				break;
			case 6:
			case UPLOAD_ERR_NO_TMP_DIR:
				$message = 'Missing a temporary folder';
				break;
			case 7:
			case UPLOAD_ERR_CANT_WRITE:
				$message = 'Failed to write file to disk';
				break;
			case 8:
			case UPLOAD_ERR_EXTENSION:
				$message = 'File upload stopped by extension';
				break;

			default:
				$message = 'Unknown upload error';
				break;
		}

		return $message;
	}

	public function image_processing(array $files, array $configuration)
	{
		if (empty($files)) {
			return false;
		}

        $imageQuality = $configuration['quality'] ?? 90;
		$this->use_original_name = $configuration['use_original_name'] ?? false;
		$this->save_with_extension = array_key_exists('convert', $configuration) ? $configuration['convert'] : 'jpg';
		$this->destination = empty($configuration['destination']) ? null : rtrim($configuration['destination'], '\/') . DS;

		$images = [];
		if (is_array($files['name'])) {
			foreach ($files as $propr => $vals) {
				foreach ($vals as $key => $val) {
					$images[$key][$propr] = $val;
				}
			}
		} else {
			$images[] = $files;
		}

		$response = [];

		foreach ($images as $key => $image) {
			if ($image['error'] ?? false) {
				$response['errors'][] = $image['name'] . ' : ' . $this->code_to_message($image['error']) . '.';
				continue;
			}

			$this->original_image_name = $image['name'];
			$image_path = pathinfo($this->original_image_name);
			$this->image_new_base_name = $this->use_original_name ? $image_path['filename'] : uniqid();
			$this->image_new_extension = $this->save_with_extension ?? $image_path['extension'];
			$this->image_new_full_name = $this->image_new_base_name . '.' . $this->image_new_extension;

			try {
				$new_image = $this->imageManager->make($image['tmp_name']);
			} catch (\Throwable $th) {
				$response['errors'][] = 'Some errors occurred while uploading ' . $this->original_image_name;
				continue;
			}

			if (null !== $new_image->exif('Orientation')) {
				$new_image->orientate();
			}

			$validation_result = $this->validate_image($new_image, $configuration['rules'] ?? []);

			if (is_array($validation_result)) {
				$response['errors'] = array_merge($validation_result, $response['errors'] ?? []);
				continue;
			}

			if (!empty($configuration['handlers'])) {
				if (isset($configuration['handlers']['watermark'])) {
					$this->watermarkConfig = $configuration['handlers']['watermark'];

                    $this->watermarkConfig['position'] = $this->watermarkConfig['position'] ?? 'center';
                    $this->watermarkConfig['prefix'] = $this->watermarkConfig['prefix'] ?? 'prefix_';
				}

				foreach ($configuration['handlers'] as $handler => $params) {
					$handler_result = $this->$handler($new_image, $params);

					if (isset($handler_result['errors'])) {
						$response['errors'] = array_merge($handler_result['errors'], $response['errors']);
						unset($handler_result['errors']);
					}

					if (!empty($handler_result) && is_array($handler_result)) {
						$response[$key] = array_merge($response[$key] ?? [], $handler_result);
					}
				}
			}

			try {
                if (
                    isset($this->watermarkConfig) &&
                    isset($this->watermarkConfig['apply_on_original']) &&
                    true == $this->watermarkConfig['apply_on_original']
                ) {
                    $this->watermark($new_image, array_merge($this->watermarkConfig, ['quality' => $imageQuality]));
                } else {
                    $new_image->save($this->destination . $this->image_new_full_name, $imageQuality, $this->save_with_extension);
                }
				$response[$key]['new_name'] = $this->image_new_full_name;
				$response[$key]['old_name'] = $this->original_image_name;
				$response[$key]['ok_main'] = 1;
				$response[$key]['image_type'] = $new_image->width() > $new_image->height() ? 'landscape' : 'portrait';
			} catch (NotWritableException $exception) {
				$response['errors'][] = 'Occured errors in resize process: ' . $exception->getMessage();
			}
		}

        //reset watermark image to be created again for next images
        $this->withWatermark = null;

		return $response;
	}


    /**
     * Makes the image from the uploaded file.
     *
     * @throws ReadException   when failed to read the file
     */
    public function makeImageFromFile(File $file): Image
    {
        try {
            $image = $this->imageManager->make($file->getPathname());
        } catch (\Throwable $e) {
            throw new ReadException(sprintf('Failed to read the file "%s".', $file->getPathname()), 0, $e);
        }

        return $image;
    }

    /**
     * Determines if uploaded file with image is valid.
     *
     * @throws ValidationException when validation failed
     */
    public function assertImageIsValid(Image $image, array $rules, ?string $orignalName = null): void
    {
        $this->original_image_name = $orignalName ?? $image->basename;
        $validationResult = $this->validate_image($image, $rules);
        if (!is_array($validationResult)) {
            return;
        }

        $violationList = new ConstraintViolationList();
        foreach ($validationResult as $key => $error) {
            $violationList->add(
                new ConstraintViolation(null, null, null, $error)
            );
        }

        throw new ValidationException('The image failed validation', 0, null, $violationList);
    }

	private function validate_image(Image $image, array $rules = [])
	{
		if (empty($rules)) {
			return true;
		}

		$messages = [
			'exact_ratio'   => $this->original_image_name . ' : The picture dimensions should be [WIDTH]:[HEIGHT].',
			'max_height'    => $this->original_image_name . ' : The maximum picture height has to be [VAL] pixels.',
			'min_height'    => $this->original_image_name . ' : The minimum picture height has to be [VAL] pixels.',
			'height'        => $this->original_image_name . ' : The picture height has to be [VAL] pixels.',
			'max_width'     => $this->original_image_name . ' : The maximum picture width has to be [VAL] pixels.',
			'min_width'     => $this->original_image_name . ' : The minimum picture width has to be [VAL] pixels.',
			'width'         => $this->original_image_name . ' : The picture width has to be [VAL] pixels.',
			'mime-type'     => $this->original_image_name . ' : File has not available mime-type ([VAL]). Allowed mime-type: [ALLOWED_MIME]',
			'format'        => $this->original_image_name . ' : Invalid file format. List of supported formats ([VAL]).',
			'ratio'         => $this->original_image_name . ' : The picture dimensions should not be more than 1:[VAL].',
			'size'          => $this->original_image_name . ' : The maximum file size has to be [VAL] MB.',
			'watermark'     => 'The watermark image does not exist',
		];

		$result = [];

		if (!empty($rules['ratio']) && compareFloatNumbers(max($image->width(), $image->height()) / min($image->width(), $image->height()), floatval($rules['ratio']), '>')) {
			$result[] = str_replace('[VAL]', $rules['ratio'], $messages['ratio']);
		}

		if (!empty($rules['exact_ratio']) && $image->width() * $rules['exact_ratio']['height'] != ceil($image->height() * $rules['exact_ratio']['width'])) {
			$result[] = str_replace(['[WIDTH]', '[HEIGHT]'], [$rules['exact_ratio']['width'], $rules['exact_ratio']['height']], $messages['exact_ratio']);
		}

		if (!empty($rules['size']) && $image->filesize() > $rules['size']) {
			$result[] = str_replace('[VAL]', $rules['size'] / 1048576, $messages['size']);
		}

		if (!empty($rules['max_height']) && $image->height() > $rules['max_height']) {
			$result[] = str_replace('[VAL]', $rules['max_height'], $messages['max_height']);
		}

		if (!empty($rules['min_height']) && $image->height() < $rules['min_height']) {
			$result[] = str_replace('[VAL]', $rules['min_height'], $messages['min_height']);
		}

		if (!empty($rules['max_width']) && $image->width() > $rules['max_width']) {
			$result[] = str_replace('[VAL]', $rules['max_width'], $messages['max_width']);
		}

		if (!empty($rules['min_width']) && $image->width() < $rules['min_width']) {
			$result[] = str_replace('[VAL]', $rules['min_width'], $messages['min_width']);
		}

		if (!empty($rules['watermark']) && !file_exists($rules['watermark'])) {
			$result[] = $messages['watermark'];
		}

		if (!empty($rules['format'])) {
			$allowed_formats = explode(',', $rules['format']);
			// $allowed_mimes = array_filter(array_map(function($format){
			//     return Mime::getMimeFromExtension($format);
			// }, $allowed_formats));

			$allowed_mimes_raw = array_filter(array_map(function ($format) {
				return (new MimeTypes())->getMimeTypes($format);
			}, $allowed_formats));

			$allowed_mimes = array_reduce($allowed_mimes_raw, 'array_merge', []);

			if (!in_array($image->mime(), $allowed_mimes)) {
				$result[] = str_replace(
                    [
                        '[VAL]',
                        '[ALLOWED_MIME]',
                    ],
                    [
                        $image->mime(),
                        implode(', ', $allowed_mimes)
                    ],
                    $messages['mime-type']
                );
			}
		}

        if (!empty($rules['width']) && $image->width() != $rules['width']) {
            $result[] = str_replace('[VAL]', $rules['width'], $messages['width']);
        }

        if (!empty($rules['height']) && $image->height() != $rules['height']) {
            $result[] = str_replace('[VAL]', $rules['height'], $messages['height']);
        }

		return empty($result) ? true : $result;
	}

	private function create_thumbs(Image $image, array $thumbs): array
	{
		$result = [];
		foreach ($thumbs as $key => $thumb) {
			$thumb_name = str_replace('{THUMB_NAME}', $this->image_new_base_name, $thumb['name']);

			if (empty($thumb['w']) && empty($thumb['h'])) {
				$result['thumbs'][$key]['ok_thumb'] = 0;
				$result['errors'][] = $thumb_name . ' don\'t processed';

				continue;
			}

			$image->backup();
			$this->resize($image, ['width' => $thumb['w'], 'height' => $thumb['h'], 'fit' => $thumb['fit'] ?? null]);

			try {
				$image->save($this->destination . $thumb_name . '.' . $this->image_new_extension, 100, $this->save_with_extension);

				$result['thumbs'][$key]['ok_thumb'] = 1;
				$result['thumbs'][$key]['thumb_name'] = $thumb_name;
				$result['thumbs'][$key]['thumb_key'] = $thumb;
			} catch (NotWritableException $exception) {
				$result['thumbs'][$key]['ok_thumb'] = 0;
				$result['errors'][] = $thumb_name . ' ' . $exception->getMessage();
			}

			$image->reset();

			if (isset($thumb['watermark']) && (bool) $thumb['watermark'] && !empty($this->watermarkConfig)) {
				if (empty($this->withWatermark)) {
					$this->watermark($image, $this->watermarkConfig);
				}

				$this->withWatermark->backup();

				$this->resize($this->withWatermark, ['width' => $thumb['w'], 'height' => $thumb['h'], 'fit' => $thumb['fit'] ?? null]);
				$this->withWatermark->save($this->destination . $this->watermarkConfig['prefix'] . $thumb_name . '.' . $this->image_new_extension, 100, $this->save_with_extension);

				$this->withWatermark->reset();
			}
		}

		return $result;
	}

	private function resize(Image &$image, array $params):void
	{
		$maintain_ratio_function = function ($constraint) {
			$constraint->aspectRatio();
		};

		$newImageWidth = 'R' === $params['width'] ? null : (int) $params['width'];
        //prevents image stretching
        if (null !== $newImageWidth && $image->width() < $newImageWidth) {
            return;
        }

		$newImageHeight = $params['height'] === 'R' ? null : (int) $params['height'];
        //prevents image stretching
        if (null !== $newImageHeight && $image->height() < $newImageHeight) {
            return;
        }

		$issetNullDimension = !($newImageWidth && $newImageHeight);

		if (!$issetNullDimension && isset($params['fit']) && 'cover' === $params['fit']) {
			$image_ratio = $image->width() / $image->height();
			$config_ration = $newImageWidth / $newImageHeight;

			list($newImageWidth, $newImageHeight) = compareFloatNumbers($image_ratio, $config_ration, '>') ? [null, $newImageHeight] : [$newImageWidth, null];
		}

		$image->resize($newImageWidth, $newImageHeight, $maintain_ratio_function);
	}

	private function watermark(Image &$image, $params):void
	{
		//backup the image current state
		$image->backup();
		//insert watermark and save
        $watermark = $this->imageManager->make(dirname(\App\Common\PUBLIC_PATH) . '/' . $params['path']);

        // resize watermark image
        if (!empty($params['width'])) {
            $watermarkWidth = (int) ($image->width() * $params['width']);
            //limit max watermark width with 300px
            $watermark->resize(min($watermarkWidth, 300), null, function ($constraint) {$constraint->aspectRatio();});
        }

		$image->insert($watermark, $params['position']);
		$image->save($this->destination . $params['prefix'] . $this->image_new_full_name, $params['quality'] ?? 90, $this->save_with_extension);

		//save image with watermark to be used for thumbs later
		$this->withWatermark = clone $image;

		//reset image for later processing
		$image->reset();
	}
}

/* End of file tinymvc_library_image_intervention.php */
/* Location: /tinymvc/myapp/plugins/tinymvc_library_image_intervention.php */
