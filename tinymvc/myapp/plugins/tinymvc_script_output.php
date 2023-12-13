<?php

use App\Common\Contracts\Group\GroupType;
use App\Common\Contracts\User\UserStatus;
use App\Common\Database\BaseModel;
use App\Common\DependencyInjection\ServiceLocator\LibraryLocator;
use App\Common\DependencyInjection\ServiceLocator\ModelLocator;
use App\Common\Http\LegacyJsonResponse;
use App\Common\Proxy\HigherOrderTapProxy;
use App\Messenger\Message\Event\Lifecycle\UserWasMutedEvent;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ConnectionRegistry;
use ExportPortal\Bridge\Symfony\Component\Messenger\MessengerInterface;
use Money\Converter;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Exception\ParserException;
use Money\Exchange\FixedExchange;
use Money\Formatter\AggregateMoneyFormatter;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Formatter\IntlMoneyFormatter;
use Money\Money;
use Money\Parser\AggregateMoneyParser;
use Money\Parser\DecimalMoneyParser;
use Money\Parser\IntlLocalizedDecimalParser;
use Money\Parser\IntlMoneyParser;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use const App\Common\PUBLIC_DATETIME_FORMAT;
use const App\Common\PUBLIC_PATH;
use const App\Common\ROOT_PATH;
use const App\Moderation\Types\TYPE_B2B;
use const App\Moderation\Types\TYPE_COMPANY;
use const App\Moderation\Types\TYPE_ITEM;
use \App\Common\Contracts\Company\CompanyType;

/**
 * Returns the subcategories list view by level.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [27.10.2021]
 * Reason: Code style
 * Reason: refactor method name
 *
 * @param array  $sub_categories - list of categories
 * @param string $level          - prefix (usually &mdash;)
 *
 * @return \TinyMVC_View - view with the generated subcategories
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/q.-Output#recursive_ctegories_product
 */
function recursive_ctegories_product($sub_categories, $level)
{
    $level .= '&mdash;';
    views('new/item/select_product_view', ['categories'=>$sub_categories, 'level' => $level]);
}

/**
 * Used for display clean output. Need to use if cleanInput was not used on inserting data to database.
 *
 * @param mixed $output - string to clean
 *
 * @return string the cleaned output
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/g.-Strings-and-text#cleanOutput
 */
function cleanOutput($output)
{
    return htmlspecialchars((string) $output, ENT_QUOTES, 'UTF-8', false);
}

/**
 * Decode url param.
 */
function decodeUrlString(string $string): string
{
    return mb_detect_encoding($string, 'UTF-8') ? urldecode($string) : utf8_encode(urldecode($string));
}

/**
 * Checks if values are equal then returns 'selected'.
 *
 * @param string $value       - value to compare
 * @param string $compareWith - value to compare with
 *
 * @return string - selected or ''
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/q.-Output#selected
 */
function selected($value = '', $compareWith = '')
{
    return ($value == $compareWith) ? 'selected' : '';
}

/**
 * Get path to the seller's widget.
 *
 * @param int    $idSeller - the id of the seller
 * @param string $key      - seller widget key
 * @param string $url      - url to the widget
 *
 * @return string - path to the widget
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/e.-Groups-and-Rights#sellerWidgetFilePath
 */
function sellerWidgetFilePath($idSeller, $key, $url)
{
    $url = preg_replace('/^\s*(https?:\/\/)?(www\.)?/', '', $url);
    $url = preg_replace('/\/.*/', '', $url);
    $url = preg_replace('/\?.*/', '', $url);

    return "public/widgets/{$idSeller}/{$key}.{$url}.widget";
}

/**
 * Checks if value is in the list or equal to value and returns "checked" if it is.
 *
 * @param string $search - value to search
 * @param mixed  $in     - array or value to compare with
 *
 * @return string|void - checked
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/q.-Output#checked
 */
function checked($search, $in)
{
    if ((is_array($in) && in_array($search, $in)) || $search == $in) {
        return 'checked';
    }
}

/**
 * Checks if values are equal then returns 'active'.
 *
 * @param string $value       - value to compare
 * @param string $compareWith - value to compare with
 *
 * @return string - active or ''
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/q.-Output#active
 */
function active($value = '', $compareWith = '')
{
    return ($value == $compareWith) ? 'active' : '';
}

/**
 * Returns the $str param if values equal.
 *
 * @param mixed $search - what to compare
 * @param mixed $in     - what to compare to
 * @param mixed $str    - what to return in case it is equal
 *
 * @return mixed $str - the string to return
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/q.-Output#equals
 */
function equals($search, $in, $str)
{
    if ($search == $in) {
        return $str;
    }
}

/**
 * Retruns $str if values are equal and $elseStr if are not.
 *
 * @param mixed $search  - what to compare
 * @param mixed $in      - what to compare to
 * @param mixed $str     - what to return in case it is equal
 * @param mixed $elseStr
 *
 * @return mixed $str or $elseStr
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/q.-Output#equalsElse
 */
function equalsElse($search, $in, $str, $elseStr)
{
    if ($search == $in) {
        return $str;
    }

    return $elseStr;
}

/**
 * Truncate a text to a certain amount of words.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [27.10.2021]
 * Reason: refactor method name
 *
 * @param string $str   - the string to be trunated
 * @param int    $words - the number of words to be reduced to
 *
 * @return string - the truncated string
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/g.-Strings-and-text#truncWords
 */
if (!function_exists('truncWords')) {
    function truncWords($str, $words = 15)
    {
        $count = count(preg_split('/[\\s]+/', $str));
        $arr = preg_split('/[\\s]+/', $str, $words + 1);
        if (count($arr) < $count) {
            $arr[$words] = '...';
        }

        $arr = array_slice($arr, 0, $words + 1);

        return join(' ', $arr);
    }
}

/**
 * Returns the the string with the capital letters for words, exceptions make 'the, of, and etc.'.
 *
 * @param string $string - string to capitalize
 *
 * @return string the new capitalized string
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/g.-Strings-and-text#capitalWord
 */
function capitalWord($string)
{
    $string = ucwords(strtolower($string));

    $search = ['Of ', 'The ', 'And ', 'Or ', 'Not ', 'But ', 'Yet ', 'So ', 'Nor ', 'As ', 'For '];
    $replace = ['of ', 'the ', 'and ', 'or', 'not', 'but ', 'yet ', 'so ', 'nor ', 'as ', 'for '];

    return str_replace($search, $replace, $string);
}

/**
 * @author Bendiucov Tatiana
 *
 * @deprecated [27.10.2021]
 * Old method
 * @see getDateFormat
 *
 * @param mixed $date
 * @param mixed $format
 * @param mixed $default
 */
function formatDate($date, $format = PUBLIC_DATETIME_FORMAT, $default = '&mdash;')
{
    $str_to_time = strtotime($date);
    if ($str_to_time && '0000-00-00 00:00:00' != $date) {
        return date($format, $str_to_time);
    }

    return $default;
}

/**
 * Returns how many years, months and days has passed since one date to another
 * May be depracated but there is no better alternative at the moment.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [27.10.2021]
 * @deprecated Use timeAgo function
 * Reason: Code style
 * Reason: refactor method name
 *
 * @param mixed  $date    - start date
 * @param string $format  - format of the date to return (by default years months and days too)
 * @param mixed  $date_to - end date - if not set then today right now
 *
 * @return string time ago
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/i.-Date-and-Time#time_Ago
 */
function time_Ago($date, $format = 'Y,m,d', $date_to)
{
    $count_date = [];

    if (empty($date_to)) {
        $date_to = time();
    }

    $days = floor(($date_to - strtotime($date)) / 86400);
    $formats = explode(',', $format);
    if (in_array('Y', $formats)) {
        $years = floor($days / 365);
        if ($years > 0) {
            $ynum = ($years > 1) ? ' years' : ' year';
            $count_date[] = $years . $ynum;
        }
    }

    $rest = $days % 365;
    if (in_array('m', $formats)) {
        $months = floor($rest / 30);
        if ($months > 0) {
            $mnum = ($months > 1) ? ' months' : ' month';
            $count_date[] = $months . $mnum;
        }
    }

    if (in_array('d', $formats)) {
        $days = $rest % 30;
        if ($days > 0) {
            $dnum = ($days > 1) ? ' days' : ' day';
            $count_date[] = $days . $dnum;
        }
    }

    return implode(', ', $count_date);
}

/**
 * Returns how many years, months and days has passed since one date to another
 * or returns 'recently' if the format parameter said for example years and is less than a year
 * May be depracated but there is no better alternative at the moment.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [27.10.2021]
 * Reason: Code style
 *
 * @param mixed  $date   - start date
 * @param string $format - format of the date to return (by default years months and days too)
 * @param mixed  $limit  - end date - if not set then today right now, if true then will return 'ago' in the end of the string
 * @param bool   $ago
 *
 * @return string time ago
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/i.-Date-and-Time#timeAgo
 */
function timeAgo($date, $format = 'Y,m', $limit = false, $ago = true)
{
    $currentDate = new DateTime();
    $targetDate = (new DateTime())->setTimestamp(strtotime($date));
    $dateDiff = $currentDate->diff($targetDate);
    $etime = $currentDate->getTimestamp() - $targetDate->getTimestamp();

    if (false != $limit) {
        $s = 1;
        $min = 60 * $s;
        $h = 60 * $min;
        $d = 24 * $h;
        $m = 30 * $d;
        $y = 12 * $m;
        $limits = ['Y' => $y, 'm' => $m, 'd' => $d];
        if ($etime > ($limits[$limit] ?? null)) {
            return formatDate($date, 'j F Y');
        }
    }

    $formats = explode(',', $format);

    if ($etime < 1) {
        return '0 seconds';
    }

    $timeAgo = [];
    if (in_array('Y', $formats)) {
        if ($dateDiff->y >= 1) {
            $timeAgo[] = $dateDiff->y . ' year' . ($dateDiff->y > 1 ? 's' : '');
        }
    }

    if (in_array('m', $formats)) {
        if ($dateDiff->m >= 1) {
            $timeAgo[] = $dateDiff->m . ' month' . ($dateDiff->m > 1 ? 's' : '');
        }
    }

    if (in_array('d', $formats)) {
        if ($dateDiff->d >= 1) {
            $timeAgo[] = $dateDiff->d . ' day' . ($dateDiff->d > 1 ? 's' : '');
        }
    }

    if (in_array('H', $formats)) {
        if ($dateDiff->h >= 1) {
            $timeAgo[] = $dateDiff->h . ' hour' . ($dateDiff->h > 1 ? 's' : '');
        }
    }

    if (in_array('i', $formats)) {
        if ($dateDiff->h >= 1) {
            $timeAgo[] = $dateDiff->h . ' minute' . ($dateDiff->h > 1 ? 's' : '');
        }
    }

    if (in_array('s', $formats)) {
        if ($dateDiff->s >= 1) {
            $timeAgo[] = $dateDiff->s . ' second' . ($dateDiff->s > 1 ? 's' : '');
        }
    }

    if (!empty($timeAgo[0])) {
        return implode(', ', $timeAgo) . (($ago) ? ' ago' : '');
    }

    return 'recently';
}

/**
 * Verifies if right(s) exist in the session or in the list sent. Only when all are available then returns true.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [27.10.2021]
 * Reason: refactor method name
 *
 * @param string $rights          - the right(s) to verify
 * @param array  $availableRights - list of rights, if not set then they are taken from the session
 *
 * @return bool
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/e.-Groups-and-Rights#have_right
 */
function have_right($rights, $availableRights = [])
{
    $all = true;
    $existRights = session()->rights;

    if (!empty($availableRights)) {
        $existRights = $availableRights;
    }

    if (empty($existRights)) {
        return false;
    }

    $searchRights = explode(',', $rights);
    foreach ($searchRights as $right) {
        if (!in_array(trim($right), $existRights)) {
            $all = false;
        }
    }

    return $all;
}

/**
 * Verifies if at least one right exist in the session or in the list sent.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [27.10.2021]
 * Reason: refactor method name
 *
 * @param string $searchRights         - the right(s) to verify
 * @param array  $userRights
 *
 * @return bool
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/e.-Groups-and-Rights#have_right_or
 */
function have_right_or($searchRights, $userRights = [])
{
    if (empty($userRights = $userRights ?: session()->rights)) {
        return false;
    }

    $searchRights = array_map(
        fn ($right) => trim($right),
        is_array($searchRights) ? $searchRights : explode(',', $searchRights)
    );

    return !empty(array_intersect($searchRights, $userRights));
}

/**
 * Returns the the string as for url keywords (filtered and instead of spaces are '+' signs).
 *
 * @param string $str - the string to change
 *
 * @return string
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/g.-Strings-and-text#strForUrlKeywords
 */
function strForUrlKeywords(string $str)
{
    $str = explode(' ', $str);
    $str = array_filter($str);

    return implode('+', $str);
}

/**
 * Returns the string encoded for url.
 *
 * @param string $str       - the string to change
 * @param string $delimeter - the delimiter instead of spaces (by default -)
 * @param bool   $lower     - to lowercase (by default true)
 *
 * @return string - the url encoded string
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/g.-Strings-and-text#strForURL
 */
function strForURL($str, $delimeter = '-', $lower = true)
{
    $rules = [
        'Any-Latin;',
        'NFD;',
        '[:Nonspacing Mark:] Remove;',
        'NFC;',
        '[:Punctuation:] Remove;',
    ];

    if ($lower) {
        $rules[] = 'Lower();';
    }

    $str = transliterator_transliterate(implode('', $rules), $str);
    $str = preg_replace('/[^a-zA-Z0-9\\/\_\ \-]/', '', $str);
    $str = preg_replace('/[-\s]+/', $delimeter, $str);

    return trim($str, $delimeter);
}

/**
 * Decodes the url to string.
 *
 * @param string $str    - the encoded string
 * @param bool   $idLast - is there an id in the end, if it is then remove it (by default true)
 *
 * @return string the decoded string
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/g.-Strings-and-text
 */
function urlToStr(string $str, bool $idLast = true)
{
    $words = explode('-', capitalWord($str));
    if ($idLast) {
        array_pop($words);
    }

    return implode(' ', $words);
}

/**
 * @author Bendiucov Tatiana
 *
 * @deprecated [27.10.2021]
 * Old method
 * @see trunc_words
 *
 * @param mixed $string
 * @param mixed $word_limit
 */
function limit_words($string, $word_limit)
{
    $words = explode(' ', $string);
    if (count($words) > $word_limit) {
        return implode(' ', array_splice($words, 0, $word_limit)) . ' ...';
    }

    return $string;
}

/**
 * Get the id from the string.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [27.10.2021]
 * Reason: refactor method name
 *
 * @param string $str - string containing the id in the end
 *
 * @return int - the id
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/g.-Strings-and-text#id_from_link
 */
function id_from_link($str)
{
    $segments = explode('-', $str);

    return (int) end($segments);
}

/**
 * Searches the multidimensional array for some value by key. May return multiple results if parameter is set so.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [27.10.2021]
 * Reason: refactor method name
 *
 * @param array  $array    - the array to search in
 * @param mixed  $key      - the key for which to search
 * @param string $value    - the value to search for
 * @param bool   $multiple - return one result(false) or multiple(true) (by default false)
 *
 * @return array - the array that has the value or values
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/f.-Arrays-and-Objects#search_in_array
 */
function search_in_array($array, $key, $value, $multiple = false)
{
    foreach ($array as $arr) {
        if ($arr[$key] == $value) {
            if ($multiple) {
                $found[] = $arr;
            } else {
                return $arr;
            }
        }
    }

    return $found;
}

/**
 * Returns an array that has grouped some elements by key. The delimiter is ','.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [27.10.2021]
 * Reason: refactor method name
 *
 * @param array $array - elements
 * @param array $keys  - array with the keys to group by
 *
 * @return array the elements grouped by the keys
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/f.-Arrays-and-Objects#arrayToListKeys
 */
function arrayToListKeys($array, $keys)
{
    $listOfKeys = [];
    foreach ($keys as $key) {
        foreach ($array as $element) {
            $listOfKeys[$key][] = $element[$key];
        }

        $listOfKeys[$key] = implode(',', $listOfKeys[$key]);
    }

    return $listOfKeys;
}

/**
 * Gets only the number from a string.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [27.10.2021]
 * Reason: refactor method name
 *
 * @param string $str - string to search into
 *
 * @return null|string|string[] - the found number (if it was found)
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/h.-Numbers-and-Money#get_only_number
 */
function get_only_number($str)
{
    return preg_replace('/\\D/', '', $str);
}

/**
 * @author Bendiucov Tatiana
 *
 * @deprecated [27.10.2021]
 * Old method
 * @see remove_dir
 *
 * @param mixed $dir
 */
function removeDirectory($dir)
{
    if ($objs = glob($dir . '/*')) {
        foreach ($objs as $obj) {
            is_dir($obj) ? removeDirectory($obj) : unlink($obj);
        }
    }

    rmdir($dir);
}

/**
 * Function to generate the video's link from YouTube or Vimeo.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [27.10.2021]
 * Reason: refactor method name
 *
 * @param string $id       - the id of the video
 * @param string $type     - the type of the player (youtube or vimeo)
 * @param int    $autoplay - if autoplay then 1 (by default)
 *
 * @return string the link to the video
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/q.-Output#get_video_link
 */
function get_video_link($id, $type, $autoplay = 1)
{
    if (empty($id)) {
        return false;
    }

    switch ($type) {
        case 'youtube': return 'https://www.youtube.com/embed/' . $id . '?autoplay=' . $autoplay;

break;
        case 'vimeo': return 'https://player.vimeo.com/video/' . $id;

break;
    }
}

/**
 * @depracted
 *
 * @see get_video_link
 *
 * @param mixed $id
 * @param mixed $type
 */
function getDirectVideoLink($id, $type)
{
    if (empty($id)) {
        return '#';
    }

    switch ($type) {
        case 'youtube': return 'https://www.youtube.com/watch?v=' . $id;
        case 'vimeo': return 'https://vimeo.com/' . $id;
    }
}

/**
 * Return iframe for video (vimeo or youtube).
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [27.10.2021]
 * Reason: refactor method name
 *
 * @param string $id       - the id of the video
 * @param string $type     - the type of the player (youtube or vimeo)
 * @param int    $w        - width of the iframe
 * @param int    $h        - height of the iframe
 * @param int    $autoplay - if 1 then autoplay is on
 *
 * @return string - iframe
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/q.-Output#generate_video_html
 */
function generate_video_html($id, $type, $w, $h, $autoplay = 1)
{
    if (empty($id)) {
        return false;
    }

    switch ($type) {
        case 'vimeo':
            return '<iframe class="player bd-none" src="//player.vimeo.com/video/' . $id . '?autoplay=' . $autoplay . '&title=0&amp;byline=0&amp;portrait=0&amp;color=13bdab" width="' . $w . '" height="' . $h . '" webkitallowfullscreen mozallowfullscreen allowfullscreen' . addQaUniqueIdentifier('global__video-iframe') . '></iframe>';
        case 'youtube':
            return '<iframe class="player bd-none" width="' . $w . '" height="' . $h . '" src="//www.youtube.com/embed/' . $id . '?autoplay=' . $autoplay . '" allowfullscreen ' . addQaUniqueIdentifier('global__video-iframe') . '></iframe>';
    }
}

/**
 * Validates and cleans the emails as string or the emails list sent as array.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [27.10.2021]
 * Reason: refactor method name
 *
 * @param array|string $emails
 *
 * @return array - filtered emails
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/j.-Email-and-Password#filter_email
 */
function filter_email($emails)
{
    if (!is_array($emails)) {
        $emails = explode(\App\Common\EMAIL_DELIMITER, $emails);
    }

    $filtered_emails = [];
    foreach ($emails as $one) {
        $filter_em = trim($one);
        if (filter_var($filter_em, FILTER_VALIDATE_EMAIL)) {
            $filtered_emails[] = $filter_em;
        }
    }

    return $filtered_emails;
}

/**
 * @author Bendiucov Tatiana
 *
 * @deprecated [27.10.2021]
 * Old method
 * @see getDisplayImageLink and getImgSrc
 *
 * @param mixed $img_path
 * @param mixed $template_img
 */
function getImage($img_path, $template_img = false)
{
    $path_parts = pathinfo($img_path);
    if (!empty($path_parts['extension']) && file_exists($img_path)) {
        return $img_path;
    }

    return $template_img;
}

/**
 * Get file size as a string (ex: 10 MB).
 *
 * @param string $fileName - the path of the file to check
 *
 * @return string
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/g.-Strings-and-text#fileSizeSuffix
 */
function fileSizeSuffix($fileName)
{
    if (!file_exists($fileName) || !is_readable($fileName)) {
        return '0 B';
    }

    $size = filesize($fileName);
    if ($size > pow(1024, 3)) {
        return round(($size / pow(1024, 3)), 2) . ' GB';
    }
    if ($size > pow(1024, 2)) {
        return round(($size / pow(1024, 2))) . ' MB';
    }
    if ($size > 1024) {
        return round(($size / 1024)) . ' KB';
    }

    return "{$size} B";
}

/**
 * Takes a number (file size sent as parameter) to calculate and return it as GB or MB, etc (ex 10 GB).
 *
 * @param int $fileSize - the size of the file
 *
 * @return string
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/g.-Strings-and-text#fileSizeSuffixText
 */
function fileSizeSuffixText($fileSize)
{
    $size = (int) $fileSize;
    if ($size > pow(1024, 3)) {
        return round(($size / pow(1024, 3)), 2) . ' GB';
    }
    if ($size > pow(1024, 2)) {
        return round(($size / pow(1024, 2))) . ' MB';
    }
    if ($size > 1024) {
        return round(($size / 1024)) . ' KB';
    }

    return "{$size} B";
}

/**
 * Returns an array with a column as a key grouped or not.
 *
 * @param array  $records    - array to change
 * @param string $key        - the name of the column that will become key
 * @param bool   $groupByKey - if true then all values are set as array in one key, if false then array column functionality used
 *
 * @return array
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/f.-Arrays-and-Objects#arrayByKey
 */
function arrayByKey($records, $key, $groupByKey = false)
{
    $result = [];

    if (empty($records) || !is_array($records) || empty($key)) {
        return $result;
    }

    if (!$groupByKey) {
        return array_column($records, null, $key);
    }

    foreach ($records as $record) {
        if (!isset($record[$key])) {
            continue;
        }

        //We cast $record[$key] to string because it can be an object, for example an Enum, returned from the model
        $result[(string) $record[$key]][] = $record;
    }

    return $result;
}

/**
 * Hide the string with the type word.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [27.10.2021]
 * Reason: Code style
 *
 * @param string $type - type of hidden information (email, phone, fax, info)
 * @param string $str  - not used now
 *
 * @return string - hidden string
 *
 * @see
 */
function hiden($type, $str = '')
{
    $hiden = 'Click to show';

    switch ($type) {
        case 'email':
            // $hiden = substr($str, 0, 1).'*****@'.substr($str, strrpos($str,'@')+1, 1).'*****.'.substr($str, strrpos($str,'.')+1);
            $hiden .= ' email';

        break;
        case 'phone':
            // $hiden = substr($str, 0, 4).'** **** **';
            $hiden .= ' phone';

        break;
        case 'fax':
            // $hiden = substr($str, 0, 4).'** **** **';
            $hiden .= ' fax';

        break;
        case 'info':
            // $hiden = substr($str, 0, 4).'** **** **';
            $hiden .= ' information';

        break;
    }

    return $hiden;
}

/**
 * Return "disabled='disabled'" string if values are equal.
 *
 * @param mixed $search - what to compare
 * @param mixed $in     - what to compare with
 *
 * @return null|string - disabled='disabled' if equals
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/q.-Output#disabled
 */
function disabled($search, $in)
{
    if ($search == $in) {
        return 'disabled = \'disabled\'';
    }
}

/**
 * Get certification expire soon from session.
 *
 * @return int - session value of certificationExpireSoon
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/p.-Account-related-helpers#getCertificationExpireSoon
 */
function getCertificationExpireSoon()
{
    return (int) session()->certificationExpireSoon;
}

/**
 * Remove certification expire soon from session.
 *
 * @return bool true
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/p.-Account-related-helpers#clearCertificationExpireSoon
 */
function clearCertificationExpireSoon()
{
    session()->clear('certificationExpireSoon');

    return true;
}

/**
 * Session get paid until value.
 *
 * @return mixed
 *
 * @see
 */
function getPaidUntil()
{
    return isset(session()->paidUntil) ? session()->paidUntil : false;
}

/**
 * Check if current user is an admin logged as user.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [27.10.2021]
 * Reason: refactor method name
 *
 * @return int - 0 or 1
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/d.-Checks-and-permissions#admin_logged_as_user
 */
function admin_logged_as_user()
{
    return (int) session()->id_admin > 0;
}

/**
 * Return the id from the session of the admin logged as another user.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [27.10.2021]
 * Reason: refactor method name
 *
 * @return int
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/d.-Checks-and-permissions#admin_logged_as_id
 */
function admin_logged_as_id()
{
    return (int) session()->id_admin;
}

/**
 * Returns the name of the admin from session that is logged as another user.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [27.10.2021]
 * Reason: refactor method name
 *
 * @return string
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/d.-Checks-and-permissions#admin_logged_as_name
 */
function admin_logged_as_name()
{
    return session()->admin_name;
}

/**
 * Returns true or false if the user is currently logged in.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [27.10.2021]
 * Reason: refactor method name
 *
 * @return bool
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/d.-Checks-and-permissions#logged_in
 */
function logged_in()
{
    return session()->loggedIn;
}

/**
 * @author Bendiucov Tatiana
 *
 * @deprecated [27.10.2021]
 * need to see if it is relevant
 */
function logged_in_by_token()
{
    return empty(tmvc::instance()->controller->session->accreditation_token);
}

/**
 * Checks if the current user is my user (the owner).
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [27.10.2021]
 * Reason: refactor method name
 *
 * @param int $id - id of the user to check if current
 *
 * @return bool
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/d.-Checks-and-permissions#is_my
 */
function is_my($id)
{
    return session()->id == $id;
}

/**
 * Checks if the id of the company is current user's company.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [27.10.2021]
 * Reason: refactor method name
 *
 * @param int $idCompany - the id of the company to check if current
 * @param bool
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/d.-Checks-and-permissions#is_my_company
 */
function is_my_company($idCompany = 0)
{
    if (0 == $idCompany) {
        return false;
    }

    $companies = !empty(session()->companies) ? session()->companies : [];

    return in_array($idCompany, $companies);
}

/**
 * Checks if the value of the key is the owner. Needs to be deleted after a while to use @see in_session().
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [27.10.2021]
 * Reason: refactor method name
 *
 * @param mixed  $id         - the value to search for
 * @param string $sessionVar - the name of the session key to look up
 *
 * @return bool
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/d.-Checks-and-permissions#of_my
 */
function of_my($id, $sessionVar)
{
    return session()->{$sessionVar} == $id;
}

/**
 * Check if the name of the key is equal to the value sent as a second parameter.
 * The difference between this method and of_my() is that it can work with the array too.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [27.10.2021]
 * Reason: refactor method name
 *
 * @param string $session_array_name - the key name of the session value to look up
 * @param mixed  $value              - the value to search for
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/d.-Checks-and-permissions#of_my
 */
function in_session($session_array_name, $value)
{
    $ses_name = session()->{$session_array_name};
    if (is_array($ses_name)) {
        return in_array($value, $ses_name);
    }

    return $value == $ses_name;
}

/**
 * Checks if `user_type` value in session is equal to the needed value (sent parameter).
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [27.10.2021]
 * Reason: refactor method name
 *
 * @param string $type - the type of user to check
 *
 * @return bool
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/p.-Account-related-helpers#user_type
 */
function user_type($type)
{
    return session()->user_type == $type;
}

/**
 * Returns the value of `subscription_email` value in session.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [27.10.2021]
 * Reason: refactor method name
 *
 * @return int
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/p.-Account-related-helpers#subscription_email
 */
function subscription_email()
{
    return session()->subscription_email;
}

/**
 * Returns the value of `notify_email` in session.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [27.10.2021]
 * Reason: refactor method name
 *
 * @return int
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/p.-Account-related-helpers#notify_email
 */
function notify_email()
{
    return session()->notify_email;
}

/**
 * Returns the `group_type` value from session.
 *
 * @return null|string
 *
 * @author Bendiucov Tatiana
 *
 * @deprecated [27.10.2021]
 * Old method 2.27.5 in favor of userGroupType()
 * @see userGroupType()
 *
 * @uses userGroupType()
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/p.-Account-related-helpers#user_group_type
 */
function user_group_type()
{
    try {
        return (string) userGroupType();
    } catch (\ValueError $e) {
        return null;
    }
}

if (!function_exists('userGroupType')) {
    /**
     * Returns the user grop type value from session.
     *
     * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/p.-Account-related-helpers#userGroupType
     *
     * @throws ValueError if value in session is empty
     */
    function userGroupType(): GroupType
    {
        return GroupType::from(session()->group_type);
    }
}

/**
 * Returs user `status` session value.
 *
 * @return null|string
 *
 * @author Bendiucov Tatiana
 *
 * @deprecated [27.10.2021]
 * Old method 2.27.6 in favor of userStatus()
 * @see userStatus()
 *
 * @uses userStatus()
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/p.-Account-related-helpers#user_status
 */
function user_status()
{
    try {
        return (string) userStatus();
    } catch (\ValueError $e) {
        return null;
    }
}

