<?php
/**
 * View Intferface
 */
namespace Qii\View;

interface Intf
{
	public function assign($spec, $value = null);

	public function fetch($tpl);

	public function display($tpl);
}