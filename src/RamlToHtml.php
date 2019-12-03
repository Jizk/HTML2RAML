<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../Utility/Utility.php';
require __DIR__ . '/Build/BuildMarkups.php';
require __DIR__ . '/Build/BuildText.php';
require __DIR__ . '/Build/BuildImg.php';
require __DIR__ . '/Build/BuildVideo.php';

use HTML2RAML\Build\BuildImg;
use HTML2RAML\Build\BuildVideo;
use HTML2RAML\Build\BuildMarkups;
use HTML2RAML\Build\BuildText;
use Utility\Utility;

class RamlToHtml
{
    public function raml2Html()
    {
        $content = file_get_contents(__DIR__ . '/../Test/ffchild.json');
        $content = json_decode($content);
        $html = $this->parserRaml($content);
        print_r($html);
    }

    private function parserRaml($raml)
    {
        $result = '<header><meta http-equiv="Content-Type" content="text/html;charset=utf-8"></header>' . "\n";
        foreach ($raml as $rItem) {
            $oneSegment = "";
            // 按type解析
            if ($rItem->type == 0) {
                if (!empty($rItem->li)) {
                    $oneSegment = BuildText::Instance()->buildLiHtml($rItem);
                } else {
                    $textRet = $rItem->text->text;
                    if (!empty($rItem->text->markups)) {
                        $textRet = Utility::Instance()->reParseMarkup($rItem, $textRet);
                    }
                    if (!empty($rItem->text->linetype)) {
                        $textRet = BuildMarkups::Instance()->buildTag($textRet, $rItem->text->linetype, '');
                    }
                    $attrs = '';
                    if (!empty($rItem->text->align)) {
                        $attrs .= " style='text-align:{$rItem->text->align}'";
                    }
                    if (!empty($rItem->id)) {
                        $attrs .= " dataid={$rItem->id}";
                    }
                    $oneSegment = BuildMarkups::Instance()->buildTag($textRet, 'p', $attrs);
                }
            } elseif ($rItem->type == 1) {
                if (!empty($rItem->image)) {
                    $oneSegment = BuildImg::Instance()->buildImgHtml($rItem);
                }
                // 超链接
                if (!empty($rItem->image->markups)) {
                    $attrs = "href={$rItem->image->markups[0]->source}";
                    $oneSegment = BuildMarkups::Instance()->buildTag($oneSegment, 'a', $attrs);
                }
            } elseif ($rItem->type == 2) {
                $oneSegment = BuildVideo::Instance()->buildVideoHtml($rItem);
            }
            $result .= ($oneSegment . "\n");
        }
        return $result;
    }
}

$r2t = new RamlToHtml();
$r2t->raml2Html();