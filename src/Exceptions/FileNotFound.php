<?php
namespace Qii\Exceptions;

class FileNotFound extends Errors
{
	const VERSION = '1.2';

	public function __construct($message, $code, $previous = null)
    {
        $message = self::getRelatePath($_SERVER['SCRIPT_FILENAME'], $message);
        $message = \Qii::i(1405, $message);
        parent::__construct($message, $code, $previous);
    }

    /**
	 * 显示错误
	 *
	 * @param Object $e Exception
	 */
	public static function getError($e)
	{
		
	}
}