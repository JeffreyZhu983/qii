<?php
/**
 * 字符串Hash方法
 *
 */
namespace Qii\Library;

class Hash
{
    const HASH_MODE = 701819;
    public function DJBHash($str) // 0.22
    {
        $hash = 0;
        $n = strlen($str);
        for ($i = 0; $i < $n; $i++) {
            $hash += ($hash << 5) + ord($str[$i]);
        }
        return $hash % self::HASH_MODE;
    }
    
    public function ELFHash($str) // 0.35
    {
        $hash = $x = 0;
        $n = strlen($str);
        
        for ($i = 0; $i < $n; $i++) {
            $hash = ($hash << 4) + ord($str[$i]);
            if (($x = $hash & 0xf0000000) != 0) {
                $hash ^= ($x >> 24);
                $hash &= ~$x;
            }
        }
        return $hash % self::HASH_MODE;
    }
    
    public function JSHash($str) // 0.23
    {
        $hash = 0;
        $n = strlen($str);
        
        for ($i = 0; $i < $n; $i++) {
            $hash ^= (($hash << 5) + ord($str[$i]) + ($hash >> 2));
        }
        return $hash % self::HASH_MODE;
    }
    
    public function SDBMHash($str) // 0.23
    {
        $hash = 0;
        $n = strlen($str);
        
        for ($i = 0; $i < $n; $i++) {
            $hash = ord($str[$i]) + ($hash << 6) + ($hash << 16) - $hash;
        }
        return $hash % self::HASH_MODE;
    }
    
    public function APHash($str) // 0.30
    {
        $hash = 0;
        $n = strlen($str);
        
        for ($i = 0; $i < $n; $i++) {
            if (($i & 1) == 0) {
                $hash ^= (($hash << 7) ^ ord($str[$i]) ^ ($hash >> 3));
            } else {
                $hash ^= (~(($hash << 11) ^ ord($str[$i]) ^ ($hash >> 5)));
            }
        }
        return $hash % self::HASH_MODE;
    }
    
    public function DEKHash($str) // 0.23
    {
        $n = strlen($str);
        $hash = $n;
        
        for ($i = 0; $i < $n; $i++) {
            $hash = (($hash << 5) ^ ($hash >> 27)) ^ ord($str[$i]);
        }
        return $hash % self::HASH_MODE;
    }
    
    public function FNVHash($str) // 0.31
    {
        $hash = 0;
        $n = strlen($str);
        
        for ($i = 0; $i < $n; $i++) {
            $hash *= 0x811C9DC5;
            $hash ^= ord($str[$i]);
        }
        
        return $hash % self::HASH_MODE;
    }
    
    public function PJWHash($str) // 0.33
    {
        $hash = $test = 0;
        $n = strlen($str);
        
        for ($i = 0; $i < $n; $i++) {
            $hash = ($hash << 4) + ord($str[$i]);
            
            if (($test = $hash & -268435456) != 0) {
                $hash = (($hash ^ ($test >> 24)) & (~-268435456));
            }
        }
        
        return $hash % self::HASH_MODE;
    }
    
    public function PHPHash($str) // 0.34
    {
        $hash = 0;
        $n = strlen($str);
        
        for ($i = 0; $i < $n; $i++) {
            $hash = ($hash << 4) + ord($str[$i]);
            if (($g = ($hash & 0xF0000000))) {
                $hash = $hash ^ ($g >> 24);
                $hash = $hash ^ $g;
            }
        }
        return $hash % self::HASH_MODE;
    }
    
    public function OpenSSLHash($str) // 0.22
    {
        $hash = 0;
        $n = strlen($str);
        
        for ($i = 0; $i < $n; $i++) {
            $hash ^= (ord($str[$i]) << ($i & 0x0f));
        }
        return $hash % self::HASH_MODE;
    }
    
    public function MD5Hash($str) // 0.050
    {
        $hash = md5($str);
        $hash = ord($hash[0]) | (ord($hash[1]) << 8) | (ord($hash[2]) << 16) | (ord($hash[3]) << 24) | (ord($hash[4]) << 32) | (ord($hash[5]) << 40) | (ord($hash[6]) << 48) | (ord($hash[7]) << 56);
        return $hash % self::HASH_MODE;
    }
}