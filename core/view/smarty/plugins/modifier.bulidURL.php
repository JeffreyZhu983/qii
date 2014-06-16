<?php

function smarty_modifier_bulidURL($Array, $fileName = '', $extenstion = '', $trimExtension = false)
{
	return Qii::load("Router")->URI($Array, $fileName, $extenstion, $trimExtension);
}
?>