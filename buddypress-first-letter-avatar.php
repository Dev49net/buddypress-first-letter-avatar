<?php

/**
 * Plugin Name: BuddyPress First Letter Avatar
 * Plugin URI: https://github.com/DanielAGW/buddypress-first-letter-avatar
 * Contributors: DanielAGW
 * Description: Set custom avatars for BuddyPress users. The avatar will be a first (or any other) letter of the users's name.
 * Version: 1.0.3
 * Author: Daniel Wroblewski
 * Author URI: https://github.com/DanielAGW
 * Tags: avatars, comments, buddypress, custom avatar, discussion, change avatar, avatar, custom wordpress avatar, first letter avatar, comment change avatar, wordpress new avatar, avatar
 * Requires at least: 4.0
 * Tested up to: 4.2
 * Stable tag: trunk
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */



class BuddyPress_First_Letter_Avatar {

	// Setup (these values always stay the same):
	const MINIMUM_PHP = '5.4';
	const MINIMUM_WP = '4.0';
	const BPFLA_IMAGES_PATH = 'images'; // avatars root directory
	const BPFLA_GRAVATAR_URL = 'https://secure.gravatar.com/avatar/';    // default url for gravatar - we're using HTTPS to avoid annoying warnings
	const PLUGIN_NAME = 'BuddyPress First Letter Avatar';

	// Default configuration (this is the default configuration only for the first plugin usage):
	const BPFLA_USE_PROFILE_AVATAR = TRUE;  // TRUE: if user has his profile avatar, use it; FALSE: use custom avatars or Gravatars
	const BPFLA_USE_GRAVATAR = TRUE;  // TRUE: if user has Gravatar, use it; FALSE: use custom avatars or user's profile avatar
	const BPFLA_USE_JS = FALSE;  // TRUE: use JS to replace avatars to Gravatar; FALSE: generate avatars and gravatars here in PHP
	const BPFLA_AVATAR_SET = 'default'; // directory where avatars are stored
	const BPFLA_LETTER_INDEX = 0;  // 0: first letter; 1: second letter; -1: last letter, etc.
	const BPFLA_IMAGES_FORMAT = 'png';   // file format of the avatars
	const BPFLA_ROUND_AVATARS = FALSE;     // TRUE: use rounded avatars; FALSE: dont use round avatars
	const BPFLA_IMAGE_UNKNOWN = 'mystery';    // file name (without extension) of the avatar used for users with usernames beginning
										// with symbol other than one from a-z range
	// variables duplicating const values (will be changed in constructor after reading config from DB):
	private $use_profile_avatar = self::BPFLA_USE_PROFILE_AVATAR;
	private $use_gravatar = self::BPFLA_USE_GRAVATAR;
	private $use_js = self::BPFLA_USE_JS;
	private $avatar_set = self::BPFLA_AVATAR_SET;
	private $letter_index = self::BPFLA_LETTER_INDEX;
	private $images_format = self::BPFLA_IMAGES_FORMAT;
	private $round_avatars = self::BPFLA_ROUND_AVATARS;
	private $image_unknown = self::BPFLA_IMAGE_UNKNOWN;



