<?php
/*
 
 * @author:zhangwj
 * @data:2012-01-10
 * @content:UBB Convert HTML
 * @email:zhangwj.9991.com@gmail.com
$eq = new UBB($str);初始化类
 
//以下为ubbEncode参数
$eq->url      = true;       //启用url自动解析   默认false
$eq->html     = true;       //启用HTML编码（处理<，>，全角/半角空格，制表符，换行符）默认true
$eq->image    = true;       //启用图象标签解析  默认true
$eq->font     = true;       //启用字体标签解析  默认true
$eq->element  = true;       //启用外部元素解析  默认true
$eq->flash    = true;       //启用Flash解析     默认true
$eq->php      = true;       //启用语法高亮显示  默认true
//ubbEncode参数结束
 
echo($eq->getImageOpener());//输出图片自动缩放所需js函数
echo $eq->htmlEncode();          //输出ubb编码后字符串
echo"<hr>";
echo $eq->ubbEncode();           //输出ubb编码后字符串
echo"<hr>";
echo $eq->removeHtml();          //输出移除html标签的字符串
echo"<hr>";
echo $eq->ubbEncode();           //输出移除ubb标签的字符串
 
 
 
支持ubb标签列表：
 
图片类：
[img]http://www.aaa.com/aaa.gif[/img]    插入图片
[limg]http://www.aaa.com/aaa.gif[/limg]  图片左绕排
[rimg]http://www.aaa.com/aaa.gif[/rimg]  图片右绕排
[cimg]http://www.aaa.com/aaa.gif[/cimg]  图片居中绕排
 
文本控制类：
[br] 换行符
[b]粗体字[b]
[i]斜体字[i]
[u]下划线[u]
[s]删除线[s]
[sub]文字下标[sub]
[sup]文字上标[sup]
[left]文字左对齐[left]
[right]文字右对齐[right]
[center]文字居中[center]
[align=(left|center|right)]文字对齐方式[align]
[size=([1-6])]文字大小[size]
[font=(字体)[font]
[color=(文字颜色)][color]
[list]无序列表[list]
[list=s]有序列表[list]
[list=(A|1|I)]有序列表（列表方式为（abc,123,I II III））[list]
[list=(num)]有序列表（自num开始计数）[list]
[li]列表单元项[li]
 
外部元素类：
[url]链接[/url]
[url=(链接)]链接文字[/url]
[email]邮件地址[/email]
[email=(邮件地址)]说明文字[/email]邮件地址
[quote]引用块[/quote]
[iframe]内插帧地址[/iframe]
[iframe=(内插帧高度)]内插帧地址[/iframe]
[swf]flash动画地址[/swf]
[swf=宽度,高度]flash动画地址[/swf]
 
代码块:
[code][/code]
[php][/php]
[code 代码块名称][/code]
[php 代码块名称][/php]
 
如需使用php语法高亮请务必在代码块两端加上"<??>"标签
*/
namespace Qii\Library;

class UBB {
        var $str           = "";
        var $iconpath      = "/image/icon";//图标文件路径
        var $imagepath     = "/uploads/article";//图片文件默认路径
        //var $tagfoot = ' border="1" onload="ImageLoad(this);" onClick="ImageOpen(this)" style="cursor: hand" ';//图片文件附加属性
        var $tagfoot = ' border="1" style="cursor: hand" ';//图片文件附加属性
 
        var $url     = false;        //url自动解析
        var $html    = true;                //HTML编码
        var $image   = true;                //解析图象标签
        var $font    = true;                //字体标签
        var $element = true;                //外部元素
        var $flash   = true;                //Flash
        var $php     = true;                //语法高亮显示
        var $others  = true;                //ubb转换时候的其他处理
 