if (!function_exists('userStatus')) {
    /**
     * Returs user status session value.
     *
     * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/p.-Account-related-helpers#userStatus
     *
     * @throws ValueError if value in session is empty
     */
    function userStatus(): UserStatus
    {
        return UserStatus::from(session()->status);
    }
}

/**
 * Checks if group or list of groups is the right group of the current user.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [27.10.2021]
 * Reason: Code style
 * Reason: refactor method name
 *
 * @param string $group_type
 *
 * @return bool
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/d.-Checks-and-permissions#check_group_type
 */
function check_group_type($group_type = '')
{
    if (empty($group_type)) {
        return false;
    }

    if (!is_array($group_type)) {
        $group_type = explode(',', $group_type);
    }

    $group_type = array_map('trim', $group_type);

    return in_array(user_group_type(), $group_type);
}

/**
 * If current user is staff (not main account) then this method returns the id of the main seller user.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [27.10.2021]
 * Reason: refactor method name
 *
 * @return int
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/d.-Checks-and-permissions#my_seller
 */
function my_seller()
{
    return (int) session()->my_seller;
}

/**
 * Returns the id of the only registered user from session.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [27.10.2021]
 * Reason: refactor method name
 *
 * @return null|int
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/d.-Checks-and-permissions#id_user_verification
 */
function id_user_verification()
{
    $idUserVerification = (int) session()->user_verification;

    if ($idUserVerification) {
        return $idUserVerification;
    }

    return null;
}

/**
 * Return the session id (id of the current user).
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [27.10.2021]
 * Reason: refactor method name
 *
 * @return int
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/p.-Account-related-helpers#id_session
 */
function id_session()
{
    return (int) session()->id;
}
/**
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [27.10.2021]
 * Reason: refactor method name
 *
 * @return string
 */
function photo_session(){
    return session()->user_photo;
}

/**
 * Returns the email from session.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [27.10.2021]
 * Reason: refactor method name
 *
 * @return string
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/p.-Account-related-helpers#email_session
 */
function email_session()
{
    return session()->email;
}

/**
 * Get the name of the group from session (of current user).
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [27.10.2021]
 * Reason: refactor method name
 *
 * @return string
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/p.-Account-related-helpers#group_name_session
 */
function group_name_session()
{
    return session()->group_name;
}

/**
 * @author Bendiucov Tatiana
 *
 * @todo Remove [27.10.2021]
 * Reason: Not used
 */
/*function group_name_suffix_session()
{
    return tmvc::instance()->controller->session->group_name_suffix;
}*/

/**
 * @return string
 */
function groupNameWithSuffix(): string
{
    return group_name_session() . (session()->group_name_suffix ?: '');
}

/**
 * Returns the id of the group from session (of current user).
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [27.10.2021]
 * Reason: refactor method name
 *
 * @return int
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/p.-Account-related-helpers#group_session
 */
function group_session()
{
    return session()->group;
}

/**
 * Return group expired value from session (1 or 0).
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [27.10.2021]
 * Reason: refactor method name
 *
 * @return int
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/p.-Account-related-helpers#group_expired_session
 */
function group_expired_session()
{
    return session()->group_expired;
}

/**
 * Returns the full name of the current user (concatenates fname and lname) from session.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [27.10.2021]
 * Reason: refactor method name
 *
 * @return string
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/p.-Account-related-helpers#user_name_session
 */
function user_name_session()
{
    return trim(session()->fname . ' ' . session()->lname);
}

/**
 * Verifies if current user has id_company in session.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [27.10.2021]
 * Reason: refactor method name
 *
 * @return bool
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/d.-Checks-and-permissions#i_have_company
 */
function i_have_company()
{
    return (bool) session()->id_company;
}

/**
 * If user has company id in the session it returns it, or empty string if he doesn't.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [27.10.2021]
 * Reason: refactor method name
 *
 * @return int|string
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/p.-Account-related-helpers#my_company_id
 */
function my_company_id()
{
    return i_have_company() ? session()->id_company : '';
}

/**
 * Returns true or false depending on the current user group - if he has shipper company.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [27.10.2021]
 * Reason: refactor method name
 *
 * @return bool
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/p.-Account-related-helpers#i_have_shipper_company
 */
function i_have_shipper_company()
{
    return !empty(session()->shipper_id_company);
}

/**
 * If current user is shipper it return his company id else returns -1.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [27.10.2021]
 * Reason: refactor method name
 *
 * @return int
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/p.-Account-related-helpers#my_shipper_company_id
 */
function my_shipper_company_id()
{
    return i_have_shipper_company() ? session()->shipper_id_company : -1;
}

/**
 * If current user is shipper staff this method returns the main shipper's id user.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [27.10.2021]
 * Reason: refactor method name
 *
 * @return int
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/p.-Account-related-helpers#my_shipper
 */
function my_shipper()
{
    return (int) session()->my_shipper;
}

/**
 * Get session variable value.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Remove [27.10.2021]
 * Reason: Not used
 */
/*function var_session($var)
{
    return session()->$var;
}*/

/**
 * Get company index from session if current user has company.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [27.10.2021]
 * Reason: refactor method name
 *
 * @return string - the index
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/p.-Account-related-helpers#my_company_index
 */
function my_company_index()
{
    return i_have_company() ? session()->index_company : '';
}

/**
 * Checks if current user is staff. Works for user and shipper staff.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [27.10.2021]
 * Reason: refactor method name
 *
 * @return bool
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/p.-Account-related-helpers#is_user_staff
 */
function is_user_staff()
{
    return user_type('shipper_staff') || user_type('users_staff');
}

/**
 * Checks if current user type is privileged and has rights (if rights are sent as parameter).
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [27.10.2021]
 * Reason: refactor method name
 *
 * @param string $type   - type of user (user, company or shipper_company)
 * @param int    $idType - id of the user (or id of the company)
 * @param string $rights - (optional)
 *
 * @return bool
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/d.-Checks-and-permissions#is_privileged
 */
function is_privileged($type, $idType, $rights = false)
{
    switch ($type) {
        case 'user':
            $result = is_my($idType) || $idType == privileged_user_id();

        break;
        case 'company':
            $result = in_session('companies', $idType);

        break;
        case 'shipper_company':
            $result = session()->shipper_id_company == $idType;

        break;

        default:
            return false;

        break;
    }

    return $result && is_string($rights) ? have_right($rights) : $result;
}

/**
 * Returns the privileged user id. If current user is staff then returns the main user id.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [27.10.2021]
 * Reason: refactor method name
 *
 * @return int
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/p.-Account-related-helpers#privileged_user_id
 */
function privileged_user_id()
{
    switch (session()->user_type) {
        case 'users_staff':
            return my_seller();

        break;
        case 'shipper_staff':
            return my_shipper();

        break;
    }

    return id_session();
}

if (!function_exists('principal_id')) {
    /**
     * Return the principal id of the current user.
     *
     * @author Bendiucov Tatiana
     *
     * @todo Refactoring [27.10.2021]
     * Reason: refactor method name
     *
     * @return null|int - id or null if not set (legacy)
     *
     * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/p.-Account-related-helpers#principal_id
     */
    function principal_id(): ?int
    {
        return (int) session()->id_principal ?: null;
    }
}

/**
 * Returns company name of the current user if he has company.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [27.10.2021]
 * Reason: refactor method name
 *
 * @return string - the company name
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/p.-Account-related-helpers#my_company_name
 */
function my_company_name()
{
    return i_have_company() ? session()->name_company : '';
}

/**
 * Returns company name of the current user if he has shipper company.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [27.10.2021]
 * Reason: refactor method name
 *
 * @return string - the company name
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/p.-Account-related-helpers#my_shipper_company_name
 */
function my_shipper_company_name()
{
    return i_have_shipper_company() ? session()->shipper_name_company : '';
}

/**
 * Returns ids of the users current user follows.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [27.10.2021]
 * Reason: refactor method name
 *
 * @return array
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/p.-Account-related-helpers#i_follow
 */
function i_follow()
{
    return session()->followed;
}

/*
* @author Bendiucov Tatiana
* @todo Remove [27.10.2021]
* Reason: Not used
*/
/*function validParams($type, $required = false, $aditional = array()){
    $class = "validate[";
    $params = $aditional;

    if($required) {
        $params[] = "required";
    }

    switch($type){
        // case 1:
        // 	$params[] = 'custom[onlyLetterNumber]';
        // break;
        case 2:
            $params[] = "custom[onlyLetterSp]";
        break;
        case 3:
            $params[] = "custom[onlyNumberSp]";
        break;
    }

    $class .= implode(",", $params) . "]";
    return $class;
}*/

/**
 * Returns how many percent it is substracting (minuses from total).
 *
 * @param float|int $numb    - the total number
 * @param float|int $percent - the percent to substract
 *
 * @return float|int - the total - percent
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/h.-Numbers-and-Money#minusPercent
 */
function minusPercent($numb, $percent)
{
    return $numb - ($numb * $percent / 100);
}

/**
 * Get the percent from number.
 *
 * @param float|int $from_number - total
 * @param float|int $number      - percent
 *
 * @return float|int calculated percent
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/h.-Numbers-and-Money#calculate_percent
 */
function calculate_percent($from_number, $number)
{
    return normalize_discount(($number * 100 / $from_number));
}

/**
 * Normalize discount. If the number has more than 2 digits after the comma then
 * it will get round up and then return with only 2 digits after.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [27.10.2021]
 * Reason: refactor method name
 *
 * @param float|int $number - the number to normalize
 *
 * @return float|int normalized percent
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/h.-Numbers-and-Money#normalize_discount
 */
function normalize_discount($number = 0)
{
    $temp_number = ceil($number * 100);
    $temp_number = (int) $temp_number;

    return $temp_number / 100;
}

/**
 * Get the current url from the tinymvc uri object.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [27.10.2021]
 * Reason: refactor method name
 *
 * @return string - the url
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/c.-Urls,-Http,-Domains#current_url
 */
function current_url()
{
    return tmvc::instance()->controller->uri->current_url();
}

/**
 * Sends the json response.
 *
 * @deprecated [27.10.2021]
 * @see json()
 *
 * @uses json()
 *
 * @param mixed $message
 * @param mixed $type
 * @param mixed $additional_params
 */
// function jsonResponse($message = "", $type = "error", $data = [], int $status = 200)
// {
//     $response = new LegacyJsonResponse($message, $type, $data, $status);
//     $response->send();
//     exit;
// }

/**
 * Response in json format (for system messages).
 *
 * @param string $message           - the message to return
 * @param string $type              - error or success or info or warning
 * @param array  $additional_params - parameters sent
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/q.-Output#jsonResponse
 */
function jsonResponse($message = '', $type = 'error', $additional_params = [])
{
    $resp = $additional_params;
    $resp['mess_type'] = $type;
    $resp['message'] = $message;

    exit(json_encode($resp));
}

/**
 * Response in json format for datatables data.
 *
 * @param string $message           - the message to return
 * @param array  $additional_params - parameters sent
 * @param string $type              - error or success or info or warning
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/q.-Output#jsonDTResponse
 */
function jsonDTResponse($message = '', $additional_params = [], $type = 'error')
{
    $output = [
        'iTotalRecords'        => 0,
        'iTotalDisplayRecords' => 0,
        'aaData'               => [],
    ];

    $output = array_merge($output, is_array($additional_params) ? $additional_params : [$additional_params]);
    $output['mess_type'] = $type;
    $output['message'] = $message;

    exit(json_encode($output));
}

/**
 * Used to return system message in modal (like jsonResponse for modals).
 *
 * @param string $message - the message
 * @param string $type    - errors or success or info or warning
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/q.-Output#messageInModal
 */
function messageInModal($message, $type = 'errors')
{
    views()->display('new/messages_view', [
        'message'=> $message,
        'type'   => $type,
    ]);

    exit();
}

/**
 * Redirects the user to another link.
 *
 * @param string     $link     - link to redirect to
 * @param int|string $httpCode - the code to redirect with (301 available only for now)
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/c.-Urls,-Http,-Domains#headerRedirect
 */
function headerRedirect($link = __SITE_URL, $httpCode = '')
{
    $currentUrl = uri()->current_url();
    $currentUrlNoGet = explode('?', $currentUrl);
    $currentUrlNoGet = reset($currentUrlNoGet);

    // $referer_components = explode('/', $currentUrlNoGet);
    // $replace_uri_string = isset(tmvc::instance()->site_urls['login/index']['replace_uri_string'])?tmvc::instance()->site_urls['login/index']['replace_uri_string']:null;
    // if(!in_array($replace_uri_string, $referer_components) && !in_array('login', $referer_components)){
    // 	session()->__set('REAL_REFERER', $currentUrl);
    // }

    switch ($httpCode) {
        case 301:
            header('HTTP/1.1 301 Moved Permanently');

        break;
    }

    header('Location: ' . $link);

    exit();
}

/**
 * Shows the view of comming soon functionality.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [27.10.2021]
 * Reason: refactor method name
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/q.-Output#show_comming_soon
 */
function show_comming_soon()
{
    header('HTTP/1.1 501 Not Implemented');

    views()->assign([
        'meta_data' => ['comming_soon_page' => 'Comming soon!!!'],
        'content'   => 'new/comming_soon_view',
    ]);
    views()->display('new/error_template_view');

    exit();
}

/**
 * Shows the view that something is wrong.
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/q.-Output#showOopsSomethingWrong
 *
 * @param bool $isLegacy
 */
function showOopsSomethingWrong($isLegacy = true)
{
    header('HTTP/1.1 404 Not Found');

    views()->assign([
        'meta_data' => ['error_page' => 'Oops'],
        'content'   => 'new/errors/oops_something_wrong_view',
    ]);
    views()->display('new/error_template_view');

    exit();
}

/**
 * Shows the view of 404.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [27.10.2021]
 * Reason: refactor method name
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/q.-Output#show_404
 */
function show_404()
{
    header('HTTP/1.1 404 Not Found');

    views()->assign([
        'meta_data'  => ['error_page' => '404'],
        'referrer'   => request()->headers->get('referer') ?? '/',
        'content'    => 'new/errors/404_view',
    ]);
    views()->display('new/error_template_view');

    exit();
}

/**
 * Shows the view of 403.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [27.10.2021]
 * Reason: refactor method name
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/q.-Output#show_403
 */
function show_403()
{
    header('HTTP/1.1 403 Forbidden');
    views()->assign([
        'meta_data'  => ['error_page' => '403'],
        'content'    => 'new/errors/403_view',
        'referrer'   => request()->headers->get('referer') ?? '/',
        'is_blocked' => false,
    ]);
    views()->display('new/error_template_view');

    exit();
}

/**
 * Shows the view of 403.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [27.10.2021]
 * Reason: refactor method name
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/q.-Output#show_blocked
 */
function show_blocked()
{
    if (have_right('view_blocked_page')) {
        session()->setMessages('This page is blocked for site visitors.', 'warning');

        return;
    }

    header('HTTP/1.1 403 Forbidden');

    views()->assign([
        'meta_data'  => ['error_page' => '403'],
        'content'    => 'new/errors/403_view',
        'referrer'   => request()->headers->get('referer') ?? '/',
        'is_blocked' => true,
    ]);
    views()->display('new/error_template_view');

    exit();
}

/**
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [27.10.2021]
 * Reason: refactor method name
 * Reason: Code style and functionality
 *
 * @param mixed $check_url
 */
function not_allowed_url($check_url = '')
{
    if (empty($check_url)) {
        return false;
    }

    $dir = TMVC_BASEDIR . 'myapp/controllers';
    $files = scandir($dir);

    foreach ($files as $file) {
        if ('.' != $file && '..' != $file) {
            $path_parts = pathinfo($dir . '/' . $file);
            $not_allowed_by_name[] = strtolower($path_parts['filename']);
        }
    }

    $not_allowed_by_routing = [
        'usr',
        'export_import',
        'buying',
        'selling',
        'category',
        'topics',
        'item',
        'event',
        'search',
        'maincategories',
        'seller',
        'snapshot',
        'branch',
        'shipper',
        'pre-registration',
        'register',
        'blocked',
        'documentation',
        'error',
    ];

    $not_allowed_url = array_merge($not_allowed_by_name, $not_allowed_by_routing);

    return in_array($check_url, $not_allowed_url);
}

/**
 * Checks if uri sent as a parameter is in the list of available uri segments (also sent as parameter). If not returns 404 page view.
 *
 * @param array $uriAssoc             - array of uris to check
 * @param array $availableUriSegments - array with values to check by
 *
 * @return mixed
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/d.-Checks-and-permissions#checkuri
 */
function checkURI($uriAssoc = [], $availableUriSegments = [])
{
    if (empty($uriAssoc) && empty($availableUriSegments)) {
        return true;
    }

    foreach ($uriAssoc as $keyUri => $valueUri) {
        if (!in_array($keyUri, $availableUriSegments)) {
            show_404();
        }

        if (empty($valueUri)) {
            show_404();
        }
    }
}

/**
 * Checks if page number is valid - is not empty and is numeric pozitive. Else shows 404.
 *
 * @param int $page - page number
 *
 * @return mixed - true if valid, 404 if not
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/d.-Checks-and-permissions#checkIsValidPage
 */
function checkIsValidPage($page)
{
    if (null === $page) {
        return true;
    }

    if (!is_numeric($page) || (int) $page <= 0) {
        show_404();
    }

    return true;
}

/**
 * Checks if the number is numeric and positive.
 *
 * @param mixed $number - the number to check
 *
 * @return bool
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/h.-Numbers-and-Money
 */
function isPositiveNumber($number)
{
    return is_numeric($number) && (int) $number >= 0;
}

/**
 * Returns the number as a string using the id of the order (or bill) filled with zero to fit the length and a hash before.
 *
 * @param int $id     - id of the order (bill)
 * @param int $length - the length of the string (default 11)
 *
 * @return string - the order number with hash
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/h.-Numbers-and-Money#orderNumber
 */
function orderNumber($id, $length = 11)
{
    return '#' . orderNumberOnly($id, $length);
}

/**
 * The reverse of the orderNumber method. It returns the number without the hash and the extra zeros.
 *
 * @param string $orderNumber - the number with #
 *
 * @return int - the number as a integer
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/h.-Numbers-and-Money#orderNumberToId
 */
function orderNumberToId(string $orderNumber)
{
    return (int) substr($orderNumber, 1);
}

/**
 * Returns the number filled with zeroes. Used mostly for orders and bills ids.
 *
 * @param int $id     - id of the order (bill)
 * @param int $length - the length of the string (default 11)
 *
 * @return string - the number filled with zeros
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/h.-Numbers-and-Money#orderNumberOnly
 */
function orderNumberOnly($id, $length = 11)
{
    return str_pad($id, $length, '0', STR_PAD_LEFT);
}

/**
 * Checks if the current user has the right for some action and redirects if not.
 *
 * @param string $action - the action
 *
 * @return bool or redirect
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/d.-Checks-and-permissions#checkAdmin
 */
function checkAdmin($action)
{
    if (!logged_in()) {
        session()->setMessages(translate('systmess_error_should_be_logged'), 'errors');
        headerRedirect(__SITE_URL . 'login');
    }

    if (!have_right_or($action)) {
        session()->setMessages(translate('systmess_error_rights_perform_this_action'), 'errors');
        headerRedirect(__SITE_URL);
    }

    return true;
}

/**
 * Checks if current user has the right for some action, and returns the response as json if not.
 *
 * @param string $action - the action
 *
 * @return bool or json
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/d.-Checks-and-permissions#checkAdminAjax
 */
function checkAdminAjax($action)
{
    if (!logged_in()) {
        jsonResponse(translate('systmess_error_should_be_logged'));
    }

    if (!have_right_or($action)) {
        jsonResponse(translate('systmess_error_rights_perform_this_action'));
    }

    return true;
}

/**
 * Checks if current user has the right for some action, and returns the response as message in modal if not.
 *
 * @param string $action - the action
 *
 * @return bool or view (message in modal)
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/d.-Checks-and-permissions#checkAdminAjaxModal
 */
function checkAdminAjaxModal($action)
{
    if (!logged_in()) {
        messageInModal(translate('systmess_error_should_be_logged'));
    }

    if (!have_right_or($action)) {
        messageInModal(translate('systmess_error_rights_perform_this_action'));
    }

    return true;
}

/**
 * Checks if current user has the right for some action, and returns the response as json for datatables.
 *
 * @param string $action - the action
 *
 * @return bool or json for datatables
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/d.-Checks-and-permissions#checkAdminAjaxDT
 */
function checkAdminAjaxDT($action)
{
    if (!isAjaxRequest()) {
        headerRedirect();
    }

    if (!logged_in()) {
        jsonDTResponse(translate('systmess_error_should_be_logged'));
    }

    if (!have_right_or($action)) {
        jsonDTResponse(translate('systmess_error_rights_perform_this_action'));
    }

    return true;
}

/**
 * Checks if current user has the right for some action, and redirects if not.
 *
 * @param string $action - the action
 * @param string $path   - link to redirect to
 *
 * @return bool or header redirect
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/d.-Checks-and-permissions#checkPermision
 */
function checkPermision($action, $path = __SITE_URL)
{
    checkIsLogged();

    if (!have_right_or($action)) {
        session()->setMessages(translate('systmess_error_rights_perform_this_action'), 'errors');
        headerRedirect($path);
    }

    return true;
}

/**
 * Checks if current user has the right for some action, and returns json if not.
 *
 * @param string $action - the action
 *
 * @return bool or json
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/d.-Checks-and-permissions#checkPermisionAjax
 */
function checkPermisionAjax($action)
{
    if (!logged_in()) {
        jsonResponse(translate('systmess_error_should_be_logged_in'));
    }

    if (!have_right_or($action)) {
        jsonResponse(translate('systmess_error_permission_not_granted'));
    }

    return true;
}

/**
 * Checks if current user has the right for some action, and returns view for modal.
 *
 * @param string $action - the action
 *
 * @return bool or view
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/d.-Checks-and-permissions#checkPermisionAjaxModal
 */
function checkPermisionAjaxModal($action)
{
    if (!logged_in()) {
        messageInModal(translate('systmess_error_should_be_logged_in'));
    }

    if (!have_right_or($action)) {
        messageInModal(translate('systmess_error_permission_not_granted'));
    }

    return true;
}

/**
 * Checks if current user has the right for some action, and returns json for datatable.
 *
 * @param string $action - the action
 *
 * @return bool or view
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/d.-Checks-and-permissions#checkPermisionAjaxDT
 */
function checkPermisionAjaxDT($action)
{
    if (!logged_in()) {
        jsonDTResponse(translate('systmess_error_should_be_logged_in'));
    }

    if (!have_right_or($action)) {
        jsonDTResponse(translate('systmess_error_permission_not_granted'));
    }

    return true;
}

/**
 * Checks if group has expired and returns response based on $actionType (null for redirect, ajax, modal or dt).
 *
 * @param string $actionType - the action type
 *
 * @return mixed
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/d.-Checks-and-permissions#checkGroupExpire
 */
function checkGroupExpire($actionType = null)
{
    if (group_expired_session()) {
        $message = session()->paid_price > 0 ? translate('system_message_certification_has_expired') : translate('system_message_free_certification_has_expired');

        switch ($actionType) {
            default:
                session()->setMessages($message, 'warning');
                headerRedirect(__SITE_URL . 'upgrade');

            break;
            case 'ajax':
                jsonResponse($message, 'warning');

            break;
            case 'modal':
                messageInModal($message, 'warning');

            break;
            case 'dt':
                jsonDTResponse($message, [], 'warning');

            break;
        }
    }
}

/**
 * Downloads the file.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [28.10.2021]
 * Reason: refactor method name
 *
 * @param string $file     - path to file
 * @param string $fileName - the name of the downloaded file
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/a.-FileSystem#file_force_download
 */
function file_force_download($file, $fileName = '')
{
    if (file_exists($file)) {
        if (ob_get_level()) {
            ob_end_clean();
        }

        if ('' == $fileName) {
            $fileName = basename($file);
        } else {
            $pathParts = pathinfo($file);
            $fileName .= '.' . $pathParts['extension'];
        }

        header('Content-Description: File Transfer');
        header('Content-Type: ' . mimeContentType($file));
        header('Content-Disposition: attachment; filename=' . $fileName);
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));

        readfile($file);

        exit;
    }
}

/**
 * Returns the url of the current user's company.
 *
 * @param bool $useSiteUrl - absolute or relative url
 *
 * @return string - url
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/p.-Account-related-helpers#getMyCompanyURL
 */
function getMyCompanyURL($useSiteUrl = true)
{
    if (i_have_company()) {
        $companyIndexName = my_company_index();
        $companyName = my_company_name();
        $companyId = my_company_id();

        if (!empty($companyIndexName)) {
            return ($useSiteUrl ? __SITE_URL : '') . $companyIndexName;
        }

        return ($useSiteUrl ? __SITE_URL : '') . 'seller/' . strForURL($companyName . ' ' . $companyId);
    }

    return $useSiteUrl ? __SITE_URL : '';
}

/**
 * @author Bendiucov Tatiana
 *
 * @todo Remove [28.10.2021]
 * Reason: Not used
 *
 * @param mixed $array
 * @param mixed $includeSiteUrl
 */
/*function getCompanyURI($array)
{
    if (!empty($array["index_name"])) {
        return $array["index_name"];
    }

    return strForURL($array["name_company"]) . "-" . $array["id_company"];
}
*/

/**
 * Returns the url of company url based on the data of the company sent as the first parameter.
 *
 * @param array $array          - the data about company needed to create the url
 * @param bool  $includeSiteUrl - true by default, to return with site url (absolute url)
 *
 * @return string - the url
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/p.-Account-related-helpers#getCompanyURL
 */
function getCompanyURL($array, $includeSiteUrl = true)
{
    $prefixUrl = $includeSiteUrl ? __SITE_URL : '';

    if (!empty($array['index_name'])) {
        return $prefixUrl . $array['index_name'];
    }

    if ('company' == $array['type_company']) {
        return $prefixUrl . 'seller/' . strForURL($array['name_company']) . '-' . $array['id_company'];
    }

    return $prefixUrl . 'branch/' . strForURL($array['name_company']) . '-' . $array['id_company'];
}

/**
 * Returns the shipper page url.
 *
 * @param array $array - the data need to create the shipper url (co_name and id)
 *
 * @return string url
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/p.-Account-related-helpers#getShipperURL
 */
function getShipperURL($array)
{
    return __SITE_URL . 'shipper/' . strForURL($array['co_name']) . '-' . $array['id'];
}

/**
 * Returns the url of company url based on the data of the company sent as the first parameter. The difference between this and getCompanyURL
 * method is that this one uses the partner id.
 *
 * @param array $array - the data about company needed to create the url
 *
 * @return string - the url
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/p.-Account-related-helpers#getCompanyPartnerURL
 */
function getCompanyPartnerURL($array)
{
    if (!empty($array['index_name'])) {
        return __SITE_URL . $array['index_name'];
    }

    if(is_a($array['type_company'], CompanyType::class)){
        if(CompanyType::COMPANY === $array['type_company']->value){
            return __SITE_URL . 'seller/' . strForURL($array['name_company']) . '-' . $array['id_company'];
        }
    }elseif ('company' == $array['type_company']) {
        return __SITE_URL . 'seller/' . strForURL($array['name_company']) . '-' . $array['id_company'];
    }

    return __SITE_URL . 'branch/' . strForURL($array['name_company']) . '-' . $array['id_company'];
}

/**
 * @author Bendiucov Tatiana
 *
 * @todo Remove [28.10.2021]
 * Reason: Not used
 *
 * @param mixed $params
 * @param mixed $path
 */
/*function card_valid_date($month,$year){
    if($year < date("y")) {
        return false;
    }

    if($month < date("n") || $month > 12) {
        return false;
    }
}*/

/**
 * Generates and saves the google map image based on parameters sent.
 *
 * @param array  $params array with parameters
 * @param string $path   the path to where to save image
 *
 * @return string the name of the iamge
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/b.-Images#generateGoogleMapImage
 *
 * @author Anton Zencenco
 * @todo Remove `[07.02.2022]` See the reason for deprecation
 * @deprecated `v2.32 [07.02.2022]` This function must be removed for two reasons:
 *     - map image is not used anymore
 *     - the map cannot be generated anymore due to Google Maps API restrictions
 */
function generateGoogleMapImage($params, $path)
{
    $url = 'https://maps.googleapis.com/maps/api/staticmap?';
    $mapParams = [];
    $zoom = 13;
    $size = [575, 300];

    extract($params);
    $mapParams[] = "zoom={$zoom}";
    $mapParams[] = "size={$size}";

    if (isset($marker)) {
        $mapParams[] = "markers=color:red%7Clabel:C%7C{$marker['lat']},{$marker['lng']}";
    }

    if (isset($marker_address)) {
        $mapParams[] = "markers=color:red%7Clabel:C%7C{$marker_address}";
    }

    if (isset($coords)) {
        $mapParams[] = "center={$coords['lat']},{$coords['lng']}";
    }

    if (isset($address)) {
        $mapParams[] = "center={$address}";
    }

    $url .= implode('&', $mapParams);
    $url = str_replace(' ', '+', $url);

    if (!is_dir($path)) {
        mkdir($path, 0755, true);
    }

    $mapName = 'map_' . time() . '.png';

    file_put_contents($path . '/' . $mapName, file_get_contents($url));

    return $mapName;
}

/**
 * Return a random string.
 *
 * @param int $length - the length of the string
 *
 * @return string
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/g.-Strings-and-text#genRandStr
 */
