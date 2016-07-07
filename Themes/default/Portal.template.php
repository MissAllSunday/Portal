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
				<div itemscope="" itemtype="http://schema.org/BlogPosting" class="row blog_post">
					<h4 class="catbg row"><span itemprop="name headline">', $n['link'] ,'</span></h4>
					<div class="row ">
						<div class="flow-text col s12">
							<span itemprop="articleBody">', $n['body'] ,'</span>
							<p>
							<div class="divider"></div>
						</div>
						<div class="row"></div>
						<div class="row">
							<div class="col s6 m10 info right-align">
								<span itemprop="datePublished dateModified" content="', date('c', $n['timestamp']) ,'">', $n['time'] ,'</span> <i class="tiny material-icons valign b_icon_time">query_builder</i><br>
								', $n['comment_link'] ,' <i class="tiny material-icons b_icon_chat">chat_bubble_outline</i><br>
								<span itemprop="author" itemscope itemtype="https://schema.org/Person">
								<span itemprop="name"><a itemprop="url" rel="author" href="', $n['poster']['href'] ,'">', $n['poster']['name'] ,'</a></span></span> <i class="tiny material-icons  b_icon_face">face</i>';

		echo '
							</div>
							<div class="col s6 m2" property="image" typeof="ImageObject">
								<link property="url" href="', $n['poster']['avatar']['href'] ,'" />
								<meta property="height" content="50" />
								<meta property="width" content="50" />
									<img property="image" src="', $n['poster']['avatar']['href'] ,'" class="avatar">
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
