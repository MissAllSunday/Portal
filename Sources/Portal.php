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

class Portal extends Ohara
{
	public static $name = __CLASS__;

	public function __construct()
	{
		global $boarddir;

		// Use composer!
		require_once ($boarddir .'/vendor/autoload.php');
	}

	public function init()
	{
		global $context;

		// Mod is disabled.
		if(!$this->setting('enable'))
			return;

		// Define some context vars.
		$context[self::$name] = array(
			'news' => array(),
			'github' => array(
				'repos' => false,
				'user' => false,
			),
		);

		loadTemplate(self::$name);

		// Get the news.
		$context[self::$name]['news'] = $this->getNews();

		// Set a canonical URL for this page.
		$context['canonical_url'] = $scripturl . (!empty($this->_page) && $this->_page > 1 ? '?page='. $this->_page : '');
		$context['page_title'] = sprintf($txt['forum_index'], $context['forum_name']) . (!empty($this->_page) && $this->_page > 1 ? ' - Page '. $this->_page : '');
		$context['sub_template'] = 'portal';

		// Get github data.
		if ($this->status())
		{
			$this->github();

			// Catch any runtime error.
			try{
				$this->_github->authenticate($this->setting('githubClient'), $this->setting('githubPass'), Github\Client::AUTH_URL_CLIENT_ID);
				$context[self::$name]['github']['repos'] = $this->_github->api('user')->repositories('MissAllSunday');
				$context[self::$name]['github']['user'] = false;
			}
			catch (RuntimeException $e)
			{
				log_error('issues with github API: '. $e->getMessage());
			}
		}
	}

	public function settings(&$config_vars)
	{
		$config_vars[] = $this->text('title');
		$config_vars[] = array('check', self::$name .'_enable', 'subtext' => $this->text('enable_sub'));
		$config_vars[] = array('int', self::$name .'_limit', 'subtext' => $this->text('limit_sub'));
		$config_vars[] = array('int', self::$name .'_maxLimit', 'subtext' => $this->text('maxLimit_sub'));
		$config_vars[] = array('text', self::$name .'_boards', 'subtext' => $this->text('_sub'));
		$config_vars[] = array('text', self::$name .'_githubClient', 'subtext' => $this->text('_sub'));
		$config_vars[] = array('text', self::$name .'_githubPass', 'subtext' => $this->text('_sub'));
		$config_vars[] = '';
	}

	public function actions(&$actions)
	{
		// Redirect the boardIndex to action "forum".
		$actions['forum'] = array('BoardIndex', 'BoardIndex');
	}

	public function getNews()
	{
		global $scripturl, $txt, $settings, $modSettings, $context;
		global $smcFunc;

		loadLanguage('Stats');

		// Get some settings.
		$this->_limit = $this->enable('limit') ? $this->setting('limit') : 5;
		$this->_maxLimit = $this->enable('maxLimit') ? $this->setting('limit') : 50;
		$this->_boards = $this->enable('boards') ? explode(',', $this->setting('boards')) : array();
		$this->_page = isset($_GET['page']) ? (int) $_GET['page'] : 0;

		// Load the message icons - the usual suspects.
		$stable_icons = array('xx', 'thumbup', 'thumbdown', 'exclamation', 'question', 'lamp', 'smiley', 'angry', 'cheesy', 'grin', 'sad', 'wink', 'poll', 'moved', 'recycled', 'wireless', 'clip');
		$icon_sources = array();
		foreach ($stable_icons as $icon)
			$icon_sources[$icon] = 'images_url';

		if (!empty($modSettings['enable_likes']))
		{
			$context['can_like'] = allowedTo('likes_like');
			$context['can_see_likes'] = allowedTo('likes_view');
		}

		// Find the post ids.
		$request = $smcFunc['db_query']('', '
			SELECT t.id_first_msg
			FROM {db_prefix}topics as t
			LEFT JOIN {db_prefix}boards as b ON (b.id_board = t.id_board)
			WHERE t.id_board IN({array_int:boards})' . ($modSettings['postmod_active'] ? '
				AND t.approved = {int:is_approved}' : '') . '
				AND {query_see_board}
			ORDER BY t.id_first_msg DESC
			LIMIT ' . $this->_page . ', ' . $this->_limit,
			array(
				'boards' => $this->_boards,
				'is_approved' => 1,
			)
		);
		$posts = array();
		while ($row = $smcFunc['db_fetch_assoc']($request))
			$posts[] = $row['id_first_msg'];
		$smcFunc['db_free_result']($request);

		if (empty($posts))
			return array();

		// Find the posts.
		$request = $smcFunc['db_query']('', '
			SELECT
				m.icon, m.subject, m.body, IFNULL(mem.real_name, m.poster_name) AS poster_name, m.poster_time, m.likes,
				t.num_replies, t.id_topic, m.id_member, m.smileys_enabled, m.id_msg, t.locked, t.id_last_msg, m.id_board
			FROM {db_prefix}topics AS t
				INNER JOIN {db_prefix}messages AS m ON (m.id_msg = t.id_first_msg)
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = m.id_member)
			WHERE t.id_first_msg IN ({array_int:post_list})
			ORDER BY t.id_first_msg DESC
			LIMIT ' . count($posts),
			array(
				'post_list' => $posts,
			)
		);
		$return = array();
		$recycle_board = !empty($modSettings['recycle_enable']) && !empty($modSettings['recycle_board']) ? (int) $modSettings['recycle_board'] : 0;
		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			$row['body'] = parse_bbc($row['body'], $row['smileys_enabled'], $row['id_msg']);

