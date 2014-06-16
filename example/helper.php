<?php
/**
 * Qii test unit
 * 
 * @author Jinhui.zhu	<jinhui.zhu@live.cn>
 * @version  $Id: Qii.php,v 1.1 2010/04/23 06:02:12 Jinhui.Zhu Exp $
 */
require('../Qii.php');
Qii::setCachePath('tmp');
Qii::setXpath('configure/site.xml', 'helper');
Qii::setDB('configure/db.config.php');
Qii::setRouter('configure/router.config.php');
Qii::dispatch();
?>