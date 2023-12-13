<?php

use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;

/**
 * Get the name of the temporarily created image thumb.
 *
 * @param string $module    - the module name (as in config) ex: 'company_branches.main'
 * @param int    $thumbSize - the size of the thumb to return
 * @param string $fileName  - the file name of the image
 *
 * @return string the name of the image thumb
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/b.-Images#gettempimgthumbmodule-thumb_size-file_name
 */
function getTempImgThumb($module, $thumbSize, $fileName)
{
    $config = config("img.{$module}");
    $thumbSize = (string) $thumbSize;

    return str_replace(['{THUMB_NAME}'], [$fileName], $config['thumbs'][$thumbSize]['name']);
}

/**
 * Get the path to a temporary image.
 *
 * @param string $module        - the module name (as in config) ex: 'company_branches.main'
 * @param array  $replaceParams - parameters to replace
 *
 * @return string the path to the temporary image
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/b.-Images#gettempimgpathmodule-replace_params-array
 */
function getTempImgPath($module, $replaceParams = [])
{
    $pathTemplate = config("img.{$module}.temp_folder_path");

    return str_replace(array_keys($replaceParams), array_values($replaceParams), $pathTemplate);
}

/**
 * Get the path to the image.
 *
 * @param string $module        - the module name (as in config) ex: 'company_branches.main'
 * @param array  $replaceParams
 *
 * @return string the path to the image
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/b.-Images#getimgpathmodule-replace_params-array
 */
function getImgPath($module, $replaceParams = [])
{
    $pathTemplate = config("img.{$module}.folder_path");

    return str_replace(array_keys($replaceParams), array_values($replaceParams), $pathTemplate);
}

/**
 * Get link to no-photo image based on group.
 *
 * @param int   $noImageGroup - id of the user group
 * @param array $imageSize    - optionally send the image size as an array with 'w' and 'h'
 *
 * @return string link to no photo image
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/b.-Images#getnophotono_image_group-null-image_size-null-1
 */
function getNoPhoto($noImageGroup = null, $imageSize = null)
{
    $userGroups = [
        0  => 'guest',
        1  => 'buyer',
        2  => 'seller',
        3  => 'seller',
        5  => 'manufacturer',
        6  => 'manufacturer',
        13 => 'order-manager',
        14 => 'admin',
        15 => 'user-manager',
        16 => 'super-admin',
        17 => 'support',
        18 => 'content-manager',
        19 => 'billing-manager',
        25 => 'company-staff-user',
        31 => 'shipper',
        32 => 'shipper',
    ];

    if ('dynamic' === $noImageGroup && !empty($imageSize)) {
        return getNoImage($imageSize['w'], $imageSize['h'], 'half');
    }
    $group = isset($userGroups[$noImageGroup]) ? $userGroups[$noImageGroup] : 'other';
    $template = 'public/img/no_image/group/noimage-{GROUP}.svg';

    return str_replace(['{GROUP}'], [$group], $template);
}

/**
 * Get the relative link of the image based on the module and thumb size.
 *
 * @param string $module        - the module name (as in config) ex: 'company_branches.main'
 * @param int    $thumbSize     - the size of the thumb to return
 * @param array  $replaceParams - replacements
 *
 * @return string - link to the image src
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/b.-Images#getimgsrcmodule-thumb_size-replace_params-array
 */
function getImgSrc($module, $thumbSize, $replaceParams = [])
{
    $config = config("img.{$module}");
    $thumbSize = (string) $thumbSize;
    $replaceDynamic['{FOLDER_PATH}'] = $config['folder_path'];

    if ('original' != $thumbSize) {
        $replaceParams['{THUMB_NAME}'] = $replaceParams['{FILE_NAME}'];
        $replaceParams['{FILE_NAME}'] = $config['thumbs'][$thumbSize]['name'];
    }

    $replaceParams = array_merge(
        $replaceDynamic,
        $replaceParams
    );

    $pathTemplate = $config['file_path'];

    return str_replace(array_keys($replaceParams), array_values($replaceParams), $pathTemplate);
}

/**
 * Get the full link for displaying an image.
 *
 * @param array  $replaceParams - array with the parameters to replace the template. Example below
 * @param string $type          - module name. By default 'users.main'
 * @param array  $params        - array with parameters like: no_image_group, thumb_size, image_size
 *
 * @return string - the link to the image or no-photo image if file doesn't exist
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/b.-Images#getdisplayimagelinkreplace_params-type-usersmain-params-array
 */
