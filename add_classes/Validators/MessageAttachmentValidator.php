<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;
use App\Common\Validation\Legacy\ValidatorAdapter;
use App\Plugins\EPDocs\NotFoundException;
use App\Plugins\EPDocs\Rest\Resources\TemporaryFile as TemporaryFileResource;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\ParameterBag;

final class MessageAttachmentValidator extends Validator
{
    /**
     * The temporary files resource.
     *
     * @var TemporaryFileResource
     */
    private $temporaryFiles;

    /**
     * {@inheritdoc}
     */
    public function __construct(ValidatorAdapter $validator, ?ParameterBag $messages = null, ?ParameterBag $labels = null, TemporaryFileResource $temporaryFiles)
    {
        parent::__construct($validator, $messages, $labels);

        $this->temporaryFiles = $temporaryFiles;
    }

    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        return array(
            array(
                'field' => 'id_theme',
                'label' => 'Theme',
                'rules' => array(
                    function (string $attr, $value, callable $fail): void {
                        if (empty($value)) {
                            $fail('You did not select any theme.');
                        }
                    },
                ),
            ),
            array(
                'field' => 'recipients',
                'label' => 'Recipients',
                'rules' => array(
                    function (string $attr, $value, callable $fail): void {
                        if (empty($this->getValidationData()->get('recipients'))) {
                            $fail('You did not select any recipients. Please choose a recipient first.');
                        }
                    },
                ),
            ),
            array(
                'field' => 'attachments',
                'rules' => array(
                    function (string $attr, ?Collection $attachments, callable $fail): void {
                        if (null === $attachments || 0 === $attachments->count()) {
                            $fail('At least one attachment is required');
                        }
                    },
                    function (string $attr, ?Collection $attachments, callable $fail): void {
                        if (null === $attachments) {
                            return;
                        }

                        foreach ($attachments as $attachment) {
                            if (empty($attachment) || false === base64_decode($attachment, true)) {
                                $fail('At least one of the attachments references has invalid format.');
                            }
                        }
                    },
                    function (string $attr, ?Collection $attachments, callable $fail): void {
                        if (null === $attachments) {
                            return;
                        }

                        try {
                            foreach ($attachments as $attachment) {
                                if (false === ($decoded = base64_decode($attachment, true))) {
                                    continue;
                                }

                                $this->temporaryFiles->getFile($decoded);
                            }
                        } catch (NotFoundException $exception) {
                            $fail('At least one of the attachments does not exist.');
                        } catch (\Exception $exception) {
                            $fail('Failed to find the attachments. Please contact administration.');
                        }
                    },
                ),
            ),
        );
    }
}
