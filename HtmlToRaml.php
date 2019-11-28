<?php

require 'vendor/autoload.php';
require './Utility/Utility.php';
require './Utility/Builder.php';

use PHPHtmlParser\Dom;

class HtmlToRaml
{
    /**
     * @throws \PHPHtmlParser\Exceptions\UnknownChildTypeException
     */
    public function html2Raml()
    {
        $content = file_get_contents(__DIR__ . '/Test/demo2.html');
//        $content = $this->cleanHtml($content);
//        $content = $this->getHtml($content);
        $content = "<body>" . $content . "</body>";
        $html = Utility::Instance()->html2Str($content);

        $dom = new Dom;
        $dom->loadStr($html, []);

        if (empty($dom->root->firstChild()->getChildren()[1])) {
            $root = $dom->root->firstChild()->getChildren()[0];
        } else {
            $root = $dom->root->firstChild()->getChildren()[1];
        }

        return $this->parserHtml($root);
    }

    private static $hTagNameArray = ['h1', 'h2', 'h3'];

    /**
     * 解析Html
     * @param  Dom\HtmlNode $item
     * @return array
     * @throws \PHPHtmlParser\Exceptions\UnknownChildTypeException
     */
    private function parserHtml($item)
    {
        $ret = [];
        $children = $item->getChildren();

        /**
         * @var Dom\HtmlNode $item
         */
        foreach ($children as $item) {
            $outerHtml = Utility::Instance()->trimContent($item->outerHtml());
            if (empty($outerHtml)) {
                continue;
            }
            $tag = strtolower($item->getTag()->name());
            if (!empty($outerHtml)) {
                if ($tag == 'p'
                    || $tag == 'img'
                    || $tag == 'ul'
                    || in_array($tag, self::$hTagNameArray)
                    || $tag == 'video'
                ) {
                    $innerContent = $item->innerHtml();
                    $sourceContent = Utility::Instance()->trimContent(strip_tags($innerContent));
                    if (!empty($sourceContent)) {
                        if ($tag == 'p') {
                            $align = Utility::Instance()->getCssValueFromItem($item, 'text\-align');
                            $id = Utility::Instance()->getId($item);
                            $pAml = Builder::Instance()->buildTextNode($sourceContent, '', $align, $id);
                            Utility::Instance()->parseMarkUp($item, $pAml);
//                            Utility::Instance()->parseSentence($item, $pAml);
                            $ret[] = $pAml;
                        } elseif ($tag == 'ul') {
                            $ulChildren = Utility::Instance()->getChildrenByTag($item, 'li');
                            $order = 0;
                            /**
                             * @var Dom\HtmlNode $child
                             */
                            foreach ($ulChildren as $child) {
                                $id = Utility::Instance()->getId($child);
                                $childrenItem = Utility::Instance()->trimContent(strip_tags($child->innerHtml()));
                                $pAml = Builder::Instance()->buildLiNode($childrenItem, $id, ++$order);
                                Utility::Instance()->parseMarkUp($child, $pAml);
                                $ret[] = $pAml;
                            }
                        } elseif (in_array($tag, self::$hTagNameArray)) {
                            $align = Utility::Instance()->getCssValueFromItem($item, 'text\-align');
                            $id = Utility::Instance()->getId($item);
                            $pAml = Builder::Instance()->buildTextNode($sourceContent, $tag, $align, $id);
                            Utility::Instance()->parseMarkUp($item, $pAml);
                            $ret[] = $pAml;
                        }
                    } elseif ($tag == 'img') {
                        $id = Utility::Instance()->getId($item);
                        $pAml = Builder::Instance()->buildImgNode($item, $id);
                        $ret[] = $pAml;
                    } elseif ($tag == 'video') {
                        $id = Utility::Instance()->getId($item);
                        $pAml = Builder::Instance()->buildVideoNode($item, $id);
                        $ret[] = $pAml;
                    } elseif (strpos($innerContent, "</a>") !== false) { // <p><a><img /></a></p> 不能在下面解析嵌套的img标签的判断之后，会出错
                        $aChildren = Utility::Instance()->getChildrenByTag($item, 'a');
                        foreach ($aChildren as $aChild) {
                            $imgChildren = Utility::Instance()->getChildrenByTag($aChild, 'img');
                            if ($imgChildren) {
                                foreach ($imgChildren as $imgChild) {
                                    $id = Utility::Instance()->getId($aChild);
                                    $pAml = Builder::Instance()->buildImgNode($imgChild, $id);
                                    Utility::Instance()->parseMarkUp($item, $pAml, '', 'image');
                                    $ret[] = $pAml;
                                }
                            }
                        }
                    } elseif (strpos($innerContent, '<img') !== false) { // <p><img /></p>
                        $imgChildren = Utility::Instance()->getChildrenByTag($item, 'img');
                        if ($imgChildren) {
                            foreach ($imgChildren as $imgChild) {
                                $id = Utility::Instance()->getId($item);
                                $pAml = Builder::Instance()->buildImgNode($imgChild, $id);
                                $ret[] = $pAml;
                            }
                        }
                    }
                }
            }
        }

        return $ret;
//        return json_encode($ret, 256);
    }


}

$h2r = new HtmlToRaml();
//$demo->html2Raml();
print_r($h2r->html2Raml());