function getDisplayImageLink($replaceParams, $type = 'users.main', $params = [])
{
    $noImageGroup = 'other';
    $thumbSize = 'original';

    extract(arrayCamelizeAssocKeys($params));

    $link = getImgSrc("{$type}", $thumbSize, $replaceParams);
    $path_parts = pathinfo($link);

    if (empty($path_parts['extension']) || !file_exists($link)) {
        if (!isset($imageSize)) {
            $link = getNoPhoto($noImageGroup);
        } else {
            $link = getNoPhoto($noImageGroup, $imageSize);
        }
    }

    return __SITE_URL . $link . '?' . filemtime($link);
}

if (!function_exists('getDisplayImagePath')) {
    /**
     * Function to get the path for the displayed image.
     *
     * @param array  $replacements - array with the parameters to replace the template
     * @param string $type         - module name. By default 'users.main'
     * @param array  $params       - array with parameters like: no_image_group, thumb_size, image_size
     *
     * Ex:
     * getDisplayImagePath(
     *    ['{ID}' => $contactId, '{FILE_NAME}' => $contact['user_photo'] ?? null],
     *    'users.main',
     *    ['thumb_size' => 0, 'no_image_group' => $contact['user_group']]
     * );
     *
     * @return string path to the image
     *
     * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/b.-Images#getdisplayimagepathreplace_params-type-usersmain-params-array
     */
    function getDisplayImagePath(array $replacements, string $type = 'users.main', array $params = []): string
    {
        $noImage = $params['no_image_group'] ?? null;
        $thumbSize = $params['thumb_size'] ?? 'original';
        $basePath = dirname(\App\Common\PUBLIC_PATH) . '/';
        $imagePath = $basePath . getImgSrc("{$type}", $thumbSize, $replacements);
        $imageExtension = pathinfo($imagePath, PATHINFO_EXTENSION) ?: null;
        if (empty($imageExtension) || !file_exists($imagePath)) {
            $imagePath = $basePath . getNoPhoto($noImage);
        }

        return $imagePath;
    }
}

if (!function_exists('getImagePath')) {
    /**
     * Function to get the full path for the image without the default No Photo image if doesn't exist.
     *
     * @param array  $replacements - array with the parameters to replace the template
     * @param string $type         - module name. By default 'users.main'
     * @param int    $thumbSize    - the size of the thumb if you need it
     *
     * Ex:
     * getDisplayImagePath(
     *    ['{ID}' => $contactId, '{FILE_NAME}' => $contact['user_photo'] ?? null],
     *    'users.main',
     *    0
     * );
     *
     * @return string full path to the image
     *
     * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/b.-Images#getImagePath
     */
    function getImagePath(array $replacements, string $type = 'users.main', int $thumbSize = null): string
    {
        $basePath = dirname(\App\Common\PUBLIC_PATH) . '/';

        return $basePath . getImgSrc("{$type}", $thumbSize ?? 'original', $replacements);
    }
}

if (!function_exists('getWatermarkImagePath')) {
    /**
     * Function to get the path for the watermark image without the default No Photo image if doesn't exist.
     *
     * @param array  $replacements - array with the parameters to replace the template
     * @param string $type         - module name. By default 'users.main'
     * @param int    $thumbSize    - the size of the thumb if you need it
     *
     * Ex:
     * getDisplayImagePath(
     *    ['{ID}' => $contactId, '{FILE_NAME}' => $contact['user_photo'] ?? null],
     *    'users.main',
     *    0
     * );
     *
     * @return string path to the image
     *
     * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/b.-Images#getWatermarkImagePath
     */
    function getWatermarkImagePath(array $replacements, string $type = 'users.main', $thumbSize = null): string
    {
        $basePath = dirname(\App\Common\PUBLIC_PATH) . '/';
        $config = config("img.{$type}");
        $watermarkConfig = config("img.{$type}.watermark");

        if (empty($watermarkConfig)) {
            return '';
        }

        $replaceDynamic['{FOLDER_PATH}'] = $config['folder_path'];

        if (isset($thumbSize)) {
            $replacements['{THUMB_NAME}'] = $replacements['{FILE_NAME}'];
            $replacements['{FILE_NAME}'] = $watermarkConfig['prefix'] . $config['thumbs'][(string) $thumbSize]['name'];
        } else {
            $replacements['{FILE_NAME}'] = $watermarkConfig['prefix'] . $replacements['{FILE_NAME}'];
        }

        $replace_params = array_merge(
            $replaceDynamic,
            $replacements
        );

        $pathTemplate = $config['file_path'];
        $path = str_replace(array_keys($replace_params), array_values($replace_params), $pathTemplate);

        return $basePath . $path;
    }
}

