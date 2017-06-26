<?php
namespace Qii\Loger;
interface Writer
{
	public function setFileName($fileName);
	public function writeLog($loger);
}