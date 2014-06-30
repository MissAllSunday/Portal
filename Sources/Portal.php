<?php

/**
 * @package Portal mod
 * @version 1.0
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2014, Jessica González
 * @license http://www.mozilla.org/MPL/2.0/
 */

if (!defined('SMF'))
	die('No direct access...');

// I should really, really use composer for this...
require_once($sourcedir . '/Ohara.php');

class Portal extends Ohara
{
	public static $name = __CLASS__;

	public function __construct()
	{
		$this->_page = isset($_GET['start']) ? (int) $_GET['start'] : 0;
	}

	public function init()
	{
		global $context;

		// Set a canonical URL for this page.
		$context['canonical_url'] = $scripturl . (!empty($page) && $page > 1 ? '?page='. $page : '');
		$context['page_title'] = sprintf($txt['forum_index'], $context['forum_name']) . (!empty($page) && $page > 1 ? ' - Page '. $page : '');

		// Get the news.
		$context[self]['news'] = $this->getNews();
	}

	public function getNews()
	{

	}
}
