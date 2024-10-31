=== Plugin Name ===
Contributors: posterd
Donate link: http://design.lviv.ua/
Tags: post, image, thumbnail
Requires at least: 2.5
Tested up to: 2.6
Stable tag: 1.1
Version: 1.1

Post Attached Image plugin adds a image thumbnail suggested by user to any post 
in list and a large image on the single page.

== Description ==

Post Attached Image plugin adds a image thumbnail suggested by user to any post 
in list and a large image on the single page. It is usefull to create a products 
catalog for example.

== Installation ==

1. Upload the file post-attached-image.php to the /wp-content/plugins/ directory
2. Activate the plugin through the "Plugins" menu in WordPress
3. Configure the plugin in the "P-Image" menu located under the WordPress "Options" menu

If image can't be uploaded is needed to edit a little file /wp-admin/edit-form-advanced.php at line 12.
It is because your browser can't set encoding type using javascript.
_form_
must be
_form enctype="multipart/form-data"_
Without this string file will not be uploaded.