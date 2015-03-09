BuddyPress First Letter Avatar
==============

A WordPress-BuddyPress plugin to set custom avatars for users with no Gravatar and no profile avatar. The avatar will be a first (or any other) letter of the users's name.

![BuddyPress First Letter Avatar banner](/assets/banner-772x250.png?raw=true)

## Description

This plugin was inspired by avatars used on some [Discourse forums](http://www.discourse.org/) and is based on my other plugin, [WP First Letter Avatar](https://github.com/DanielAGW/wp-first-letter-avatar). What it does is, by default, check if user has configured her/his avatar or has Gravatar assigned to the email address. If neither of them is present - custom avatar is used. Custom avatar consists of user's name first letter and colorful background. 

BuddyPress First Letter Avatar helps you bring more colors into your BuddyPress site. Plus, your users will be more willing to actively participate in your site since they can actually relate to these avatars much better than to Mystery Man.

BuddyPress First Letter Avatar includes a set of beautiful, colorful letter avatars in many sizes. Optimal size will be chosen by the plugin in order to display high quality avatar and not download, for example, big 512px avatars when only 48px is needed... PSD template for avatar is also included. 

Plugin is configurable - you can disable Gravatar, disable displaying user's avatars, choose different letter, use custom sets of avatars, use rounded avatars etc.

All images were compressed using the fantastic [TinyPNG](https://tinypng.com/), so avatars are incredibly light and ultra-high quality.

## Installation

You can download a [zip from GitHub](https://github.com/DanielAGW/buddypress-first-letter-avatar/archive/master.zip) and upload it using the WordPress plugin uploader or manually unzip it and place in ```wp-content/plugins/```. You can also download it from [WordPress.org Plugin Directory](https://wordpress.org/plugins/buddypress-first-letter-avatar/).

## WP First Letter Avatar or BuddyPress First Letter Avatar?

The only difference between [WP First Letter Avatar](https://github.com/DanielAGW/wp-first-letter-avatar) and this BuddyPress First Letter Avatar plugin is that the latter is written specifically for BuddyPress. If you don't have BuddyPress activated on your site, BuddyPress First Letter Avatar will not modify your website in any way - it will just do nothing.

These two plugins can be activated together (there is no conflict between them), but there is no need for it. BuddyPress First Letter Avatar works on every avatar on BuddyPress site, whereas WP First Letter Avatar works on every avatar on every WordPress site without BuddyPress. So if you use BuddyPress - get this plugin; if you don't - use [WP First Letter Avatar](https://github.com/DanielAGW/wp-first-letter-avatar).

## Configuration

Configuration is very simple. Here are configuration options available in options:

**Letter index:**

0: use first letter for the avatar; 1: use second letter; -1: use last letter, etc.

**File format:**

File format of your avatars, for example png or jpg.

**Unknown image name:**

Name of the file used for unknown usernames.

**Avatar set:**

Directory where avatars are stored.

**Use users' profile avatars:**

Check: use users' profile avatars when available; Uncheck: use custom avatars or Gravatars.

**Use Gravatar:**

Check: use Gravatar when available; Uncheck: use custom avatars or users' profile avatars.

**Round avatars:**

Check: use rounded avatars; Uncheck: use standard avatars.

## Issues
If you notice any errors or have an idea for improving the plugin, please open an [issue on GitHub](https://github.com/DanielAGW/buddypress-first-letter-avatar/issues) or write on [WordPress plugin support forum](https://wordpress.org/support/plugin/buddypress-first-letter-avatar).