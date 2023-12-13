<?php

use App\Common\Exceptions\InvalidApiTokenException;
use App\Email\Systmessages;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use function GuzzleHttp\json_decode;

/**
 * Clean a string of all the tags, js, special characters etc. Used to sanitize some input.
 *
 * @param string $input             - the string to be sanitized
 * @param bool   $toLower          - if you need to return all lowercase then send true
 * @param bool   $cleanWhitespaces - by default it does clean whitespaces (true)
 *
 * @return string - the sanitezed string
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/g.-Strings-and-text#cleanInput
 */
function cleanInput($input, $toLower = false, $cleanWhitespaces = true)
{
    $search = [
        '@<script[^>]*?>.*?</script>@si',   // wrong by strip_tags, Strip out javascript
        '@<style[^>]*?>.*?</style>@siU',    // wrong by strip_tags, Strip style tags properly
    ];

    $output = trim($input);
    $output = strip_tags($output, '<style><script>');
    $output = preg_replace($search, '', $output);
    $output = htmlspecialchars($output, ENT_QUOTES, 'UTF-8');

    if ($cleanWhitespaces) {
        $output = preg_replace('/\s+/S', ' ', $output);
    }

    if ($toLower) {
        $output = strtolower($output);
    }

    return $output;
}

/**
 * Uses `htmlspecialchars_decode` php function to decode special chars.
 *
 * @param string $cleanedStr - the cleaned string to decode
 *
 * @return string - the decoded string
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/g.-Strings-and-text#decodeCleanInput
 */
function decodeCleanInput($cleanedStr)
{
    return htmlspecialchars_decode($cleanedStr, ENT_QUOTES);
}

/**
 * Converts a string to a GET query string.
 *
 * @param string $str - string
 *
 * @return bool|string string as GET query or false if empty
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/g.-Strings-and-text#strToGET
 */
function strToGET($str = '')
{
    $array = array_filter(explode('&', $str));
    if (empty($array)) {
        return false;
    }

    $get = [];
    foreach ($array as $element) {
        $components = explode('=', $element);

        if (empty($components[0]) || empty($components[1])) {
            continue;
        }

        $get[$components[0]] = $components[1];
    }

    $get = array_filter($get);

    if (empty($get)) {
        return false;
    }

    return $get;
}

/**
 * Turns an array to GET QUERY.
 *
 * @param array  $array get parameters
 * @param string $keys  string keys to be used. e.g 'a,b,id'
 * @param string $type  the working mode. Could be 'except' - the default value. Could be 'only'
 *
 * @return string query string
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/g.-Strings-and-text#arrayToGET
 */
function arrayToGET($array, $keys = null, $type = 'except')
{
    if (!is_array($array)) {
        return '';
    }

    $keys = array_flip(explode(',', $keys));
    $array = ('except' == $type) ? array_diff_key($array, $keys) : array_intersect_key($array, $keys);

    $el = [];
    foreach ($array as $key => $value) {
        $value = trim($value);
        if (empty($value)) {
            continue;
        }

        $el[] = $key . '=' . $value;
    }

    return count($el) ? '?' . implode('&', $el) : '';
}

/**
 * Get the ip of the visitor on the site.
 *
 * @return string the ip
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/3.-Urls,-Http,-Domains#getvisitorip
 */
function getVisitorIP()
{
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $theIP = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $theIP = $_SERVER['REMOTE_ADDR'];
    }

    return trim($theIP);
}

/**
 * Ensures an ip address is both a valid IP and does not fall within
 * a private network range.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [26.10.2021]
 * Reason: refactor method name
 *
 * @param mixed $ip - the ip number
 *
 * @return bool
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/d.-Checks-and-permissions#validate_ip
 */
