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
     * @param Dom\HtmlNode $item
     * @param Dom\HtmlNode $baseItem
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
     * @param Dom\HtmlNode $item
     * @param Dom\HtmlNode $baseItem
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
     * @param Dom\HtmlNode $item
     * @param Dom\HtmlNode $baseItem
     */
    function getStartEnd($item, $baseItem)
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
}