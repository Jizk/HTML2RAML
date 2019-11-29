<?php

use src\Build\BuildText\BuildText;
use src\Build\BuildImg\BuildImg;
use src\Build\BuildVideo\BuildVideo;
use Utility\Utility\Utility;

class RamlToHtml
{
    public function raml2Html()
    {
        $content = file_get_contents(__DIR__ . '/Test/ffchild.json');
        $content = json_decode($content);
        $html = $this->parserRaml($content);
        return $html;
    }

    private function parserRaml($raml)
    {
        $result = '<header><meta http-equiv="Content-Type" content="text/html;charset=utf-8"></header>';
        foreach ($raml as $rItem){
            $oneSegment = "";
            // 按type解析
            if ($rItem->type == 0){

            }elseif ($rItem->type == 1){
                $oneSegment = BuildImg::Instance()->buildImgHtml($rItem);
            }elseif ($rItem->type == 2){
                $oneSegment = BuildVideo::Instance()->buildVideoHtml($rItem);
            }
        }
    }
}