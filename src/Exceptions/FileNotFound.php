<?php
namespace Qii\Exceptions;

class FileNotFound extends Errors
{
	const VERSION = '1.2';

	public function __construct($message, $code, $previous = null)
    {
        if($code == 404) {
            $documentRoot = str_replace(array('/', '\\'), array(DS, DS), $_SERVER['DOCUMENT_ROOT']);
            $message = str_replace($documentRoot, '', $message);
        }
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