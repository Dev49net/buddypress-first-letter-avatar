<?php

/**
 * Plugin Name: BuddyPress First Letter Avatar
 * Plugin URI: http://dev49.net
 * Contributors: Dev49.net, DanielAGW
 * Description: Set custom avatars for BuddyPress users. The avatar will be the first (or any other) letter of the user's name on a colorful background.
 * Version: 2.1
 * Author: Dev49.net
 * Author URI: http://dev49.net
 * Tags: avatars, comments, buddypress, custom avatar, discussion, change avatar, avatar, custom wordpress avatar, first letter avatar, comment change avatar, wordpress new avatar, avatar, initial avatar
 * Requires at least: 4.0
 * Tested up to: 4.3.1
 * Stable tag: trunk
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */



class BuddyPress_First_Letter_Avatar {

	// Setup:
	const MINIMUM_PHP = '5.4';
	const MINIMUM_WP = '4.0';
	const IMAGES_PATH = 'images'; // avatars root directory
	const GRAVATAR_URL = 'https://secure.gravatar.com/avatar/';    // default url for gravatar
	const PLUGIN_NAME = 'BuddyPress First Letter Avatar';

	// Default configuration (this is the default configuration only for the first plugin use):
	const USE_PROFILE_AVATAR = TRUE;  // TRUE: if user has his profile avatar, use it; FALSE: use custom avatars or Gravatars
	const USE_GRAVATAR = TRUE;  // TRUE: if user has Gravatar, use it; FALSE: use custom avatars or user's profile avatar
	const AVATAR_SET = 'default'; // directory where avatars are stored
	const LETTER_INDEX = 0;  // 0: first letter; 1: second letter; -1: last letter, etc.
	const IMAGES_FORMAT = 'png';   // file format of the avatars
	const ROUND_AVATARS = FALSE;     // TRUE: use rounded avatars; FALSE: dont use round avatars
	const IMAGE_UNKNOWN = 'mystery';    // file name (without extension) of the avatar used for users with usernames beginning with symbol other than one from a-z range
	const FILTER_PRIORITY = 10;  // plugin filter priority
	
	// variables duplicating const values (will be changed in constructor after reading config from DB):
	private $use_profile_avatar = self::USE_PROFILE_AVATAR;
	private $use_gravatar = self::USE_GRAVATAR;
	private $avatar_set = self::AVATAR_SET;
	private $letter_index = self::LETTER_INDEX;
	private $images_format = self::IMAGES_FORMAT;
	private $round_avatars = self::ROUND_AVATARS;
	private $image_unknown = self::IMAGE_UNKNOWN;
	private $filter_priority = self::FILTER_PRIORITY;



	public function __construct(){

		// add Settings link to plugins page:
		add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_settings_link'));

		// add plugin activation hook:
		register_activation_hook(__FILE__, array($this, 'plugin_activate'));

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
			$this->use_profile_avatar = (array_key_exists('bpfla_use_profile_avatar', $options) ? (bool)$options['bpfla_use_profile_avatar'] : FALSE); 
			$this->use_gravatar = (array_key_exists('bpfla_use_gravatar', $options) ? (bool)$options['bpfla_use_gravatar'] : FALSE);
			$this->avatar_set = (array_key_exists('bpfla_avatar_set', $options) ? (string)$options['bpfla_avatar_set'] : self::AVATAR_SET);
			$this->letter_index = (array_key_exists('bpfla_letter_index', $options) ? (int)$options['bpfla_letter_index'] : self::LETTER_INDEX);
			$this->images_format = (array_key_exists('bpfla_file_format', $options) ? (string)$options['bpfla_file_format'] : self::IMAGES_FORMAT);
			$this->round_avatars = (array_key_exists('bpfla_round_avatars', $options) ? (bool)$options['bpfla_round_avatars'] : FALSE);
			$this->image_unknown = (array_key_exists('bpfla_unknown_image', $options) ? (string)$options['bpfla_unknown_image'] : self::IMAGE_UNKNOWN);
			$this->filter_priority = (array_key_exists('bpfla_filter_priority', $options) ? (int)$options['bpfla_filter_priority'] : self::FILTER_PRIORITY);				
		}

		// add stylesheets/scripts:
		add_action('wp_enqueue_scripts', function(){
			wp_enqueue_style('bpfla-style-handle', plugins_url('css/style.css', __FILE__));
		});	

		// add filter to get_avatar:
		add_filter('get_avatar', array($this, 'set_comment_avatar'), $this->filter_priority, 5); // this will only be used for anonymous WordPress comments (from non-users)

