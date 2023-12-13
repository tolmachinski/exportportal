<?php

declare(strict_types=1);

use App\Common\Assets\ServiceResetInterface;

if (!function_exists('assets')) {
    /**
     * Returns the assets wrapper library.
     */
    function assets(): TinyMVC_Library_Assets
    {
        return library(TinyMVC_Library_Assets::class);
    }
}

if (!function_exists('asset')) {
    /**
     * Returns the public path of the given asset path (which can be a CSS file, a JavaScript file, an image path, etc.).
     * This function takes into account where the application is installed (e.g. in case the project is accessed in a host subdirectory)
     * and the optional asset package base path.
     */
    function asset(string $path, ?string $packageName = null): ?string
    {
        return assets()->getPackages()->getUrl($path, $packageName);
    }
}

if (!function_exists('assetVersion')) {
    /**
     * Returns the current version of the package.
     */
    function assetVersion(?string $packageName = null): ?string
    {
        return assets()->getPackages()->getVersion('', $packageName);
    }
}

if (!function_exists('webLinks')) {
    /**
     * Returns the web links wrapper library.
     */
    function webLinks(): TinyMVC_Library_Web_Link
    {
        return library(TinyMVC_Library_Web_Link::class);
    }
}

if (!function_exists('preload')) {
    /**
     * Preloads a resource.
     *
     * @param string $uri        Public path to the resource
     * @param array  $attributes The attributes of this link (e.g. "['as' => true]", "['crossorigin' => 'use-credentials']")
     *
     * @return string The path of the asset
     */
    function preload($uri, array $attributes = [])
    {
        return webLinks()->link($uri, 'preload', $attributes);
    }
}

if (!function_exists('dnsPrefetch')) {
    /**
     * Resolves a resource origin as early as possible.
     *
     * @param string $uri        A public path
     * @param array  $attributes The attributes of this link (e.g. "['as' => true]", "['pr' => 0.5]")
     *
     * @return string The path of the asset
     */
    function dnsPrefetch($uri, array $attributes = [])
    {
        return webLinks()->link($uri, 'dns-prefetch', $attributes);
    }
}

if (!function_exists('preconnect')) {
    /**
     * Initiates a early connection to a resource (DNS resolution, TCP handshake, TLS negotiation).
     *
     * @param string $uri        Public path to the resource
     * @param array  $attributes The attributes of this link (e.g. "['as' => true]", "['pr' => 0.5]")
     *
     * @return string The path of the asset
     */
    function preconnect($uri, array $attributes = [])
    {
        return webLinks()->link($uri, 'preconnect', $attributes);
    }
}

if (!function_exists('prefetch')) {
    /**
     * Indicates to the client that this resource should be prefetched.
     *
     * @param string $uri        Public path to the resource
     * @param array  $attributes The attributes of this link (e.g. "['as' => true]", "['pr' => 0.5]")
     *
     * @return string The path of the asset
     */
    function prefetch($uri, array $attributes = [])
    {
        return webLinks()->link($uri, 'prefetch', $attributes);
    }
}

if (!function_exists('prerender')) {
    /**
     * Indicates to the client that this resource should be prerendered.
     *
     * @param string $uri        Public path to the resource
     * @param array  $attributes The attributes of this link (e.g. "['as' => true]", "['pr' => 0.5]")
     *
     * @return string The path of the asset
     */
    function prerender(string $uri, array $attributes = []): string
    {
        return webLinks()->link($uri, 'prerender', $attributes);
    }
}

if (!function_exists('encore')) {
    /**
     * Returns the encore wrapper library.
     */
    function encore(): TinyMVC_Library_Encore
    {
        return library(TinyMVC_Library_Encore::class);
    }
}

if (!function_exists('encoreLinks')) {
    /**
     * Prints block for rendered encore LINK tags.
     */
    function encoreLinks(): void
    {
        echo encore()->getLinksBlockTag();
    }
}

if (!function_exists('encoreScripts')) {
    /**
     * Prints block for rendered encore SCRIPT tags.
     */
    function encoreScripts(): void
    {
        echo encore()->getScriptsBlockTag();
    }
}

if (!function_exists('encoreEntryJsFiles')) {
    /**
     * Returns the list of JS files for given entry.
     */
    function encoreEntryJsFiles(string $entryName, ?string $entrypointName = '_default', bool $reset = false): iterable
    {
        $entrypoint = encore()->getEntrypointsCollection()->getEntrypointLookup($entrypointName ?? '_default');
        if ($reset && $entrypoint instanceof ServiceResetInterface) {
            $entrypoint->resetFiles();
        }

        return $entrypoint->getJSFiles($entryName);
    }
}

