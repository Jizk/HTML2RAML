<?php
/**
 * Created by PhpStorm.
 * User: jizhongkai
 * Date: 2019/11/29
 * Time: 14:55
 */

namespace HTML2RAMl\Build;

use PHPHtmlParser\Dom;

class BuildVideo
{
    private static $instance;

    /**
     * @return BuildVideo
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
     * video 视频
     * @param Dom\HtmlNode $item
     * @param $id
     * @return array
     */
    function buildVideoNode($item, $id)
    {
        $url = $item->getAttribute('src');
        $cover = $item->getAttribute('controls poster');

        $ret = [
            'id' => $id,
            'type' => 2,
            'media' => [
                'source' => $url
            ],
        ];

        if (!empty($cover)) {
            $ret['media']['cover'] = $cover;
        }

        return $ret;
    }

    /**
     * 生成一个html-video节点
     * @param $videoItem
     * @return string
     */
    function buildVideoHtml($videoItem)
    {
        if (!empty($videoItem->id)){
            $attrs = " dataid='{$videoItem->id}'";
        }

        if (!empty($videoItem->media->cover)){
            $attrs = " controls poster='{$videoItem->media->cover}'";
        }

        return "<video {$attrs} src='{$videoItem->media->source}'/>";
    }
}