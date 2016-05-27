<?php

/**
 * @package Portal mod
 * @version 1.0
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2014, Jessica González
 * @license http://www.mozilla.org/MPL/2.0/
 */

function template_portal_above()
{
	global $context, $txt, $scripturl, $modSettings;

	echo '';
}

function template_portal_main()
{
	global $context;

	echo '';
}

function template_portal_below()
{
	global $context, $txt, $scripturl,$modSettings;


	foreach ($context['Portal']['news'] as $n)
	{
		echo '
				<div class="row blog_post">
					<h4 class="catbg row">', $n['link'] ,'</h4>
					<div class="row ">
						<div class="flow-text col s12">
							', $n['body'] ,'
							<p>
							<div class="divider"></div>
						</div>
						<div class="row"></div>
						<div class="row">
							<div class="col s6 m10 info right-align">
								', $n['time'] ,'<i class="tiny material-icons valign">query_builder</i><br>
								', $n['comment_link'] ,'<i class="tiny material-icons ">chat_bubble_outline</i><br>';

		echo '
							</div>
							<div class="col s6 m2">
								<a href="', $n['poster']['href'] ,'">
									<img src="', $n['poster']['avatar']['href'] ,'" class="avatar">
								</a>
							</div>
						</div>
					</div>
				</div>';
	}

	echo '
				<div class="row">
					', $context['Portal']['pagination'] ,'
				</div>';

	echo '
			</div>
			<div class="col s12 l3" id="sidebar">';

	if (!$context['user']['is_logged'])
	{
		echo '
				<div class="row">
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
					</form>
				</div>';
	}

	echo '
				<div class="row">
					<ul class="collapsible portal_recent" data-collapsible="accordion">';

	if (!empty($context['Portal']['recent']))
	{
		echo '
						<li>
						<div class="collapsible-header"><i class="material-icons">recent_actors</i>Recent topics</div>
						<div class="collapsible-body">
							<div class="row recent">
							<p></p>';

		foreach ($context['Portal']['recent'] as $r)
			echo '
								<div class="col s12 truncate">', $r['link'] ,'
								</div>
								<div class="col s12 row">
									<div class="col s3 small">
										<a href="', $r['poster']['href'] ,'">
											<img src="', $r['poster']['avatar']['href'] ,'" class="circle">
										</a>
									</div>
									<div class="col s9 small">
										<i class="tiny material-icons">query_builder</i> ', date('M j, Y', $r['timestamp']) ,'
										', ($r['is_new']) ? '<span class="new badge"></span>' : '' ,'
									</div>
									<div class="col s12 divider"></div>
								</div>';
		echo '
							</div>
						</div>
						</li>';
	}

	if (!empty($context['Portal']['github']['repos']))
	{
		echo '
						<li>
						<div class="collapsible-header"><i class="material-icons">code</i>Random repos!</div>
						<div class="collapsible-body">
							<div class="row repos">';

		foreach ($context['Portal']['github']['repos'] as $r)
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
									<div class="col s12 container">
										', $r['description'] ,'
									</div>
									<div class="col s12 divider"></div>
								</div>';

		echo '
							</div>
						</div>
						</li>';
	}


	echo '
					</ul>
				</div>
			</div>
		</div>
	</div>';

	// Show the XHTML, RSS and WAP2 links, as well as the copyright.
	// Footer is now full-width by default.
	echo '
	<footer class="page-footer">
		<div class="container">
			<div class="row">
				<div class="col l6 s12">
					<img src="', $context['Portal']['github']['user']['avatar_url'] ,'" class="circle left">
					<h5 class="white-text">
						', $context['Portal']['github']['user']['login'] ,'
					</h5>
				</div>
				<div class="col l4 offset-l2 s12">
					<ul>
						<li><a class="grey-text text-lighten-3" href="', $context['Portal']['github']['user']['html_url'] ,'">Github</a></li>
						<li><a class="grey-text text-lighten-3" href="https://twitter.com/missallsuki">Twitter</a></li>
					</ul>
				</div>
			</div>
		</div>
		<div class="footer-copyright">
			<div class="container">
				', theme_copyright() ,' | ', sprintf($txt['page_created_full'], $context['load_time'], $context['load_queries']), '
			</div>
		</div>
	</footer>';
}