if (!function_exists('encoreEntryCssFiles')) {
    /**
     * Returns the list of CSS files for given entry.
     */
    function encoreEntryCssFiles(string $entryName, ?string $entrypointName = '_default', bool $reset = false): iterable
    {
        $entrypoint = encore()->getEntrypointsCollection()->getEntrypointLookup($entrypointName ?? '_default');
        if ($reset && $entrypoint instanceof ServiceResetInterface) {
            $entrypoint->resetFiles();
        }

        return $entrypoint->getCssFiles($entryName);
    }
}

if (!function_exists('encoreEntryLinkTags')) {
    /**
     * Appends the LINK tags for given entrypoint to the HTML body.
     *
     * @param string      $entryName      the name of the entry in the entrypoints file
     * @param null|string $entrypointName the name of the entrypoints file ('_default' used when only one exists)
     * @param null|string $packageName    the name of the assets package
     * @param null|array  $attributes     the list of additional attributes for ALL rendered tags
     * @param null|array  $preload        the preload information ('enable' => true/false, 'importance' => 'auto'/'low'/'high' and 'nopuhs'=> true/false etc.)
     * @param null|bool   $reset          indicates if list of files must be reset
     */
    function encoreEntryLinkTags(
        string $entryName,
        ?string $entrypointName = '_default',
        ?string $packageName = null,
        ?array $attributes = [],
        ?array $preload = null,
        ?bool $reset = false
    ): void {
        encore()->addRenderedLinkEntry(
            rawEncoreEntryLinkTags($entryName, $entrypointName ?? '_default', $packageName, $attributes, $preload, $reset)
        );
    }
}

if (!function_exists('encoreEntryScriptTags')) {
    /**
     * Appends the SCRIPT tags for given entrypoint to the HTML body.
     *
     * @param string      $entryName      the name of the entry in the entrypoints file
     * @param null|string $entrypointName the name of the entrypoints file ('_default' used when only one exists)
     * @param null|string $packageName    the name of the assets package
     * @param null|array  $attributes     the list of additional attributes for ALL rendered tags
     * @param null|array  $preload        the preload information ('enable' => true/false, 'importance' => 'auto'/'low'/'high' and 'nopuhs'=> true/false etc.)
     * @param null|bool   $reset          indicates if list of files must be reset
     */
    function encoreEntryScriptTags(
        string $entryName,
        ?string $entrypointName = '_default',
        ?string $packageName = null,
        ?array $attributes = [],
        ?array $preload = null,
        ?bool $reset = false
    ): void {
        encore()->addRenderedScriptEntry(
            rawEncoreEntryScriptTags($entryName, $entrypointName ?? '_default', $packageName, $attributes, $preload, $reset)
        );
    }
}

if (!function_exists('inlineEncoreEntryLinkTags')) {
    /**
     * Appends the inline STYLES (or LINK) tags for given entrypoint to the HTML body.
     *
     * @param string      $entryName      the name of the entry in the entrypoints file
     * @param null|string $entrypointName the name of the entrypoints file ('_default' used when only one exists)
     * @param null|string $packageName    the name of the assets package
     * @param null|array  $replacements   the list of replacement values
     * @param null|array  $attributes     the list of additional attributes for ALL rendered tags
     * @param null|bool   $reset          indicates if list of files must be reset
     * @param null|bool   $escape         indicates if output must be escaped with CDATA
     */
    function inlineEncoreEntryLinkTags(
        string $entryName,
        ?string $entrypointName = '_default',
        ?string $packageName = null,
        ?array $replacements = [],
        ?array $attributes = [],
        ?bool $reset = false,
        ?bool $escape = true
    ): void {
        encore()->addRenderedLinkEntry(
            rawInlineEncoreEntryLinkTags($entryName, $entrypointName ?? '_default', $packageName, $replacements, $attributes, $reset, $escape)
        );
    }
}

if (!function_exists('inlineEncoreEntryScriptTags')) {
    /**
     * Appends the inline SCRIPT tags for given entrypoint to the HTML body.
     *
     * @param string      $entryName      the name of the entry in the entrypoints file
     * @param null|string $entrypointName the name of the entrypoints file ('_default' used when only one exists)
     * @param null|string $packageName    the name of the assets package
     * @param null|array  $replacements   the list of replacement values
     * @param null|array  $attributes     the list of additional attributes for ALL rendered tags
     * @param null|bool   $reset          indicates if list of files must be reset
     * @param null|bool   $escape         indicates if output must be escaped with CDATA
     */
    function inlineEncoreEntryScriptTags(
        string $entryName,
        ?string $entrypointName = '_default',
        ?string $packageName = null,
        ?array $replacements = [],
        ?array $attributes = [],
        ?bool $reset = false,
        ?bool $escape = true
    ): void {
        encore()->addRenderedScriptEntry(
            rawInlineEncoreEntryScriptTags($entryName, $entrypointName ?? '_default', $packageName, $replacements, $attributes, $reset, $escape)
        );
    }
}

