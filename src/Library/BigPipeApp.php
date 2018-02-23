<?php
/**
 * Big pipe
 */
namespace Qii\Library;

use BigPipe;

class BigPipeApp {
	public static $debugging = FALSE;

    /**
     * Create Pagelet instance
     *
     * @param string $ID id标识
     * @return BigPipe\Pagelet
     */
	public static function createPagelet($ID = NULL): BigPipe\Pagelet {
		$namespace = self::$debugging ? 'Debugging' : 'BigPipe';
		$classname = "{$namespace}\Pagelet";

		$Pagelet = new $classname(...func_get_args());
		return $Pagelet;
	}

    /**
     * Create Stylesheet instance
     *
     * @param string $ID id标识
     * @param $href
     * @return BigPipe\Resource\Stylesheet
     */
	public static function createStylesheet($ID, $href): BigPipe\Resource\Stylesheet {
		$namespace = self::$debugging ? 'Debugging' : 'BigPipe';
		$classname = "{$namespace}\Resource\Stylesheet";

		$Stylesheet = new $classname(...func_get_args());
		return $Stylesheet;
	}

    /**
     * Create Javascript instance
     *
     * @param string $ID id标识
     * @param $href
     * @return BigPipe\Resource\Javascript
     */
	public static function createJavascript($ID, $href): BigPipe\Resource\Javascript {
		$namespace = self::$debugging ? 'Debugging' : 'BigPipe';
		$classname = "{$namespace}\Resource\Javascript";

		$Javascript = new $classname(...func_get_args());
		return $Javascript;
	}

    /**
     * 渲染
     */
	public static function render() {
        BigPipe\BigPipe::completeResponse();
    }
}