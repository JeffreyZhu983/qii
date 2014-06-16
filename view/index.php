<html>
<head>
<title>Welcome to Qii</title>
<?php
	include(dirname(__FILE__) . DS .  'style.php');
?>
</head>
<body>

<h1>Welcome to Qii! </h1>

<p>The page you are looking at is being generated dynamically by Qii.</p>

<ul>系统信息列表:
<?php
$messageArray = Qii::getPrivate('global', 'System');
foreach($messageArray AS $error)
{
?>
<li><code><?php echo $error;?></code></li>
<?php
}
?>
</ul>
<?php
	include(dirname(__FILE__) . DS  . 'footer.php');
?>

</body>
</html>