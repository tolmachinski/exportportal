<?php

/**
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [01.12.2021]
 * library refactoring
 *
 * @link https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/d.-Libraries/f.-Cookies
 */
class TinyMVC_Library_Cookies
{
    var $time = 1; //default cookies time
	public $exchange_rate_file = 'current_exchange_rate.json';
    var $cookieArray = array();

    public function __construct()
    {
		//echo "constrict";
        $this->cookieArray = $_COOKIE;
        $currency_time = 43200; // 3600 * 12 = 12 hours
		if(!$this->exist_cookie('currency_time') || !$this->exist_cookie('currency_value') || !$this->exist_cookie('currency_code')){
			$this->setCookieParam('currency_value', 1, $currency_time);
			$this->setCookieParam('currency_code', '$', $currency_time);
            if (file_exists($this->exchange_rate_file)) {
                $this->setCookieParam('currency_time', filemtime($this->exchange_rate_file), $currency_time);
            } else {
                $this->setCookieParam('currency_time', 0, $currency_time);
            }
		}

		if(!$this->exist_cookie('currency_key') || !$this->exist_cookie('currency_suffix')){
			$this->setCookieParam('currency_key', 'USD', $currency_time);
			$this->setCookieParam('currency_suffix', 'dollar', $currency_time);
		}
    }

    /**
    * get cookie parameter value by given key
    * @param string $key parameter key
    * @param mixed $nullValue return value for non-existent key
    * @return mixed parameter value
    */
    public function getCookieParam($key, $nullValue = null) {
        return array_key_exists($key, $this->cookieArray) ? $this->cookieArray[$key] : $nullValue;
    }

    /**
     * set cookie parameter value
     * @param string $key parameter key
     * @param string parameter value
     */
    public function setCookieParam($key, $value, $time = 3600) {
        $this->cookieArray[$key] = $value;
        setcookie($key, $value, time()+$time, '/', __JS_COOKIE_DOMAIN, !DEBUG_MODE, FALSE);
    }

    public function removeCookie($key) {
        unset($this->cookieArray[$key]);
        setcookie($key, '', -1, '/', __JS_COOKIE_DOMAIN, !DEBUG_MODE, FALSE);
    }

    public function exist_cookie($key) {
    	if(isset($this->cookieArray[$key]))
    		return true;
    	else
    		return false;
    }
}
