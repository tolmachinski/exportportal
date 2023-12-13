<?php

namespace App\Common\Translations\Translators;

use App\Common\Translations\Exceptions\ProxyTranslatorException;

//how do I add config data (url, port)?

class ProxyTranslator
{
    public $errors;
    private $_source = array(
        'lang_from' => 'en', // default value
        'langs_to'  => array(),
    );

    private static $_validLangs = array(
        'en', 'cn', 'es', 'ru', 'fr',
        'de', 'it', 'ro', 'hi', 'iw',
        'vi', 'si', 'ta', 'sq', 'ar',
        'pt',
    );

    private static $_project;
    private static $_httpProxyAddress;
    private static $_init = false;

    public function __construct($type, $id = null)
    {
        if (false == self::$_init) {
            self::_throw('class not initialized');
        }

        self::_validateText('type', $type);
        $this->_type = $type;

        $this->_id = $id;
    }

    public static function init($project, $httpProxyAddress)
    {
        self::_validateText('project', $project);
        self::$_project = $project;

        self::_validateText('httpProxyAddress', $httpProxyAddress);
        self::$_httpProxyAddress = $httpProxyAddress;

        self::$_init = true;
    }

    public static function fromJson($jsonString)
    {
        $json = json_decode($jsonString, true);

        $instance = new ProxyTranslator($json['_type'], $json['_id']);
        $instance->_fill_from_array($json);

        return $instance;
    }

    public static function getProject()
    {
        return self::$_project;
    }

    public function getType()
    {
        return $this->_type;
    }

    public function getId()
    {
        return $this->_id;
    }

    public function setLanguageFrom($langFrom)
    {
        self::_validateText('langTo', $langFrom);
        self::_validateLang($langFrom);

        $this->_source['lang_from'] = $langFrom;
    }

    public function getLanguagesTo()
    {
        return $this->_source['langs_to'];
    }

    public function addLanguagesTo($langsTo)
    {
        if (!is_array($langsTo)) {
            self::_throw('langsTo is not array');
        }

        $langsToLen = count($langsTo);
        if (0 == $langsToLen) {
            self::_throw('langsTo is an empty array');
        }

        foreach ($langsTo as $key => $langTo) {
            self::_validateText("langsTo[{$key}]", $langTo);
            self::_validateLang($langTo);
        }

        $langsTo = array_merge($langsTo, $this->_source['langs_to']);
        $this->_source['langs_to'] = array_unique($langsTo);
    }

    public function addLanguageTo($langTo)
    {
        self::_validateText('langTo', $langTo);
        self::_validateLang($langTo);

        array_push($this->_source['langs_to'], $langTo);
        $this->_source['langs_to'] = array_unique($this->_source['langs_to']);
    }

    public function setJsonString($jsonstring)
    {
        $this->_source['jsonstring'] = $jsonstring;
    }

    public function getJsonString()
    {
        return $this->_source['jsonstring'];
    }

    public function getCustomdata()
    {
        return $this->_source['customdata'];
    }

    public function setCustomdata($customdata)
    {
        self::_validateText('customdata', $customdata);
        $this->_source['customdata'] = $customdata;
    }

    public function setUrl($url)
    {
        self::_validateText('url', $url);
        //todo: php validate url
        $this->_source['url'] = $url;
    }

    public function getUrl()
    {
        return $this->_source['url'];
    }

    public function getTranslations()
    {
        return array_map(function ($el) {
            $el['translation'] = json_decode($el['jsonstring'], true);
            unlink($el['jsonstring']);

            return $el;
        }, $this->_source['translations']);
    }

    public function update()
    {
        return $this->_http_request('PUT');
    }

    public function fetch()
    {
        return $this->_http_request('GET');
    }

    public function create()
    {
        return $this->_http_request('POST');
    }

    public function delete()
    {
        return $this->_http_request('DELETE');
    }

