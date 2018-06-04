<?php

namespace Qii\Library\Min;

class CssMin
{
    /**
     * Directory of this stylesheet
     *
     * @var string
     */
    private static $_currentDir = '';

    /**
     * version number
     * @var int
     */
    private static $_version = '';

    /**
     * DOC_ROOT
     *
     * @var string
     */
    private static $_docRoot = '';

    /**
     * directory replacements to map symlink targets back to their source
     * (within the document root) E.g. '/var/www/symlink' => '/var/realpath'
     *
     * @var array
     */
    private static $_symlinks = array();

    /**
     * Path to prepend
     *
     * @var string
     */
    private static $_prependPath = null;

    /**
     * Defines which class to call as part of callbacks, change this if you
     * extend Minify_CSS_UriRewriter
     *
     * @var string
     */
    protected static $className = '\Qii\Library\Min\CssMin';

    /**
     * In CSS content, rewrite file relative URIs as root relative
     *
     * @param string $css
     *
     * @param string $currentDir The directory of the current CSS file.
     * @param string $docRoot The document root of the web site in which the CSS
     *            file resides (default = $_SERVER['DOCUMENT_ROOT']).
     * @param string $version 版本号.
     * @param array $symlinks (default = array()) If the CSS file is stored in a
     *            symlink-ed directory, provide an array of link paths to target
     *            paths, where the link paths are within the document root.
     *            Because paths need to be normalized for this to work, use "//"
     *            to substitute the doc root in the link paths (the array keys).
     *            E.g.: <code> array('//symlink' => '/real/target/path') // unix
     *            array('//static' => '\\staticStorage') // Windows </code>
     * @return string
     */
    public static function rewrite($css, $currentDir, $version = 1, $docRoot = null, $symlinks = array())
    {
        self::$_docRoot = self::_realpath($docRoot ? $docRoot : $_SERVER['DOCUMENT_ROOT']);
        self::$_currentDir = self::_realpath($currentDir);
        self::$_symlinks = array();
        self::$_version = $version;

        // normalize symlinks
        foreach ($symlinks as $link => $target) {
            $link = ($link === '//') ? self::$_docRoot : str_replace('//', self::$_docRoot . '/', $link);
            $link = strtr($link, '/', DIRECTORY_SEPARATOR);
            self::$_symlinks[$link] = self::_realpath($target);
        }
        $css = self::_trimUrls($css);

        // rewrite
        $css = preg_replace_callback("/url\\(\\s*(['\"](.*?)['\"]|[^\\)\\s]+)\\s*\\)/", array(
            self::$className,
            '_processUriCB'
        ), $css);
        $css = self::tripSpace($css);

        return $css;
    }

    /**
     * 去掉空格、注释等内容
     * @param string $css
     * @return string
     */
    public static function tripSpace($css)
    {
        $css = str_replace("\r\n", "\n", $css);
        $search = array("/\/\*[\d\D]*?\*\/|\t+/", "/\s+/", "/\}\s+/");
        $replace = array(null, " ", "}\n");
        $css = preg_replace($search, $replace, $css);
        $search = array("/\\;\s/", "/\s+\{\\s+/", "/\\:\s+\\#/", "/,\s+/i", "/\\:\s+\\\'/i", "/\\:\s+([0-9]+|[A-F]+)/i");
        $replace = array(";", "{", ":#", ",", ":\'", ":$1");
        $css = preg_replace($search, $replace, $css);
        $css = str_replace("\n", null, $css);
        return $css;
    }

    public static function prepend($css, $path)
    {
        self::$_prependPath = $path;

        $css = self::_trimUrls($css);
        // append
        $css = preg_replace_callback('/@import\\s+([\'"])(.*?)[\'"]/', array(
            self::$className,
            '_processUriCB'
        ), $css);
        $css = preg_replace_callback('url\\(\\s*([\'"](.*?)[\'"]|[^\\)\\s]+)\\s*\\)/', array(
            self::$className,
            '_processUriCB'
        ), $css);

        self::$_prependPath = null;
        return $css;
    }

    public static function rewriteRelative($uri, $realCurrentDir, $realDocRoot, $symlinks = array())
    {
        // prepend path with current dir separator (OS-independent)
        $path = strtr($realCurrentDir, '/', DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . strtr($uri, '/', DIRECTORY_SEPARATOR);


        // "unresolve" a symlink back to doc root
        foreach ($symlinks as $link => $target) {
            if (0 === strpos($path, $target)) {
                // replace $target with $link
                $path = $link . substr($path, strlen($target));
                break;
            }
        }
        // strip doc root
        $path = substr($path, strlen($realDocRoot));


        // fix to root-relative URI
        $uri = strtr($path, '/\\', '//');
        $uri = self::removeDots($uri);

        return $uri;
    }

    /**
     * Remove instances of "./" and "../" where possible from a root-relative
     * URI
     *
     * @param string $uri
     *
     * @return string
     */
    public static function removeDots($uri)
    {
        $uri = str_replace('/./', '/', $uri);
        // inspired by patch from Oleg Cherniy
        do {
            $uri = preg_replace('@/[^/]+/\\.\\./@', '/', $uri, 1, $changed);
        } while ($changed);
        return $uri;
    }

    /**
     * Get realpath with any trailing slash removed. If realpath() fails, just
     * remove the trailing slash.
     *
     * @param string $path
     *
     * @return mixed path with no trailing slash
     */
    protected static function _realpath($path)
    {
        $realPath = realpath($path);
        if ($realPath !== false) {
            $path = $realPath;
        }
        return rtrim($path, '/\\');
    }

    /**
     *
     * @param string $css
     *
     * @return string
     */
    private static function _trimUrls($css)
    {
        return preg_replace('/
			url\\(      # url(
			\\s*
			([^\\)]+?)  # 1 = URI (assuming does not contain ")")
			\\s*
		\\)         # )
		/x', 'url($1)', $css);
    }

    /**
     *
     * @param array $m
     *
     * @return string
     */
    private static function _processUriCB($m)
    {
        //$m matched either '/@import\\s+([\'"])(.*?)[\'"]/' or
        // '/url\\(\\s*([^\\)\\s]+)\\s*\\)/'
        $quoteChar = ($m[1][0] === "'" || $m[1][0] === '"') ? $m[1][0] : '';
        $uri = ($quoteChar === '') ? $m[1] : substr($m[1], 1, strlen($m[1]) - 2);
        // if not root/scheme relative and not starts with scheme
        if (!preg_match('~^(/|[a-z]+\:)~', $uri)) {
            // URI is file-relative: rewrite depending on options
            if (self::$_prependPath === null) {

                $uri = self::rewriteRelative($uri, self::$_currentDir, self::$_docRoot, self::$_symlinks);
            } else {
                $uri = self::$_prependPath . $uri;

                if ($uri[0] === '/') {
                    $root = '';
                    $rootRelative = $uri;
                    $uri = $root . self::removeDots($rootRelative);
                } elseif (preg_match('@^((https?\:)?//([^/]+))/@', $uri, $m) && (false !== strpos($m[3], '.'))) {
                    $root = $m[1];
                    $rootRelative = substr($uri, strlen($root));
                    $uri = $root . self::removeDots($rootRelative);
                }
            }
        }

        if (stripos($uri,"?") > 0) {
            $uris = explode("?", $uri);
            $uri = $uris[0];
            $version = '?'. $uris[1] . '&v='. self::$_version;
        }else{
            $version = "?v=" . self::$_version;
        }
        return "url({$quoteChar}{$uri}{$version}{$quoteChar})";
    }
}