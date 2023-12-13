<?php

declare(strict_types=1);

namespace App\Common\Transformers;

use App\Filesystem\CompanyLogoFilePathGenerator;
use ExportPortal\Contracts\Filesystem\FilesystemOperator;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;
use League\Fractal\TransformerAbstract;

class CompanyPickOfTheMonthForBaiduTransformer extends TransformerAbstract
{
    /**
     * The base URL.
     */
    private string $baseUrl;

    /**
     * The file storage.
     */
    private FilesystemOperator $storage;

    /**
     * @param string                      $baseUrl           the base URL
     * @param FilesystemProviderInterface $filesystePorvider The file storage provider
     */
    public function __construct(FilesystemProviderInterface $filesystePorvider, string $baseUrl)
    {
        $this->baseUrl = $baseUrl;
        $this->storage = $filesystePorvider->storage('public.storage');
    }

    public function transform(array $company)
    {
        return [
            'id_company'        => $company['id_company'],
            'title'             => $company['name_company'],
            'user_group_name'   => $company['user_group_name'],
            'country'           => $company['country'],
            'registration_date' => getDateFormat($company['registration_date'], 'Y-m-d H:i:s', 'M Y'),
            'country_flag'      => \sprintf('%s/%s', rtrim($this->baseUrl, '/'), \ltrim(getCountryFlag($company['country']), '/')),
            'link'              => getCompanyURL($company),
            'photo'             => $this->storage->url(CompanyLogoFilePathGenerator::logoPath(
                (int) $company['id_company'],
                !empty($company['logo_company']) ? $company['logo_company'] : 'no-image.jpg'
            )),
        ];
    }
}