if (!function_exists('getUserAvatar')) {
    /**
     * Get the displayable link to the user avatar.
     *
     * getUserAvatar($company['id'], $company['logo'], $user["user_group"], 0);
     *
     * @param int      $userId       - id of the user to replace
     * @param string   $userPhoto    - the name of the user avatar image
     * @param null|int $noImageGroup - if not found that the name of no photo group
     * @param null|int $thumbSize    - the thumb size if needed
     *
     * @return string the link to the avatar image
     *
     * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/b.-Images#getuseravataruser_id-user_photo-no_image_group-null-thumb_size-null
     */
    function getUserAvatar($userId, $userPhoto, $noImageGroup = null, $thumbSize = null)
    {
        $params = [];

        if (!is_null($noImageGroup)) {
            $params['no_image_group'] = $noImageGroup;
        }

        if (!is_null($thumbSize)) {
            $params['thumb_size'] = $thumbSize;
        }

        return getDisplayImageLink(['{ID}' => $userId, '{FILE_NAME}' => $userPhoto], 'users.main', $params);
    }
}

if (!function_exists('getShipperLogo')) {
    /**
     * Get the image of the shipper.
     *
     * @param int    $shipperId - id of the shipper
     * @param string $logo      - name of the file
     * @param int    $prefix    - thumb size
     *
     * Ex:
     * getShipperLogo($shipper['id'], $shipper['logo'], 0);
     *
     * @return string link to shipper logo
     *
     * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/b.-Images#getcompanylogocompany_id-logo_name-prefix-null
     */
    function getShipperLogo($shipperId, $logo, $prefix = null)
    {
        $params = [];

        if (!is_null($prefix)) {
            $params['thumb_size'] = $prefix;
        }

        return getDisplayImageLink(['{ID}' => $shipperId, '{FILE_NAME}' => $logo], 'shippers.main', $params);
    }
}

if (!function_exists('getCompanyLogo')) {
    /**
     * Get the image of the company.
     *
     * @param int    $company_id - id of the company
     * @param string $logo_name  - name of the file
     * @param int    $prefix     - thumb size
     *
     * Ex:
     * getCompanyLogo($company['id'], $company['logo'], 0);
     *
     * @return string link to company logo
     *
     * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/b.-Images#getcompanylogocompany_id-logo_name-prefix-null
     */
    function getCompanyLogo($company_id, $logo_name, $prefix = null)
    {
        $params = [];

        if (!is_null($prefix)) {
            $params['thumb_size'] = $prefix;
        }

        return getDisplayImageLink(['{ID}' => $company_id, '{FILE_NAME}' => $logo_name], 'companies.main', $params);
    }
}

if (!function_exists('getDownloadableMaterialsCoverPath')) {
    /**
     * Make and return URL to downloadable material cover.
     *
     * @return string
     */
    function getDownloadableMaterialsCoverPath(int $idMaterial, string $coverName)
    {
        return 'public/storage/downloadable_materials/' . $idMaterial . '/' . $coverName;
    }
}

if (!function_exists('getLazyImage')) {
    /**
     * Create and get the lazy image.
     *
     * @param int|string $width  - by default 10
     * @param int|string $height - by default 10
     *
     * @return string the link to the svg
     *
     * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/b.-Images#getlazyimageint-width-10-int-height-10
     */
    function getLazyImage($width = 10, $height = 10, bool $localPath = false)
    {
        $imageName = sprintf('image_%s_%s.svg', str_replace('%', 'pr', $width), str_replace('%', 'pr', $height));
        $directory = \App\Common\VAR_PATH . '/app/public/lazy';
        $relUrl = "/public/storage/lazy/{$imageName}";

        create_dir($directory, 0775);
        if (!file_exists($path = "{$directory}/{$imageName}")) {
            file_put_contents(
                $path,
                <<<CONTENT
                <svg width="{$width}" height="{$height}" version="1.1" viewBox="0 0 {$width} {$height}" xmlns="http://www.w3.org/2000/svg"><rect width="{$width}" height="{$height}" fill="#DDDDDD" stroke-width=".8612"/></svg>
                CONTENT
            );
        }

        if ($localPath) {
            return $relUrl;
        }

        return __IMG_URL . ltrim($relUrl, '/');
    }
}

