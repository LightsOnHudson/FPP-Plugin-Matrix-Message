<?php

function createMatrixEventFile() {

	global $eventDirectory,$pluginDirectory,$pluginName,$scriptDirectory;



	//echo "next event file name available: ".$nextEventFilename."\n";

	$EVENT_KEY = "RUN-MATRIX";

	//check to see that the file doesnt already exist - do a grep and return contents
	$EVENT_CHECK = checkEventFilesForKey($EVENT_KEY);
	if(!$EVENT_CHECK)
	{
			
		$nextEventFilename = getNextEventFilename();
		$MAJOR=substr($nextEventFilename,0,2);
		$MINOR=substr($nextEventFilename,3,2);
		$eventData  ="";
		$eventData  = "majorID=".(int)$MAJOR."\n";
		$eventData .= "minorID=".(int)$MINOR."\n";
		$eventData .= "name='PROJECTOR-".$key."'\n";
		$eventData .= "effect=''\n";
		$eventData .= "startChannel=\n";
		$eventData .= "script='$EVENT_KEY'.sh'\n";

		//	echo "eventData: ".$eventData."<br/>\n";
		file_put_contents($eventDirectory."/".$nextEventFilename, $eventData);

		$scriptCMD = $pluginDirectory."/".$pluginName."/"."matrix.php";
		createScriptFile($EVENT_KEY.".sh",$scriptCMD);
	}


	//echo "$key => $val\n";
		





}
function outputMessages($queueMessages) {

	global $pluginDirectory,$MESSAGE_TIMEOUT, $fpp_matrixtools_Plugin, $fpp_matrixtools_Plugin_Script,$Matrix,$MATRIX_FONT,$MATRIX_FONT_SIZE,$MATRIX_PIXELS_PER_SECOND;

	//print_r($queueMessages);

	if(count($queueMessages) <=0) {
		//	echo "No messages to output \n";
		return;
	}

	enableMatrixToolOutput();

	for($i=0;$i<=count($queueMessages)-1;$i++) {

		$messageParts = explode("|",$queueMessages[$i]);


		//echo "0: ".$messageParts[0]."\n";
		//echo "1: ".$messageParts[1]."\n";
		//echo "2: " .$messageParts[2]."\n";
		//echo "3: ".$messageParts[3]."\n";

		$messageText = urldecode($messageParts[1]);

		//echo "Sending message: ".$messageText." to matrix FIFO\n";

		$cmd = $pluginDirectory."/".$fpp_matrixtools_Plugin."/".$fpp_matrixtools_Plugin_Script." --blockname \"".$Matrix."\" --font ".$MATRIX_FONT." --fontsize ".$MATRIX_FONT_SIZE." --pixelspersecond ".$MATRIX_PIXELS_PER_SECOND. " --message \"".$messageText."\"";

		//echo "p10 output cmd: ".$cmd."\n";

		logEntry("Matrix output cmd: ".$cmd);
		exec($cmd,$outputResults);
		//print_r($outputResults);

		//	$cmd = "/bin/echo \"".$messageText. "\" > ".$matrixFIFO;
		//	exec($cmd,$output);
		//echo "sleeping ".$MESSAGE_TIMEOUT. " sending clear line then";
		//	echo "sleeping: ".$MESSAGE_TIMEOUT." before clear \n";

		//	sleep($MESSAGE_TIMEOUT);

		sleep(1);
		clearMatrix();
	


		//$clearLineCmd = "/bin/echo \"\" > ".$matrixFIFO;
		//exec($clearLineCmd,$clearOutput);

	}


}



function printPluginsInstalled()


{

	global $PLUGINS,$pluginDirectory;

	include_once 'excluded_plugins.inc.php';
	//get all plugins
	
	$PLUGINS_INSTALLED = directoryToArray($pluginDirectory);//, $recursive)($pluginDirectory);
	//print_r($PLUGINS_INSTALLED);
	

	$PLUGINS_READ = explode(",",$PLUGINS);
	//print_r($PLUGINS_READ);

	echo "<select multiple=\"multiple\" name=\"PLUGINS[]\">";


	for($i=0;$i<=count($PLUGINS_INSTALLED)-1;$i++) {
		$PLUGIN_INSTALLED_TEMP = basename($PLUGINS_INSTALLED[$i]);

		if(in_array($PLUGIN_INSTALLED_TEMP,$EXCLUDE_PLUGIN_ARRAY)) {
			continue;
		}
		if(in_array($PLUGIN_INSTALLED_TEMP,$PLUGINS_READ)) {
				
			echo "<option selected value=\"" . $PLUGIN_INSTALLED_TEMP . "\">" . $PLUGIN_INSTALLED_TEMP . "</option>";
		} else {

			echo "<option value=\"" . $PLUGIN_INSTALLED_TEMP . "\">" . $PLUGIN_INSTALLED_TEMP . "</option>";
		}

	}
	echo "</select>";
}

