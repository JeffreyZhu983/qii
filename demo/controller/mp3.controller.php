<?php
require(dirname(dirname(__FILE__)) .'/AudioExif.php');
class mp3_controller extends Controller 
{
	public function index()
	{
		$AE = new AudioExif();
		print_r($AE->GetInfo(dirname(dirname(__FILE__)) . '/3.mp3'));
	}
}
?>