function genRandStr($length = 16)
{
    $chars = 'abcdefghijklmnopqrstuvwxyz1234567890'; //length:36
    $finalRand = '';

    for ($i = 0; $i < $length; ++$i) {
        $finalRand .= $chars[rand(0, strlen($chars) - 1)];
    }

    return $finalRand;
}

/**
 * Generates a random password for users created in CR.
 *
 * @param int    $length        - the length of the password
 * @param bool   $addDashes     - to add dashes or not
 * @param string $availableSets (Letters Upercase Decimal Symbols)
 *
 * @return string password
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/g.-Strings-and-text
 */
function generateRandomPassword($length = 10, $addDashes = true, $availableSets = 'luds')
{
    $sets = [];
    if (false !== strpos($availableSets, 'l')) {
        $sets[] = 'abcdefghjkmnpqrstuvwxyz';
    }

    if (false !== strpos($availableSets, 'u')) {
        $sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
    }

    if (false !== strpos($availableSets, 'd')) {
        $sets[] = '123456789';
    }

    if (false !== strpos($availableSets, 's')) {
        $sets[] = '!@#$%&*?';
    }

    $all = '';
    $password = '';
    foreach ($sets as $set) {
        $password .= $set[array_rand(str_split($set))];
        $all .= $set;
    }

    $all = str_split($all);
    for ($i = 0; $i < $length - count($sets); ++$i) {
        $password .= $all[array_rand($all)];
    }

    $password = str_shuffle($password);
    if (!$addDashes) {
        return $password;
    }

    $dashLen = floor(sqrt($length));
    $dashStr = '';
    while (strlen($password) > $dashLen) {
        $dashStr .= substr($password, 0, $dashLen) . '-';
        $password = substr($password, $dashLen);
    }

    $dashStr .= $password;

    return $dashStr;
}

/**
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [26.10.2021]
 * Reason: Code style
 * Reason: refactor method name
 *
 * @param mixed $func
 * @param mixed $mess
 */
function get_function($func, $mess)
{
    switch ($func) {
        case 'setMessages':
            $tmvc = tmvc::instance();
            $tmvc->controller->session->setMessages($mess);

        break;
        case 'jsonResponse':
            jsonResponse($mess);

        break;
    }
}

/**
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [26.10.2021]
 * Reason: Code style
 * Reason: refactor method name
 *
 * @param mixed $operation
 * @param mixed $function
 */
function is_allowed($operation, $function = 'jsonResponse')
{
    if (!config('env.ENABLE_FUNCTION_IS_ALLOWED', true)) {
        return;
    }

    $tmvc = tmvc::instance();
    if ($tmvc->controller->session->is_muted) {
        get_function($function, translate('systmess_error_account_is_muted'));
    }

    $freq = $tmvc->my_config[$operation];
    $cur_time = time();
    $last_oper_time = $tmvc->controller->session->isset_operation($operation);

    if ((false === $last_oper_time) || (($cur_time - $last_oper_time) > $freq)) {
        $tmvc->controller->session->set_operation_time($operation);

        return true;
    }

    $max = $tmvc->my_config['max_operations_per_sess'];

    $cur_count = $tmvc->controller->session->incr_count();

    if ($cur_count < floor($max * 0.9)) {
        get_function($function, translate('systmess_error_too_many_operations'));
    }

    if ($cur_count == floor($max * 0.9)) {
        get_function($function, translate('systmess_warning_punishment_for_too_many_operations'));
    }

    if ($cur_count < $max) {
        get_function($function, translate('systmess_error_too_many_operations'));
    }

    $id_user = $tmvc->controller->session->id;

    $data_systmess = [
        'mess_code' => 'user_muted',
        'id_users'  => [$id_user],
        'systmess'  => true,
    ];

    $tmvc->controller->load->model('Notify_Model', 'notify');
    $tmvc->controller->load->model('User_Model', 'user');

    //send system message
    $tmvc->controller->notify->send_notify($data_systmess);

    //mute this user
    $tmvc->controller->session->is_muted = 1;
    $tmvc->controller->user->updateUserMain($id_user, ['is_muted' => 1]);

    // You are talking too much, mister Anderson
    container()->get(MessengerInterface::class)->bus('event.bus')->dispatch(new UserWasMutedEvent((int) $id_user));

    //add notice for admin
    $notice = [
        'add_date' => date('Y/m/d H:i:s'),
        'add_by'   => 'System',
        'notice'   => 'User is muted',
    ];

    $tmvc->controller->user->set_notice($id_user, $notice);

    get_function($function, translate('systmess_notice_about_temporarily_suspending_account'));
}

/**
 * Get the price in the right format.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [27.10.2021]
 * Reason: refactor method name
 *
 * @param mixed $price              - the price to convert
 * @param bool  $showCurrencySymbol - show or not the currency symbol
 * @param string default
 * @param mixed $default
 *
 * @return string - the formatted price
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/h.-Numbers-and-Money#get_price
 */
function get_price($price = 0, $showCurrencySymbol = true, $default = '0.00')
{
    $currencies = new ISOCurrencies();
    $currencyFormatter = new AggregateMoneyFormatter([
        'USD' => new IntlMoneyFormatter($usdFormatter = new \NumberFormatter('en_US', \NumberFormatter::CURRENCY), $currencies),
        'GBP' => new IntlMoneyFormatter(new \NumberFormatter('en_GB', \NumberFormatter::CURRENCY), $currencies),
        'EUR' => new IntlMoneyFormatter(new \NumberFormatter('nl_NL', \NumberFormatter::CURRENCY), $currencies),
    ]);

    $amount = $price;
    if (!$price instanceof Money) {
        $amount = priceToUsdMoney($price);
        if (null === $amount) {
            return value($default);
        }
    }

    if ($showCurrencySymbol) {
        $currencyCode = cookies()->cookieArray['currency_key'];
        $currencyValueMultiplier = cookies()->cookieArray['currency_value'];
        $currencyConverter = new Converter($currencies, new FixedExchange([
            'USD' => [$currencyCode => $currencyValueMultiplier],
        ]));

        return $currencyFormatter->format($currencyConverter->convert($amount, new Currency($currencyCode)));
    }

    return trim(trim($currencyFormatter->format($amount), $usdFormatter->getSymbol(\NumberFormatter::CURRENCY_SYMBOL)));
}
/**
 * @author Bendiucov Tatiana
 *
 * @todo Remove [28.10.2021]
 * Reason: Not used
 *
 * @param mixed $time_plus
 * @param mixed $time_type
 * @param mixed $from_date
 * @param mixed $weekend
 */
/*function get_currency_price_to_usd($fprice, $currency = '', $currency_symbol=false){
    if($currency == ""){
        return false;
    }

    switch ($currency) {
        case "USD":
            if($currency_symbol){
                return "$ ".number_format($fprice, 2, ".", ",");
            } else{
                return number_format($fprice, 2, ".", ",");
            }
        break;
        case "RMB":
            $exchange_rates = json_decode(file_get_contents("current_exchange_rate.json"), true);

            if($currency_symbol){
                return "$ ".number_format($fprice / $exchange_rates["CNY"], 2, ".", ",");
            } else{
                return number_format($fprice / $exchange_rates["CNY"], 2, ".", ",");
            }
        break;
        default:
            $exchange_rates = json_decode(file_get_contents("current_exchange_rate.json"), true);

            if($currency_symbol){
                return "$ ".number_format($fprice / $exchange_rates[$currency], 2, ".", ",");
            } else{
                return number_format($fprice / $exchange_rates[$currency], 2, ".", ",");
            }
        break;
    }
}*/

/**
 * @author Bendiucov Tatiana
 *
 * @deprecated [28.10.2021]
 * Reason: Better alternatives
 * @see use DateTime object and the add method and DateInterval to add how much time you need.
 * Ex:
 * $date = new DateTime('2000-01-01');
 * $date->add(new DateInterval('P10D'));
 */
function date_plus($time_plus, $time_type = 'days', $from_date = false, $weekend = false)
{
    if (!$from_date) {
        $from_date = date('Y-m-d H:i:s');
    }

    $result_date = $from_date;
    switch ($time_type) {
        default:
        case 'days':
            if ($weekend) {
                if ($time_plus > 0) {
                    for ($i = 1; $i <= $time_plus; ++$i) {
                        $result_date = date('Y-m-d H:i:s', strtotime($result_date . '+ 1days'));
                        if (isWeekend($result_date)) {
                            ++$time_plus;
                        }
                    }
                } elseif ($time_plus < 0) {
                    $time_plus = $time_plus * (-1);
                    for ($i = 1; $i <= $time_plus; ++$i) {
                        $result_date = date('Y-m-d H:i:s', strtotime($result_date . '- 1days'));
                        if (isWeekend($result_date)) {
                            ++$time_plus;
                        }
                    }
                }
            } else {
                if ($time_plus > 0) {
                    $result_date = date('Y-m-d H:i:s', strtotime($result_date . '+ ' . $time_plus . 'days'));
                } elseif ($time_plus < 0) {
                    $result_date = date('Y-m-d H:i:s', strtotime($result_date . '- ' . $time_plus . 'days'));
                }
            }

        break;
    }

    return $result_date;
}

/**
 * Checks if current date is a weekend day.
 *
 * @param string $date - the date to check
 *
 * @return bool
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/i.-Date-and-Time#isWeekend
 */
function isWeekend($date)
{
    $weekDay = date('w', strtotime($date));

    return 0 == $weekDay || 6 == $weekDay;
}

/**
 * @author Bendiucov Tatiana
 *
 * @deprecated [28.10.2021]
 * Reason: Better alternatives
 * @see use DateTime object and the add method diff to see the difference
 * Ex:
 * $origin = new DateTime('2009-10-11');
 * $target = new DateTime('2009-10-13');
 * $interval = $origin->diff($target);
 * echo $interval->format('%R%a дней');
 *
 * @param mixed $date_from
 * @param mixed $date_to
 * @param mixed $format
 */
function date_difference($date_from, $date_to, $format = 'days')
{
    $datetime_from = strtotime($date_from);
    $datetime_to = strtotime($date_to);
    $total_sec = $datetime_from - $datetime_to;
    switch ($format) {
        case 'days':
        default:
            return ceil($total_sec / 86400);

        break;
    }
}

/**
 * Checks if date is expired.
 *
 * @param string $date   - the date
 * @param string $format - the format of the sent date
 *
 * @return bool
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/i.-Date-and-Time#isDateExpired
 */
function isDateExpired($date, $format = 'Y-m-d')
{
    return \DateTime::createFromFormat($format, $date) < new \DateTime();
}

/**
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [28.10.2021]
 * Reason: Code style
 * Reason: refactor method name
 *
 * @param mixed $params
 */
function get_pagination_html($params)
{
    $parent_element = 'div';
    $children_element = 'a';
    $parent_element_classes = 'pagination';
    $children_element_classes = '';
    $active_page_class = 'active';
    $visible_pages = 5;
    $data = [];
    $parts = [];
    $start_page = 1;
    $dots = true;
    $dots_text = ['first' => '&hellip;', 'last' => '&hellip;'];
    $prev_next = true;
    $prev_next_text = ['prev' => translate('pagination_link_prev'), 'next' => translate('pagination_link_next')];
    $first_last = true;
    $first_last_text = ['first' => translate('pagination_link_first'), 'last' => translate('pagination_link_last')];
    $implode = '';
    extract($params);
    if (!$count_total) {
        return '';
    }

    if (!empty($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = ' data-' . $key . "='" . $value . "' ";
        }
    }

    $finish_page = ceil($count_total / $per_page);

    $first = '';
    $last = '';
    $prev = '';
    $next = '';
    $dots_on_start = '';
    $dots_on_end = '';

    if ($visible_pages) {
        $interval = ($visible_pages - 1) / 2;
        $start_page = $cur_page - $interval;
        $additional = 0;
        if ($start_page < 1) {
            $additional = 1 - $start_page;
            $start_page = 1;
        } elseif ($start_page > 1 && $dots) {
            $dots_on_start = '<span class="' . $children_element_classes . '">' . $dots_text['first'] . '</span>';
        }

        if ($first_last) {
            if ($cur_page > 1) {
                $first = '<' . $children_element . ' class="' . $children_element_classes . '" ' . implode(' ', $data) . ' data-page="1">' . $first_last_text['first'] . '</' . $children_element . '>';
            } else {
                $first = '<span class="' . $children_element_classes . ' disabled" ' . implode(' ', $data) . ' data-page="1">' . $first_last_text['first'] . '</span>';
            }

            if ($cur_page < $finish_page) {
                $last = '<' . $children_element . ' class="' . $children_element_classes . '" ' . implode(' ', $data) . ' data-page="' . $finish_page . '">' . $first_last_text['last'] . '</' . $children_element . '>';
            } else {
                $last = '<span class="' . $children_element_classes . ' disabled" ' . implode(' ', $data) . ' data-page="' . $finish_page . '">' . $first_last_text['last'] . '</span>';
            }
        }

        if ($prev_next) {
            if ($cur_page > 1) {
                $prev = '<' . $children_element . ' class="' . $children_element_classes . '" data-page="' . ($cur_page - 1) . '" ' . implode(' ', $data) . '>' . $prev_next_text['prev'] . '</' . $children_element . '>';
            } else {
                $prev = '<span class="' . $children_element_classes . ' disabled" data-page="1" ' . implode(' ', $data) . '>' . $prev_next_text['prev'] . '</span>';
            }

            if ($cur_page < $finish_page) {
                $next = '<' . $children_element . ' class="' . $children_element_classes . '" data-page="' . ($cur_page + 1) . '" ' . implode(' ', $data) . '>' . $prev_next_text['next'] . '</' . $children_element . '>';
            } else {
                $next = '<span class="' . $children_element_classes . ' disabled" data-page="' . $finish_page . '" ' . implode(' ', $data) . '>' . $prev_next_text['next'] . '</span>';
            }
        }

        if ($dots && ($finish_page > ($interval + $cur_page))) {
            $dots_on_end = '<span class="' . $children_element_classes . '">' . $dots_text['last'] . '</span>';
        }

        $temp_finish_page = $cur_page + $interval + $additional;
        $additional = 0;
        if ($temp_finish_page > $finish_page) {
            $additional = abs($finish_page - $cur_page - $interval);
            $start_page = max(1, $start_page - $additional);
        } else {
            $finish_page = $temp_finish_page;
        }
    }

    $parts[] = '<' . $parent_element . ' class="' . $parent_element_classes . '">';
    $parts[] = $first;
    $parts[] = $prev;
    $parts[] = $dots_on_start;

    for ($i = $start_page; $i <= $finish_page; ++$i) {
        $parts[] = '<' . (($i == $cur_page) ? 'span' : $children_element) . ' class="' . $children_element_classes . (($cur_page == $i) ? ' ' . $active_page_class : '') . '" data-page="' . $i . '" ' . implode(' ', $data) . '>' . $i . '</' . (($i == $cur_page) ? 'span' : $children_element) . '>';
    }

    $parts[] = $dots_on_end;
    $parts[] = $next;
    $parts[] = $last;
    $parts[] = '</' . $parent_element . '>';

    return implode($implode, $parts);
}

/**
 * Returns the link to no photo thumb.
 *
 * @param int $idUserGroup - the id of the group
 * @param int $size        - the size of the thumb
 *
 * @return string
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/b.-Images#thumbNoPhoto
 */
function thumbNoPhoto($idUserGroup, $size = 80)
{
    switch ($idUserGroup) {
        case 3: case 2:
            $groupName = 'seller';

        break;
        case 5: case 6:
            $groupName = 'manufacturer';

        break;
        case 13:
            $groupName = 'order-manager';

        break;
        case 14:
            $groupName = 'admin';

        break;
        case 15:
            $groupName = 'user-manager';

        break;
        case 16:
            $groupName = 'super-admin';

        break;
        case 17:
            $groupName = 'support';

        break;
        case 18:
            $groupName = 'content-manager';

        break;
        case 19:
            $groupName = 'billing-manager';

        break;
        case 25:
            $groupName = 'company-staff-user';

        break;
        case 31:
        case 32:
            $groupName = 'shipper';

        break;

        default:
            $groupName = 'buyer';
    }

    $link = 'public/img/no_image/noimage-' . strForUrl($groupName) . '-' . $size . '.jpg';
    if (!file_exists($link)) {
        $link = 'public/img/no_image/noimage-buyer-' . $size . '.jpg';
    }

    return $link;
}

/**
 * Returns the class of the color coresponding the group name.
 *
 * @param string $group - the group name
 *
 * @return string
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/q.-Output#userGroupNameColor
 */
function userGroupNameColor($group)
{
    $certifiedGroup = ['Certified Seller', 'Certified Manufacturer'];

    return in_array($group, $certifiedGroup) ? ' txt-orange' : ' txt-green';
}

/**
 * Returns the class of the color coresponding the group id.
 *
 * @param int $group - id of the group
 *
 * @return string
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/q.-Output#userGroupColor
 */
function userGroupColor($group)
{
    $unverifiedGroup = [
        1  => 'txt-green', 	     // Buyer
        2  => 'txt-blue-light',  // Verified Seller
        3  => 'txt-blue2', 	     // Certified Seller
        5  => 'txt-blue-light',  // Verified Manufacturer
        6  => 'txt-blue2', 	     // Certified Manufacturer
        13 => 'txt-orange',      // Order Manager
        14 => 'txt-orange',      // Admin
        15 => 'txt-orange',      // User Manager
        16 => 'txt-orange',      // Super Admin
        17 => 'txt-orange',      // Support
        18 => 'txt-orange',      // Content Manager
        19 => 'txt-orange',      // Billing Manager
        25 => 'txt-green', 	     // Company staff user
        31 => 'txt-orange-dark', // Freight Forwarder
        32 => 'txt-orange-dark', // Freight Forwarder Staff User
    ];

    return $unverifiedGroup[$group];
}

/**
 * @author Bendiucov Tatiana
 *
 * @todo Remove [28.10.2021]
 * Reason: Not used
 *
 * @param mixed $number
 */
/*function blockUserGroup($group, $size = 25, $type = "half") {
    $groups = array(
        1 => array("text" => "B", "bg" => "green", "name" => "Buyer"),
        2 => array("text" => "VS", "bg" => "blue-light", "name" => "Verified Seller"),
        3 => array("text" => "CS", "bg" => "blue", "name" => "Certified Seller"),
        5 => array("text" => "VM", "bg" => "blue-light", "name" => "Verified Manufacturer"),
        6 => array("text" => "CM", "bg" => "blue", "name" => "Certified Manufacturer"),
        13 => array("text" => "OM", "bg" => "red", "name" => "Order Manager"),
        14 => array("text" => "A", "bg" => "red", "name" => "Admin"),
        15 => array("text" => "UM", "bg" => "red", "name" => "User Manager"),
        16 => array("text" => "SA", "bg" => "red", "name" => "Super Admin"),
        17 => array("text" => "S", "bg" => "red", "name" => "Support"),
        18 => array("text" => "CM", "bg" => "red", "name" => "Content Manager"),
        19 => array("text" => "BM", "bg" => "red", "name" => "Billing Manager"),
        25 => array("text" => "CS", "bg" => "green", "name" => "Company staff user"),
        31 => array("text" => "S", "bg" => "orange-dark", "name" => "Freight Forwarder"),
        32 => array("text" => "SS", "bg" => "orange-dark", "name" => "Freight Forwarder Staff User")
    );

    if($type == "half") {
        $name = $groups[$group]["text"];
    } else {
        $name = $groups[$group]["name"];
    }

    if(isset($groups[$group])){
        if($size != 25) {
            $group_block = '<div class="img-main-status img-main-status--'.$size.' bg-'.$groups[$group]['bg'].'" title="'.$groups[$group]['name'].'">'.$name.'</div>';
        } else {
            $group_block = '<div class="img-main-status bg-'.$groups[$group]['bg'].'" title="'.$groups[$group]['name'].'">'.$name.'</div>';
        }

        return $group_block;
    } else {
        return;
    }
}*/

/**
 * Returns the same number with a hash as prefix.
 *
 * @param int $number - the number
 *
 * @return string
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/h.-Numbers-and-Money#idNumber
 */
function idNumber($number)
{
    return '#' . $number;
}

/**
 * @author Bendiucov Tatiana
 *
 * @todo Remove [28.10.2021]
 * Reason: Not used
 *
 * @param mixed $str
 * @param mixed $maxLength
 */
/*function have_additional_info(){
    return (bool)tmvc::instance()->controller->session->country;
}*/

/**
 * @author Bendiucov Tatiana
 *
 * @todo Remove [28.10.2021]
 * Reason: Not used
 */
/*function array_to_uri($array){
    if(is_array($array)){
        foreach($array as $key => $value){
            if($key !== "main"){
                if(!empty($value)) {
                    $str[] = $key . "/" . cleanInput($value);
                }
            } else {
                $str[] = $value;
            }
        }
        return implode("/", $str) . "/";
    } else {
        return false;
    }
}*/

/**
 * @author Bendiucov Tatiana
 *
 * @todo Remove [28.10.2021]
 * Reason: Not used
 */
/*function generateRandomString($length = 10) {
    return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
}*/

/**
 * Cuts the string to the length indicated.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [28.10.2021]
 * Reason: refactor method name
 *
 * @param string $str       - the string to cut
 * @param int    $maxLength - the length to cut to
 *
 * @return string
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/g.-Strings-and-text#cut_str
 */
function cut_str($str = '', $maxLength = 50)
{
    $trimmedStr = trim($str);

    return mb_substr($trimmedStr, 0, $maxLength);
}

/**
 * Cuts the string to the length indicated and adds three dots if length bigger than maxLength.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [28.10.2021]
 * Reason: refactor method name
 *
 * @param string $str       - the string to cut
 * @param int    $maxLength - the length to cut to
 *
 * @return string
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/g.-Strings-and-text#cut_str_with_dots
 */
function cut_str_with_dots($str, $maxLength = 50)
{
    $trimmedStr = trim($str);

    return mb_strlen($trimmedStr) > $maxLength ? mb_substr($trimmedStr, 0, $maxLength) . '...' : $trimmedStr;
}

/**
 * Checks if date as string is a valid date.
 *
 * @param string $date   - the date
 * @param string $format - the format of the current date
 *
 * @return bool
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/i.-Date-and-Time#validateDate
 */
function validateDate($date, $format = 'Y-m-d H:i:s')
{
    if ($date instanceof DateTimeInterface) {
        return true;
    }

    $d = date_create_from_format($format, $date);

    return $d && date_format($d, $format) == $date;
}

/**
 * Returns the string or DateTime date from one format to another.
 *
 * @param mixed  $date         - the date
 * @param string $format       - the date format
 * @param string $returnFormat - the format to return
 *
 * @return null|string
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/i.-Date-and-Time#getDateFormat
 */
function getDateFormat($date, $format = 'Y-m-d H:i:s', $returnFormat = 'j M, Y H:i')
{
    if (null === $date) {
        return;
    }

    $format = null === $format ? 'Y-m-d H:i:s' : $format;
    if (!$date instanceof DateTimeInterface && !$date instanceof DateTime && !$date instanceof DateTimeImmutable) {
        $d = date_create_from_format($format, $date);
        if ($d && date_format($d, $format) == $date) {
            return $d->format($returnFormat);
        }

        return null;
    }

    return $date->format($returnFormat);
}

/**
 * Get date format just like getDateFormat method, but checks if not empty first. Accepts default parameter if empty.
 *
 * @param mixed  $date         - the date
 * @param string $format       - the date format
 * @param string $returnFormat - the format to return
 * @param string $default      - the default string to return if empty
 *
 * @return null|string
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/i.-Date-and-Time#getDateFormatIfNotEmpty
 */
function getDateFormatIfNotEmpty($date, $format = 'Y-m-d H:i:s', $returnFormat = 'j M, Y H:i', $default = '—')
{
    if (empty($date) || '0000-00-00 00:00:00' === $date) {
        return $default;
    }

    $formated = getDateFormat($date, $format, $returnFormat);
    if (!empty($formated)) {
        return $formated;
    }

    return $default;
}

/**
 * @author Bendiucov Tatiana
 *
 * @todo Remove [28.10.2021]
 * Reason: Not used
 *
 * @param mixed $blocked
 */
/*function cm2in($value){
    return $value*0.39370079;
}*/

/**
 * @author Bendiucov Tatiana
 *
 * @todo Remove [28.10.2021]
 * Reason: Not used
 */
/*function kg2oz($value){
    return $value*35.274;
}*/

/**
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [29.10.2021]
 * Reason: Code style
 * Reason: refactor method name
 */
