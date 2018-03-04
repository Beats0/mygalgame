=== Gravatar China ===
Contributors: LOO2K
Donate link: http://loo2k.com/donate/
Tags: comments, Gravatar, avatar, local cache
Requires at least: 2.0.2
Tested up to: 1.1
Stable tag: 1.0

Here is a short description of the plugin.  This should be no more than 150 characters.  No markup here.

== Description ==
这是一个关于 Gravatar Cache 的重要更新，之前（2010.10.15） Gravatar 由于一些众所未知的原因不能访问，所以当初制作了一个 Gravatar 头像的本地缓存插件，但是由于当时编写的比较匆忙，遗留下了一些问题，包括但不限于：无法使用默认图片、无法缓存不同大小的头像等；

最近一段时间（2011.08.02），Gravatar 再次无法访问，所以重新写了一个插件 Gravatar China for WordPress，并解决的之前存在的所有已知的问题；

Gravatar China for WordPress 特性

	* Gravatar 头像防墙补丁：替换 Gravatar 头像能正常访问的地址；
	* Gravatar 本地缓存：对特殊的网络环境下给头像进行本地缓存；
	* 自定义设置缓存过期时间；
	* 国内、国外主机用户通用；
	* 完美兼容 WordPress；

Gravatar China for WordPress 说明

* 本插件针对中国大陆的网络环境制作；

* 一般情况下，你可以在 Gravatar 头像不能正常访问的时候启用本插件的 “Gravatar 补丁”，它能帮助你的 WordPress 访客连接到正常的头像地址上；

* 通常，根据网页前端的性能优化来说，不推荐用户启用 “Gravatar 本地缓存”，因为它对 WordPress 的性能有一定的影响，当然这个影响仅限于生成本地缓存的时候；（启用缓存前请确认你的 WordPress 目录 wp-content/plugins/gravatar-cn/cache 可写）
	
== Installation ==

1. 上传插件的压缩文件到 `/wp-content/plugins/` 目录；
2. 在 WordPress 后台启用 Gravatar China 插件；
3. 在设置里选择是否启用设置；

== Screenshots ==

1. This screen shot description corresponds to screenshot-1.(png|jpg|jpeg|gif). Note that the screenshot is taken from
the directory of the stable readme.txt, so in this case, `/tags/4.3/screenshot-1.png` (or jpg, jpeg, gif)

== Changelog ==

= 1.0 =
* 新建插件.