			if (!empty($recycle_board) && $row['id_board'] == $recycle_board)
				$row['icon'] = 'recycled';

			// Check that this message icon is there...
			if (!empty($modSettings['messageIconChecks_enable']) && !isset($icon_sources[$row['icon']]))
				$icon_sources[$row['icon']] = file_exists($settings['theme_dir'] . '/images/post/' . $row['icon'] . '.png') ? 'images_url' : 'default_images_url';

			censorText($row['subject']);
			censorText($row['body']);

			$return[] = array(
				'id' => $row['id_topic'],
				'message_id' => $row['id_msg'],
				'icon' => '<img src="' . $settings[$icon_sources[$row['icon']]] . '/post/' . $row['icon'] . '.png" alt="' . $row['icon'] . '">',
				'subject' => $row['subject'],
				'time' => timeformat($row['poster_time']),
				'timestamp' => forum_time(true, $row['poster_time']),
				'body' => $row['body'],
				'href' => $scripturl . '?topic=' . $row['id_topic'] . '.0',
				'link' => '<a href="' . $scripturl . '?topic=' . $row['id_topic'] . '.0">' . $row['num_replies'] . ' ' . ($row['num_replies'] == 1 ? $txt['ssi_comment'] : $txt['ssi_comments']) . '</a>',
				'replies' => $row['num_replies'],
				'comment_href' => !empty($row['locked']) ? '' : $scripturl . '?action=post;topic=' . $row['id_topic'] . '.' . $row['num_replies'] . ';last_msg=' . $row['id_last_msg'],
				'comment_link' => !empty($row['locked']) ? '' : '<a href="' . $scripturl . '?action=post;topic=' . $row['id_topic'] . '.' . $row['num_replies'] . ';last_msg=' . $row['id_last_msg'] . '">' . $txt['ssi_write_comment'] . '</a>',
				'new_comment' => !empty($row['locked']) ? '' : '<a href="' . $scripturl . '?action=post;topic=' . $row['id_topic'] . '.' . $row['num_replies'] . '">' . $txt['ssi_write_comment'] . '</a>',
				'poster' => array(
					'id' => $row['id_member'],
					'name' => $row['poster_name'],
					'href' => !empty($row['id_member']) ? $scripturl . '?action=profile;u=' . $row['id_member'] : '',
					'link' => !empty($row['id_member']) ? '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['poster_name'] . '</a>' : $row['poster_name']
				),
				'locked' => !empty($row['locked']),
				'is_last' => false,
				// Nasty ternary for likes not messing around the "is_last" check.
				'likes' => !empty($modSettings['enable_likes']) ? array(
					'count' => $row['likes'],
					'you' => in_array($row['id_msg'], prepareLikesContext((int) $row['id_topic'])),
					'can_like' => !$context['user']['is_guest'] && $row['id_member'] != $context['user']['id'] && !empty($context['can_like']),
				) : array(),
			);
		}
		$smcFunc['db_free_result']($request);

		if (empty($return))
			return $return;

		$return[count($return) - 1]['is_last'] = true;

		return $return;
	}

	public function github()
	{
		global $boarddir;

		$this->_github = new Github\Client(
			new Github\HttpClient\CachedHttpClient(array('cache_dir' => $boarddir .'/cache/github-api-cache'))
		);
	}

	public function status()
	{
		$v = json_decode($this->fetch_web_data('https://status.github.com/api/status.json'));

		if (!empty($v) && is_object($v) && trim($v->status) == 'good')
			return true;

		else
			return false;
	}

	/**
	 * Tries to fetch the content of a given url
	 *
	 * @access protected
	 * @param string $url the url to call
	 * @return mixed either the page requested or a boolean false
	 */
	protected function fetch_web_data($url)
	{
		// Safety first!
		if (empty($url))
			return false;

		// I can haz cURL?
		if (function_exists ('curl_init'))
		{
			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$content = curl_exec($ch);
			curl_close($ch);

			// Send the data directly, evil, I'm evil! :P
			return $content;
		}

		// Good old SMF's fetch_web_data to the rescue!
		else
		{
			// Requires a function in a source file far far away...
			require_once($this->_sourcedir .'/Subs-Package.php');

			// Send the result directly, we are gonna handle it on every case.
			return fetch_web_data($url);
		}
	}
}
