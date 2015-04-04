=== BuddyPress First Letter Avatar ===
Plugin Name: BuddyPress First Letter Avatar
Version: 1.0.2
Plugin URI: https://github.com/DanielAGW/buddypress-first-letter-avatar
Contributors: DanielAGW
Tags: avatars, comments, buddypress, custom avatar, discussion, change avatar, avatar, custom wordpress avatar, first letter avatar, comment change avatar, wordpress new avatar, avatar
Requires at least: 3.0.1
Tested up to: 4.1.1
Stable tag: trunk
Author: Daniel Wroblewski
Author URI: https://github.com/DanielAGW
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

BuddyPress plugin to set custom avatars for users with no Gravatar and no profile avatar. The avatar will be a first letter of the users's name.

== Description ==

BuddyPress First Letter Avatar **sets custom avatars for users without profile picture or Gravatar**. The avatar will be a first letter of the users's name, just like in [Discourse](http://www.discourse.org/). You can also configure plugin to use any other letter to set custom avatar.

BuddyPress First Letter Avatar is based on my other plugin - [WP First Letter Avatar](https://wordpress.org/plugins/wp-first-letter-avatar/). BuddyPress First Letter Avatar is basically the same thing, but programmed to work with BuddyPress.

BuddyPress First Letter Avatar includes a set of **beautiful, colorful letter avatars** in many sizes. Optimal size will be chosen by the plugin in order to display high quality avatar and not download, for example, big 512px avatars when only 48px is needed... **PSD template** for avatar is also included.

You can also create your own avatar set by creating new directory next to *'default'* folder and following the naming convention from *'default'*. Keep in mind that your avatar set will be deleted after updating plugin!

By default, custom avatar will be set only to users without profile pictures and Gravatars, but you can change that in settings and not use Gravatar/profile pictures at all.

BuddyPress First Letter Avatar helps you **bring more colors into your BuddyPress site**. Plus, your users will be more **willing to actively participate in your site** since they can actually relate to these avatars much better than to Mystery Man.

All images were compressed using the fantastic [TinyPNG](https://tinypng.com/), so avatars are **incredibly light and ultra-high quality**.

You can [fork the plugin on GitHub](https://github.com/DanielAGW/buddypress-first-letter-avatar).

= Requirements =
BuddyPress First Letter Avatar requires at least PHP 5.4.0. It **does not work properly** on PHP 5.3.x and earlier.

== Installation ==

= From WordPress dashboard =

1. Go to *'Plugins > Add New'*.
2. Search for *'BuddyPress First Letter Avatar'*.
3. Activate *'BuddyPress First Letter Avatar'* in *'Plugins'* page.
4. Plugin works right out of the box. For additional configuration, go to *'Settings > BuddyPress First Letter Avatar'*.

= Manual installation =

Extract the zip file and drop the contents in *'wp-content/plugins/'* directory of your WordPress installation, then activate the Plugin from *'Plugins'* page.

== Frequently Asked Questions ==

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

= 1.0.1 =
Fixed avatar presentation in WP-Admin. Update recommended.

= 1.0 =
First BuddyPress First Letter Avatar release.