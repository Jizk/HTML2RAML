# HTML2RAML
---
总结先写在前面，因为网上没有开源的RAML解析器，所以自己尝试先仿照轻芒RAML的格式写了一个粗糙的。目前自己练习的只有转文本标签跟图片标签，后续会不定时更新。自定义标签其实具有高灵活性，只要约定好固定的解析格式，可以很方便的扩展解析各种标签。
## RAML语法介绍
---
RAML是一种用来描述文章的标记语言，基于JSON形式，渲染文章页面。

```
  [
    {
        "id":xxx,
        "type":0,
        "text":"一段文字描述"，
        "markups":[
            {
                "tag":"strong",
                "start":0,
                "end":2
            }
        ]
    },
    {
        "id":xxx,
        "type":1,
        "image":{
            "width":xxxx,
            "height":xxxx,
            "source":xxxxx,
        }
    }
  ]
```
RAML会将文章转成一个JSON List的字符串。其中List的每一个元素代表文章的一个段落，每个段落有正文内容text，相关标记markups。上述JSON描述包含了文本和图片两种格式，具体展示形式是
**一段**文字描述

## RAML标记介绍 
---
RAML每个List元素，包含一系列的Key，这些Key主要分为段落内容标记，段落样式标记，文本标记。仿照**轻芒RAML**解析格式。

### 段落内容标记
#### id 
每个段落都有一个id。
#### type
每个段落都有一个类型，不同的type主要内容字段也会有所不同，例如上面text跟image两种类型。
#### text
包含纯文本text内容跟markups辅助标记信息。
#### image
包含source图片url、width图片宽度、height图片高度。
### 段落样式标记
#### linetype
自己训练的只有<h*>标题,还可以自己定义一些放大缩小的样式。比如big，small
### 文本标记
用来辅助当前List元素的标记。
#### start+end
用来标记markups的作用范围。取值范围是[0,段落长度）。
#### tag 
用来表示markups的类型，比如strong加粗，span块，img图片。后期可以随时扩展

这里只简单介绍了自己目前当前写到的RAML解析器，详细的RAML可以参照[轻芒](https://github.com/qingmang-team/docs/blob/master/raml/intro.md)。轻芒涵盖了大多数可以用到的标签。主要要了解HTML解析到DOM的规则，约定好自己的JSON格式，可以很轻松的实现HTML to RAML、RAML to HTML。后期逐步实现，加油！

# 更新记录
- 11.27.19 HTML2RAML单项解析，简单支持文本标签、加粗样式、块标签样式、按照句号切割每个段落的句子。
