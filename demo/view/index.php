<html>
<head>
<title>Welcome to Qii > Index</title>
{#include file='style.php'#}
</head>
<body>

<h1>Welcome to Qii! > 载入的文件列表</h1>

<ul>文件列表:
{#section name=index loop=$file_lists#}
<li><code>
{#$file_lists[index]#}
</code></li>
{#/section#}
</ul>
{#include file='footer.php'#}
</body>
</html>