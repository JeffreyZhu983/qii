<?php
namespace Qii\Exceptions;

class Errors extends \Exception
{
	const VERSION = '1.2';

	/**
	 * 获取两个文件的相对路径
	 * @param String $cur
	 * @param String $absp
	 * @return String
	 */
	public static function getRelatePath($cur, $absp)
	{
		$cur = str_replace('\\', '/', $cur);
		$absp = str_replace('\\', '/', $absp);
		$sabsp = explode('/', $absp);
		$scur = explode('/', $cur);
		$la = count($sabsp) - 1;
		$lb = count($scur) - 1;
		$l = max($la, $lb);

		for ($i = 0; $i <= $l; $i++) {
			if ($sabsp[$i] != $scur[$i])
				break;
		}
		$k = $i - 1;
		$path = "";
		for ($i = 1; $i <= ($lb - $k - 1); $i++)
			$path .= "../";
		for ($i = $k + 1; $i <= ($la - 1); $i++)
			$path .= $sabsp[$i] . "/";
		$path .= $sabsp[$la];
		return $path;
	}

	/**
	 * 显示错误
	 *
	 * @param Object $e Exception
	 */
	public static function getError($e)
	{
		$message = array();
		if (isset($_GET['isAjax']) && $_GET['isAjax'] == 1) {
			echo json_encode(array('code' => $e->getCode(), 'msg' => strip_tags($e->getMessage())), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
			return;
		}
		$message[] = \Qii::i('Error file', self::getRelatePath($_SERVER['SCRIPT_FILENAME'], $e->getFile()));
		$message[] = \Qii::i('Error code', $e->getCode());
		$message[] = \Qii::i('Error description', $e->getMessage());
		$message[] = \Qii::i('Error line', $e->getLine() . ' on ' . self::getLineMessage($e->getFile(), $e->getLine()));
		$traceString = \Qii::i('Trace as below') . '<br />';
		$traces = explode("\n", $e->getTraceAsString());
		foreach ($traces AS $trance) {
			$traceString .= "&nbsp;&nbsp;&nbsp;&nbsp;" . $trance . '<br />';
		}
		$message[] = $traceString;
		if (\Qii::getInstance()->logerWriter != null) {
			$message[] = 'Source URL:' . \Qii::getInstance()->request->url->getCurrentURL();
			$message[] = 'Referer URL:' . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : \Qii::getInstance()->request->url->getCurrentURL());
			\Qii::getInstance()->logerWriter->writeLog($message);
		}
		$appConfigure = \Qii\Config\Register::getConfig();

		$env = \Qii\Config\Register::get(\Qii\Consts\Config::APP_ENVIRON, 'dev');
		if ($env == 'product' || ($appConfigure['errorPage'] && (isset($appConfigure['debug']) && $appConfigure['debug'] == 0))) {
			list($controller, $action) = explode(':', $appConfigure['errorPage']);
			$controllerCls = \Qii\Config\Register::get(\Qii\Consts\Config::APP_DEFAULT_CONTROLLER_PREFIX) . '_' . $controller;
			$action = preg_replace('/(Action)$/i', "", $action);
			$filePath = \Qii\Autoloader\Import::requireByClass($controllerCls);
			if (!is_file($filePath)) {
				if ($env == 'product') return '';
				\Qii\Autoloader\Import::requires(Qii_DIR . DS . 'Exceptions' . DS . 'Error.php');
				call_user_func_array(array('\Qii\Exceptions\Error', 'index'), array($controller, $action));
				die();
			} else {
				\Qii::getInstance()->request->setControllerName($controller);
				\Qii::getInstance()->request->setActionName($action);
				\Qii::getInstance()->dispatcher->setRequest(\Qii::getInstance()->request);
				\Qii::getInstance()->dispatcher->dispatch($controller, $action, $e);
				die();
			}
		}
		include(join(DS, array(Qii_DIR, 'Exceptions', 'View', 'error.php')));
	}

	/**
	 * 获取指定文件的指定行内容
	 *
	 * @param String $fileName 文件名
	 * @param Int $line 行号
	 * @return String
	 */
	public static function getLineMessage($fileName, $line)
	{
		$seekline = max(0, $line - 1);
		$spl = new \SplFileObject($fileName);
		$code = array();
		if ($line > 1) {
			$maxLine = 10;
			$firstLine = max(0, $seekline - $maxLine);

			$spl->seek($firstLine);
			$min = $seekline - $maxLine;
			$max = $seekline + $maxLine + 1;

			for ($i = $min; $i < $max; $i++) {
				$currentLine = $i + ($min < 0 ? abs($min) : 0) + 1;
				$color = $currentLine == $line ? ' color="red"' : '';
				if ($spl->eof()) break;
				$code[] = '<font ' . $color . '>' . $currentLine . ':</font>' . "\t" . '<font ' . $color . '>' . htmlspecialchars(rtrim($spl->current())) . '</font>';
				$spl->next();
			}
		} else {
			$spl->seek($seekline);
			$code[] = htmlspecialchars(rtrim($spl->current()));
		}
		return '<pre style="font-weight:bold;">' . join("<br />", $code) . '</pre>';
	}

	/**
	 * sprintf 格式化语言错误信息内容
	 *
	 *
	 * Qii::e($message, $argv1, $argv2, ..., $line);
	 * $message = sprintf($message, $argv1, $argv2, ...);
	 * throw new \Qii_Exceptions_Errors($message, $line);
	 */
	public static function e()
	{
		$argvs = func_get_args();
		$count = count($argvs);
		$message = array_shift($argvs);
		$line = (int) array_pop($argvs);
		if ($count == 2) {
			throw new \Qii\Exceptions\Errors($message, $line);
		}
		$message = vsprintf($message, $argvs);
		throw new \Qii\Exceptions\Errors($message, $line);
	}
}