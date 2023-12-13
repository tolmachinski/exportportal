<?php

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [02.12.2021]
 * library refactoring code style
 */
class TinyMVC_Library_mpdf
{
    public $dpi = 96;
    public $mode = 'utf-8';
    public $format = 'A4';
    public $font_size = 0;
    public $font_family = 'roboto';
    public $margin_left = 0;
    public $margin_right = 0;
    public $margin_top = 5;
    public $margin_bottom = 10;
    public $margin_height = 0;
    public $margin_footer = 0;
    public $orientation = 'P';

    public function config(array $param = [])
    {
        extract($param);

        if (isset($model)) {
            $this->mode = $mode;
        }

        if (isset($format)) {
            $this->format = $format;
        }

        if (isset($font_size)) {
            $this->font_size = $font_size;
        }

        if (isset($font_family)) {
            $this->font_family = $font_family;
        }

        if (isset($margin_left)) {
            $this->margin_left = $margin_left;
        }

        if (isset($margin_right)) {
            $this->margin_right = $margin_right;
        }

        if (isset($margin_top)) {
            $this->margin_top = $margin_top;
        }

        if (isset($margin_bottom)) {
            $this->margin_bottom = $margin_bottom;
        }

        if (isset($margin_height)) {
            $this->margin_height = $margin_height;
        }

        if (isset($margin_footer)) {
            $this->margin_footer = $margin_footer;
        }

        if (isset($orientation)) {
            $this->orientation = $orientation;
        }

        if (isset($dpi)) {
            $this->dpi = $dpi;
        }
    }

    public function new_pdf()
    {
        $defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        return new \Mpdf\Mpdf([
            // Initial configs
            'mode'              => $this->mode,
            'format'            => $this->format,
            'default_font_size' => $this->font_size,
            'default_font'      => $this->font_family,
            'margin_left'       => $this->margin_left,
            'margin_right'      => $this->margin_right,
            'margin_top'        => $this->margin_top,
            'margin_bottom'     => $this->margin_bottom,
            'margin_height'     => $this->margin_height,
            'margin_footer'     => $this->margin_footer,
            'orientation'       => $this->orientation,
            'dpi'               => $this->dpi,
            'fontDir'           => array_merge($fontDirs, ['public/css/fonts']),
            'fontdata'          => $fontData + [
                'roboto' => [
                    'R' => 'Roboto-Regular.ttf',
                    'B' => 'Roboto-Bold.ttf',
                    'M' => 'Roboto-Medium.ttf',
                ],
            ],

            // PDF configs
            'baseScript'        => \Mpdf\Ucdn::SCRIPT_LATIN,
            'useSubstitutions'  => true,
            'autoScriptToLang'  => true,
            'autoLangToFont'    => true,

            // Runtime configs
            'tempDir'           => App\Common\TEMP_PATH,
        ]);
    }

    public function formatFreePdf(array $options = [])
    {
        $defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        return new \Mpdf\Mpdf(array_merge(
            [
                // Initial configs
                'mode'              => $this->mode,
                'fontDir'           => array_merge($fontDirs, ['public/css/fonts']),
                'fontdata'          => $fontData + [
                    'roboto' => [
                        'R' => 'Roboto-Regular.ttf',
                        'B' => 'Roboto-Bold.ttf',
                        'M' => 'Roboto-Medium.ttf',
                    ],
                ],

                // Runtime configs
                'tempDir'           => App\Common\TEMP_PATH,
            ], $options
        ));
    }
}
