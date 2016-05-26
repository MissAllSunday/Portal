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
	global $context, $txt;


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
							<div class="col s8 info right-align">
								', $n['time'] ,'<i class="tiny material-icons valign">query_builder</i><br>
								', $n['comment_link'] ,'<i class="tiny material-icons ">chat_bubble_outline</i><br>';

		if (!empty($n['likes']))
			echo '
								', $n['likes']['count'] ,'<i class="tiny material-icons valign">thumb_up</i><br>';

		echo '
							</div>
							<div class="col s4">
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
			<div class="col s12 l3 " id="sidebar">
				<div class="row">
				', var_dump($context['Portal']['github']) ,'
				</div>
			</div>
		</div>
	</div>';

	// Show the XHTML, RSS and WAP2 links, as well as the copyright.
	// Footer is now full-width by default.
	echo '
	<footer class="page-footer">
		<div class="footer-copyright">';

	// There is now a global "Go to top" link at the right.
		echo '
			<div class="container">
			', theme_copyright();

	// Show the load time?
	if ($context['show_load_time'])
		echo '
		<br>', sprintf($txt['page_created_full'], $context['load_time'], $context['load_queries']), '';

	echo '
			</div>
		</div>
	</footer>';
}