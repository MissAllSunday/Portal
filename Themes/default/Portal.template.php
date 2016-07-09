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

	echo '
			<div itemscope itemtype="http://schema.org/Blog">
				<link itemprop="mainEntityOfPage" href="'. $context['canonical_url'] .'" />';

	$i = 0;
	foreach ($context['Portal']['news'] as $n)
	{
		echo '
				<div itemprop="mainEntity" itemscope="" itemtype="http://schema.org/BlogPosting" class="row blog_post">
					<link itemprop="mainEntityOfPage" href="'. $context['canonical_url'] .'" />
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
								<meta itemprop="image" content="', $n['poster']['avatar']['href'] ,'">
								<a itemprop="url" rel="author" href="', $n['poster']['href'] ,'"><span itemprop="name">', $n['poster']['name'] ,'</span></a></span> <i class="tiny material-icons  b_icon_face">face</i>';

		echo '
							</div>
							<div class="col s6 m2">
									<img  src="', $n['poster']['avatar']['href'] ,'" class="avatar">
								</a>
							</div>
						</div>
					</div>
				</div>';

		$i++;
	}

	echo '
			</div>
				<div class="row">
					', $context['Portal']['pagination'] ,'
				</div>';
}
