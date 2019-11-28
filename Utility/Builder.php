<?php
/**
 * Created by PhpStorm.
 * User: jizhongkai
 * Date: 2019/11/26
 * Time: 16:14
 */

use PHPHtmlParser\Dom;

class Builder
{
    private static $instance;

    /**
     * @return Builder
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
     * 文本段落
     * @param $text
     * @param string $lineType
     * @param string $align
     * @param $id
     * @return array
     */
    function buildTextNode($text, $lineType = '', $align = '', $id)
    {
        $ret = [
            'id' => $id,
            'type' => 0,
            'text' => [
                'text' => $text,
            ]
        ];

        if (!empty($lineType)){
            $ret['text']['linetype'] = $lineType;
        }

        if (!empty($align)){
            $ret['text']['align'] = $align;
        }

        return $ret;
    }

    /**
     * image图片
     * @param Dom\HtmlNode $item
     * @param $id
     * @return array
     */
    function buildImgNode($item, $id)
    {
        $url = $item->getAttribute('src');
        $width = $item->getAttribute('data-width');
        $height = $item->getAttribute('data-height');

        $ret = [
            'id' => $id,
            'type' => 1,
            'image' => [
                'source' => $url,
            ]
        ];

        if (!empty($width)){
            $ret['image']['width'] = $width;
        }

        if (!empty($height)){
            $ret['image']['height'] = $height;
        }

        return $ret;
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
    function buildSpanMarkUp($item, $baseItem)
    {
        $text = Utility::Instance()->trimContent(strip_tags($item->innerHtml()));
        $color = Utility::Instance()->getCssValueFromItem($item, 'color');
        $pos = $this->getStartEnd($item, $baseItem);

        if (!empty($text)){
            $markUp = [
                'tag' => 'span',
                'value' => $color,
                'start' => $pos['start'],
                'end' => $pos['end'],
            ];

            return $markUp;
        }else{
            return [];
        }
    }

    /**
     * strong 辅助标记
     * @param Dom\HtmlNode $item
     * @param Dom\HtmlNode $baseItem
     * @return array
     */
    function buildStrongMarkUp($item, $baseItem)
    {
        $text = Utility::Instance()->trimContent(strip_tags($item->innerHtml()));
        $pos = $this->getStartEnd($item, $baseItem);

        if (!empty($text)){
            $markUp = [
                'tag' => 'strong',
                'start' => $pos['start'],
                'end' => $pos['end'],
            ];
            return $markUp;
        }else{
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
    function buildSentence($text, $start, $end)
    {
        if (!empty($text)){
            $markUp = [
                'tag' => 'sentence',
                'start' => $start,
                'end' => $end,
            ];
            return $markUp;
        }else{
            return [];
        }
    }

    /**
     * 超链接辅助标记
     * @param Dom\HtmlNode $item
     * @param Dom\HtmlNode $baseItem
     * @return array
     */
    function buildAMarkup($item, $baseItem)
    {
        $url = $item->getAttribute('src');
        $width = $item->getAttribute('data-width');
        $height = $item->getAttribute('data-height');
        $pos = $this->getStartEnd($item, $baseItem);

        if (!empty($url)){
            $markups = [
                'tag' => 'a',
                'start' => $pos['start'],
                'end' => $pos['end'],
                'source' => $url,
            ];

            if (!empty($width)){
                $markups['width'] = $width;
            }

            if (!empty($height)){
                $markups['height'] = $height;
            }
            return $markups;
        }else{
            return [];
        }
    }
}