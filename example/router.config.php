<?php
/**
* index.control中的对应方法转发到test.control.php中的对应方法
* 路由的使用方法：
* *:* => *:* 不路由
* *:* => *:yyy 所有control和action都转发到 *->yyy
* *:* => yy:* 所有转发到xxx->*, 这里的*，前边对应的是什么，后边就对应转发到什么，比如: *:xxx => yy:xxx
* xx:* => yy:* xx中对应的方法转发到yy对应的方法，比如: xx:xxx => yy:xxx
* xx:* => yy:yyy xxx Controller的所有方法都转发到 yy->yyy
* *:xx => yy:yyy 所有Controller的xx方法转发到 yy->yyy
*/
return array(
			 'index:*' => 'test:*'
);
?>