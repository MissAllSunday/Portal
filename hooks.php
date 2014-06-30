<?php

/**
 * @package Portal mod
 * @version 1.0
 * @author Jessica Gonz�lez <suki@missallsunday.com>
 * @copyright Copyright (c) 2014, Jessica Gonz�lez
 * @license http://www.mozilla.org/MPL/2.0/
 */

	if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
		require_once(dirname(__FILE__) . '/SSI.php');
	elseif (!defined('SMF'))
		exit('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

	$hooks = array(
		'integrate_default_action' => '$sourcedir/Portal.php|Portal::init#',
		'integrate_actions' => '$sourcedir/Portal.php|Portal::actions#',
	);

	$call = 'add_integration_function';

	foreach ($hooks as $hook => $function)
		$call($hook, $function);
