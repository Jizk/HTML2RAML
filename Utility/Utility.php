<?php
/**
 * Created by PhpStorm.
 * User: jizhongkai
 * Date: 2019/11/25
 * Time: 16:52
 */

namespace Utility;

require __DIR__ . '/simple_html_dom.php';

use PHPHtmlParser\Dom;
use HTML2RAML\Build\BuildMarkups;

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

    public function html2Str($shtml)
    {
        $html = str_get_html($shtml);
        $this->addId($html);
        return strval($html);
    }

    private function addId(&$html)
    {
        foreach ($html->find('*') as $item) {
            if (empty($item->dataId)) {
                $item->dataId = self::$cnt++;
            }

            $this->addId($item);
        }
    }

    /**
     * @param Dom\HtmlNode $item
     * @return string
     */
    public function getId($item)
    {
        return strtolower($item->getAttribute('dataid'));
    }

    /**
     * 清洗
     * @param $item
     * @return string
     */
    public function trimContent($item)
    {
        $item = str_replace('&nbsp;', '', $item);
        return trim(html_entity_decode($item));
    }

    /**
     * 清洗多级无用的div
     * @param $html
     * @return string
     */
    public function cleanHtml($html)
    {
        do {
            $html = trim($html);
            $inHtml = $html;
            if (mb_substr($html, 0, mb_strlen('<div>', 'utf-8'), 'utf-8') == '<div>') {
                $html = mb_substr($html, mb_strlen('<div>'), null, 'utf-8');
            }
            if (mb_substr($html, mb_strlen($html, 'utf-8') - mb_strlen('</div>', 'utf-8'), mb_strlen('</div>'), 'utf-8') == '</div>') {
                $html = mb_substr($html, 0, mb_strlen($html, 'utf-8') - mb_strlen('</div>', 'utf-8'), 'utf-8');
            }
        } while ($inHtml != $html);

        return $html;
    }

    public function getHtml($html)
    {
        $ret = '';
        if (!empty($html)) {
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
    public function getCssValueFromItem($item, $styleName)
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

    private function getDefaultAttributeValue($key)
    {
        $ret = '';
        foreach ($this->defaultAttribute as $k => $v) {
            if ($k == $key) {
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
     * @param $tagFroA
     */
    public function parseMarkUp($sourceItem, &$aml, $baseItem = null, $tagFroA = 'text')
    {
        $children = $sourceItem->getChildren();

        if (empty($baseItem)) {
            $baseItem = $sourceItem;
        }
        /**
         * @var Dom\HtmlNode $item
         */
        foreach ($children as $item) {
            $outHtml = $this->trimContent($item->outerHtml());
            if (!empty($outHtml)) {
                $tag = strtolower($item->getTag()->name());
                if ($tag == 'span') {
                    $aml['text']['markups'][] = BuildMarkups::Instance()->buildSpanMarkUp($item, $baseItem);
                }

                if ($tag == 'strong') {
                    $aml['text']['markups'][] = BuildMarkups::Instance()->buildStrongMarkUp($item, $baseItem);
                }

                if ($tag == 'a') {
                    $aml[$tagFroA]['markups'][] = BuildMarkups::Instance()->buildAMarkup($item, $baseItem);
                }

                if (get_class($item) == 'PHPHtmlParser\Dom\HtmlNode') {
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
    public function parseSentence($item, &$aml)
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
        for ($index = 0; $index < count($pos); $index++) {
            $len = $pos[$index] - $start;
            $text = $this->util_substr($content, $start, $len);
            $aml['text']['markups'][] = BuildMarkups::Instance()->buildSentenceMarkUp($text, $start, $start + $len);
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
    public function util_substr($base, $start, $len = null)
    {
        return mb_substr($base, $start, $len, 'utf-8');
    }

    /**
     * 获取长度
     *
     * @param $str
     * @return int
     */
    public function util_strlen($str)
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
    public function util_strpos($str, $searchStr, $offset = 0)
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
    public function getChildrenByTag($item, $tag)
    {
        $children = $item->getChildren();

        $ret = [];
        if (empty($children)) {
            return $ret;
        }
        /**
         * @var Dom\HtmlNode $item
         */
        foreach ($children as $item) {
            $tagName = strtolower($item->getTag()->name());
            if ($tagName == $tag) {
                $ret[] = $item;
            }
        }

        return $ret;
    }

    /**
     * 反解析辅助标记
     *
     * @param $item
     * @param $text
     * @return string
     */
    public function reParseMarkup($item, $text)
    {
        $markups = $item->text->markups;
        // 按start倒序
        usort($markups, function ($one, $two) {
            if ($one->start == $two->start) {
                return $two->end - $one->end;
            }
            return $two->start - $one->start;
        });

        $currentText = $text;
        for ($index = 0; $index < sizeof($markups); $index++) {
            $markupItem = $markups[$index];

            if ($markupItem->tag == 'span') {
                if (!empty($markupItem->value)) {
                    $attrs = "style=color:" . $markupItem->value;
                }
                $currentText = $this->insertMarkTag($markupItem, $currentText, 'span', $attrs);
            }
            if ($markupItem->tag == 'strong') {
                $currentText = $this->insertMarkTag($markupItem, $currentText, 'strong', '');

            }
            if ($markupItem->tag == 'a') {
                $attrs = "href=" . $markupItem->source;
                $currentText = $this->insertMarkTag($markupItem, $currentText, 'a', $attrs);
            }
        }
        return $currentText;
    }

    /**
     * 生成一个Html标记
     * @param $markup
     * @param $text
     * @param $tagName
     * @param $attrs
     * @return string
     */
    private function insertMarkTag($markup, $text, $tagName, $attrs)
    {
        $markStart = $this->util_strpos($text, $markup->text, $markup->start);
        $markEnd = $this->util_strlen($markup->text) + $markStart;

        $before = $this->util_substr($text, 0, $markStart);
        $after = $this->util_substr($text, $markEnd);

        return $before . "<$tagName $attrs>" . $markup->text . "</$tagName>" . $after;
    }
}