        public function __construct($str='',$imgph='') {
                if($str) {
                        $str = strtr($str,array("\n\r"=>"\n","\r\n"=>"\n","\r"=>"\n","　"=>"　"));
                        $this->str = $str;
                }
                if($imgph) $this->imagepath = $imgph;
        }
        public function getImageOpener() {
                return "<script language=\"javascript\" type=\"text/javascript\">\r\nfunction ImageLoad(img) {\r\nif(img.width>480) img.width=480;\r\n}\r\nfunction ImageOpen(img) {\r\nwindow.open(img.src,'','menubar=no,scrollbars=yes,width='+(screen.width-8)+',height='+(screen.height-74)+',left=0,top=0');\r\n}\r\n</script>";
        }
        public function removeHtml($str='') {
                if(!$str) $str = $this->str;
                return strip_tags($str);
        }
        public function removeUbb($str='') {
                if(!$str) $str = $this->str;
                return preg_replace("/\[\/?\w+(\s+[^\]\s]+)*\s*\]/is","",$str);
        }
        public function htmlEncode($str='') {
                if(!$str) $str = $this->str;
                $str = preg_replace("/\n{2,}/s","\n\n",$str);
                return str_replace("\n","\n<br />",$str);
        }
        public function bbcodeurl($url, $tags) {
            if(!preg_match("/<.+?>/s", $url)) {
                return sprintf($tags, $url, addslashes($url));
            } else {
                return '&nbsp;'.$url;
            }
        }
        public function parseimg($width, $height, $src) {
            return $this->bbcodeurl($src, '<img'.($width > 0 ? " width=\"$width\"" : '').($height > 0 ? " height=\"$height\"" : '')." src=\"$src\" border=\"0\" alt=\"\" />");
        }
        public function ubbEncode($str='') {
                if(!$str) $str = $this->str;
                $rpl_ary = array();
                $reg_ary = array();
                if($this->html) $str = $this->htmlEncode($str,true);
                $tagfoot = $this->tagfoot;
                $icon    = $this->iconpath;
                $image   = $this->imagepath;
                if($this->php) {
                        preg_match_all('/(\n\<br \/\>)*\[(php|code)\s*(.*?)\]\s*(.+?)\s*\[\/(php|code)\](\n\<br \/\>)*/is',$str,$ary);
                        $str = preg_split('/(\n\<br \/\>)*\[(php|code)\s*(.*?)\]\s*(.+?)\s*\[\/(php|code)\](\n\<br \/\>)*/is',$str);
                }
                if($this->url){
                        $reg_ary = array_merge($reg_ary,array(
                                '/(?<!\]|\=)\s*(\b\w+@(?:\w+\.)+\w{2,3})\b\s*(?<!\[)/i',
                                '/(?<!\]|\=)\s*(\b(http|https|ftp):\/\/(\w+\.)+\w+(\/[\w|&|%|\?|=|\+|\.|-]+)*)\b\s*(?<!\[)/i',
                        ));
                        $rpl_ary = array_merge($rpl_ary,array(
                                '[email]\\1[/email]',
                                '[url]\\1[/url]',
                        ));
                }
                if($this->image) {
                        $reg_ary = array_merge($reg_ary,array(
                        "/\[img\]\s*([^\[\<\r\n]+?)\s*\[\/img\]/is",
                        "/\[img=(\d{1,4})[x|\,](\d{1,4})\]\s*([^\[\<\r\n]+?)\s*\[\/img\]/is",
                        '/\[img\]\s*http(s?):\/\/(\S+)\s*\[\/img\]/i',//1
                        '/\[limg\]\s*http(s?):\/\/(\S+)\s*\[\/limg\]/i',//2
                        '/\[rimg\]\s*http(s?):\/\/(\S+)\s*\[\/rimg\]/i',//3
                        '/\[cimg\]\s*http(s?):\/\/(\S+)\s*\[\/cimg\]/i',//4
                        '/\[img\]\s*([^\/:]+)\s*\[\/img\]/i',//5
                        '/\[limg\]\s*([^\/:]+)\s*\[\/limg\]/i',//6
                        '/\[rimg\]\s*([^\/:]+)\s*\[\/rimg\]/i',//7
                        '/\[cimg\]\s*([^\/:]+)\s*\[\/cimg\]/i',//8
                        '/\[img\]\s*(\S+)\s*\[\/img\]/is',//9
                        '/\[limg\]\s*(\S+)\s*\[\/limg\]/i',//10
                        '/\[rimg\]\s*(\S+)\s*\[\/rimg\]/i',//11
                        '/\[cimg\]\s*(\S+)\s*\[\/cimg\]/i',//12
                        ));
                        $rpl_ary = array_merge($rpl_ary,array(
                        $this->bbcodeurl('\\1', '<img src="%s" '.$tagfoot.'/>'),
                        $this->parseimg('\\1', '\\2', '\\3'),
                        '<img src="http\1://\2"'.$tagfoot.'>',//1
                        '<img src="http\1://\2"'.$tagfoot.' align="left">',//2
                        '<img src="http\1://\2"'.$tagfoot.' align="right">',//3
                        '<div align="center"><img src="http\1://\2"'.$tagfoot.'></div>',//4
                        '<img src="'.$image.'/\1"'.$tagfoot.'>',//5
                        '<img src="'.$image.'/\1"'.$tagfoot.' align="left">',//6
                        '<img src="'.$image.'/\1"'.$tagfoot.' align="right">',//7
                        '<div align="center"><img src="'.$image.'/\1"'.$tagfoot.'></div>',//8
                        '<img src="\1"'.$tagfoot.'>',//9
                        '<img src="\1"'.$tagfoot.' align="left">',//10
                        '<img src="\1"'.$tagfoot.' align="right">',//11
                        '<div align="center"><img src="\1"'.$tagfoot.'></div>',//12
                        ));
                }
                if($this->font) {
                        $reg_ary = array_merge($reg_ary,array(
                        '/\[br\]/i',
                        '/\[b\]/i',
                        '/\[\/b\]/i',
                        '/\[i(=s)?\]\s*(.+?)\s*\[\/i\]/is',
                        '/\[u\]\s*(.+?)\s*\[\/u\]/is',
                        '/\[s\]\s*(.+?)\s*\[\/s\]/is',
                        '/\[sub\]\s*(.+?)\s*\[\/sub\]/is',
                        '/\[sup\]\s*(.+?)\s*\[\/sup\]/is',
                        '/\[left\]/i',
                        '/\[\/left\]/i',
                        '/\[right\]/i',
                        '/\[\/right\]/i',
                        '/\[center\]/i',
                        '/\[\/center\]/i',
                        '/\[align=\s*(left|center|right)\]/i',
                        '/\[\/align\]/i',
                        '/\[size=\s*([\.|\d])\s*\]/i',
                        '/\[\/size\]/i',
                        '/\[size=(\d+(\.\d+)?(px|pt|in|cm|mm|pc|em|ex|%)+?)\]/i',
                        '/\[font=\s*(.*?)\s*\]/i',
                        '/\[\/font\]/i',
                        '/\[color=\s*(.*?)\s*\]/i',
                        '/\[\/color\]/i',
                        '/\[list\]/i',
                        '/\[\/list\]/i',
                        '/\[list=s\]/i',
                        '/\[\/list\]/i',
                        '/\[list=(A|1|I)\]/i',
                        '/\[\/list\]/i',
                        '/\[list=(\S+?)\]/i',
                        '/\[\/list\]/i',
                        '/\[li\]/i',
                        '/\[\/li\]/i',
                        '/\[p=(\d{1,2}), (\d{1,2}), (left|center|right)\]/i',
                        '/\[float=(left|right)\]/i'
                        ));
                        $rpl_ary = array_merge($rpl_ary,array(
                        '<br />',
                        '<b>',
                        '</b>',
                        '<i>\\2</i>',
                        '<u>\\1</u>',
                        '<s>\\1</s>',
                        '<sub>\\1</sub>',
                        '<sup>\\1</sup>',
                        '<div align="left">',
                        '</div>',
                        '<div align="right">',
                        '</div>',
                        '<div align="center">',
                        '</div>',
                        '<div align="\\1">',
                        '</div>',
                        '<font size="\\1pt">',
                        '</font>',
                        '<font style="font-size: \\1">',
                        '<font face="\\1">',
                        '</font>',
                        '<font color="\\1">',
                        '</font>',
                        '<ul>',
                        '</ul>',
                        '<ol>',
                        '</ol>',
                        '<ol type="\\1">',
                        '</ol>',
                        '<ol start="\\1">',
                        '</ol>',
                        '<li>',
                        '</li>',
                        '<p style="line-height: \\1px; text-indent: \\2em; text-align: \\3;">',
                        '<span style="float: \\1;">'
                        ));
                }
                if($this->element){
                        $reg_ary = array_merge($reg_ary,array(
                        '/\[url=\s*(.+?)(,1)?\s*\]\s*(.+?)\s*\[\/url\]/i',
                        '/\[url]\s*(.+?)\s*\[\/url\]/i',
                        '/\[email=\s*(.+?)\s*\]\s*(.+?)\s*\[\/email\]/i',
                        '/\[email]\s*(.+?)\s*\[\/email\]/i',
                        '/\[quote\]\s*(<br \/>)?\s*(.+?)\s*\[\/quote\]/is',
                        '/\[iframe\]\s*(.+?)\s*\[\/iframe\]/is',
                        '/\[iframe=\s*(\d+?)\s*\]\s*(.+?)\s*\[\/iframe\]/is',
                        ));
                        $rpl_ary = array_merge($rpl_ary,array(
                        '<a href="\1" target="_blank">\3</a> ',
                        '<a href="\1" target="_blank">\1</a> ',
                        '<a href="mailto:\1">\2</a> ',
                        '<a href="mailto:\1">\1</a> ',
                        '<table cellpadding="0" cellspacing="0" border="0" width="90%" align="center" style="border:1px gray solid;"><tr><td><table width="100%" cellpadding="5" cellspacing="1" border="0"><tr><td width="100%">\2</td></tr></table></td></tr></table>',
                        '<iframe src="\" name="ifr1" frameborder="0" allowtransparency="true" scrolling="yes" width="100%" height="340" marginwidth="0" marginheight="0" hspace="0" vspace="0">\1</iframe><br><a href="\1" target="_blank">如果你的浏览器不支持嵌入框，请点这里查看</a>',
                        '<iframe src="\2" name="ifr1" frameborder="0" allowtransparency="true" scrolling="yes" width="100%" height="\1" marginwidth="0" marginheight="0" hspace="0" vspace="0">\2</iframe><br><a href="\2" target="_blank">如果你的浏览器不支持嵌入框，请点这里查看</a>',
                        ));
                }
                if($this->flash){
                        $reg_ary = array_merge($reg_ary,array(
                        '/\[swf\]\s*(.+?)\s*\[\/swf\]/i',
                        '/\[swf=(\d+)\,(\d+)\]\s*(.+?)\s*\[\/swf\]/i'
                        ));
                        $rpl_ary = array_merge($rpl_ary,array(
                        '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0"><param name="movie" value="\1" /><param name="quality" value="high" /><embed src="\1" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash"></embed></object>',
                        '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0" width="\1" height="\2"><param name="movie" value="\3" /><param name="quality" value="high" /><embed src="\3" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" width="\1" height="\2"></embed></object>'
                        ));
                }
                if($this->others){//处理特殊ubb问题
                        $reg_ary = array_merge($reg_ary,array(
                        '/\[upload=\d+]/i',
                        '/\[attachment=(\d+?)\]/i',
                        '/\[attach\]\s*(\d+?)\s*\[\/attach\]/i',
                        '/\[qq\]\s*(\d+?)\s*\[\/qq\]/i',
                        '/\[indent\]/i',
                        '/\[\/indent\]/i'
                        ));
                        $rpl_ary = array_merge($rpl_ary,array(
                        '', 
                        '',
                        '\\1',
                        'QQ:\\1',
                        '<blockquote>',
                        '</blockquote>'                      
                        ));
                }
                if(sizeof($reg_ary)&&sizeof($rpl_ary))$str = preg_replace($reg_ary,$rpl_ary,$str);
                if($this->php) {
                        $tmp = $str[0];
                        for($i=0; $i<sizeof($ary[4]); $i++) {
                                ob_start();
                                highlight_string(trim(strtr($ary[4][$i],array('&lt;'=>'<','&gt;'=>'>',"&nbsp;"=>" ","<br />"=>""))));
                                $tmp .= '<table border=1 cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#055AA0" width=95%><tr><td><code>'.(trim($ary[3][$i])?trim($ary[3][$i]):'代码片段:').'</code><br /><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td width="100%" class="code">'.ob_get_contents().'</td></tr></table></td></tr></table>'.$str[$i+1];
                                ob_end_clean();
                        }
                        $str = $tmp;
                        unset($tmp);
                }
                return $str;
        }
}