if (!function_exists('getNoImage')) {
    /**
     * Function that creates the link for the no photo image used in getNoPhoto.
     *
     * @param int    $width  - 10 by default
     * @param int    $height - 10 by default
     * @param string $link   - 'full' by default, meaning the absolute link, send other string to get the relative url
     *
     * @return string the link for the created image
     *
     * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/b.-Images#getnoimageint-width-10-int-height-10-link-full
     */
    function getNoImage(int $width = 10, int $height = 10, $link = 'full')
    {
        /** @var FilesystemProviderInterface */
        $provider = container()->get(FilesystemProviderInterface::class);
        $storage = $provider->storage('public.storage');
        $pathName = "/noimages/image_{$width}_{$height}.svg";
        if (!$storage->fileExists($pathName)) {
            $storage->write(
                $pathName,
                '<svg width="' . $width . '" height="' . $height . '" style="background-color:#DDDDDD" version="1.1" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><g transform="translate(0 -161.53)"><path fill="#FFFFFF" d="m353.55 579.29c-4.3739-0.68804-5.1274-0.96487-5.4128-1.9884-0.38793-1.3914-0.41478-7.0562-0.034562-7.2912 0.17473-0.10799 2.0981 0.41284 4.2741 1.1574 8.9749 3.0709 15.24 2.6179 19.25-1.392 0.77632-0.77632 1.6373-2.1257 1.9132-2.9987 0.56859-1.7988 1.5126-10.036 1.1874-10.362-0.11707-0.11707-1.3255 0.50627-2.6853 1.3852-6.2563 4.0436-12.801 4.629-18.612 1.6646-3.8565-1.9675-7.335-6.4892-8.7076-11.319-0.76946-2.7076-1.1376-13.626-0.56476-16.751 1.0987-5.9946 6.2913-13.065 11.39-15.508 4.0409-1.9366 12.008-1.8286 16.652 0.22581 1.6774 0.742 1.9071 0.75844 2.7128 0.19412 0.61769-0.43264 1.8321-0.61653 4.0718-0.61657l3.1916-6.13e-5 -0.13876 25.533-0.13876 25.533-1.8372 3.1933c-4.3139 7.4981-8.9321 9.7883-19.554 9.697-2.7334-0.023488-5.8644-0.18342-6.9578-0.35542zm14.768-26.669c1.4991-0.46225 3.5779-1.2716 4.6196-1.7986l1.894-0.95814-0.10482-13.012-0.10482-13.012-2.1541-1.0496c-2.8602-1.3936-7.0667-2.0348-10.263-1.5643-7.3443 1.081-11.368 8.8297-10.388 20.005 0.51419 5.8645 2.674 9.9296 6.1033 11.488 2.2584 1.026 6.8931 0.98187 10.398-0.098958zm-218.76 10.433c-4.4891-1.1743-10.078-5.7995-12.426-10.283-1.8502-3.5333-2.5268-7.2345-2.5268-13.822 0-9.0937 1.4519-13.746 5.7104-18.298 3.9026-4.1719 8.6782-6.1534 14.83-6.1534 6.3319 0 10.833 1.7965 14.405 5.7492 6.3177 6.9918 8.0899 21.268 3.8398 30.932-2.6918 6.1207-6.582 9.8491-11.767 11.277-2.4466 0.67399-10.289 1.0626-12.066 0.59782zm10.699-7.5335c2.173-0.96121 5.0872-4.4852 6.2361-7.541 0.66728-1.7748 0.78948-3.0753 0.78452-8.3494-0.00875-9.3041-1.1614-13.119-4.8668-16.106-4.8811-3.9357-11.439-3.3753-15.882 1.3572-2.2732 2.4213-3.2504 4.8948-3.8923 9.8522-0.83679 6.4627 0.26194 12.719 2.9798 16.968 2.33 3.6424 5.2755 5.048 10.005 4.7744 1.6418-0.094992 3.6068-0.49979 4.6359-0.95502zm148.8 7.461c-1.6778-0.4207-3.7032-1.5368-5.6017-3.0869-4.0379-3.2968-6.0769-10.965-4.3082-16.203 2.5061-7.4215 8.2147-10.269 24.287-12.117l4.0753-0.46836-0.00223-1.4462c-0.00375-2.4425-0.81275-4.5415-2.3385-6.0673-3.2263-3.2263-9.0386-3.5053-19.017-0.9127-2.0353 0.52881-3.7877 0.96147-3.8943 0.96147-0.2889 0-0.15716-5.6123 0.1583-6.7436 0.2457-0.88121 0.64733-1.0474 4.0357-1.6697 4.3599-0.80076 13.701-0.96177 17.291-0.29805 4.0523 0.74911 8.2022 3.3745 9.6211 6.0867 0.35484 0.67823 0.8887 2.2172 1.1864 3.4199 0.68527 2.7688 1.3761 19.59 1.2246 29.819l-0.11482 7.753-3.0146 0.11492c-1.658 0.063209-3.4919-0.066552-4.0753-0.28835-0.93348-0.35491-1.0607-0.619-1.0607-2.2023 0-0.98945-0.087719-1.799-0.19493-1.799-0.10721 0-1.3432 0.7861-2.7465 1.7469-3.6303 2.4854-6.5195 3.4825-10.577 3.6501-1.8587 0.076809-4.0792-0.035787-4.9344-0.25022zm10.869-7.8902c1.335-0.41548 3.5842-1.4442 4.9983-2.2861l2.5711-1.5307 0.01384-14.15-1.2922 0.2131c-0.71069 0.1172-2.8129 0.40431-4.6717 0.63802-10.945 1.3761-14.7 3.9947-14.789 10.313-0.088907 6.2703 5.5398 9.1779 13.17 6.8032zm86.943 6.8807c-6.1313-1.4835-10.567-5.1564-13.707-11.349-2.1236-4.1882-2.6601-8.0461-2.0656-14.854 0.66043-7.5633 2.6364-11.975 7.367-16.449 3.9419-3.7281 8.0809-4.9975 15.522-4.7608 3.9148 0.12453 4.6221 0.26417 6.7438 1.3313 1.3037 0.6557 3.2717 2.0931 4.3735 3.1942 3.582 3.5799 5.1998 8.1609 5.6466 15.989l0.2553 4.4729h-32.435l0.20437 2.546c0.2643 3.2927 2.2001 7.5488 4.4152 9.7073 2.6795 2.6111 5.4398 3.7745 9.42 3.9701 4.4031 0.21642 7.2658-0.5078 13.006-3.2903l4.4729-2.1682v8.6511l-4.2741 1.3964c-2.3507 0.768-5.2222 1.5879-6.381 1.822-2.9124 0.58839-9.7393 0.47533-12.564-0.20808zm16.454-29.285c-0.00324-0.49202-0.27084-2.0184-0.59469-3.392-1.5007-6.3651-4.5627-8.9214-10.731-8.9584-3.9164-0.023495-6.3185 0.9287-9.0086 3.5711-1.9204 1.8863-4.4815 6.7039-4.2374 7.971 0.29182 1.5153 1.6731 1.6909 13.345 1.697l11.232 0.00584-0.00591-0.89458zm-342.32-1.7031v-31.224l5.8644 0.26203c3.2254 0.14412 5.9783 0.37375 6.1175 0.51028 0.13919 0.13654 5.6856 10.857 12.325 23.822l12.072 23.574 0.10248-24.085 0.10248-24.085 8.1444 0.22654v62.024h-9.4829l-13.15-25.713c-7.2325-14.142-13.284-25.846-13.448-26.009-0.16401-0.16304-0.29858 7.0242-0.29905 15.972-4.7769e-4 8.9474-0.11858 20.696-0.26246 26.108l-0.2616 9.8403h-7.8245zm130.41 7.9651v-23.259h7.1566v46.518h-7.1566zm19.482-0.015476v-23.274l6.9578 0.22975 0.12017 2.0873c0.066098 1.148 0.19873 2.0873 0.29475 2.0873s1.0103-0.6379 2.0317-1.4175c5.7491-4.3884 12.01-5.5477 16.755-3.1026 1.0287 0.53009 2.8761 2.0606 4.1053 3.4011l2.2349 2.4373 2.9254-2.289c6.3863-4.9969 10.623-5.9501 16.566-3.7267 2.819 1.0547 4.9617 3.1717 6.4678 6.3902 1.0904 2.3302 1.2756 3.2558 1.5861 7.9275 0.19336 2.9091 0.42258 11.417 0.50939 18.907l0.15783 13.617h-8.1096l-0.2463-16.202c-0.30778-20.246-0.62766-22.106-4.0842-23.746-3.015-1.4307-7.4805-0.27642-12.337 3.1888l-2.5381 1.8111v34.948h-7.1566l-0.013603-10.039c-0.015937-11.763-0.56469-23.72-1.187-25.864-0.24135-0.83168-0.95021-2.0841-1.5753-2.7831-1.1329-1.267-1.1488-1.271-5.0609-1.271-3.7823 0-4.0077 0.050406-6.2226 1.3916-1.264 0.76536-2.9117 1.8966-3.6615 2.5139l-1.3634 1.1223v34.93h-7.1566zm-20.277-34.972v-4.1747h8.747v8.3494h-8.747zm-37.251-42.517c-4.3113-2.0073-5.2943-3.3475-6.0561-8.2568-0.63678-4.1037-1.1527-13.271-3.0162-53.593-4.3546-94.223-4.5611-100.73-3.3274-104.81 0.46305-1.5309 3.0652-3.6732 5.2601-4.3306 3.9368-1.1791 12.705-1.7395 65.065-4.1583 18.697-0.86369 38.556-1.7864 44.132-2.0505s15.148-0.496 21.271-0.51537l11.132-0.035216 1.8911 1.313c1.0672 0.741 2.2414 1.9997 2.6952 2.8891 1.6581 3.2501 1.8939 6.2811 3.9567 50.864 3.2992 71.302 3.9146 85.636 4.2364 98.684 0.254 10.296 0.24418 10.486-0.65156 12.559-1.0834 2.5073-3.3536 4.5866-5.9218 5.424-2.1119 0.68859-8.2986 1.1035-33.043 2.2158-9.731 0.43745-37.264 1.6891-61.185 2.7815-23.921 1.0924-43.691 1.9844-43.934 1.9822-0.24247-0.00216-1.3697-0.43637-2.505-0.96496zm19.174-41.774c6.3341-0.3292 31.108-1.4923 55.052-2.5847 51.985-2.3717 61.372-2.8347 61.601-3.0389 0.095398-0.085158-1.0292-26.765-2.4992-59.288-1.47-32.523-2.6662-59.631-2.6584-60.239l0.01428-1.1059-4.0753 0.22991c-2.2414 0.12645-21.967 1.0273-43.834 2.0018-53.548 2.3865-81.08 3.704-83.593 4.0003-2.0634 0.24327-2.0873 0.2617-2.0873 1.6064 0 1.9025 2.1619 50.988 3.5784 81.247 0.65004 13.886 1.2826 27.483 1.4058 30.217 0.12313 2.7334 0.31257 5.5513 0.42097 6.262l0.1971 1.2922h2.4805c1.3643 0 7.6629-0.26935 13.997-0.59855zm-11.475-5.2546c-0.33052-0.53479-1.5592-24.669-1.6148-31.719-0.039577-5.0153 0.04408-5.7433 0.84524-7.3554 1.6359-3.2918 23.56-33.195 30.125-41.088 3.1918-3.8377 6.1956-5.0149 9.6889-3.7972 1.4769 0.51485 6.2436 5.9412 19.521 22.223 21.188 25.982 20.794 25.532 21.634 24.624 0.29006-0.31339 3.1727-3.6114 6.4059-7.3288 3.2332-3.7175 7.3197-8.2297 9.0812-10.027 2.8778-2.9367 3.4171-3.3128 5.3155-3.7069 3.3465-0.69474 4.4274-0.12907 9.3393 4.8877 5.1267 5.2362 9.0812 10.133 9.771 12.099 1.1773 3.3554 2.0669 15.38 2.0731 28.022l0.00389 7.8167-3.2801 0.24254c-1.8041 0.13339-27.076 1.3146-56.159 2.6248-29.084 1.3102-55.045 2.4954-57.691 2.6337-3.086 0.16128-4.9006 0.10736-5.0598-0.15037zm95.307-78.574c-4.3375-1.1068-7.6657-3.9867-9.1042-7.878-0.58625-1.5859-0.78508-3.1268-0.78684-6.0981-0.00281-4.7101 0.8057-6.8086 3.7577-9.7536 2.5766-2.5704 5.0863-3.6546 8.9628-3.8718 3.7501-0.21012 5.5866 0.21701 8.5344 1.9849 5.7378 3.4411 8.2169 11.377 5.4649 17.493-1.3464 2.9923-4.2138 6.0071-6.9397 7.2967-2.6359 1.247-6.859 1.6001-9.8891 0.82698zm50.573 104.98c-0.68202-0.4987-0.6847-0.53678-1.8269-25.916-0.23619-5.2482-0.53686-10.839-0.66816-12.425l-0.23873-2.8825h1.6046c2.5594 0 3.1942-0.82722 3.6613-4.7711 1.5635-13.2 11.993-115.39 11.796-115.58-0.15805-0.15805-20.111-2.2803-72.012-7.6594-52.195-5.4096-60.161-6.1795-60.494-5.8467-0.15817 0.15817-0.70542 3.7257-1.2161 7.9278-0.60546 4.9819-1.124 7.8357-1.4904 8.2021-0.62018 0.62018-6.1264 0.78819-8.5073 0.25959l-1.3341-0.2962 0.26127-2.6429c0.74564-7.5426 1.8777-15.748 2.3697-17.176 0.79064-2.2949 2.4979-4.6077 4.1678-5.6462 2.8627-1.7803 8.0622-1.4114 65.249 4.6288 28.318 2.9911 57.242 6.0413 64.274 6.7782 14.519 1.5215 13.901 1.3212 17.071 5.5316 1.5902 2.1121 1.7506 2.5317 1.7479 4.5723-0.013797 10.407-12.091 127.74-15.741 152.93-0.34837 2.4041-0.81672 4.73-1.0408 5.1687-1.4713 2.8805-6.2096 5.8885-7.6346 4.8466z" stroke-width=".39759"/></g></svg>'
            );
        }

        $imageUrl = $storage->url($pathName);
        if ('full' == $link) {
            return $imageUrl;
        }

        return ltrim(\parse_url($imageUrl, PHP_URL_PATH), '/');
    }
}

