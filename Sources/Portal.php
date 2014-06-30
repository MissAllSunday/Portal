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

		// Set a canonical URL for this page.
		$context['canonical_url'] = $scripturl . (!empty($page) && $page > 1 ? '?page='. $page : '');
		$context['page_title'] = sprintf($txt['forum_index'], $context['forum_name']) . (!empty($page) && $page > 1 ? ' - Page '. $page : '');

		// Get the news.
		$context[self]['news'] = $this->getNews();
	}

	public function getNews()
	{
		global $scripturl, $txt, $settings, $modSettings, $context;
		global $smcFunc;

		loadLanguage('Stats');

		// Get some settings.
		$this->_limit = $this->enable('limit') ? $this->setting('limit') : 5;
		$this->_boards = $this->enable('boards') ? explode(',', $this->setting('boards')) : array();
		$this->_start = isset($_GET['start']) ? (int) $_GET['start'] : 0;

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
			LIMIT ' . $start . ', ' . $limit,
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
			// If we want to limit the length of the post.
			if (!empty($length) && $smcFunc['strlen']($row['body']) > $length)
			{
				$row['body'] = $smcFunc['substr']($row['body'], 0, $length);
				$cutoff = false;

				$last_space = strrpos($row['body'], ' ');
				$last_open = strrpos($row['body'], '<');
				$last_close = strrpos($row['body'], '>');
				if (empty($last_space) || ($last_space == $last_open + 3 && (empty($last_close) || (!empty($last_close) && $last_close < $last_open))) || $last_space < $last_open || $last_open == $length - 6)
					$cutoff = $last_open;
				elseif (empty($last_close) || $last_close < $last_open)
					$cutoff = $last_space;

				if ($cutoff !== false)
					$row['body'] = $smcFunc['substr']($row['body'], 0, $cutoff);
				$row['body'] .= '...';
			}

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

		if ($output_method != 'echo')
			return $return;
	}
}
