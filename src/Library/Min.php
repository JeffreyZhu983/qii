<?php
namespace Qii\Library;

use Qii\Library\Min\CssMin;
use Qii\Library\Min\JSMin;

class Min
{
    const CSS_TYPE = 'text/css';
    const JS_TYPE = 'application/x-javascript';

    const CSS = 'css';
    const JS = 'js';

    /**
     * Min constructor.
     */
    public function __construct()
    {

    }

    /**
     * Min constructor.
     * @param array $map  ['type' => 'js/css', 'files' => []]
     * @return string
     */
    public function minify($map)
    {
        if(empty($map)) return;
        if(empty($map['type'])) return;
        if(!in_array($map['type'], [self::CSS, self::JS]))
        {
            throw new MinException('Unsupported files');
        }
        switch ($map['type'])
        {
            case self::CSS:
                return $this->minifyCSS($map['files'], $map['root'] ?? '', $map['version'] ?? '');
                break;
            case self::JS:
                return $this->minifyJS($map['files']);
                break;
            default:
                return '';
                break;
        }
        return '';
    }

    public function minifyCSS($files, $root = '', $version = '')
    {
        if(is_array($files))
        {
            foreach($files AS $file)
            {
                $css[] = $this->minifyCSS($file);
            }
            return join("\n", $css);
        }
        if(!is_file($files))
        {
            return "/*文件'". $files ."'未找到*/";
        }
        $content = $this->getContent($files);
        $currentDir = dirname($files);
        return CssMin::rewrite($content, $currentDir, $version, $root);
    }

    /**
     * @param array $files 文件列表
     * @return array
     */
    public function minifyJS($files)
    {
        if(is_array($files))
        {
            $js = [];
            foreach ($files as $file)
            {
                $js[] = $this->minifyJS($file);
            }
            return join("\n", $js);
        }
        return JSMin::minify($this->getContent($files));
    }

    /**
     * 返回文件内容
     * @param string $file 文件名称
     * @return bool|string
     */
    protected function getContent($file)
    {
        $content = file_get_contents($file);
        // remove UTF-8 BOM
        return (pack("CCC", 0xef, 0xbb, 0xbf) === substr($content, 0, 3)) ? substr($content, 3) : $content;
    }
    /**
     * @param string $type 类型
     */
    public function sendHeader($type)
    {
        $header = [
            'js' => 'Content-Type: application/x-javascript',
            'css' => 'Content-Type: text/css',
        ];
        if(!isset($header[$type]))
        {
            return;
        }
        header("Access-Control-Allow-Origin:'*'");
        header("Expires: " . date("D, j M Y H:i:s", strtotime("now + 10 years")) ." GMT");
        header($header[$type]);
        return $this;
    }
}

class MinException extends \Exception{

}