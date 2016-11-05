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
updateSettings(array('_configPortal' => json_encode($_config)));

// Update or create $modSettings['OharaAutoload']
if (empty($modSettings['OharaAutoload']))
	$pref = array(
		'namespaces' => array(
			'Guzzle' => array('{$vendorDir}/guzzle/guzzle/src'),
		),
		'psr4' => array(
			'Symfony\\Component\\EventDispatcher\\' => array('{$vendorDir}/symfony/event-dispatcher'),
			'Github\\' => array('{$vendorDir}/knplabs/github-api/lib/Github'),
		),
		'classmap' => array(),
	);

else
{
	$pref = smf_json_decode($modSettings['OharaAutoload'], true);

	$pref['namespaces']['Guzzle'] = array('{$vendorDir}/guzzle/guzzle/src');
	$pref['psr4']['Symfony\\Component\\EventDispatcher\\'] = array('{$vendorDir}/symfony/event-dispatcher');
	$pref['psr4']['Github\\'] = array('{$vendorDir}/knplabs/github-api/lib/Github');
}

// Either way, save it.
updateSettings(array('OharaAutoload' => json_encode($pref)));

if (SMF == 'SSI')
	echo 'Database changes are complete!';