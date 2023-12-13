<?php

namespace App\Common\Validation\Constraints;

use App\Common\Validation\ConstraintViolation;
use App\Common\Validation\ConstraintViolationList;
use App\Common\Validation\ValidationDataInterface;
use App\Common\Validation\ValidationException;
use UnexpectedValueException;

class Ip extends AbstractConstraint
{
    const TYPE = 'ip';

    const V4 = '4';
    const V6 = '6';
    const ALL = 'all';

    // adds FILTER_FLAG_NO_PRIV_RANGE flag (skip private ranges)
    const V4_NO_PRIV = '4_no_priv';
    const V6_NO_PRIV = '6_no_priv';
    const ALL_NO_PRIV = 'all_no_priv';

    // adds FILTER_FLAG_NO_RES_RANGE flag (skip reserved ranges)
    const V4_NO_RES = '4_no_res';
    const V6_NO_RES = '6_no_res';
    const ALL_NO_RES = 'all_no_res';

    // adds FILTER_FLAG_NO_PRIV_RANGE and FILTER_FLAG_NO_RES_RANGE flags (skip both)
    const V4_ONLY_PUBLIC = '4_public';
    const V6_ONLY_PUBLIC = '6_public';
    const ALL_ONLY_PUBLIC = 'all_public';

    const IP_ALIAS = 'ip';
    const VERSION_ALIAS = 'version';

    protected static $versions = array(
        self::V4,
        self::V6,
        self::ALL,
        self::V4_NO_PRIV,
        self::V6_NO_PRIV,
        self::ALL_NO_PRIV,
        self::V4_NO_RES,
        self::V6_NO_RES,
        self::ALL_NO_RES,
        self::V4_ONLY_PUBLIC,
        self::V6_ONLY_PUBLIC,
        self::ALL_ONLY_PUBLIC,
    );

    /**
     * The allowed version of the IP.
     *
     * @var null|string
     */
    private $version;

    public function __construct(array $options = array())
    {
        parent::__construct($options);

        if (isset($options['version'])) {
            $this->version = $options['version'];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function assert(ValidationDataInterface $data)
    {
        $value = $data->get('ip');
        if (null === $value || '' === $value) {
            return;
        }
        if (!is_scalar($value) && !(\is_object($value) && method_exists($value, '__toString'))) {
            throw new UnexpectedValueException($value, 'string');
        }

        $value = (string) $value;
        switch ($this->version) {
            case static::V4:
                $flag = FILTER_FLAG_IPV4;

                break;
            case static::V6:
                $flag = FILTER_FLAG_IPV6;

                break;
            case static::V4_NO_PRIV:
                $flag = FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE;

                break;
            case static::V6_NO_PRIV:
                $flag = FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE;

                break;
            case static::ALL_NO_PRIV:
                $flag = FILTER_FLAG_NO_PRIV_RANGE;

                break;
            case static::V4_NO_RES:
                $flag = FILTER_FLAG_IPV4 | FILTER_FLAG_NO_RES_RANGE;

                break;
            case static::V6_NO_RES:
                $flag = FILTER_FLAG_IPV6 | FILTER_FLAG_NO_RES_RANGE;

                break;
            case static::ALL_NO_RES:
                $flag = FILTER_FLAG_NO_RES_RANGE;

                break;
            case static::V4_ONLY_PUBLIC:
                $flag = FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;

                break;
            case static::V6_ONLY_PUBLIC:
                $flag = FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;

                break;
            case static::ALL_ONLY_PUBLIC:
                $flag = FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;

                break;
            default:
                $flag = null;

                break;
        }

        if (false !== strpos($value, '/')) {
            $violations = $this->validateIpSubnet($value, $flag);
        } else {
            $violations = $this->validateIpAddress($value, $flag);
        }

        if ($violations->count() > 0) {
            $exception = new ValidationException('The validation failed with errors.');
            $exception->setValidationErrors($violations);

            throw $exception;
        }
    }

    /**
     * Validates IP adrress.
     *
     * @param string $ip
     * @param string $flag
     */
    private function validateIpAddress($ip, $flag)
    {
        $violations = new ConstraintViolationList();
        if (!filter_var($ip, FILTER_VALIDATE_IP, $flag)) {
            $violations->add(new ConstraintViolation($ip, $ip, $this, 'The provided IP address is invalid.'));
        }

        return $violations;
    }

    /**
     * Validate IP subnet.
     *
     * @param string $subnet
     * @param mixed  $flag
     */
    private function validateIpSubnet($subnet, $flag)
    {
        $isIpV6 = substr_count($subnet, ':') > 1;
        $violations = new ConstraintViolationList();
        list($address, $netmask) = explode('/', $subnet, 2);
        if (!$isIpV6) {
            if ('0' !== $netmask && $netmask < 0 || $netmask > 32) {
                $violations->add(new ConstraintViolation(
                    $netmask,
                    $netmask,
                    $this,
                    'The netmask in the IP address is out of range.'
                ));
            }
        } else {
            if ('0' !== $netmask && $netmask < 1 || $netmask > 128) {
                $violations->add(new ConstraintViolation(
                    $netmask,
                    $netmask,
                    $this,
                    'The netmask in the IP address is out of range.'
                ));
            }
        }
        $violations->merge($this->validateIpAddress($address, $flag));

        return $violations;
    }
}