function dashboard_nav_rights($blocked = false)
{
    if (!$blocked) {
        $items = [
            'dashboard' => [
                'params' => [
                    'right' => '',
                    'title' => 'Personal info',
                    'icon'  => 'user',
                ],
                'items' => [
                    'my_page' => [
                        'right'      => 'have_personal_page',
                        'title'      => 'My page',
                        'link'       => 'usr',
                        'icon'       => 'user',
                        'icon_color' => 'bg-blue',
                    ],
                    'customs_calculator' => [
                        'title'         => 'Customs calculator',
                        'link'          => 'https://customsdutyfree.com/duty-calculator',
                        'icon'          => 'customs-calculator',
                        'icon_color'    => 'bg-blue',
                        'external_link' => 'https://customsdutyfree.com/duty-calculator',
                        'add_class'     => 'js-customs-calculator',
                        'target'        => '_blank',
                    ],
                    'cr_user_guide' => [
                        'right'      => 'have_cr_personal_page',
                        'title'      => 'User guide',
                        'link'       => 'user_guide/ba',
                        'icon'       => 'book-search',
                        'icon_color' => 'bg-blue',
                    ],
                    'cr_my_page' => [
                        'right'      => 'have_cr_personal_page',
                        'title'      => 'My page',
                        'link'       => 'country_representative',
                        'icon'       => 'user-page',
                        'icon_color' => 'bg-blue',
                    ],
                    'my_info' => [
                        'right'      => 'manage_personal_info',
                        'title'      => 'Personal info',
                        'link'       => 'user/preferences',
                        'icon'       => 'pencil',
                        'icon_color' => 'bg-blue',
                    ],
                    'personal_documents' => [
                        'right'      => 'manage_personal_documents',
                        'title'      => 'Verification documents',
                        'link'       => 'personal_documents',
                        'icon'       => 'folder',
                        'icon_color' => 'bg-blue',
                    ],
                    'additional_fields' => [
                        'right'      => 'manage_additional_personal_information',
                        'title'      => 'Change additional fields',
                        'link'       => 'user/additional_fields',
                        'icon'       => 'additional',
                        'icon_color' => 'bg-blue',
                    ],
                    'change_email_pass' => [
                        'right'      => '',
                        'title'      => 'Email and password',
                        'link'       => 'user/change_email_pass',
                        'icon'       => 'locked',
                        'icon_color' => 'bg-blue',
                    ],
                    'email_delivery_settings' => [
                        'right'      => '',
                        'title'      => 'Email delivery settings',
                        'link'       => 'user/email_delivery_settings',
                        'icon'       => 'gears',
                        'icon_color' => 'bg-blue',
                    ],
                    'photo' => [
                        'right'      => '',
                        'title'      => 'Photo',
                        'link'       => 'user/photo',
                        'icon'       => 'photo-gallery',
                        'icon_color' => 'bg-blue',
                    ],
                    'cr_short_bio' => [
                        'right'      => 'manage_short_bio',
                        'title'      => 'Short Bio',
                        'link'       => 'cr_user/short_bio',
                        'icon'       => 'items',
                        'icon_color' => 'bg-blue',
                    ],
                    'promo_materials' => [
                        'right'      => 'have_promo_materials',
                        'title'      => 'Promo materials',
                        'link'       => 'promo_materials',
                        'icon'       => 'download-stroke',
                        'icon_color' => 'bg-blue',
                    ],
                    'group' => [
                        'right'      => 'upgrade_group',
                        'title'      => 'Upgrade',
                        'link'       => 'upgrade',
                        'icon'       => 'upgrade',
                        'icon_color' => 'bg-blue',
                    ],
                    'user_statistic' => [
                        'right'      => 'have_statistic',
                        'title'      => 'Statistics',
                        'link'       => 'user_statistic/my',
                        'icon'       => 'statistic',
                        'icon_color' => 'bg-blue',
                    ],
                    'user_cancel' => [
                        'right'      => 'request_account_cancelation',
                        'title'      => 'Account cancellation',
                        'link'       => 'user_cancel',
                        'icon'       => 'remove-circle',
                        'icon_color' => 'bg-red',
                    ],
                ],
            ],
            'community' => [
                'params' => [
                    'right' => 'manage_personal_followers,leave_feedback,manage_personal_reviews,write_questions_on_item,invite_friend,reply_questions,write_comments,manage_blogs',
                    'title' => 'Community',
                    'icon'  => 'community',
                ],
                'items' => [
                    'followers' => [
                        'right'      => 'manage_personal_followers',
                        'title'      => 'Followers',
                        'link'       => 'followers/my',
                        'icon'       => 'followers',
                        'icon_color' => 'bg-blue',
                    ],
                    'feedbacks_written' => [
                        'right'      => 'leave_feedback',
                        'title'      => 'Feedback written',
                        'link'       => 'feedbacks/my/type/written',
                        'icon'       => 'reply-right-empty',
                        'icon_color' => 'bg-blue',
                    ],
                    'feedbacks_received' => [
                        'right'      => 'leave_feedback',
                        'title'      => 'Feedback received',
                        'link'       => 'feedbacks/my',
                        'icon'       => 'reply-left-empty',
                        'icon_color' => 'bg-blue',
                    ],
                    'reviews' => [
                        'right'      => 'manage_personal_reviews',
                        'title'      => 'Reviews',
                        'link'       => 'reviews/my',
                        'icon'       => 'star-empty',
                        'icon_color' => 'bg-blue',
                    ],
                    'questions' => [
                        'right'      => 'write_questions_on_item,reply_questions',
                        'title'      => 'Questions on items',
                        'link'       => 'items_questions/my',
                        'icon'       => 'question-circle',
                        'icon_color' => 'bg-blue',
                    ],
                    'items_comments' => [
                        'right'      => 'write_comments',
                        'title'      => 'Comments',
                        'link'       => 'items_comments/my',
                        'icon'       => 'comments-stroke',
                        'icon_color' => 'bg-blue',
                    ],
                    'blog' => [
                        'right'      => 'manage_blogs',
                        'title'      => 'Blogs',
                        'link'       => 'blogs/my',
                        'icon'       => 'pencil',
                        'icon_color' => 'bg-blue',
                    ],
                    'invite_customers' => [
                        'right'      => 'external_invites_customers',
                        'title'      => 'Invite customers',
                        'link'       => 'company/popup_forms/invite_customers',
                        'popup'      => 'Invite friends to the site',
                        'icon'       => 'plus-circle',
                        'icon_color' => 'bg-blue',
                    ],
                    'invite_add_review' => [
                        'right'      => 'external_invites_rf',
                        'title'      => 'Ask for feedback/reviews',
                        'link'       => 'company/popup_forms/invite_external_feedback',
                        'popup'      => 'Invite buyers for add review',
                        'icon'       => 'comment-stroke',
                        'icon_color' => 'bg-blue',
                    ],
                    'friend_invite' => [
                        'right'       => 'invite_friend',
                        'title'       => 'Friend invite',
                        'link'        => 'invite/popup_forms/invite',
                        'popup'       => 'Friend invite',
                        'icon'        => 'envelope-plus',
                        'icon_color'  => 'bg-blue',
                        'popup_width' => '404',
                    ],
                    'community_questions' => [
                        'right'      => 'manage_community_questions',
                        'title'      => 'Questions',
                        'link'       => 'community_questions/my',
                        'icon'       => 'question-circle',
                        'icon_color' => 'bg-blue',
                    ],
                ],
            ],
            'activity' => [
                'params' => [
                    'right' => 'manage_shipper_estimates,manage_buyer_orders,manage_cr_trainings,manage_cr_expense_reports,attend_cr_event,manage_cr_personal_events,manage_personal_items,highlight_item,feature_item,manage_seller_offers,make_offers,send_inquiry,manage_seller_inquiries,manage_seller_estimate,buy_item,manage_seller_po,manage_seller_orders,see_orders,manage_personal_bills,manage_disputes,manage_shipper_orders',
                    'title' => 'Activity',
                    'icon'  => 'globe',
                ],
                'items' => [
                    'add_items' => [
                        'right'      => 'manage_personal_items',
                        'title'      => 'Add item',
                        'link'       => 'items/choose_category',
                        'icon'       => 'plus-circle',
                        'icon_color' => 'bg-blue',
                    ],
                    'items' => [
                        'right'      => 'manage_personal_items',
                        'title'      => 'Items',
                        'link'       => 'items/my',
                        'icon'       => 'items',
                        'icon_color' => 'bg-green',
                    ],
                    'highlight' => [
                        'right'      => 'highlight_item',
                        'title'      => 'Highlight',
                        'link'       => 'highlight/my',
                        'icon'       => 'highlight',
                        'icon_color' => 'bg-blue',
                    ],
                    'featured' => [
                        'right'      => 'feature_item',
                        'title'      => 'Featured',
                        'link'       => 'featured/my',
                        'icon'       => 'arrow-line-up',
                        'icon_color' => 'bg-blue',
                    ],
                    'offers' => [
                        'right'      => 'manage_seller_offers,make_offers',
                        'title'      => 'Offers',
                        'link'       => 'offers/my',
                        'icon'       => 'offers',
                        'icon_color' => 'bg-blue',
                    ],
                    'inquiries' => [
                        'right'      => 'send_inquiry,manage_seller_inquiries',
                        'title'      => 'Inquiries',
                        'link'       => 'inquiry/my',
                        'icon'       => 'inquiries',
                        'icon_color' => 'bg-blue',
                    ],
                    'estimates' => [
                        'right'      => 'manage_seller_estimate,buy_item',
                        'title'      => 'Estimates',
                        'link'       => 'estimate/my',
                        'icon'       => 'estimates',
                        'icon_color' => 'bg-blue',
                    ],
                    'po' => [
                        'right'      => 'manage_seller_po,buy_item',
                        'title'      => 'Producing Requests',
                        'link'       => 'po/my',
                        'icon'       => 'po',
                        'icon_color' => 'bg-blue',
                    ],
                    'orders' => [
                        'right'      => 'manage_buyer_orders,manage_seller_orders,see_orders,manage_shipper_orders',
                        'title'      => 'Orders',
                        'link'       => 'order/my',
                        'icon'       => 'file',
                        'icon_color' => 'bg-blue',
                    ],
                    'sample_orders' => [
                        'right'      => 'view_sample_orders',
                        'title'      => 'Sample orders',
                        'link'       => 'sample_orders/my',
                        'icon'       => 'sample-order',
                        'icon_color' => 'bg-blue',
                    ],
                    'orders_docs' => [
                        'right'      => 'view_documents',
                        'title'      => 'Order documents',
                        'link'       => 'order_documents',
                        'icon'       => 'folder',
                        'icon_color' => 'bg-blue',
                    ],
                    'billing' => [
                        'right'      => 'manage_personal_bills',
                        'title'      => 'Billing',
                        'link'       => 'billing/my',
                        'icon'       => 'dollar-circle',
                        'icon_color' => 'bg-blue',
                    ],
                    'basket' => [
                        'right'      => 'buy_item',
                        'title'      => 'Basket',
                        'link'       => 'basket/my',
                        'icon'       => 'basket',
                        'icon_color' => 'bg-blue',
                    ],
                    'buyer_shipping_estimates' => [
                        'right'      => 'buy_item',
                        'title'      => 'Shipping estimates',
                        'link'       => 'shippers/estimates_requests',
                        'icon'       => 'truck',
                        'icon_color' => 'bg-blue',
                    ],
                    'ff_shipping_estimates' => [
                        'right'      => 'manage_shipper_estimates',
                        'title'      => 'Shipping estimates',
                        'link'       => 'shippers/estimates',
                        'icon'       => 'truck',
                        'icon_color' => 'bg-blue',
                    ],
                    'ff_upcoming_orders' => [
                        'right'      => 'manage_quote_requests',
                        'title'      => 'Upcoming orders',
                        'link'       => 'orders_bids/upcoming',
                        'icon'       => 'arrow-clock',
                        'icon_color' => 'bg-blue',
                    ],
                    'ff_orders_bids' => [
                        'right'      => 'manage_quote_requests',
                        'title'      => 'Orders bids',
                        'link'       => 'orders_bids/my',
                        'icon'       => 'file',
                        'icon_color' => 'bg-blue',
                    ],
                    'disputes' => [
                        'right'      => 'manage_disputes',
                        'title'      => 'Disputes',
                        'link'       => 'dispute/my',
                        'icon'       => 'low',
                        'icon_color' => 'bg-blue',
                    ],
                    'droplist' => [
                        'right'      => 'droplist_access',
                        'title'      => 'Droplist',
                        'link'       => 'items/droplist',
                        'icon'       => 'bell-stroke',
                        'icon_color' => 'bg-blue',
                        'new'        => true
                    ],
                    'cr_events'          => [
                        'title'      => 'Events',
                        'right'      => 'manage_cr_personal_events',
                        'link'       => 'cr_events/my',
                        'icon'       => 'calendar',
                        'icon_color' => 'bg-blue',
                    ],
                    'cr_user_events'     => [
                        'title'      => 'Country events',
                        'right'      => 'attend_cr_event',
                        'link'       => 'cr_events/my',
                        'icon'       => 'calendar',
                        'icon_color' => 'bg-blue',
                    ],
                    'cr_expense_reports' => [
                        'title'      => 'Expense Reports',
                        'right'      => 'manage_cr_expense_reports',
                        'link'       => 'cr_expense_reports/my',
                        'icon'       => 'dollar-circle',
                        'icon_color' => 'bg-blue',
                    ],
                    'cr_trainings'       => [
                        'title'      => 'Trainings',
                        'right'      => 'manage_cr_trainings',
                        'link'       => 'cr_training/my',
                        'icon'       => 'training',
                        'icon_color' => 'bg-blue',
                    ],
                ],
            ],
            'organizer' => [
                'params' => [
                    'right' => '',
                    'title' => 'Organizer',
                    'icon'  => 'info',
                ],
                'items' => [
                    'calendar' => [
                        'right'      => 'have_calendar',
                        'title'      => 'Calendar',
                        'link'       => 'calendar/my',
                        'icon'       => 'calendar',
                        'icon_color' => 'bg-blue',
                    ],
                    'sticker' => [
                        'right'      => 'moderate_content',
                        'title'      => 'Sticker',
                        'link'       => 'sticker/my',
                        'icon'       => 'sticker',
                        'icon_color' => 'bg-blue',
                    ],
                    'widgets' => [
                        'right'      => 'sell_item',
                        'title'      => 'Widgets',
                        'link'       => 'dashboard/widgets',
                        'icon'       => 'widgets',
                        'icon_color' => 'bg-blue',
                    ],
                    // "customize_menu" =>
                    // 	array(
                    // 		"right" => "",
                    // 		"title" => "Customize menu",
                    // 		"link" => "dashboard/customize_menu",
                    // 		"icon" => "menu",
                    // 		"icon_color" => "bg-blue",
                    // 	),
                    'my_dashboard' => [
                        'right'      => '',
                        'title'      => translate('header_navigation_link_go_to_dashboard_title'),
                        'link'       => 'dashboard',
                        'icon'       => 'nav-grid',
                        'icon_color' => 'bg-blue',
                    ],
                ],
            ],
            'mycompany' => [
                'params' => [
                    'right' => 'manage_branches,have_about_info,have_services_contacts,have_company,edit_company,buyer_edit_company,shipper_edit_company',
                    'title' => 'My company',
                    'icon'  => 'building',
                ],
                'items' => [
                    'company_info' => [
                        'right'      => 'edit_company,buyer_edit_company,shipper_edit_company',
                        'title'      => 'Company info',
                        'link'       => 'company/edit',
                        'icon'       => 'pencil',
                        'icon_color' => 'bg-blue',
                    ],
                    'branches' => [
                        'right'      => 'manage_branches',
                        'title'      => 'Branches',
                        'link'       => 'company_branches/my',
                        'icon'       => 'branches',
                        'icon_color' => 'bg-blue',
                    ],
                    'about' => [
                        'right'      => 'have_about_info',
                        'title'      => 'About',
                        'link'       => 'company_about/my',
                        'icon'       => 'info-stroke',
                        'icon_color' => 'bg-blue',
                    ],
                    'services' => [
                        'right'      => 'have_services_contacts',
                        'title'      => 'Departments',
                        'link'       => 'company_services/my',
                        'icon'       => 'gears',
                        'icon_color' => 'bg-blue',
                    ],
                    'company_page' => [
                        'right'      => 'edit_company',
                        'title'      => 'Company page',
                        'link'       => '',
                        'icon'       => 'buildings',
                        'icon_color' => 'bg-blue',
                    ],
                    'ff_company_page' => [
                        'right'      => 'shipper_edit_company',
                        'title'      => 'Company page',
                        'link'       => '',
                        'icon'       => 'buildings',
                        'icon_color' => 'bg-blue',
                    ],
                    /*"seller_banners" =>
                        array(
                            "right" => "have_company",
                            "title" => "Banners",
                            "link" => "seller_banners/my",
                            "icon" => "highlight"
                        ),*/
                    'shipping_countries' => [
                        'right'      => 'shipper_edit_company',
                        'title'      => 'Shipping countries',
                        'link'       => 'shipping_countries',
                        'icon'       => 'globe',
                        'icon_color' => 'bg-blue',
                    ],
                ],
            ],
            'mediacompany' => [
                'params' => [
                    'right' => 'have_news,have_updates,have_library,have_pictures,have_videos',
                    'title' => 'Company posts',
                    'icon'  => 'news',
                ],
                'items' => [
                    'news' => [
                        'right'      => 'have_news',
                        'title'      => 'News',
                        'link'       => 'seller_news/my',
                        'icon'       => 'news',
                        'icon_color' => 'bg-blue',
                    ],
                    'updates' => [
                        'right'      => 'have_updates',
                        'title'      => 'Updates',
                        'link'       => 'seller_updates/my',
                        'icon'       => 'updates',
                        'icon_color' => 'bg-blue',
                    ],
                    'library' => [
                        'right'      => 'have_library',
                        'title'      => 'Library',
                        'link'       => 'seller_library/my',
                        'icon'       => 'folder',
                        'icon_color' => 'bg-blue',
                    ],
                    'pictures' => [
                        'right'      => 'have_pictures',
                        'title'      => 'Pictures',
                        'link'       => 'seller_pictures/my',
                        'icon'       => 'photo-gallery',
                        'icon_color' => 'bg-blue',
                    ],
                    'videos' => [
                        'right'      => 'have_videos',
                        'title'      => 'Videos',
                        'link'       => 'seller_videos/my',
                        'icon'       => 'videos',
                        'icon_color' => 'bg-blue',
                    ],
                ],
            ],
            'b2b' => [
                'params' => [
                    'right' => 'manage_b2b_requests,mange_ff_partnership_requests',
                    'title' => 'B2B',
                    'icon'  => 'request',
                ],
                'items' => [
                    'add_request' => [
                        'right'      => 'manage_b2b_requests',
                        'title'      => 'Add request',
                        'link'       => 'b2b/reg',
                        'icon'       => 'plus-circle',
                        'icon_color' => 'bg-blue',
                    ],
                    'requests' => [
                        'right'      => 'manage_b2b_requests,mange_ff_partnership_requests',
                        'title'      => 'Requests',
                        'link'       => 'b2b/my_requests',
                        'icon'       => 'comment-stroke',
                        'icon_color' => 'bg-blue',
                    ],
                    'partners' => [
                        'right'      => 'manage_b2b_requests,mange_ff_partnership_requests',
                        'title'      => 'Partners',
                        'link'       => 'b2b/my_partners',
                        'icon'       => 'partners',
                        'icon_color' => 'bg-blue',
                    ],
                    'shippers' => [
                        'right'      => 'manage_b2b_requests',
                        'title'      => 'Freight Forwarders',
                        'link'       => 'shippers/my_partners',
                        'icon'       => 'truck',
                        'icon_color' => 'bg-blue',
                    ],
                    'seller_b2b' => [
                        'right'      => 'manage_b2b_requests',
                        'title'      => 'B2B tabs',
                        'link'       => 'seller_b2b/my',
                        'icon'       => 'pencil',
                        'icon_color' => 'bg-blue',
                    ],
                ],
            ],
        ];
    } else {
        $items = [
            'dashboard' => [
                'params' => [
                    'right' => '',
                    'title' => 'Extend upgrade',
                    'icon'  => '',
                ],
                'items' => [
                    'upgrade_extend' => [
                        'right'      => 'upgrade_group',
                        'title'      => 'Extend upgrade',
                        'link'       => 'upgrade',
                        'icon'       => 'upgrade',
                        'icon_color' => 'bg-blue',
                    ],
                    'billing' => [
                        'right'      => 'manage_personal_bills',
                        'title'      => 'Billing',
                        'link'       => 'billing/my',
                        'icon'       => 'dollar-circle',
                        'icon_color' => 'bg-blue',
                    ],
                ],
            ],
        ];
    }

    return $items;
}

/**
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [29.10.2021]
 * Reason: Code style
 * Reason: refactor method name
 */
