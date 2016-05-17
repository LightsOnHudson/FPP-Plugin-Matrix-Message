<?php
$DEBUG=true;

include_once "/opt/fpp/www/common.php";
include_once "functions.inc.php";
include_once "commonFunctions.inc.php";
include_once "MatrixFunctions.inc.php";

$pluginName = "MatrixMessage";
$fpp_matrixtools_Plugin = "fpp-matrixtools";
$fpp_matrixtools_Plugin_Script = "scripts/matrixtools";
$FPP_MATRIX_PLUGIN_ENABLED=false;
$logFile = $settings['logDirectory']."/".$pluginName.".log";

$pluginUpdateFile = $settings['pluginDirectory']."/".$pluginName."/"."pluginUpdate.inc";


$gitURL = "https://github.com/LightsOnHudson/FPP-Plugin-Matrix-Message.git";

logEntry("plugin update file: ".$pluginUpdateFile);

if(isset($_POST['updatePlugin']))
{
	logEntry("updating plugin...");
	$updateResult = updatePluginFromGitHub($gitURL, $branch="master", $pluginName);

	echo $updateResult."<br/> \n";
}


if(isset($_POST['submit']))
{
	
	$PLUGINS =  implode(',', $_POST["PLUGINS"]);
//	echo "Writring config fie <br/> \n";
	WriteSettingToFile("PLUGINS",$PLUGINS,$pluginName);
	
	WriteSettingToFile("ENABLED",urlencode($_POST["ENABLED"]),$pluginName);
	WriteSettingToFile("FONT",urlencode($_POST["FONT"]),$pluginName);
	WriteSettingToFile("FONT_SIZE",urlencode($_POST["FONT_SIZE"]),$pluginName);
	WriteSettingToFile("PIXELS_PER_SECOND",urlencode($_POST["PIXELS_PER_SECOND"]),$pluginName);
	WriteSettingToFile("COLOR",urlencode($_POST["COLOR"]),$pluginName);

	
	WriteSettingToFile("LAST_READ",urlencode($_POST["LAST_READ"]),$pluginName);
	WriteSettingToFile("MESSAGE_TIMEOUT",urlencode($_POST["MESSAGE_TIMEOUT"]),$pluginName);
	
	WriteSettingToFile("MATRIX",urlencode($_POST["MATRIX"]),$pluginName);
	WriteSettingToFile("INCLUDE_TIME",urlencode($_POST["INCLUDE_TIME"]),$pluginName);
	WriteSettingToFile("TIME_FORMAT",urlencode($_POST["TIME_FORMAT"]),$pluginName);
	WriteSettingToFile("HOUR_FORMAT",urlencode($_POST["HOUR_FORMAT"]),$pluginName);	
}

	
	
	
//	$PLUGINS = urldecode(ReadSettingFromFile("PLUGINS",$pluginName));
$PLUGINS = $pluginSettings['PLUGINS'];
//	$ENABLED = urldecode(ReadSettingFromFile("ENABLED",$pluginName));
$ENABLED = $pluginSettings['ENABLED'];
//	$Matrix = urldecode(ReadSettingFromFile("MATRIX",$pluginName));
$Matrix = $pluginSettings['MATRIX'];
//	$LAST_READ = urldecode(ReadSettingFromFile("LAST_READ",$pluginName));
$LAST_READ = $pluginSettings['LAST_READ'];
$FONT= $pluginSettings['FONT'];
$FONT_SIZE= $pluginSettings['FONT_SIZE'];
$PIXELS_PER_SECOND= $pluginSettings['PIXELS_PER_SECOND'];
$COLOR= urldecode($pluginSettings['COLOR']);

