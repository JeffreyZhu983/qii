<html>
<head>
<title>Welcome to Qii > Check environment</title>
<?php
	include(dirname(__FILE__) . DS .  'style.php');
?>
</head>
<body>

<h1><a href="<?=$href;?>">Welcome to Qii!</a> > Check environment</h1>

<p>The page you are looking at is being generated dynamically by Qii.</p>

<ul>PHP Information as below:
<li><code>System : <?=PHP_OS;?></code></li>
<?php
$zh = (Qii::getPrivate('configure', 'zh_cn'));
$environment = array(
		array('PHP Version', version_compare(PHP_VERSION,"5.3.0",">="), true, 'PHP 5.3.0 or higher is required.'),
		array('Reflection extension', class_exists('Reflection',false), true),
		array('PCRE extension', extension_loaded("pcre"), false),
		array('SPL extension', extension_loaded("SPL"), true),
		array('DOM extension', class_exists("DOMDocument",false), true),
		array('PDO extension', extension_loaded('pdo'), true),
		array('PDO MySQL extension', extension_loaded('pdo_mysql'), true, 'This is required if you are using MySQL database.'),
		array('GD extension', extension_loaded('gd'), true),
		array('Shmop extension', extension_loaded('shmop'), true, 'Shmop'),
		array('Memcache extension', extension_loaded("memcache"), false),
		array('APC extension', extension_loaded("apc"), true),
		array('Mcrypt extension', extension_loaded("mcrypt"), false, 'Required encrypt and decrypt methods.'),
		array('SOAP extension', extension_loaded("soap"), false, 'Required soap extension.'));
foreach($environment AS $v)
{
?>
<li><code>
<?php
echo $v[0] . ($v[1] == 1 ? '<input type="checkbox" checked disabled />' : '<input type="checkbox" disabled />') . ($v[1] != 1 ? "<font color='red'>Not Required " : "<font>"). "" . (isset($v[3]) ? $v[3] : $v[0])."</font>";
?>
</code></li>
<?php
}
?>
</ul>
<?php
	include(dirname(__FILE__) . DS  . 'footer.php');
?>

</body>
</html>