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

	elseif (!defined('SMF'))
		exit('Error Cannot remove - please verify you put this in the same place as SMF\'s index.php.');

	$hooks = array(
		'integrate_default_action' => '$sourcedir/Portal.php|Portal::init#',
		'integrate_actions' => '$sourcedir/Portal.php|Portal::actions#',
		'integrate_general_mod_settings' => '$sourcedir/Portal.php|Portal::settings#',
		'integrate_mark_read_button' => '$sourcedir/Portal.php|Portal::linkTree#',
		'integrate_menu_buttons' => '$sourcedir/Portal.php|Portal::menu#',
	);

	foreach ($hooks as $hook => $function)
		remove_integration_function($hook, $function);
