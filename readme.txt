=== BuddyPress First Letter Avatar ===
Plugin Name: BuddyPress First Letter Avatar
Text Domain: buddypress-first-letter-avatar
Domain Path: /languages/
Version: 2.2.5
Plugin URI: http://dev49.net
Contributors: Dev49.net, DanielAGW
Tags: avatars, comments, buddypress, custom avatar, discussion, change avatar, avatar, custom wordpress avatar, first letter avatar, comment change avatar, wordpress new avatar, avatar, initial avatar
Requires at least: 4.0
Tested up to: 4.4.2
Stable tag: trunk
Author: Dev49.net
Author URI: http://dev49.net
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A WordPress-BuddyPress plugin to set fancy custom avatars for users with no Gravatar and no profile picture.

== Description ==

BuddyPress First Letter Avatar **sets custom avatars for users without profile picture or Gravatar**. The avatar will be a first letter of user's name. You can also configure plugin to use any other letter to set custom avatar.

BuddyPress First Letter Avatar is based on my other plugin - [WP First Letter Avatar](https://wordpress.org/plugins/wp-first-letter-avatar/). BuddyPress First Letter Avatar is basically the same thing, but programmed to work with BuddyPress.

BuddyPress First Letter Avatar includes a set of **beautiful, colorful letter avatars** in many sizes. Optimal size will be chosen by the plugin in order to display high quality avatar and not download, for example, big 512px avatars when only 48px is needed... **PSD template** for avatar is also included.

You can also create your own avatar set by creating new directory next to *'default'* folder and following the naming convention from *'default'*. 

By default, custom avatar will be set only to users without profile pictures and Gravatars, but you can change that in settings and not use Gravatar/profile pictures at all.

BuddyPress First Letter Avatar helps you **bring more colors into your BuddyPress site**. Plus, your users will be more **willing to actively participate in your site** since they can actually relate to these avatars much better than to the Mystery Person.

All images were compressed using the fantastic [TinyPNG](https://tinypng.com/), so avatars are **incredibly light and ultra-high quality**.

Plugin is also available [on GitHub](https://github.com/Dev49net/buddypress-first-letter-avatar).

= Requirements =
BuddyPress First Letter Avatar requires at least PHP 5.4. It **does not work properly** on PHP 5.3.x and earlier.

== Installation ==

= From WordPress dashboard =

1. Go to *'Plugins > Add New'*.
2. Search for *'BuddyPress First Letter Avatar'*.
3. Activate *'BuddyPress First Letter Avatar'* in *'Plugins'* page.
4. Plugin works right out of the box. For additional configuration, go to *'Settings > BuddyPress First Letter Avatar'*.

= Manual installation =

Extract the zip file and drop the contents in *'wp-content/plugins/'* directory of your WordPress installation, then activate the Plugin from *'Plugins'* page.

== Frequently Asked Questions ==

= Plugin does not work, what should I do? =

There may be some conflict with this plugin and some other plugins you are using. If BuddyPress First Letter Avatar is overriding your other avatar plugins, please go to plugin settings and change Filter Priority value to a lower value - for example 9, or even -1. If other plugins are overriding BuddyPress First Letter Avatar images, try increasing the value to 11 or 9999. Experimenting with these values should give you some results. Filter priority value basically specifies the order that avatar filters are executed in. Setting it to a high value will cause BuddyPress First Letter Avatar to execute after other plugins, whereas setting it to a low value will execute BuddyPress First Letter Avatar before other plugins.

= Can I change custom avatars? =

Absolutely! Just create new directory in 'images' directory, call it, for example 'my_avatar_set' and change the avatar set in settings. Make sure to follow the directory and filename convention. 
NOTE: Your custom avatars WILL BE DELETED after updating the plugin! Make backup! 

= Can I set custom avatars based on last (or any other) character in user's name? =

Of course! This can be done in plugin settings.

= I don't want to use Gravatar at all. Can I disable it? =
Yes! By default, BuddyPress First Letter Avatar sets custom avatar only to users without Gravatar and profile avatar, but in plugin settings you can disable it and use custom avatar for everybody.

= Can avatars be round, like in Google+? =
Yes - just go to plugin settings and click Round avatars.

= Will users still be able to use their own avatars? =
Absolutely. The default priority is: first look for user's profile avatar, then try to get Gravatar. If these options fail, use BuddyPress First Letter Avatar and assign custom first letter avatar. You can disable using users' profile avatars and/or Gravatars in BuddyPress First Letter Avatar settings.

== Screenshots ==

1. BuddyPress First Letter Avatar with standard BuddyPress activity page.
2. This shows three standard WordPress comments with first letter avatars (these commenters don't have their Gravatars) and one with standard Gravatar.
3. Two comments with custom first letter avatars.
4. Set of alphabet avatars in BuddyPress First Letter Avatar.
5. Very simple settings page for BuddyPress First Letter Avatar. You can decide which character should be used to specify avatar, turn off Gravatar, use custom avatar sets, use rounded avatars etc.

== Changelog ==

= 2.2.5 =
* Added Polish translation
* Fixed problem with bbPress avatars

= 2.2.4 =
* Added fallback for Polish letters (thanks Micha³!)
* Plugin prepared for translations (contributors are welcome!)

= 2.2.3 =
* Fixed possible PHP error on activation due to anonymous function used

= 2.2.2 =
* Added support for Arabic letters (huge thanks to **@AmiNimA**)
* Added latest wpDiscuz compatibility
* Fixed possible PHP error

= 2.2.1 =
* Fixed problem with filter priority value

= 2.2 =
* Added support for numbers
* Added support for Cyrillic script (huge thanks to **@collex**)
* Removed error message if BuddyPress is not activated
* Fixed group avatars issue
* Improved BuddyPress compatibility
* Slightly improved performance
* WordPress 4.4 ready
* Small fix: changed description of filter priority value in settings (thanks to **@yolandal**)

= 2.1.1 =
* Fixed minor Gravatar compatibility issue
* Improved coding style (resulting in possibly slightly better performance)

= 2.1 =
* Redesigned Gravatar/first letter avatar choice mechanism (faster and more reliable performance)

= 2.0.1 =
* Fixed possible problem with verifying Gravatars

= 2.0 =
* WordPress 4.3 ready (fully tested)

= 1.0.4 =
* Greatly improved security of AJAX requests
* Added new feature - filter priority (only for advanced users)
* Fixed possible compatibilty issues with other plugins by adding prefix to couple of global JS variables
* Fixed weird error some users experienced (avatars displaying as letter A for every user)
* Fixed user avatar display in admin panel (in Users > Your profile > Extended Profile)
* Asynchronous JavaScript Gravatar verification now as default option for new plugin users
* No longer need to activate plugin on Settings > Discussion page (it was causing problems)
* Changed plugin author from myself to my brand - Dev49.net :-)

= 1.0.3 =
* Fixed couple of minor issues
* Improved JavaScript Gravatar loading
* Improved compatibility with non-English BuddyPress versions
* Added new default avatar in Settings > Discussion page
* Plugin options removed from database after uninstalling (no DB leftovers after uninstalling)
* Added protection disallowing activating plugin on PHP < 5.4 and WP < 4.0
* Added protection disallowing activating plugin without BuddyPress activated

= 1.0.2 =
* PHP 5.4.x or later REQUIRED: PHP 5.3.x is no longer supported by PHP team, if you are still using it - update immediately
* Added asynchronous Gravatar loading for faster page rendering (needs to be activated in plugin Settings)
* Added auto-check to see if one or more options in plugin Settings are not empty
* Fixed standard avatars replacement on Discussion page in Settings
* Couple of minor fixes

= 1.0.1 =
* Fixed avatar presentation in WP-Admin

= 1.0 =
* First BuddyPress First Letter Avatar release

== Upgrade Notice ==

= 2.2.5 =
Fixed problem with bbPress and possibly other plugins - update recommended.

= 2.2.4 =
Added fallback for Polish letters - update not necessary.

= 2.2.3 =
Fixed possible PHP error on activation. Update not necessary.

= 2.2.2 =
Added support for Arabic letters. Update not necessary.

= 2.2.1 =
Fixed filter priority issue. Update strongly recommended.

= 2.2 =
Added support for numbers and improved performance. Update recommended.

= 2.1.1 =
Improved reliability. Update recommended.

= 2.1 =
Improved performance and reliability. Update recommended.

= 2.0.1 =
Fixed possible Gravatar incompatibility. Update recommended.

= 1.0.4 =
Fixed couple of issues, added new features. Update recommended.

= 1.0.3 =
Fixed couple of issues, added new features. Update recommended.

= 1.0.1 =
Fixed avatar presentation in WP-Admin. Update recommended.

= 1.0 =
First BuddyPress First Letter Avatar release.