function dashboard_admin_nav_rights()
{
    return [
        'dashboard' => [
            'params' => [
                'right' => 'manage_personal_info',
                'title' => 'Personal info',
                'icon'  => '',
            ],
            'items' => [
                'my_info' => [
                    'right'      => 'manage_personal_info',
                    'title'      => 'Personal info',
                    'link'       => 'user/preferences',
                    'icon'       => 'user',
                    'icon_color' => 'bg-blue',
                ],
                'change_email_pass' => [
                    'right'      => 'manage_personal_info',
                    'title'      => 'Email and password',
                    'link'       => 'user/change_email_pass',
                    'icon'       => 'locked',
                    'icon_color' => 'bg-blue',
                ],
                'email_delivery_settings' => [
                    'right'      => 'manage_personal_info',
                    'title'      => 'Email delivery settings',
                    'link'       => 'user/email_delivery_settings',
                    'icon'       => 'gears',
                    'icon_color' => 'bg-blue',
                ],
            ],
        ],
        'organizer' => [
            'params' => [
                'right' => '',
                'title' => 'Organizer',
                'icon'  => 'info',
            ],
            'items' => [
                'sticker' => [
                    'right'      => 'manage_personal_stickers',
                    'title'      => 'Sticker',
                    'link'       => 'sticker/my',
                    'icon'       => 'sticker',
                    'icon_color' => 'bg-blue',
                ],
                'customize_menu' => [
                    'right'      => '',
                    'title'      => 'Customize menu',
                    'link'       => 'dashboard/customize_menu',
                    'icon'       => 'menu',
                    'icon_color' => 'bg-blue',
                ],
                'my_dashboard' => [
                    'right'      => '',
                    'title'      => translate('header_navigation_link_go_to_admin_dashboard_title'),
                    'link'       => 'admin',
                    'icon'       => 'nav-grid',
                    'icon_color' => 'bg-blue',
                ],
            ],
        ],
        'categories' => [
            'params' => [
                'right' => 'items_categories_administration,items_categories_attr_administration,items_categories_articles_administration',
                'title' => 'Categories',
                'icon'  => 'categories',
            ],
            'items' => [
                'categories' => [
                    'right'      => 'items_categories_administration',
                    'title'      => 'Categories',
                    'link'       => 'categories/administration',
                    'icon'       => 'tree',
                    'icon_color' => 'bg-blue',
                ],
                'attributes' => [
                    'right'      => 'items_categories_attr_administration',
                    'title'      => 'Attributes',
                    'link'       => 'catattr/administration',
                    'icon'       => 'filter',
                    'icon_color' => 'bg-blue',
                ],
                'categories_articles' => [
                    'right'      => 'items_categories_articles_administration',
                    'title'      => 'Categories articles',
                    'link'       => 'categories_articles/administration',
                    'icon'       => 'branches',
                    'icon_color' => 'bg-blue',
                ],
            ],
        ],
        'users' => [
            'params' => [
                'right' => 'manage_upgrade_requests,users_administration,calling_statuses_administration,notification_messages_administration,manage_user_documents,cancellation_requests_administration,ep_staff_administration,manage_grouprights,gr_packages_administration,user_services_administration,user_statistic_administration',
                'title' => 'Users',
                'icon'  => 'user',
            ],
            'items' => [
                'users' => [
                    'right'      => 'users_administration',
                    'title'      => 'Users',
                    'link'       => 'users/administration',
                    'icon'       => 'followers',
                    'icon_color' => 'bg-blue',
                ],
                'verification' => [
                    'right'      => 'manage_user_documents',
                    'title'      => "Users' verification",
                    'link'       => 'verification/users',
                    'icon'       => 'ok-circle',
                    'icon_color' => 'bg-blue',
                ],
                'upgrade_requests' => [
                    'right'      => 'manage_upgrade_requests',
                    'title'      => 'Upgrade requests',
                    'link'       => 'upgrade/requests',
                    'icon'       => 'arrow-line-up',
                    'icon_color' => 'bg-blue',
                ],
                'calling_statuses' => [
                    'right'      => 'calling_statuses_administration',
                    'title'      => 'Calling statuses',
                    'link'       => 'users/calling_statuses',
                    'icon'       => 'comments-stroke',
                    'icon_color' => 'bg-blue',
                ],
                'notification_messages' => [
                    'right'      => 'notification_messages_administration',
                    'title'      => 'Reason messages',
                    'link'       => 'users/reason_messages',
                    'icon'       => 'bell-stroke',
                    'icon_color' => 'bg-blue',
                ],
                'profile_edit_requests' => [
                    'right'      => 'users_administration',
                    'title'      => 'Profile edit requests',
                    'link'       => 'profile_edit_requests/administration',
                    'icon'       => 'user-add',
                    'icon_color' => 'bg-blue',
                ],
                'company_edit_requests' => [
                    'right'      => 'companies_administration',
                    'title'      => 'Company edit requests',
                    'link'       => 'company_edit_requests/administration',
                    'icon'       => 'buildings',
                    'icon_color' => 'bg-blue',
                ],
                'cancellation_requests' => [
                    'right'      => 'cancellation_requests_administration',
                    'title'      => 'Cancellation requests',
                    'link'       => 'user_cancel/administration',
                    'icon'       => 'remove-circle',
                    'icon_color' => 'bg-red',
                ],
                'staff_administrators' => [
                    'right'      => 'ep_staff_administration',
                    'title'      => 'Staff &amp; administrators',
                    'link'       => 'users/ep_staff',
                    'icon'       => 'followers',
                    'icon_color' => 'bg-blue',
                ],
                'crud_group_rights' => [
                    'right'      => 'manage_grouprights',
                    'title'      => 'Crud group &amp; rights',
                    'link'       => 'admin/grouprightad',
                    'icon'       => 'widgets',
                    'icon_color' => 'bg-green',
                ],
                'group_rights' => [
                    'right'      => 'manage_grouprights',
                    'title'      => 'Groups and rights',
                    'link'       => 'admin/groupright',
                    'icon'       => 'widgets',
                    'icon_color' => 'bg-blue',
                ],
                'group_packages' => [
                    'right'      => 'gr_packages_administration',
                    'title'      => 'Account upgrade packages',
                    'link'       => 'group_packages/administration',
                    'icon'       => 'box',
                    'icon_color' => 'bg-blue',
                ],
                'rights_packages' => [
                    'right'      => 'gr_packages_administration',
                    'title'      => 'Rights packages',
                    'link'       => 'rights_packages/administration',
                    'icon'       => 'rights',
                    'icon_color' => 'bg-green',
                ],
                'user_services' => [
                    'right'      => 'user_services_administration',
                    'title'      => 'Users services',
                    'link'       => 'admin/user_services',
                    'icon'       => 'gears',
                    'icon_color' => 'bg-blue',
                ],
                'users_statistic' => [
                    'right'      => 'user_statistic_administration',
                    'title'      => 'Users statistics',
                    'link'       => 'user_statistic/administration',
                    'icon'       => 'statistic',
                    'icon_color' => 'bg-blue',
                ],
            ],
        ],
        'directory' => [
            'params' => [
                'right' => 'directory_administration,moderate_content,manage_content',
                'title' => 'Directory',
                'icon'  => 'buildings',
            ],
            'items' => [
                'directory' => [
                    'right'      => 'moderate_content',
                    'title'      => 'Directory',
                    'link'       => 'directory/administration',
                    'icon'       => 'buildings',
                    'icon_color' => 'bg-blue',
                ],
                'companies_news' => [
                    'right'      => 'moderate_content',
                    'title'      => 'Companies news',
                    'link'       => 'directory/news_administration',
                    'icon'       => 'news',
                    'icon_color' => 'bg-blue',
                ],
                'companies_photos' => [
                    'right'      => 'moderate_content',
                    'title'      => 'Companies photos',
                    'link'       => 'directory/photos_administration',
                    'icon'       => 'photo-gallery',
                    'icon_color' => 'bg-blue',
                ],
                'companies_videos' => [
                    'right'      => 'moderate_content',
                    'title'      => 'Companies videos',
                    'link'       => 'directory/videos_administration',
                    'icon'       => 'videos',
                    'icon_color' => 'bg-blue',
                ],
                'companies_updates' => [
                    'right'      => 'moderate_content',
                    'title'      => 'Companies updates',
                    'link'       => 'directory/updates_administration',
                    'icon'       => 'updates',
                    'icon_color' => 'bg-blue',
                ],
                'companies_library' => [
                    'right'      => 'moderate_content',
                    'title'      => 'Companies library',
                    'link'       => 'directory/library_administration',
                    'icon'       => 'folder',
                    'icon_color' => 'bg-blue',
                ],
                'b2b_requests' => [
                    'right'      => 'moderate_content',
                    'title'      => 'B2B Requests',
                    'link'       => 'b2b/administration',
                    'icon'       => 'partners',
                    'icon_color' => 'bg-blue',
                ],
                'companies_type' => [
                    'right'      => 'directory_administration',
                    'title'      => 'Companies type',
                    'link'       => 'directory/administration_types',
                    'icon'       => 'copyright',
                    'icon_color' => 'bg-blue',
                ],
                'companies_industries' => [
                    'right'      => 'directory_administration',
                    'title'      => 'Companies industries',
                    'link'       => 'directory/administration_industries',
                    'icon'       => 'branches',
                    'icon_color' => 'bg-green',
                ],
                'companies_categories' => [
                    'right'      => 'directory_administration',
                    'title'      => 'Companies categories',
                    'link'       => 'directory/administration_categories',
                    'icon'       => 'tree',
                    'icon_color' => 'bg-orange',
                ],
            ],
        ],
        'items' => [
            'params' => [
                'right' => 'items_administration,moderate_content',
                'title' => 'Items',
                'icon'  => 'items',
            ],
            'items' => [
                'items' => [
                    'right'      => 'items_administration',
                    'title'      => 'Items',
                    'link'       => 'items/administration',
                    'icon'       => 'items',
                    'icon_color' => 'bg-green',
                ],
                'items_featured' => [
                    'right'      => 'items_administration',
                    'title'      => 'Items featured',
                    'link'       => 'items/featured_administration',
                    'icon'       => 'arrow-line-up',
                    'icon_color' => 'bg-blue',
                ],
                'items_highlight' => [
                    'right'      => 'items_administration',
                    'title'      => 'Items highlighted',
                    'link'       => 'items/highlight_administration',
                    'icon'       => 'highlight',
                    'icon_color' => 'bg-blue',
                ],
                'items_questions' => [
                    'right'      => 'moderate_content',
                    'title'      => 'Items questions',
                    'link'       => 'items_questions/administration',
                    'icon'       => 'question-circle',
                    'icon_color' => 'bg-blue',
                ],
                'items_comments' => [
                    'right'      => 'moderate_content',
                    'title'      => 'Items comments',
                    'link'       => 'items_comments/administration',
                    'icon'       => 'comments-stroke',
                    'icon_color' => 'bg-blue',
                ],
            ],
        ],
        'orders' => [
            'params' => [
                'right' => 'administrate_orders',
                'title' => 'Orders',
                'icon'  => 'orders',
            ],
            'items' => [
                'order_not_assigned' => [
                    'right'      => 'administrate_orders',
                    'title'      => 'Order not assigned',
                    'link'       => 'order/admin_not_assigned',
                    'icon'       => 'file',
                    'icon_color' => 'bg-red',
                ],
                'order_assigned' => [
                    'right'      => 'administrate_orders',
                    'title'      => 'Order assigned',
                    'link'       => 'order/admin_assigned',
                    'icon'       => 'file-ok',
                    'icon_color' => 'bg-green',
                ],
                'orders_docs' => [
                    'right'      => 'monitor_documents',
                    'title'      => 'Order documents',
                    'link'       => 'order_documents/administration',
                    'icon'       => 'folder',
                    'icon_color' => 'bg-blue',
                ],
                'cancel_order_reasons' => [
                    'right'      => 'administrate_orders',
                    'title'      => 'Cancel order reasons',
                    'link'       => 'order/admin_reasons',
                    'icon'       => 'remove-circle',
                    'icon_color' => 'bg-red',
                ],
                'order_disputes' => [
                    'right'      => 'administrate_orders',
                    'title'      => 'Order disputes',
                    'link'       => 'dispute/administration',
                    'icon'       => 'low',
                    'icon_color' => 'bg-blue',
                ],
                'shippers' => [
                    'right'      => 'administrate_orders',
                    'title'      => 'Freight Forwarders',
                    'link'       => 'shippers/administration',
                    'icon'       => 'truck',
                    'icon_color' => 'bg-blue',
                ],
                'offers' => [
                    'right'      => 'administrate_orders',
                    'title'      => 'Offers',
                    'link'       => 'offers/administration',
                    'icon'       => 'offers',
                    'icon_color' => 'bg-blue',
                ],
                'estimate' => [
                    'right'      => 'administrate_orders',
                    'title'      => 'Estimates',
                    'link'       => 'estimate/administration',
                    'icon'       => 'estimates',
                    'icon_color' => 'bg-blue',
                ],
                'inquiries' => [
                    'right'      => 'administrate_orders',
                    'title'      => 'Inquiries',
                    'link'       => 'inquiry/inquiry_administration',
                    'icon'       => 'inquiries',
                    'icon_color' => 'bg-blue',
                ],
                'po' => [
                    'right'      => 'administrate_orders',
                    'title'      => 'Producing Requests',
                    'link'       => 'po/administration',
                    'icon'       => 'po',
                    'icon_color' => 'bg-blue',
                ],
            ],
        ],
        'billing' => [
            'params' => [
                'right' => 'manage_bills',
                'title' => 'Billing',
                'icon'  => 'billing',
            ],
            'items' => [
                'billing' => [
                    'right'      => 'manage_bills',
                    'title'      => 'Billing',
                    'link'       => 'billing/administration',
                    'icon'       => 'dollar-circle',
                    'icon_color' => 'bg-blue',
                ],
                'external_bills' => [
                    'right'      => 'manage_bills',
                    'title'      => 'External bills',
                    'link'       => 'external_bills/administration',
                    'icon'       => 'dollar-circle',
                    'icon_color' => 'bg-blue',
                ],
                'payments_methods' => [
                    'right'      => 'manage_bills',
                    'title'      => 'Payments methods',
                    'link'       => 'payments/administration',
                    'icon'       => 'payments',
                    'icon_color' => 'bg-blue',
                ],
            ],
        ],
        'site_content' => [
            'params' => [
                'right' => 'manage_content,admin_site',
                'title' => 'Site content',
                'icon'  => 'window',
            ],
            'items' => [
                'faq' => [
                    'right'      => 'moderate_content',
                    'title'      => 'FAQ',
                    'link'       => 'faq/administration',
                    'icon'       => 'book-search',
                    'icon_color' => 'bg-blue',
                ],
                'user_guide' => [
                    'right'      => 'manage_content',
                    'title'      => 'User Guide',
                    'link'       => 'user_guide/administration',
                    'icon'       => 'folder',
                    'icon_color' => 'bg-blue',
                ],
                'accreditation_documents' => [
                    'right'      => 'manage_content',
                    'title'      => 'Accreditation documents',
                    'link'       => 'verification_document_types/administration',
                    'icon'       => 'folder-ok',
                    'icon_color' => 'bg-blue',
                ],
                'systems_messages' => [
                    'right'      => 'manage_content',
                    'title'      => 'Notifications',
                    'link'       => 'systmess/administration',
                    'icon'       => 'bell-stroke',
                    'icon_color' => 'bg-blue',
                ],
                'our_partners' => [
                    'right'      => 'manage_content',
                    'title'      => 'Our partners',
                    'link'       => 'partners/administration',
                    'icon'       => 'partners',
                    'icon_color' => 'bg-blue',
                ],
                'video' => [
                    'right'      => 'manage_content',
                    'title'      => 'Video',
                    'link'       => 'video',
                    'icon'       => 'videos',
                    'icon_color' => 'bg-blue',
                ],
                'popuplar_topics' => [
                    'right'      => 'manage_content',
                    'title'      => 'Popular topics',
                    'link'       => 'topics/administration',
                    'icon'       => 'comments-stroke',
                    'icon_color' => 'bg-blue',
                ],
                'textual_blocks' => [
                    'right'      => 'manage_content',
                    'title'      => 'Textual blocks',
                    'link'       => 'text_block/administration',
                    'icon'       => 'comments-stroke',
                    'icon_color' => 'bg-blue',
                ],
                'country_articles' => [
                    'right'      => 'manage_content',
                    'title'      => 'Country articles',
                    'link'       => 'country_articles/administration',
                    'icon'       => 'pencil',
                    'icon_color' => 'bg-blue',
                ],
                'international_standards' => [
                    'right'      => 'manage_content',
                    'title'      => 'International standards',
                    'link'       => 'international_standards/administration',
                    'icon'       => 'file',
                    'icon_color' => 'bg-green',
                ],
                'customs_requirements' => [
                    'right'      => 'manage_content',
                    'title'      => 'Customs requirements',
                    'link'       => 'customs_requirements/administration',
                    'icon'       => 'file',
                    'icon_color' => 'bg-blue',
                ],
                'library_country_statistic' => [
                    'right'      => 'manage_content',
                    'title'      => 'Country statistic',
                    'link'       => 'library_country_statistic/manage',
                    'icon'       => 'statistic',
                    'icon_color' => 'bg-blue',
                ],
                'ep_news' => [
                    'right'      => 'manage_content',
                    'title'      => 'EP news',
                    'link'       => 'ep_news/administration',
                    'icon'       => 'news',
                    'icon_color' => 'bg-blue',
                ],
                'ep_updates' => [
                    'right'      => 'manage_content',
                    'title'      => 'EP updates',
                    'link'       => 'ep_updates/administration',
                    'icon'       => 'updates',
                    'icon_color' => 'bg-blue',
                ],
                'our_team' => [
                    'right'      => 'manage_content',
                    'title'      => 'Our team',
                    'link'       => 'our_team/administration',
                    'icon'       => 'followers',
                    'icon_color' => 'bg-blue',
                ],
                'offices' => [
                    'right'      => 'manage_content',
                    'title'      => 'Offices',
                    'link'       => 'offices/administration',
                    'icon'       => 'buildings',
                    'icon_color' => 'bg-blue',
                ],
                'hirings' => [
                    'right'      => 'manage_content',
                    'title'      => 'Hirings',
                    'link'       => 'hiring/administration',
                    'icon'       => 'magnifier',
                    'icon_color' => 'bg-blue',
                ],
                'mass_media' => [
                    'right'      => 'manage_content',
                    'title'      => 'Mass media',
                    'link'       => 'mass_media/administration_media',
                    'icon'       => 'globe',
                    'icon_color' => 'bg-blue',
                ],
                'mass_media_news' => [
                    'right'      => 'manage_content',
                    'title'      => 'Mass media news',
                    'link'       => 'mass_media/administration_news',
                    'icon'       => 'globe',
                    'icon_color' => 'bg-blue',
                ],
                'meta_pages' => [
                    'right'      => 'manage_content',
                    'title'      => 'Meta pages',
                    'link'       => 'meta/administration',
                    'icon'       => 'link',
                    'icon_color' => 'bg-blue',
                ],
                'ep_modules' => [
                    'right'      => 'admin_site',
                    'title'      => 'EP modules',
                    'link'       => 'ep_modules/administration',
                    'icon'       => 'gears',
                    'icon_color' => 'bg-blue',
                ],
                'ep_pages' => [
                    'right'      => 'admin_site',
                    'title'      => 'EP pages',
                    'link'       => 'pages/administration',
                    'icon'       => 'gears',
                    'icon_color' => 'bg-blue',
                ],
                'configuration' => [
                    'right'      => 'admin_site',
                    'title'      => 'Configuration',
                    'link'       => 'config/administration',
                    'icon'       => 'gears',
                    'icon_color' => 'bg-blue',
                ],
                'cache_configuration' => [
                    'right'      => 'admin_site',
                    'title'      => 'Cache configuration',
                    'link'       => 'cache_config/administration',
                    'icon'       => 'gears',
                    'icon_color' => 'bg-green',
                ],
            ],
        ],
        'content_templates' => [
            'params' => [
                'right' => 'manage_content',
                'title' => 'Content templates',
                'icon'  => 'file',
            ],
            'items' => [
                'links_storage' => [
                    'right'      => 'manage_content',
                    'title'      => 'Links storage',
                    'link'       => 'links_storage/administration',
                    'icon'       => 'link',
                    'icon_color' => 'bg-blue',
                ],
                'email_templates' => [
                    'right'      => 'manage_content',
                    'title'      => 'E-mail template files',
                    'link'       => 'email_templates/administration',
                    'icon'       => 'inbox',
                    'icon_color' => 'bg-blue',
                ],
            ],
        ],
        'incoming_messages' => [
            'params' => [
                'right' => 'manage_email_messages,manage_content',
                'title' => 'Incoming messages',
                'icon'  => 'envelope-send',
            ],
            'items' => [
                'email_message' => [
                    'right'      => 'manage_email_messages',
                    'title'      => 'Messages',
                    'link'       => 'email_message/administration',
                    'icon'       => 'comments-stroke',
                    'icon_color' => 'bg-blue',
                ],
                'email_message_my' => [
                    'right'      => 'manage_email_messages',
                    'title'      => 'My messages',
                    'link'       => 'email_message/my',
                    'icon'       => 'envelope-stroke',
                    'icon_color' => 'bg-green',
                ],
                'ep_staff_groups' => [
                    'right'      => 'manage_email_messages',
                    'title'      => 'Support categories',
                    'link'       => 'category_support/administration',
                    'icon'       => 'followers',
                    'icon_color' => 'bg-blue',
                ],
                'contact_messages' => [
                    'right'      => 'manage_content',
                    'title'      => 'Contact messages',
                    'link'       => 'contact/administration',
                    'icon'       => 'comments-stroke',
                    'icon_color' => 'bg-blue',
                ],
            ],
        ],
        'banners' => [
            'params' => [
                'right' => 'admin_site',
                'title' => 'Banners',
                'icon'  => 'news',
            ],
            'items' => [
                'banner' => [
                    'right'      => 'admin_site',
                    'title'      => 'banner/administration/',
                    'link'       => 'Link to us',
                    'icon'       => 'events',
                    'icon_color' => 'bg-blue',
                ],
            ],
        ],
        'community' => [
            'params' => [
                'right' => 'community_questions_administration,moderate_content,blogs_administration,manage_content',
                'title' => 'Community',
                'icon'  => 'community',
            ],
            'items' => [
                'community_questions' => [
                    'right'      => 'community_questions_administration',
                    'title'      => 'Questions',
                    'link'       => 'community_questions/administration',
                    'icon'       => 'question-circle',
                    'icon_color' => 'bg-blue',
                ],
                'question_categories' => [
                    'right'      => 'community_questions_administration',
                    'title'      => 'Question categories',
                    'link'       => 'community_questions/question_categories',
                    'icon'       => 'tree',
                    'icon_color' => 'bg-orange',
                ],
                'question_answers' => [
                    'right'      => 'community_questions_administration',
                    'title'      => 'Question answers',
                    'link'       => 'community_questions/answers_administration',
                    'icon'       => 'comment-stroke',
                    'icon_color' => 'bg-green',
                ],
                'question_comments' => [
                    'right'      => 'community_questions_administration',
                    'title'      => 'Question comments',
                    'link'       => 'community_questions/comments_administration',
                    'icon'       => 'comments-stroke',
                    'icon_color' => 'bg-gray',
                ],
                'reviews' => [
                    'right'      => 'moderate_content',
                    'title'      => 'Reviews',
                    'link'       => 'reviews/administration',
                    'icon'       => 'star-empty',
                    'icon_color' => 'bg-blue',
                ],
                'feedbacks' => [
                    'right'      => 'moderate_content',
                    'title'      => 'Feedbacks',
                    'link'       => 'feedbacks/administration',
                    'icon'       => 'reply-right-empty',
                    'icon_color' => 'bg-blue',
                ],
                'blogs' => [
                    'right'      => 'blogs_administration',
                    'title'      => 'Blogs',
                    'link'       => 'blogs/administration',
                    'icon'       => 'pencil',
                    'icon_color' => 'bg-blue',
                ],
                'blogs_categories' => [
                    'right'      => 'blogs_administration',
                    'title'      => 'Blogs categories',
                    'link'       => 'blogs/category_administration',
                    'icon'       => 'tree',
                    'icon_color' => 'bg-blue',
                ],
                'bloggers_articles' => [
                    'right'      => 'bloggers_articles_administration',
                    'title'      => 'Bloggers articles',
                    'link'       => 'bloggers/administration',
                    'icon'       => 'pencil',
                    'icon_color' => 'bg-blue',
                ],
                'complains' => [
                    'right'      => 'manage_content',
                    'title'      => 'Reports',
                    'link'       => 'complains/administration',
                    'icon'       => 'megaphone',
                    'icon_color' => 'bg-red',
                ],
                'complains_types' => [
                    'right'      => 'manage_content',
                    'title'      => 'Reports types themes',
                    'link'       => 'complains/types_themes_administration',
                    'icon'       => 'megaphone',
                    'icon_color' => 'bg-orange',
                ],
            ],
        ],
        'api' => [
            'params' => [
                'right' => 'admin_site',
                'title' => 'API',
                'icon'  => 'keys',
            ],
            'items' => [
                'api_keys' => [
                    'right'      => 'admin_site',
                    'title'      => 'API keys',
                    'link'       => 'api_keys/administration',
                    'icon'       => 'key',
                    'icon_color' => 'bg-blue',
                ],
            ],
        ],
        'translations' => [
            'params' => [
                'right' => 'super_admin,manage_translations',
                'title' => 'Translations',
                'icon'  => 'globe',
            ],
            'items' => [
                'languages' => [
                    'right'      => 'super_admin',
                    'title'      => 'Languages',
                    'link'       => 'translations/languages',
                    'icon'       => 'globe-circle',
                    'icon_color' => 'bg-blue',
                ],
                'routings' => [
                    'right'      => 'super_admin',
                    'title'      => 'Routings',
                    'link'       => 'translations/routings',
                    'icon'       => 'branches',
                    'icon_color' => 'bg-orange',
                ],
                'files' => [
                    'right'      => 'manage_translations',
                    'title'      => 'Static text',
                    'link'       => 'translations/administration',
                    'icon'       => 'library',
                    'icon_color' => 'bg-green',
                ],
                'system_messages' => [
                    'right'      => 'manage_translations',
                    'title'      => 'System messages',
                    'link'       => 'translations/system_messages',
                    'icon'       => 'library',
                    'icon_color' => 'bg-green',
                ],
            ],
        ],
        'analytics' => [
            'params' => [
                'right' => 'manage_analytics,manage_analytics_targets,export_db_reports',
                'title' => 'Analytics',
                'icon'  => 'statistic',
            ],
            'items' => [
                'export_reports' => [
                    'right'      => 'export_db_reports',
                    'title'      => 'Reports',
                    'link'       => 'reports',
                    'icon'       => 'statistic',
                    'icon_color' => 'bg-blue',
                ],
                'pageviews' => [
                    'right'      => 'manage_analytics',
                    'title'      => 'Pageviews Analytics',
                    'link'       => 'analytics/pageviews',
                    'icon'       => 'statistic',
                    'icon_color' => 'bg-blue',
                ],
                'forms_filled' => [
                    'right'      => 'manage_analytics',
                    'title'      => 'Forms Analytics',
                    'link'       => 'analytics/forms_filled',
                    'icon'       => 'statistic',
                    'icon_color' => 'bg-blue',
                ],
                'ga' => [
                    'right'      => 'manage_analytics',
                    'title'      => 'Google Analytics',
                    'link'       => 'analytics/ga_pageviews',
                    'icon'       => 'statistic',
                    'icon_color' => 'bg-blue',
                ],
                'targets' => [
                    'right'      => 'manage_analytics_targets',
                    'title'      => 'Targets',
                    'link'       => 'analytics/targets',
                    'icon'       => 'items',
                    'icon_color' => 'bg-blue',
                ],
            ],
        ],
        'moderation' => [
            'params' => [
                'right' => 'moderate_content',
                'title' => 'Moderation',
                'icon'  => 'low',
            ],
            'items' => [
                'b2b' => [
                    'right'      => 'moderate_content',
                    'title'      => 'B2B requests',
                    'link'       => 'moderation/administration/' . TYPE_B2B,
                    'icon'       => 'partners',
                    'icon_color' => 'bg-blue',
                ],
                'companies' => [
                    'right'      => 'moderate_content',
                    'title'      => 'Companies',
                    'link'       => 'moderation/administration/' . TYPE_COMPANY,
                    'icon'       => 'building',
                    'icon_color' => 'bg-blue',
                ],
                'items' => [
                    'right'      => 'moderate_content',
                    'title'      => 'Items',
                    'link'       => 'moderation/administration/' . TYPE_ITEM,
                    'icon'       => 'box-close',
                    'icon_color' => 'bg-blue',
                ],
            ],
        ],
    ];
}

/**
 * Compares two float numbers.
 *
 * @param float  $float1   The first number
 * @param float  $float2   The number to compare against the first
 * @param string $operator The operator. Valid options are =, <=, <, >=, >, <>, eq, lt, lte, gt, gte, ne
 * @param float  $epsilon  The operation epsilon
 *
 * @throws \InvalidArgumentException if operator is unknown
 *
 * @return bool
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/h.-Numbers-and-Money#compareFloatNumbers
 */
function compareFloatNumbers($float1, $float2, $operator = '=', $epsilon = 0.01)
{
    $float1 = (float) $float1;
    $float2 = (float) $float2;
    switch ($operator) {
        // equal
        case '=':
        case 'eq':
            if (abs($float1 - $float2) < $epsilon) {
                return true;
            }

            break;
        // less than
        case '<':
        case 'lt':
            if (abs($float1 - $float2) < $epsilon) {
                return false;
            }
                if ($float1 < $float2) {
                    return true;
                }

            break;
        // less than or equal
        case '<=':
        case 'lte':
            if (compareFloatNumbers($float1, $float2, '<', $epsilon) || compareFloatNumbers($float1, $float2, '=', $epsilon)) {
                return true;
            }

            break;
        // greater than
        case '>':
        case 'gt':
            if (abs($float1 - $float2) < $epsilon) {
                return false;
            }

                if ($float1 > $float2) {
                    return true;
                }

            break;
        // greater than or equal
        case '>=':
        case 'gte':
            if (compareFloatNumbers($float1, $float2, '>', $epsilon) || compareFloatNumbers($float1, $float2, '=', $epsilon)) {
                return true;
            }

            break;
        case '<>':
        case '!=':
        case 'ne':
            if (abs($float1 - $float2) > $epsilon) {
                return true;
            }

            break;

        default:
            throw new \InvalidArgumentException("Unknown operator '{$operator}' in compareFloatNumbers()");
    }

    return false;
}

/**
 * Returns the number of words in a string.
 *
 * @param string $str - the string
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [29.10.2021]
 * Reason: refactor method name
 *
 * @return int
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/g.-Strings-and-text#str_word_count_utf8
 */
function str_word_count_utf8($str)
{
    $a = preg_split('/\\W+/u', $str, -1, PREG_SPLIT_NO_EMPTY);

    return count($a);
}

/**
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [29.10.2021]
 * Reason: Code style
 * Reason: refactor method name
 *
 * @param null|mixed $salt
 * @param mixed      $split_length
 */
function get_sha1_token($salt = null, $split_length = 5)
{
    if (is_array($salt)) {
        $salt = implode('', $salt);
    }

    $token = sha1($salt . uniqid(rand()));

    if (false != $split_length) {
        $token = implode('-', str_split($token, $split_length));
    }

    return $token;
}

/**
 * Get the subdomain url.
 *
 * @param string $subDomain
 * @param string $uri
 *
 * @return string
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/c.-Urls,-Http,-Domains#getSubDomainURL
 */
function getSubDomainURL($subDomain = '', $uri = '')
{
    $url = __HTTP_S . "{$subDomain}." . __HTTP_HOST_ORIGIN;
    if (!empty($uri)) {
        $url .= "/{$uri}";
    }

    return $url;
}

/**
 * @author Bendiucov Tatiana
 *
 * @deprecated [28.10.2021]
 * Reason: there is new model
 *
 * @param mixed $page
 * @param mixed $total_records
 * @param mixed $limit
 */
function getDbQueryLimit($page = 1, $total_records = 0, $limit = 0)
{
    if ($limit <= 0) {
        return 0;
    }

    $pages = ceil($total_records / $limit);

    if ($page > $pages) {
        $page = $pages;
    }

    $start = ($page - 1) * $limit;
    if ($start < 0) {
        $start = 0;
    }

    return "{$start}, {$limit}";
}

/**
 * Returns the link to the flag image based on country name.
 *
 * @param string $countryName
 *
 * @return string
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/q.-Output#getCountryFlag
 */
function getCountryFlag($countryName = '')
{
    $flag = "public/img/flags/" . strForURL(capitalWord($countryName), '-', false) . '.svg';

    if (!file_exists($flag)) {
        $flag = "public/img/flags/none.svg";
    }

    return $flag;
}

/**
 * Gets the interval between two dates.
 *
 * @param string $start     - start date
 * @param string $end       - end date
 * @param string $format    - the format of the start and end date sent as parameters
 * @param string $separator - the separator between dates to output
 *
 * @return string - the interval
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/i.-Date-and-Time#getDateTimeInterval
 */
function getDateTimeInterval($start = 0, $end = 0, $format = 'Y-m-d H:i:s', $separator = '-')
{
    if (empty($start) || empty($end)) {
        return false;
    }

    $startDate = \DateTime::createFromFormat($format, $start);
    $endDate = \DateTime::createFromFormat($format, $end);
    $startYear = $startDate->format('Y');
    $endYear = $endDate->format('Y');
    $startMonth = $startDate->format('M');
    $endMonth = $endDate->format('M');
    $startDay = $startDate->format('d');
    $endDay = $endDate->format('d');
    $startTime = $startDate->format('g:iA');
    $endTime = $endDate->format('g:iA');
    $start = $startDate->format('d M - g:iA');
    $end = $endDate->format('d M - g:iA, Y');

    if ($startYear === $endYear && $startMonth === $endMonth && $startDay === $endDay) {
        return $startDate->format('l, d M, Y - ') . implode($separator, array_filter([$startTime, $endTime]));
    }

    if ($startYear !== $endYear) {
        $start = $startDate->format('d M, Y - g:iA');
        $end = $endDate->format('d M, Y - ') . $endTime;
    }

    return implode($separator, array_filter([$start, $end]));
}

/**
 * Gets the interval between two times.
 *
 * @param string $start     - start date
 * @param string $end       - end date
 * @param string $format    - the format of the start and end date sent as parameters
 * @param string $separator - the separator between dates to output
 *
 * @return string - the time interval
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/i.-Date-and-Time#getTimeInterval
 */
function getTimeInterval($start = 0, $end = 0, $format = 'Y-m-d H:i:s', $separator = '-')
{
    if (empty($start) || empty($end)) {
        return false;
    }

    $startDate = \DateTime::createFromFormat($format, $start);
    $endDate = \DateTime::createFromFormat($format, $end);
    $startYear = $startDate->format('Y');
    $endYear = $endDate->format('Y');
    $startMonth = $startDate->format('M');
    $endMonth = $endDate->format('M');
    $startDay = $startDate->format('d');
    $endDay = $endDate->format('d');
    $end = $endDate->format('d M, Y');

    if ($startYear === $endYear && $startMonth === $endMonth && $startDay === $endDay) {
        return $startDate->format('d M, Y');
    }
    if ($startYear !== $endYear) {
        $start = $startDate->format('d M, Y');
    } elseif ($startMonth !== $endMonth) {
        $start = $startDate->format('d M');
    } else {
        $start = $startDate->format('d');
    }

    return implode($separator, array_filter([$start, $end]));
}

/**
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [29.10.2021]
 * Reason: Code style
 * Reason: refactor method name
 *
 * @param mixed $str
 * @param mixed $order
 */
function wrap_mb_reverse($str, $order)
{
    if (rand(0, 1)) {
        $str = strrev($str);
        $open_tag_span = '<span class="easb__direction flex-order' . $order . '">';
    } else {
        $open_tag_span = '<span class="flex-order' . $order . '">';
    }

    $result = [];
    for ($i = 0, $len = strlen($str); $i < $len; ++$i) {
        if (rand(0, 1)) {
            $result[] = '&#' . ord($str[$i]) . ';';
        } else {
            $result[] = $str[$i];
        }
    }

    return $open_tag_span . join($result) . '</span>';
}

/**
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [29.10.2021]
 * Reason: Code style
 *
 * @param mixed $email_address
 * @param mixed $classes
 */
function antispambot($email_address = '', $classes = '')
{
    $result = [];

    $piece = '';
    $order = 0;
    for ($i = 0, $len = strlen($email_address); $i < $len; ++$i) {
        if (0 == rand(0, 4)) {
            $result[] = wrap_mb_reverse($piece, ++$order);
            $piece = '';
        }

        $piece .= $email_address[$i];
    }
    $result[] = wrap_mb_reverse($piece, ++$order);
    shuffle($result);

    $email_no_spam_address = '<div class="easb ' . $classes . '">' . implode($result) . '</div>';

    return str_replace('@', '&#64;', $email_no_spam_address);
}

/**
 * Returns the current user's profile link.
 *
 * @return string
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/p.-Account-related-helpers#getMyProfileLink
 */
function getMyProfileLink()
{
    return getUserLink(user_name_session(), id_session(), userGroupType());
}

/**
 * Returns the user profile link depending on his name, id and group type.
 *
 * @param string $fullName
 * @param int    $id
 * @param string $groupType - buyer, seller, shipper, cr affiliate
 *
 * @return string
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/p.-Account-related-helpers#getUserLink
 */
function getUserLink($fullName, $id, $groupType)
{
    $groupType = strtolower($groupType);

    $link = __SITE_URL;
    switch ($groupType) {
        case 'buyer':
        case 'seller':
        case 'shipper':
            $link .= 'usr/' . strForURL($fullName) . '-' . $id;

        break;
        case 'cr affiliate':
            $link .= 'country_representative/' . strForURL($fullName) . '-' . $id;

        break;

        default:
            $link = false;

        break;
    }

    return $link;
}

/*
 *
 * @author Bendiucov Tatiana
 * @todo Remove [29.10.2021]
 * Reason: Not used
 */
/*function getLangColumnValue(&$array = array(), $key = "", $lang = __SITE_LANG)
{
    if (!empty($array["{$key}_{$lang}"])) {
        return addslashes($array["{$key}_{$lang}"]);
    }

    return addslashes($array["{$key}_en"]);
}*/

if (!function_exists('is_arrayable')) {
    /**
     * @author Bendiucov Tatiana
     *
     * @deprecated [28.10.2021]
     * Reason: No need for such method
     *
     * @param mixed $value
     */
    function is_arrayable($value)
    {
        return is_array($value)
            || (is_object($value) && method_exists($value, 'toArray'))
            || $value instanceof \ArrayObject
            || $value instanceof \IteratorAggregate
            || $value instanceof \stdClass
            || is_scalar($value)
            || is_resource($value);
    }
}

if (!function_exists('arrayable_to_array')) {
    /**
     * @author Bendiucov Tatiana
     *
     * @deprecated [28.10.2021]
     * Reason: No need for such method
     *
     * @param mixed $arrayble
     */
    function arrayable_to_array($arrayble)
    {
        if (is_array($arrayble)) {
            return $arrayble;
        }
        if (is_object($arrayble) && method_exists($arrayble, 'toArray')) {
            return $arrayble->toArray();
        }
        if ($arrayble instanceof \ArrayObject) {
            return $arrayble->getArrayCopy();
        }

        return $arrayble instanceof \IteratorAggregate ? iterator_to_array($arrayble) : (array) $arrayble;
    }
}

if (!function_exists('accreditation_i18n')) {
    /**
     * @author Bendiucov Tatiana
     *
     * @todo Refactoring [29.10.2021]
     * Reason: Code style
     * Reason: refactor method name
     *
     * @param mixed      $i18n
     * @param mixed      $column
     * @param null|mixed $lang
     * @param null|mixed $default_value
     * @param mixed      $default_lang
     */
    function accreditation_i18n(&$i18n, $column, $lang = null, $default_value = null, $default_lang = 'en')
    {
        if (!is_arrayable($i18n)) {
            return $default_value;
        }
        if (is_string($i18n)) {
            $decoded_i18n = json_decode($i18n, true);
            if (is_string($decoded_i18n) || json_last_error() || null === $decoded_i18n) {
                return $default_value;
            }

            $i18n = $decoded_i18n;
        } else {
            $i18n = arrayable_to_array($i18n);
        }

        $value = null;
        $lang = null !== $lang ? $lang : __SITE_LANG;
        if (!empty($i18n[$lang][$column]['value'])) {
            $value = $i18n[$lang][$column]['value'];
        } elseif (!empty($i18n[$default_lang][$column]['value'])) {
            $value = $i18n[$default_lang][$column]['value'];
        }

        return !empty($value) ? $value : $default_value;
    }
}

if (!function_exists('record_i18n')) {
    /**
     * @author Bendiucov Tatiana
     *
     * @todo Refactoring [29.10.2021]
     * Reason: Code style
     * Reason: refactor method name
     *
     * @param mixed      $i18n
     * @param mixed      $column
     * @param null|mixed $lang
     * @param null|mixed $default_value
     * @param mixed      $default_lang
     */
    function record_i18n(&$i18n, $column, $lang = null, $default_value = null, $default_lang = 'en')
    {
        if (!is_arrayable($i18n)) {
            return $default_value;
        }
        if (is_string($i18n)) {
            $decoded_i18n = json_decode($i18n, true);
            if (is_string($decoded_i18n) || json_last_error() || null === $decoded_i18n) {
                return $default_value;
            }

            $i18n = $decoded_i18n;
        } else {
            $i18n = arrayable_to_array($i18n);
        }

        $value = null;
        $lang = null !== $lang ? $lang : __SITE_LANG;
        if (!empty($i18n[$lang][$column])) {
            $value = $i18n[$lang][$column];
        } elseif (!empty($i18n[$default_lang][$column])) {
            $value = $i18n[$default_lang][$column];
        }

        return !empty($value) ? $value : value($default_value);
    }
}

if (!function_exists('payment_method_i18n')) {
    /**
     * @author Bendiucov Tatiana
     *
     * @todo Refactoring [29.10.2021]
     * Reason: Code style
     * Reason: refactor method name
     *
     * @param mixed      $method
     * @param mixed      $column
     * @param null|mixed $lang
     * @param null|mixed $default_value
     */
    function payment_method_i18n($method, $column, $lang = null, $default_value = null)
    {
        if (!isset($method['i18n'])) {
            return isset($method[$column]) ? $method[$column] : value($default_value);
        }

        return record_i18n(
            $method['i18n'],
            $column,
            $lang,
            isset($method[$column]) ? $method[$column] : null
        );
    }
}

