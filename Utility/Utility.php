<?php
/**
 * Created by PhpStorm.
 * User: jizhongkai
 * Date: 2019/11/25
 * Time: 16:52
 */

require __DIR__ . '/simple_html_dom.php';

use PHPHtmlParser\Dom;

class Utility
{
    private static $instance;
    private static $cnt = 0;

    /**
     * @return Utility
     */
    public static function Instance()
    {
        $class = get_called_class();
        if (empty(self::$instance[$class])) {
            self::$instance[$class] = new $class();
        }
        return self::$instance[$class];
    }

    function html2Str($shtml)
    {
        $html = str_get_html($shtml);
        $this->addId($html);
        return strval($html);
    }
    function addId(&$html)
    {
        foreach ($html->find('*') as $item){
            if (empty($item->dataId)){
                $item->dataId = self::$cnt++;
            }

            $this->addId($item);
        }
    }
    /**
     * @param Dom\HtmlNode $item
     * @return string
     */
    function getId($item)
    {
        return strtolower($item->getAttribute('dataid'));
    }

    /**
     * 清洗
     * @param $item
     * @return string
     */
    function trimContent($item)
    {
        $item = str_replace('&nbsp;', '', $item);
        return trim(html_entity_decode($item));
    }
    /**
     * 清洗多级无用的div
     * @param $html
     * @return string
     */
    function cleanHtml($html)
    {
        do{
            $html = trim($html);
            $inHtml = $html;
            if(mb_substr($html, 0, mb_strlen('<div>', 'utf-8'), 'utf-8') == '<div>'){
                $html = mb_substr($html, mb_strlen('<div>'), null, 'utf-8');
            }
            if(mb_substr($html, mb_strlen($html, 'utf-8')-mb_strlen('</div>', 'utf-8'), mb_strlen('</div>'), 'utf-8')=='</div>'){
                $html = mb_substr($html, 0, mb_strlen($html, 'utf-8')-mb_strlen('</div>', 'utf-8'), 'utf-8');
            }
        }while($inHtml != $html);

        return $html;
    }
    function getHtml($html)
    {
        $ret = '';
        if (!empty($html)){
            $ret = '<div>' . $html . '</div>';
        }
        return $ret;
    }

    /**
     * 获取css样式
     * @param Dom\HtmlNode $item
     * @param $styleName
     * @return string
     */
    function getCssValueFromItem($item, $styleName)
    {
        $styleStr = strtolower($item->getAttribute('style'));
        $styleName = strtolower($styleName);

        $cssArray = explode(";", $styleStr);

        $mValue = '';
        foreach ($cssArray as $cssItem) {
            $cssItemArray = explode(":", $cssItem);
            if (sizeof($cssItemArray) > 1) {
                if (strtolower($cssItemArray[0]) == $styleName) {
                    $mValue = trim($cssItemArray[1]);
                }
            }
        }
        if ($mValue == 'null' || empty($mValue)) {//兼容null值，第一次知道还有null值
            $mValue = $this->getDefaultAttributeValue($styleName);
        }

        return $mValue;
    }
    private $defaultAttribute = [
        'color' => '#333',
        'text-align' => 'left',
    ];
    function getDefaultAttributeValue($key)
    {
        $ret = '';
        foreach ($this->defaultAttribute as $k => $v){
            if ($k == $key){
                $ret = $v;
            }
        }
        return $ret;
    }


    /**
     * 解析辅助标记
     *
     * @param Dom\HtmlNode $sourceItem
     * @param $aml
     * @param Dom\HtmlNode $baseItem
     */
    function parseMarkUp($sourceItem, &$aml, $baseItem = null)
    {
        $children = $sourceItem->getChildren();

        if (empty($baseItem)){
            $baseItem = $sourceItem;
        }
        /**
         * @var Dom\HtmlNode $item
         */
        foreach ($children as $item){
            $outHtml = $this->trimContent($item->outerHtml());
            if (!empty($outHtml)){
                $tag = strtolower($item->getTag()->name());
                if ($tag == 'span'){
                    $aml['text']['markups'][] = Builder::Instance()->buildSpanMarkUp($item, $baseItem);
                }

                if ($tag == 'strong'){
                    $aml['text']['markups'][] = Builder::Instance()->buildStrongMarkUp($item, $baseItem);
                }

                if ($tag == 'a'){
                    $aml['text']['markups'][] = Builder::Instance()->buildAMarkup($item, $baseItem);
                }

                if (get_class($item) == 'PHPHtmlParser\Dom\InnerNode') {
                    $this->parseMarkUp($item, $aml, $sourceItem);
                }
            }
        }
    }

    /**
     * 解析句子格式
     *
     * @param Dom\HtmlNode $item
     * @param $aml
     */
    function parseSentence($item, &$aml)
    {
        $content = $this->trimContent(strip_tags($item->outerHtml()));

        $lastPos = 0;
        $pos = [];
        while (($lastPos = $this->util_strpos($content, '。', $lastPos)) !== false) {
            if ($lastPos > $this->util_strlen($content)) {
                break;
            }
            $pos[] = $lastPos;
            $lastPos = $lastPos + $this->util_strlen('。');
        }

        $start = 0;
        for ($index = 0; $index < count($pos); $index++){
            $len = $pos[$index] - $start;
            $text = $this->util_substr($content, $start, $len);
            $aml['text']['markups'][] = Builder::Instance()->buildSentence($text, $start, $start+$len);
            $start = $pos[$index] + 1;
        }
    }

    /**
     * 截取子串
     *
     * @param $base
     * @param $start
     * @param int $len
     * @return string
     */
    function util_substr($base, $start, $len = 0)
    {
        return mb_substr($base, $start, $len, 'utf-8');
    }

    /**
     * 获取长度
     *
     * @param $str
     * @return int
     */
    function util_strlen($str)
    {
        return mb_strlen($str, 'utf-8');
    }

    /**
     * 获取子串位置
     *
     * @param $str
     * @param $searchStr
     * @param int $offset
     * @return bool|false|int
     */
    function util_strpos($str, $searchStr, $offset = 0)
    {
        return mb_strpos($str, $searchStr, $offset, 'utf-8');
    }

    /**
     * 获取嵌套子标签
     *
     * @param Dom\HtmlNode $item
     * @param $tag
     * @return array
     */
    function getChildrenByTag($item, $tag)
    {
        $children = $item->getChildren();

        $ret = [];
        if (empty($children)){
            return $ret;
        }
        /**
         * @var Dom\HtmlNode $item
         */
        foreach ($children as $item){
            $tagName = strtolower($item->getTag()->name());
            if ($tagName == $tag){
                $ret[] = $item;
            }
        }

        return $ret;
    }
}