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

// Use composer!
require_once ($boarddir .'/vendor/autoload.php');
require_once ($sourcedir .'/ohara/src/Suki/Ohara.php');

class Portal extends Suki\Ohara
{
	public $name = __CLASS__;
	protected $_useConfig = true;

	public function __construct()
	{
		$this->setRegistry();
	}

	public function sideBar()
	{
		global $context;

		$context['sidebar'] = array(
			'github' => false,
			'recent' => $this->getRecent(),
		);

		// Get github data.
		if ($this->status())
		{
			$this->github();

			// Catch any runtime error.
			try
			{
				$this->_github->authenticate($this->setting('githubClient'), $this->setting('githubPass'), Github\Client::AUTH_URL_CLIENT_ID);
				$context['sidebar']['github']['user'] = $this->_github->api('user')->show($this->setting('githubUser'));

				$context['sidebar']['github']['repos'] = $this->_github->api('user')->repositories($this->setting('githubUser'));

				// Pick 5 random repos.
				shuffle($context['sidebar']['github']['repos']);
				$context['sidebar']['github']['repos'] = array_slice($context['sidebar']['github']['repos'], 0, 5);
			}

			catch (RuntimeException $e)
			{
				log_error('issues with github API: '. $e->getMessage());
			}
		}
	}

	public function addInit()
	{
		global $context, $txt, $scripturl;

		// Define some context vars.
		$context[$this->name] = array();

		// Get the news.
		$context[$this->name] = $this->getNews();

		// Set a canonical URL for this page.
		$context['canonical_url'] = $scripturl . (!empty($this->_start) && $this->_start > 1 ? '?news;start='. $this->_start : '');
		$context['page_title'] = sprintf($txt['forum_index'], $context['forum_name']) . (!empty($this->_start) && $this->_start > 1 ? ' - Page '. $this->_start : '');

		loadTemplate($this->name);
		loadTemplate('Sidebar');
		loadTemplate('Ads');

		// Clean everything up!
		$context['template_layers'] = array();
		$context['sub_template'] = 'portal_main';

		// Load what we need when we need it.
		$context['template_layers'] = array(
			'html',
			'body',
			'sidebar',
			'ads',
			'portal',
		);
	}

	public function addSettings(&$config_vars)
	{
		$config_vars[] = $this->text('title');
		$config_vars[] = array('check', $this->name .'_enable', 'subtext' => $this->text('enable_sub'));
		$config_vars[] = array('int', $this->name .'_limit', 'subtext' => $this->text('limit_sub'));
		$config_vars[] = array('int', $this->name .'_maxLimit', 'subtext' => $this->text('maxLimit_sub'));
		$config_vars[] = array('text', $this->name .'_boards', 'subtext' => $this->text('boards_sub'));
		$config_vars[] = array('text', $this->name .'_githubUser', 'subtext' => $this->text('githubUser_sub'));
		$config_vars[] = array('text', $this->name .'_githubClient', 'subtext' => $this->text('githubClient_sub'));
		$config_vars[] = array('text', $this->name .'_githubPass', 'subtext' => $this->text('githubPass_sub'));
		$config_vars[] = '';
	}

	public function addActions(&$actions)
	{
		// Mod is disabled.
		if(!$this->setting('enable'))
			return;

		// Redirect the boardIndex to action "forum".
		$actions['forum'] = array('BoardIndex.php', 'BoardIndex');
	}

	public function addForceTheme()
	{
		// Force the default theme on admin action.
		if ($this->data('action') && ($this->data('action') == 'admin' || $this->data('action') == 'moderate'))
			$_REQUEST['theme'] = 1;
	}

