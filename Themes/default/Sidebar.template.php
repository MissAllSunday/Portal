<?php

/**
 * @package Portal mod
 * @version 1.0
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2014, Jessica González
 * @license http://www.mozilla.org/MPL/2.0/
 */

function template_sidebar_main(){}

function template_sidebar_above(){}

function template_sidebar_below()
{
	global $context, $txt, $scripturl, $modSettings;

	echo '
	</div>
	<div class="col s12 l3 " id="sidebar">
		<div class="row">';

	if ($context['user']['is_logged'])
	{
		echo '
					<h4 class="catbg title truncate">
						<i class="material-icons prefix">account_circle</i>
						<a href="', $scripturl, '?action=profile">', $context['user']['name'] ,'</a>
					</h3>
					<div class="row">
						<div class="col s12 center user_info_c">
							', $context['user']['avatar']['image'] ,'
							<p class="row">
								<span class="col s6 center tooltipped" data-tooltip="', $txt['unread_since_visit'], '">
									<i class="material-icons green-text text-darken-3">announcement</i><br>
									<a href="', $scripturl, '?action=unread" class=" green-text text-darken-3">', $txt['view_unread_category'], '</a>
								</span>
								<span class="col s6 center tooltipped" data-tooltip="', $txt['show_unread_replies'], '">
									<i class="material-icons green-text text-darken-3">speaker_notes</i><br>
									<a href="', $scripturl, '?action=unreadreplies" class="green-text text-darken-3">', $txt['unread_replies'], '</a>
								</span>
						</div>
						<div class="divider"></div>
					</div>
					<div class="row">';

		if ($context['allow_pm'])
		{
			echo '
						<div class="col s12">
							<div class="row valign-wrapper pm_block">
								<i class="material-icons">chevron_right</i>
								<a href="', $scripturl, '?action=pm" class="side_pop valign ', !empty($context['self_pm']) ? ' active' : '', '" id="side_pm" data-href="', $scripturl ,'?action=pm;sa=popup">', $txt['pm_short'], '</a>
								', !empty($context['user']['unread_messages']) ? ' <span class="valign new badge">' . $context['user']['unread_messages'] . '</span>' : '', '
							</div>
						</div>
						<div id="side_pm_con" class="row"></div>';
		}

		echo '
						<div class="col s12">
							<div class="row valign-wrapper alerts_block">
								<i class="material-icons">chevron_right</i>
								<a href="', $scripturl, '?action=profile;area=showalerts;u=', $context['user']['id'] ,'" class="side_pop valign', !empty($context['self_alerts']) ? ' active' : '', '" id="side_alerts" data-href="', $scripturl , '?action=profile;area=alerts_popup;u=', $context['user']['id'] ,'">', $txt['alerts'] ,'
								</a>
								', !empty($context['user']['alerts']) ? ' <span class="valign new badge">' . $context['user']['alerts'] . '</span>' : '', '
							</div>
						</div>
						<div id="side_alerts_con" class="row"></div>';

		echo '
					</div>';
	}

	else
	{
		echo '
					<h4 class="catbg title">
						<i class="material-icons">vpn_key</i> ', $txt['login'], '
					</h3>
					<form class="login col s12" action="', $context['login_url'], '" name="frmLogin" id="frmLogin" method="post" accept-charset="', $context['character_set'], '">
						<div class="row">
							<div class="input-field col s12">
								<input type="text" id="', !empty($context['from_ajax']) ? 'ajax_' : '', 'loginuser" name="user" size="20" value="', (!empty($context['default_username']) ? $context['default_username'] : '') , '" class="input_text">
								<label for="', !empty($context['from_ajax']) ? 'ajax_' : '', 'loginuser">', $txt['username'], '</label>
							</div>
						</div>
						<div class="row">
							<div class="input-field col s12">
								<input type="password" id="', !empty($context['from_ajax']) ? 'ajax_' : '', 'loginpass" name="passwrd" value="', (!empty($context['default_password']) ? $context['default_password'] : '') , '" size="20" class="input_password">
								<label for="', !empty($context['from_ajax']) ? 'ajax_' : '', 'loginpass">', $txt['password'], '</label>
							</div>
						</div>
						<div class="row">
							<div class="input-field col s12">
								<input type="number" name="cookielength" size="4" maxlength="4" value="', $modSettings['cookieTime'], '"', !empty($context['never_expire']) ? ' disabled' : '', ' class="input_text" min="1" max="525600" id="min_logged_in">
								<label for="min_logged_in">', $txt['mins_logged_in'], '</label>
							</div>
						</div>
						<div class="row">
							<div class="input-field col s12">
								<input type="checkbox" name="cookieneverexp"', !empty($context['never_expire']) ? ' checked' : '', ' class="input_check" onclick="this.form.cookielength.disabled = this.checked;" id="always_logged">
								<label for="always_logged">', $txt['always_logged_in'], '</label>
							</div>
						</div>';

		// If they have deleted their account, give them a chance to change their mind.
		if (isset($context['login_show_undelete']))
			echo '
						<div class="row">
							<div class="input-field col s12">
								<input type="checkbox" name="undelete" class="input_check" id="show_undelete">
								<label for="show_undelete">', $txt['undelete_account'], '</label>
							</div>
						</div>';

		echo '
						<p>
							<input type="submit" value="', $txt['login'], '" class="button_submit">
						</p>
						<p class="smalltext">
							<a href="', $scripturl, '?action=reminder">', $txt['forgot_your_password'], '</a>
						</p>
						<input type="hidden" name="hash_passwrd" value="">
						<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
						<input type="hidden" name="', $context['login_token_var'], '" value="', $context['login_token'], '">
					</form>';
	}

echo '
				<div class="row">
					<ul class="collapsible portal_recent" data-collapsible="accordion">';

	if (!empty($context['sidebar']['recent']))
	{
		echo '
						<li>
						<div class="collapsible-header"><i class="material-icons">recent_actors</i>Recent topics</div>
						<div class="collapsible-body">
							<div class="row recent">
							<p></p>';

		foreach ($context['sidebar']['recent'] as $r)
			echo '
								<div class="col s12 small">
									<i class="tiny material-icons b_icon_time">query_builder</i> ', date('M j, Y', $r['timestamp']) ,'
								</div>
								<div class="col s12 row">
									<div class="col s3 small">
										<a href="', $r['poster']['href'] ,'">
											<img src="', $r['poster']['avatar']['href'] ,'" class="circle avatar">
										</a>
									</div>
									<div class="col s9 truncate">
										', $r['link'] ,'
										', ($r['is_new']) ? '<span class="new badge"></span>' : '' ,'
									</div>
									<div class="col s12 divider"></div>
								</div>';
		echo '
							</div>
						</div>
						</li>';
	}

	if (!empty($context['sidebar']['github']['repos']))
	{
		echo '
						<li>
						<div class="collapsible-header"><i class="material-icons">code</i>Random repos!</div>
						<div class="collapsible-body">
							<div class="row repos">';

		foreach ($context['sidebar']['github']['repos'] as $r)
			echo '
								<div class="col s12 truncate">
									<h5 class="catbg"><a href="', $r['html_url'] ,'">', $r['name'] ,'</a></h5>
								</div>
								<div class="col s12 row">
									<div class="col s4 center">
										<i class="material-icons green-text text-darken-3">code</i></a><br>
										<span class="small">', $r['language'] ,'</span>
									</div>
									<div class="col s4 center">
										<i class="material-icons brown-text">call_split</i></a><br>
										<span class="small">', $r['forks'] ,'</span>
									</div>
									<div class="col s4 center">
										<i class="material-icons light-blue-text">remove_red_eye</i></a><br>
										<span class="small">', $r['watchers'] ,'</span>
									</div>
									<div class="col s12 container small">
										', $r['description'] ,'
									</div>
									<div class="col s12 divider"></div>
								</div>';

		echo '
							</div>
						</div>
						</li>';
	}

	// Google search
	echo '
						<li>
							<div class="collapsible-header"><i class="material-icons">search</i>Search</div>
							<div class="collapsible-body">
								<div class="row input-field">
<script>
  (function() {
    var cx = \'014230621869110231478:uw3koc4onrw\';
    var gcse = document.createElement(\'script\');
    gcse.type = \'text/javascript\';
    gcse.async = true;
    gcse.src = \'https://cse.google.com/cse.js?cx=\' + cx;
    var s = document.getElementsByTagName(\'script\')[0];
    s.parentNode.insertBefore(gcse, s);
  })();
</script>
<gcse:search></gcse:search>
								</div>
							</div>
						</li>';
	// Close UL
	echo '
					</ul>
				</div>';

	echo '
		</div>
	</div>';
}