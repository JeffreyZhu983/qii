<?php
/**
 * Class Trie
 * 关键词过滤
 * 
 * 使用方法
 * $trie = new Qii\Library\Trie();
 * $arr = file('test.txt');
 * $trie->setWords($arr);
 * $trie->search('过滤词aha');
 */

namespace Qii\Library;

class Trie
{
    private $trie;
    
    public function __construct()
    {
        $this->trie = array('children' => array(), 'isWord' => false);
    }
    
    /**
     * 设置过滤的词
     *
     * @param string $word 过滤词
     */
    public function setWord($word)
    {
        $word = trim($word);
        $trieNode = &$this->trie;
        for ($i = 0; $i < strlen($word); $i++) {
            $character = $word[$i];
            if (!isset($trieNode['children'][$character])) {
                $trieNode['children'][$character] = array('isWord' => false);
            }
            if ($i == strlen($word) - 1) {
                $trieNode['children'][$character] = array('isWord' => true);
            }
            $trieNode = &$trieNode['children'][$character];
        }
    }
    
    /**
     * 设置过滤词
     *
     * @param string $words 过滤词
     */
    public function setWords($words)
    {
        if (!is_array($words)) {
            return;
        }
        
        foreach ($words as $word) {
            $this->setWord($word);
        }
    }
    
    /**
     * 是否是过滤的词
     *
     * @param string $word
     * @return bool
     */
    public function isWord($word)
    {
        $trieNode = &$this->trie;
        for ($i = 0; $i < strlen($word); $i++) {
            $character = $word[$i];
            if (!isset($trieNode['children'][$character])) {
                return false;
            } else {
                if ($i == (strlen($word) - 1) && $trieNode['children'][$character]['isWord'] == true) {
                    return true;
                } elseif ($i == (strlen($word) - 1) && $trieNode['children'][$character]['isWord'] == false) {
                    return false;
                }
                $trieNode = &$trieNode['children'][$character];
            }
        }
    }
    
    /**
     * 查找哪些词是在过滤列表中
     *
     * @param string $text
     * @return array
     */
    public function search($text = "")
    {
        $textLen = strlen($text);
        $trieNode = $tree = $this->trie;
        $find = array();
        $wordRootPosition = 0;
        $preNode = false;
        $word = '';
        for ($i = 0; $i < $textLen; $i++) {
            if (isset($trieNode['children'][$text[$i]])) {
                $word = $word . $text[$i];
                $trieNode = $trieNode['children'][$text[$i]];
                if ($preNode == false) {
                    $wordRootPosition = $i;
                }
                $preNode = true;
                if ($trieNode['isWord']) {
                    $find[] = array('position' => $wordRootPosition, 'word' => $word);
                }
            } else {
                $trieNode = $tree;
                $word = '';
                if ($preNode) {
                    $i = $i - 1;
                    $preNode = false;
                }
            }
        }
        return $find;
    }
    
    /**
     * 匹配最长长度
     *
     * @param string $text
     * @return mixed
     */
    public function searchMax($text)
    {
        $textLen = strlen($text);
        $trieNode = $tree = $this->trie;
        $find = array();
        $wordRootPosition = 0;
        $preNode = false;
        $word = '';
        for ($i = 0; $i < $textLen; $i++) {
            if (isset($trieNode['children'][$text[$i]])) {
                $word = $word . $text[$i];
                $trieNode = $trieNode['children'][$text[$i]];
                if ($preNode == false) {
                    $wordRootPosition = $i;
                }
                $preNode = true;
                if ($trieNode['isWord']) {
                    $find[] = array('position' => $wordRootPosition, 'word' => $word);
                }
            } else {
                $trieNode = $tree;
                $word = '';
                if ($preNode) {
                    $i = $i - 1;
                    $preNode = false;
                }
            }
        }
        $n = count($find) - 1;
        return $find[$n];
    }
}
