<?php

class Portal extends Ohara
{
	public function init()
	{
		/* Getting the current page. */
		$page = !empty($_GET['page']) ? ( int) trim($_GET['page']) : 1;

		// Set a canonical URL for this page.
		$context['canonical_url'] = $scripturl . (!empty($page) && $page > 1 ? '?page='. $page : '');
		$context['page_title'] = sprintf($txt['forum_index'], $context['forum_name']) . (!empty($page) && $page > 1 ? ' - Page '. $page : '');

		// Use SSI to get the news...
	}
}
