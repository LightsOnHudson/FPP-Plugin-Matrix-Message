#!/usr/bin/php
<?
//error_reporting(0);
ob_flush();flush();
//TODO:
//Oct 31: Installed the ability to send a message directly from a plugin using 'subscribedPlugin' and 'onDemandMessage'


$pluginName ="MatrixMessage";
$MatrixMessageVersion = "2.0";
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
include_once("commonFunctions.inc.php");
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





$pluginConfigFile = $settings['configDirectory'] . "/plugin." .$pluginName;
if (file_exists($pluginConfigFile))
	$pluginSettings = parse_ini_file($pluginConfigFile);

	
	//if it is locked then exit. however; we may need to tell it to keep running in a message queue situation
	//do not run it again - if the matrix is active. //this feature blocks this as well
	//check for other active messages below
	if(($pid = lockHelper::lock()) === FALSE) {
		exit(0);
	
	}
	

//$ENABLED = trim(urldecode(ReadSettingFromFile("ENABLED",$pluginName)));
$ENABLED = $pluginSettings['ENABLED'];



if($ENABLED != "ON") {

	logEntry("Plugin Status: DISABLED Please enable in Plugin Setup to use & Restart FPPD Daemon");
	lockHelper::unlock();
	exit(0);

}

//get the FPP version - needed for the various FPPmm commands
$fpp_version = "v" . exec("git --git-dir=/opt/fpp/.git/ describe --tags", $output, $return_val);
if ( $return_val != 0 )
	$fpp_version = "Unknown";
	unset($output);
	logEntry("FPP version: ".$fpp_version);
	
	//example version is : v1.9-50-gfe8e9a5
	//trim before the first -
	$fpp_version = trim(get_string_between ($fpp_version,"v","-"));
	
	logEntry("FPP version: ".$fpp_version);


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

$Matrix = urldecode($pluginSettings['MATRIX']);
$overlayMode = urldecode($pluginSettings['OVERLAY_MODE']);

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

if(isset($_GET['subscribedPlugin'])) {
    $subscribedPlugin = $_GET['subscribedPlugin'];
    logEntry("Only getting plugin messages for plugin: ".$subscribedPlugin);
    $MATRIX_PLUGIN_OPTIONS = $subscribedPlugin;
}

if(isset($_GET['onDemandMessage'])) {
	$onDemandMessage = $_GET['onDemandMessage'];
	logEntry("Receiving an onDemandMessage from subscribed plugin: ".$subscribedPlugin);
	$MATRIX_PLUGIN_OPTIONS = $subscribedPlugin;
}

        
$MATRIX_ACTIVE = false;
        
//TODO: Change this to get pluguin messages from their resprective datbases. once this is done, then can just get new plugin messages that way!

if($MESSAGE_QUEUE_PLUGIN_ENABLED) {
	if($onDemandMessage != "") {
		//got an ondemand message, and we may get more and more of these so we should output them all
		
	
		$queueMessages = array(time()."|".$onDemandMessage."|".$subscribedPlugin);
		if($DEBUG) {
			logEntry("MATRIX MESSAGE: On Demand message mode: ");
			//logEntry("Message 0: ".$queueMessages[0]);
		}
		
	} else {
        $queueMessages = getNewPluginMessages($MATRIX_PLUGIN_OPTIONS);
		
	}
	
	$messageCount = count($queueMessages);
	
        if($messageCount >0 ) {
        //if($queueMessages != null || $queueMessages != "") {
        $MATRIX_ACTIVE = true;
        $queueCount =0;
        
        $LOOP_COUNT =0;
        	do {
        	//	$queueCount =0;
        		logEntry("MATRIX MESSAGE: LOOP COUNT: ".$LOOP_COUNT++);
        		logEntry("MATRIX MESSAGE: QUEUE COUNT: ".$queueCount,0,__FILE__,__LINE__);// $sourceFile, $sourceLine)
        		if($queueCount >0) {
        			foreach ($queueMessages as $tmpMSG) {
        				logEntry("MATRIX MESSAGE: LOOP ID: ".$LOOP_COUNT." MSG: ".$tmpMSG,0,__FILE__,__LINE__);
        			}
        		}
        		//extract the high water mark from the first message and write that back to the plugin! or
        		//gets the same message twice in a flood of incomming on demand messages
        		
        		// Jan 3:$messageQueueParts = explode("|",$queueMessages[0]);
        	//	logEntry("MATRIX plugin: Writing high water for plugin:".$MATRIX_PLUGIN_OPTIONS." ".urldecode($messageQueueParts[0]));
        	////	WriteSettingToFile("LAST_READ",urldecode($messageQueueParts[0]),$MATRIX_PLUGIN_OPTIONS);
        		
        		//echo "0: ".$messageParts[0]."\n";
        		logEntry("-----------------------------------");
        		
				outputMessages($queueMessages);
			
				if($DEBUG) 
					logEntry("MATRIX PLUGIN OPTIONS[0] = ".$MATRIX_PLUGIN_OPTIONS);
				if((strtoupper($MATRIX_PLUGIN_OPTIONS) != "CFOLNANOMATRIXSYSTEM") && $onDemandMessage != "") {
					if($DEBUG) {
						logEntry("MATRIX MESSAGE: On demand mode, querying for new plugin messages");
					}
					//get new messages
					$queueMessages = null;
					
					$queueMessages = getNewPluginMessages($MATRIX_PLUGIN_OPTIONS);
					$queueCount = count($queueMessages);
					logEntry("Matrix Message NEW QUEUE COUNT: ".$queueCount);
					
					sleep(1);
					
					

				}
        	} while ($queueCount > 0) ;
        
        	disableMatrixToolOutput();
        	
        } else {
        	logEntry("MATRIX MESSAGE: No messages  exists??");
        }
        
} else {
        logEntry("MessageQueue plugin is not enabled/installed");
        lockHelper::unlock();
        exit(0);
}
//disableMatrixToolOutput();

lockHelper::unlock();

?>
