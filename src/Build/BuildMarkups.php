<?php
/**
 * Created by PhpStorm.
 * User: jizhongkai
 * Date: 2019/11/29
 * Time: 14:55
 */

namespace HTML2RAMl\Build;

use PHPHtmlParser\Dom;
use Utility\Utility\Utility;

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
        $color = Utility::Instance()->getCssValueFromItem($item, 'color');
        $pos = $this->getStartEnd($item, $baseItem);

        if (!empty($text)) {
            $markUp = [
                'tag' => 'span',
                'value' => $color,
                'start' => $pos['start'],
                'end' => $pos['end'],
            ];

            return $markUp;
        } else {
            return [];
        }
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
}