if (!function_exists('translationFileKeyI18n')) {
    /**
     * @author Bendiucov Tatiana
     *
     * @todo Refactoring [29.10.2021]
     * Reason: Code style
     * Reason: refactor method name
     *
     * @param mixed      $key
     * @param mixed      $column
     * @param null|mixed $lang
     * @param null|mixed $default_value
     */
    function translationFileKeyI18n(&$key, $column, $lang = null, $default_value = null)
    {
        if (null === $key) {
            return null;
        }

        $value = null;
        $lang = null !== $lang ? $lang : __SITE_LANG;
        if ('en' !== $lang) {
            $i18n = record_i18n(
                $key['translation_localizations'],
                $column,
                $lang
            );

            if (null !== $i18n && !empty($i18n['value'])) {
                $value = $i18n['value'];
            }
        }

        if (null === $value) {
            $value = isset($key[$column]) ? $key[$column] : (
                isset($key["translation_{$column}"])
                    ? $key["translation_{$column}"]
                    : null
            );
        }

        return !empty($value) ? trim($value) : (null !== $default_value ? trim(value($default_value)) : null);
    }
}

/**
 * @author Bendiucov Tatiana
 *
 * @todo Remove [27.10.2021]
 * Reason: Not used
 *
 * @param mixed $info
 */
/*function getEmailTpl($tpl_file = null, $lang = __SITE_LANG){
    if(!$tpl_file){
        return false;
    }

    $default_lang = 'en';
    $view_path = TMVC_MYAPPDIR . 'views' . DS;

    $view_file =  'emails' . DS . $lang . DS . "{$tpl_file}";
    $default_view_file = 'emails' . DS . $default_lang . DS . "{$tpl_file}";
    if(!file_exists($view_path . $view_file.'.php')){
        if(!file_exists($view_path . $default_view_file.'.php')){
            throw new Exception("Unknown file '{$view_path}{$view_file}.php'");
        } else{
            $view_file = $default_view_file;
        }
    }


    return $view_file;
}*/

/**
 * Base64 encode json.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [29.10.2021]
 * Reason: refactor method name
 *
 * @param array $info
 *
 * @return string - the array to encode
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/g.-Strings-and-text#referal_encode
 */
function referal_encode($info = [])
{
    return base64_encode(json_encode($info));
}

/**
 * Inverse of referal_encode. Returns the array that was base64 encoded.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [29.10.2021]
 * Reason: refactor method name
 *
 * @param string $hash - the encoded string
 *
 * @return array
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/g.-Strings-and-text#referal_decode
 */
function referal_decode($hash = '')
{
    return json_decode(base64_decode($hash), true);
}

if (!function_exists('array_unified_diff')) {
    /**
     * Returns the array differences between two arrays.
     *
     * @author Bendiucov Tatiana
     *
     * @todo Refactoring [29.10.2021]
     * Reason: refactor method name
     *
     * @return array
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/f.-Arrays-and-Objects#array_unified_diff
     */
    function array_unified_diff(array $old, array $new)
    {
        $section = array_intersect_key($old, $new);
        if (empty($section)) {
            return [null, null];
        }

        $new = array_diff($new, $old);
        $old = array_intersect_key($section, $new);
        ksort($old);
        ksort($new);

        return [$old, $new];
    }
}

if (!function_exists('get_user_activity_context')) {
    /**
     * @author Bendiucov Tatiana
     *
     * @todo Refactoring [29.10.2021]
     * Reason: Code style
     * Reason: refactor method name
     */
    function get_user_activity_context()
    {
        if (!logged_in()) {
            return [
                'user' => [
                    'fullname' => 'Guest User',
                ],
            ];
        }

        $url = getMyProfileLink();
        $rel_url = substr($url, strlen(__SITE_URL));

        return [
            'user' => [
                'name'     => user_name_session(),
                'email'    => session()->email,
                'group'    => [
                    'id'   => group_session(),
                    'type' => user_group_type(),
                    'name' => group_name_session(),
                ],
                'profile'  => [
                    'url'    => $url,
                    'relUrl' => $rel_url,
                ],
            ],
        ];
    }
}

if (!function_exists('makeItemUrl')) {
    /**
     * Returns the item url.
     *
     * @param int    $id             - the id of the item
     * @param string $title          - the title of the item
     * @param bool   $includeSiteUrl - determine if include URL or not
     *
     * @return string
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/c.-Urls,-Http,-Domains#makeItemUrl
     */
    function makeItemUrl($id, $title = 'Item', $includeSiteUrl = true)
    {
        $prefix_url = $includeSiteUrl ? __SITE_URL : '';

        return $prefix_url . 'item/' . strForURL($title) . "-{$id}";
    }
}

/*
 * @author Bendiucov Tatiana
 * @todo Remove [27.10.2021]
 * Reason: Not used
 */
/*if (!function_exists('makeOrderedItemUrl')) {
    function makeOrderedItemUrl(int $id, string $title = "Item", string $group = null): string
    {
        return getUrlForGroup('items/ordered/' . strForURL($title) . "-{$id}", $group);
    }
}*/

if (!function_exists('startsWith')) {
    /**
     * Checks if string starts with a certain substring.
     *
     * @param string $haystack - the string to into
     * @param string $needle   - the substring to find
     *
     * @return bool
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/g.-Strings-and-text#startsWith
     */
    function startsWith($haystack, $needle)
    {
        $length = strlen($needle);

        return substr($haystack, 0, $length) === $needle;
    }
}

if (!function_exists('endsWith')) {
    /**
     * Checks if string ends with a certain substring.
     *
     * @param string $haystack - the string to into
     * @param string $needle   - the substring to find
     *
     * @return bool
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/g.-Strings-and-text#endsWith
     */
    function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        if (0 == $length) {
            return true;
        }

        return substr($haystack, -$length) === $needle;
    }
}

if (!function_exists('value')) {
    /**
     * Return the default value of the given value.
     *
     * @param mixed $value
     *
     * @return mixed
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/q.-Output#value
     */
    function value($value)
    {
        return $value instanceof \Closure ? $value() : $value;
    }
}

if (!function_exists('env')) {
    /**
     * Gets the value of an environment variable.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @source https://github.com/laravel/framework/blob/5.5/src/Illuminate/Support/helpers.php
     *
     * @return mixed
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/q.-Output#env
     */
    function env($key, $default = null)
    {
        $value = array_key_exists($key, $_ENV)
            ? $_ENV[$key]
            : (
                array_key_exists($key, $_SERVER)
                    ? $_SERVER[$key]
                    : getenv($key)
            );

        if (false === $value) {
            return value($default);
        }

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return;
        }
        if (strlen($value) > 1 && startsWith($value, '"') && endsWith($value, '"')) {
            return substr($value, 1, -1);
        }

        return $value;
    }
}

if (!function_exists('config')) {
    /**
     * Returns the config item by key.
     *
     * @author Bendiucov Tatiana
     *
     * @todo Refactoring [29.10.2021]
     * Reason: Code style
     *
     * @param null|string $key
     * @param null|mixed  $default
     *
     * @return mixed
     */
    function config($key = null, $default = null)
    {
        $app = tmvc::instance();
        $config = !empty($app->my_config) ? $app->my_config : [];

        return dataGet($config, $key, $default);
    }
}

if (!function_exists('app')) {
    /**
     * Return current active controller.
     *
     * @return \TinyMVC_Controller
     *
     * @author Bendiucov Tatiana
     *
     * @deprecated [29.10.2021]
     * Reason: Old method
     * @see controller()
     */
    function app()
    {
        return controller();
    }
}

if (!function_exists('container')) {
    /**
     * Return the application container.
     *
     * @return mixed
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/n.-System#container
     */
    function container(): ContainerInterface
    {
        return tmvc::instance()->getContainer();
    }
}

if (!function_exists('controller')) {
    /**
     * Return current active controller.
     *
     * @return \TinyMVC_Controller
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/n.-System#controller
     */
    function controller()
    {
        return container()->get(TinyMVC_Controller::class);
    }
}

if (!function_exists('request')) {
    /**
     * Return the incomming request.
     *
     * @return \App\Common\Http\Request
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/n.-System#request
     */
    function request()
    {
        return tmvc::instance()->getRequest();
    }
}

if (!function_exists('database')) {
    /**
     * Return the database connection.
     *
     * @param mixed $poolname
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/n.-System#database
     */
    function database(?string $poolname = null): Connection
    {
        return container()->get(ConnectionRegistry::class)->getConnection($poolname);
    }
}

if (!function_exists('requestStack')) {
    /**
     * Return the incomming request.
     *
     * @return RequestStack
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/n.-System#requestStack
     */
    function requestStack()
    {
        return container()->get(RequestStack::class);
    }
}

if (!function_exists('library')) {
    /**
     * Loads and returns an instanse of the TMVC library.
     *
     * @param string      $className
     * @param null|string $alias
     *
     * @return null|object
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/n.-System#requestStack
     */
    function library($className, $alias = null)
    {
        if (empty($alias)) {
            $alias = $className;
        }
        $className = ucfirst($className);
        if (!startsWith(strtolower($className), 'tinymvc_library_')) {
            $className = "TinyMVC_Library_{$className}";
        }

        $container = container();
        /** @var LibraryLocator */
        $locator = $container->get(LibraryLocator::class);
        if (!$locator->has($className)) {
            $container->get(TinyMVC_Load::class)->library($className, $alias);
        }

        return $locator->get(strtolower($className));
    }
}

if (!function_exists('model')) {
    /**
     * Loads and returns an instanse of the TMVC model.
     *
     * @param string      $className
     * @param null|string $alias
     * @param null|string $connection
     *
     * @return null|BaseModel|Model|TinyMVC_Model
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/n.-System#model
     */
    function model($className, $alias = null, $connection = null)
    {
        if (empty($alias)) {
            $alias = $className;
        }

        $className = ucfirst($className);
        if (!endsWith(strtolower((string) $className), '_model')) {
            $className = "{$className}_Model";
        }

        $container = container();
        /** @var ModelLocator */
        $locator = $container->get(ModelLocator::class);
        if (!$locator->has($className)) {
            $container->get(TinyMVC_Load::class)->model($className, $alias, null, $connection);
        }

        return $locator->get(strtolower($className));
    }
}

if (!function_exists('session')) {
    /**
     * Returns an instanse of the TMVC session.
     *
     * @return \TinyMVC_Library_Session
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/n.-System#session
     */
    function session()
    {
        return library(TinyMVC_Library_Session::class);
    }
}

if (!function_exists('cookies')) {
    /**
     * Returns an instanse of the TMVC cookies.
     *
     * @return \TinyMVC_Library_Cookies
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/n.-System#cookies
     */
    function cookies()
    {
        return library(TinyMVC_Library_Cookies::class);
    }
}

if (!function_exists('views')) {
    /**
     * Returns an instanse of the TMVC views handler.
     *
     * @param string|string[] $path
     *
     * @return \TinyMVC_View
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/n.-System#views
     */
    function views($path = null, array $vars = null)
    {
        return tap(container()->get(TinyMVC_View::class), function (TinyMVC_View $views) use ($path, $vars) {
            if (null !== $vars) {
                $views->assign($vars);
            }

            if (null !== $path) {
                foreach ((array) $path as $path) {
                    $views->display($path);
                }
            }
        });
    }
}

if (!function_exists('uri')) {
    /**
     * Returns an instanse of the TMVC views handler.
     *
     * @return \TinyMVC_Library_URI
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/n.-System#uri
     */
    function uri()
    {
        return library(TinyMVC_Library_URI::class);
    }
}

if (!function_exists('tap')) {
    /**
     * Call the given Closure with the given value then return the value.
     *
     * @param mixed         $value
     * @param null|callable $callback
     *
     * @return mixed
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/n.-System#tap
     */
    function tap($value, $callback = null)
    {
        if (null === $callback) {
            return new HigherOrderTapProxy($value);
        }
        $callback($value);

        return $value;
    }
}

if (!function_exists('with')) {
    /**
     * Return the given value, optionally passed through the given callback.
     *
     * @param mixed         $value
     * @param null|callable $callback
     *
     * @return mixed
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/n.-System#with
     */
    function with($value, $callback = null)
    {
        if (null === $callback || !is_callable($callback)) {
            return $value;
        }

        return $callback($value);
    }
}

if (!function_exists('dt_ordering')) {
    /**
     * Maps columns from datatable request to ordered table columns.
     *
     * @author Bendiucov Tatiana
     *
     * @deprecated [29.10.2021]
     * Reason: Old method
     * @see dtOrdering
     *
     * @param mixed $source
     *
     * @throws InvalidArgumentException if the datatble request parameters contain invalid or malformed values
     */
    function dt_ordering($source, array $map = [], Closure $transformer = null)
    {
        return dtOrdering($source, $map, $transformer);
    }
}

if (!function_exists('flat_dt_ordering')) {
    /**
     * @author Bendiucov Tatiana
     *
     * @deprecated [29.10.2021]
     * Reason: Old method
     * @see dtOrdering
     *
     * @param mixed $source
     */
    function flat_dt_ordering($source, array $map = [])
    {
        return dt_ordering($source, $map, function ($order) {
            return "{$order['column']}-{$order['direction']}";
        });
    }
}

if (!function_exists('dtOrdering')) {
    /**
     * Datatable ordering helper method that uses the source and transforms it to query like sorting needed in the model.
     *
     * @param array $source
     * @param array $map         - the name of the column with the name in the database table
     * @param mixed $transformer
     *
     * @return array
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/l.-Datatables#dtOrdering
     */
    function dtOrdering($source, array $map = [], Closure $transformer = null, bool $isLegacyMode = false)
    {
        $isLegacy = $isLegacyMode || isset($source['iSortingCols']);
        foreach (!$isLegacy ? $source : [] as $key => $value) {
            if (
                startsWith('sSortDir_', $key)
                || startsWith('mDataProp_', $key)
            ) {
                $isLegacy = true;

                break;
            }
        }

        $ordering = [];
        if (!$isLegacy) {
            $columns = $source['columns'] ?? [];
            foreach ($source['order'] ?? [] as list('column' => $columnNumber, 'dir' => $direction)) {
                if (!isset($columns[$columnNumber])) {
                    continue;
                }

                $column = $columns[$columnNumber];
                $columnDirection = mb_strtolower($direction);
                $columnKey = $column['name'] ?: ($column['data'] ?? null);
                if (
                    null === $columnKey
                    || !in_array($columnDirection, ['asc', 'desc'], true)
                    || !isset($map[$columnKey])
                ) {
                    continue;
                }

                $ordering[] = [
                    'column'    => $map[$columnKey],
                    'direction' => $columnDirection,
                ];
            }

            return $ordering;
        }

        $sortingColumnsAmount = isset($source['iSortingCols']) ? (int) $source['iSortingCols'] : 0;
        if ($sortingColumnsAmount <= 0) {
            return [];
        }

        for ($i = 0; $i < $sortingColumnsAmount; ++$i) {
            $column_direction_key = "sSortDir_{$i}";
            $column_index_key = "iSortCol_{$i}";
            if (
                !isset($source[$column_direction_key])
                || !isset($source[$column_index_key])
            ) {
                continue;
            }

            $column_direction = mb_strtolower($source[$column_direction_key]);
            $column_index = (int) $source[$column_index_key];
            // $raw_column_index = $source[$column_index_key] ?? null;
            // if (null == $raw_column_index || !is_numeric($raw_column_index) || (string) ($column_index = (int) $raw_column_index) !== $raw_column_index) {
            //     throw new InvalidArgumentException(
            //         sprintf('The column "%s" contains malformed value.', "iSortCol_{$i}")
            //     );
            // }

            $column_key = "mDataProp_{$column_index}";
            if (
                !isset($source[$column_key])
                || !in_array($column_direction, ['asc', 'desc'], true)
            ) {
                continue;
            }

            $column_alias = $source[$column_key];
            if (!isset($map[$column_alias])) {
                continue;
            }

            $ordering[] = [
                'column'    => $map[$column_alias],
                'direction' => $column_direction,
            ];
        }

        return $transformer instanceof \Closure ? array_map($transformer, $ordering) : $ordering;
    }
}

if (!function_exists('dataGet')) {
    /**
     * Get an item from an array or object using "dot" notation.
     * Imported from Laravel Support helpers.
     *
     * @param mixed            $target
     * @param array|int|string $key
     * @param mixed            $default
     *
     * @return mixed
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/f.-Arrays-and-Objects#dataGet
     */
    function dataGet($target, $key, $default = null)
    {
        if (is_null($key)) {
            return $target;
        }
        $key = is_array($key) ? $key : explode('.', $key);
        while (null !== ($segment = array_shift($key))) {
            if ('*' === $segment) {
                if (!isArrayable($target)) {
                    return value($default);
                }

                $result = [];
                foreach ($target as $item) {
                    $result[] = dataGet($item, $key);
                }

                return in_array('*', $key) ? arrayCollapse($result) : $result;
            }

            if (isArrayable($target) && arrayableKeyExists($target, $segment)) {
                $target = $target[$segment];
            } elseif (is_object($target) && isset($target->{$segment})) {
                $target = $target->{$segment};
            } else {
                return value($default);
            }
        }

        return $target;
    }
}

if (!function_exists('arrayGet')) {
    /**
     * Get an item from an array using "dot" notation.
     * Imported from Laravel Support helper.
     *
     * @param array|\ArrayAccess $array
     * @param string             $key
     * @param mixed              $default
     *
     * @return mixed
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/f.-Arrays-and-Objects#arrayGet
     */
    function arrayGet($array, $key, $default = null)
    {
        if (!isArrayable($array)) {
            return value($default);
        }
        if (null === $key) {
            return $array;
        }

        if (arrayableKeyExists($array, $key)) {
            return $array[$key];
        }

        if (false === strpos($key, '.')) {
            return isset($array[$key]) ? $array[$key] : value($default);
        }

        foreach (explode('.', $key) as $segment) {
            if (isArrayable($array) && arrayableKeyExists($array, $segment)) {
                $array = $array[$segment];
            } else {
                return value($default);
            }
        }

        return $array;
    }
}

if (!function_exists('arrayHas')) {
    /**
     * Check if array has item(s) using "dot" notation.
     * Imported from Laravel Support helper.
     *
     * @param array|\ArrayAccess $array
     * @param string|string[]    $keys
     *
     * @return bool
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/f.-Arrays-and-Objects#arrayHas
     */
    function arrayHas($array, $keys)
    {
        $keys = (array) $keys;
        if (!isArrayable($array) || [] === $keys) {
            return false;
        }

        foreach ($keys as $key) {
            $target = $array;
            if (arrayableKeyExists($target, $key)) {
                continue;
            }

            foreach (explode('.', $key) as $segment) {
                if (isArrayable($target) && arrayableKeyExists($target, $segment)) {
                    $target = $target[$segment];
                } else {
                    return false;
                }
            }
        }

        return true;
    }
}

if (!function_exists('arrayPluck')) {
    /**
     * Pluck an array of values from an array.
     * Imported from laravel Support helper.
     *
     * @param array             $array
     * @param array|string      $value
     * @param null|array|string $key
     *
     * @return array
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/f.-Arrays-and-Objects#arrayPluck
     */
    function arrayPluck($array, $value, $key = null)
    {
        $key = null === $key || is_array($key) ? $key : explode('.', $key);
        $value = is_string($value) ? explode('.', $value) : $value;
        $results = [];
        foreach ($array as $item) {
            $itemValue = dataGet($item, $value);
            // If the key is "null", we will just append the value to the array and keep
            // looping. Otherwise we will key the array using the value of the key we
            // received from the developer. Then we'll return the final array form.
            if (null === $key) {
                $results[] = $itemValue;
            } else {
                $itemKey = dataGet($item, $key);
                if (is_object($itemKey) && method_exists($itemKey, '__toString')) {
                    $itemKey = (string) $itemKey;
                }

                $results[$itemKey] = $itemValue;
            }
        }

        return $results;
    }
}

if (!function_exists('arrayPull')) {
    /**
     * Get a value from the array, and remove it.
     * Imported from laravel Support helper.
     *
     * @param array  $array
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/f.-Arrays-and-Objects#arrayPull
     */
    function arrayPull(&$array, $key, $default = null)
    {
        $value = arrayGet($array, $key, $default);
        arrayForget($array, $key);

        return $value;
    }
}

if (!function_exists('arrayForget')) {
    /**
     * Remove one or many array items from a given array using "dot" notation.
     * Imported from laravel Support helper.
     *
     * @param array        $array
     * @param array|string $keys
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/f.-Arrays-and-Objects#arrayForget
     */
    function arrayForget(&$array, $keys)
    {
        $original = &$array;
        $keys = (array) $keys;

        if (0 === count($keys)) {
            return;
        }

        foreach ($keys as $key) {
            // if the exact key exists in the top-level, remove it
            if (arrayableKeyExists($array, $key)) {
                unset($array[$key]);

                continue;
            }

            // clean up before each pass
            $array = &$original;
            $parts = explode('.', $key);
            while (count($parts) > 1) {
                $part = array_shift($parts);
                if (isset($array[$part]) && is_array($array[$part])) {
                    $array = &$array[$part];
                } else {
                    continue 2;
                }
            }

            unset($array[array_shift($parts)]);
        }
    }
}

if (!function_exists('arrayCollapse')) {
    /**
     * Collapse an array of arrays into a single array.
     * Imported from Laravel Support helper.
     *
     * @param array $array
     *
     * @return array
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/f.-Arrays-and-Objects#arrayForget
     */
    function arrayCollapse($array)
    {
        $results = [];
        foreach ($array as $values) {
            if (!is_array($values)) {
                continue;
            }

            $results = array_merge($results, $values);
        }

        return $results;
    }
}

if (!function_exists('arrayDot')) {
    /**
     * Flatten a multi-dimensional associative array with dots.
     * Imported from Laravel Support helper.
     *
     * @param array  $array
     * @param string $prepend
     *
     * @return array
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/f.-Arrays-and-Objects#arrayDot
     */
    function arrayDot($array, $prepend = '')
    {
        $results = [];
        foreach ($array as $key => $value) {
            if (is_array($value) && !empty($value)) {
                $results = array_merge($results, arrayDot($value, $prepend . $key . '.'));
            } else {
                $results[$prepend . $key] = $value;
            }
        }

        return $results;
    }
}

/*
 * @author Bendiucov Tatiana
 * @todo Remove [29.10.2021]
 * Reason: Not used
 */
/*
if (!function_exists('arrayPrefixAssocKeys')) {
    function arrayPrefixAssocKeys($array, string $prefix): array
    {
        if (!isArrayable($array)) {
            return array();
        }

        $output = array();
        foreach ($array as $key => $value) {
            if (!is_numeric($key)) {
                $key = "{$prefix}{$key}";
            }

            $output[$key] = $value;
        }

        return $output;
    }
}*/

if (!function_exists('arrayCamelizeAssocKeys')) {
    /**
     * Transforms the array keys from snake case to camelCase.
     *
     * @param array $array - the array to process
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/f.-Arrays-and-Objects#arrayCamelizeAssocKeys
     */
    function arrayCamelizeAssocKeys($array): array
    {
        if (!isArrayable($array)) {
            return [];
        }

        $output = [];
        foreach ($array as $key => $value) {
            if (!is_numeric($key)) {
                $key = camelCase($key);
            }

            $output[$key] = $value;
        }

        return $output;
    }
}
/*
 * @author Bendiucov Tatiana
 * @todo Remove [29.10.2021]
 * Reason: Not used
 */
/*if (!function_exists('arrayDeepFlatten')) {
    function arrayDeepFlatten($array, $delimiter = '.', $depth = INF)
    {
        if (empty($array) || !isArrayable($array)) {
            return array();
        }

        $output = array();
        foreach ($array as $key => $value) {
            if (!is_string($value) && is_callable($value)) {
                if ($value instanceof \Closure) {
                    $flatValue = \Closure::class . '::__invoke()';
                } else {
                    try {
                        is_callable($value, false, $callableName);
                        $flatValue = "{$callableName}()";
                    } catch (\ErrorException $exception) {
                        if (is_array($value)) {
                            list($classInstance, $methodName) = $value;
                            $flatValue = get_class($classInstance) . "::{$methodName}()";
                        }
                    }
                }

                $output[] = "{$key}{$delimiter}{$flatValue}";
            } else if (is_array($value)) {
                if ($depth - 1 === 0) {
                    continue;
                }

                $output = array_merge(
                    $output,
                    array_map(
                        function ($value) use ($delimiter, $key) { return "{$key}{$delimiter}{$value}"; },
                        arrayDeepFlatten($value, $delimiter, $depth - 1)
                    )
                );
            } else {
                $flatValue = $value;
                if (is_float($value) && is_nan($value)) {
                    $flatValue = 'NaN';
                }
                if (is_null($value)) {
                    $flatValue = 'NULL';
                }
                if (is_bool($value)) {
                    $flatValue = $value ? 'TRUE' : 'FALSE';
                }
                if (is_object($value)) {
                    $flatValue = method_exists($value, '__toString') ? (string) $value : get_class($value) . " Object()";
                }

                $output[] = "{$key}{$delimiter}{$flatValue}";
            }
        }

        return $output;
    }
}*/

if (!function_exists('arrayableKeyExists')) {
    /**
     * Check if arrayble has a key.
     *
     * @param array|\ArrayAccess $array
     * @param string             $key
     *
     * @return bool
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/f.-Arrays-and-Objects#arrayableKeyExists
     */
    function arrayableKeyExists($array, $key)
    {
        if (is_array($array)) {
            return array_key_exists($key, $array);
        }

        return $array instanceof ArrayAccess && $array->offsetExists($key);
    }
}

if (!function_exists('isArrayable')) {
    /**
     * Check if value is arrayable.
     *
     * @param array|\ArrayAccess $array
     *
     * @return bool
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/f.-Arrays-and-Objects#isArrayable
     */
    function isArrayable($array)
    {
        return is_array($array) || $array instanceof \ArrayAccess;
    }
}

if (!function_exists('checkIsAjax')) {
    /**
     * Checks if request is ajax and returns 404 if not.
     *
     * @return null|404
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/d.-Checks-and-permissions#checkIsAjax
     */
    function checkIsAjax()
    {
        if (!isAjaxRequest()) {
            show_404();
        }
    }
}

if (!function_exists('checkHaveCompany')) {
    /**
     * Checks if current user has company, if not redirects.
     *
     * @return bool|redirect
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/d.-Checks-and-permissions#checkHaveCompany
     */
    function checkHaveCompany()
    {
        if (!i_have_company()) {
            session()->setMessages(translate('systmess_error_must_have_company_to_access_page'), 'errors');
            headerRedirect(__SITE_URL);
        }

        return true;
    }
}

if (!function_exists('checkHaveCompanyAjax')) {
    /**
     * Checks if current user has company for ajax requests, if not returns jsonResponse error.
     *
     * @return bool|json
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/d.-Checks-and-permissions#checkHaveCompanyAjax
     */
    function checkHaveCompanyAjax()
    {
        if (!i_have_company()) {
            jsonResponse(translate('systmess_error_should_have_company_to_perform_this_action'), 'errors');
        }

        return true;
    }
}

if (!function_exists('checkHaveCompanyAjaxModal')) {
    /**
     * Checks if current user has company for popups, if not returns messageInModal error.
     *
     * @return bool|html
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/d.-Checks-and-permissions#checkHaveCompanyAjaxModal
     */
    function checkHaveCompanyAjaxModal()
    {
        if (!i_have_company()) {
            messageInModal(translate('systmess_error_should_have_company_to_perform_this_action'), 'errors');
        }

        return true;
    }
}

if (!function_exists('checkHaveCompanyAjaxDT')) {
    /**
     * Checks if current user has company for datatables, if not returns json response error for DT.
     *
     * @return bool|json
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/d.-Checks-and-permissions#checkHaveCompanyAjaxDT
     */
    function checkHaveCompanyAjaxDT()
    {
        if (!i_have_company()) {
            jsonDTResponse(translate('systmess_error_should_have_company_to_perform_this_action'), 'errors');
        }

        return true;
    }
}

if (!function_exists('checkHaveShipperCompany')) {
    /**
     * Checks if current user is shipper and has company , if not redirects.
     *
     * @return bool|redirect
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/d.-Checks-and-permissions#checkHaveShipperCompany
     */
    function checkHaveShipperCompany()
    {
        if (!i_have_shipper_company()) {
            session()->setMessages(translate('systmess_error_must_have_company_to_access_page'), 'errors');
            headerRedirect(__SITE_URL);
        }

        return true;
    }
}

if (!function_exists('checkHaveShipperCompanyAjax')) {
    /**
     * Checks if current user is shipper and has company for ajax requests, if not returns jsonResponse.
     *
     * @return bool|redirect
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/d.-Checks-and-permissions#checkHaveShipperCompanyAjax
     */
    function checkHaveShipperCompanyAjax()
    {
        if (!i_have_shipper_company()) {
            jsonResponse(translate('systmess_error_should_have_company_to_perform_this_action'), 'errors');
        }

        return true;
    }
}

if (!function_exists('checkHaveShipperCompanyAjaxModal')) {
    /**
     * Checks if current user is shipper and has company for modals, if not returns messageInModal.
     *
     * @return bool|redirect
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/d.-Checks-and-permissions#checkHaveShipperCompanyAjaxModal
     */
    function checkHaveShipperCompanyAjaxModal()
    {
        if (!i_have_shipper_company()) {
            messageInModal(translate('systmess_error_should_have_company_to_perform_this_action'), 'errors');
        }

        return true;
    }
}

if (!function_exists('checkHaveShipperCompanyAjaxDT')) {
    /**
     * Checks if current user is shipper and has company for datatables, if not returns json response error for DT.
     *
     * @return bool|json
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/d.-Checks-and-permissions#checkHaveShipperCompanyAjaxDT
     */
    function checkHaveShipperCompanyAjaxDT()
    {
        if (!i_have_shipper_company()) {
            jsonDTResponse(translate('systmess_error_should_have_company_to_perform_this_action'), 'errors');
        }

        return true;
    }
}

