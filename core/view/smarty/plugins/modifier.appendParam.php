<?php

function smarty_modifier_appendParam($url, $param)
{
	if(stristr($url, '?'))
	{
		$url = $url . '&' .$param;
	}
	else 
	{
		$url = $url . '?' . $param;
	}
	echo $url;
}
?>