=== Custom Field Template ===
Contributors: Hiroaki Miyashita
Donate link: http://wpgogo.com/development/custom-field-template.html
Tags: custom field, custom fields, custom, fields, field, template, meta, custom field template, custom post type
Requires at least: 2.1
Tested up to: 4.9.4
Stable tag: 2.3.8
License: GPLv2 or later

The Custom Field Template plugin extends the functionality of custom fields.

== Description ==

The Custom Field Template plugin adds the default custom fields on the Write Post/Page. The template format is almost same as the one of the rc:custom_field_gui plugin. The difference is following.

* You can set any number of the custom field templates and switch the templates when you write/edit the post/page.
* This plugin does not use the ini file for the template but set it in the option page of the plugin.
* Support for TinyMCE in the textarea.
* Support for media buttons in the textarea. - requires at least 2.5.
* Support for multiple fields with the same key.
* Support for hideKey and label options.
* You can see the full option list in the setting page of the plugin.
* You can customize the design of custom field template with css.
* You can replace custom keys by labels.
* You can use wpautop function.
* You can use PHP codes in order to set values. (experimental, `code = 0`)
* You can set an access user level in each field. (`level = 1`)
* Supprt for inserting custom field values into tags automatically. (`insertTag = true`)
* Adds [cft] Shortcode to display the custom field template. (only shows the attributes which have `output = true`)
* Adds template instruction sections.
* Adds the value label option for the case that values are diffrent from viewed values. (`valueLabel = apples # oranges # bananas`)
* Adds the blank option. (`blank = true`)
* Adds the break type. Set CSS of '#cft div'. (`type = break`)
* Adds [cft] Shortcode Format.
* Adds the sort option. (`sort = asc`, `sort = desc`, `sort = order`)
* Support for Quick Edit of custom fields. (tinyMCE and mediaButton are not supported yet)
* Support for the custom field search. (only shows the attributes which have `search = true`.)
* Adds [cftsearch] Shortcode Format. (under development)
* Adds PHP codes for the output value. (`outputCode = 0`)
* Adds PHP codes before saving the values. (`editCode = 0`)
* Adds the save functionality.
* Adds the class option. (`class = text`)
* Adds the auto hook of `the_content()`. (experimental)
* You can use the HTML Editor in the textarea. (`htmlEditor = true`)
* Adds the box title replacement option.
* Adds the select option of the post type.
* Adds the value count option.
* Adds the option to use the shortcode in the widhet.
* Adds the attributes of JavaScript Event Handlers. (`onclick = alert('ok');`)
* Adds the Initialize button.
* Adds the attributes of before and after text. (`before = blah`, `after = blah`)
* Adds the export and import functionality.
* Adds the style attribute. (`style = color:#FF0000;`)
* Adds the maxlength attribute. (`maxlength = 10`)
* Adds the attributes of multiple fields. (`multiple = true`, `startNum = 5`, `endNum = 10`, `multipleButton = true`)
* Adds the attributes of the date picker in `text` type. (`date = true`, `dateFirstDayOfWeek = 0`, `dateFormat = yyyy/mm/dd`)
* Adds the filter of page template file names (Thanks, Joel Pittet).
* Adds the attribute of `shortCode` in order to output the shortcode filtered values. (`shortCode = true`)
* Adds the attribute of `outputNone` in case there is no data to output. (`outputNone = No Data`)
* Adds the attribute of `singleList` attribute in order to output with `<ul><li>` if the value is single. ex) `singleList = true`
* Adds the file upload type. (`type = file`)
* Adds the fieldset type. (`type = fieldset_open`, `type = fieldset_close`)
* Adds the option to deploy the box in each template.

Localization

