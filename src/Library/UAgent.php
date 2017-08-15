<?php
namespace Qii\Library;


_require(Qii_DIR . "/Library/Third/WhichBrowser/Parser.php");

class UAgent extends \WhichBrowser\Parser
{
	public function __construct($headers = null, $options = [])
	{
		parent::__construct($headers, $options);
	}
}