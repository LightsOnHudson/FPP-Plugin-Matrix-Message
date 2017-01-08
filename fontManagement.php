<?php
//$DEBUG=true;

include_once "/opt/fpp/www/common.php";
include_once 'functions.inc.php';
include_once 'commonFunctions.inc.php';
$pluginName = "MatrixMessage";

$messageQueue_Plugin = "MessageQueue";
$MESSAGE_QUEUE_PLUGIN_ENABLED=false;


$logFile = $settings['logDirectory']."/".$pluginName.".log";

if(isset($_POST['updateFonts']))
{
	logEntry("updating fonts...");
	
	$updateFontCMD = "/usr/bin/sudo /bin/mv /home/fpp/media/upload/*.ttf /usr/local/share/fonts";
	exec ($updateFontCMD,$updateOutput);
	
	echo "Install return command: ".$updateOutput."\n";
	
	}




echo "This Page manages the installation of additional fonts to be used by the Matrix Message or other tools that uses fonts real time\n";

echo "<p/> \n";

echo "To install new fonts: \n";
echo "<p/> \n";
echo "<ul> \n";
echo "<li> Upload the TTF file using the File Manager \n";
echo "<li> The files will go to the 'Uploads' tab of the File Manager \n";
echo "<li> Revisit this page and press the 'INSTALL FONTS' button below \n";
echo "<li> This will MOVE the fonts you uploaded to the proper location \n";
echo "<li> You're done \n";
echo "</ul> \n";

?>
<form method="post" action="http://<? echo $_SERVER['SERVER_ADDR'].":".$_SERVER['SERVER_PORT']?>/plugin.php?plugin=<?echo $pluginName;?>&page=fontManagement.php">

<input type="submit" value="Update Fonts" name="updateFonts" class="buttons">

</form>