if (!function_exists('checkIsLogged')) {
    /**
     * Checks if current user is logged, if not then redirects.
     *
     * @param string $getParameters
     *
     * @return bool|json
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/d.-Checks-and-permissions#checkIsLogged
     */
    function checkIsLogged($getParameters = '')
    {
        if (!logged_in()) {
            session()->setMessages(translate('systmess_error_should_be_logged_in'), 'info');
            headerRedirect(__SITE_URL . "login{$getParameters}");
        }

        return true;
    }
}

if (!function_exists('checkIsLoggedAjax')) {
    /**
     * Checks if current user is logged for ajax requests, if not then returns jsonResponse.
     *
     * @return bool|json
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/d.-Checks-and-permissions#checkIsLoggedAjax
     */
    function checkIsLoggedAjax()
    {
        if (!logged_in()) {
            jsonResponse(translate('systmess_error_should_be_logged_in'), 'info');
        }

        return true;
    }
}

if (!function_exists('checkIsLoggedAjaxModal')) {
    /**
     * Checks if current user is logged for modals, if not then returns messageInModal.
     *
     * @return bool|json
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/d.-Checks-and-permissions#checkIsLoggedAjaxModal
     */
    function checkIsLoggedAjaxModal()
    {
        if (!logged_in()) {
            messageInModal(translate('systmess_error_should_be_logged_in'), 'info');
        }

        return true;
    }
}

if (!function_exists('checkIsLoggedAjaxDT')) {
    /**
     * Checks if current user is logged for datatables, if not then returns json for DT.
     *
     * @return bool|json
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/d.-Checks-and-permissions#checkIsLoggedAjaxDT
     */
    function checkIsLoggedAjaxDT()
    {
        if (!logged_in()) {
            jsonDTResponse(translate('systmess_error_should_be_logged_in'), 'info');
        }

        return true;
    }
}

if (!function_exists('removeFileIfExists')) {
    /**
     * Removes a file if exists.
     *
     * @param string $path
     *
     * @return bool
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/a.-FileSystem#removeFileIfExists
     */
    function removeFileIfExists($path)
    {
        if (null !== $path && file_exists($path) && is_file($path)) {
            return @unlink($path);
        }

        return true;
    }
}

if (!function_exists('removeFileByPatternIfExists')) {
    /**
     * Removes a file (files) by pattern sent as second parameter if file(s) exists.
     *
     * @param string $path
     * @param string $pathGlob
     *
     * @return bool
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/a.-FileSystem#removeFileByPatternIfExists
     */
    function removeFileByPatternIfExists($path, $pathGlob)
    {
        if (null !== $path && file_exists($path) && is_file($path)) {
            return array_map('unlink', glob($pathGlob));
        }

        return true;
    }
}

if (!function_exists('getEmailHashToken')) {
    /**
     * Returns email(s) as hash.
     *
     * @param mixed $emails
     *
     * @return string
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/j.-Email-and-Password#getEmailHashToken
     */
    function getEmailHashToken($emails)
    {
        if (is_array($emails)) {
            $emails = implode(',', $emails);
        }

        return get_sha1_token($emails, false);
    }
}

if (!function_exists('strLimit')) {
    /**
     * Limit the number of characters in a string.
     *
     * @param string $value
     * @param int    $limit
     * @param string $end
     *
     * @return string
     *
     * @author Bendiucov Tatiana
     *
     * @deprecated [29.10.2021]
     * Reason: There is another one
     * @see cut_str_with_dots
     */
    function strLimit($value, $limit = 100, $end = '...')
    {
        if (mb_strwidth($value, 'UTF-8') <= $limit) {
            return $value;
        }

        return rtrim(mb_strimwidth($value, 0, $limit, '', 'UTF-8')) . $end;
    }
}

if (!function_exists('dd')) {
    /**
     * Displays the arguments with 500 code and exits.
     *
     * @param mixed ...$args
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/q.-Output#dd
     */
    function dd(...$args)
    {
        http_response_code(500);
        dump(...$args);

        exit;
    }
}

if (!function_exists('ds')) {
    /**
     * Returns dump of the arguments in the string.
     *
     * @param mixed $args
     *
     * @return string
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/q.-Output#ds
     */
    function ds(...$args)
    {
        ob_start();
        dump(...$args);
        $results = ob_get_contents();
        ob_end_clean();

        return $results;
    }
}

if (!function_exists('de')) {
    /**
     * Returns dump of the arguments in the string and exits.
     *
     * @param mixed $args
     *
     * @return string
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/q.-Output#de
     */
    function de(...$args)
    {
        dump(...$args);

        exit;
    }
}

if (!function_exists('studlyCase')) {
    /**
     * Turns a string to studlyCase.
     *
     * @param string $str
     *
     * @return string
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/g.-Strings-and-text#studlyCase
     */
    function studlyCase($str)
    {
        static $studlyCache;
        if (isset($studlyCache[$str])) {
            return $studlyCache[$str];
        }

        return $studlyCache[$str] = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $str)));
    }
}

if (!function_exists('camelCase')) {
    /**
     * Turns a string to camelCase.
     *
     * @param string $str
     *
     * @return string
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/g.-Strings-and-text#camelCase
     */
    function camelCase($str)
    {
        static $camelCase;
        if (isset($camelCase[$str])) {
            return $camelCase[$str];
        }

        return $camelCase[$str] = lcfirst(studlyCase($str));
    }
}

if (!function_exists('snakeCase')) {
    /**
     * Turns a string to snake_case.
     *
     * @param string $str
     * @param mixed  $delimiter
     *
     * @return string
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/g.-Strings-and-text#snakeCase
     */
    function snakeCase($str, $delimiter = '_')
    {
        static $snakeCache;

        $key = $str;
        if (isset($snakeCache[$key][$delimiter])) {
            return $snakeCache[$key][$delimiter];
        }

        if (!ctype_lower($str)) {
            $str = preg_replace('/\s+/u', '', ucwords($str));
            $str = mb_strtolower(preg_replace('/(.)(?=[A-Z])/u', '$1' . $delimiter, $str), 'UTF-8');
        }

        return $snakeCache[$key][$delimiter] = $str;
    }
}

if (!function_exists('concat')) {
    /**
     * Concatenates all received parameters into string.
     *
     * @param string[] $parts
     *
     * @return string
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/g.-Strings-and-text#concat
     */
    function concat(...$parts)
    {
        return !empty($parts) ? implode('', $parts) : '';
    }
}

if (!function_exists('redirect')) {
    /**
     * Header redirect to url.
     *
     * @param string $relativeUrl
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/c.-Urls,-Http,-Domains#redirect
     */
    function redirect($relativeUrl)
    {
        headerRedirect(__SITE_URL . ltrim($relativeUrl, '/'));
    }
}

if (!function_exists('redirectWithMessage')) {
    /**
     * Header redirect to url and set system message to show after redirect.
     *
     * @param string $relativeUrl
     * @param string $message
     * @param string $type
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/c.-Urls,-Http,-Domains#redirectWithMessage
     */
    function redirectWithMessage($relativeUrl, $message, $type = null)
    {
        session()->setMessages($message, $type);
        headerRedirect(__SITE_URL . ltrim($relativeUrl, '/'));
    }
}

if (!function_exists('checkPermisionAndRedirect')) {
    /**
     * Checks if user is logged in and has right, then redirects if he does have teh right. If not then returns false.
     *
     * @param string $action - action to check if has right tp
     * @param string $path   - where to redirect to
     *
     * @return null|bool
     *
     * @see
     */
    function checkPermisionAndRedirect($action, $path = __SITE_URL)
    {
        if (!logged_in()) {
            session()->setMessages(translate('systmess_error_should_be_logged'), 'errors');
            headerRedirect(__SITE_URL . 'login');
        }

        if (have_right_or($action)) {
            headerRedirect($path);
        }

        return false;
    }
}

if (!function_exists('dtConditionResolveType')) {
    /**
     * Returns the right type of the condition for dt.
     *
     * @param string $type
     * @param string $value
     *
     * @return string
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/l.-Datatables#dtConditionResolveType
     */
    function dtConditionResolveType($type, $value)
    {
        $types = [
            'bool'    => true,
            'boolean' => true,
            'int'     => true,
            'integer' => true,
            'float'   => true,
            'double'  => true,
            'string'  => true,
            'array'   => true,
            'object'  => true,
            'null'    => true,
        ];

        if (is_string($type) && isset($types[$type])) {
            settype($value, $type);

            return $value;
        }

        if ($type instanceof \Closure) {
            return $type($value);
        }

        if (is_callable($type)) {
            return call_user_func_array($type, [$value]);
        }

        if (is_string($type) && strpos($type, ':')) {
            list($typeCallable, $rawArgs) = explode(':', $type, 2);
            if (is_callable($typeCallable)) {
                $args = explode(',', $rawArgs);
                array_unshift($args, $value);

                return call_user_func_array($typeCallable, $args);
            }
        }

        return $value;
    }
}

if (!function_exists('dtConditions')) {
    /**
     * @author Bendiucov Tatiana
     *
     * @todo Refactoring [29.10.2021]
     * Reason: Code style
     *
     * @param mixed $source
     */
    function dtConditions($source, array $map = [], Closure $transformer = null)
    {
        if (
            empty($map)
            || empty($source)
            || !isArrayable($source)
            || !isArrayable($map)
        ) {
            return [];
        }

        $output = [];
        foreach ($map as $condition => $mappingEntry) {
            if (!isset($mappingEntry['as'])) {
                continue;
            }

            $condition = $mappingEntry['as'];
            $key = isset($mappingEntry['key']) ? $mappingEntry['key'] : $condition;
            $types = isset($mappingEntry['type']) ? $mappingEntry['type'] : null;
            $default = isset($mappingEntry['default']) ? $mappingEntry['default'] : null;
            $isNullable = isset($mappingEntry['nullable']) ? (bool) $mappingEntry['nullable'] : false;
            $value = dataGet($source, $key, $default);
            if (!$isNullable && null === $value) {
                continue;
            }
            if (null === $types) {
                $output[$condition] = $value;

                continue;
            }

            $typesPipe = [];
            if (is_string($types)) {
                if (strpos($types, '|')) {
                    $typesPipe = array_filter(explode('|', $types));
                } else {
                    $typesPipe = [$types];
                }
            } elseif (is_array($types) && !is_callable($types)) {
                $typesPipe = array_filter($types);
            } else {
                $typesPipe = [$types];
            }

            foreach ($typesPipe as $type) {
                $value = dtConditionResolveType($type, $value);
            }

            if (!$isNullable && null === $value) {
                continue;
            }

            $output[$condition] = $value;
        }

        return null !== $transformer ? $transformer($output, $source, $map) : $output;
    }
}

if (!function_exists('getBacketItemsKey')) {
    /**
     * Creates and returns a key for the basket items group.
     *
     * @param mixed $sellerId - the id of the seller
     * @param mixed $itemsIds - the ids of the items from group
     *
     * @return null|string - the key
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/q.-Output#getBacketItemsKey
     */
    function getBacketItemsKey($sellerId, $itemsIds)
    {
        if (empty($sellerId) || empty($itemsIds)) {
            return null;
        }

        $keyParts = array_merge([$sellerId], $itemsIds);

        try {
            return Uuid::uuid5(Uuid::NAMESPACE_X500, implode(':::', $keyParts))->toString();
        } catch (\Exception $e) {
            return null;
        }
    }
}

if (!function_exists('getBacketGroupUrl')) {
    /**
     * Returns the url to basket by group.
     *
     * @param string $groupKey - the key generated with getBacketItemsKey
     *
     * @return string - url
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/q.-Output#getBacketGroupUrl
     */
    function getBacketGroupUrl($groupKey)
    {
        if (empty($groupKey)) {
            return __SITE_URL;
        }

        return __SITE_URL . "shippers/estimates_requests/group/{$groupKey}";
    }
}

if (!function_exists('getMimePropertiesFromFormats')) {
    /**
     * Get mime properties from provided comma-separated list of file extensions.
     *
     * @param string $formats comma-separated list of file extensions
     *
     * @return array
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/b.-Images#getMimePropertiesFromFormats
     */
    function getMimePropertiesFromFormats($formats)
    {
        $formats = explode(',', $formats);
        $mimetypes = array_filter(array_unique(array_map(
            function ($extension) {
                return \Hoa\Mime\Mime::getMimeFromExtension($extension);
            },
            $formats
        )));
        $accept = implode(',', $mimetypes);
        $formats = implode('|', $formats);
        $mimetypes = implode('|', array_map(function ($type) { return preg_quote($type); }, $mimetypes));

        return compact('accept', 'formats', 'mimetypes');
    }
}

if (!function_exists('base64UrlEncode')) {
    /**
     * Function base64 encode for url remove not allowed characters.
     *
     * @param string $data
     *
     * @return string
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/g.-Strings-and-text#base64UrlEncode
     */
    function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}

if (!function_exists('base64UrlDecode')) {
    /**
     * Function base64 decode for url remove not allowed characters.
     *
     * @param string $data
     *
     * @return string
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/g.-Strings-and-text#base64UrlDecode
     */
    function base64UrlDecode($data)
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
}

if (!function_exists('json')) {
    /**
     * A format-pure JSON response.
     *
     * @param array $data    HTTP response body
     * @param int   $status  HTTP response code
     * @param array $headers A set of HTTP response headers
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/q.-Output#output
     */
    function json($data = [], $status = 200, array $headers = [])
    {
        $response = new JsonResponse($data, $status, $headers);
        $response->send();

        exit;
    }
}

if (!function_exists('output')) {
    /**
     * A pure text Http response.
     *
     * @param array $data    HTTP response body
     * @param int   $status  HTTP response code
     * @param array $headers A set of HTTP response headers
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/q.-Output#output
     */
    function output($data = '', $status = 200, array $headers = [])
    {
        $response = new Response($data, $status, $headers);
        $response->send();

        exit;
    }
}

/*
 *
 * @author Bendiucov Tatiana
 * @todo Remove [01.11.2021]
 * Reason: Not used
 */
/*if (!function_exists('getExtensionFromName')) {
    function getExtensionFromName($name) {
        if (strpos($name, '.') === false) {
            return null;
        }
        $parts = explode('.', $name);

        return end($parts);
    }
}*/

/*
 *
 * @author Bendiucov Tatiana
 * @todo Remove [01.11.2021]
 * Reason: Not used
 */
/*if (!function_exists('getUniqueFileName')) {
    function getUniqueFileName($extension = null)
    {
        if(null === $extension) {
            return md5(uniqid(rand()));
        }

        return md5(uniqid(rand())) . ".{$extension}";
    }
}*/

if (!function_exists('fileDownloadFromStream')) {
    /**
     * Downloads a file from stream.
     *
     * @param string $resourse  - the stream
     * @param string $name      - name of the file
     * @param string $extension
     * @param string $mime      - type of stream (default octet)
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/q.-Output#fileDownloadFromStream
     */
    function fileDownloadFromStream($resourse, $name, $extension = null, $mime = 'application/octet-stream')
    {
        if (is_resource($resourse)) {
            if (ob_get_level()) {
                ob_end_clean();
            }

            $stats = fstat($resourse);
            $filename = 'downloaded';
            if (!empty($name)) {
                $filename = $name;
            }
            if (!empty($extension)) {
                $filename = "{$filename}.{$extension}";
            }

            header('Content-Description: File Transfer');
            header("Content-Type: {$mime}");
            header("Content-Disposition: attachment; filename={$filename}");
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header("Content-Length: {$stats['size']}");

            echo stream_get_contents($resourse);
            fclose($resourse);
        }
    }
}

if (!function_exists('json_encode_unescaped')) {
    /**
     * Wrapper for JSON encoding with applied unescape options.
     *
     * @author Bendiucov Tatiana
     *
     * @todo Refactoring [27.10.2021]
     * Reason: refactor method name
     *
     * @param mixed $value   The value being encoded
     * @param int   $options JSON encode option bitmask
     * @param int   $depth   Set the maximum depth. Must be greater than zero.
     *
     * @return string
     *
     * @see http://www.php.net/manual/en/function.json-encode.php
     */
    function json_encode_unescaped($value, $options = 0, $depth = 512)
    {
        return json_encode($value, $options | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE, $depth);
    }
}

if (!function_exists('publicPath')) {
    /**
     * Returns the full path to the public directory.
     *
     * @param string $path is a realtive path to the file or directory
     *
     * @return string
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/a.-FileSystem#publicPath
     */
    function publicPath($path = '')
    {
        $relpath = ltrim(ltrim($path, '/'), DIRECTORY_SEPARATOR);

        return realpath(TMVC_BASEDIR . '../public') . ($relpath ? DIRECTORY_SEPARATOR . $relpath : '');
    }
}

if (!function_exists('publicUrl')) {
    /**
     * Returns the url to the public directory.
     *
     * @param string $path    is a relative URL path
     * @param string $baseUrl is a base URL address
     *
     * @return string
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/c.-Urls,-Http,-Domains#publicUrl
     */
    function publicUrl($path, $baseUrl = __SITE_URL)
    {
        $cleaned = ltrim(str_replace('\\', '/', $path), '/');

        return fileModificationTime("public/{$cleaned}", $baseUrl);
    }
}

if (!function_exists('isExpiredDate')) {
    /**
     * Returns true/false after checking if date is expired.
     *
     * @author Bendiucov Tatiana
     *
     * @deprecated [01.11.2021]
     * Old method
     * @see isDateExpired
     *
     * @param DateTimeInterface $check_date is a date to check if expired
     */
    function isExpiredDate(?DateTimeInterface $check_date = null): bool
    {
        if (null == $check_date) {
            return true;
        }

        return $check_date <= new DateTime();
    }
}

if (!function_exists('numericToUsdMoney')) {
    /**
     * Transforms numeric values to the \Money\Money instance in USD currency.
     *
     * @param mixed $amount
     *
     * @return null|\Money\Money
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/h.-Numbers-and-Money#numericToUsdMoney
     */
    function numericToUsdMoney($amount)
    {
        if (!is_numeric($amount)) {
            return null;
        }

        $currency = new Currency('USD');
        $parser = new DecimalMoneyParser(new ISOCurrencies());

        try {
            return $parser->parse((string) $amount, $currency);
        } catch (ParserException $exception) {
            try {
                return $parser->parse((string) ($amount + 0), $currency);
            } catch (ParserException $exception) {
                return null;
            }
        }
    }
}

if (!function_exists('priceToUsdMoney')) {
    /**
     * Transforms price values to the \Money\Money instance in USD currency.
     *
     * @param mixed $amount
     *
     * @return null|\Money\Money
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/h.-Numbers-and-Money#priceToUsdMoney
     */
    function priceToUsdMoney($amount)
    {
        if ($amount instanceof Money) {
            return $amount;
        }

        if (!is_string($amount) && !is_numeric($amount)) {
            return null;
        }

        if (is_numeric($amount)) {
            return numericToUsdMoney($amount);
        }

        $currency = new Currency('USD');
        $currencies = new ISOCurrencies();
        $formatter = new \NumberFormatter('en_US', \NumberFormatter::CURRENCY);
        $parser = new AggregateMoneyParser([
            new IntlMoneyParser($formatter, $currencies),
            new IntlLocalizedDecimalParser($formatter, $currencies),
            new DecimalMoneyParser($currencies),
        ]);

        try {
            return $parser->parse($amount, $currency);
        } catch (ParserException $exception) {
            try {
                return $parser->parse('$' . trim(trim($amount, '$')), $currency);
            } catch (ParserException $exception) {
                try {
                    return $parser->parse((string) ($amount + 0), $currency);
                } catch (ParserException $exception) {
                    return null;
                }
            }
        }
    }
}

if (!function_exists('moneyToDecimal')) {
    /**
     * Transforms the \Money\Money instance to decimal.
     *
     * @param Money $money
     *
     * @return string
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/h.-Numbers-and-Money#moneyToDecimal
     */
    function moneyToDecimal(Money $money = null)
    {
        $formatter = new DecimalMoneyFormatter(new ISOCurrencies());

        return $formatter->format(null === $money ? Money::USD(0) : $money);
    }
}

/**
 * @author Bendiucov Tatiana
 *
 * @todo Remove [01.11.2021]
 * Reason: Not used
 *
 * @param null|mixed $controller
 * @param null|mixed $action
 */
/*if (!function_exists('getCurrentLangDetail')) {
    function getCurrentLangDetail($columns = array())
    {
        $app = tmvc::instance();
        $current_lang_detail = $app->current_lang_detail;

        if (empty($columns)) {
            return $current_lang_detail;
        }

        if (is_string($columns)) {
            return isset($current_lang_detail[$columns]) ? $current_lang_detail[$columns] : null;
        }

        if (is_array($columns)) {
            $result_data = array();

            foreach ($columns as $column) {
                if (isset($current_lang_detail[$column])) {
                    $result_data[$column] = $current_lang_detail[$column];
                }
            }

            return $result_data;
        }

        return null;
    }
}*/

/**
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [01.11.2021]
 * Reason: Code style
 */
function getPageHash($controller = null, $action = null)
{
    $controller = null === $controller ? app()->name : $controller;
    $action = null === $action ? tmvc::instance()->action : $action;

    return md5($controller . $action);
}

/*
 * @author Bendiucov Tatiana
 * @todo Remove [01.11.2021]
 * Reason: Not used
 */
/*function booleanToInt($value)
{
    return (int) filter_var($value, FILTER_VALIDATE_BOOLEAN);
}*/

if (!function_exists('confirmUserAccount')) {
    /**
     * @author Bendiucov Tatiana
     *
     * @todo Refactoring [27.10.2021]
     * Reason: Code style
     *
     * @param mixed $user_id
     * @param mixed $user
     * @param mixed $is_manual
     */
    function confirmUserAccount($user_id, $user, $is_manual = false)
    {
        /** @var User_Model $users */
        $users = model(User_Model::class);
        // Update users statuses

        $users->updateUserMain(
            $user_id,
            array_filter([
                'status'            => 'new' == $user['status'] ? 'pending' : $user['status'],
                'status_temp'       => 'new' == $user['status'] ? null : 'pending',
                'email_confirmed'   => 1,
            ])
        );

        if ($is_manual) {
            $users->set_notice($user_id, [
                'add_date' => date('Y/m/d H:i:s'),
                'add_by'   => user_name_session(),
                'notice'   => sprintf(
                    'The user was confirmed by the administrator %s',
                    user_name_session()
                ),
            ]);
        } else {
            $users->set_notice($user_id, [
                'add_date' => date('Y/m/d H:i:s'),
                'add_by'   => trim(implode(' ', [arrayGet($user, 'fname', 'Unknown'), arrayGet($user, 'lname', 'User')])),
                'notice'   => 'The user was confirmed',
            ]);
        }
    }
}

/**
 * Returns the class of the image, based on type - portrait or landscape.
 *
 * @param string $type - portrait or landscape
 * @param string $url  - the link to the image
 *
 * @return bool|string
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/b.-Images#viewPictureType
 */
function viewPictureType($type = '', $url = '')
{
    if (!empty($type)) {
        if ('portrait' == $type) {
            return 'image--portrait';
        }
        if ('landscape' == $type) {
            return 'image--landscape';
        }
    } elseif (!empty($url)) {
        list($width, $height) = @getimagesize($url);
        if (!$width || !$height) {
            return false;
        }
        if ($width <= $height) {
            return 'image--portrait';
        }
        if ($width > $height) {
            return 'image--landscape';
        }
    }

    return true;
}

if (!function_exists('tokenizeSearchText')) {
    function tokenizeSearchText($text, array $stopwords = [])
    {
        $text = mb_strtolower($text);
        $split = preg_split('/[^\\p{L}\\p{N}\\p{M}]+/u', $text, -1, PREG_SPLIT_NO_EMPTY);

        return array_diff($split, $stopwords);
    }
}

if (!function_exists('form_validation_label')) {
    /**
     * Returns the label for the form field validation from the provided metadata.
     *
     * @author Bendiucov Tatiana
     *
     * @todo Refactoring [01.11.2021]
     * Reason: refactor method name
     *
     * @param string $name
     * @param string $rule
     * @param string $label
     *
     * @return null|string
     *
     * @see
     */
    function form_validation_label(array $metadata, $name, $rule, $label = 'input-label--required')
    {
        if (
            empty($metadata)
            || empty($rule_metadata = arrayGet($metadata, "{$name}.{$rule}", arrayGet($metadata, "*.{$rule}", [])))
            || !isset($rule_metadata['enabled'])
            || !$rule_metadata['enabled']
        ) {
            return null;
        }

        return $label;
    }
}

if (!function_exists('form_validation_rules')) {
    /**
     * Returns the validation rules for the form field validation from the provided metadata.
     *
     * @param string $name
     * @param string $rule
     * @param string $delimiter
     *
     * @return null|string
     */
    function form_validation_rules(array $metadata, $name, $rule, $delimiter = ',')
    {
        if (
            empty($metadata)
            || empty($rule_metadata = arrayGet($metadata, "{$name}.{$rule}", arrayGet($metadata, "*.{$rule}", [])))
            || !isset($rule_metadata['enabled'])
            || !$rule_metadata['enabled']
        ) {
            return null;
        }

        $rules = isset($rule_metadata['rules']) ? $rule_metadata['rules'] : null;
        if (is_array($rules)) {
            $rules = implode($delimiter, $rules);
        }

        return $rules;
    }
}

/*
 *
 * @author Bendiucov Tatiana
 * @todo Remove [01.11.2021]
 * Reason: Not used
 *
 * Checks if validation rule in the validation metadata is enabled
 *
 * @param array $metadata
 * @param string $name
 * @param string $rule
 *
 * @return bool
 */
/*if (!function_exists('form_validation_enabled')) {

    function form_validation_enabled(array $metadata, $name, $rule)
    {
        if (
            empty($metadata)
            || empty($rule_metadata = arrayGet($metadata, "{$name}.{$rule}", []))
            || !isset($rule_metadata['enabled'])
            || !$rule_metadata['enabled']
        ) {
            return false;
        }

        return true;
    }
}*/

if (!function_exists('getCountrySelectOptions')) {
    /**
     * @author Bendiucov Tatiana
     *
     * @todo Refactoring [27.10.2021]
     * Reason: Code style
     *
     * @param mixed $countries
     * @param mixed $selected_country
     * @param mixed $params
     * @param mixed $placeholder
     */
    function getCountrySelectOptions($countries, $selected_country = 0, $params = [], $placeholder = 'default')
    {
        if ('default' == $placeholder) {
            $placeholder = translate('form_placeholder_select_country');
        }

        $options = '<option value="" disabled="disabled" selected>' . $placeholder . '</option>';

        if (isset($params['include_default_option']) && !$params['include_default_option']) {
            $options = '';
        }

        if (empty($countries)) {
            return $options;
        }

        $countries_groups_separator = '<option value="" disabled="disabled">-------------------------------------</option>';
        $column_value = empty($params['value']) ? 'id' : $params['value'];
        $column_displayed = empty($params['display']) ? 'country' : $params['display'];
        $add_countries_groups_separator = false;
        $first_group_countries = [];
        $second_group_countries = '';
        $isset_selected_country = !empty($selected_country);
        $isset_data_attrs = !empty($params['data_attrs']);
        $was_expected_selected_option = false;
        $selected_property_name = $params['selectedPropertyName'] ?? 'id';

        foreach ($countries as $country) {
            $attr_selected = '';
            $data_attrs = '';
            $need_mark_as_selected = false;

            if ($isset_data_attrs) {
                foreach ($params['data_attrs'] as $data_name => $array_key) {
                    // this check is added to enable the addition of date attributes by condition only to certain options
                    if (isset($country[$array_key])) {
                        $data_attrs .= ' data-' . $data_name . '="' . $country[$array_key] . '"';
                    }
                }
            }

            if ($isset_selected_country && !$was_expected_selected_option && ($country[$selected_property_name] == $selected_country)) {
                $was_expected_selected_option = true;

                if (empty($country['position_on_select'])) {
                    // if is country from second group of countries
                    $attr_selected = ' selected';
                } else {
                    $need_mark_as_selected = true;
                }
            }

            $option = '<option value="' . $country[$column_value] . '"' . $attr_selected . $data_attrs . '>' . $country[$column_displayed] . '</option>';
            $second_group_countries .= $option;

            if (!empty($country['position_on_select'])) {
                $add_countries_groups_separator = true;

                if ($need_mark_as_selected) {
                    $option = '<option value="' . $country[$column_value] . '" selected' . $data_attrs . '>' . $country[$column_displayed] . '</option>';
                }

                $first_group_countries[$country['position_on_select']] = $option;
            }
        }

        ksort($first_group_countries);

        $options .= empty($first_group_countries) ? '' : implode('', $first_group_countries);
        $options .= $add_countries_groups_separator ? $countries_groups_separator : '';
        $options .= empty($second_group_countries) ? '' : $second_group_countries;

        return $options;
    }
}

/**
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [01.11.2021]
 * Reason: Code style
 *
 * @param mixed $id_user
 */