	public function addMenu(&$buttons)
	{
		global $txt, $context, $scripturl;

		// Mod is disabled.
		if(!$this->setting('enable'))
			return;

		$buttons['home']['sub_buttons']['forum'] = array(
			'title' => $this->text('forum_label'),
			'href' => $scripturl . '?action=forum',
			'show' => true,
			'action_hook' => true,
		);

		// Unset the main search button.
		unset($buttons['search']);

		// And add it as a sub button of home.
		$buttons['home']['sub_buttons']['search'] = array(
			'title' => $txt['search'],
			'href' => $scripturl . '?action=search',
			'show' => $context['allow_search'],
			'sub_buttons' => array(
			),
		);

		// Same for members.
		unset($buttons['mlist']);
		$buttons['home']['sub_buttons']['mlist'] = array(
			'title' => $txt['members_title'],
			'href' => $scripturl . '?action=mlist',
			'show' => $context['allow_memberlist'],
			'sub_buttons' => array(
				'mlist_view' => array(
					'title' => $txt['mlist_menu_view'],
					'href' => $scripturl . '?action=mlist',
					'show' => true,
				),
				'mlist_search' => array(
					'title' => $txt['mlist_search'],
					'href' => $scripturl . '?action=mlist;sa=search',
					'show' => true,
					'is_last' => true,
				),
			),
		);

		// Sidebar!!!!
		$this->sideBar();
	}

	public function addMenuActions(&$dummy)
	{
		// Dunno why I added this!
	}

	public function addLinkTree()
	{
		global $context, $scripturl;

		// Only add this if we're on the forum action
		if ($this->setting('enable') && $this->data('action') == 'forum')
			$context['linktree'][] = array(
				'url' => $scripturl . '?action=forum',
				'name' => $this->text('forum_label')
			);
	}

	public function getRecent($num_recent = 5, $exclude_boards = null, $include_boards = null)
	{
		global $settings, $scripturl, $txt, $user_info;
		global $modSettings, $smcFunc, $context;

		// Somebody has been sitting in my chair and nas broken it!
		if (($posts = cache_get_data($this->name .'-recent', 360)) != null)
			return $posts;

		if ($exclude_boards === null && !empty($modSettings['recycle_enable']) && $modSettings['recycle_board'] > 0)
			$exclude_boards = array($modSettings['recycle_board']);

		else
			$exclude_boards = empty($exclude_boards) ? array() : (is_array($exclude_boards) ? $exclude_boards : array($exclude_boards));

		// Only some boards?.
		if (is_array($include_boards) || (int) $include_boards === $include_boards)
			$include_boards = is_array($include_boards) ? $include_boards : array($include_boards);

		elseif ($include_boards != null)
		{
			$output_method = $include_boards;
			$include_boards = array();
		}

		$icon_sources = array();
		foreach ($context['stable_icons'] as $icon)
			$icon_sources[$icon] = 'images_url';

		// Find all the posts in distinct topics.  Newer ones will have higher IDs.
		$request = $smcFunc['db_query']('substring', '
			SELECT
				t.id_topic, b.id_board, b.name AS board_name
			FROM {db_prefix}topics AS t
				INNER JOIN {db_prefix}messages AS ml ON (ml.id_msg = t.id_last_msg)
				LEFT JOIN {db_prefix}boards AS b ON (b.id_board = t.id_board)
			WHERE t.id_last_msg >= {int:min_message_id}' . (empty($exclude_boards) ? '' : '
				AND b.id_board NOT IN ({array_int:exclude_boards})') . '' . (empty($include_boards) ? '' : '
				AND b.id_board IN ({array_int:include_boards})') . '
				AND {query_wanna_see_board}' . ($modSettings['postmod_active'] ? '
				AND t.approved = {int:is_approved}
				AND ml.approved = {int:is_approved}' : '') . '
			ORDER BY t.id_last_msg DESC
			LIMIT ' . $num_recent,
			array(
				'include_boards' => empty($include_boards) ? '' : $include_boards,
				'exclude_boards' => empty($exclude_boards) ? '' : $exclude_boards,
				'min_message_id' => $modSettings['maxMsgID'] - (!empty($context['min_message_topics']) ? $context['min_message_topics'] : 35) * min($num_recent, 5),
				'is_approved' => 1,
			)
		);
		$topics = array();
		while ($row = $smcFunc['db_fetch_assoc']($request))
			$topics[$row['id_topic']] = $row;
		$smcFunc['db_free_result']($request);

		// Did we find anything? If not, bail.
		if (empty($topics))
			return array();

		$recycle_board = !empty($modSettings['recycle_enable']) && !empty($modSettings['recycle_board']) ? (int) $modSettings['recycle_board'] : 0;

		// Find all the posts in distinct topics.  Newer ones will have higher IDs.
		$request = $smcFunc['db_query']('substring', '
			SELECT
				mf.poster_time, mf.subject, ml.id_topic, mf.id_member, ml.id_msg, t.num_replies, t.num_views, mg.online_color,
				mem.email_address, mem.avatar, COALESCE(am.id_attach, 0) AS member_id_attach, am.filename AS member_filename, am.attachment_type AS member_attach_type,
				IFNULL(mem.real_name, mf.poster_name) AS poster_name, ' . ($user_info['is_guest'] ? '1 AS is_read, 0 AS new_from' : '
				IFNULL(lt.id_msg, IFNULL(lmr.id_msg, 0)) >= ml.id_msg_modified AS is_read,
				IFNULL(lt.id_msg, IFNULL(lmr.id_msg, -1)) + 1 AS new_from') . ', mf.icon
			FROM {db_prefix}topics AS t
				INNER JOIN {db_prefix}messages AS ml ON (ml.id_msg = t.id_last_msg)
				INNER JOIN {db_prefix}messages AS mf ON (mf.id_msg = t.id_last_msg)
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = mf.id_member)
				LEFT JOIN {db_prefix}attachments AS am ON (am.id_member = mf.id_member)' . (!$user_info['is_guest'] ? '
				LEFT JOIN {db_prefix}log_topics AS lt ON (lt.id_topic = t.id_topic AND lt.id_member = {int:current_member})
				LEFT JOIN {db_prefix}log_mark_read AS lmr ON (lmr.id_board = t.id_board AND lmr.id_member = {int:current_member})' : '') . '
				LEFT JOIN {db_prefix}membergroups AS mg ON (mg.id_group = mem.id_group)
			WHERE t.id_topic IN ({array_int:topic_list})',
			array(
				'current_member' => $user_info['id'],
				'topic_list' => array_keys($topics),
			)
		);
		$posts = array();
		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			// Censor the subject.
			censorText($row['subject']);
			censorText($row['body']);

