<?php
/**
 * 62进制数据转换
 *
 * @
 */
class Base62
{
    /**
     * @var string $chars 62进制对应的数据
     */
    public static $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

    /**
     * base 62 转 10 进制
     * @param string $str 62 进制
     * @return bool|string
     */
    public function to10($str)
    {
        $out = 0;
        $len = strlen($str) - 1;
        for ($t = 0; $t <= $len; $t++) {
            $out = $out + strpos(self::$chars, substr($str, $t, 1)) * pow(62, $len - $t);
        }
        return substr(sprintf("%f", $out), 0, -7);
    }

    /**
     * 10进制转62进制
     *
     * @param int $i 10 进制数据
     * @return string
     */
    public function to62($i)
    {
        if ($i == 0) return 'a';
        $out = '';
        for ($t = floor(log10($i) / log10(62)); $t >= 0; $t--) {
            $a = floor($i / pow(62, $t));
            $out = $out . substr(self::$chars, $a, 1);
            $i = $i - ($a * pow(62, $t));
        }
        return $out;
    }
}