<?php

/*
	PHP file containing uninstall procedure.
	BuddyPress First Letter Avatar prefix - 'bpfla'
*/

// delete plugin options:

if (!defined('WP_UNINSTALL_PLUGIN')){
	exit;
}

$option_name = 'bpfla_settings';
if (is_multisite()){
	delete_site_option($option_name);
} else {
	delete_option($option_name);
}

$option_name = 'avatar_default_bpfla_backup';
if (is_multisite()){
	delete_site_option($option_name);
} else {
	delete_option($option_name);
}
