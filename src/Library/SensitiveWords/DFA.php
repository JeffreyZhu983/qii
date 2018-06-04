<?php
/**
 * DFA 关键词过滤
 * @link https://blog.csdn.net/u013303402/article/details/79218554
 *
 * @useage:
 *
 * $filter = new \Qii\Library\SensitiveWords\DFA();
 * $filter->setDefaultKeywords();
 * $result = $filter->searchKey('关键词');
 *
 */
namespace Qii\Library\SensitiveWords;

class DFA
{
    private $arrHashMap = [];

    public function getHashMap()
    {
        return $this->arrHashMap;
    }

    /**
     * 设置默认词库
     *
     */
    public function setDefaultKeywords()
    {
        $this->setTreeByFile(__DIR__ . DS . 'Words.txt');
    }

    /**
     * 设置默认词库
     *
     * @param string $file 文件路径
     *
     * @throws \Exception
     */
    public function setTreeByFile($file)
    {
        if(!is_file($file)) throw new \Exception('敏感词文件不存在', __LINE__);

        $fp = fopen($file, 'ra');

        if(!$fp) throw new \Exception('读取文件失败', __LINE__);

        while(!feof($fp))
        {
            $keyword = trim(fgets($fp));
            $this->addKeyWord($keyword);
        }
    }

    public function addKeyWord($strWord)
    {
        $len = mb_strlen($strWord, 'UTF-8');

        // 传址
        $arrHashMap = &$this->arrHashMap;
        for ($i = 0; $i < $len; $i++) {
            $word = mb_substr($strWord, $i, 1, 'UTF-8');
            // 已存在
            if (isset($arrHashMap[$word])) {
                if ($i == ($len - 1)) {
                    $arrHashMap[$word]['end'] = 1;
                }
            } else {
                // 不存在
                if ($i == ($len - 1)) {
                    $arrHashMap[$word] = [];
                    $arrHashMap[$word]['end'] = 1;
                } else {
                    $arrHashMap[$word] = [];
                    $arrHashMap[$word]['end'] = 0;
                }
            }
            // 传址
            $arrHashMap = &$arrHashMap[$word];
        }
    }

    public function searchKey($strWord)
    {
        $len = mb_strlen($strWord, 'UTF-8');
        $arrHashMap = $this->arrHashMap;
        for ($i = 0; $i < $len; $i++) {
            $word = mb_substr($strWord, $i, 1, 'UTF-8');
            if (!isset($arrHashMap[$word])) {
                // reset hashmap
                $arrHashMap = $this->arrHashMap;
                continue;
            }
            if ($arrHashMap[$word]['end']) {
                return true;
            }
            $arrHashMap = $arrHashMap[$word];
        }
        return false;
    }
}