<?php

/**
 * @package Portal mod
 * @version 1.0
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2014, Jessica González
 * @license http://www.mozilla.org/MPL/2.0/
 */


function template_ads_main(){}

function template_ads_above()
{
	global $context;

	if ($context['user']['is_logged'] || isset($_REQUEST['xml']))
		return;

	echo '
	<div class="roundframe row">
	<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- responsive -->
<ins class="adsbygoogle"
     style="display:block"
     data-ad-client="ca-pub-9370289436233241"
     data-ad-slot="8600718495"
     data-ad-format="auto"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
</div>';
}

function template_ads_below()
{
	global $context;

	if ($context['user']['is_logged'] || isset($_REQUEST['xml']))
		return;

	echo '
	<div class="roundframe row">
	<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- responsive -->
<ins class="adsbygoogle"
     style="display:block"
     data-ad-client="ca-pub-9370289436233241"
     data-ad-slot="8600718495"
     data-ad-format="auto"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
</div>';
}
