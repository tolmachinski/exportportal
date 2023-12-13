<?php

use App\Common\Translations\Translators\ProxyTranslator;

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [03.12.2021]
 * library refactoring: code style
 */
class TinyMVC_Library_ProxyTranslator
{
    public function __construct()
    {
        $translator_ip_port = config('proxytranslator_ip_port');
        $translator_project = config('proxytranslator_project');

        ProxyTranslator::init($translator_project, $translator_ip_port);
    }

    public function fromJson($json)
    {
        return ProxyTranslator::fromJson($json);
    }

    public function getTranslation($module, $idTranslation)
    {
        $proxyT = new ProxyTranslator($module, $idTranslation);

        if (false == $proxyT->fetch()) {
            return false;
        }

        return $proxyT->getTranslations();
    }

    public function initTranslation($config = array())
    {
        assert($config['module']);
        assert($config['fortranslate']);

        $proxyT = new ProxyTranslator($config['module']);
        $proxyT->setJsonString(json_encode($config['fortranslate']));

        if (isset($config['langsTo'])) {
            $proxyT->addLanguagesTo($config['langsTo']);
        }

        if (isset($config['url'])) {
            $proxyT->setUrl($config['url']);
        }

        if (isset($config['customdata'])) {
            $proxyT->setCustomdata($config['customdata']);
        }

        if (false == $proxyT->create()) {
            return false;
        }

        return $proxyT->getId();
    }
}
