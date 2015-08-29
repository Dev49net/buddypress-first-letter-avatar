BuddyPress First Letter Avatar
==============

A WordPress-BuddyPress plugin to set custom avatars for users with no Gravatar and no profile avatar. The avatar will be the first (or any other) letter of user's name.

![BuddyPress First Letter Avatar banner](/assets/banner-772x250.png?raw=true)

## Description

This plugin is based on my other plugin, [WP First Letter Avatar](https://github.com/Dev49net/wp-first-letter-avatar). What it does is, by default, checks if user has configured her/his avatar or has Gravatar assigned to the email address. If neither of them is present - custom avatar is used. Custom avatar consists of the first letter of user's name and a colorful background. 

BuddyPress First Letter Avatar helps you bring more colors into your BuddyPress site. Plus, your users will be more willing to actively participate in your site since they can actually relate to these avatars much better than to Mystery Person.

BuddyPress First Letter Avatar includes a set of beautiful, colorful letter avatars in many sizes. Optimal size will be chosen by the plugin in order to display high quality avatar and not download, for example, big 512px avatars when only 48px is needed... PSD template for avatar is also included. 

Plugin is highly configurable - you can disable Gravatar, disable displaying user's avatars, choose different letter, use custom sets of avatars, use rounded avatars etc.

All images were compressed using the fantastic [TinyPNG](https://tinypng.com/), so avatars are incredibly light and ultra-high quality.

## Installation

You can download a [zip from GitHub](https://github.com/Dev49net/buddypress-first-letter-avatar/archive/master.zip) and upload it using the WordPress plugin uploader or manually unzip it and place in ```wp-content/plugins/```. You can also download it from [WordPress.org Plugin Directory](https://wordpress.org/plugins/buddypress-first-letter-avatar/).

## WP First Letter Avatar or BuddyPress First Letter Avatar?

The only difference between [WP First Letter Avatar](https://github.com/Dev49net/wp-first-letter-avatar) and this BuddyPress First Letter Avatar plugin is that the latter is written specifically for BuddyPress. If you don't have BuddyPress activated on your site, BuddyPress First Letter Avatar will not modify your website in any way - it will just do nothing.

BuddyPress First Letter Avatar works on every avatar on BuddyPress site, whereas WP First Letter Avatar works on every avatar on every WordPress site without BuddyPress. So if you use BuddyPress - get this plugin; if you don't - use [WP First Letter Avatar](https://github.com/Dev49net/wp-first-letter-avatar). These two plugins should not be running simultaneously.

## Requirements

BuddyPress First Letter Avatar requires at least PHP 5.4. It **does not work properly** on PHP 5.3.x and earlier.

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

**Use JavaScript for Gravatars:**

Check: use JavaScript to check for Gravatars (faster); Uncheck: use PHP to check for Gravatars (slower).

**Round avatars:**

Check: use rounded avatars; Uncheck: use standard avatars.

## Issues
If you notice any errors or have an idea for improving the plugin, please write on [WordPress plugin support forum](https://wordpress.org/support/plugin/buddypress-first-letter-avatar).