if (!function_exists('rawEncoreEntryLinkTags')) {
    /**
     * Retruns the LINK tags for given entrypoint.
     *
     * @param string      $entryName      the name of the entry in the entrypoints file
     * @param null|string $entrypointName the name of the entrypoints file ('_default' used when only one exists)
     * @param null|string $packageName    the name of the assets package
     * @param null|array  $attributes     the list of additional attributes for ALL rendered tags
     * @param null|array  $preload        the preload information ('enable' => true/false, 'importance' => 'auto'/'low'/'high' and 'nopuhs'=> true/false etc.)
     * @param null|bool   $reset          indicates if list of files must be reset
     */
    function rawEncoreEntryLinkTags(
        string $entryName,
        ?string $entrypointName = '_default',
        ?string $packageName = null,
        ?array $attributes = [],
        ?array $preload = null,
        ?bool $reset = false
    ): string {
        $encore = encore();
        $hasPreload = false;
        if (null !== $preload) {
            $hasPreload = true;
            $encore->changePreloadOptions($preload);
        }
        $renderer = encore()->getTagRenderer();
        if ($reset && $renderer instanceof ServiceResetInterface) {
            $renderer->resetFiles();
        }

        try {
            return $renderer->renderLinkTags($entryName, $entrypointName ?? '_default', $packageName, $attributes ?? [], false, false);
        } finally {
            if ($hasPreload) {
                $encore->resetPreloadOptions();
            }
        }
    }
}

if (!function_exists('rawEncoreEntryScriptTags')) {
    /**
     * Retruns the SCRIPT tags for given entrypoint.
     *
     * @param string      $entryName      the name of the entry in the entrypoints file
     * @param null|string $entrypointName the name of the entrypoints file ('_default' used when only one exists)
     * @param null|string $packageName    the name of the assets package
     * @param null|array  $attributes     the list of additional attributes for ALL rendered tags
     * @param null|array  $preload        the preload information ('enable' => true/false, 'importance' => 'auto'/'low'/'high' and 'nopuhs'=> true/false etc.)
     * @param null|bool   $reset          indicates if list of files must be reset
     */
    function rawEncoreEntryScriptTags(
        string $entryName,
        ?string $entrypointName = '_default',
        ?string $packageName = null,
        ?array $attributes = [],
        ?array $preload = null,
        ?bool $reset = false
    ): string {
        $encore = encore();
        $hasPreload = false;
        if (null !== $preload) {
            $hasPreload = true;
            $encore->changePreloadOptions($preload);
        }
        $renderer = encore()->getTagRenderer();
        if ($reset && $renderer instanceof ServiceResetInterface) {
            $renderer->resetFiles();
        }

        try {
            return $renderer->renderScriptTags($entryName, $entrypointName ?? '_default', $packageName, $attributes ?? [], false, false);
        } finally {
            if ($hasPreload) {
                $encore->resetPreloadOptions();
            }
        }
    }
}

if (!function_exists('rawInlineEncoreEntryLinkTags')) {
    /**
     * Retruns the inline STYLE (or LINK) tags for given entrypoint.
     *
     * @param string      $entryName      the name of the entry in the entrypoints file
     * @param null|string $entrypointName the name of the entrypoints file ('_default' used when only one exists)
     * @param null|string $packageName    the name of the assets package
     * @param null|array  $replacements   the list of replacement values
     * @param null|array  $attributes     the list of additional attributes for ALL rendered tags
     * @param null|bool   $reset          indicates if list of files must be reset
     * @param null|bool   $escape         indicates if output must be escaped with CDATA
     */
    function rawInlineEncoreEntryLinkTags(
        string $entryName,
        ?string $entrypointName = '_default',
        ?string $packageName = null,
        ?array $replacements = [],
        ?array $attributes = [],
        ?bool $reset = false,
        ?bool $escape = true
    ): string {
        $renderer = encore()->getTagRenderer();
        if ($reset && $renderer instanceof ServiceResetInterface) {
            $renderer->resetFiles();
        }
        $content = encore()->getTagRenderer()->renderLinkTags(
            $entryName,
            $entrypointName ?? '_default',
            $packageName,
            $attributes ?? [],
            true,
            $escape ?? true
        );

        return !empty($replacements) ? strtr($content, $replacements) : $content;
    }
}

