<?php

/**
 * @package Portal mod
 * @version 1.0
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2014, Jessica González
 * @license http://www.mozilla.org/MPL/2.0/
 */

function template_portal_above(){}

function template_portal_main(){}

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
}