function validate_ip($ip)
{
    if ('unknown' === strtolower($ip)) {
        return false;
    }

    // generate ipv4 network address
    $ip = ip2long($ip);

    // if the ip is set and not equivalent to 255.255.255.255
    if (false !== $ip && -1 !== $ip) {
        // make sure to get unsigned long representation of ip
        // due to discrepancies between 32 and 64 bit OSes and
        // signed numbers (ints default to signed in PHP)
        $ip = sprintf('%u', $ip);
        // do private network range checking
        if ($ip >= 0 && $ip <= 50331647) {
            return false;
        }
        if ($ip >= 167772160 && $ip <= 184549375) {
            return false;
        }
        if ($ip >= 2130706432 && $ip <= 2147483647) {
            return false;
        }
        if ($ip >= 2851995648 && $ip <= 2852061183) {
            return false;
        }
        if ($ip >= 2886729728 && $ip <= 2887778303) {
            return false;
        }
        if ($ip >= 3221225984 && $ip <= 3221226239) {
            return false;
        }
        if ($ip >= 3232235520 && $ip <= 3232301055) {
            return false;
        }
        if ($ip >= 4294967040) {
            return false;
        }
    }

    return true;
}

/**
 * Checks if the request is AJAX.
 *
 * @return bool
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/d.-Checks-and-permissions#isAjaxRequest
 */
function isAjaxRequest()
{
    return tmvc::instance()->getRequest()->isAjaxRequest();
}

/**
 * @author Bendiucov Tatiana
 *
 * @deprecated [25.10.2021]
 * old method, there is a new one
 * @see cleanInput()
 *
 * @param mixed $str
 */
function filter($str)
{
    $str = trim($str);
    $str = htmlspecialchars($str);
    $str = stripslashes($str);
    $str = str_replace('\'', '&#039;', $str);
    $str = strip_tags($str);
    // $str = mysql_escape_string($str);
    if (0 !== strlen($str)) {
        return $str;
    }

    return false;
}

/**
 * Extracts from the string only the number.
 *
 * @param string $str - string
 *
 * @return int - the number extracted
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/8.-Numbers-and-Money#toId
 */
function toId($str)
{
    preg_match_all('/\d+/', $str, $matches);

    return intval($matches[0][0]);
}

/**
 * Function for getting only the links from a string.
 *
 * @param string $text - string to search
 *
 * @return array - array with link found
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/g.-Strings-and-text#getLinksFromText
 */
function getLinksFromText($text)
{
    preg_match_all('/<a\s+href=["\']([^"\']+)["\']/i', $text, $matches, PREG_PATTERN_ORDER);

    $links = array_filter($matches[1]);
    if (empty($links)) {
        return [];
    }

    return $links;
}

/**
 * Get all the available subdomains as an array.
 *
 * @return array - array with all the subdomains
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/3.-Urls,-Http,-Domains#getsubdomains
 */
function getSubDomains()
{
    return [
        'blog'      => config('env.BLOG_SUBDOMAIN'),
        'community' => config('env.COMMUNITY_SUBDOMAIN'),
        'shippers'  => config('env.SHIPPER_SUBDOMAIN'),
        'bloggers'  => config('env.BLOGGERS_SUBDOMAIN'),
    ];
}

/**
 * Checks if the domain is right
 * Redirects to the domain for group type if not right.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [25.10.2021]
 * Reason: Code style
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/d.-Checks-and-permissions#checkdomainforgroup
 */
function checkDomainForGroup()
{
    $sub_domains = getSubDomains();
    $user_gt = user_group_type();
    $groups_to_sub_domains = [
        'Shipper' => 'shippers',
    ];

    // If current URL host is localhost we will suffer under endless redirects, so
    // the safest solution is to leave this function.
    if ('localhost' === parse_url(__CURRENT_URL, PHP_URL_HOST)) {
        return;
    }

    if (
        (isset($groups_to_sub_domains[$user_gt]) && __CURRENT_SUB_DOMAIN !== $sub_domains[$groups_to_sub_domains[$user_gt]])
        || (!isset($groups_to_sub_domains[$user_gt]) && __SITE_URL !== __CURRENT_SUB_DOMAIN_URL)
    ) {
        headerRedirect(getUrlForGroup(request()->getRequestUri()));
    }
}

