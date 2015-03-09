<?php
/**
 * Plugin Name: BuddyPress First Letter Avatar
 * Plugin URI: https://github.com/DanielAGW/buddypress-first-letter-avatar
 * Contributors: DanielAGW
 * Description: Set custom avatars for BuddyPress users. The avatar will be a first (or any other) letter of the users's name.
 * Version: 1.0.1
 * Author: Daniel Wroblewski
 * Author URI: https://github.com/DanielAGW
 * Tags: avatars, comments, buddypress, custom avatar, discussion, change avatar, avatar, custom wordpress avatar, first letter avatar, comment change avatar, wordpress new avatar, avatar
 * Requires at least: 3.0.1
 * Tested up to: 4.1.1
 * Stable tag: trunk
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */



class BuddyPress_First_Letter_Avatar {

	// Setup (these values always stay the same):
	const BPFLA_IMAGES_PATH = 'images'; // avatars root directory
	const BPFLA_GRAVATAR_URL = 'https://secure.gravatar.com/avatar/';    // default url for gravatar - we're using HTTPS to avoid annoying warnings

	// Default configuration (this is the default configuration only for the first plugin usage):
	const BPFLA_USE_PROFILE_AVATAR = TRUE;  // TRUE: if user has his profile avatar, use it; FALSE: use custom avatars or Gravatars
	const BPFLA_USE_GRAVATAR = TRUE;  // TRUE: if user has Gravatar, use it; FALSE: use custom avatars or user's profile avatar
	const BPFLA_AVATAR_SET = 'default'; // directory where avatars are stored
	const BPFLA_LETTER_INDEX = 0;  // 0: first letter; 1: second letter; -1: last letter, etc.
	const BPFLA_IMAGES_FORMAT = 'png';   // file format of the avatars
	const BPFLA_ROUND_AVATARS = FALSE;     // TRUE: use rounded avatars; FALSE: dont use round avatars
	const BPFLA_IMAGE_UNKNOWN = 'mystery';    // file name (without extension) of the avatar used for users with usernames beginning
										// with symbol other than one from a-z range
	// variables duplicating const values (will be changed in constructor after reading config from DB):
	private $use_profile_avatar = self::BPFLA_USE_PROFILE_AVATAR;
	private $use_gravatar = self::BPFLA_USE_GRAVATAR;
	private $avatar_set = self::BPFLA_AVATAR_SET;
	private $letter_index = self::BPFLA_LETTER_INDEX;
	private $images_format = self::BPFLA_IMAGES_FORMAT;
	private $round_avatars = self::BPFLA_ROUND_AVATARS;
	private $image_unknown = self::BPFLA_IMAGE_UNKNOWN;



