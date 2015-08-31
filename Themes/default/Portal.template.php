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

	echo '
	<div class="portal mdl-layout mdl-js-layout has-drawer is-upgraded">
		<header class="mdl-layout__header mdl-layout__header--transparent">
			<div class="mdl-layout__header-row">
				<span class="mdl-layout-title">', $context['page_title'] ,'</span>
				<div class="mdl-layout-spacer"></div>
				<nav class="mdl-navigation">
					<a class="mdl-navigation__link" href="">Forum</a>
				</nav>
			</div>
		</header>
		<div class="mdl-layout__drawer">
			', $context['user']['avatar']['image'] ,'
			<div class="main-menu-avatar-dropdown">
				<span>', $context['user']['name'] ,'</span>
				<div class="mdl-layout-spacer"></div>
				<button id="accbtn" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--icon" data-url="' . $scripturl . '?action=profile;area=popup">
					<i class="material-icons" role="presentation">arrow_drop_down</i>
					<span class="visuallyhidden">Accounts</span>
				</button>
				<ul class="mdl-menu mdl-menu--bottom-right mdl-js-menu mdl-js-ripple-effect accbtn_holder" for="accbtn">
				</ul>
			</div>
			<nav class="mdl-navigation">';

	// Note: Menu markup has been cleaned up to remove unnecessary spans and classes.
	foreach ($context['menu_buttons'] as $act => $button)
	{
		echo '
				<div class="mdl-navigation__link', $button['active_button'] ? ' active' : '', '">
					<a  href="', $button['href'], '"', isset($button['target']) ? ' target="' . $button['target'] . '"' : '', '>
						', $button['title'], '
					</a>
					', (!empty($button['sub_buttons']) ? '<i class="material-icons sub_menu_button" data-for="sub_menu_list_'. $act .'">keyboard_arrow_down</i>' : '') ,'
				</div>';

		if (!empty($button['sub_buttons']))
		{
			echo '
				<nav class="mdl-navigation sub_menu_list" id="sub_menu_list_', $act ,'">';

			foreach ($button['sub_buttons'] as $childbutton)
			{
				echo '

					<a class="mdl-navigation__link sub_menu" href="', $childbutton['href'], '"' , isset($childbutton['target']) ? ' target="' . $childbutton['target'] . '"' : '', '>
										', $childbutton['title'], '
									</a>';
			}
				echo '
				</nav>';
		}
	}

	echo '
		</nav>
	</div>';
}

