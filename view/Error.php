<html>
<head>
<title>Welcome to Qii > Throw Error</title>
<?php
	include(dirname(__FILE__) . DS .  'style.php');
?>
</head>
<body>

<h1>Welcome to Qii! > <font color="red">Throw Error</font></h1>

<p>The page you are looking at is being generated dynamically by Qii.</p>

<ul>Error informtions:
<?php
//$messageArray = Qii::getPrivate('error');
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