		// add filter to bp_core_fetch_avatar:
		add_filter('bp_core_fetch_avatar', array($this, 'set_buddypress_avatar'), $this->filter_priority, 1); // this is used for every avatar call except the anonymous comment posters

		// when in admin, make sure first letter avatars are not displayed on discussion settings page:
		if (is_admin()){
			global $pagenow;
			if ($pagenow == 'options-discussion.php'){
				remove_filter('get_avatar', array($this, 'set_comment_avatar'), $this->filter_priority);
			}
		}

	}



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
			wp_die('<p><strong>' . self::PLUGIN_NAME . '</strong> plugin requires ' . $flag . ' version ' . $version . ' or greater.</p>', 'Plugin Activation Error',  array('response' => 200, 'back_link' => TRUE));
		}

		// check if BuddyPress is active:
		if (!function_exists('bp_is_active')){
			deactivate_plugins(plugin_basename(__FILE__));
			wp_die('<p><strong>' . self::PLUGIN_NAME . '</strong> plugin requires <strong>BuddyPress</strong> to be activated.</p>', 'Plugin Activation Error',  array('response' => 200, 'back_link' => TRUE));
		}

	}



	public function add_settings_link($links){

		// add localised Settings link do plugin settings on plugins page:
		$settings_link = '<a href="options-general.php?page=buddypress_first_letter_avatar">'.__('Settings', 'default').'</a>';
		array_unshift($links, $settings_link);
		return $links;

	}



	public function set_comment_avatar($avatar, $id_or_email, $size = '96', $default, $alt = ''){

		// create two main variables:
		$name = '';
		$email = '';

		
		if (is_object($id_or_email)){ // id_or_email can actually be also a comment object, so let's check it first
			if (!empty($id_or_email->comment_ID)){
				$comment_id = $id_or_email->comment_ID; // it is a comment object and we can take the ID
			} else {
				$comment_id = NULL;
			}
		} else {
			$comment_id = NULL;
		}

		if ($comment_id === NULL){ // if it's not a regular comment, use $id_or_email to get more data

			if (is_numeric($id_or_email)){ // if id_or_email represents user id, get user by id
				$id = (int) $id_or_email;
				$user = get_user_by('id', $id);
			} else if (is_object($id_or_email)){ // if id_or_email represents an object
				if (!empty($id_or_email->user_id)){  // if we can get user_id from the object, get user by id
					$id = (int) $id_or_email->user_id;
					$user = get_user_by('id', $id);
				}
			}

			if (!empty($user) && is_object($user)){ // if commenter is a registered user...
				$name = $user->data->display_name;
				$email = $user->data->user_email;
			} else if (is_string($id_or_email)){ // if string was supplied
				if (!filter_var($email, FILTER_VALIDATE_EMAIL)){ // if it is NOT email, it must be a username
					$name = $id_or_email;
				} else { // it must be email
					$email = $id_or_email;
					$user = get_user_by('email', $email);
				}	
			} else { // if commenter is not a registered user, we have to try various fallbacks
				$post_id = get_the_ID();
				if ($post_id !== NULL){ // if this actually is a post...
					$post_data = array('name' => '', 'email' => '');
					// first we try for bbPress:
					$post_data['name'] = get_post_meta($post_id, '_bbp_anonymous_name', TRUE);
					$post_data['email'] = get_post_meta($post_id, '_bbp_anonymous_email', TRUE);
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

			$name = get_comment_author();
			$email = get_comment_author_email();

		}


		if (empty($name)){ // if, for some reason, there is no name, use email instead
			$name = $email;
		} else if (empty($email)){ // and if no email, use user/guest name
			$email = $name;
		}		
		
		// check whether Gravatar should be used at all:
		if ($this->use_gravatar == TRUE){
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



	public function set_buddypress_avatar($html_data = ''){

		$html_doc = new DOMDocument();
		$html_doc->loadHTML($html_data);
		$image = $html_doc->getElementsByTagName('img');
		if (empty($image)){
			return $html_data;
		}
		
		foreach ($image as $image_data){
			$original_image = $image_data->getAttribute('src');
			$size = $image_data->getAttribute('width');
			$alt = $image_data->getAttribute('alt');
			$foreign_alt1 = __('Profile picture of %s', 'buddypress');
			$foreign_alt1 = str_replace('%s', '', $foreign_alt1);
			$foreign_alt2 = __('Profile photo of %s', 'buddypress');
			$foreign_alt2 = str_replace('%s', '', $foreign_alt2);
			$foreign_alt3 = __('Profile Photo', 'buddypress');
			if (stripos($alt, 'Profile picture of ') === 0){ // if our alt attribute has "profile picture of" in the beginning...
				$name = str_replace('Profile picture of ', '', $alt);
			} else if (stripos($alt, 'Profile photo of ') === 0){ // or profile photo of...
				$name = str_replace('Profile photo of ', '', $alt);
			} else if (stripos($alt, $foreign_alt1) !== false){
				$name = str_replace($foreign_alt1, '', $alt);
			} else if (stripos($alt, $foreign_alt2) !== false){
				$name = str_replace($foreign_alt2, '', $alt);
			} else if ($alt == $foreign_alt3){
				if (is_user_logged_in()){
					$user = get_user_by('id', get_current_user_id());
					$name = $user->data->display_name;
				} else {
					$name = '';
				}
			} else if (!empty($alt)){ // if there is some problem - just assign alt to name
				$name = $alt;
			} else { // empty alt -> assign logged in user avatar
				if (is_user_logged_in()){
					$user = get_user_by('id', get_current_user_id());
					$name = $user->data->display_name;
				} else {
					$name = '';
				}
			}
		}
		
		// something went wrong, just return what came in function argument:
		if (empty($original_image) || empty($size) || empty($name) || empty($alt)){
			return $html_data;
		}

		// if there is no gravatar URL, it means that user has set his own profile avatar,
		// so we're gonna see if we should be using it;
		// if we should, just return the input data and leave the avatar as it was:
		if ($this->use_profile_avatar == TRUE){
			if (stripos($original_image, 'gravatar.com/avatar') === FALSE){
				return $html_data;
			}
		}
		
		// check whether Gravatar should be used at all:
		if ($this->use_gravatar == TRUE){
			if (stripos($original_image, 'gravatar.com/avatar') === TRUE){
				$gravatar_uri = $this->generate_gravatar_uri_from_gravatar_url($original_image);
			} else {
				$user = get_user_by('slug', $name); // try to find user data by user nicename
				if (!empty($user) && !empty($user->data->user_email)){
					$email = $user->data->user_email;
				} else {
					$email = '';
				}
				$gravatar_uri = $this->generate_gravatar_uri($email, $size);
			}			
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



	private function generate_avatar_img_tag($avatar_uri, $size, $alt = '', $gravatar_uri = ''){

		// prepare extra classes for <img> tag depending on plugin settings:
		$extra_img_class = '';
		if ($this->round_avatars == TRUE){
			$extra_img_class .= 'round-avatars';
		}

		$output_data = "<img alt='{$alt}' src='{$avatar_uri}' class='avatar avatar-{$size} photo bpfla {$extra_img_class}' width='{$size}' height='{$size}' />";

		// return the complete <img> tag:
		return $output_data;

	}



	private function generate_first_letter_uri($username, $size){

		// get picture filename (and lowercase it) from commenter name:
		if (empty($username)){  // if, for some reason, the result is empty, set file_name to default unknown image
			$file_name = $this->image_unknown;
		} else {
			$file_name = substr($username, $this->letter_index, 1); // get one letter counting from letter_index
			$file_name = strtolower($file_name); // lowercase it...
		}

		// create array with allowed character range (in this case it is a-z range):
		$allowed_chars = range('a', 'z');
		// check if the file name meets the requirement; if it doesn't - set it to unknown
		if (!in_array($file_name, $allowed_chars)){
			$file_name = $this->image_unknown;
		}

		// detect most appropriate size based on WP avatar size:
		if ($size <= 48) $custom_avatar_size = '48';
		else if ($size > 48 && $size <= 96) $custom_avatar_size = '96';
		else if ($size > 96 && $size <= 128) $custom_avatar_size = '128';
		else if ($size > 128 && $size <= 256) $custom_avatar_size = '256';
		else $custom_avatar_size = '512';

		// create file path - avatar_uri variable will look something like this:
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



	private function generate_gravatar_uri($email, $size){

		if (!filter_var($email, FILTER_VALIDATE_EMAIL)){ // if email not correct
			$email = ''; // set it to empty string
		}

		// email to gravatar url:
		$avatar_uri = self::GRAVATAR_URL;
		$avatar_uri .= md5(strtolower(trim($email)));
		$avatar_uri .= "?s={$size}&r=g";

		return $avatar_uri;

	}

	
	
}


// create BuddyPress_First_Letter_Avatar object:
$bp_first_letter_avatar = new BuddyPress_First_Letter_Avatar();


// require back-end of the plugin
if (is_admin() && !defined('DOING_AJAX')){
	require_once 'buddypress-first-letter-avatar-config.php';
}
