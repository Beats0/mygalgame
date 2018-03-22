# mygalgame
### 基于ZanBlog对Mygalgame的重构计划
声明:

1. 此rep由[ZanBlog V2](https://github.com/yeahzan/zanblog)更改而来,为个人更改,与galgame网站无任何关系

2. 目前功能虽然已大部分完成,但还有待进一步测试与优化中...

3. 使用主题时请根据自己实际情况自行更改

使用
* [安装](#安装)
* [相关](#相关)
* [使用问题](https://github.com/Beats0/mygalgame/blob/master/Usage.md)
* [License](#License)

#### 安装
下载主题,将 `plugins` 和 `themes` 放在 `wordpress\wp-content\` 里面

##### themes
登陆WordPress后台,选择 `外观` —— `主题` ,找到 `mygalgame` 主题,选择启用,至此,mygalgame主题安装成功。

##### plugins
```
plugins
├─akismet                   留言评论插件,必需,可更新
├─auto-highslide            图片插件,必需,不可更新
├─breadcrumb-navxt          面包屑导航,必需,可更新
├─custom-field-template     自定义字段,必需,可更新
├─external-featured-image   特色图片外链插件,必需,可更新
├─gravatar-china            头像防墙补丁,非必需,不可更新
├─infinite-scroll           文章异步加载,必需,不可更新
├─syntaxhighlighter         代码高亮,非必需,可更新
├─wp mail smtp              设置SMTP,非必需,可更新,此插件用于需要登录注册功能的网站,设置smtp从而用指定的邮箱来接收登录注册以及评论的邮件。
└─wp-postviews              浏览数统计,必需,可更新
```
------------------------------------------------------------------


#### 相关
[ZanBlog官网](http://www.yeahzan.com/zanblog/)

[GitHub zanblog](https://github.com/yeahzan/zanblog)

[GitHub hexo-theme-gal](https://github.com/ZEROKISEKI/hexo-theme-gal)

------------------------------------------------------------------

#### License

The MIT License (MIT)

Copyright (c) 2013 yeahzan

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.