function template_portal_main()
{
	global $context;

	echo '
		<main class="mdl-layout__content">
			<div class="portal__posts mdl-grid">
				<div class="mdl-card coffee-pic mdl-cell mdl-cell--8-col">
					<div class="mdl-card__media mdl-color-text--grey-50">
						<h3><a href="entry.html">Coffee Pic</a></h3>
					</div>
					<div class="mdl-card__supporting-text meta mdl-color-text--grey-600">
						<div class="minilogo"></div>
						<div>
							<strong>The Newist</strong>
							<span>2 days ago</span>
						</div>
					</div>
				</div>
				<div class="mdl-card something-else mdl-cell mdl-cell--8-col mdl-cell--4-col-desktop">
					<button class="mdl-button mdl-js-ripple-effect mdl-js-button mdl-button--fab mdl-color--accent">
						<i class="material-icons mdl-color-text--white" role="presentation">add</i>
						<span class="visuallyhidden">add</span>
					</button>
					<div class="mdl-card__media mdl-color--white mdl-color-text--grey-600">
						<img src="images/logo.png">
						+1,337
					</div>
					<div class="mdl-card__supporting-text meta meta--fill mdl-color-text--grey-600">
						<div>
							<strong>The Newist</strong>
						</div>
						<ul class="mdl-menu mdl-js-menu mdl-menu--bottom-right" for="menubtn">
							<li class="mdl-menu__item mdl-js-ripple-effect">About</li>
							<li class="mdl-menu__item mdl-js-ripple-effect">Message</li>
							<li class="mdl-menu__item mdl-js-ripple-effect">Favorite</li>
							<li class="mdl-menu__item mdl-js-ripple-effect">Search</li>
						</ul>
						<button id="menubtn" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--icon">
							<i class="material-icons" role="presentation">more_vert</i>
							<span class="visuallyhidden">show menu</span>
						</button>
					</div>
				</div>
				<div class="mdl-card on-the-road-again mdl-cell mdl-cell--12-col">
					<div class="mdl-card__media mdl-color-text--grey-50">
						<h3><a href="entry.html">On the road again</a></h3>
					</div>
					<div class="mdl-color-text--grey-600 mdl-card__supporting-text">
						Enim labore aliqua consequat ut quis ad occaecat aliquip incididunt. Sunt nulla eu enim irure enim nostrud aliqua consectetur ad consectetur sunt ullamco officia. Ex officia laborum et consequat duis.
					</div>
					<div class="mdl-card__supporting-text meta mdl-color-text--grey-600">
						<div class="minilogo"></div>
						<div>
							<strong>The Newist</strong>
							<span>2 days ago</span>
						</div>
					</div>
				</div>
				<div class="mdl-card amazing mdl-cell mdl-cell--12-col">
					<div class="mdl-card__title mdl-color-text--grey-50">
						<h3 class="quote"><a href="entry.html">I couldn’t take any pictures but this was an amazing thing…</a></h3>
					</div>
					<div class="mdl-card__supporting-text mdl-color-text--grey-600">
						Enim labore aliqua consequat ut quis ad occaecat aliquip incididunt. Sunt nulla eu enim irure enim nostrud aliqua consectetur ad consectetur sunt ullamco officia. Ex officia laborum et consequat duis.
					</div>
					<div class="mdl-card__supporting-text meta mdl-color-text--grey-600">
						<div class="minilogo"></div>
						<div>
							<strong>The Newist</strong>
							<span>2 days ago</span>
						</div>
					</div>
				</div>
				<div class="mdl-card shopping mdl-cell mdl-cell--12-col">
					<div class="mdl-card__media mdl-color-text--grey-50">
						<h3><a href="entry.html">Shopping</a></h3>
					</div>
					<div class="mdl-card__supporting-text mdl-color-text--grey-600">
						Enim labore aliqua consequat ut quis ad occaecat aliquip incididunt. Sunt nulla eu enim irure enim nostrud aliqua consectetur ad consectetur sunt ullamco officia. Ex officia laborum et consequat duis.
					</div>
					<div class="mdl-card__supporting-text meta mdl-color-text--grey-600">
						<div class="minilogo"></div>
						<div>
							<strong>The Newist</strong>
							<span>2 days ago</span>
						</div>
					</div>
				</div>
				<nav class="demo-nav mdl-cell mdl-cell--12-col">
					<div class="section-spacer"></div>
					<a href="entry.html" class="demo-nav__button" title="show more">
						More
						<button class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--icon">
							<i class="material-icons" role="presentation">arrow_forward</i>
						</button>
					</a>
				</nav>
			</div>
			<footer class="mdl-mini-footer">
				<div class="mdl-mini-footer--left-section">
					<button class="mdl-mini-footer--social-btn social-btn social-btn__twitter">
						<span class="visuallyhidden">Twitter</span>
					</button>
					<button class="mdl-mini-footer--social-btn social-btn social-btn__blogger">
						<span class="visuallyhidden">Facebook</span>
					</button>
					<button class="mdl-mini-footer--social-btn social-btn social-btn__gplus">
						<span class="visuallyhidden">Google Plus</span>
					</button>
				</div>
				<div class="mdl-mini-footer--right-section">
					<button class="mdl-mini-footer--social-btn social-btn__share">
						<i class="material-icons" role="presentation">share</i>
						<span class="visuallyhidden">share</span>
					</button>
				</div>
			</footer>
		</main>';
}

function template_portal_below()
{
	echo '
		<div class="mdl-layout__obfuscator"></div>
	</div>';
}