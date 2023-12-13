<?php

namespace App\Services;

use App\Common\Contracts\Entities\CountryCodeInterface;
use App\Entities\ISO\ISO3166;
use App\Entities\Phones\Country;
use App\Entities\Phones\CountryCode as PhoneCountryCode;
use Country_Model;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DomainException;
use League\ISO3166\ISO3166 as ISO3166Metadata;
use League\ISO3166\ISO3166DataProvider;
use OutOfBoundsException;
use Phone_Codes_Model;
use Traversable;
use function arrayGet;
use function iterator_to_array;
use function with;

final class PhoneCodesService
{
    const SORT_AS_IS = 1;

    const SORT_BY_PRIORITY = 2;

    /**
     * The repositiry for the countries.
     *
     * @var Country_Model|Phone_Codes_Model
     */
    private $phoneCodesRepository;

    /**
     * The provider for ISO3166 data.
     *
     * @var ISO3166DataProvider
     */
    private $iso3166Provider;

    /**
     * The collection of country codes.
     *
     * @var Collection
     */
    private $countryCodes;

    /**
     * Creates the instance of PhoneCodeService.
     *
     * @param Country_Model|Phone_Codes_Model $phoneCodesRepository
     */
    public function __construct($phoneCodesRepository, ISO3166DataProvider $iso3166Provider = null)
    {
        $this->countryCodes = new ArrayCollection();
        $this->iso3166Provider = $iso3166Provider;
        $this->phoneCodesRepository = $phoneCodesRepository;
        if ($phoneCodesRepository instanceof Country_Model) {
            \trigger_deprecation('app', 'v2.29.7', 'The model "%s" must be used in favor of deprecated model "%s"', Phone_Codes_Model::class, Country_Model::class);
        }
    }

    /**
     * Finds first matching country code.
     *
     * @param null|int $phoneCodeId
     * @param null|int $phoneCodeName
     * @param null|int $countryId
     * @param mixed    $sort
     *
     * @return ArrayCollection|CountryCodeInterface[]
     */
    public function findAllMatchingCountryCodes($phoneCodeId = null, $phoneCodeName = null, $countryId = null, $sort = self::SORT_AS_IS)
    {
        if (null === $phoneCodeId && null === $phoneCodeName && null === $countryId) {
            return new ArrayCollection();
        }

        if ($sort !== static::SORT_AS_IS && $sort !== static::SORT_BY_PRIORITY) {
            throw new DomainException(sprintf('The argument 4 in the method %s::() contains unknown or invalid value', __METHOD__));
        }

        $countryCodes = $this->getCountryCodes();
        if (0 === $countryCodes->count()) {
            return new ArrayCollection();
        }

        if ($sort === static::SORT_AS_IS) {
            return new ArrayCollection(
                $countryCodes->filter(function (CountryCodeInterface $countryCode) use ($phoneCodeId, $phoneCodeName, $countryId) {
                    return (null !== $phoneCodeId && $countryCode->getId() === (int) $phoneCodeId)
                        || (null !== $phoneCodeName && $countryCode->getName() === (string) $phoneCodeName)
                        || (
                            null !== $countryId
                            && null !== $countryCode->getCountry()
                            && $countryCode->getCountry()->getId() === (int) $countryId
                        );
                })->getValues()
            );
        }

        $idPool = [];
        $namePool = [];
        $countryPool = [];
        /** @var CountryCodeInterface[] $countryCodes */
        foreach ($countryCodes as $countryCode) {
            if (null !== $phoneCodeId && $countryCode->getId() === (int) $phoneCodeId) {
                $idPool[$countryCode->getId()] = $countryCode;

                continue;
            }

            if (null !== $phoneCodeName && $countryCode->getName() === (string) $phoneCodeName) {
                $namePool[$countryCode->getId()] = $countryCode;

                continue;
            }

            if (null !== $countryId && null !== $countryCode->getCountry() && $countryCode->getCountry()->getId() === (int) $countryId) {
                $countryPool[$countryCode->getId()] = $countryCode;

                continue;
            }
        }

        return new ArrayCollection(array_values(array_merge($idPool, $namePool, $countryPool)));
    }

    /**
     * Returns the collection of country codes.
     *
     * @return Collection
     */
    public function getCountryCodes()
    {
        if (null === $this->countryCodes || 0 === $this->countryCodes->count()) {
            $this->countryCodes = new ArrayCollection(
                iterator_to_array(
                    $this->makeCountryCodes(array_filter((array) $this->getListOfPhondeCodes()))
                )
            );
        }

        return $this->countryCodes;
    }