	public function __construct(){

		// add plugin activation hook:
		register_activation_hook(__FILE__, array($this, 'plugin_activate'));

		// add plugin deactivation hook:
		register_deactivation_hook(__FILE__, array($this, 'plugin_deactivate'));

		// add new avatar to Settings > Discussion page:
		add_filter('avatar_defaults', array($this, 'add_discussion_page_avatar'));

		// check for currently set default avatar:
		$avatar_default = get_option('avatar_default');
		$plugin_avatar = plugins_url(self::BPFLA_IMAGES_PATH . '/bp-first-letter-avatar.png', __FILE__);
		if ($avatar_default != $plugin_avatar){ // if first letter avatar is not activated in settings > discussion page...
			return; // cancel plugin execution
		}

		// add Settings link to plugins page:
		add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'bpfla_add_settings_link'));

		// add stylesheets/scripts for front-end and admin:
		add_action('wp_enqueue_scripts', array($this, 'bpfla_add_scripts'));
		add_action('admin_enqueue_scripts', array($this, 'bpfla_add_scripts'));

		// add Ajax action for asynchronous Gravatar verification:
		add_action('wp_ajax_gravatar_verify', array($this, 'ajax_gravatar_exists'));
		add_action('wp_ajax_nopriv_gravatar_verify', array($this, 'ajax_gravatar_exists'));

		// add filter to get_avatar:
		add_filter('get_avatar', array($this, 'set_comment_avatar'), 10, 5); // this will only be used for anonymous WordPress comments

		// add filter to bp_core_fetch_avatar:
		add_filter('bp_core_fetch_avatar', array($this, 'set_buddypress_avatar'), 10, 1);

		// when in admin, make sure first letter avatars are not displayed on discussion settings page:
		if (is_admin()){
			global $pagenow;
			if ($pagenow == 'options-discussion.php'){
				remove_filter('get_avatar', array($this, 'set_comment_avatar'));
			}
		}

		// get plugin configuration from database:
		$options = get_option('bpfla_settings');
		if (empty($options)){
			// no records in DB, use default (const) values to save plugin config:
			$settings = array(
				'bpfla_use_profile_avatar' => self::BPFLA_USE_PROFILE_AVATAR,
				'bpfla_use_gravatar' => self::BPFLA_USE_GRAVATAR,
				'bpfla_use_js' => self::BPFLA_USE_JS,
				'bpfla_avatar_set' => self::BPFLA_AVATAR_SET,
				'bpfla_letter_index' => self::BPFLA_LETTER_INDEX,
				'bpfla_file_format' => self::BPFLA_IMAGES_FORMAT,
				'bpfla_round_avatars' => self::BPFLA_ROUND_AVATARS,
				'bpfla_unknown_image' => self::BPFLA_IMAGE_UNKNOWN
			);
			add_option('bpfla_settings', $settings);
		} else {
			// there are records in DB for our plugin, let's check if some of them are not empty:
			$change_values = FALSE; // do not update settings by default...
			if ($options['bpfla_avatar_set'] === ''){
				$options['bpfla_avatar_set'] = self::BPFLA_AVATAR_SET;
				$change_values = TRUE;
			}
			if ($options['bpfla_letter_index'] === ''){
				$options['bpfla_letter_index'] = self::BPFLA_LETTER_INDEX;
				$change_values = TRUE;
			}
			if ($options['bpfla_file_format'] === ''){
				$options['bpfla_file_format'] = self::BPFLA_IMAGES_FORMAT;
				$change_values = TRUE;
			}
			if ($options['bpfla_unknown_image'] === ''){
				$options['bpfla_unknown_image'] = self::BPFLA_IMAGE_UNKNOWN;
				$change_values = TRUE;
			}
			if (empty($options['bpfla_use_profile_avatar'])){
				$options['bpfla_use_profile_avatar'] = FALSE;
				$change_values = TRUE;
			}
			if (empty($options['bpfla_use_gravatar'])){
				$options['bpfla_use_gravatar'] = FALSE;
				$change_values = TRUE;
			}
			if (empty($options['bpfla_use_js'])){
				$options['bpfla_use_js'] = FALSE;
				$change_values = TRUE;
			}
			if (empty($options['bpfla_round_avatars'])){
				$options['bpfla_round_avatars'] = FALSE;
				$change_values = TRUE;
			}
			if ($change_values === TRUE){
				$settings['bpfla_use_profile_avatar'] = $options['bpfla_use_profile_avatar'];
				$settings['bpfla_use_gravatar'] = $options['bpfla_use_gravatar'];
				$settings['bpfla_use_js'] = $options['bpfla_use_js'];
				$settings['bpfla_avatar_set'] = $options['bpfla_avatar_set'];
				$settings['bpfla_letter_index'] = $options['bpfla_letter_index'];
				$settings['bpfla_file_format'] = $options['bpfla_file_format'];
				$settings['bpfla_round_avatars'] = $options['bpfla_round_avatars'];
				$settings['bpfla_unknown_image'] = $options['bpfla_unknown_image'];
				update_option('bpfla_settings', $settings);
			}
			// and then assign them to our class properties
			$this->use_profile_avatar = $options['bpfla_use_profile_avatar'];
			$this->use_gravatar = $options['bpfla_use_gravatar'];
			$this->use_js = $options['bpfla_use_js'];
			$this->avatar_set = $options['bpfla_avatar_set'];
			$this->letter_index = $options['bpfla_letter_index'];
			$this->images_format = $options['bpfla_file_format'];
			$this->round_avatars = $options['bpfla_round_avatars'];
			$this->image_unknown = $options['bpfla_unknown_image'];
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

		// backup current active default avatar:
		$current_avatar = get_option('avatar_default');
		update_option('avatar_default_bpfla_backup', $current_avatar);

		// set first letter avatar as main avatar when activating the plugin:
		$avatar_file = plugins_url(self::BPFLA_IMAGES_PATH . '/bp-first-letter-avatar.png', __FILE__);
		update_option('avatar_default' , $avatar_file); // set the new avatar to be the default

	}



	public function plugin_deactivate(){ // plugin deactivation event

		// restore previous default avatar:
		$plugin_option_value = plugins_url(self::BPFLA_IMAGES_PATH . '/bp-first-letter-avatar.png', __FILE__);
		$option_name = 'avatar_default_bpfla_backup';
		$option_value = get_option($option_name);
		if (!empty($option_value) && $option_value != $plugin_option_value){
			update_option('avatar_default' , $option_value);
		}

	}



	public function add_discussion_page_avatar($avatar_defaults){

		// add new avatar to Settings > Discussion page
		$avatar_file = plugins_url(self::BPFLA_IMAGES_PATH . '/bp-first-letter-avatar.png', __FILE__);
		$avatar_defaults[$avatar_file] = self::PLUGIN_NAME;
		return $avatar_defaults;

	}



	public function bpfla_add_settings_link($links){

		// add localised Settings link do plugin settings on plugins page:
		$settings_link = '<a href="options-general.php?page=buddypress_first_letter_avatar">'.__('Settings', 'default').'</a>';
		array_unshift($links, $settings_link);
		return $links;

	}



	public function bpfla_add_scripts(){

		// add main CSS file:
		wp_enqueue_style('prefix-style', plugins_url('css/style.css', __FILE__));

		// add main JS file, only when JS is used:
		if ($this->use_js == TRUE){
			$js_variables = array(
				'img_data_attribute' => 'data-bpfla-gravatar',
				'ajaxurl' => admin_url('admin-ajax.php')
			);
			wp_enqueue_script('bpfla-script-handle', plugins_url('js/script.js', __FILE__), array('jquery'));
			wp_localize_script('bpfla-script-handle', 'bpfla_vars_data', $js_variables);
		}

	}



	public function ajax_gravatar_exists(){

		$gravatar_uri = $_POST['gravatar_uri'];
		$gravatar_exists = $this->gravatar_exists_uri($gravatar_uri);

		if ($gravatar_exists == TRUE){
			echo '1';
		} else {
			echo '0';
		}

		exit;

	}



	public function set_comment_avatar($avatar, $id_or_email, $size = '96', $default, $alt = ''){

		// create two main variables:
		$name = '';
		$email = '';

		// check if it's a comment:
		global $comment;
		if (empty($comment)){
			$comment_id = NULL;
		} else {
			$comment_id = get_comment_ID();
		}

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
		if ($this->use_gravatar == TRUE && $this->use_js == FALSE){
			// gravatar used as default option, now check whether user's gravatar is set:
			if ($this->gravatar_exists($email)){
				// gravatar is set, output the gravatar img
				$avatar_output = $this->output_gravatar_img($email, $size, $alt);
			} else {
				// gravatar is not set, proceed to choose custom avatar:
				$avatar_output = $this->choose_custom_avatar($name, $size, $alt);
			}
		} else if ($this->use_gravatar == TRUE && $this->use_js == TRUE){
			// gravatar with JS is used as default option, only custom avatars will be used; proceed to choose custom avatar:
			$avatar_output = $this->choose_custom_avatar($name, $size, $alt, $email);
		} else {
			// gravatar is not used:
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
			$foreign_alt1 = __('Profile picture of %s', 'buddypress');
			$foreign_alt1 = str_replace('%s', '', $foreign_alt1);
			$foreign_alt2 = __('Profile photo of %s', 'buddypress');
			$foreign_alt2 = str_replace('%s', '', $foreign_alt2);
			if (stripos($alt, 'Profile picture of ') === 0){ // if our alt attribute has "profile picture of" in the beginning...
				$name = str_replace('Profile picture of ', '', $alt);
			} else if (stripos($alt, 'Profile photo of ') === 0){ // or profile photo of...
				$name = str_replace('Profile photo of ', '', $alt);
			} else if (stripos($alt, $foreign_alt1) !== false){
				$name = str_replace($foreign_alt1, '', $alt);
			} else if (stripos($alt, $foreign_alt2) !== false){
				$name = str_replace($foreign_alt2, '', $alt);
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

		// if there is no gravatar URL it means that user has set his own profile avatar,
		// so we're gonna see if we should be using it;
		// if we should, just return the input data and leave the avatar as it was:
		if ($this->use_profile_avatar == TRUE){
			if (stripos($original_image, 'gravatar.com/avatar') === FALSE){
				return $html_data;
			}
		}

		// check whether Gravatar should be used at all:
		if ($this->use_gravatar == TRUE && $this->use_js == FALSE){
			// gravatar used as default option, now check whether user's gravatar is set:
			if ($this->gravatar_exists_uri($original_image)){
				// gravatar is set, return input data (nothing changes):
				return $html_data;
			} else {
				// gravatar is not set, proceed to choose custom avatar:
				$avatar_output = $this->choose_custom_avatar($name, $size, $alt);
			}
		} else if ($this->use_gravatar == TRUE && $this->use_js == TRUE){
			$avatar_output = $this->choose_custom_avatar($name, $size, $alt, $original_image);
		} else {
			// gravatar is not used as default option, only custom avatars will be used; proceed to choose custom avatar:
			$avatar_output = $this->choose_custom_avatar($name, $size, $alt);
		}

		return $avatar_output;

	}



	private function generate_gravatar_uri($email, $size){

		// email to gravatar url:
		$avatar_uri = self::BPFLA_GRAVATAR_URL;
		$avatar_uri .= md5(strtolower(trim($email)));
		$avatar_uri .= "?s={$size}&d=mm&r=g";

		return $avatar_uri;

	}



	private function output_gravatar_img($comment_email, $size, $alt = ''){

		// output gravatar:
		$avatar_uri = $this->generate_gravatar_uri($comment_email, $size);
		return $this->output_img($avatar_uri, $size, $alt);

	}



	private function output_img($avatar_uri, $size, $alt = '', $gravatar_uri = ''){

		// prepare extra classes for <img> tag depending on plugin settings:
		$extra_img_class = '';
		$extra_img_tags = '';
		if (!empty($gravatar_uri)){
			$extra_img_tags .= "data-bpfla-gravatar='{$gravatar_uri}'";
		}
		if ($this->round_avatars == TRUE){
			$extra_img_class .= 'round-avatars';
		}

		$output_data = "<img src='{$avatar_uri}' {$extra_img_tags} class='avatar avatar-{$size} photo bpfla {$extra_img_class}' width='{$size}' height='{$size}' alt='{$alt}' />";

		// return the complete <img> tag:
		return $output_data;

	}



	private function choose_custom_avatar($username, $size, $alt = '', $email = ''){

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

		$gravatar_uri = '';

		if (!empty($email)){
			if (filter_var($email, FILTER_VALIDATE_EMAIL)){
				$gravatar_uri .= $this->generate_gravatar_uri($email, $size);
			} else {
				$gravatar_uri .= $email;
			}
		}

		// output the final HTML img code:
		return $this->output_img($avatar_uri, $size, $alt, $gravatar_uri);

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
