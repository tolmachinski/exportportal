<?php
/**
 * Sanitize HTML contents :
 * Remove dangerous tags and attributes that can lead to security issues like
 * XSS or HTTP response splitting.
 *
 * @author  Frederic Minne <zefredz@gmail.com>
 * @copyright Copyright &copy; 2005-2011, Frederic Minne
 * @license http://www.gnu.org/licenses/lgpl.txt GNU Lesser General Public License version 3 or later
 *
 * @version 1.1
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [01.12.2021]
 * library refactoring: code style
 *
 * @link https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/d.-Libraries/e.-CleanHTML
 */
class TinyMVC_Library_CleanHtml
{
    // Private fields
    private $_allowedTags;
    // private $_allowJavascriptEvents;
    private $_allowJavascriptInUrls;
    private $_allowObjects;
    private $_allowScript;
    private $_allowStyle;
    private $_allowNbsp;
    private $_additionalTags;

    private $_style;
    private $_attribute;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->resetAll();
    }

    /**
     * (re)set all options to default value.
     */
    public function resetAll()
    {
        $this->_allowDOMEvents = false;
        $this->_allowJavascriptInUrls = false;
        $this->_allowNbsp = false;
        $this->_allowStyle = false;
        $this->_allowScript = false;
        $this->_allowObjects = false;

        $this->_allowedTags = '';
        $this->_additionalTags = '';

        $this->_style = 'none';
        $this->_attribute = 'none';
    }

    /**
     * Add additional tags to allowed tags.
     *
     * @param string
     * @param mixed $tags
     */
    public function addAdditionalTags($tags)
    {
        $this->_additionalTags .= $tags;
    }

    /**
     * Add additional styles to allowed style.
     *
     * @param string
     * @param mixed $styles
     */
    public function addStyle($styles = 'none')
    {
        $currentStyleAllowed = explode(',', $this->_style);
        $newStyleAllowed = explode(',', $styles);
        $tempStyle = array_merge($currentStyleAllowed, $newStyleAllowed);
        $this->_style = implode(',', $tempStyle);
    }

    /**
     * Add additional attributes to allowed attribute.
     *
     * @param string
     * @param mixed $attributes
     */
    public function addAttribute($attributes = 'none')
    {
        $currentAttrAllowed = explode(',', $this->_attribute);
        $newAttrAllowed = explode(',', $attributes);
        $tempAttribute = array_merge($currentAttrAllowed, $newAttrAllowed);
        $this->_attribute = implode(',', $tempAttribute);
    }

    /**
     * Set styles to allowed style.
     *
     * @param string
     * @param mixed $styles
     */
    public function setStyle($styles = 'none')
    {
        $this->_style = $styles;
    }

    /**
     * Set attributes to allowed attribute.
     *
     * @param string
     * @param mixed $attributes
     */
    public function setAttribute($attributes = 'none')
    {
        $this->_attribute = $attributes;
    }

    /**
     * Set default styles and attributes.
     *
     * @param array - style: list of style, attribute: list of attribute
     * @param mixed $config
     */
    public function defaultTextarea($config = [])
    {
        $this->setStyle('text-decoration');
        $this->setAttribute('style,href,src,alt,title,target,rel,width,height,frameborder,allowfullscreen');

        if (!empty($config['style'])) {
            $this->addStyle($config['style']);
        }

        if (!empty($config['attribute'])) {
            $this->addAttribute($config['attribute']);
        }
    }

    /**
     * Allow iframes.
     */
    public function allowIframes()
    {
        $this->addAdditionalTags('<iframe>');
    }

    /**
     * Allow HTML5 media tags.
     */
    public function allowHtml5Media()
    {
        $this->addAdditionalTags('<canvas><video><audio>');
    }

    /**
     * Allow object, embed, applet and param tags in html.
     */
    public function allowObjects()
    {
        $this->_allowObjects = true;
    }

    /**
     * Allow DOM event on DOM elements.
     */
    public function allowDOMEvents()
    {
        $this->_allowDOMEvents = true;
    }

    /**
     * Allow script tags.
     */
    public function allowScript()
    {
        $this->_allowScript = true;
    }

    /**
     * Allow the use of javascript: in urls.
     */
    public function allowJavascriptInUrls()
    {
        $this->_allowJavascriptInUrls = true;
    }

    /**
     * Allow style tags and attributes.
     */
    public function allowStyle()
    {
        $this->_allowStyle = true;
    }

    public function allowNbsp()
    {
        $this->_allowNbsp = true;
    }

    /**
     * Helper to allow all javascript related tags and attributes.
     */
    public function allowAllJavascript()
    {
        $this->allowDOMEvents();
        $this->allowScript();
        $this->allowJavascriptInUrls();
    }

    /**
     * Allow all tags and attributes.
     */
    public function allowAll()
    {
        $this->allowAllJavascript();
        $this->allowObjects();
        // $this->allowStyle();
        $this->allowNbsp();
        $this->allowIframes();
        $this->allowHtml5Media();
    }

    /**
     * Filter URLs to avoid HTTP response splitting attacks.
     *
     * @param   string url
     * @param mixed $url
     *
     * @return string filtered url
     */
    public function filterHTTPResponseSplitting($url)
    {
        $dangerousCharactersPattern = '~(\r\n|\r|\n|%0a|%0d|%0D|%0A)~';

        return preg_replace($dangerousCharactersPattern, '', $url);
    }

    /**
     * Remove potential javascript in urls.
     *
     * @param   string url
     * @param mixed $str
     *
     * @return string filtered url
     */
    public function removeJavascriptURL($str)
    {
        $HTML_Sanitizer_stripJavascriptURL = 'javascript:[^"]+';

        return preg_replace("/{$HTML_Sanitizer_stripJavascriptURL}/i", '__forbidden__', $str);
    }

    /**
     * Sanitize HTML
     *  remove dangerous tags and attributes
     *  clean urls.
     *
     * @param   string html code
     * @param mixed $html
     *
     * @return string sanitized html code
     */
    public function sanitize($html)
    {
        $html = $this->removeEvilTags($html);

        // $html = $this->removeEvilAttributes( $html );

        $html = $this->removeNbsp($html);

        $html = $this->sanitizeStyle($html, false);

        $html = $this->sanitizeAttributes($html, true);

        $html = $this->sanitizeHref($html);

        return $this->sanitizeSrc($html);
    }

    public function sanitizeUserInput($html)
    {
        $this->addAdditionalTags('<h3><h4><h5><h6><p><span><strong><em><b><i><u><ol><ul><li>');

        $html = $this->removeJavascriptURL($html);

        $html = $this->removeEvilTags($html);

        $this->setStyle('text-decoration');

        $this->setAttribute('style,target,rel');

        $html = $this->removeNbsp($html);

        $html = $this->sanitizeStyle($html, false);

        $html = $this->sanitizeAttributes($html, false);

        $html = $this->sanitizeHref($html);

        return $this->sanitizeSrc($html);
    }

    public function sanitizeUserIframe($html)
    {
        $this->addAdditionalTags('<iframe>');

        $this->setAttribute('src,width,height,frameborder,allowfullscreen,allow');

        // $this->allowStyle();

        $html = $this->removeJavascriptURL($html);

        $html = $this->removeEvilTags($html);

        $html = $this->sanitizeAttributes($html, false);

        // $html = $this->removeEvilAttributes( $html );

        $html = $this->sanitizeHref($html);

        return $this->sanitizeSrc($html);
    }

    public function sanitizeStyle($html, $removeEvil = true)
    {
        if (empty($html)) {
            return false;
        }

        if (empty($this->_style)) {
            return false;
        }

        if ($removeEvil) {
            $html = $this->removeEvilTags($html);
        }

        $allowed = $this->setAllowed($this->_style);
        $allowed = implode('|', $allowed);

        return preg_replace_callback('/\bstyle\s*=\s*(?:"([^"]*)"|\'([^\']*)\')/i', function ($properties) use ($allowed) {
            $property = '';
            preg_match_all("/({$allowed})\\s*:[^\"';]*/i", $properties[1], $matches);
            if (!empty($matches[0])) {
                $property = 'style="' . str_replace("'", '"', implode('; ', $matches[0])) . '"';
            }

            return $property;
        }, $html);
    }

    public function sanitizeAttributes($html, $removeEvil = true)
    {
        if (empty($html)) {
            return false;
        }

        if (empty($this->_attribute)) {
            return false;
        }

        if ($removeEvil) {
            $html = $this->removeEvilAttributes($html);
        }

        $allowed = $this->setAllowed($this->_attribute);

        return preg_replace_callback('/(<[^>]*)\s*(.*?)\s*([^>]*>)/i', function ($innerContent) use ($allowed) {
            $innerContent[0] = preg_replace('/\\s{2,}/', ' ', $innerContent[0]);
            if (!preg_match('#^(<)([a-z0-9\-._:]+)((\s)+(.*?))?((>)([\s\S]*?)((<)\/\2(>))|(\s)*\/?(>))$#im', $innerContent[0], $matches)) {
                return $innerContent[0];
            }

            $matches[5] = preg_replace('#(^|(\s)+)([a-z0-9\-]+)(=)([\'|"])([\'|"])#i', '$1$2$3$4$5<attr:value>$6', $matches[5]);
            $attributes = '';
            if (preg_match_all('#([a-z0-9\-]+)((=)([\'|"])(.*?)([\'|"]))?(?:(\s)|$)#i', $matches[5], $attrs)) {
                $useAttribute = [];
                foreach ($attrs[1] as $i => $attr) {
                    if (!in_array(preg_quote($attr), $allowed)) {
                        continue;
                    }

                    $useAttribute[] = !empty($attrs[0][$i]) ? str_replace('<attr:value>', '', $attrs[0][$i]) : '';
                }
                if (!empty($useAttribute)) {
                    $attributes = ' ' . implode('', $useAttribute);
                }
            }

            return $matches[1] . $matches[2] . $attributes . $matches[6];
        }, $html);
    }

    /**
     * Remove potential flaws in urls.
     *
     * @param   string url
     * @param mixed $url
     *
     * @return string filtered url
     */
    private function sanitizeURL($url)
    {
        if (!$this->_allowJavascriptInUrls) {
            $url = $this->removeJavascriptURL($url);
        }

        return $this->filterHTTPResponseSplitting($url);
    }

    /**
     * Callback for PCRE.
     *
     * @param matches array
     * @param mixed $matches
     *
     * @return string
     *
     * @see sanitizeURL
     */
    private function _sanitizeURLCallback($matches)
    {
        return 'href="' . $this->sanitizeURL($matches[1]) . '"';
    }

    /**
     * Remove potential flaws in href attributes.
     *
     * @param   string html tag
     * @param mixed $str
     *
     * @return string filtered html tag
     */
    private function sanitizeHref($str)
    {
        $HTML_Sanitizer_URL = 'href="([^"]+)"';

        return preg_replace_callback("/{$HTML_Sanitizer_URL}/i", [&$this, '_sanitizeURLCallback'], $str);
    }

    /**
     * Callback for PCRE.
     *
     * @param matches array
     * @param mixed $matches
     *
     * @return string
     *
     * @see sanitizeURL
     */
    private function _sanitizeSrcCallback($matches)
    {
        return 'src="' . $this->sanitizeURL($matches[1]) . '"';
    }

    /**
     * Remove potential flaws in href attributes.
     *
     * @param   string html tag
     * @param mixed $str
     *
     * @return string filtered html tag
     */
    private function sanitizeSrc($str)
    {
        $HTML_Sanitizer_URL = 'src="([^"]+)"';

        return preg_replace_callback("/{$HTML_Sanitizer_URL}/i", [&$this, '_sanitizeSrcCallback'], $str);
    }

    /**
     * Remove dangerous attributes from html tags.
     *
     * @param   string html tag
     * @param mixed $str
     *
     * @return string filtered html tag
     */
    private function removeEvilAttributes($str)
    {
        if (!$this->_allowDOMEvents) {
            $str = preg_replace_callback('/<(.*?)>/i', [&$this, '_removeDOMEventsCallback'], $str);
        }

        // if ( ! $this->_allowStyle )
        // {
        //     $str = preg_replace_callback('/<(.*?)>/i'
        //         , array( &$this, '_removeStyleCallback' )
        //         , $str );
        // }

        return $str;
    }

    /**
     * Remove DOM events attributes from html tags.
     *
     * @param   string html tag
     * @param mixed $str
     *
     * @return string filtered html tag
     */
    private function removeDOMEvents($str)
    {
        $str = preg_replace('/\s*=\s*/', '=', $str);

        $HTML_Sanitizer_stripAttrib = '(onclick|ondblclick|onmousedown|'
            . 'onmouseup|onmouseover|onmousemove|onmouseout|onkeypress|onkeydown|'
            . 'onkeyup|onfocus|onblur|onabort|onerror|onload)'
            ;

        return stripslashes(preg_replace("/{$HTML_Sanitizer_stripAttrib}/i", 'forbidden', $str));
    }

    private function removeNbsp($html)
    {
        if (!$this->_allowedTags) {
            return $html;
        }

        return preg_replace('/\\&nbsp\\;/', ' ', $html);
    }

    /**
     * Callback for PCRE.
     *
     * @param matches array
     * @param mixed $matches
     *
     * @return string
     *
     * @see removeDOMEvents
     */
    private function _removeDOMEventsCallback($matches)
    {
        return '<' . $this->removeDOMEvents($matches[1]) . '>';
    }

    /**
     * Remove style attributes from html tags.
     *
     * @param   string html tag
     * @param mixed $str
     *
     * @return string filtered html tag
     */
    // private function removeStyle( $str )
    // {
    //     $str = preg_replace ( '/\s*=\s*/', '=', $str );

    //     $HTML_Sanitizer_stripAttrib = '(style)'
    //         ;

    //     $str = stripslashes( preg_replace("/$HTML_Sanitizer_stripAttrib/i"
    //         , 'forbidden'
    //         , $str ) );

    //     return $str;
    // }

    /**
     * Callback for PCRE.
     *
     * @param matches array
     *
     * @return string
     *
     * @see removeStyle
     */
    // private function _removeStyleCallback( $matches )
    // {
    //     return '<' . $this->removeStyle( $matches[1] ) . '>';
    // }

    /**
     * Remove dangerous HTML tags.
     *
     * @param   string html code
     *
     * @return string filtered url
     */
    private function removeEvilTags($str)
    {
        $allowedTags = $this->_allowedTags;

        if ($this->_allowScript) {
            $allowedTags .= '<script>';
        }

        // if ( $this->_allowStyle )
        // {
        //     $allowedTags .= '<style>';
        // }

        if ($this->_allowObjects) {
            $allowedTags .= '<object><embed><applet><param>';
        }

        $allowedTags .= $this->_additionalTags;

        return strip_tags($str, $allowedTags);
    }

    private function setAllowed($allowed = '')
    {
        if (empty($allowed)) {
            return false;
        }

        if (!is_array($allowed)) {
            $allowed = explode(',', $allowed);
            $allowed = $this->setAllowed($allowed);
        } else {
            $allowed = array_filter((array) $allowed);
            $allowed = array_map(function ($records) {
                $records = trim($records);

                return preg_quote($records);
            }, $allowed);
        }

        return $allowed;
    }
}

function html_sanitize($str)
{
    static $san = null;

    if (empty($san)) {
        $san = new Tinymvc_Library_Cleanhtml();
    }

    return $san->sanitize($str);
}

function html_loose_sanitize($str)
{
    static $san = null;

    if (empty($san)) {
        $san = new Tinymvc_Library_Cleanhtml();
        $san->allowAll();
    }

    return $san->sanitize($str);
}
