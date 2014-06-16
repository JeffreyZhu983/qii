<?php
class error_controller extends Controller 
{
	public function __construct()
	{
		parent::__construct();
	}
	public function index()
	{
		Qii::showMessage("This page does not exist.");
	}
}
?>