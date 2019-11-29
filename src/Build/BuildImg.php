<?php
/**
 * Created by PhpStorm.
 * User: jizhongkai
 * Date: 2019/11/29
 * Time: 14:54
 */

namespace HTML2RAMl\Build;

use PHPHtmlParser\Dom;

class BuildImg
{
    private static $instance;

    /**
     * @return BuildImg
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

        if (!empty($width)) {
            $ret['image']['width'] = $width;
        }

        if (!empty($height)) {
            $ret['image']['height'] = $height;
        }

        return $ret;
    }


    /**
     * 生成一个html-img节点
     * @param $imgItem
     * @return string
     */
    function buildImgHtml($imgItem)
    {
        if (!empty($imgItem->id)) {
            $attrs = " dataid='{$imgItem->id}'";
        }

        if (!empty($imgItem->image->width)) {
            $attrs = " data-width='{$imgItem->image->width}'";
        }

        if (!empty($imgItem->image->height)) {
            $attrs = " data-height='{$imgItem->image->height}'";
        }

        return "<img {$attrs} src='{$imgItem->image->source}'/>";
    }

}