/**
 * Check if current subdomain is for shipper.
 *
 * @return bool
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/d.-Checks-and-permissions#checkshipperindomain
 */
function checkShipperinDomain()
{
    return !check_group_type('Shipper') || __CURRENT_SUB_DOMAIN == config('env.SHIPPER_SUBDOMAIN');
}

if (!function_exists('isMainDomain')) {
    /**
     * Check if current domain is the main domain.
     *
     * @return bool
     *
     * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/d.-Checks-and-permissions#ismaindomain
     */
    function isMainDomain()
    {
        return !in_array(__CURRENT_SUB_DOMAIN, getSubDomains());
    }
}

/**
 * Check email through EmailChecker api.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [25.10.2021]
 * Reason: Code style
 *
 * @param string $email                - the email;
 * @param bool   $return_full_response - if true then will return as array;
 *
 * @return 'Bad', 'Unknown' or 'Ok'
 *                If $return_full_response is set to true - array('status', 'full_response_json')
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/j.-Email-and-Password#checkEmailDeliverability
 */
function checkEmailDeliverability($email, $return_full_response = false)
{
    $hashed_email = getEncryptedEmail($email);

    $rez = model('email_hash')->findOneBy(['conditions' => ['email_hash' => $hashed_email, 'to_verify_null' => true, 'email_status_not_null' => true]]);

    if (!empty($rez)) {
        return $return_full_response ? ['status' => $rez['email_status'], 'full_response_json' => $rez['email_status_response']] : $rez['email_status'];
    }

    $raw_response = '';
    $raw_response_content = null;

    try {
        $raw_response = httpGet('https://api.emailverifyapi.com/v3/lookups/json', ['query' => [
            'key'   => config('env.EMAILCHECKER_API_KEY'),
            'email' => $email,
        ], 'timeout' => config('env.EMAILCHECKER_API_TIMEOUT_SEC')]);

        $raw_response_content = $raw_response->getBody()->getContents();
        $response = json_decode($raw_response_content, true);

        $checked_data = [
            'email_hash'            => $hashed_email,
            'email_status'          => !isset($response['deliverable']) ? 'Unknown' : (true == $response['deliverable'] ? 'Ok' : 'Bad'),
            'email_status_response' => $raw_response_content,
            'to_verify'             => null,
        ];

        if (empty($exists = model('email_hash')->findOneBy(['conditions' => ['email_hash' => $hashed_email]]))) {
            model('email_hash')->insertOne($checked_data);
        } else {
            $checked_data['rechecked'] = $exists['rechecked'] + 1;
            model('email_hash')->updateOne($exists['id'], $checked_data);
        }

        if (!isset($response['deliverable']) && isset($response['Message']) && 'The provided license key is not valid' == $response['Message']) {
            throw new InvalidApiTokenException($response['Message']);
        }
    } catch (GuzzleHttp\Exception\TransferException | InvalidApiTokenException $e) {
        if (null !== ($emailToSend = config('env.EMAILCHECKER_FAILURE_LOG_EMAIL') ?: null)) {
            $callers = debug_backtrace();
            $messageAditional = [
                'email_checked' => $email,
                'backtrace'     => 'Called from: ' . $callers[0]['file'] . '<br/> Method: ' . $callers[1]['function'],
            ];

            /** @var MailerInterface $mailer */
            $mailer = container()->get(MailerInterface::class);
            $mailer->send(
                (new Systmessages('Andrei', '', $e->getMessage() . '<br>Email checked: ' . $email, implode(' ', $messageAditional)))
                    ->subject('Email checker api failure log')
                    ->to(new Address($emailToSend))
            );
        }

        $null_response = [
            'email_hash'            => $hashed_email,
            'email_status'          => null,
            'email_status_response' => $raw_response_content ?? '[]',
            'to_verify'             => $email,
        ];
        if (empty($exists = model('email_hash')->findOneBy(['conditions' => ['email_hash' => $hashed_email]]))) {
            model('email_hash')->insertOne($null_response);
        } else {
            $null_response['rechecked'] = $exists['rechecked'] + 1;
            if ($null_response['rechecked'] < (int) config('env.EMAILCHECKER_API_MAX_RECHECK')) {
                model('email_hash')->updateOne($exists['id'], $null_response);
            } else {
                $null_response['email_status'] = 'Bad';
                model('email_hash')->updateOne($exists['id'], $null_response);
            }
        }

        return true == $return_full_response ? ['status' => null, 'full_response_json' => null] : null;
    }
    //endregin work with response

    //region return option
    $return = [
        'full_response_json' => $raw_response_content,
    ];

    $return['status'] = !isset($response['deliverable']) ? 'Unknown' : (true == $response['deliverable'] ? 'Ok' : 'Bad');
    //endregion return option

    return true == $return_full_response ? $return : $return['status'];
}

