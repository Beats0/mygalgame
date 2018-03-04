=== Nelio External Featured Image (discontinued) - Available in Nelio Content ===
Contributors: nelio, davilera
Donate link: http://bit.ly/nelio-efi-donate
Tags: external, url, featured image, featured, featured images, image
Requires at least: 3.3
Tested up to: 4.3
Stable tag: 1.4.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Download Nelio Content (a new implementation of this plugin) and use external
images from anywhere as the featured image of your pages and posts.

== Description ==

> **Important Notice**
>
> This plugin has been re-implemented from scratch and merged into [Nelio
> Content](https://wordpress.org/plugins/nelio-content). If you're interested
> in using external featured images in your blog and receiving updates and
> support, please install Nelio Content.
>
> **Upgrade**
>
> If you're already a user of Nelio External Featured Images, simply replace it
> with Nelio Content; it implements a better external featured image engine and
> it's completely compatible with Nelio External Featured Image. Please keep in
> mind that Nelio External Featured Images is now discontinued and will receive
> no more updates; new features and support are available in Nelio Content only.

Are you using an external service for storing your images? Then you'd probably
like to use those images as featured images for your pages and posts. This
plugin lets you do this easily!

And even better! If you don't specify any featured image, the plugin will use
the first image available in the post.


= Related Plugins by Nelio =

* [Nelio Content](https://neliosoftware.com/content/?fp=wordpress.org) |
[Download](https://wordpress.org/plugins/nelio-content/)
* [Nelio A/B Testing](https://nelioabtesting.com/?fp=wordpress.org) |
[Download](https://wordpress.org/plugins/nelio-ab-testing/)
* Nelio Featured Posts |
[Download](https://wordpress.org/plugins/nelio-featured-posts/)
* Nelio Related Posts |
[Download](https://wordpress.org/plugins/nelio-related-posts/)


_Featured image by
[Cubmundo](https://www.flickr.com/photos/cubmundo/6748759375)_



== Frequently Asked Questions ==

= What's the difference between Nelio Content and Nelio External Featured Images? =

Nelio Content is a plugin aimed at helping blog owners run their blog. It's
goal is to assist your work with the blog and, therefore, it features an
editorial calendar, support for sharing content on social media, and so on.

Nelio External Featured Images was designed with the solely purpose of setting
external featured images in your posts. Whilst this plugin served its purpose
quite well, there were several scenarios in which it simply didn't work.

We believe external featured images are very helpful to all users. Therefore,
we decided to integrate this plugin's functionality into Nelio Content. However,
we didn't simply "duplicate" the code from one plugin to the other; we
reimplemented the functionality from scratch and made it compatible with more
themes.

= I'm already using Nelio External Featured Images. Do I need to install Nelio Content? =

No, you don't need to install Nelio Content to keep using the plugin. However,
Nelio External Featured Images is now discontinued, and we strongly recommend
you to replace it with Nelio Content. We've implemented several improvements
in Nelio Content and made it compatible with multiple themes, including
Newspaper, Newsmag, and Enfold.

= Will you support this plugin? =

As already stated in the plugin's description, Nelio External Featured Image
is now discontinued. However, **all its functionality is available in Nelio
Content**, so you can use Nelio Content to get our support and updates.

= How does the plugin work? =

Every time an image is uploaded in the media library, WordPress automatically
creates alternative versions of that image, each with a different size
(thumbnail, medium, large and full).  Themes may then choose among these
different versions when displaying the an image in a post.

Thumbnails do also exist for featured images, and themes may registerd their
own alternative image sizes. For example, WordPress' default theme
TwentyFourteen defines a thumbnail size called "twentyfourteen-full-width"
whose dimensions are 1038x576.

This plugin uses the alternative-size information (which your theme uses for
rendering a featured image) for scaling and cropping external featured images
via CSS on your users' browsers.

There are some situations, however, in which this "size" information is not
provided. In these scenarios, our plugin relies on theme's CSS file. If the
CSS rules define the width and height of the featured image, everything should
be fine. Otherwise, you may have to tweak something.


= I don't see external featured images. What do I do? =

Try adding the following CSS code in your theme:

`
img.nelioefi {
  min-width:100px;
  min-height:100px;
}
`

and refresh your site. If you can see the external featured image, it means
that our plugin works properly, but some CSS tweaking was needed. Now, simply
edit the CSS styles of your theme so that the featured image looks good
everywhere (`width`, `height`, `max-width`, `max-height`).


= I added the CSS code you mention, but images do not appear yet. =

WordPress offers more than one function for inserting featured images.
Unfortunately, only one of them has a filter we can use. If your theme is not
using the `(get_)the_post_thumbnail`, then our plugin will not be able to
insert external featured images.

In these cases, the only solution is to tweak your theme and edit some PHP
code. Unfortunately, there's no generic solution for doing it. Just locate
where featured images are being inserted, and edit those lines so that it uses
the previous function (if possible). Please note that the plugin also offers
some helper functions that will ease the "tuning" of the theme (check the file
includes/nelio-efi-main.php to know them and review their documentation).


= How does the plugin find the first image linked in a post? =

If you don't specify any featured image, the plugin will use the first image
included in your post. In order to find the image, it looks for `img` tags that
looks like the following:

`
&lt;img src="image-url-here" ... /&gt;
`

The `img` tag and the `src` attribute have to be "together". Otherwise, our
plugin won't be able to find the featured image.


= I don't want to use the first image in a post as the featured image. How do I change this? =

That's quite easy. Simply add the following line in your theme's `functions.php` file:

`
add_filter( 'nelioefi_use_first_image', '__return_false' );
`


== Screenshots ==

1. **External Featured Image with URL.** Easily set the featured image of a
post by using the image's URL only!


== Changelog ==

= 1.4.5 (November 23, 2016) =
* **Notice Added**. Adding a notice to the plugin to let our users know that this
plugin is now discontinued and they should install Nelio Content instead. The
notice can be disabled using a new filter named
`nelioefi_recommend_nelio_content` and returning `false`.

= 1.4.4 (April 21, 2016) =
* **Bug Fix**. If there isn't any external featured image set and the post doesn't
contain any images, do not return an empty featured image.
* **Buf Fix**. Auto-select first image in post, regardless of the quotation type
used by the `img` (i.e. it works with both single and double quotes).
* **Improvement**. Using the filter `the_content` for trying to obtain the
first featured image, when (apparently) no image was included in the post.
This may occur, for example, when a plugin for converting links to images is
used.
* **Notice Fix**. Removed notice (thanks to [Lars
Schenk](https://wordpress.org/support/topic/php-notice-after-update-to-143?replies=1)).

= 1.4.3 (March 22, 2016) =
* **Improvement**. The first image of your posts are now saved in a custom
meta. This way, selecting the first image as the featured image is much
faster and the workload is reduced. This meta's name is
`nelioefi_use_first_image`.
* **Improvement**. The previous meta is only used in the front end. This
also ensures that the Dashboard is not affected by the plugin.


= 1.4.2 (March 8, 2016) =
* **Fix**. Using first image as the featured image automatically now works
under (hopefully) all circumstances. The previous version only worked if
the `img` tag and its `src` attribute were side by side. This is no longer
requried.


= 1.4.1 (January 28, 2016) =
* **Fix**. Using first image as the featured image is now working properly.


= 1.4.0 (January 27, 2016) =
* **New Funcionality!** By default, the plugin will use the first image that
appears in the content of your posts if you didn't specify any external
featured image. Note there's a new filter to change this behavior
(`nelioefi_use_first_image`); you can read more about it in the FAQ section.


= 1.2.0 =
* Bug fix: Quick Edit (and possibly other edits) a post removed the external
featured image. This does no longer happen.
* External Featured Image is inserted using the `src` attribute of an `img` tag
in RSS feeds (instead of an inline CSS `background` property).


= 1.1.0 =
* Define the ALT attribute of your external featured images.


= 1.0.9 =
* Added a new filter ("nelioefi_post_meta_key") that will let you define a
custom post_meta_key to store the URL of the external featured image.


= 1.0.8 =
* Referencing our A/B testing service for WordPress.


= 1.0.7 =
* Added External Featured Image box for custom post types. If the custom
post type's template uses featured image.
* Some helper functions have been introduced in the plugin, so that adapting
themes becomes easier.


= 1.0.6 =
* Compatibility with the Genesis Framework. By default, external featured
images have a minimum size of 150x150px. Make sure you edit your CSS files
for proper image size.
* Some minor tweaks.


= 1.0.5 =
* Bug fix. One function for locating external featured images was missplaced.
I moved it to the proper file so that it loads when the user is not an admin.


= 1.0.4 =
* Bug fix. You can now set regular featured images under all circumstances
(thanks _rprose_ for reporting the bug!).


= 1.0.3 =
* Improved image sizing. Now, the plugin uses the sizes the theme defines and
tries to scale and crop the external image for its proper display.


= 1.0.0 =
* First release.


== Upgrade Notice ==

= 1.4.5 =
Notice added.

