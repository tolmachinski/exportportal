<?php

namespace App\Common\Exceptions;

use Throwable;

/**
 * The exception thrown when company is not found.
 *
 * @author Anton Zencenco
 */
class CompanyNotFoundException extends NotFoundException
{
    /**
     * The company's ID.
     *
     * @var null|mixed
     */
    private $companyId;

    /**
     * {@inheritdoc}
     */
    public function __construct($companyId = null, string $message = 'The company with provided ID is not found', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->companyId = $companyId;
    }

    /**
     * Get the company's ID.
     *
     * @return null|mixed
     */
    public function getCompanyId(): ?int
    {
        return $this->companyId;
    }

    /**
     * Set the company's ID.
     *
     * @param null|mixed $companyId the company's ID
     *
     * @return $this
     */
    public function setCompanyId(?int $companyId): self
    {
        $this->companyId = $companyId;

        return $this;
    }
}