    /**
     * Processes the raw country codes list.
     *
     * @return Traversable
     */
    private function makeCountryCodes(array $codes)
    {
        foreach ($codes as $code) {
            $countryCode = $this->makeCountryCodeFromRaw($code, $this->iso3166Provider);
            if (null !== $countryCode) {
                yield $countryCode;
            }
        }
    }

    /**
     * Makes the country code from raw information.
     *
     * @param null|IsoDataProvider $isoProvider
     *
     * @return PhoneCountryCode
     */
    private function makeCountryCodeFromRaw(array $code, ISO3166DataProvider $isoProvider = null)
    {
        $makeId = function ($id) { return null !== $id ? (int) $id : null; };

        $country = null;
        $countryId = isset($code['id_country']) ? (int) $code['id_country'] : null;
        if (!empty($countryId)) {
            $countryName = isset($code['country_name']) ? (string) $code['country_name'] : null;

            //region Handle ISO3166
            $countryIsoAlpha2 = isset($code['country_iso3166_alpha2']) ? (string) $code['country_iso3166_alpha2'] : null;
            $countryIsoAlpha3 = isset($code['country_iso3166_alpha3']) ? (string) $code['country_iso3166_alpha3'] : null;
            $isoStoredMetadata = [];
            if (is_string($countryName) && null !== $isoProvider) {
                try {
                    $isoStoredMetadata = $isoProvider->name($countryName);
                } catch (OutOfBoundsException $exception) {
                    if (null !== $countryIsoAlpha2) {
                        try {
                            $isoStoredMetadata = $isoProvider->alpha2($countryIsoAlpha2);
                        } catch (OutOfBoundsException $exception) {
                            // Skip and go to the Alpha3 block
                        } catch (DomainException $exception) {
                            // Skip and go to the Alpha3 block
                        }
                    }

                    if (empty($isoStoredMetadata) && null !== $countryIsoAlpha3) {
                        try {
                            $isoStoredMetadata = $isoProvider->alpha3($countryIsoAlpha3);
                        } catch (OutOfBoundsException $exception) {
                            // Skip iso metadata lookup
                        } catch (DomainException $exception) {
                            // Skip iso metadata lookup
                        }
                    }
                }
            }

            $countryIsoAlpha2 = $countryIsoAlpha2 ? $countryIsoAlpha2 : (isset($isoStoredMetadata[ISO3166Metadata::KEY_ALPHA2]) ? $isoStoredMetadata[ISO3166Metadata::KEY_ALPHA2] : $isoStoredMetadata[ISO3166Metadata::KEY_ALPHA2]);
            $countryIsoAlpha3 = $countryIsoAlpha3 ? $countryIsoAlpha3 : (isset($isoStoredMetadata[ISO3166Metadata::KEY_ALPHA3]) ? $isoStoredMetadata[ISO3166Metadata::KEY_ALPHA3] : $isoStoredMetadata[ISO3166Metadata::KEY_ALPHA3]);
            $countryIsoNumeric = isset($isoStoredMetadata[ISO3166Metadata::KEY_NUMERIC]) ? $isoStoredMetadata[ISO3166Metadata::KEY_NUMERIC] : $isoStoredMetadata[ISO3166Metadata::KEY_NUMERIC];
            //endregion Handle ISO3166

            $country = new Country(
                $countryId,
                $countryName,
                null,
                new ISO3166(
                    $countryIsoAlpha2,
                    $countryIsoAlpha3,
                    $countryIsoNumeric
                )
            );
        }

        return new PhoneCountryCode(with(arrayGet($code, 'id_code'), $makeId), arrayGet($code, 'ccode'), $country, array_filter(
            [
                PhoneCountryCode::PATTERN_GENERAL            => $code['phone_pattern_general'] ?? null,
                PhoneCountryCode::PATTERN_INTERNATIONAL_MASK => $code['phone_pattern_international_mask'] ?? null,
            ]
        ));
    }

    /**
     * Gets the list of phone codes from storage.
     */
    private function getListOfPhondeCodes(): array
    {
        if ($this->phoneCodesRepository instanceof Country_Model) {
            return array_filter((array) $this->phoneCodesRepository->get_extended_country_codes_list());
        }

        return $this->phoneCodesRepository->findAllBy(['scopes' => ['extendedList']]);
    }
}