			// Recycled icon
			if (!empty($recycle_board) && $topics[$row['id_topic']]['id_board'])
				$row['icon'] = 'recycled';

			if (!empty($modSettings['messageIconChecks_enable']) && !isset($icon_sources[$row['icon']]))
				$icon_sources[$row['icon']] = file_exists($settings['theme_dir'] . '/images/post/' . $row['icon'] . '.png') ? 'images_url' : 'default_images_url';

			// Build the array.
			$posts[] = array(
				'board' => array(
					'id' => $topics[$row['id_topic']]['id_board'],
					'name' => $topics[$row['id_topic']]['board_name'],
					'href' => $scripturl . '?board=' . $topics[$row['id_topic']]['id_board'] . '.0',
					'link' => '<a href="' . $scripturl . '?board=' . $topics[$row['id_topic']]['id_board'] . '.0">' . $topics[$row['id_topic']]['board_name'] . '</a>',
				),
				'topic' => $row['id_topic'],
				'poster' => array(
					'id' => $row['id_member'],
					'name' => $row['poster_name'],
					'href' => empty($row['id_member']) ? '' : $scripturl . '?action=profile;u=' . $row['id_member'],
					'link' => empty($row['id_member']) ? $row['poster_name'] : '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['poster_name'] . '</a>',
					'avatar' => set_avatar_data(array(
						'avatar' => $row['avatar'],
						'email' => $row['email_address'],
						'filename' => !empty($row['member_filename']) ? $row['member_filename'] : '',
					)),
				),
				'subject' => $row['subject'],
				'replies' => $row['num_replies'],
				'views' => $row['num_views'],
				'short_subject' => shorten_subject($row['subject'], 25),
				'time' => timeformat($row['poster_time']),
				'timestamp' => forum_time(true, $row['poster_time']),
				'href' => $scripturl . '?topic=' . $row['id_topic'] . '.msg' . $row['id_msg'] . ';topicseen#new',
				'link' => '<a href="' . $scripturl . '?topic=' . $row['id_topic'] . '.msg' . $row['id_msg'] . '#new" rel="nofollow">' . $row['subject'] . '</a>',
				// Retained for compatibility - is technically incorrect!
				'new' => !empty($row['is_read']),
				'is_new' => empty($row['is_read']),
				'new_from' => $row['new_from'],
				'icon' => '<img src="' . $settings[$icon_sources[$row['icon']]] . '/post/' . $row['icon'] . '.png" style="vertical-align:middle;" alt="' . $row['icon'] . '">',
			);
		}

		$smcFunc['db_free_result']($request);

		// Be nice, rewind!
		cache_put_data($this->name .'-recent', $posts, 360);

		return $posts;
	}

	public function getNews()
	{
		global $txt, $settings, $context;
		global $smcFunc, $scripturl;

		// Someone else found Rome a city of bricks and left it a city of marble.
		if (($return = cache_get_data($this->name .'-news', 360)) != null)
			return $return;

		loadLanguage('Stats');

		// Get some settings.
		$this->_limit = $this->enable('limit') ? (int) $this->setting('limit') : 5;
		$this->_maxLimit = $this->enable('maxLimit') ? (int) $this->setting('maxLimit') : 50;
		$this->_boards = $this->enable('boards') ? explode(',', $this->setting('boards')) : array();
		$this->_start = $this->validate('start') ? (int) $this->data('start') : 0;

		// Load the message icons - the usual suspects.
		$icon_sources = array();
		foreach ($context['stable_icons'] as $icon)
			$icon_sources[$icon] = 'images_url';

		if (!empty($this->modSetting('enable_likes')))
		{
			$context['can_like'] = allowedTo('likes_like');
			$context['can_see_likes'] = allowedTo('likes_view');
		}

		$return = array(
			'news' => array(),
			'pagination' => constructPageIndex($scripturl . '?news', $this->_start, $this->_maxLimit, $this->_limit)
		);

		// Find the post ids.
		$request = $smcFunc['db_query']('', '
			SELECT t.id_first_msg
			FROM {db_prefix}topics as t
			LEFT JOIN {db_prefix}boards as b ON (b.id_board = t.id_board)
			WHERE t.id_board IN({array_int:boards})' . ($this->modSetting('postmod_active') ? '
				AND t.approved = {int:is_approved}' : '') . '
				AND {query_see_board}
			ORDER BY t.id_first_msg DESC
			LIMIT ' . $this->_start . ', '. $this->_limit,
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
				t.num_replies, t.id_topic, m.id_member, m.smileys_enabled, m.id_msg, t.locked, t.id_last_msg, m.id_board,
				mem.email_address, mem.avatar, COALESCE(am.id_attach, 0) AS member_id_attach, am.filename AS member_filename, am.attachment_type AS member_attach_type
			FROM {db_prefix}topics AS t
				INNER JOIN {db_prefix}messages AS m ON (m.id_msg = t.id_first_msg)
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = m.id_member)
				LEFT JOIN {db_prefix}attachments AS am ON (am.id_member = m.id_member)
			WHERE t.id_first_msg IN ({array_int:post_list})
			ORDER BY t.id_first_msg DESC
			LIMIT ' . count($posts),
			array(
				'post_list' => $posts,
			)
		);

		$recycle_board = $this->modSetting('recycle_enable') && $this->modSetting('recycle_board') ? $this->modSetting('recycle_board') : 0;
		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			$row['body'] = parse_bbc($row['body'], $row['smileys_enabled'], $row['id_msg']);

			if (!empty($recycle_board) && $row['id_board'] == $recycle_board)
				$row['icon'] = 'recycled';

			// Check that this message icon is there...
			if ($this->modSetting('messageIconChecks_enable') && !isset($icon_sources[$row['icon']]))
				$icon_sources[$row['icon']] = file_exists($settings['theme_dir'] . '/images/post/' . $row['icon'] . '.png') ? 'images_url' : 'default_images_url';

			censorText($row['subject']);
			censorText($row['body']);

			$return['news'][] = array(
				'id' => $row['id_topic'],
				'message_id' => $row['id_msg'],
				'icon' => '<img src="' . $settings[$icon_sources[$row['icon']]] . '/post/' . $row['icon'] . '.png" alt="' . $row['icon'] . '">',
				'subject' => $row['subject'],
				'time' => timeformat($row['poster_time']),
				'timestamp' => forum_time(true, $row['poster_time']),
				'body' => $row['body'],
				'href' => $scripturl . '?topic=' . $row['id_topic'] . '.0',
				'link' => '<a href="' . $scripturl . '?topic=' . $row['id_topic'] . '.0">' . $row['subject'] . '</a>',
				'replies' => $row['num_replies'],
				'comment_href' => !empty($row['locked']) ? '' : $scripturl . '?action=post;topic=' . $row['id_topic'] . '.' . $row['num_replies'] . ';last_msg=' . $row['id_last_msg'],
				'comment_link' => !empty($row['locked']) ? '' : '<a href="' . $scripturl . '?action=post;topic=' . $row['id_topic'] . '.' . $row['num_replies'] . ';last_msg=' . $row['id_last_msg'] . '">' . $txt['ssi_write_comment'] . '</a>',
				'new_comment' => !empty($row['locked']) ? '' : '<a href="' . $scripturl . '?action=post;topic=' . $row['id_topic'] . '.' . $row['num_replies'] . '">' . $txt['ssi_write_comment'] . '</a>',
				'poster' => array(
					'id' => $row['id_member'],
					'name' => $row['poster_name'],
					'href' => !empty($row['id_member']) ? $scripturl . '?action=profile;u=' . $row['id_member'] : '',
					'link' => !empty($row['id_member']) ? '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['poster_name'] . '</a>' : $row['poster_name'],
					'avatar' => set_avatar_data(array(
						'avatar' => $row['avatar'],
						'email' => $row['email_address'],
						'filename' => !empty($row['member_filename']) ? $row['member_filename'] : '',
					)),
				),
				'locked' => !empty($row['locked']),
				'is_last' => false,
				// Nasty ternary for likes not messing around the "is_last" check.
				'likes' => $this->modSetting('enable_likes') ? array(
					'count' => $row['likes'],
					'you' => in_array($row['id_msg'], prepareLikesContext((int) $row['id_topic'])),
					'can_like' => !$context['user']['is_guest'] && $row['id_member'] != $context['user']['id'] && !empty($context['can_like']),
				) : array(),
			);
		}
		$smcFunc['db_free_result']($request);

		if (empty($return))
			return $return;

		$return['news'][count($return) - 1]['is_last'] = true;

		// Because file system is ALWAYS faster right?
		cache_put_data($this->name .'-news', $return, 360);

		return $return;
	}

	public function ads()
	{
		global $context;

		if (!$context['user']['is_admin'] && !isset($_REQUEST['xml']))
			addInlineJavascript('
  (function(i,s,o,g,r,a,m){i["GoogleAnalyticsObject"]=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,"script","//www.google-analytics.com/analytics.js","ga");

  ga("create", "UA-27276940-1", "auto");
  ga("send", "pageview");', true);

		if ($context['user']['is_logged'] || isset($_REQUEST['xml']))
			return;
	}

	public function github()
	{
		global $cachedir;

		$this->_github = new Github\Client(
			new Github\HttpClient\CachedHttpClient(array('cache_dir' => $cachedir))
		);
	}

	public function status()
	{
		$v = json_decode($this->fetch_web_data('https://status.github.com/api/status.json'));

		if (!empty($v) && trim($v->status) == 'good')
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
		global $sourcedir;

		// Safety first!
		if (empty($url))
			return false;

		// Requires a function in a source file far far away...
		require_once($sourcedir .'/Subs-Package.php');

		// Send the result directly, we are gonna handle it on every case.
		return fetch_web_data($url);
	}
}
