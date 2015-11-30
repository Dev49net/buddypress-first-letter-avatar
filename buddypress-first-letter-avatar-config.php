<?php

/*
	PHP file containing whole back-end of the BuddyPress First Letter Avatar plugin.
	BuddyPress First Letter Avatar prefix - 'bpfla'
 */



// Exit if accessed directly:
if (!defined('ABSPATH')){ 
    exit; 
}


class BuddyPress_First_Letter_Avatar_Config {
	
	
	private $options;


	public function __construct(){

		add_action('admin_menu', array($this, 'add_admin_menu')); // add plugin settings page
		add_action('admin_init', array($this, 'settings_init')); // create plugin settings page content

	}



	/* 
	 * Add plugin settings page
	 */
	public function add_admin_menu(){

		add_options_page('BuddyPress First Letter Avatar', 'BuddyPress First Letter Avatar', 'manage_options', 'buddypress_first_letter_avatar', array($this, 'options_page'));

	}



	/* 
	 * Create plugin settings page content
	 */
	public function settings_init(){

		register_setting('bpfla_pluginPage', 'bpfla_settings');

		 add_settings_section(
			'bpfla_pluginPage_section',
			'Plugin configuration',
			array($this, 'settings_section_callback'),
			'bpfla_pluginPage'
		);

		add_settings_field(
			'bpfla_letter_index',
			'Letter index<br/>Default: 0',
			array($this, 'letter_index_render'),
			'bpfla_pluginPage',
			'bpfla_pluginPage_section'
		);

		add_settings_field(
			'bpfla_file_format',
			'File format<br/>Default: png',
			array($this, 'file_format_render'),
			'bpfla_pluginPage',
			'bpfla_pluginPage_section'
		);

		add_settings_field(
			'bpfla_unknown_image',
			'Unknown image name<br/>Default: mystery',
			array($this, 'unknown_image_render'),
			'bpfla_pluginPage',
			'bpfla_pluginPage_section'
		);

		add_settings_field(
			'bpfla_avatar_set',
			'Avatar set<br/>Default: default',
			array($this, 'avatar_set_render'),
			'bpfla_pluginPage',
			'bpfla_pluginPage_section'
		);

		add_settings_field(
			'bpfla_use_profile_avatar',
			'Use users\' and groups\' avatars<br/>Default: check',
			array($this, 'use_profile_avatar_render'),
			'bpfla_pluginPage',
			'bpfla_pluginPage_section'
		);

		add_settings_field(
			'bpfla_use_gravatar',
			'Use Gravatars<br/>Default: check',
			array($this, 'use_gravatar_render'),
			'bpfla_pluginPage',
			'bpfla_pluginPage_section'
		);

		add_settings_field(
			'bpfla_round_avatars',
			'Round avatars<br/>Default: uncheck',
			array($this, 'round_avatars_render'),
			'bpfla_pluginPage',
			'bpfla_pluginPage_section'
		);

		add_settings_field(
			'bpfla_filter_priority',
			'Plugin filter priority<br/>Default: 10',
			array($this, 'filter_priority_render'),
			'bpfla_pluginPage',
			'bpfla_pluginPage_section'
		);

	}