* Belorussian (by_BY) - [Marcis Gasuns](http://www.fatcow.com/)
* Catalan (ca) - [Andreu Llos](http://andreullos.com/)
* Czech (cs_CZ) - [Jakub](http://www.webees.cz/)
* German (de_DE) - F J Kaiser
* Spanish (es_ES) - [Dario Ferrer](http://www.darioferrer.com/)
* Farsi (fa_IR) - [Mehdi Zare](http://sabood.ir/)
* French (fr_FR) - Nicolas Lemoine
* Hungarian (hu_HU) - [Balazs Kovacs](http://www.netpok.hu)
* Indonesian (id_ID) - [Masino Sinaga](http://www.openscriptsolution.com/)
* Italian (it_IT) - [Gianni Diurno](http://gidibao.net/)
* Japanese (ja) - [Hiroaki Miyashita](http://wpgogo.com/)
* Dutch (nl_NL) - [Rene](http://wordpresswebshop.com/)
* Polish (pl_PL) - [Difreo](http://www.difreo.pl/)
* Brazilian Portuguese (pt_BR) - [Caciano Gabriel](http://www.gn10.com.br/)
* Russian (ru_RU) - [Sonika](http://www.sonika.ru/blog/)
* Swedish (sv_SE) - [Pontus Carlsson](http://www.fristil.se/)
* Turkish (tr_TR) - [Omer Faruk](http://ramerta.com/)
* Ukranian (uk_UA) - [Andrew Kovalev](http://www.portablecomponentsforall.com)
* Uzbek (uz_UZ) - [Alexandra Bolshova](http://www.comfi.com/)
* Chinese (zh_CN) - hurri zhu

If you have translated into your language, please let me know.

* [Japanese Custom Field Template Manual](http://ja.wpcft.com/)

Are you interested in other plugins? See the following site [CMS x WP](https://www.cmswp.jp/).

== Installation ==

1. Copy the `custom-field-template` directory into your `wp-content/plugins` directory
2. Activate the plugin through the `Plugins` menu in WordPress
3. Edit the options in `Settings` > `Custom Field Template`
4. That's it! :)

== Frequently Asked Questions ==

= How can I use this plugin? =

The template format is basically same as the one of the rc:custom_field_gui plugin.
See the default template and modify it.

= How can I display the custom fields? =

1. Use the cft shortcode. In the edit post, write down just `[cft]`. If you would like to specify the post ID, `[cft post_id=15]`. You can also set the template ID like `[cft template=1]`.
2. Do you want to insert a particular key value? Use `[cft key=Key_Name]`.
3. If you set the format of the custom fields, use `[cft format=0]`.
4. Auto Hook of `the_content()` in the option page of this plugin may help you do this. You can use [cft] shortcodes here. You can switch the cft formats in each category.

== Changelog ==

= 2.3.8 =
* Code cleaning.

= 2.3.7 =
* Bugfix: image output in the cft shortcode with the format attribute. 

= 2.3.6 =
* Code cleaning.
* Bugfix: Category ID and Page Template file name refinement.

= 2.3.5 =
* Bugfix: WordPress 4.4.

= 2.3.4 =
* Bugfix: tag save.

= 2.3.3 =
* Bugfix: tinyMCE editor.

= 2.3.2 =
* Auto hook option for the excerpt.
* Bugfix: relative path for images from the tinymce editor. 

= 2.3 =
* Post preview after publishing a post.
* Code cleaning.

= 2.2.1 =
* Bugfix: fieldset type.

= 2.2 =
* Bugfix: tinyMCE editor.

= 2.1.9 =
* Bugfix: tinyMCE editor.

= 2.1.8 =
* Bugfix: preview by multiple authors.

= 2.1.7 =
* Code cleaning.

= 2.1.6 =
* Bugfix: file type with the multipleButton attribute.
* Bugfix: save_post duplicate execution.

= 2.1.5 =
* Bugfix: values in a multiple fieldset.

= 2.1.4 =
* Bugfix: radio type in a fieldset.

= 2.1.3 =
* Code cleaning.

= 2.1.2 =
* Post preview support.
* Sort output by the input order.

= 2.1.1 =
* Farsi.
* Bugfix: key output with single quotes.
* Bugfix: media picker inside the fieldset type.
* Bugfix: empty save of PHP CODE.
* Bugfix: field label in the cftsearch shortcode.

= 2.1 =
* Bugfix: category refinement.

= 2.0.9 =
* Bugfix: output with the cft format.
* Bugfix: multibyte character key name.

= 2.0.8 =
* Code cleaning.

= 2.0.7 =
* Bugfix: media insert.

= 2.0.6 =
* Bugfix: inappropriate output with the cftsearch shortcode.

= 2.0.5 =
* Template Format for the edit screen. `[key]` will be converted to the input field. `[[key]]` is for the default key output. The `fieldset` type has not been supported yet.
* `wrap` attribute for the textarea type.
* Code cleaning.

= 2.0.4 =
* Bugfix: JavaScript error with jQuery validation.

= 2.0.3 =
* Bugfix: mediaPicker attribute with a break type.
* Bugfix: disappearance of the main editor.

= 2.0.2 =
* Bugfix: checkbox output with the label attribute.
* Bugfix: mediaButton attribute.

= 2.0.1 =
* Bugix: multibyte string key names with the html editor.

= 2.0 =
* Option to deploy the box in each template. Category ID and page template file name refinement have not been supported yet.
* Swedish (sv_SE) - Pontus Carlsson
* Bugfix: save button with the tinyMCE editor.
* Bugfix: output of custom field values.

= 1.9.9 =
* Code cleaning.
* Bugfix: image insertion using the media button.

= 1.9.8 =
* tinyMCE and quicktags in custom post types without `editor`.
* Upgrade of jQuery DatePicker plugin.
* Advertisement right column.
* Ukranian (uk_UA) - Andrew Kovalev

= 1.9.6 =
* Bugfix: error occurs in some circumstances.

= 1.9.5 =
* tinyMCE and quicktags in WordPress 3.3.
* Upgrade of jQuery Validation Plugin.
* Bugfix: multiple and fieldset options (Thanks, Colin Duwe).

= 1.9.4 =
* Support of taxonomies in the category ID field.

= 1.9.3 =
* Bugfix: `level` attribute.
* Bugfix: image insert in IE.

= 1.9.2 =
* Bugfix: js filename typo.

= 1.9.1 =
* Bugfix: `mediaPicker`, still buggy with the `multipleButton` attribute.

= 1.9 =
* Bugfix: inconsistency of `valueLabel` and `valueLabels`.

= 1.8.9 =
* Bugfix: saving multiple fields.

= 1.8.8 =
* Polish (pl_PL) - Difreo
* Bugfix: saving multiple fields.
* Bugfix: inconsistency of `valueLabel` and `valueLabels`.

= 1.8.7 =
* before and after attributes for the radio and textarea types.
* Bugfix: file type.

= 1.8.6 =
* Adds the `mediaRemove` attribute in order to prevent from deleting the file registered from the mediaPicker. ex) mediaRemove = true

= 1.8.5 =
* Bugfix: template loading.

= 1.8.4 =
* Form validation with the jQuery validatation plugin. You need to check `Use the jQuery validation` in the global settings. ex) class = required, class = email, class = url, etc.
* Support of the multiple option of the textarea type with TinyMCE.
* Code cleaning.
* Czech (cs_CZ) - Jakub

= 1.8.3 =
* Bugfix: combination of the fieldset type and the normal type.

= 1.8.2 =
* Bugfix: multiple options for the fieldset type.

= 1.8.1 =
* Bugfix: tag save.

= 1.8 =
* Added the `tagName` attribute for the `insertTag` of the custom taxonomy.
* Bugfix: value count.
* Bugfix: search.

= 1.7.9 =
* Bugfix: media blank issue of the custom post type.

= 1.7.8 =
* Updated jquery.datePicker.js

= 1.7.7 =
* Easier way to output images of the file type. ex) [cft key="file" image_size="thumbnail"] [cft key="file" image_size="medium" image_src=1] [cft key="file" image_size="large" image_width=1] [cft key="file" image_size="full" image_height=1] 
* You can use shortcodes in the cft shortcode format.
* Bugfix: search.
* Dutch (nl_NL) - Rene

= 1.7.6 =
* Bugfix: Box title.
* Bugfix: posts_per_page.

= 1.7.5 =
* Bugfix: search.

= 1.7.4 =
* `mediaPicker` attribute for the file type to select the file form Media Library. This attribute currently does not work with `multipleButton` attribute. ex) `mediaPicker = true`
* Bugfix: eval system.
* Bugfix: TinyMCE for the custom post types.

= 1.7.3 =
* Bugfix: image insert.

= 1.7.2 =
* `mediaLibrary` attribute for the file type to show the link of the uploaded file name. ex) `mediaLibrary = true`
* Bugfix: search.

= 1.7.1 =
* Bugfix: JavaScript error.

= 1.6.9 =
* Bugfix: template loading.

= 1.6.8 =
* Bugfix: prepared statement of cft search sqls.

= 1.6.7 =
* Bugfix: Code error. Do not use 1.6.6.

= 1.6.6 =
* Quick Edit for the custom post type.
* Bugfix: prepared statement of cft search sqls.
* Catalan (ca) - Andreu Llos

= 1.6.5 =
* Bugfix: the judgement of post types.

= 1.6.4 =
* Changed the default ADMIN CSS.
* Textarea resizer.

= 1.6.3 =
* Bugfix: useb disable button.

= 1.6.2 =
* Global Settings
* Text to place before and after every list and value which is called by the cft shortcode
* Bugfix: controlling conditions in WordPress 3.0

= 1.6.1 =
* Bugfix: selectable custom field templates in the custom post type.
* Bugfix: disable the default custom fields in the custom post type.
* Chinese (zh_CN) - hurri zhu

= 1.6 =
* Custom post type support.
* Bugfix: meta value save, ADMIN CSS, and cftsearch.
* Brazilian Portuguese (pt_BR) - Caciano Gabriel
* Indonesian (id_ID) - Masino Sinaga

= 1.5.7 =
* Bugfix: strip slashes.
* Bugfix: misjudgment of Autho Hook of `the_content()`.

= 1.5.6 =
* Bugfix: disable the default custom fields in the page edit screen.
* Bugfix: compatible with old WordPress versions.

= 1.5.5 =
* Bugfix: hide the preview button in order to prevent duplicate uploads.

= 1.5.4 =
* Bugfix: custom field ids.

= 1.5.3 =
* Bugfix: backslashes and cftsearch.

= 1.5.1 =
* Bugfix: backslashes are not saved.

= 1.5 =
* Bugfix: fieldset type.
* Bugfix: ajax save button.

= 1.4.9 =
* Options to disable the cutom field template, the initialize button, and the save button.
* Bugfix: enctype missing.
* Bugfix: meta values not being deleted when a file is deleted.

= 1.4.8 =
* Bugfix: file type.

= 1.4.7 =
* Bugfix: duplicate save_post action.
* Bugfix: file type.

= 1.4.6 =
* Bugfix: checkbox id and save.

= 1.4.5 =
* Bugfix: conflict with the cforms plugin.
* Bugfix: delete the empty data.
* Bugfix: checkbox label.

= 1.4.4 =
* Bugfix: file type.

= 1.4.3 =
* Bugfix: group add new button, multiple values, and quotation marks.

= 1.4.2 =
* Bugfix: search functionality.

= 1.4 =
* `file` type in order to upload images. If you set `relation = true` with `type = file`, the image you upload will be related to the post you are editing. The image id will be saved as the meta value. You can use the `multiple = true` and `multipleButton = true`.
* The group functionality. The fields between `type = fieldset_open` and `type = fieldset_close` will be enclosed in the fieldset. The key name of the `fieldset_open` and `fieldset_close` must be same. You can use `multipleButton = true` with `type = fieldset_open`. You can also use the `legend = blah` attribute. 
* Revision of the interpretation of the template codes.
* Bugfix: loading templates in switching catgories.
* Hungarian and Uzbek.

= 1.3.8 =
* `readOnly` attribute. ex) `readOnly = true`
* `startDate` and `endDate` attributes for the date picker. ex) `startDate = '1970/01/01'` and `endDate = (new Date()).asString()`
* `mediaOffImage`, `mediaOffVideo`, `mediaOffAudio`, and `mediaOffMedia` attributes. ex) `mediaOffImage = true`

= 1.3.7 =
* Bugfix: class attribute of `text` type.
* `shortCode` attribute in order to output the shortcode filtered values. ex) `shortCode = true`
* `outputNone` attribute in case there is no data to output. ex) `outputNone = No Data`
* `singleList` attribute in order to output with `<ul><li>` if the value is single. ex) `singleList = true`
* Option not to display the custom field column on the edit post list page.

= 1.3.6 =
* Changelog.

= 1.3.3 =
* Exerpt Shortcode option.

= 1.3 =
* Attributes of the date picker in `text` type. ex) `date = true`, `dateFirstDayOfWeek = 0`, `dateFormat = yyyy/mm/dd`
* Filter of page template file names (Thanks, Joel Pittet).

= 1.2.7 =
* Post ID options.

= 1.2.5 =
* French and Belorussian.

= 1.2 =
* Attributes of multiple fields. ex) `multiple = true`, `startNum = 5`, `endNum = 10`, `multipleButton = true`

= 1.1.7 =
* Maxlength attribute. ex) `maxlength = 10`

= 1.1.5 =
* Style attribute.

= 1.1.3 =   
* Attributes of before and after text. ex) `before = blah`, `after = blah`
* Export and import functionality.

= 1.1.1 =   
* Initialize button.
* Auto hook inside the content. ex) `[cfthook hook=0]`

= 1.1 =   
* Attributes of JavaScript Event Handlers. (`onclick = alert('ok');`) Event Handlers: onclick, ondblclick, onkeydown, onkeypress, onkeyup, onmousedown, onmouseup, onmouseover, onmouseout, onmousemove, onfocus, onblur, onchange, onselect 

= 1.0.8 =   
* Option to use the shortcode in the widhet.

= 1.0.7 =   
* Select option of the post type.
* Value count option.

= 1.0.5 =   
* Box title replacement option.

= 1.0.4 =   
* Option to disable the quick edit.
* Attribute of HTML Editor in the textarea. ex) `htmlEditor = true`
* Italian (it_IT) - Gianni Diurno

= 1.0.3 =   
* Option to disable the default custom fields.

= 1.0 =
* Custom field search. (only shows the attributes which have `search = true`.)
* [cftsearch] Shortcode Format.
* PHP codes for the output value. ex) `outputCode = 0`
* PHP codes before saving the values. ex) `editCode = 0`
* Save functionality.
* Class option. ex) `class = text`
* Auto hook of `the_content()`.
* German (de_DE) - F J Kaiser
* Turkish (tr_TR) - Omer Faruk

