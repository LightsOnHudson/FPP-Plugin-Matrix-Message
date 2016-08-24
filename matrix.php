#!/usr/bin/php
<?
error_reporting(0);
ob_flush();flush();
//TODO:


$pluginName ="MatrixMessage";
$myPid = getmypid();

$DEBUG=false;

$skipJSsettings = 1;
$fppWWWPath = '/opt/fpp/www/';
set_include_path(get_include_path() . PATH_SEPARATOR . $fppWWWPath);

require("common.php");
//include_once("/opt/fpp/www/common.php");
include_once("functions.inc.php");
include_once("MatrixFunctions.inc.php");
include_once("excluded_plugins.inc.php");
require ("lock.helper.php");
define('LOCK_DIR', '/tmp/');
define('LOCK_SUFFIX', $pluginName.'.lock');
$messageQueue_Plugin = "MessageQueue";
$MESSAGE_QUEUE_PLUGIN_ENABLED=false;

$fpp_matrixtools_Plugin = "fpp-matrixtools";
$fpp_matrixtools_Plugin_Script = "scripts/matrixtools";

$logFile = $settings['logDirectory']."/".$pluginName.".log";

$messageQueuePluginPath = $pluginDirectory."/".$messageQueue_Plugin."/";

$messageQueueFile = urldecode(ReadSettingFromFile("MESSAGE_FILE",$messageQueue_Plugin));



if(($pid = lockHelper::lock()) === FALSE) {
	exit(0);

}

$pluginConfigFile = $settings['configDirectory'] . "/plugin." .$pluginName;
if (file_exists($pluginConfigFile))
	$pluginSettings = parse_ini_file($pluginConfigFile);

//$ENABLED = trim(urldecode(ReadSettingFromFile("ENABLED",$pluginName)));
$ENABLED = $pluginSettings['ENABLED'];



if($ENABLED != "ON") {

	logEntry("Plugin Status: DISABLED Please enable in Plugin Setup to use & Restart FPPD Daemon");
	lockHelper::unlock();
	exit(0);

}

$pluginConfigFile = $settings['configDirectory'] . "/plugin." .$pluginName;
if (file_exists($pluginConfigFile))
	$pluginSettings = parse_ini_file($pluginConfigFile);


//$MATRIX_PLUGIN_OPTIONS = urldecode(ReadSettingFromFile("PLUGINS",$pluginName));

$MATRIX_PLUGIN_OPTIONS = $pluginSettings['PLUGINS'];

$MATRIX_FONT= $pluginSettings['FONT'];

$MATRIX_FONT_SIZE= $pluginSettings['FONT_SIZE'];
$COLOR= urldecode($pluginSettings['COLOR']);
$MATRIX_PIXELS_PER_SECOND = $pluginSettings['PIXELS_PER_SECOND'];

$INCLUDE_TIME = urldecode($pluginSettings['INCLUDE_TIME']);
$TIME_FORMAT = urldecode($pluginSettings['TIME_FORMAT']);
$HOUR_FORMAT = urldecode($pluginSettings['HOUR_FORMAT']);

$DEBUG = urldecode($pluginSettings['DEBUG']);

$SEPARATOR = "|";

//$Matrix = urldecode(ReadSettingFromFile("MATRIX",$pluginName));

$Matrix = $pluginSettings['MATRIX'];

if(trim($Matrix == "")) {
	logEntry("No Matrix name is  configured for output: exiting");
	lockHelper::unlock();
	exit(0);
} else {
	logEntry("Configured matrix name: ".$Matrix);
	
}

//$MATRIX_MESSAGE_TIMEOUT = urldecode(ReadSettingFromFile("MESSAGE_TIMEOUT",$pluginName));
$MATRIX_MESSAGE_TIMEOUT = $pluginSettings['MESSAGE_TIMEOUT'];

if($MATRIX_MESSAGE_TIMEOUT == "" || $MATRIX_MESSAGE_TIMEOUT == null) {
	$MESSAGE_TIMEOUT = 10;
	
} else {
	$MESSAGE_TIMEOUT = (int)trim($MATRIX_MESSAGE_TIMEOUT);
}

//echo "message timeout: ".$MESSAGE_TIMEOUT."\n";

//echo "message plugins to export: ".$MATRIX_PLUGIN_OPTIONS."\n";


//echo $messageQueueFile."\n";

if(file_exists($messageQueuePluginPath."functions.inc.php"))
        {
                include $messageQueuePluginPath."functions.inc.php";
                $MESSAGE_QUEUE_PLUGIN_ENABLED=true;

        } else {
                logEntry("Message Queue not installed, cannot use this plugin with out it");
                lockHelper::unlock();
                exit(0);
        }


if($MESSAGE_QUEUE_PLUGIN_ENABLED) {
        $queueMessages = getNewPluginMessages($MATRIX_PLUGIN_OPTIONS);
	$messageCount = count($queueMessages);
        if($messageCount >0 ) {
        //if($queueMessages != null || $queueMessages != "") {
        	
		outputMessages($queueMessages);
        } else {
        	logEntry("No messages file exists??");
        }
        
} else {
        logEntry("MessageQueue plugin is not enabled/installed");
        lockHelper::unlock();
        exit(0);
}
disableMatrixToolOutput();

lockHelper::unlock();

?>