	public function __construct(){

		// add Settings link to plugins page:
		add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'bpfla_add_settings_link'));

		// add stylesheets/scripts:
		add_action('wp_enqueue_scripts', array($this, 'bpfla_add_scripts'));

		// add filter to get_avatar:
		add_filter('get_avatar', array($this, 'set_comment_avatar'), 10, 5); // this will only be used for anonymous WordPress comments

		// add filter to bp_core_fetch_avatar:
		add_filter('bp_core_fetch_avatar', array($this, 'set_buddypress_avatar'), 10, 1);

		// get plugin configuration from database:
		$options = get_option('bpfla_settings');
		if (empty($options)){
			// no records in DB, use default (const) values to save plugin config:
			$settings = array(
				'bpfla_use_profile_avatar' => self::BPFLA_USE_PROFILE_AVATAR,
				'bpfla_use_gravatar' => self::BPFLA_USE_GRAVATAR,
				'bpfla_avatar_set' => self::BPFLA_AVATAR_SET,
				'bpfla_letter_index' => self::BPFLA_LETTER_INDEX,
				'bpfla_file_format' => self::BPFLA_IMAGES_FORMAT,
				'bpfla_round_avatars' => self::BPFLA_ROUND_AVATARS,
				'bpfla_unknown_image' => self::BPFLA_IMAGE_UNKNOWN
			);
			add_option('bpfla_settings', $settings);
		} else {
			// there are records in DB for our plugin, let's assign them to our variables:
			$this->use_profile_avatar = $options['bpfla_use_profile_avatar'];
			$this->use_gravatar = $options['bpfla_use_gravatar'];
			$this->avatar_set = $options['bpfla_avatar_set'];
			$this->letter_index = $options['bpfla_letter_index'];
			$this->images_format = $options['bpfla_file_format'];
			$this->round_avatars = $options['bpfla_round_avatars'];
			$this->image_unknown = $options['bpfla_unknown_image'];
		}

	}



	public function bpfla_add_settings_link($links){

		// add localised Settings link do plugin settings on plugins page:
		$settings_link = '<a href="options-general.php?page=buddypress_first_letter_avatar">'.__('Settings', 'default').'</a>';
		array_unshift($links, $settings_link);
		return $links;

	}



	public function bpfla_add_scripts(){

		// add main CSS file:
		wp_enqueue_style('prefix-style', plugins_url('css/style.css', __FILE__) );

	}



	public function set_comment_avatar($avatar, $id_or_email, $size = '96', $default, $alt = ''){

		// create two main variables:
		$name = '';
		$email = '';

		// check if it's a comment:
		$comment_id = get_comment_ID();

		if ($comment_id === NULL){ // if it's not a regular comment, use $id_or_email to get more data

			if (is_numeric($id_or_email)){ // if id_or_email represents user id, get user by id
				$id = (int) $id_or_email;
				$user = get_user_by('id', $id);
			} else if (is_object($id_or_email)){ // if id_or_email represents an object
				if (!empty($id_or_email->user_id)){  // if there we can get user_id from the object, get user by id
					$id = (int) $id_or_email->user_id;
					$user = get_user_by('id', $id);
				}
			} else { // id_or_email is not user_id and is not an object, then it must be an email
				$user = get_user_by('email', $id_or_email);
			}

			if ($user && is_object($user)){ // if commenter is a registered user...
				$name = $user->data->display_name;
				$email = $user->data->user_email;
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

		// first check whether Gravatar should be used at all:
		if ($this->use_gravatar == TRUE){
			// gravatar used as default option, now check whether user's gravatar is set:
			if ($this->gravatar_exists($email)){
				// gravatar is set, output the gravatar img
				$avatar_output = $this->output_gravatar_img($email, $size, $alt);
			} else {
				// gravatar is not set, proceed to choose custom avatar:
				$avatar_output = $this->choose_custom_avatar($name, $size, $alt);
			}
		} else {
			// gravatar is not used as default option, only custom avatars will be used; proceed to choose custom avatar:
			$avatar_output = $this->choose_custom_avatar($name, $size, $alt);
		}

		return $avatar_output;

	}



	public function set_buddypress_avatar($html_data = ''){

		$html_doc = new DOMDocument();
		$html_doc->loadHTML($html_data);
		$image = $html_doc->getElementsByTagName('img');
		foreach($image as $data) {
			$original_image = $data->getAttribute('src');
			$size = $data->getAttribute('width');
			$alt = $data->getAttribute('alt');
			if (stripos($alt, 'Profile picture of ') === 0){ // if our alt attribute has "profile picture of" in the beginning...
				$name = str_replace('Profile picture of ', '', $alt);
			} else if (stripos($alt, 'Profile photo of ') === 0){ // or profile photo of...
				$name = str_replace('Profile photo of ', '', $alt);
			} else { // if there is some problem - just assign alt to name
				$name = $alt;
			}
		}

		// something went wrong, just return what came in function argument:
		if (empty($original_image) || empty($size) || empty($name) || empty($alt)){
			return $html_data;
		}

		// if there is no gravatar URL it means that user has set his own profila avatar,
		// so we're gonna see if we should be using it;
		// if we should, just return the input data and leave the avatar as it was:
		if ($this->use_profile_avatar == TRUE){
			if (stripos($original_image, 'gravatar.com/avatar') === FALSE){
				return $html_data;
			}
		}

		// check whether Gravatar should be used at all:
		if ($this->use_gravatar == TRUE){
			// gravatar used as default option, now check whether user's gravatar is set:
			if ($this->gravatar_exists_uri($original_image)){
				// gravatar is set, return input data (nothing changes):
				return $html_data;
			} else {
				// gravatar is not set, proceed to choose custom avatar:
				$avatar_output = $this->choose_custom_avatar($name, $size, $alt);
			}
		} else {
			// gravatar is not used as default option, only custom avatars will be used; proceed to choose custom avatar:
			$avatar_output = $this->choose_custom_avatar($name, $size, $alt);
		}

		return $avatar_output;

	}



	private function output_gravatar_img($comment_email, $size, $alt = ''){

		// email to gravatar url:
		$avatar_uri = self::BPFLA_GRAVATAR_URL;
		$avatar_uri .= md5(strtolower(trim($comment_email)));
		$avatar_uri .= "?s={$size}&d=mm&r=g";

		// output gravatar:
		return $this->output_img($avatar_uri, $size, $alt);

	}



	private function output_img($avatar_uri, $size, $alt = ''){

		// prepare extra classes for <img> tag depending on plugin settings:
		$extra_img_class = '';
		if ($this->round_avatars == TRUE){
			$extra_img_class .= 'round-avatars';
		}

		$output_data = "<img src='{$avatar_uri}' class='avatar avatar-{$size} photo bpfla {$extra_img_class}' width='{$size}' height='{$size}' alt='{$alt}' />";

		// return the complete <img> tag:
		return $output_data;

	}



	private function choose_custom_avatar($username, $size, $alt = ''){

		// get picture filename (and lowercase it) from commenter name:
		//var_dump($username);
		$file_name = substr($username, $this->letter_index, 1); // get one letter counting from letter_index
		$file_name = strtolower($file_name); // lowercase it...
		// if, for some reason, the result is empty, set file_name to default unknown image:
		if (empty($file_name)){
			$file_name = $this->image_unknown;
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

		// create file path - avatar_path variable will look something like this:
		// http://yourblog.com/wp-content/plugins/wp-first-letter-avatar/images/default/96/k.png):
		$avatar_uri =
			plugins_url() . '/'
			. dirname(plugin_basename(__FILE__)) . '/'
			. self::BPFLA_IMAGES_PATH . '/'
			. $this->avatar_set . '/'
			. $custom_avatar_size . '/'
			. $file_name . '.'
			. $this->images_format;

		// output the final HTML img code:
		return $this->output_img($avatar_uri, $size, $alt);

	}



	private function gravatar_exists($email){

		/*  Check if there is gravatar assigned to this email
		    returns bool: true if gravatar is assigned, false if it is not */

		$hash = md5(strtolower(trim($email))); // email md5 hash used by gravatar system
		$uri = 'http://www.gravatar.com/avatar/' . $hash;

		$result = $this->gravatar_exists_uri($uri);

		return $result;

	}



	private function gravatar_exists_uri($uri){

		/*  Check if there is gravatar assigned to this gravatar url
		    returns bool: true if gravatar is assigned, false if it is not
		    function partially borrowed from http://codex.wordpress.org/Using_Gravatars - thanks! */


		// first check whether it is a gravatar URL; if not, return FALSE
		if (stripos($uri, 'gravatar.com/avatar') === FALSE){
			return FALSE;
		}

		// strip all GET parameters:
		$uri = strtok($uri, '?');
		$uri .=  '?d=404';
		$response = wp_remote_head($uri); // response from gravatar server

		if (is_wp_error($response)){ // caused error?
			$data = 'error';
		} else {
			$data = $response['response']['code']; // no error, assign response code to data
		}

		if ($data == '200'){ // response code is 200, gravatar exists, return true
			return TRUE;
		} else { // response code is not 200, gravatar doesn't exist, return false
			return FALSE;
		}

	}

}


// create BuddyPress_First_Letter_Avatar object:
$bp_first_letter_avatar = new BuddyPress_First_Letter_Avatar();


// require back-end of the plugin
require_once 'buddypress-first-letter-avatar-config.php';
