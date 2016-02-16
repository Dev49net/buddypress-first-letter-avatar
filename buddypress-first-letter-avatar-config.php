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
			__('Plugin configuration', 'buddypress-first-letter-avatar'),
			array($this, 'settings_section_callback'),
			'bpfla_pluginPage'
		);

		add_settings_field(
			'bpfla_letter_index',
			__('Letter index', 'buddypress-first-letter-avatar') . '<br/>' . __('Default:', 'buddypress-first-letter-avatar') . ' 0',
			array($this, 'letter_index_render'),
			'bpfla_pluginPage',
			'bpfla_pluginPage_section'
		);

		add_settings_field(
			'bpfla_file_format',
			__('File format', 'buddypress-first-letter-avatar') . '<br/>' . __('Default:', 'buddypress-first-letter-avatar') . ' png',
			array($this, 'file_format_render'),
			'bpfla_pluginPage',
			'bpfla_pluginPage_section'
		);

		add_settings_field(
			'bpfla_unknown_image',
			__('Unknown image name', 'buddypress-first-letter-avatar') . '<br/>' . __('Default:', 'buddypress-first-letter-avatar') . ' mystery',
			array($this, 'unknown_image_render'),
			'bpfla_pluginPage',
			'bpfla_pluginPage_section'
		);

		add_settings_field(
			'bpfla_avatar_set',
			__('Avatar set', 'buddypress-first-letter-avatar') . '<br/>' . __('Default:', 'buddypress-first-letter-avatar') . ' default',
			array($this, 'avatar_set_render'),
			'bpfla_pluginPage',
			'bpfla_pluginPage_section'
		);

		add_settings_field(
			'bpfla_use_profile_avatar',
			__("Use users' and groups' avatars", 'buddypress-first-letter-avatar') . '<br/>' . __('Default:', 'buddypress-first-letter-avatar') . ' ' .  __('check', 'buddypress-first-letter-avatar'),
			array($this, 'use_profile_avatar_render'),
			'bpfla_pluginPage',
			'bpfla_pluginPage_section'
		);

		add_settings_field(
			'bpfla_use_gravatar',
			__('Use Gravatar', 'buddypress-first-letter-avatar') . '<br/>' . __('Default:', 'buddypress-first-letter-avatar') . ' ' .  __('check', 'buddypress-first-letter-avatar'),
			array($this, 'use_gravatar_render'),
			'bpfla_pluginPage',
			'bpfla_pluginPage_section'
		);

		add_settings_field(
			'bpfla_round_avatars',
			__('Round avatars', 'buddypress-first-letter-avatar') . '<br/>' . __('Default:', 'buddypress-first-letter-avatar') . ' ' .  __('uncheck', 'buddypress-first-letter-avatar'),
			array($this, 'round_avatars_render'),
			'bpfla_pluginPage',
			'bpfla_pluginPage_section'
		);

		add_settings_field(
			'bpfla_filter_priority',
			__('Plugin filter priority', 'buddypress-first-letter-avatar') . '<br/>' . __('Default:', 'buddypress-first-letter-avatar') . ' 10',
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
				<strong><?php _e('Letter index', 'buddypress-first-letter-avatar'); ?></strong><br />
				<?php echo sprintf(__('%s use first letter for the avatar; %s use second letter; %s use last letter, etc.', 'buddypress-first-letter-avatar'),
				'<span style="text-decoration: underline">0</span>:', '<span style="text-decoration: underline">1</span>:', '<span style="text-decoration: underline">-1</span>:'); ?>
			</p>
			<p>
				<strong><?php _e('File format', 'buddypress-first-letter-avatar'); ?></strong><br />
				<?php echo sprintf(__('File format of your avatars, for example %s or %s.', 'buddypress-first-letter-avatar'),
				'<span style="text-decoration: underline">png</span>', '<span style="text-decoration: underline">jpg</span>'); ?>
			</p>
			<p>
				<strong><?php _e('Unknown image name', 'buddypress-first-letter-avatar'); ?></strong><br />
				<?php _e('Name of the file used for unknown usernames (without extension).', 'buddypress-first-letter-avatar'); ?>		
			</p>
			<p>
				<strong><?php _e('Avatar set', 'buddypress-first-letter-avatar'); ?></strong><br />
				<?php _e('Directory where your avatars are stored.', 'buddypress-first-letter-avatar'); ?>	
			</p>
			<p>
				<strong><?php _e("Use users' and groups' avatars", 'buddypress-first-letter-avatar'); ?></strong><br />
				<?php echo sprintf(__("%sCheck%s: use users' and groups' avatars when available; %sUncheck%s: use Gravatar or custom avatars.", 'buddypress-first-letter-avatar'),
				'<span style="text-decoration: underline">', '</span>', '<span style="text-decoration: underline">', '</span>'); ?>
			</p>
			<p>
				<strong><?php _e('Use Gravatar', 'buddypress-first-letter-avatar'); ?></strong><br />
				<?php echo sprintf(__("%sCheck%s: use Gravatar when available; %sUncheck%s: use users' profile avatars or custom avatars.", 'buddypress-first-letter-avatar'),
				'<span style="text-decoration: underline">', '</span>', '<span style="text-decoration: underline">', '</span>'); ?>
			</p>
			<p>
				<strong><?php _e('Round avatars', 'buddypress-first-letter-avatar'); ?></strong><br />
				<?php echo sprintf(__('%sCheck%s: use rounded avatars; %sUncheck%s: use standard avatars. This may not always work - your theme may override this setting.', 'buddypress-first-letter-avatar'),
				'<span style="text-decoration: underline">', '</span>', '<span style="text-decoration: underline">', '</span>'); ?>
			</p>
			<p>
				<strong><?php _e('Filter priority', 'buddypress-first-letter-avatar'); ?></strong><br />
				<?php _e('If you are using multiple avatar plugins, you can increase or decrease execution priority of this plugin. If BuddyPress First Letter Avatar is overriding your other plugins, try changing this to a lower value (for example 9).', 'buddypress-first-letter-avatar'); ?>
			</p>
			<p><?php _e('In case of any problems, please use default values.', 'buddypress-first-letter-avatar'); ?></p>

			<hr />

			<p style="text-align: right; margin-right:30px"><?php 
			$ending_text = sprintf(__('If you like the plugin, please <a href="%s">leave a rating in WordPress Plugin Directory</a>!', 'buddypress-first-letter-avatar'), 'https://wordpress.org/support/view/plugin-reviews/buddypress-first-letter-avatar#postform');
			$ending_text .= '<br />';
			$ending_text .= sprintf(__('BuddyPress First Letter Avatar was created by <a href="%s">Daniel Wroblewski</a>', 'buddypress-first-letter-avatar'), 'http://dev49.net/');
			echo $ending_text;
			?></p>

		</form>
	<?php

	}

}