	/* 
	 * Below methods are responsible for rendering each settings field
	 */
	public function letter_index_render(){

		?>
		<input style="width:40px;" type='text' name='bpfla_settings[bpfla_letter_index]' value='<?php if (array_key_exists('bpfla_letter_index', $this->options)) echo $this->options['bpfla_letter_index']; ?>' />
	<?php

	}
	public function file_format_render(){

		?>
		<input style="width: 100px;" type='text' name='bpfla_settings[bpfla_file_format]' value='<?php if (array_key_exists('bpfla_file_format', $this->options)) echo $this->options['bpfla_file_format']; ?>' />
	<?php

	}
	public function unknown_image_render(){

		?>
		<input type='text' name='bpfla_settings[bpfla_unknown_image]' value='<?php if (array_key_exists('bpfla_unknown_image', $this->options)) echo $this->options['bpfla_unknown_image']; ?>' />
	<?php

	}
	public function avatar_set_render(){

		?>
		<input type='text' name='bpfla_settings[bpfla_avatar_set]' value='<?php if (array_key_exists('bpfla_avatar_set', $this->options)) echo $this->options['bpfla_avatar_set']; ?>' />
	<?php

	}
	public function use_profile_avatar_render(){

		?>
		<input type='checkbox' name='bpfla_settings[bpfla_use_profile_avatar]' <?php if (array_key_exists('bpfla_use_profile_avatar', $this->options)) checked($this->options['bpfla_use_profile_avatar'], 1); ?> value='1' />
	<?php

	}
	public function use_gravatar_render(){

		?>
		<input type='checkbox' name='bpfla_settings[bpfla_use_gravatar]' <?php if (array_key_exists('bpfla_use_gravatar', $this->options)) checked($this->options['bpfla_use_gravatar'], 1); ?> value='1' />
	<?php

	}
	public function round_avatars_render(){

		?>
		<input type='checkbox' name='bpfla_settings[bpfla_round_avatars]' <?php if (array_key_exists('bpfla_round_avatars', $this->options)) checked($this->options['bpfla_round_avatars'], 1); ?> value='1' />
	<?php

	}
	public function filter_priority_render(){

		?>
		<input type='text' name='bpfla_settings[bpfla_filter_priority]' value='<?php if (array_key_exists('bpfla_filter_priority', $this->options)) echo $this->options['bpfla_filter_priority']; ?>' />
	<?php

	}



	/* 
	 * Get plugin options from database
	 */
	public function settings_section_callback(){

		$this->options = get_option('bpfla_settings');

	}



	/* 
	 * Create a settings form
	 */
	public function options_page(){

		?>
		<form action='options.php' method='post'>

			<h2>BuddyPress First Letter Avatar</h2>

			<?php
			settings_fields('bpfla_pluginPage');
			do_settings_sections('bpfla_pluginPage');
			submit_button();
			?>

			<hr />

			<h3>Fields description:</h3>
			<p>
				<strong>Letter index</strong><br />
				<span style="text-decoration: underline">0</span>: use first letter for the avatar; <span style="text-decoration: underline">1</span>: use second letter; <span style="text-decoration: underline">-1</span>: use last letter, etc.
			</p>
			<p>
				<strong>File format</strong><br />
				File format of your avatars, for example <span style="text-decoration: underline">png</span> or <span style="text-decoration: underline">jpg</span>.
			</p>
			<p>
				<strong>Unknown image name</strong><br />
				Name of the file used for unknown usernames (without extension).
			</p>
			<p>
				<strong>Avatar set</strong><br />
				Directory where your avatars are stored.
			</p>
			<p>
				<strong>Use users' and groups' avatars</strong><br />
				<span style="text-decoration: underline">Check</span>: use user's and group's avatar when available; <span style="text-decoration: underline">Uncheck</span>: use Gravatar or custom avatars.
			</p>
			<p>
				<strong>Use Gravatar</strong><br />
				<span style="text-decoration: underline">Check</span>: use Gravatar when available; <span style="text-decoration: underline">Uncheck</span>: use users' profile avatars or custom avatars.
			</p>
			<p>
				<strong>Round avatars</strong><br />
				<span style="text-decoration: underline">Check</span>: use rounded avatars; <span style="text-decoration: underline">Uncheck</span>: use standard avatars.
			</p>
			<p>
				<strong>Filter priority</strong><br />
				If you are using multiple avatar plugins, you can increase or decrease execution priority of this plugin. If BuddyPress First Letter Avatar is overriding your other plugins, try changing this to a lower value (for example 9).
			</p>
			<p>In case of any problems, use default values.</p>

			<hr />

			<p style="text-align: right; margin-right:30px">If you like the plugin, please <a href="https://wordpress.org/support/view/plugin-reviews/buddypress-first-letter-avatar#postform">leave a review in WordPress Plugin Directory</a>!<br />
				BuddyPress First Letter Avatar was created by <a href="http://dev49.net/">Daniel Wroblewski</a></p>

		</form>
	<?php

	}

}
