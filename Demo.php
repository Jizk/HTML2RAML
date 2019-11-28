<?php

require 'vendor/autoload.php';
require './Utility/Utility.php';
require './Utility/Builder.php';

use PHPHtmlParser\Dom;

class Demo
{
    public function html2Raml()
    {
        $content = file_get_contents(__DIR__ . '/Test/demo2.html');
//        $content = $this->cleanHtml($content);
//        $content = $this->getHtml($content);
        $content = "<body>" . $content . "</body>";
        $html = Utility::Instance()->html2Str($content);

        $dom = new Dom;
        $dom->loadStr($html, []);

        if (empty($dom->root->firstChild()->getChildren()[1])){
            $root = $dom->root->firstChild()->getChildren()[0];
        }else{
            $root = $dom->root->firstChild()->getChildren()[1];
        }

        return $this->parserHtml($root);
    }

    private static $hTagNameArray = ['h1', 'h2', 'h3'];

    /**
     * @param  Dom\HtmlNode $item
     * @return array
     * @throws \PHPHtmlParser\Exceptions\UnknownChildTypeException
     */
    private function parserHtml($item)
    {
        $ret = [];
        /**
         * @var Dom\HtmlNode $item
         */

        $children = $item->getChildren();

        foreach ($children as $item){
            $outerHtml = Utility::Instance()->trimContent($item->outerHtml());
            if (empty($outerHtml)){
                continue;
            }
            $tag = strtolower($item->getTag()->name());
            if (!empty($outerHtml)){
                if ($tag == 'p'
                    || $tag == 'image'
                    || $tag == 'ul'
                    || in_array($tag, self::$hTagNameArray)){
                    $innerContent = $item->innerHtml();
                    $sourceContent = Utility::Instance()->trimContent(strip_tags($innerContent));
                    if (!empty($sourceContent)){
                        if ($tag == 'p'){
                            $align = Utility::Instance()->getCssValueFromItem($item, 'text\-align');
                            $id = Utility::Instance()->getId($item);
                            $pAml = Builder::Instance()->buildTextNode($sourceContent, '', $align, $id);
                            Utility::Instance()->parseMarkUp($item, $pAml);
                            Utility::Instance()->parseSentence($item, $pAml);
                            $ret[] = $pAml;
                        }elseif ($tag == 'ul') {
                            $ret[] = [];
                        }elseif (in_array($tag, self::$hTagNameArray)){
                            $align = Utility::Instance()->getCssValueFromItem($item, 'text\-align');
                            $id = Utility::Instance()->getId($item);
                            $pAml = Builder::Instance()->buildTextNode($sourceContent, $tag, $align, $id);
                            Utility::Instance()->parseMarkUp($item, $pAml);
                            $ret[] = $pAml;
                        }
                    }elseif ($tag == 'image'){
                        $id = Utility::Instance()->getId($item);
                        $pAml = Builder::Instance()->buildImgNode($item, $id);
                        $ret[] = $pAml;
                    }elseif (strpos($item->innerHtml(), 'image') !== false){ // <p><img></img></p>
                        $ret[] = [];

                    }elseif ($tag == 'video'){
                        $ret[] = [];

                    }
                }
            }
        }

        return $ret;
//        return json_encode($ret, 256);
    }



}

$demo = new Demo();
//$demo->html2Raml();
print_r($demo->html2Raml());


