<?php

/**
 * Plugin Name: BuddyPress First Letter Avatar
 * Text Domain: buddypress-first-letter-avatar
 * Domain Path: /languages/
 * Plugin URI: http://dev49.net
 * Contributors: Dev49.net, DanielAGW
 * Description: Set custom avatars for BuddyPress users. The avatar will be the first (or any other) letter of the user's name on a colorful background.
 * Version: 2.2.6
 * Author: Dev49.net
 * Author URI: http://dev49.net
 * Tags: avatars, comments, buddypress, custom avatar, discussion, change avatar, avatar, custom wordpress avatar, first letter avatar, comment change avatar, wordpress new avatar, avatar, initial avatar
 * Requires at least: 4.4
 * Tested up to: 4.4.2
 * Stable tag: trunk
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */



// Exit if accessed directly:
if (!defined('ABSPATH')){
    exit;
}



class BuddyPress_First_Letter_Avatar {

	// Setup:
	const MINIMUM_PHP = '5.4';
	const MINIMUM_WP = '4.0';
	const IMAGES_PATH = 'images'; // avatars root directory
	const GRAVATAR_URL = 'https://secure.gravatar.com/avatar/'; // default url for gravatar
	const PLUGIN_NAME = 'BuddyPress First Letter Avatar';

	// Default configuration (this is the default configuration only for the first plugin use):
	const USE_PROFILE_AVATAR = true;  // TRUE: if user has his profile avatar, use it; FALSE: use custom avatars or Gravatars
	const USE_GRAVATAR = true;  // TRUE: if user has Gravatar, use it; FALSE: use custom avatars or user's profile avatar
	const AVATAR_SET = 'default'; // directory where avatars are stored
	const LETTER_INDEX = 0;  // 0: first letter; 1: second letter; -1: last letter, etc.
	const IMAGES_FORMAT = 'png';   // file format of the avatars
	const ROUND_AVATARS = false;     // TRUE: use rounded avatars; FALSE: dont use round avatars
	const IMAGE_UNKNOWN = 'mystery';    // file name (without extension) of the avatar used for users with usernames beginning with symbol other than one from a-z range
	const FILTER_PRIORITY = 10;  // plugin filter priority

	// properties duplicating const values (will be changed in constructor after reading config from DB):
	private $use_profile_avatar = self::USE_PROFILE_AVATAR;
	private $use_gravatar = self::USE_GRAVATAR;
	private $avatar_set = self::AVATAR_SET;
	private $letter_index = self::LETTER_INDEX;
	private $images_format = self::IMAGES_FORMAT;
	private $round_avatars = self::ROUND_AVATARS;
	private $image_unknown = self::IMAGE_UNKNOWN;
	private $filter_priority = self::FILTER_PRIORITY;



