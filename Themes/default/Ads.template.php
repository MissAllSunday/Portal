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
	echo '
	<div class="roundframe" id="info_center" style="margin:auto; text-align:center;">
	<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- missallsundayAbove -->
<ins class="adsbygoogle"
     style="display:inline-block;width:728px;height:90px"
     data-ad-client="ca-pub-9370289436233241"
     data-ad-slot="5073688098"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
</div>';
}

function template_ads_below()
{
	echo '
	<div class="roundframe" id="info_center" style="margin:auto; text-align:center;">
	<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- missallsundayAbove -->
<ins class="adsbygoogle"
     style="display:inline-block;width:728px;height:90px"
     data-ad-client="ca-pub-9370289436233241"
     data-ad-slot="5073688098"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
</div>';
}
