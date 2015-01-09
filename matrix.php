#!/usr/bin/php
<?
error_reporting(0);

//TODO:


$pluginName ="MatrixMessage";
$myPid = getmypid();

$DEBUG=false;

$skipJSsettings = 1;
include_once("/opt/fpp/www/config.php");
include_once("/opt/fpp/www/common.php");
include_once("functions.inc.php");
include_once("excluded_plugins.inc.php");
require ("lock.helper.php");
define('LOCK_DIR', '/tmp/');
define('LOCK_SUFFIX', '.lock');
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
$ENABLED = trim(urldecode(ReadSettingFromFile("ENABLED",$pluginName)));



if($ENABLED != "on" && $ENABLED != "1") {
	logEntry("Plugin Status: DISABLED Please enable in Plugin Setup to use & Restart FPPD Daemon");
	lockHelper::unlock();
	exit(0);

}


$MATRIX_PLUGIN_OPTIONS = urldecode(ReadSettingFromFile("PLUGINS",$pluginName));
$MATRIX_FONT= "fixed";
$MATRIX_FONT_SIZE= 12;
$MATRIX_PIXELS_PER_SECOND = 20;


$Matrix = urldecode(ReadSettingFromFile("MATRIX",$pluginName));

if(trim($Matrix == "")) {
	logEntry("No Matrix name is  configured for output: exiting");
	lockHelper::unlock();
	exit(0);
} else {
	logEntry("Configured matrix name: ".$Matrix);
	
}
$MATRIX_MESSAGE_TIMEOUT = urldecode(ReadSettingFromFile("MESSAGE_TIMEOUT",$pluginName));


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
        if($queueMessages != null || $queueMessages != "") {
        	
        //print_r($queueMessages);
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
