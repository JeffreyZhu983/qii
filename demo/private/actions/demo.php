<?php
namespace actions;

use Qii\Action_Abstract;

class demo extends Action_Abstract
{
	public function execute()
	{
		print_r(__METHOD__);
	}
}