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
                    || $tag == 'img'
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
                            $ret[] = [];
                        }
                    }elseif ($tag == 'img'){
                        $ret[] = [];

                    }elseif (strpos($item->innerHtml(), 'img') !== false){ // <p><img></img></p>
                        $ret[] = [];

                    }elseif ($tag == 'audio'){
                        $ret[] = [];

                    }
                }
            }
        }

        return json_encode($ret, 256);
    }



}

//$html = '<body><div><div><h2>hello world</h2></div></div></body>';
//$html = str_get_html($html);
//$a=0;
//foreach ($html->find('*') as $item){
//    if (empty($item->dataId)){
//        $item->predataId = $a++;
//    }
//}
//var_dump(strval($html));
//
//$dom = new Dom;
////var_dump($dom);
//$dom->loadStr($html, []);
//var_dump($dom->root->firstChild());


//$e = $html->find("*", 0);
//print_r($e->children(1));
//print_r($e->plaintext);
//print_r($e->outHtml);
$demo = new Demo();
//$demo->html2Raml();
print_r($demo->html2Raml());


