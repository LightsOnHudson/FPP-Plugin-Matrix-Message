<?php
//$DEBUG=true;

include_once "/opt/fpp/www/common.php";
include_once "functions.inc.php";
$pluginName = "MatrixMessage";
$fpp_matrixtools_Plugin = "fpp-matrixtools";
$fpp_matrixtools_Plugin_Script = "scripts/matrixtools";
$FPP_MATRIX_PLUGIN_ENABLED=false;
$logFile = $settings['logDirectory']."/".$pluginName.".log";





if(isset($_POST['submit']))
{
	
	$PLUGINS =  implode(',', $_POST["PLUGINS"]);
//	echo "Writring config fie <br/> \n";
	WriteSettingToFile("PLUGINS",$PLUGINS,$pluginName);
	
	WriteSettingToFile("ENABLED",urlencode($_POST["ENABLED"]),$pluginName);

	
	WriteSettingToFile("LAST_READ",urlencode($_POST["LAST_READ"]),$pluginName);
	WriteSettingToFile("MESSAGE_TIMEOUT",urlencode($_POST["MESSAGE_TIMEOUT"]),$pluginName);
	
	WriteSettingToFile("MATRIX",urlencode($_POST["MATRIX"]),$pluginName);
}

	
	
	
	$PLUGINS = urldecode(ReadSettingFromFile("PLUGINS",$pluginName));
	$ENABLED = urldecode(ReadSettingFromFile("ENABLED",$pluginName));

	$Matrix = urldecode(ReadSettingFromFile("MATRIX",$pluginName));
	$LAST_READ = urldecode(ReadSettingFromFile("LAST_READ",$pluginName));
	
//	echo "Matrix : ".$Matrix."<br/>\n";

	//echo $messageQueueFile."\n";
	
	if(file_exists($pluginDirectory."/".$fpp_matrixtools_Plugin."/".$fpp_matrixtools_Plugin_Script))
	{
		logEntry($pluginDirectory."/".$fpp_matrixtools_Plugin."/".$fpp_matrixtools_Plugin_Script." EXISTS: Enabling");
		$FPP_MATRIX_PLUGIN_ENABLED=true;
	
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



<form method="post" action="http://<? echo $_SERVER['SERVER_NAME']?>/plugin.php?plugin=<?echo $pluginName;?>&page=plugin_setup.php">


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

PrintMatrixList();

function PrintMatrixList()
{
	global $pluginDirectory,$fpp_matrixtools_Plugin,$fpp_matrixtools_Plugin_Script,$Matrix;

	

	$cmdGetMatrixList = $pluginDirectory."/".$fpp_matrixtools_Plugin."/".$fpp_matrixtools_Plugin_Script. " --getblocklist";
	exec($cmdGetMatrixList,$blockOutput);
	//print_r($blockOutput);
	
	
	//$matrixBlocks=array();
	echo "<select name=\"MATRIX\">";
	
	for($i=0;$i<=count($blockOutput)-1;$i++) {
	
		$blockParts = explode(":",$blockOutput[$i]);
		
		//echo "blockPart 0: ".$blockParts[0]. " : ".$blockParts[1]."<br/> \n";
		
		if(trim(strtoupper($blockParts[0]))=="NAME") {
			if(trim($blockParts[1])==$Matrix) {
				echo "<option selected value=\"".trim($Matrix)."\">".trim($Matrix)."</option>\n";
			} else {
				echo "<option value=\"".trim($blockParts[1])."\">".trim($blockParts[1])."</option>\n";
			}
		}
	}	
	
		echo "</select>";
}
echo "<p/>\n";
echo "Include Plugins in Matrix output: \n";
printPluginsInstalled();

?>
<p/>
<input id="submit_button" name="submit" type="submit" class="buttons" value="Save Config">

</form>


<p>To report a bug, please file it against the sms Control plugin project on Git: https://github.com/LightsOnHudson/FPP-Plugin-Matrix

</fieldset>
</div>
<br />
</html>
