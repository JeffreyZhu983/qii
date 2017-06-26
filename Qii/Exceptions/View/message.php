<html>
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta http-equiv="content-type" content="text/html;charset=utf-8">
    <title><?php echo Qii::i('Show Message'); ?></title>
    <?php
    include(dirname(__FILE__) . DS . 'style.php');
    ?>
</head>
<body>

<h1><font color="red"><?php echo Qii::i('Show Message'); ?></font></h1>

<p><?php echo Qii::i('The page you are looking at is being generated dynamically by Qii'); ?></p>

<ul>Messages:
    <?php
    foreach ($message AS $error) {
        ?>
        <li><code><?= $error; ?></code></li>
        <?php
    }
    ?>
</ul>
<?php
include(dirname(__FILE__) . DS . 'footer.php');
?>

</body>
</html>