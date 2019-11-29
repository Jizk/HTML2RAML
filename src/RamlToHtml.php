<?php

require __DIR__ . '/../Utility/Utility.php';
require __DIR__ . '/Build/BuildMarkups.php';
require __DIR__ . '/Build/BuildText.php';
require __DIR__ . '/Build/BuildImg.php';
require __DIR__ . '/Build/BuildVideo.php';

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