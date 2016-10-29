<?php

/**
 * @package Portal mod
 * @version 1.0
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2014, Jessica González
 * @license http://www.mozilla.org/MPL/2.0/
 */

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');

else if(!defined('SMF'))
	die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php and SSI.php files.');

if ((SMF == 'SSI') && !$user_info['is_admin'])
	die('Admin priveleges required.');

// Prepare and insert this mod's config array.
$_config = array(
	'_availableHooks' => array(
		'init' => 'integrate_default_action',
		'actions' => 'integrate_actions',
		'settings' => 'integrate_general_mod_settings',
		'linktree' => 'integrate_mark_read_button',
		'menu' => 'integrate_menu_buttons',
		'forceTheme' => 'integrate_user_info',
		'rssBody' => 'integrate_RssFeed_body',
		'codeBbc' => 'integrate_bbc_codes',
	),
);

// All good.
updateSettings(array('_configBlogNews' => json_encode($_config)));

if (SMF == 'SSI')
	echo 'Database changes are complete!';