	public function __construct(){

		/* --------------- CONFIGURATION --------------- */

		// get plugin configuration from database:
		$options = get_option('bpfla_settings');
		if (empty($options)){
			// no records in DB, use default (const) values to save plugin config:
			$initial_settings = array(
				'bpfla_use_profile_avatar' => self::USE_PROFILE_AVATAR,
				'bpfla_use_gravatar' => self::USE_GRAVATAR,
				'bpfla_avatar_set' => self::AVATAR_SET,
				'bpfla_letter_index' => self::LETTER_INDEX,
				'bpfla_file_format' => self::IMAGES_FORMAT,
				'bpfla_round_avatars' => self::ROUND_AVATARS,
				'bpfla_unknown_image' => self::IMAGE_UNKNOWN,
				'bpfla_filter_priority' => self::FILTER_PRIORITY
			);
			add_option('bpfla_settings', $initial_settings);
		} else { // there are records in DB for our plugin
			// and then assign them to our class properties (only if exsits in array):
			$this->use_profile_avatar = (array_key_exists('bpfla_use_profile_avatar', $options) ? (bool)$options['bpfla_use_profile_avatar'] : false);
			$this->use_gravatar = (array_key_exists('bpfla_use_gravatar', $options) ? (bool)$options['bpfla_use_gravatar'] : false);
			$this->avatar_set = (array_key_exists('bpfla_avatar_set', $options) ? (string)$options['bpfla_avatar_set'] : self::AVATAR_SET);
			$this->letter_index = (array_key_exists('bpfla_letter_index', $options) ? (int)$options['bpfla_letter_index'] : self::LETTER_INDEX);
			$this->images_format = (array_key_exists('bpfla_file_format', $options) ? (string)$options['bpfla_file_format'] : self::IMAGES_FORMAT);
			$this->round_avatars = (array_key_exists('bpfla_round_avatars', $options) ? (bool)$options['bpfla_round_avatars'] : false);
			$this->image_unknown = (array_key_exists('bpfla_unknown_image', $options) ? (string)$options['bpfla_unknown_image'] : self::IMAGE_UNKNOWN);
			$this->filter_priority = (array_key_exists('bpfla_filter_priority', $options) ? (int)$options['bpfla_filter_priority'] : self::FILTER_PRIORITY);
		}


		/* --------------- WP HOOKS --------------- */

		// add plugins_loaded action to load textdomain:
		add_action('plugins_loaded', array($this, 'plugins_loaded'));

		// add Settings link to plugins page:
		add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_settings_link'));

		// add plugin activation hook:
		register_activation_hook(__FILE__, array($this, 'plugin_activate'));

		// add stylesheets/scripts:
		add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));

		// add filter to get_avatar:
		add_filter('get_avatar', array($this, 'set_comment_avatar'), $this->filter_priority, 5); // this will only be used for anonymous WordPress comments (from non-users)

		// add filter to bp_core_fetch_avatar:
		add_filter('bp_core_fetch_avatar', array($this, 'set_buddypress_avatar'), $this->filter_priority, 2); // this is used for every avatar call except the anonymous comment posters

		// filter just the avatar URL:
		add_filter('bp_core_fetch_avatar_url', array($this, 'set_buddypress_avatar_url'), $this->filter_priority, 2);

        // add filter for wpDiscuz:
		add_filter('wpdiscuz_author_avatar_field', array($this, 'set_wpdiscuz_avatar'), $this->filter_priority, 4);

		// when in admin, make sure first letter avatars are not displayed on discussion settings page:
		if (is_admin()){
			global $pagenow;
			if ($pagenow == 'options-discussion.php'){
				remove_filter('get_avatar', array($this, 'set_comment_avatar'), $this->filter_priority);
			}
		}

	}



	/*
	 * Plugins loaded - load text domain
	 */
	public function plugins_loaded(){

		load_plugin_textdomain('buddypress-first-letter-avatar', FALSE, basename(dirname(__FILE__)) . '/languages/');

	}



	/*
	 * Add scripts and stylesheets
	 */
	public function enqueue_scripts(){

		wp_enqueue_style('wpfla-style-handle', plugins_url('css/style.css', __FILE__));

	}



	/*
	 * On plugin activation check WP and PHP version and if requirements are not met, disable the plugin and display error
	 */
	public function plugin_activate(){ // plugin activation event

		$php = self::MINIMUM_PHP;
		$wp = self::MINIMUM_WP;

		// check PHP and WP compatibility:
		global $wp_version;
		if (version_compare(PHP_VERSION, $php, '<'))
			$flag = 'PHP';
		else if	(version_compare($wp_version, $wp, '<'))
			$flag = 'WordPress';

		if (!empty($flag)){
			$version = 'PHP' == $flag ? $php : $wp;
			deactivate_plugins(plugin_basename(__FILE__));
			$wrong_version_text = sprintf(__('<p>This plugin requires %s version %s or greater.</p>', 'buddypress-first-letter-avatar'), $flag, $version);
			$wrong_version_message_title = __('Plugin Activation Error', 'buddypress-first-letter-avatar');
			wp_die($wrong_version_text, $wrong_version_message_title, array('response' => 200, 'back_link' => true));
		}

	}



	/*
	 * Add Settings link to Plugins section
	 */
	public function add_settings_link($links){

		// add localised Settings link do plugin settings on plugins page:
		$settings_link = '<a href="options-general.php?page=buddypress_first_letter_avatar">'.__('Settings', 'buddypress-first-letter-avatar').'</a>';
		array_unshift($links, $settings_link);

		return $links;

	}



	/*
     * This is method is used to filter wpDiscuz parameter - it feeds $comment object to get_avatar() function
     * (more on line 102 in wpdiscuz/templates/comment/class.WpdiscuzWalker.php)
     */
	public function set_wpdiscuz_avatar($author_avatar_field, $comment, $user, $profile_url){

        // that's all we need - instead of user ID or guest email supplied in
        // $author_avatar_field, we just need to return the $comment object
		return $comment;

	}



	/*
     * This method is used only for guest comments (BP filters do not filter guest avatars)
	 * It returns a full HTML <img /> tag with avatar (first letter or Gravatar)
	 */
	public function set_comment_avatar($avatar, $id_or_email, $size = '96', $default = '', $alt = ''){

		// create two main variables:
		$name = '';
		$email = '';
		$user = null; // we will try to assign User object to this

		if (is_object($id_or_email)){ // id_or_email can actually be also a comment object, so let's check it first
			if (!empty($id_or_email->comment_ID)){
				$comment_id = $id_or_email->comment_ID; // it is a comment object and we can take the ID
			} else {
				$comment_id = null;
			}
		} else {
			$comment_id = null;
		}

		if ($comment_id === null){ // if it's not a regular comment, use $id_or_email to get more data

			if (is_numeric($id_or_email)){ // if id_or_email represents user id, get user by id
				$id = (int) $id_or_email;
				$user = get_user_by('id', $id);
			} else if (is_object($id_or_email)){ // if id_or_email represents an object
				if (!empty($id_or_email->user_id)){  // if we can get user_id from the object, get user by id
					$id = (int) $id_or_email->user_id;
					$user = get_user_by('id', $id);
				}
			}

			if (!empty($user) && is_object($user)){ // if commenter is a registered user... (technically it's not possible, since ...
				$name = $user->data->display_name; // ... this method is only called when unregistered user writes comments, but it's still worth checking)
				$email = $user->data->user_email;
			} else if (is_string($id_or_email)){ // if string was supplied
				if (!filter_var($id_or_email, FILTER_VALIDATE_EMAIL)){ // if it is NOT email, it must be a username
					$name = $id_or_email;
				} else { // it must be email
					$email = $id_or_email;
					$user = get_user_by('email', $email);
				}
			} else { // if commenter is not a registered user, we have to try various fallbacks
				$post_id = get_the_ID();
				if ($post_id !== null){ // if this actually is a post...
					$post_data = array('name' => '', 'email' => '');
					// first we try for bbPress:
					$post_data['name'] = get_post_meta($post_id, '_bbp_anonymous_name', true);
					$post_data['email'] = get_post_meta($post_id, '_bbp_anonymous_email', true);
					if (!empty($post_data)){ // we have some post data...
						$name = $post_data['name'];
						$email = $post_data['email'];
					}
				} else { // nothing else to do, assign email from id_or_email to email and later use it as name
					if (!empty($id_or_email)){
						$email = $id_or_email;
					}
				}
			}

		} else { // if it's a standard comment, use basic comment functions to retrive info

			$comment = $id_or_email;

			if (!empty($comment->comment_author)){
				$name = $comment->comment_author;
			} else {
				$name = get_comment_author();
			}

			if (!empty($comment->comment_author_email)){
				$email = $comment->comment_author_email;
			} else {
				$email = get_comment_author_email();
			}

		}

		if (empty($name) && !empty($user) && is_object($user)){ // if we do not have the name, but we have user object
			$name = $user->display_name;
		}

		if (empty($email) && !empty($user) && is_object($user)){ // if we do not have the email, but we have user object
			$email = $user->user_email;
		}

		// check whether Gravatar should be used at all:
		if ($this->use_gravatar == true){
			$gravatar_uri = $this->generate_gravatar_uri($email, $size);
			$first_letter_uri = $this->generate_first_letter_uri($name, $size);
			$avatar_uri = $gravatar_uri . '&default=' . urlencode($first_letter_uri);
		} else {
			// gravatar is not used:
			$first_letter_uri = $this->generate_first_letter_uri($name, $size);
			$avatar_uri = $first_letter_uri;
		}

		$avatar_img_output = $this->generate_avatar_img_tag($avatar_uri, $size, $alt); // get final <img /> tag for the avatar/gravatar

		return $avatar_img_output;

	}



	/*
	 * This method is used to filter just the avatar URL. Basically the same as set_buddypress_avatar(),
	 * but it does not return the full <img /> tag, it just returns the image URL
	 */
	public function set_buddypress_avatar_url($image_url = '', $params = array()) {

		$user_id = $params['item_id'];
		$size = $params['width'];
		$alt = $params['alt'];
		$email = $params['email'];

		if (!is_numeric($user_id)){ // user_id was not passed, so we cannot do anything about this avatar
			return $image_url;
		}

		// if there is no gravatar URL, it means that user has set his own profile avatar,
		// so we're gonna see if we should be using it (user avatar);
		// if we should, just return the input data and leave the avatar as it was:
		if ($this->use_profile_avatar == true){
			if (stripos($image_url, 'gravatar.com/avatar') === false){ // we need to specifically check for false (hence '===')
				return $image_url;
			}
		}

		$user = get_user_by('id', $user_id);

		if (empty($size)){ // if for some reason size was not specified...
			$size = 48; // just set it to 48
		}

		if (empty($alt)){
			$alt = __('Profile Photo', 'buddypress');
		}

		$name = $user->data->display_name;

		if (empty($email)){ // email was not supplied with parameters
			$email = $user->data->user_email; // get it by user id
		}

		if (empty($name)){
			$name = bp_core_get_username($user_id); // BuddyPress fallback
		}

		if (empty($name)){
			$name = $user->data->user_nicename; // another fallback (to WP nicename)
		}

		$first_letter_uri = $this->generate_first_letter_uri($name, $size); // get letter URL

		// check whether Gravatar should be used at all:
		if ($this->use_gravatar == true && !empty($email)){ // if we should use gravatar and we have email
			$gravatar_uri = $this->generate_gravatar_uri($email, $size);
			$image_url = $gravatar_uri . '&default=' . urlencode($first_letter_uri);
		} else { // gravatar not used or we do not have email
			$image_url = $first_letter_uri;
		}

		return $image_url;

	}



	/*
	 * This method is used to filter every avatar, except for anonymous comments.
	 * It returns full <img /> HTML tag
	 */
	public function set_buddypress_avatar($html_data = '', $params = array()){

		if (empty($params)){ // data not supplied
			return $html_data; // return original image
		}

		// Create HTML object to get some data out of the image supplied:
		$html_doc = new DOMDocument();
		$html_doc->loadHTML($html_data);
		$image = $html_doc->getElementsByTagName('img');
		if (empty($image)){ // if there is no image...
			return $html_data;
		}

		foreach ($image as $image_data){ // we are using foreach, but in fact there should be only one image
			$original_image_url = $image_data->getAttribute('src'); // url of the original image
			break; // this foreach loop should be exectued only once no matter what, since there is only one img tag, but just to be safe we are going to use break here
		}

		// these params are very well documented in BuddyPress' bp-core-avatar.php file:
		$id = $params['item_id'];
		$object = $params['object'];
		$size = $params['width'];
		$alt = $params['alt'];
		$email = $params['email'];

		if ($object == 'user'){ // if we are filtering user's avatar

			// if there is no gravatar URL, it means that user has set his own profile avatar,
			// so we're gonna see if we should be using it (user avatar);
			// if we should, just return the input data and leave the avatar as it was:
			if ($this->use_profile_avatar == true){
				if (stripos($original_image_url, 'gravatar.com/avatar') === false){ // we need to specifically check for false (hence '===')
					return $html_data;
				}
			}

			if (empty($id) && $id !== 0){ // if id not specified (and id not equal 0)
				if (is_user_logged_in()){ // if user logged in
					$user = get_user_by('id', get_current_user_id());
					$id = get_current_user_id(); // get current user's id
				} else {
					return $html_data; // no id specified and user not logged in - return the original image
				}
			}

			$user = get_user_by('id', $id); // let's get user object from DB

			if (empty($size)){ // if for some reason size was not specified...
				$size = 48; // just set it to 48
			}

			if (empty($alt)){
				$alt = __('Profile Photo', 'buddypress');
			}

			if (empty($email)){ // if for some reason email was not specified
				$email = $user->data->user_email; // get it by user id
			}

			$name = $user->data->display_name;
			if (empty($name)){
				$name = bp_core_get_username($id); // BuddyPress fallback
			}
			if (empty($name)){
				$name = $user->data->user_nicename; // another fallback (to WP nicename)
			}

		} else if ($object == 'group'){ // we're filtering group

			if (empty($id) && $id !== 0){ // if for some reason there is no id
				return $html_data;
			}

			$group = groups_get_group(array('group_id' => $id)); // get the Group object by ID

			if (empty($group)){ // if for some reason group is empty/does not exist/etc.
				return $html_data; // return the input data
			}

			// we are using the same way to determine whether group has avatar set as we did with user avatars
			// if there is no gravatar URL, it means that group has their own avatar,
			// so we're gonna see if we should be using it (user/group avatar);
			// if we should, just return the input data and leave the avatar as it was:
			if ($this->use_profile_avatar == true){
				if (stripos($original_image_url, 'gravatar.com/avatar') === false){ // we need to specifically check for false (hence '===')
					return $html_data;
				}
			}

			if (empty($group->name)){ // if for some reason there is no name
				return $html_data;
			}

			$name = $group->name;

			if (empty($size)){ // if for some reason size was not specified...
				$size = 96; // just set it to 96
			}

			if (empty($alt)){
				$alt = __('Group logo of %s', 'buddypress');
			}

		} else if ($object == 'blog'){ // we're filtering blog

			return $html_data;	// this feature is not used at all, so just return the input parameter

		} else { // not user, not group and not blog - just return the input html image

			return $html_data;

		}

		$first_letter_uri = $this->generate_first_letter_uri($name, $size); // get letter URL

		// check whether Gravatar should be used at all:
		if ($this->use_gravatar == true && !empty($email)){ // if we should user gravatar and we have email
			$gravatar_uri = $this->generate_gravatar_uri($email, $size);
			$avatar_uri = $gravatar_uri . '&default=' . urlencode($first_letter_uri);
		} else { // gravatar not used or we do not have email
			$avatar_uri = $first_letter_uri;
		}

		$avatar_img_output = $this->generate_avatar_img_tag($avatar_uri, $size, $alt); // get final <img /> tag for the avatar/gravatar

		return $avatar_img_output;

	}



	/*
	 * Generate full HTML <img /> tag with avatar URL, size, CSS classes etc.
	 */
	private function generate_avatar_img_tag($avatar_uri, $size, $alt = ''){

		// prepare extra classes for <img> tag depending on plugin settings:
		$extra_img_class = '';
		if ($this->round_avatars == true){
			$extra_img_class .= 'round-avatars';
		}

		$output_data = "<img alt='{$alt}' src='{$avatar_uri}' class='avatar avatar-{$size} photo bpfla {$extra_img_class}' width='{$size}' height='{$size}' />";

		// return the complete <img> tag:
		return $output_data;

	}



	/*
	 * This method generates full URL for letter avatar (for example http://yourblog.com/wp-content/plugins/buddypress-first-letter-avatar/images/default/96/k.png),
	 * according to the $name and $size provided
	 */
	private function generate_first_letter_uri($name, $size){

		// get picture filename (and lowercase it) from commenter name:
		if (empty($name)){  // if, for some reason, the name is empty, set file_name to default unknown image

			$file_name = $this->image_unknown;

		} else { // name is not empty, so we can proceed

			$file_name = substr($name, $this->letter_index, 1); // get one letter counting from letter_index
			$file_name = strtolower($file_name); // lowercase it...

			if (extension_loaded('mbstring')){ // check if mbstring is loaded to allow multibyte string operations
				$file_name_mb = mb_substr($name, $this->letter_index, 1); // repeat, this time with multibyte functions
				$file_name_mb = mb_strtolower($file_name_mb); // and again...
			} else { // mbstring is not loaded - we're not going to worry about it, just use the original string
				$file_name_mb = $file_name;
			}

			// couple of exceptions:
			if ($file_name_mb == 'ą'){
				$file_name = 'a';
				$file_name_mb = 'a';
			} else if ($file_name_mb == 'ć'){
				$file_name = 'c';
				$file_name_mb = 'c';
			} else if ($file_name_mb == 'ę'){
				$file_name = 'e';
				$file_name_mb = 'e';
			} else if ($file_name_mb == 'ń'){
				$file_name = 'n';
				$file_name_mb = 'n';
			} else if ($file_name_mb == 'ó'){
				$file_name = 'o';
				$file_name_mb = 'o';
			} else if ($file_name_mb == 'ś'){
				$file_name = 's';
				$file_name_mb = 's';
			} else if ($file_name_mb == 'ż' || $file_name_mb == 'ź'){
				$file_name = 'z';
				$file_name_mb = 'z';
			}

			// create arrays with allowed character ranges:
			$allowed_numbers = range(0, 9);
			foreach ($allowed_numbers as $number){ // cast each item to string (strict param of in_array requires same type)
				$allowed_numbers[$number] = (string)$number;
			}
			$allowed_letters_latin = range('a', 'z');
			$allowed_letters_cyrillic = range('а', 'ё');
			$allowed_letters_arabic = range('آ', 'ی');
			// check if the file name meets the requirement; if it doesn't - set it to unknown
			$charset_flag = ''; // this will be used to determine whether we are using latin chars, cyrillic chars, arabic chars or numbers
			// check whther we are using latin/cyrillic/numbers and set the flag, so we can later act appropriately:
			if (in_array($file_name, $allowed_numbers, true)){
				$charset_flag = 'number';
			} else if (in_array($file_name, $allowed_letters_latin, true)){
				$charset_flag = 'latin';
			} else if (in_array($file_name, $allowed_letters_cyrillic, true)){
				$charset_flag = 'cyrillic';
			} else if (in_array($file_name, $allowed_letters_arabic, true)){
				$charset_flag = 'arabic';
			} else { // for some reason none of the charsets is appropriate
				$file_name = $this->image_unknown; // set it to uknknown
			}

			if (!empty($charset_flag)){ // if charset_flag is not empty, i.e. flag has been set to latin, number or cyrillic...
				switch ($charset_flag){ // run through various options to determine the actual filename for the letter avatar
					case 'number':
						$file_name = 'number_' . $file_name;
						break;
					case 'latin':
						$file_name = 'latin_' . $file_name;
						break;
					case 'cyrillic':
						$temp_array = unpack('V', iconv('UTF-8', 'UCS-4LE', $file_name_mb)); // beautiful one-liner by @bobince from SO - http://stackoverflow.com/a/27444149/4848918
						$unicode_code_point = $temp_array[1];
						$file_name = 'cyrillic_' . $unicode_code_point;
						break;
					case 'arabic':
						$temp_array = unpack('V', iconv('UTF-8', 'UCS-4LE', $file_name_mb));
						$unicode_code_point = $temp_array[1];
						$file_name = 'arabic_' . $unicode_code_point;
						break;
					default:
						$file_name = $this->image_unknown; // set it to uknknown
						break;
				}
			}

		}

		// detect most appropriate size based on WP avatar size:
		if ($size <= 48) $custom_avatar_size = '48';
		else if ($size > 48 && $size <= 96) $custom_avatar_size = '96';
		else if ($size > 96 && $size <= 128) $custom_avatar_size = '128';
		else if ($size > 128 && $size <= 256) $custom_avatar_size = '256';
		else $custom_avatar_size = '512';

		// create file path - $avatar_uri variable will look something like this:
		// http://yourblog.com/wp-content/plugins/buddypress-first-letter-avatar/images/default/96/k.png):
		$avatar_uri =
			plugins_url() . '/'
			. dirname(plugin_basename(__FILE__)) . '/'
			. self::IMAGES_PATH . '/'
			. $this->avatar_set . '/'
			. $custom_avatar_size . '/'
			. $file_name . '.'
			. $this->images_format;

		// return the final first letter image url:
		return $avatar_uri;

	}



	/*
	 * This method generates full URL for Gravatar, according to the $email and $size provided
	 */
	private function generate_gravatar_uri($email, $size = '96'){

		if (!filter_var($email, FILTER_VALIDATE_EMAIL)){ // if email not correct
			$email = ''; // set it to empty string
		}

		// email to gravatar url:
		$avatar_uri = self::GRAVATAR_URL;
		$avatar_uri .= md5(strtolower(trim($email)));
		$avatar_uri .= "?s={$size}&r=g";

		return $avatar_uri;

	}



	/*
	 * This method is not used, but I'm keeping it since it may be useful.
	 * This method generates a clean gravatar URL from any kind of gravatar URL. It's useful, because it allows to control exactly how the Gravatar URL looks like.
	 * It basically strips any parameters and makes sure that the gravatar URL always has the same format (for example https://secure.gravatar.com/avatar/)
	 */
	/*
	private function generate_gravatar_uri_from_gravatar_url($gravatar_inital_uri){ // this method is needed to make sure we control how the gravatar uri looks like

	// before we start anything, we need to get the actual size from the displayed gravatar:
	$url_parts = parse_url($gravatar_inital_uri);
	if (!empty($url_parts['query'])){
	parse_str($url_parts['query'], $url_query);
	if (!empty($url_query['s'])){
	$size = $url_query['s'];
	} else if (!empty($url_query['size'])){
	$size = $url_query['size'];
	} else {
	$size = '96';
	}
	} else {
	$size = '96';
	}

	// first let's strip all get parameters:
	$gravatar_uri_array = explode('?', $gravatar_inital_uri);
	$gravatar_uri = $gravatar_uri_array[0];

	$gravatar_uri = strtolower($gravatar_uri); // lowercase the whole url

	$possible_starts = array( // possible ways of how the url may start
	'https://secure.gravatar.com/avatar/',
	'https://www.gravatar.com/avatar/',
	'https://gravatar.com/avatar/',
	'http://secure.gravatar.com/avatar/',
	'http://www.gravatar.com/avatar/',
	'http://gravatar.com/avatar/',
	'//secure.gravatar.com/avatar/',
	'//www.gravatar.com/avatar/',
	'//gravatar.com/avatar/'
	);

	$gravatar_hash = '';

	foreach ($possible_starts as $possible_start){
	if (strpos($gravatar_uri, $possible_start) === 0){ // if starts with this string...
	$gravatar_hash = str_replace($possible_start, '', $gravatar_uri); // we need to remove the possible url beginning, so that we are left with just the md5 gravatar hash
	break; // since we have found what we needed, we can cancel loop execution
	}
	}

	// now we have the just the md5 hash, so we can construct the gravatar uri exactly the way we want:
	$avatar_uri = self::GRAVATAR_URL;
	$avatar_uri .= $gravatar_hash;
	$avatar_uri .= "?s={$size}&r=g";

	return $avatar_uri;

	}
	 */


}


// create BuddyPress_First_Letter_Avatar object:
$bp_first_letter_avatar = new BuddyPress_First_Letter_Avatar();


// require back-end of the plugin
if (is_admin() && !defined('DOING_AJAX')){
	require_once 'buddypress-first-letter-avatar-config.php';
	// create BuddyPress_First_Letter_Avatar_Config object:
	$bp_first_letter_avatar_config = new BuddyPress_First_Letter_Avatar_Config();
}