/**
 * Encrypts email using 'sha3-512'.
 *
 * @param string $email to encrypt
 *
 * @return string encrypted email
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/j.-Email-and-Password#getEncryptedEmail
 */
function getEncryptedEmail($email)
{
    return hash('sha3-512', $email);
}

/**
 * Encrypts the password by new method, and by old if needed.
 *
 * @param string      $password - the password to encrypt
 * @param bool        $isLegacy - by old method or new. By default by new
 * @param null|string $email    - user email needed if the old method for encryption is used
 *
 * @return string the password hash
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/j.-Email-and-Password#getEncryptedPassword
 */
function getEncryptedPassword($password, $isLegacy = false, $email = null)
{
    if ($isLegacy) {
        return sha1($email . $password);
    }

    return password_hash($password, PASSWORD_ARGON2ID);
}

/**
 * Check the correct password.
 *
 * @param string      $password - the initial password
 * @param string      $password - the hashed password
 * @param bool        $isLegacy - by old method or new. By default by new method
 * @param null|string $email    - user email needed if the old method for encryption is used
 * @param mixed       $hash
 *
 * @return bool the password is right or not
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/j.-Email-and-Password#checkPassword
 */
function checkPassword($password, $hash, $isLegacy = false, $email = null)
{
    if ($isLegacy) {
        return $hash === sha1($email . $password);
    }

    return password_verify($password, $hash);
}

if (!function_exists('is_iterable')) {
    /**
     * Check if the object is traversable or if array.
     *
     * @author Bendiucov Tatiana
     *
     * @todo Refactoring [26.10.2021]
     * Reason: refactor method name
     *
     * @param mixed $obj - object or array to check
     *
     * @return bool
     *
     * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/f.-Arrays-and-Objects#is_iterable
     */
    function is_iterable($obj)
    {
        return is_array($obj) || (is_object($obj) && ($obj instanceof \Traversable));
    }
}

/**
 * Returns special url if the group of the user is shipper.
 *
 * @param string      $path  - the link to page
 * @param null|string $group - the group name
 *
 * @return string - the url with the right site url
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/e.-Groups-and-Rights#getUrlForGroup
 */
function getUrlForGroup($path = '/', $group = null)
{
    $path = ltrim($path, '/');
    if ('shipper' === strtolower($group) || check_group_type('Shipper')) {
        return __SHIPPER_URL . $path;
    }

    return __SITE_URL . $path;
}

/**
 * Sets the user type and the id of the only registered user in session.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [26.10.2021]
 * Reason: refactor method name
 *
 * @param int    $idUser - the id of the user
 * @param int    $grId   - id of the group
 * @param string $grName - the name of the group
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/p.-Account-related-helpers#set_verification_session_data
 */
function set_verification_session_data($idUser, $grId, $grName)
{
    $grType = returnTrueUserGroupName($grId, $grName);

    session()->__set('user_verification', $idUser);
    session()->__set('user_verification_type', $grType);
}

/**
 * Checks if the google recaptcha is valid.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [25.10.2021]
 * Reason: Code style
 *
 * @return bool - return true if valid and array with detailed error message if not valid
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/d.-Checks-and-permissions#ajax_validate_google_recaptcha
 */
