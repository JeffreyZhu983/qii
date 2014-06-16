<?php
if($showHeader)
{
?>
<html>
<head>
<title>Welcome to Qii > Print Message</title>
<?php 
}
?>
<?php
	include(dirname(__FILE__) . DS .  'style.php');
?>
<?php 
if($showHeader)
{
?>
<style type="text/css">
body{text-align:left;}
</style>
</head>
<body>
<h1><a href="<?php echo $href;?>">Welcome to Qii!</a> > Print Message</h1>

<p>The page you are looking at is being generated dynamically by Qii.</p>

<ul><p>Print Message:</p>
<?php 
}
else
{
	echo "<ul>";
}
?>
<?php
foreach($messageArray AS $key => $message)
{
?>
<li><?php
	if(is_string($key))
	{
	?>
	<?php echo $key;?>
	<?php
		}
		?>
	<code><?php if(!is_array($message)){echo $message;}else{Qii::dump($message);};?></code>
</li>
<?php
}
?>
<li><code><a href="javascript:;" onclick="history.go(-1);">返回上一页</a></code></li>
</ul>
<?php 
if($showHeader)
{
include(dirname(__FILE__) . DS  . 'footer.php');
?>
</body>
</html>
<?php 
}
?>