if (!function_exists('image_exist')) {
    /**
     * Checks if image exists by path.
     *
     * @author Bendiucov Tatiana
     *
     * @todo Refactoring [26.10.2021]
     * Reason: refactor method name
     *
     * @param string $link - url
     *
     * @return int - 1 or 0
     *
     * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/b.-Images#image_existlink
     */
    function image_exist($link)
    {
        return (int) is_file($link);
    }
}

if (!function_exists('switchBadgeImages')) {
    /**
     * Checks if image exists by path.
     *
     * @param string $userPhoto - name of the photo
     *
     * @return int - 1 or 0
     *
     * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/b.-Images#image_existlink
     */
    function switchBadgeImages(int $idUser, string $userPhoto)
    {
        $files = [
            'main' => getImagePath(['{ID}' => $idUser, '{FILE_NAME}' => $userPhoto]),
            '0'    => getImagePath(['{ID}' => $idUser, '{FILE_NAME}' => $userPhoto], 'users.main', 0),
            '1'    => getImagePath(['{ID}' => $idUser, '{FILE_NAME}' => $userPhoto], 'users.main', 1),
        ];
        $config = config('img.users.main.watermark');

        foreach ($files as $key => $image) {
            $badgeImage = getWatermarkImagePath(['{ID}' => $idUser, '{FILE_NAME}' => $userPhoto], 'users.main', 'main' === $key ? null : $key);

            if (!file_exists($image) || !file_exists($badgeImage)) {
                continue;
            }

            $tempImage = getImagePath(['{ID}' => $idUser, '{FILE_NAME}' => 'temp_' . $userPhoto], 'users.main');

            rename($badgeImage, $tempImage);
            rename($image, $badgeImage);
            rename($tempImage, $image);

            touch($badgeImage);
            touch($image);

            //region webp
            $filename = pathinfo($image, PATHINFO_FILENAME);
            $webp = getImagePath(['{ID}' => $idUser, '{FILE_NAME}' => $filename . '.webp'], 'users.main');
            $webpBadge = getImagePath(['{ID}' => $idUser, '{FILE_NAME}' => $config['prefix'] . $filename . '.webp'], 'users.main');

            if (file_exists($webp) && file_exists($webpBadge)) {
                rename($webpBadge, $tempImage);
                rename($webp, $webpBadge);
                rename($tempImage, $webp);

                touch($webp);
                touch($webpBadge);
            }
            //endregion webp
        }
    }
}