function destroyUserSession($id_user)
{
    /** @var User_Model $users */
    $users = model(User_Model::class);
    $user = $users->getSimpleUser($id_user);
    if (empty($user)) {
        return false;
    }

    session()->destroyBySessionId($user['ssid']);
    $update = [
        'clean_session_token' => '',
        'logged'              => 0,
        // 'last_active'         => date('Y-m-d H:i:s'),
        'cookie_salt'         => genRandStr(8),
    ];
    $users->updateUserMain($user['idu'], $update);
    $users->set_notice($id_user, [
        'add_date' => date('Y/m/d H:i:s'),
        'add_by'   => 'System',
        'notice'   => 'Session has been destroyed',
    ]);

    /** @var Elasticsearch_Users_Model $elasticsearchUsersModel */
    $elasticsearchUsersModel = model(Elasticsearch_Users_Model::class);
    $elasticsearchUsersModel->sync((int) $id_user);
}

/*
 *
 * @author Bendiucov Tatiana
 * @todo Remove [01.11.2021]
 * Reason: Not used
 *
 * Transforms the &nbsp; in the text to the provided string token
 *
 * @param string $text
 * @param string $token
 *
 * @return string
 */
/*if (!function_exists('nbsp2token')) {

    function nbsp2token($text, $token = ' ')
    {
        return preg_replace("/\&nbsp\;/", $token, $text);
    }
}*/

if (!function_exists('is_certified')) {
    /**
     * Check if seller or manufacturer is certified by id group.
     *
     * @author Bendiucov Tatiana
     *
     * @todo Refactoring [01.11.2021]
     * Reason: refactor method name
     *
     * @param int $idGroup
     *
     * @return bool
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/d.-Checks-and-permissions#is_certified
     */
    function is_certified(?int $idGroup = null)
    {
        return in_array($idGroup ?? (int) session()->group, [3, 6]);
    }
}

if (!function_exists('is_certified_seller')) {
    /**
     * Check if seller is certified by id group.
     *
     * @author Bendiucov Tatiana
     *
     * @todo Refactoring [01.11.2021]
     * Reason: refactor method name
     *
     * @param int $idGroup
     *
     * @return bool
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/d.-Checks-and-permissions#is_certified_seller
     */
    function is_certified_seller(?int $idGroup = null)
    {
        return in_array($idGroup ?? (int) session()->group, [3]);
    }
}

if (!function_exists('is_certified_manufacturer')) {
    /**
     * Check if manufacturer is certified by id group.
     *
     * @author Bendiucov Tatiana
     *
     * @todo Refactoring [01.11.2021]
     * Reason: refactor method name
     *
     * @param int $idGroup
     *
     * @return bool
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/d.-Checks-and-permissions#is_certified_manufacturer
     */
    function is_certified_manufacturer(?int $idGroup = null)
    {
        return in_array($idGroup ?? (int) session()->group, [6]);
    }
}

if (!function_exists('is_verified_manufacturer')) {
    /**
     * Check if manufacturer is verified by id group.
     *
     * @author Bendiucov Tatiana
     *
     * @todo Refactoring [01.11.2021]
     * Reason: refactor method name
     *
     * @param int $idGroup
     *
     * @return bool
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/d.-Checks-and-permissions#is_verified_manufacturer
     */
    function is_verified_manufacturer(?int $idGroup = null)
    {
        return in_array($idGroup ?? (int) session()->group, [5]);
    }
}

if (!function_exists('is_verified_seller')) {
    /**
     * Check if sseller is verified by id group.
     *
     * @author Bendiucov Tatiana
     *
     * @todo Refactoring [01.11.2021]
     * Reason: refactor method name
     *
     * @param int $idGroup
     *
     * @return bool
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/d.-Checks-and-permissions#is_verified_seller
     */
    function is_verified_seller(?int $idGroup = null)
    {
        return in_array($idGroup ?? (int) session()->group, [2]);
    }
}

if (!function_exists('is_buyer')) {
    /**
     * Check if current user is buyer. Checks in session if group id is not sent as parameter.
     *
     * @author Bendiucov Tatiana
     *
     * @todo Refactoring [01.11.2021]
     * Reason: refactor method name
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/d.-Checks-and-permissions#is_buyer
     */
    function is_buyer(?int $groupId = null): bool
    {
        return 1 === ($groupId ?? (int) session()->group);
    }
}

if (!function_exists('is_seller')) {
    /**
     * Check if current user is seller. Checks in session if group id is not sent as parameter.
     *
     * @author Bendiucov Tatiana
     *
     * @todo Refactoring [01.11.2021]
     * Reason: refactor method name
     *
     * @return bool
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/d.-Checks-and-permissions#is_seller
     */
    function is_seller(?int $groupId = null)
    {
        return in_array($groupId ?? (int) session()->group, [2, 3]);
    }
}

if (!function_exists('is_manufacturer')) {
    /**
     * Check if current user is manufacturer. Checks in session if group id is not sent as parameter.
     *
     * @author Bendiucov Tatiana
     *
     * @todo Refactoring [01.11.2021]
     * Reason: refactor method name
     *
     * @return bool
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/d.-Checks-and-permissions#is_manufacturer
     */
    function is_manufacturer(?int $groupId = null)
    {
        return in_array($groupId ?? (int) session()->group, [5, 6]);
    }
}

if (!function_exists('is_shipper')) {
    /**
     * Check if current user is shipper. Checks in session if group id is not sent as parameter.
     *
     * @author Bendiucov Tatiana
     *
     * @todo Refactoring [01.11.2021]
     * Reason: refactor method name
     *
     * @return bool
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/d.-Checks-and-permissions#is_shipper
     */
    function is_shipper(?int $groupId = null)
    {
        return 31 === ($groupId ?? (int) session()->group);
    }
}

if (!function_exists('is_verified')) {
    /**
     * Check if current user is verified (seller and manufacturer).
     *
     * @author Bendiucov Tatiana
     *
     * @todo Refactoring [01.11.2021]
     * Reason: refactor method name
     *
     * @param int $idGroup
     *
     * @return bool
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/d.-Checks-and-permissions#is_verified
     */
    function is_verified(?int $idGroup = null)
    {
        return in_array($idGroup ?? (int) session()->group, [2, 5]);
    }
}

if (!function_exists('throwableToMessage')) {
    /**
     * Returns formatted throwable message.
     *
     * @param \Throwable $th
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/q.-Output#throwableToMessage
     */
    function throwableToMessage(Throwable $th, ?string $fallbackMessage = null): string
    {
        return sprintf(
            '%s (0x%s)',
            DEBUG_MODE ? $th->getMessage() : ($fallbackMessage ?? 'The operation failed with error.'),
            orderNumberOnly(base_convert($th->getCode(), 10, 16), 9)
        );
    }
}

if (!function_exists('getPublicScriptContent')) {
    /**
     * Returns the public script from js directory.
     *
     * @param string $relativePath - the path to script
     * @param bool   $escapeString - to escape or not
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/q.-Output#getPublicScriptContent
     */
    function getPublicScriptContent(string $relativePath, bool $escapeString = false, array $context = []): string
    {
        $prefix = $escapeString ? '/*<![CDATA[*/ ' : '';
        $postfix = $escapeString ? ' /*]]>*/' : '';
        $content = '';
        if (preg_match('/^(.*)?\\.m?js$/', $relativePath) && file_exists($path = PUBLIC_PATH . '/' . ltrim($relativePath, '/'))) {
            $content = strtr(file_get_contents($path) ?? '', $context);
        }

        return "{$prefix}{$content}{$postfix}";
    }
}

if (!function_exists('getPublicStyleContent')) {
    /**
     * Returns the public style file from css directory.
     *
     * @param string $relativePath - the path to script
     * @param bool   $escapeString - to escape or not
     * @param bool   $publicPath   - determine if use public path
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/q.-Output#getPublicScriptContent
     */
    function getPublicStyleContent(string $relativePath, bool $escapeString = false, bool $publicPath = true, array $context = []): string
    {
        $path = $publicPath ? PUBLIC_PATH : ROOT_PATH;

        $prefix = $escapeString ? '/*<![CDATA[*/ ' : '';
        $postfix = $escapeString ? ' /*]]>*/' : '';
        $content = '';
        if (preg_match('/^(.*)?\\.css$/', $relativePath) && file_exists($path = $path . '/' . ltrim($relativePath, '/'))) {
            $content = strtr(file_get_contents($path) ?? '', $context);
        }

        return "{$prefix}{$content}{$postfix}";
    }
}
/*
 * @author Bendiucov Tatiana
 * @todo Remove [01.11.2021]
 * Reason: Not used
 *
 * Transforms the string to stream;
 *
 * @return resource
 *
 * @throws RuntimeException if failed to create the stream
 *
 */
/*if (!function_exists('strToStream')) {

    function strToStream(string $str)
    {
        $stream = fopen('php://memory','r+');
        fwrite($stream, $str);
        rewind($stream);

        if (false === $stream) {
            throw new RuntimeException("Failed to create the stram.");
        }

        return $stream;
    }
}*/

if (!function_exists('throwableToArray')) {
    /**
     * Creates array output from the throwable instance.
     *
     * @param Throwble $th
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/q.-Output#throwableToArray
     */
    function throwableToArray(Throwable $th): array
    {
        return [
            'message' => $th->getMessage(),
            'trace'   => $th->getTrace(),
            'file'    => $th->getFile(),
            'line'    => $th->getLine(),
        ];
    }
}

/**
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [01.11.2021]
 * Reason: Code style
 *
 * @param mixed $group
 * @param mixed $name
 */
function returnTrueUserGroupName($group = '', $name = '')
{
    if (empty($group)) {
        $group = group_session();
    }

    if (empty($name)) {
        $name = user_group_type();
    }

    if (in_array($group, \App\Common\MANUFACTURER_GROUPS_ID)) {
        return 'manufacturer';
    }

    return strtolower($name);
}

/**
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [01.11.2021]
 * Reason: Code style
 *
 * @param null|mixed $id_group
 * @param null|mixed $group
 * @param null|mixed $accounts
 */
function checkRightSwitchGroup($id_group = null, $group = null, $accounts = null)
{
    $accounts = isset($accounts) ? array_map('strtolower', $accounts) : ['buyer', 'seller', 'manufacturer'];
    $group = $group ?? user_group_type();
    $id_group = $id_groiup ?? group_session();

    return in_array(returnTrueUserGroupName($id_group, $group), $accounts);
}

/**
 * Returns unknown value in red span.
 *
 * @param string $txt
 *
 * @return string
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/q.-Output#unknowValueHtml
 */
function unknowValueHtml($txt = 'N/A')
{
    return "<span class='txt-red'>{$txt}</span>";
}

if (!function_exists('withDebugInformation')) {
    /**
     * Returns debug stack information.
     *
     * @param array $data       - the main data
     * @param array $debugStack - the debug data
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/q.-Output#withDebugInformation
     */
    function withDebugInformation(array $data, array $debugStack = []): array
    {
        if (!DEBUG_MODE) {
            return $data;
        }

        return array_merge($data, ['_debug' => $debugStack]);
    }
}

if (!function_exists('classBasename')) {
    /**
     * Get the class so called "basename" of the given object/class.
     *
     * @author Bendiucov Tatiana
     *
     * @deprecated [01.11.2021]
     * Old method
     *
     * @param object|string $class
     */
    function classBasename($class): string
    {
        return basename(str_replace('\\', '/', is_object($class) ? get_class($class) : $class));
    }
}

if (!function_exists('validateAge')) {
    /**
     * Validates the age of the user (over 18 default).
     *
     * @param mixed $birthday
     * @param int   $age      - 18 default
     *
     * @return bool
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/d.-Checks-and-permissions#validateAge
     */
    function validateAge($birthday, $age = 18)
    {
        $birthdayDate = new DateTime($birthday);
        $birthdayDate->add(new DateInterval("P{$age}Y"));

        if ($birthdayDate <= new DateTime()) {
            return true;
        }

        return false;
    }
}

if (!function_exists('getCommentResourceToken')) {
    /**
     * Creates a resource token for comments resource base on components.
     *
     * @param array $hashComponents - the array with components (generated by other methods like blogCommentsResourceHashComponents)
     *
     * @return string
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/q.-Output#getCommentResourceToken
     */
    function getCommentResourceToken(array $hashComponents): ?string
    {
        return empty($hashComponents) ? null : hash('sha3-512', implode($hashComponents));
    }
}

if (!function_exists('blogCommentsResourceHashComponents')) {
    /**
     * Returns array with components for hasing with getCommentResourceToken. The array consists of id of the blog and the salt.
     *
     * @param int $idBlog - the id of the blog
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/q.-Output#blogCommentsResourceHashComponents
     */
    function blogCommentsResourceHashComponents(int $idBlog): array
    {
        return [
            'id'    => $idBlog,
            'salt'  => 'RandomSaltForBlogsDetailPage',
        ];
    }
}

if (!function_exists('tradeNewsCommentsResourceHashComponents')) {
    /**
     * Returns array with components for hasing with getCommentResourceToken. The array consists of id of the trade news and the salt.
     *
     * @param int $idTradeNews - the id of the trade news
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/q.-Output#tradeNewsCommentsResourceHashComponents
     */
    function tradeNewsCommentsResourceHashComponents(int $idTradeNews): array
    {
        return [
            'id'    => $idTradeNews,
            'salt'  => 'RandomSaltForEPTradeNewsPage',
        ];
    }
}

if (!function_exists('newsCommentsResourceHashComponents')) {
    /**
     * Returns array with components for hasing with getCommentResourceToken. The array consists of id of the news and the salt.
     *
     * @param int $idNews - the id of the news
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/q.-Output#newsCommentsResourceHashComponents
     */
    function newsCommentsResourceHashComponents(int $idNews): array
    {
        return [
            'id'    => $idNews,
            'salt'  => 'RandomSaltForEPNewsPage',
        ];
    }
}

if (!function_exists('updatesCommentsResourceHashComponents')) {
    /**
     * Returns array with components for hasing with getCommentResourceToken. The array consists of id of the update and the salt.
     *
     * @param int $idUpdate - the id of the update
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/q.-Output#updatesCommentsResourceHashComponents
     */
    function updatesCommentsResourceHashComponents(int $idUpdate): array
    {
        return [
            'id'    => $idUpdate,
            'salt'  => 'RandomSaltForEPUpdatesPage',
        ];
    }
}

if (!function_exists('eventsCommentsResourceHashComponents')) {
    /**
     * Returns array with components for hasing with getCommentResourceToken. The array consists of id of the event and the salt.
     *
     * @param int $idEvent - the id of the event
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/q.-Output#eventsCommentsResourceHashComponents
     */
    function eventsCommentsResourceHashComponents(int $idEvent): array
    {
        return [
            'id'   => $idEvent,
            'salt' => 'RandomSaltForEvents',
        ];
    }
}

if (!function_exists('getBlogUrl')) {
    /**
     * Returns the blog post url base on parameters like title and id.
     *
     * @param array $urlParams - the parameters to create url. Required id and title
     *
     * @return null|string
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/c.-Urls,-Http,-Domains#getBlogUrl
     */
    function getBlogUrl(array $urlParams): ?string
    {
        if (empty($urlParams['id']) || empty($urlParams['title_slug']) || empty($urlParams['category_url'])) {
            return null;
        }

        return __BLOG_URL . "{$urlParams['category_url']}/{$urlParams['title_slug']}-{$urlParams['id']}";
    }
}

if (!function_exists('getEpNewsUrl')) {
    /**
     * Returns the ep news url based on the url sent as array.
     *
     * @param array $urlParams - required field 'url'
     *
     * @return string
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/c.-Urls,-Http,-Domains#getEpNewsUrl
     */
    function getEpNewsUrl(array $urlParams): ?string
    {
        if (empty($urlParams['url'])) {
            return null;
        }

        return __SITE_URL . 'ep_news/detail/' . $urlParams['url'];
    }
}

if (!function_exists('getEpUpdatesUrl')) {
    /**
     * Returns the ep updates url based on the url sent as array.
     *
     * @param array $urlParams - required field 'url'
     *
     * @return string
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/c.-Urls,-Http,-Domains#getEpUpdatesUrl
     */
    function getEpUpdatesUrl(array $urlParams): ?string
    {
        if (empty($urlParams['url'])) {
            return null;
        }

        return __SITE_URL . 'ep_updates/detail/' . $urlParams['url'];
    }
}

if (!function_exists('getTradeNewsUrl')) {
    /**
     * Returns the trade news url based on parameters sent as array.
     *
     * @param array $urlParams - required field 'title_slug' and 'id_trade_news'
     *
     * @return string
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/c.-Urls,-Http,-Domains#getTradeNewsUrl
     */
    function getTradeNewsUrl(array $urlParams): ?string
    {
        if (empty($urlParams['title_slug']) || empty($urlParams['id_trade_news'])) {
            return null;
        }

        return __SITE_URL . 'trade_news/detail/' . $urlParams['title_slug'] . '-' . $urlParams['id_trade_news'];
    }
}

if (!function_exists('varToString')) {
    /**
     * Converts a variable (object, array, resource, etc...) to string.
     *
     * @param mixed $var
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/g.-Strings-and-text#varToString
     */
    function varToString($var): string
    {
        if (\is_object($var)) {
            return sprintf('an object of type %s', \get_class($var));
        }

        if (\is_array($var)) {
            $a = [];
            foreach ($var as $k => $v) {
                $a[] = sprintf('%s => ...', $k);
            }

            return sprintf('an array ([%s])', mb_substr(implode(', ', $a), 0, 255));
        }

        if (\is_resource($var)) {
            return sprintf('a resource (%s)', get_resource_type($var));
        }

        if (null === $var) {
            return 'null';
        }

        if (false === $var) {
            return 'a boolean value (false)';
        }

        if (true === $var) {
            return 'a boolean value (true)';
        }

        if (\is_string($var)) {
            return sprintf('a string ("%s%s")', mb_substr($var, 0, 255), mb_strlen($var) > 255 ? '...' : '');
        }

        if (is_numeric($var)) {
            return sprintf('a number (%s)', (string) $var);
        }

        return (string) $var;
    }
}

if (!function_exists('getQaUniqueIdentifierAttributes')) {
    /**
     * Returns array as ["atas" => value].
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/q.-Output#getQaUniqueIdentifierAttributes
     */
    function getQaUniqueIdentifierAttributes(string $attrValue): array
    {
        return ['atas' => $attrValue];
    }
}

if (!function_exists('addQaUniqueIdentifier')) {
    /**
     * Returns atas as string "atas='value'". To be used in html.
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/q.-Output#addQaUniqueIdentifier
     */
    function addQaUniqueIdentifier(string $attrValue): string
    {
        return filter_var(config('env.APP_SHOW_QA_ATTRIBUTES', false), FILTER_VALIDATE_BOOL) ? "atas=\"{$attrValue}\"" : '';
    }
}

if (!function_exists('getControllerActionFromUrl')) {
    /**
     * @author Bendiucov Tatiana
     *
     * @todo Refactoring [02.11.2021]
     * Reason: Code style
     */
    function getControllerActionFromUrl(string $url): ?array
    {
        $pathComponents = parse_url($url);
        if (empty($pathComponents)) {
            return null;
        }

        $app = tmvc::instance();

        //region determinate url segments
        $pathInfo = $pathComponents['path'];
        $subDomainRawInfo = explode('.', $pathComponents['host']);
        $currentSubDomain = strtolower(count($subDomainRawInfo) > 2 ? implode('.', array_slice($subDomainRawInfo, 0, -2)) : '');

        if (!empty($app->config['routing']['search']) && !empty($app->config['routing']['replace'])) {
            $pathInfo = preg_replace(
                $app->config['routing']['search'],
                $app->config['routing']['replace'],
                $pathInfo
            );
        }

        $urlSegments = empty($pathInfo) ? [] : array_filter(explode('/', $pathInfo), 'mb_strlen');
        //endregion determinate url segments

        //region determinate controller
        $controllerName = $app->config['root_controller'] ?? null;

        if (null === $controllerName) {
            if ($currentSubDomain === config('env.BLOG_SUBDOMAIN')) {
                $controllerName = $app->config['blog_controller'];
            } elseif (in_array($currentSubDomain, $app->config['cr_available'])) {
                $controllerName = $app->config['cr_controller'];
            } else {
                $controllerName = ($urlSegments[1] ?: null) ?? $app->config['default_controller'] ?? null;
            }
        }

        $myAppDir = dirname(__FILE__, 2);
        $controllerFilePath = "{$myAppDir}/controllers/{$controllerName}.php";
        if (!is_file($controllerFilePath)) {
            $controllerName = $app->config['company_controller'] ?? null;
        }
        //endregion determinate controller

        //region determinate action
        if (!empty($app->config['root_action'])) {
            $actionName = $app->config['root_action'];
        } else {
            switch ($controllerName) {
                case $app->config['blog_controller']:
                    $actionName = (isset($urlSegments[1]) && in_array($urlSegments[1], ['detail', 'preview_blog'])) ? $urlSegments[1] : $app->config['blog_default_action'];

                break;
                case $app->config['cr_controller']:
                    $actionName = empty($urlSegments[1]) ? $app->config['cr_default_action'] : $urlSegments[1];

                break;

                default:
                    $actionName = !empty($urlSegments[2]) ? $urlSegments[2] : (empty($app->config['default_action']) ? 'index' : $app->config['default_action']);

                break;
            }
        }

        return [
            'controller'    => $controllerName,
            'action'        => $actionName,
        ];
    }
}

if (!function_exists('isSubscribedUser')) {
    /**
     * @author Bendiucov Tatiana
     *
     * @todo Refactoring [02.11.2021]
     * Reason: Code style
     */
    function isSubscribedUser(): bool
    {
        if (!empty(email_session())) {
            /** @var Subscribe_Model $subscribeModel */
            $subscribeModel = model(Subscribe_Model::class);

            $isSubscriber = $subscribeModel->existSubscriber(email_session());

            if ($isSubscriber) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('getEpEventMainImageUrl')) {
    /**
     * Returns the main image URL for the event.
     *
     * @deprecated `2.41.0.0` `17-11-2022` It is no longer used. No substitution provided.
     *
     * @param string $noImagePath
     *
     * @return string url to image
     */
    function getEpEventMainImageUrl(int $eventId, ?string $eventImageName, $noImagePath = 'public/img/no_image/no-image-80x80.png'): string
    {
        $folderPath = getImgPath('ep_events.main', ['{ID}' => $eventId]);

        return __IMG_URL . getImage($folderPath . $eventImageName, $noImagePath);
    }
}

if (!function_exists('getEpEventRecommendedImageUrl')) {
    /**
     * Returns the image URL for the recommended event.
     *
     * @deprecated `2.41.0.0` `17-11-2022` It is no longer used. No substitution provided.
     *
     * @param string $noImagePath
     *
     * @return string url to image
     */
    function getEpEventRecommendedImageUrl(int $eventId, ?string $eventImageName, $noImagePath = 'public/img/no_image/no-image-80x80.png'): string
    {
        $folderPath = getImgPath('ep_events.recommended', ['{ID}' => $eventId]);

        return __IMG_URL . getImage($folderPath . $eventImageName, $noImagePath);
    }
}

if (!function_exists('getEpEventGalleryImageUrl')) {
    /**
     * Returns the image URL for the gallery event.
     *
     * @deprecated `2.41.0.0` `17-11-2022` It is no longer used. No substitution provided.
     *
     * @param string $noImagePath
     *
     * @return string url to image
     */
    function getEpEventGalleryImageUrl(int $eventId, string $eventImageName, $noImagePath = 'public/img/no_image/no-image-80x80.png'): string
    {
        $folderPath = getImgPath('ep_events.gallery', ['{ID}' => $eventId]);

        return __IMG_URL . getImage($folderPath . $eventImageName, $noImagePath);
    }
}

if (!function_exists('getEpEventSpeakerImageUrl')) {
    /**
     * Returns the image for the event speaker.
     *
     * @deprecated `2.41.0.0` `17-11-2022` It is no longer used. No substitution provided.
     *
     * @param string $noImagePath
     *
     * @return string url to image
     */
    function getEpEventSpeakerImageUrl(int $speakerId, ?string $speakerImageName, $noImagePath = 'public/img/no_image/group/noimage-not-registered.svg'): string
    {
        $folderPath = $folderPath ?? getImgPath('ep_events_speakers.main', ['{ID}' => $speakerId]);

        return __IMG_URL . getImage($folderPath . $speakerImageName, $noImagePath);
    }
}

if (!function_exists('getEpEventPartnerImageUrl')) {
    /**
     * Returns the image for the event partner.
     *
     * @deprecated `2.41.0.0` `17-11-2022` It is no longer used. No substitution provided.
     *
     * @param string $noImagePath
     *
     * @return string url to image
     */
    function getEpEventPartnerImageUrl(int $partnerId, ?string $partnerImageName, $noImagePath = 'public/img/no_image/no-image-80x80.png'): string
    {
        $folderPath = $folderPath ?? getImgPath('ep_events_partners.main', ['{ID}' => $partnerId]);

        return __IMG_URL . getImage($folderPath . $partnerImageName, $noImagePath);
    }
}

if (!function_exists('getEpEventDetailUrl')) {
    /**
     * Returns the ep event detail url, based on id and title.
     *
     * @param array $event - required fields 'title' and 'id'
     *
     * @return string
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/c.-Urls,-Http,-Domains#getEpEventDetailUrl
     */
    function getEpEventDetailUrl(array $event): ?string
    {
        return empty($event) ? null : __SITE_URL . 'ep_events/detail/' . $event['id'] . '/' . strForURL($event['title']);
    }
}

if (!function_exists('getArrayFromString')) {
    /**
     * @author Bendiucov Tatiana
     *
     * @deprecated [02.11.2021]
     * Old method
     * Used in old models only
     *
     * @param mixed $string
     */
    function getArrayFromString($string): array
    {
        if (is_array($string)) {
            return $string;
        }

        $array = explode(',', $string);

        return array_map(
            fn ($element) => trim($element, " '\""),
            $array
        );
    }
}

/**
 * Adds the suffix and prefix for custom[] validation engine rule.
 *
 * @param string $rules - rules seperated by comma
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/q.-Output#createCustomValidationRule
 */
function createCustomValidationRule(string $rules): string
{
    if (empty($rules)) {
        return '';
    }

    $fieldRulesArray = array_map(function ($arrayValue) {
        return sprintf('custom[%s]', trim($arrayValue));
    }, explode(',', $rules));

    return implode(',', $fieldRulesArray);
}

/**
 * Gets the group name by id of the group.
 *
 * @param bool $withCertification - to display Verified/Certified (true) or just Seller/Manufacturer (false)
 * @param bool $tolower           - to lowercase
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/q.-Output#getGroupNameById
 */
function getGroupNameById(int $idGroup, bool $withCertification = true, bool $tolower = false): string
{
    $groups = [
        1  => 'Buyer',
        2  => 'Seller',
        3  => 'Seller',
        5  => 'Manufacturer',
        6  => 'Manufacturer',
        13 => 'Order Manager',
        14 => 'Admin',
        15 => 'User Manager',
        16 => 'Super Admin',
        17 => 'Support',
        18 => 'Content Manage',
        19 => 'Billing Manager',
        25 => 'Company staff user',
        31 => 'Freight Forwarder',
        32 => 'Freight Forwarder Staff User',
    ];

    if (empty($idGroup) || !array_key_exists($idGroup, $groups)) {
        return '';
    }

    $groupNamePrefix = '';
    if ($withCertification) {
        $groupNamePrefix = in_array($idGroup, [3, 6]) ? 'Certified ' : '';
        $groupNamePrefix = in_array($idGroup, [2, 5]) ? 'Verified ' : '';
    }

    if ($tolower) {
        return strtolower($groupNamePrefix . $groups[$idGroup]);
    }

    return $groupNamePrefix . $groups[$idGroup];
}


if (!function_exists('prepareEmailContent')) {
    /**
     * @param mixed $email
     * @param mixed $replace
     */
    function prepareEmailContent($email, $replace): string
    {
        if (empty($email)) {
            return $email;
        }

        $template = [];
        foreach ($email as $emailItem) {
            $path = "admin/emails_template/email_elements/general/{$emailItem['name']}_view";
            if (file_exists("tinymvc/myapp/views/{$path}.php")) {
                $template[] = views()->fetch($path, array_merge($emailItem, $replace));
            }
        }

        return implode('', $template);
    }
}

/**
 * Get and create if not exist, value of cookie _ep_client_id
 *
 * @return string
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/p.-Account-related-helpers#getepclientidcookievalue-string
 */
function getEpClientIdCookieValue(): string
{
    $epClientId = cookies()->getCookieParam('_ep_client_id');

    $renew = false;
    if(null !== $epClientId){
        try {
            Uuid::fromString((string) $epClientId);
        } catch (InvalidUuidStringException $exception) {
            $renew = true;
        }
    }

    if (null === $epClientId || $renew) {
        $epClientId = Uuid::uuid1()->toString();

        cookies()->setCookieParam('_ep_client_id', $epClientId, 365 * 24 * 60 * 60); //1 year
    }

    return $epClientId;
}

if (!function_exists('isBackstopEnabled')) {
    /**
     * Determine if Backstop enabled and the test is running.
     */
    function isBackstopEnabled(): bool
    {
        return container()->getParameter('backstop.enabled')
            && requestStack()->getMainRequest()
            && requestStack()->getMainRequest()->query->has('backstop');
    }
}

/**
 * Function to add dynamic url for promo banners
 *
 * @return string
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/g.-Strings-and-text#getbannerdynamicurlstring-alias-string-defaulturl
 */
if (!function_exists('getBannerDynamicUrl')) {
    function getBannerDynamicUrl(string $alias, string $defaultUrl): string
    {
        $dinamicUrls = [
            'home_top_header_company_page' => fn() => getMyCompanyURL(),
        ];

        return isset($dinamicUrls[$alias]) ? $dinamicUrls[$alias]() : $defaultUrl;
    }
}