= 0.9 =
* Sort option. ex) `sort = asc` or `sort = desc`
* Quick Edit of custom fields.

= 0.8 =
* The value label option for the case that values are diffrent from viewed values. (`valueLabel = apples # oranges # bananas`).
* Blank option. ex) `blank = true`
* Break type. Set CSS of '#cft div'. ex) `type = break` | #cft div { width:50%; float:left; }
* [cft] Shortcode Format.
* Russian (ru_RU) - Sonika

= 0.7.3 =
* Spanish (es_ES) - Dario Ferrer.

= 0.7.2 =
* PHP codes for `checkbox`.

= 0.7.1 =
* Template Instruction.

= 0.7 =
* Inserting custom field values into tags automatically. ex) `insertTag = true`
* [cft] Shortcode to display the custom field template. (only shows the attributes which have `output = true`).

= 0.6.5 =
* User level in each field. ex) `level = 2`

= 0.6.4 =
* PHP codes in order to set values of `radio` and `select` types. ex) `code = 0`

= 0.6 =
* `type = text`, which is same as `type = textfield`.
* Option to replace custom keys by labels

= 0.5 =
* Full option list.
* `clearButton = true` in radios.
* Keeps tinyMCE height after resizing the textarea and saving the post.

= 0.4.4 =
* Multiple checkboxes.

= 0.4 =
* Multiple fields with the same key.
* hideKey options. ex) `hideKey = true`
* The default of media buttons is false. ex) `mediaButton = true`

= 0.3.1 =
* Media buttons in the textarea.

= 0.2 =
* TinyMCE in the textarea.

= 0.1 =
* Initial release.

== Screenshots ==

1. Custom Field Template - Settings
2. Custom Field Template

== Known Issues / Bugs ==

== Uninstall ==

1. Deactivate the plugin
2. That's it! :)
