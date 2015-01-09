<?php

$pages = Array(
	'name' => "MatrixMessage",
	'type' => "output",
	'page' => "plugin_setup.php");

?>

<li><a href="<?php echo preg_replace('/.*\/plugins\/(.*)/', 'plugin.php?plugin=$1&page=plugin_setup.php', dirname(__FILE__)); ?>"><?php echo $pages['name'];?></a></li
>
