<?php
/**
 * class ShortURL
 * usage:
 * 
 * \Qii\Library\ShortURL::build($url);
 */
namespace Qii\Library;

class ShortURL {  
    static $chars = [ "a" , "b" , "c" , "d" , "e" , "f" , "g" , "h" ,  
        "i" , "j" , "k" , "l" , "m" , "n" , "o" , "p" , "q" , "r" , "s" , "t" ,  
        "u" , "v" , "w" , "x" , "y" , "z" , "0" , "1" , "2" , "3" , "4" , "5" ,  
        "6" , "7" , "8" , "9" , "A" , "B" , "C" , "D" , "E" , "F" , "G" , "H" ,  
        "I" , "J" , "K" , "L" , "M" , "N" , "O" , "P" , "Q" , "R" , "S" , "T" ,  
        "U" , "V" , "W" , "X" , "Y" , "Z"  
    ];

    static $secKey = 'sU0dl';
  
    public static function build($url){  
        if($url == null){  
            return null ;  
        }
        $group = [];
        //先得到url的32个字符的md5码  
        $md5 = md5($url);
        //将32个字符的md5码分成4段处理，每段8个字符  
        for ($i = 0; $i < 4 ; $i++) {   
            $offset = i * 8 ;  
            $sub = substr($md5, $offset, $offset + 8);
            $sub16 = base_convert($sub, 16, 10); //将sub当作一个16进制的数，转成long    
            // & 0X3FFFFFFF，去掉最前面的2位，只留下30位  
            $sub16 &= 0X3FFFFFFF ;  
            $shortURL = '';
            //将剩下的30位分6段处理，每段5位  
            for ($j = 0; $j < 6 ; $j++) {  
                //得到一个 <= 61的数字  
                $t = $sub16 & 0x0000003D;
                $shortURL .= self::$chars[$t] ;  
                  
                $sub16 >>= 5 ;  //将sub16右移5位  
            }
            return self::sign($shortURL);
        }
        return null ;  
    } 
    /**
     * 对生成的短链 key 进行签名
     */
    public static function sign($str)
    {
        $sign = md5(self::$secKey . $str);
        return $str . substr($sign, 8, 2);
    }
    /**
     * 验证生成的URL是否正确
     * @param string $str URL 串
     * @return bool
     */
    public static function verify($str)
    {
        $urlSign = substr($str, -2);
        $str = substr($str, 0, -2);
        $sign = md5(self::$secKey . $str);
        if(substr($sign, 8, 2) !== $urlSign) return false;
        return true;
    }
}