if (!function_exists('rawInlineEncoreEntryScriptTags')) {
    /**
     * Retruns the inline SCRIPT tags for given entrypoint.
     *
     * @param string      $entryName      the name of the entry in the entrypoints file
     * @param null|string $entrypointName the name of the entrypoints file ('_default' used when only one exists)
     * @param null|string $packageName    the name of the assets package
     * @param null|array  $replacements   the list of replacement values
     * @param null|array  $attributes     the list of additional attributes for ALL rendered tags
     * @param null|bool   $reset          indicates if list of files must be reset
     * @param null|bool   $escape         indicates if output must be escaped with CDATA
     */
    function rawInlineEncoreEntryScriptTags(
        string $entryName,
        ?string $entrypointName = '_default',
        ?string $packageName = null,
        ?array $replacements = [],
        ?array $attributes = [],
        ?bool $reset = false,
        ?bool $escape = true
    ): string {
        $renderer = encore()->getTagRenderer();
        if ($reset && $renderer instanceof ServiceResetInterface) {
            $renderer->resetFiles();
        }
        $content = encore()->getTagRenderer()->renderScriptTags(
            $entryName,
            $entrypointName ?? '_default',
            $packageName,
            $attributes ?? [],
            true,
            $escape ?? true,
        );

        return !empty($replacements) ? strtr($content, $replacements) : $content;
    }
}

if (!function_exists('dispatchDynamicFragment')) {
    /**
     * Dispatches the dynamic fragment.
     */
    function dispatchDynamicFragment(string $name, ?array $args = null, bool $isDeferred = false): string
    {
        $encodedArgs = '';
        if (!empty($args)) {
            $encodedArgs = implode(', ', array_map(function ($args) { return json_encode($args); }, $args ?? []));
            $encodedArgs = ", {$encodedArgs}";
        }
        $resolver = str_replace('[ARGS]', "'{$name}'{$encodedArgs}", preg_replace(
            '/[\\s]+/',
            ' ',
            str_replace(["\n", "\t"], ' ', <<<'RESOLVER'
            if ('dispatchFragment' in window && typeof dispatchFragment === 'function') {
                    window.dispatchFragment([ARGS]);
                } else {
                    throw new SyntaxError("The function 'dispatchFragment' is undefined or it is invalid.");
                }
            RESOLVER)
        ));

        if ($isDeferred) {
            return <<<OUT
            <script>
                (function () {
                    var r=false;
                    var h=function(){ if(r){return}r=true; {$resolver} };
                    var c = function(){ document.removeEventListener("DOMContentLoaded", c), window.removeEventListener("load", c), h(); };
                    "loading" !== document.readyState ? window.setTimeout(h) : (document.addEventListener("DOMContentLoaded", c), window.addEventListener("load", c));
                })();
            </script>
            OUT;
        }

        return <<<OUT
            <script>{$resolver}</script>
            OUT;
    }
}

if (!function_exists('dispatchDynamicFragmentInCompatMode')) {
    /**
     * Dispatches the dynamic fragment in compatibility mode.
     */
    function dispatchDynamicFragmentInCompatMode(string $name, ?string $fallbackSrc, ?string $fallbackBoot = null, ?array $args = null, bool $isDeferred = false): string
    {
        $fallbackBoot = $fallbackBoot ?? 'function () {}';
        $encodedArgs = '';
        if (!empty($args)) {
            $encodedArgs = implode(', ', array_map(function ($args) { return json_encode($args); }, $args ?? []));
            $encodedArgs = ", {$encodedArgs}";
        }
        $resolver = str_replace(['[ARGS]', '[SRC]', '[FUN]'], ["'{$name}'{$encodedArgs}", $fallbackSrc ?? '', $fallbackBoot], preg_replace(
            '/[\\s]+/',
            ' ',
            str_replace(["\n", "\t"], ' ', <<<'RESOLVER'
                if ('ENCORE_MODE' in window && ENCORE_MODE) {
                    if ('dispatchFragment' in window && typeof dispatchFragment === 'function') {
                        window.dispatchFragment([ARGS]);
                    } else {
                        throw new SyntaxError("The function 'dispatchFragment' is undefined or it is invalid.");
                    }
                } else {
                    var fn = [FUN]; var url="[SRC]"; url ? getScript(url, !0).then(fn).catch(console.error) : fn();
                }
            RESOLVER)
        ));

        if ($isDeferred) {
            return <<<OUT
            <script>
                (function () {
                    var r=false;
                    var h=function(){ if(r){return}r=true; {$resolver} };
                    var c = function(){ document.removeEventListener("DOMContentLoaded", c), window.removeEventListener("load", c), h(); };
                    "loading" !== document.readyState ? window.setTimeout(h) : (document.addEventListener("DOMContentLoaded", c), window.addEventListener("load", c));
                })();
            </script>
            OUT;
        }

        return <<<OUT
            <script>{$resolver}</script>
            OUT;
    }
}

// End of file tinymvc_script_assets.php
// Location: /tinymvc/myapp/plugins/tinymvc_script_assets.php
