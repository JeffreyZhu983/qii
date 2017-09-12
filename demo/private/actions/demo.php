<?php
namespace actions;

use Qii\Base\Action;

class demo extends Action
{
	public function execute()
	{
		print_r(__METHOD__);
	}
}