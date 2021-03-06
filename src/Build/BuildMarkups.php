<?php
/**
 * Created by PhpStorm.
 * User: jizhongkai
 * Date: 2019/11/29
 * Time: 14:55
 */

namespace HTML2RAMl\Build;

use PHPHtmlParser\Dom;
use Utility\Utility;

class BuildMarkups
{
    private static $instance;

    /**
     * @return BuildMarkups
     */
    public static function Instance()
    {
        $class = get_called_class();
        if (empty(self::$instance[$class])) {
            self::$instance[$class] = new $class();
        }
        return self::$instance[$class];
    }

    /**
     * 计算起止位置
     * @param Dom\HtmlNode $item
     * @param Dom\HtmlNode $baseItem
     * @return array
     */
    private function getStartEnd($item, $baseItem)
    {
        $itemOut = $item->outerHtml();
        $baseOut = $baseItem->outerHtml();

        $start = Utility::Instance()->util_strpos($baseOut, $itemOut);
        $sub = Utility::Instance()->util_substr($baseOut, 0, $start);
        $sub = Utility::Instance()->trimContent(strip_tags($sub));
        $start = Utility::Instance()->util_strlen($sub);

        $end = Utility::Instance()->util_strlen(Utility::Instance()->trimContent(strip_tags($itemOut))) + $start;

        return [
            'start' => $start,
            'end' => $end,
        ];
    }

    /**
     * span 辅助标记
     * @param Dom\HtmlNode $item
     * @param Dom\HtmlNode $baseItem
     * @return array
     */
    public function buildSpanMarkUp($item, $baseItem)
    {
        $text = Utility::Instance()->trimContent(strip_tags($item->innerHtml()));
        $cssArray = Utility::Instance()->getCssValueFromItem($item); // css value array
        $pos = $this->getStartEnd($item, $baseItem);

        $markUp = [];
        if (!empty($text)) {
            foreach ($cssArray as $css => $value) {
                $markUp[] = [
                    'tag' => 'span',
                    'css' => $css,
                    'value' => $value,
                    'start' => $pos['start'],
                    'end' => $pos['end'],
                    'text' => $text,
                ];
            }
        }

        return $markUp;
    }

    /**
     * strong 辅助标记
     * @param Dom\HtmlNode $item
     * @param Dom\HtmlNode $baseItem
     * @return array
     */
    public function buildStrongMarkUp($item, $baseItem)
    {
        $text = Utility::Instance()->trimContent(strip_tags($item->innerHtml()));
        $pos = $this->getStartEnd($item, $baseItem);

        if (!empty($text)) {
            $markUp = [
                'tag' => 'strong',
                'start' => $pos['start'],
                'end' => $pos['end'],
                'text' => $text
            ];
            return $markUp;
        } else {
            return [];
        }
    }

    /**
     * 句子辅助标记
     * @param $text
     * @param $start
     * @param $end
     * @return array
     */
    public function buildSentenceMarkUp($text, $start, $end)
    {
        if (!empty($text)) {
            $markUp = [
                'tag' => 'sentence',
                'start' => $start,
                'end' => $end,
                'text' => $text
            ];
            return $markUp;
        } else {
            return [];
        }
    }

    /**
     * 超链接辅助标记
     * @param Dom\HtmlNode $item
     * @param Dom\HtmlNode $baseItem
     * @return array
     */
    public function buildAMarkup($item, $baseItem)
    {
        $text = Utility::Instance()->trimContent(strip_tags($item->innerHtml()));
        $url = $item->getAttribute('href');
        $width = $item->getAttribute('data-width');
        $height = $item->getAttribute('data-height');
        $pos = $this->getStartEnd($item, $baseItem);

        if (!empty($url)) {
            $markups = [
                'tag' => 'a',
                'start' => $pos['start'],
                'end' => $pos['end'],
                'source' => $url,
                'text' => $text
            ];

            if (!empty($width)) {
                $markups['width'] = $width;
            }

            if (!empty($height)) {
                $markups['height'] = $height;
            }
            return $markups;
        } else {
            return [];
        }
    }

    /**
     * 拼接属性标签
     * @param $item
     * @param $tagName
     * @param $attrs
     * @return string
     */
    public function buildTag($item, $tagName, $attrs)
    {
        return "<$tagName $attrs>" . $item . "</$tagName>";
    }

    /**
     * em 斜体辅助标记
     * @param $item
     * @param $baseItem
     * @return array
     */
    public function buildEmMarkup($item, $baseItem)
    {
        $text = Utility::Instance()->trimContent(strip_tags($item->innerHtml()));
        $pos = $this->getStartEnd($item, $baseItem);

        if (!empty($text)) {
            $markUp = [
                'tag' => 'em',
                'start' => $pos['start'],
                'end' => $pos['end'],
                'text' => $text
            ];
            return $markUp;
        } else {
            return [];
        }
    }
}