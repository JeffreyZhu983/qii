<html>
<head>
<title>Welcome to Qii > Loaded Files</title>
<?php
	include(dirname(__FILE__) . DS .  'style.php');
?>
</head>
<body>

<h1><a href="<?=$href;?>">Welcome to Qii!</a> > Loaded files</h1>

<p>The page you are looking at is being generated dynamically by Qii.</p>

<ul>Loaded files as below:
<?php
$fileList = get_included_files();
foreach($fileList AS $file)
{
?>
<li><code><?php echo $file;?></code></li>
<?php
}
?>
</ul>
<?php
	include(dirname(__FILE__) . DS  . 'footer.php');
?>

</body>
</html>