function ajax_validate_google_recaptcha()
{
    if (false === filter_var(config('env.RECAPTCHA_ENABLED', false), FILTER_VALIDATE_BOOLEAN)) {
        return [null, null];
    }

    $error_message = translate('systmess_error_you_didnt_pass_bot_check');
    $token = isset($_POST['token']) ? $_POST['token'] : null;
    if (empty($token)) {
        jsonResponse($error_message, 'error', withDebugInformation(
            [
                'errors' => [
                    [
                        'title'  => 'Token is empty',
                        'detail' => 'Verification token recieved from request is empty',
                    ],
                ],
            ],
            ['token' => $token]
        ));
    }

    $url = 'https://www.recaptcha.net/recaptcha/api/siteverify';
    $verify = file_get_contents($url, false, stream_context_create([
        'http' => [
            'method'  => 'POST',
            'content' => http_build_query([
                'secret'   => config('env.RECAPTCHA_PRIVATE_TOKEN_REGISTER'),
                'response' => $token,
            ]),
        ],
    ]));
    if (false === $verify) {
        jsonResponse($error_message, 'error', withDebugInformation(
            [
                'errors' => [
                    [
                        'title'  => 'Verification server is unresponsive',
                        'detail' => 'The capthca verification server failed to respond in time or returned empty response',
                    ],
                ],
            ],
            ['token' => $token]
        ));
    }
    $captcha_success = json_decode($verify);

    if (null === $captcha_success || json_last_error()) {
        jsonResponse($error_message, 'error', withDebugInformation(
            [
                'errors' => [
                    [
                        'title'  => 'Malformed response',
                        'detail' => 'The captchs verification server returned malformed response',
                    ],
                ],
            ],
            ['token' => $token, 'challenge' => $captcha_success]
        ));
    }

    if (false === $captcha_success->success) {
        jsonResponse($error_message, 'error', withDebugInformation(
            [
                'errors' => [
                    [
                        'title'  => 'Bot-check failed',
                        'detail' => 'The score in bot-check is too low',
                        'meta'   => [
                            'score' => $captcha_success->score,
                            'codes' => $captcha_success->{'error-codes'},
                        ],
                    ],
                ],
            ],
            ['token' => $token, 'challenge' => $captcha_success]
        ));
    }

    return $captcha_success->success;
}

if (!function_exists('verifyCertificationExpireSoon')) {
    /**
     * Verifies if the certificated user's status is going to expire soon.
     *
     * @param string $paidDate - the dated of expiration
     *
     * @return array ['notify' - (bool) if the user should be notified or not, 'days' - (int) days remaining]
     *
     * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/e.-Groups-and-Rights#verifyCertificationExpireSoon
     */
    function verifyCertificationExpireSoon($paidDate)
    {
        $daysUserRemain = date_difference($paidDate, date('Y-m-d'));
        $days = [30, 7, 3, 1];
        $notify = false;

        if (in_array($daysUserRemain, $days)) {
            $notify = true;
        }

        return ['notify' => $notify, 'days' => $daysUserRemain];
    }
}

if (!function_exists('verifyNeedCertifyUpgrade')) {
    /**
     * Checks if the user is not certified yet.
     *
     * @return bool
     *
     * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/e.-Groups-and-Rights#verifyNeedCertifyUpgrade
     */
    function verifyNeedCertifyUpgrade()
    {
        if (
            logged_in()
            && !is_certified()
            && (is_verified_manufacturer() || is_verified_seller())
            && 'active' == user_status()
        ) {
            return true;
        }

        return false;
    }
}

if (!function_exists('elasticsearchModelNaming')) {
    /**
     * Create Uuid id for elasticsearch index Help data
     *
     * @return string
     *
     * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/g.-Strings-and-text#elasticsearchmodelnamingint-id-string-type-string
     */
    function elasticsearchModelNaming (int $id, string $type): string
    {
        return Uuid::uuid5(Uuid::NAMESPACE_X500, "{$type}-{$id}")->toString();
    }
}
