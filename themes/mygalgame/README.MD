# mygalgame
### 基于ZanBlog对Mygalgame的重构计划
声明:

1. 此rep由[ZanBlog V2](https://github.com/yeahzan/zanblog)更改而来,为个人更改,与galgame网站无任何关系

2. 目前功能虽然已大部分完成,但还有待进一步测试与优化中...

3. 使用主题时请根据自己实际情况自行更改

使用
* [安装](#安装)
* [小工具](#小工具)
* [侧边栏与菜单](#侧边栏与菜单)
* [文章](#文章)
* [Q&A](#qa)
* [相关](#相关)
* [引用](#引用)
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

#### 配置`Infinite Scroll`(建议不更改)
Infinite Scroll是一个异步加载插件,安装好之后选择 `后台` —— `设置` —— `Infinite Scroll` ,在输入框中输入以下数据。

（请对应前5个输入框存放,而并不是放在同一个输入框中）

注：请在编辑器选择为文本状态下填写,而不是可视化的状态下。

Input:
```
1. Content Selector: #article-list
2. Navigation Selector: #load-more
3. Next Selector: #load-more
4. Item Selector: .article
```
Callback: 
```js
jQuery("#load-more").removeClass("disabled");
jQuery("#load-more i").removeClass("fa fa-spinner");
jQuery("#load-more attr").text("加载更多");
```
Finished Message
```html
<p class="alert alert-danger"><i class="fa fa-warning"></i> 内容加载完毕</p>
```
最后把 `Behavior` 改为 `Manual Trigger` (才会实现加载功能,默认就是翻页的）,保存完成。

注：同时需要在 `后台` － `主题选项` 中,把文章分页形式改为 `1`

更多参考：http://www.yeahzan.com/zanblog/archives/457.html

#### 小工具
`外观` —— `小工具`

要激活某一小工具,将它拖动到侧栏或点击它。要禁用某一小工具并删除其设置,将它拖回来。

------------------------------------------------------------------

#### 侧边栏与菜单
##### 侧边栏设置说明
选择 `外观` —— `小工具`

要激活某一小工具,将它拖动到侧栏或点击它。要禁用某一小工具并删除其设置,将它拖回来。

页面右侧的位置也是我们已经设定好的,你只需要将左侧小工具拖动到右侧对应位置。

例如以下图片显示,我们在首页侧边栏加入了前台登录组件、搜索框组件、最新评论组件、最热文章组件、集合组件。

你还可以通过拖动来对这些组件的显示顺序进行排序。

![小工具](https://wx1.sinaimg.cn/large/006nOlwNgy1fp12zwr3gbj31aq0o2q51.jpg)

##### 导航栏设置说明
1.新建菜单（网站后台—>外观—>菜单）

可以自定义链接,结构不对齐就是子项目的意思

![菜单](https://wx1.sinaimg.cn/large/006nOlwNgy1fp12zwo8tuj30rl0nvwfj.jpg)

2.使用 `FontAwesome` 图标
在导航标签前添加类似`<i class="fa fa-home"></i>`的代码,具体图标Class参照 [FontAwesome](http://fontawesome.dashgame.com/)

3.新建页面

例如messageboard(留言板什么的),然后再添加到菜单中即可
注意创建留言板时选择留 `言板模板` ,注意在快速编辑中勾选 `许评论` 权限

4.首页公告

不要选择公告工具,要在 `主题选项` 中选择 `首页公告`即可

------------------------------------------------------------------

#### 文章

几个写文章时常用的标签

```html
<!--文字框-->
<div class="alert alert-info"><span style="font-size: 10pt;">alert alert-info</span></div>

<div class="alert alert-danger">alert alert-danger</div>

<div class="alert alert-success">alert alert-success</div>

<!--图片效果-->
<div class="dAnim"><a href="https://wx1.sinaimg.cn/large/006nOlwNgy1fp0v3noihrj337b29eu0x.jpg" class="highslide-image " onclick="return hs.expand(this);"><img title="点击放大" src="https://wx1.sinaimg.cn/large/006nOlwNgy1fp0v3noihrj337b29eu0x.jpg" alt="" width="650"></a></div>
```
------------------------------------------------------------------

#### Q&A

Zanblog常见问题汇总 http://www.yeahzan.com/zanblog/archives/394.html

>1.安装以后报错
```
Fatal error: Call to undefined function the_views() in /data/htdocs/lioil0.host.smartgslb.com/wp-content/themes/zanblog/index.php on line 30
```

解答：这是由于没有安装Wp-PostViews插件,只需在后台搜索并安装该插件即可。

>2.为什么我安装后,右侧边上面的 最热文章 最新评论 最新文章 没有显示呢？为什么安装完主题后,侧边栏布局有问题？

解答：可以查看文章《Zanblog侧边栏与菜单设置说明文档》了解。

>3.导航栏设置fixed属性后adminbar会挡住导航栏,这个问题有解决的办法吗？

解答：ZanblogV2.0.2版本解决了这个问题,（为导航栏增添了checkbox框,可以选择导航栏是否fixed到浏览器顶端。）如果你使用的是之前的版本,可以下载最新版。

>4.在我的机器上执行index.php这行获取摘要的时候报错了
```php
<?php echo mb_strimwidth(strip_tags(apply_filters(‘the_content’, $post->post_content)), 0, 250,”…”); ?>
```
解答：服务器配置文件php.ini里面开启字符串分割。

>5.为什么主题包中有“__MACOSX”这个文件夹以及“LICENSE.txt” 、“README.md”这些文件？

解答：这个文件夹是Mac系统下压缩产生的额外文件,直接删除即可,不会有任何影响。

>6.怎么修改网站Logo？

解答：可以直接替换掉主题文件夹下的images中的logo_hover.png（Hover效果时的图片）和logo.png这两张图片。


>7.二级菜单那里,我的后台并没有CSS类可选那项,怎么办？

解答：后台－>外观－>菜单 ,右侧头部点击 “显示选项”就可以看到了。

>8.为什么不支持IE8以下的浏览器？

解答：因为我们采用的是Bootstrap3框架,而该框架自身就是针对于>=IE8进行设计的。

>9.怎么让多说的评论同步到最新评论里？它只显示原生评论里的内容 。

解答：选择后台=>多说评论=>高级选项=>数据同步就可以。

>10.IE6.7.8下错位严重?

解答：这是因为我们使用的bootstrap3.0的ui框架,本身不支持ie8以下版本,更多详情可以了解bootstrap3.0。

>11.Zanblog下如何添加友情链接？

解答：zanblog2.0.4以及之前的版本,要启用友情链接,首先需要安装Link Manager插件。装好后进入后台->链接->添加链接,新建一个你所需要的链接名称以及URL,然后在分类目录中勾选“友情链接”,保存即可。

zanblog2.0.5及之后版本集成了一个功能,后台本身就有链接这一区域,新建一个你所需要的链接名称以及URL,然后新建一个分类目录（目录名例如：友情链接）并勾选,保存即可。

>12.如何将按钮自动加载改为分页？

解答：如果你不喜欢按钮加载文章的形式,而想要原始的分页功能,Zanblog也能够非常轻松地实现,只要把index.php第87行的auto改为manual即可,无需安装额外的分页插件。

>13.为什么我的侧边栏是在底部呢？

解答：我们采用的是响应式布局,如果你电脑屏幕宽度在992px以下的话,为了有不够的空间来查看文章,侧边栏选择放到文章的底部。
如果是在992px以上,那可能是你哪里设置出了问题。

>14.网站字体变大后,最新文章、最新评论、最热文章会溢出,怎么解决？

解答：这是因为我们通过代码来选择截取多少字数来显示,当字体变大,但显示字数不变的时候就会溢出,解决办法：
修改sidebar.php中

第二个参数代表显示的字数
```php
<?php echo zanblog_get_most_comments(8, 46, 90); ?>
```

第三个参数代表显示的字数
```php
<?php echo zanbog_latest_comments_list(6, 40, 20);?>
```

其中的数字：20代表显示的字数
```php
<?php echo zanblog_cut_string(strip_tags($post—>post_title), 20); ?>
```

当然最好希望大家可以查看 `functions.php` 中的方法来了解本质。

>15.文章特色图像的尺寸是多少？文章中如何添加特色图像？

解答：不再使用特色图片,而是使用有外链的`External Featured Image`插件,推荐尺寸为750*300px,小于这个尺寸图像会居中显示。在后台写文章的页面右下角有一个`External Featured Image`,注意从而在首页每篇文章里显示出来。

>16.点击文章存档出現404 error的頁面

解答:路径不对,请仔细看一下sidebar.php文件,里面关于文章存档的链接是写死的。
![](https://wx1.sinaimg.cn/large/006nOlwNgy1fp13ue75kpj30fe031aag.jpg)

需要新建页面,同时选择模版为文章存档。

![](https://wx1.sinaimg.cn/large/006nOlwNgy1fp13ue76haj30fo026wel.jpg)

![](https://wx1.sinaimg.cn/large/006nOlwNgy1fp13ue7b8pj307w04v0st.jpg)

>17.想要更换网站的颜色/配色,需要怎么做？

解答: 配色都是通过css来实现的,所以你需要修改css文件,包括 `bootstrapUI.css` 和 `myblog.css`.

>18.如何修改默认的评论头像

因为wordpress默认评论头像使用的是Gravatar标志,
所以当用户没有设置过Gravatar头像时,会调用默认的头像,但是默认头像有点丑,所以想要修改的话,可以这样操作：

在后台－设置－讨论的最下面修改默认头像,zanblog官网使用的是小怪物。

>19.如何不用插件,直接使用音乐文件

在编辑器中（文本状态）添加如下代码：
```html
<audio src="http://www.yeahzan.com/zanblog/xx.mp3" controls="controls" name="media"/></audio>
```

注：src路径修改为正确的mp3文件路径。

>20.安装主题时,显示主题缺少style.css样式表

我们的编程环境时mac系统,在压缩的时候会产生__MACOSX等文件,所以你不能下载下来直接上传,WP会找不到主题的,要先解压,然后单独把主题文件打包上传。

>21.如何让最新评论不显示自己的？

请查看 `functions.php` 文件中的关于文章评论的这一块代码,然后将`comment_author != '佚站互联'`中的佚站互联改成你自己的wordpress后台账号名称。

>22.为何友情链接只在首页显示？

友情链接只在首页显示并不是BUG,而是出于SEO的目的,避免网站权重过多地被友情链接中的网站分去。感兴趣的朋友可以去了解一下SEO。

>23.Zanblog是否支持IE8？

ZanblogV2.0.7版本,专门针对IE8进行了兼容,所以可以很明确的告诉大家：对于IE8,Zanblog是支持。至于IE8以下的版本,因为Zanblog用的是Bootstrap3前端框架,它仅支持IE8及以上版本,所以Zanblog也不考虑IE8以下版本。

但是有一些赞友反映说自己安装的是IE8,但不兼容是什么原因？这通常是因为你的浏览器虽然是IE8,但是启用了IE8兼容模式,而所谓的兼容模式实际上是IE7,大家可以通过点击浏览器中的工具－开发人员工具,查看工具栏最右边是否是浏览器模式：IE8,文本模式：IE8标准（正确）,而如果是浏览器模式：IE8兼容模式,文本模式：IE7标准（错误）,则实际上用的是IE7,对于IE7,Zanblog是不兼容的。

>24.如何修改每页展示文章数量和评论数量


文章： `后台`－>`设置`－>`阅读`

评论： `后台`－>`设置`－>`讨论`

建议把设置都更改一下

建议把自己的WordPress类库文件升级,例如jQuery等js类库,这样是为了提高网页界面加载速度,将最新类库的代码覆盖到`wordpress\wp-includes\js\` 或 `wordpress\wp-content\themes\mygalgame\ui\js\`对应对的文件即可,如需添加js文件,在`wordpress\wp-content\themes\mygalgame\ui\js\`添加js文件,并在`wordpress\wp-content\themes\mygalgame\includes\theme-functions.php`注册调用

------------------------------------------------------------------

#### 相关
[ZanBlog官网](http://www.yeahzan.com/zanblog/)

[GitHub zanblog](https://github.com/yeahzan/zanblog)

[GitHub hexo-theme-gal](https://github.com/ZEROKISEKI/hexo-theme-gal)

------------------------------------------------------------------

#### 引用

- Bootstrap(css,js):[GitHub:Bootstrap](https://github.com/twbs/bootstrap)
- AOS.js(css,js):[GitHub:AOS.js](https://github.com/michalsnik/aos)
- font-awesome：(css)[GitHub:font-awesome](https://github.com/FortAwesome/Font-Awesome)
- WORDPRESS：[wordpress.org](https://wordpress.org/themes/)
- WORDPRESS SVN and PHP：(PHP)[GitHub:WordPress](https://github.com/WordPress/WordPress)
- WP Ajax Edit Comments:[https://wordpress.org/plugins/wp-ajax-edit-comments/](https://wordpress.org/plugins/wp-ajax-edit-comments/)
- highslide.js(js):[http://highslide.com](http://highslide.com) and [https://www.highcharts.com/demo](https://www.highcharts.com/demo)
- jquery:[GitHub:jquery](https://github.com/jquery/jquery)
- jquery.lazyload:[GitHub:jquery.lazyload](https://github.com/tuupola/jquery_lazyload)
- jQuery-Colorbox:[GitHub:jQuery-Colorbox](https://github.com/afranken/jQuery-Colorbox)
- jquery-migrate:[GitHub:jquery-migrate](https://github.com/jquery/jquery-migrate)
- jquery-cookie:[GitHub:jquery-cookie](https://github.com/carhartl/jquery-cookie)
- jQuery Validate:[GitHub:jQuery Validate](https://github.com/DiegoLopesLima/validate)

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