function directoryToArray($directory, $recursive) {
	$array_items = array();
	if ($handle = opendir($directory)) {
		while (false !== ($file = readdir($handle))) {
			//do not include . or .DS_Store or .thumbs... etc
			if ($file != "." && $file != ".." && substr($file,0,1) != ".") {
				if (is_dir($directory. "/" . $file)) {
					if($recursive) {
						$array_items = array_merge($array_items, directoryToArray($directory. "/" . $file, $recursive));
					}
					$file = $directory . "/" . $file;
					$array_items[] = preg_replace("/\/\//si", "/", $file);
				} else {
					$file = $directory . "/" . $file;
					$array_items[] = preg_replace("/\/\//si", "/", $file);
				}
			}
		}
		closedir($handle);
	}
	return $array_items;
}
//is fppd running?????
function isFPPDRunning() {
	$FPPDStatus=null;
	logEntry("Checking to see if fpp is running...");
        exec("if ps cax | grep -i fppd; then echo \"True\"; else echo \"False\"; fi",$output);

        if($output[1] == "True" || $output[1] == 1 || $output[1] == "1") {
                $FPPDStatus = "RUNNING";
        }
	//print_r($output);

	return $FPPDStatus;
        //interate over the results and see if avahi is running?

}
//get current running playlist
function getRunningPlaylist() {

	global $sequenceDirectory;
	$playlistName = null;
	$i=0;
	//can we sleep here????

	//sleep(10);
	//FPPD is running and we shoud expect something back from it with the -s status query
	// #,#,#,Playlist name
	// #,1,# = running

	$currentFPP = file_get_contents("/tmp/FPP.playlist");
	logEntry("Reading /tmp/FPP.playlist : ".$currentFPP);
	if($currentFPP == "false") {
		logEntry("We got a FALSE status from fpp -s status file.. we should not really get this, the daemon is locked??");
	}
	$fppParts="";
	$fppParts = explode(",",$currentFPP);
//	logEntry("FPP Parts 1 = ".$fppParts[1]);

	//check to see the second variable is 1 - meaning playing
	if($fppParts[1] == 1 || $fppParts[1] == "1") {
		//we are playing

		$playlistParts = pathinfo($fppParts[3]);
		$playlistName = $playlistParts['basename'];
		logEntry("We are playing a playlist...: ".$playlistName);
		
	} else {

		logEntry("FPPD Daemon is starting up or no active playlist.. please try again");
	}
	
	
	//now we should have had something
	return $playlistName;
}
function logEntry($data) {

	global $logFile,$myPid;

	

		$data = $_SERVER['PHP_SELF']." : [".$myPid."] ".$data;
	
	$logWrite= fopen($logFile, "a") or die("Unable to open file!");
	fwrite($logWrite, date('Y-m-d h:i:s A',time()).": ".$data."\n");
	fclose($logWrite);
}



function processCallback($argv) {
	global $DEBUG,$pluginName;
	
	
	if($DEBUG)
		print_r($argv);
	//argv0 = program
	
	//argv2 should equal our registration // need to process all the rgistrations we may have, array??
	//argv3 should be --data
	//argv4 should be json data
	
	$registrationType = $argv[2];
	$data =  $argv[4];
	
	logEntry("PROCESSING CALLBACK: ".$registrationType);
	$clearMessage=FALSE;
	
	switch ($registrationType)
	{
		case "media":
			if($argv[3] == "--data")
			{
				$data=trim($data);
				logEntry("DATA: ".$data);
				$obj = json_decode($data);
	
				$type = $obj->{'type'};
				logEntry("Type: ".$type);	
				switch ($type) {
						
					case "sequence":
						logEntry("media sequence name received: ");	
						processSequenceName($obj->{'Sequence'},"STATUS");
							
						break;
					case "media":
							
						logEntry("We do not support type media at this time");
							
						//$songTitle = $obj->{'title'};
						//$songArtist = $obj->{'artist'};
	
	
						//sendMessage($songTitle, $songArtist);
						//exit(0);
	
						break;
						
						case "both":
								
						logEntry("We do not support type media/both at this time");
						//	logEntry("MEDIA ENTRY: EXTRACTING TITLE AND ARTIST");
								
						//	$songTitle = $obj->{'title'};
						//	$songArtist = $obj->{'artist'};
							//	if($songArtist != "") {
						
						
						//	sendMessage($songTitle, $songArtist);
							//exit(0);
						
							break;
	
					default:
						logEntry("We do not understand: type: ".$obj->{'type'}. " at this time");
						exit(0);
						break;
	
				}
	
	
			}
	
			break;
			exit(0);
	
		case "playlist":

			logEntry("playlist type received");
			if($argv[3] == "--data")
                        {
                                $data=trim($data);
                                logEntry("DATA: ".$data);
                                $obj = json_decode($data);
				$sequenceName = $obj->{'sequence0'}->{'Sequence'};	
				$sequenceAction = $obj->{'Action'};	
                                                processSequenceName($sequenceName,$sequenceAction);
                                                //logEntry("We do not understand: type: ".$obj->{'type'}. " at this time");
                                        //      logEntry("We do not understand: type: ".$obj->{'type'}. " at this time");
			}

			break;
			exit(0);			
		default:
			exit(0);
	
	}
	

}
?>
