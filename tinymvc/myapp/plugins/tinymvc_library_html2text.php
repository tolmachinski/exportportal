<?php

/**
 * Class Html2Text_lib
 * Class-wrapper for Html2Text library.
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [02.12.2021]
 * library refactoring code style
 */
class TinyMVC_Library_Html2Text
{
    private $html;
    private $text;

    public function convert($html)
    {
        $htmlConverter = new \Html2Text\Html2Text($html);

        $this->html = $html;
        $this->text = $htmlConverter->getText();

        return $this;
    }

    public function get_text()
    {
        return $this->text;
    }
}
