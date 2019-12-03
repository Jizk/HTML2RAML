<?php
/**
 * Created by PhpStorm.
 * User: jizhongkai
 * Date: 2019/11/29
 * Time: 14:54
 */

namespace HTML2RAMl\Build;

use PHPHtmlParser\Dom;
use Utility\Utility;

class BuildText
{
    private static $instance;

    /**
     * @return BuildText
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
    public function buildTextNode($text, $lineType = '', $align = '', $id)
    {
        $ret = [
            'id' => $id,
            'type' => 0,
            'text' => [
                'text' => $text,
            ]
        ];

        if (!empty($lineType)) {
            $ret['text']['linetype'] = $lineType;
        }

        if (!empty($align)) {
            $ret['text']['align'] = $align;
        }

        return $ret;
    }

    /**
     * li 排版
     * @param $text
     * @param $id
     * @param $order
     * @return array
     */

    public function buildLiNode($text, $id, $order)
    {
        $ret = [
            'id' => $id,
            'type' => 0,
            'text' => [
                'text' => $text
            ],
            'li' => [
                'type' => 'ul',
                'level' => 1,
                'order' => $order,
            ],
            'blockquote' => 0,
        ];

        return $ret;
    }

    /**
     * Li Html
     * @param $item
     * @return string
     */
    public function buildLiHtml($item)
    {
        if (!empty($item->text->markups)) {
            $segement = Utility::Instance()->reParseMarkup($item, $item->text->text);
        } else {
            $segement = $item->text->text;
        }
        if (!empty($item->id)) {
            $attrs = "dataid={$item->id}";
        }

        return "<ul><li {$attrs}>" . $segement . "</li></ul>";
    }
}