    private static function _validateLang($langTo)
    {
        if (!in_array($langTo, self::$_validLangs)) {
            self::_throw("not valid lang '{$langTo}'");
        }
    }

    private static function _validateText($paramName, $lang)
    {
        if (is_null($lang)) {
            self::_throw("'{$paramName}' is null");
        }

        if (!is_string($lang)) {
            self::_throw("'{$paramName}' is not a string");
        }

        if (empty(trim($lang))) {
            self::_throw("'{$paramName}' is not a string");
        }
    }

    private static function _throw($msg)
    {
        throw new ProxyTranslatorException($msg);
    }

    private function _get_json_source()
    {
        $filtered_array = array_filter($this->_source, function ($el) {
            return !empty($el);
        });

        if (empty($filtered_array)) {
            self::_throw('empty json data');
        }

        return json_encode($filtered_array);
    }

    private function _http_request($method)
    {
        $url = self::$_httpProxyAddress . '/' . self::$_project . "/{$this->_type}";
        if ('POST' == $method) {
            if (!is_null($this->_id)) {
                self::_throw('POST method and not null id');
            }
        } else {
            if (is_null($this->_id)) {
                self::_throw('non POST method and null id');
            }
            $url = $url . '/' . $this->_id;
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if ('POST' == $method || 'PUT' == $method) {
            $data_string = $this->_get_json_source();
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt(
                $ch,
                CURLOPT_HTTPHEADER,
                array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string), )
            );
        }

        $result = curl_exec($ch);
        if (false == $result) {
            //good news. curl_exex did not work
            self::_throw('curl_exec: url = ' . $url);
        }

        return $this->_fill_from_array(json_decode($result, true));
    }

    private function _fill_from_array($json)
    {
        if (!empty($json['errors'])) {
            $this->errors = $json['errors'];

            return false;
        }

        if (empty($json['_id'])) {
            self::_throw('empty _id');
        }

        $this->_id = $json['_id'];
        $this->_source = $json['_source'];

        return true;
    }
}

//php create translation
//
/*
ProxyTranslator::init("project", "http://localhost:5000");
echo "creating...";
$googleTranslation = new ProxyTranslator("type1");
$googleTranslation->addLanguageTo("de");
$googleTranslation->addText("hello");

if($googleTranslation->create() == false) {
    echo " - errors ";
} else {
    echo " - ok";
};
echo PHP_EOL . PHP_EOL;

echo "\t\tsleeping for 10 seconds".PHP_EOL.PHP_EOL;
sleep(10);

echo "fetching...";
if($googleTranslation->fetch() == false) {
    echo " - errors ";
} else {
    echo " translation - ";
};
echo PHP_EOL . PHP_EOL;

echo "updating...";
$googleTranslation->addLanguageTo("ro");
if($googleTranslation->update() == false) {
    echo " - errors ";
} else {
    echo " - ok";
}
echo PHP_EOL . PHP_EOL;

echo "\t\tsleeping for 10 seconds".PHP_EOL.PHP_EOL;
sleep(10);

echo "fetching...";
if($googleTranslation->fetch() == false) {
    echo " - errors ";
} else {
    echo " translation - ";
};
echo PHP_EOL . PHP_EOL;


echo "deleting...";
if($googleTranslation->delete() == false) {
    echo " - errors ";
} else {
    echo " - ok";
}
 */

/*
$str = '{ "_id": "nsjavWEB1Ld2x0a5Bk_3", "_index": "project", "_source": { "created_at": "2018-02-22T16:10:56.920774", "lang_from": "en", "langs_to": [ "de", "ro" ], "text": "hello", "translations": [ { "lang_to": "de", "text": "Hallo" }, { "lang_to": "ro", "text": "buna" } ] }, "_type": "type1" }';
ProxyTranslator::init("project", "http://localhost:5000");
$googleTranslation = ProxyTranslator::fromJson($str);
if($googleTranslation->update() == false) {
    echo "errors - ";
} else {
    echo " update - ok";
};
 */