$INCLUDE_TIME = urldecode($pluginSettings['INCLUDE_TIME']);
$TIME_FORMAT = urldecode($pluginSettings['TIME_FORMAT']);
$HOUR_FORMAT = urldecode($pluginSettings['HOUR_FORMAT']);



	
//	echo "Matrix : ".$Matrix."<br/>\n";

	//echo $messageQueueFile."\n";
	
	if(file_exists($pluginDirectory."/".$fpp_matrixtools_Plugin."/".$fpp_matrixtools_Plugin_Script))
	{
		logEntry($pluginDirectory."/".$fpp_matrixtools_Plugin."/".$fpp_matrixtools_Plugin_Script." EXISTS: Enabling");
		$FPP_MATRIX_PLUGIN_ENABLED=true;
		//$cmdGetMatrixList = $pluginDirectory."/".$fpp_matrixtools_Plugin."/".$fpp_matrixtools_Plugin_Script. " --getblocklist";
		//exec($cmdGetMatrixList,$blockOutput);
		createMatrixEventFile();
	} else {
		logEntry("FPP Matrix tools plugin is not installed, cannot use this plugin with out it");
		echo "FPP Matrix Tools plugin is not installed. Install the plugin and revisit this page to continue";
		exit(0);
		//exit(0);
	}
	//echo "sports read: ".$SPORTS."<br/> \n";
//	echo "Loading Matrix panels:<br/> \n";
	//ob_flush();
?>

<html>
<head>
</head>

<div id="<?echo $pluginName;?>" class="settings">
<fieldset>
<legend><?php echo $pluginName;?> Support Instructions</legend>

<p>Known Issues:
<ul>
<li>NONE</li>
</ul>
<p>Configuration:
<ul>
<li>This plugin allows you to use the fpp-matrixtools plugin to output messages from the MessageQueue system</li>
<li>Select your plugins to output to your matrix below and click SAVE</li>
<li>Configure your Matrix first before selecting here</li>
</ul>



<form method="post" action="http://<? echo $_SERVER['SERVER_ADDR'].":".$_SERVER['SERVER_PORT']?>/plugin.php?plugin=<?echo $pluginName;?>&page=plugin_setup.php">


<?
echo "<input type=\"hidden\" name=\"LAST_READ\" value=\"".$LAST_READ."\"> \n";
$restart=0;
$reboot=0;

echo "ENABLE PLUGIN: ";

if($ENABLED== 1 || $ENABLED == "on") {
		echo "<input type=\"checkbox\" checked name=\"ENABLED\"> \n";
//PrintSettingCheckbox("Radio Station", "ENABLED", $restart = 0, $reboot = 0, "ON", "OFF", $pluginName = $pluginName, $callbackName = "");
	} else {
		echo "<input type=\"checkbox\"  name=\"ENABLED\"> \n";
}

echo "<p/> \n";


echo "Matrix Name: ";

PrintMatrixList("MATRIX",$Matrix);


echo "<p/>\n";

echo "Include Time: ";

if($INCLUDE_TIME == 1 || $INCLUDE_TIME == "on") {
	echo "<input type=\"checkbox\" checked name=\"INCLUDE_TIME\"> \n";
	//PrintSettingCheckbox("Radio Station", "ENABLED", $restart = 0, $reboot = 0, "ON", "OFF", $pluginName = $pluginName, $callbackName = "");
} else {
	echo "<input type=\"checkbox\"  name=\"INCLUDE_TIME\"> \n";
}

echo "Time Format: ";
printTimeFormats("TIME_FORMAT",$TIME_FORMAT);


echo "Hour Format: ";
printHourFormats("HOUR_FORMAT",$HOUR_FORMAT);




echo "<p/> \n";
echo "Include Plugins in Matrix output: \n";
printPluginsInstalled();

echo "<p/> \n";

echo "Font:  \n";
printFontsInstalled("FONT",$FONT);

echo "<p/> \n";
echo "Font Size: \n";
printFontSizes("FONT_SIZE",$FONT_SIZE);

echo "<p/> \n";

echo "Pixels per second: \n";
printPixelsPerSecond("PIXELS_PER_SECOND",$PIXELS_PER_SECOND);

echo "<p/> \n";

echo "Color: (#RRGGBB or common name 'red' or for a random color type 'random') \n";

if($COLOR == "") {
	//set a default color
	$COLOR = "yellow";
}
echo "<input type=\"text\" name=\"COLOR\" value=\"".$COLOR."\"> \n";
echo "<p/> \n";
?>
<p/>
<input id="submit_button" name="submit" type="submit" class="buttons" value="Save Config">
<?
 if(file_exists($pluginUpdateFile))
 {
 	//echo "updating plugin included";
	include $pluginUpdateFile;
}
?>
<p>To report a bug, please file it against <?php echo $gitURL;?>
</form>

</fieldset>
</div>
<br />
</html>

