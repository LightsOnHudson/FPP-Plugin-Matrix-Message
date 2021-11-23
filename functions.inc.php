<?php

function printHourFormats($ELEMENT_NAME,$ELEMENT_SELECTED)


{

	global $DEBUG;

	$T_FORMATS = array("12","24");


	echo "<select  name=\"".$ELEMENT_NAME."\">";

	//print_r($PLUGINS_READ);


	for($i=0;$i<=count($T_FORMATS)-1;$i++) {



		if($T_FORMATS[$i] == $ELEMENT_SELECTED) {

			echo "<option selected value=\"" . $ELEMENT_SELECTED . "\">" . $ELEMENT_SELECTED . " HR</option>";
		} else {

			echo "<option value=\"" . $T_FORMATS[$i] . "\">" . $T_FORMATS[$i] . " HR</option>";
		}

	}
	echo "</select>";
}

function printTimeFormats($ELEMENT_NAME,$ELEMENT_SELECTED)


{

	global $DEBUG;

	$T_FORMATS = array("h:i" => "HH:MM","h:i:s" => "HH:MM:SS");
	

	
	
	echo "<select  name=\"".$ELEMENT_NAME."\">";
	
	//print_r($PLUGINS_READ);
	foreach($T_FORMATS as $key => $value)
	{
		
		
		
		if($key == $ELEMENT_SELECTED) {

			echo "<option selected value=\"" . $key . "\">" . $value . "</option>";
		} else {

			echo "<option value=\"" . $key . "\">" .  $value . "</option>";
		}

	}
	echo "</select>";
}

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
		$eventData .= "name='".$EVENT_KEY."'\n";
		$eventData .= "effect=''\n";
		$eventData .= "startChannel=\n";
		$eventData .= "script='".$EVENT_KEY.".sh'\n";

		//	echo "eventData: ".$eventData."<br/>\n";
		file_put_contents($eventDirectory."/".$nextEventFilename, $eventData);

		$scriptCMD = $pluginDirectory."/".$pluginName."/"."matrix.php";
		createScriptFile($EVENT_KEY.".sh",$scriptCMD);
	}


	//echo "$key => $val\n";
		





}
function outputMessages($queueMessages) {

	global $DEBUG, $pluginDirectory,$MESSAGE_TIMEOUT, $fpp_matrixtools_Plugin, $fpp_matrixtools_Plugin_Script,$Matrix,$MATRIX_FONT,$MATRIX_FONT_SIZE,$MATRIX_PIXELS_PER_SECOND,$COLOR, $INCLUDE_TIME, $TIME_FORMAT, $HOUR_FORMAT,$SEPARATOR;
                                               		// sjt 11/22/2021 - possible bug with pipe entered by user as "$SEPARATOR"
	//print_r($queueMessages);
	
	if($DEBUG)
		logEntry("MESSAGE QUEUE: Inside function ".__METHOD__,0,__FILE__,__LINE__);

	if(count($queueMessages) <=0) {
		logEntry("No messages to output ");
		return;
	}
	

	If (strtoupper(trim($COLOR)) == "RANDOM") {
		
		//print_r ("Start counter");
		$counter=rand(0,5);
		//print_r ("Start switch");
		switch ($counter) {
			case "0":
				$mycolor = "indigo";
				//echo $mycolor;
				break;
			case "1":
				$mycolor = "red";
				//echo $mycolor;
				break;
			case "2":
				$mycolor = "green";
				//echo $mycolor;
				break;
			case "3":
				$mycolor = "blue";
				//echo $mycolor;
				break;
			case "4":
				$mycolor = "yellow";
				//echo $mycolor;
				break;
			case "5":
				$mycolor = "purple";
				//echo $mycolor;
				break;
		}
		logEntry("MATRIX MESSAGE: Selecting RANDOM COLOR: ".$mycolor);
		//print_r ("End switch");
		$COLOR=($mycolor);
		//print_r ($COLOR);
	} 
	

	enableMatrixToolOutput();

	for($i=0;$i<=count($queueMessages)-1;$i++) {
	$messageText = "";
	
	if($INCLUDE_TIME == 1 || $INCLUDE_TIME == "on") {
		
		switch ($HOUR_FORMAT) {
			
			case "12":
				$messageTime = date($TIME_FORMAT);
				break;
				
			case "24":
				
				$messageTime = date($TIME_FORMAT);
				
				break;
		}
		
		logEntry( "Message time: ".$messageTime);
		
		$messageText = "Time: ".$messageTime." ".$SEPARATOR." ";
	}
	
	//for($i=0;$i<=count($queueMessages)-1;$i++) {

		$messageParts = explode("|",$queueMessages[$i]);


		//echo "0: ".$messageParts[0]."\n";
		//echo "1: ".$messageParts[1]."\n";
		//echo "2: " .$messageParts[2]."\n";
		//echo "3: ".$messageParts[3]."\n";

		$messageText .= urldecode($messageParts[1]);
		logEntry("MATRIX PLUGIN: Writing last read for plugin BEFORE sending Message!: ".urldecode($messageParts[2]). ": ".urldecode($messageParts[0]));
		
		WriteSettingToFile("LAST_READ",urldecode($messageParts[0]),urldecode($messageParts[2]));

		$cmd = $pluginDirectory."/".$fpp_matrixtools_Plugin."/".$fpp_matrixtools_Plugin_Script." --blockname \"".$Matrix."\" --color '".$COLOR."' --font ".$MATRIX_FONT." --fontsize ".$MATRIX_FONT_SIZE." --pixelspersecond ".$MATRIX_PIXELS_PER_SECOND. " --message \"".urldecode($messageText)."\"";

		logEntry("Matrix output cmd: ".$cmd);
		exec($cmd,$outputResults);


		sleep(1);
		clearMatrix();
		//write the last read right after the message goes out!!! back to the plugin that requested it!


		//sleep(1);
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
		if((substr($PLUGIN_INSTALLED_TEMP,0,1) != "." && substr($PLUGIN_INSTALLED_TEMP,0,1) != "_")) {
			if(in_array($PLUGIN_INSTALLED_TEMP,$PLUGINS_READ)) {
					
				echo "<option selected value=\"" . $PLUGIN_INSTALLED_TEMP . "\">" . $PLUGIN_INSTALLED_TEMP . "</option>";
			} else {
	
				echo "<option value=\"" . $PLUGIN_INSTALLED_TEMP . "\">" . $PLUGIN_INSTALLED_TEMP . "</option>";
			}
		}

	}
	echo "</select>";
}

function printPixelsPerSecond($ELEMENT, $PIXELS_PER_SECOND)


{

        global $PLUGINS,$pluginDirectory;

        //updated to 40, Nov 9 2015
        $MAX_PIXELS_PER_SECOND = 99;

        echo "<select name=\"".$ELEMENT."\">";


        for($i=0;$i<=$MAX_PIXELS_PER_SECOND-1;$i++) {

                        if($i == $PIXELS_PER_SECOND) {

                                 echo "<option selected value=\"" . $i. "\">" . $i. "</option>";
                       } else {
                        echo "<option value=\"" . $i. "\">" . $i. "</option>";
                        }
        }
        echo "</select>";
}
function printFontSizes($ELEMENT, $FONT_SIZE)


{

        global $PLUGINS,$pluginDirectory;

	$MAX_FONT_SIZE = 64;

        echo "<select name=\"".$ELEMENT."\">";


        for($i=0;$i<=$MAX_FONT_SIZE-1;$i++) {

                        if($i == $FONT_SIZE) {

                                 echo "<option selected value=\"" . $i. "\">" . $i. "</option>";
                       } else {
                        echo "<option value=\"" . $i. "\">" . $i. "</option>";
                        }
        }
        echo "</select>";
}




function printFontsInstalled($ELEMENT, $FONT)


{

	// this uses the fpp-matrix tools plugin to get the fonts that it can use!
	
	
        global $DEBUG,$PLUGINS,$pluginDirectory, $fpp_matrixtools_Plugin_Script, $fpp_matrixtools_Plugin;
        
      //  $fpp_matrixtools_Plugin = "fpp-matrixtools";
       // $fpp_matrixtools_Plugin_Script = "scripts/matrixtools";
	
        
        $fontsDirectory = "/usr/share/fonts/truetype/";

	//$FONTS_LIST_CMD = "/usr/bin/fc-list";
	
	//$FONT_LIST = system($FONTS_LIST_CMD);
	//if($DEBUG)
	//	print_r($FONT_LIST);
	
       // $FONTS_INSTALLED = directoryToArray($fontsDirectory, true);//, $recursive)($pluginDirectory);

        $MatrixToolsFontsCMD = $pluginDirectory."/".$fpp_matrixtools_Plugin . "/".$fpp_matrixtools_Plugin_Script. " --getfontlist";
        
        exec($MatrixToolsFontsCMD, $fontsList);
     //   print_r($fontsList);
        
       
       // $FONTS_INSTALLED = explode(" ",$fontsList);

      //  print_r($FONTS_INSTALLED);
        //print_r($PLUGINS_READ);

        echo "<select name=\"".$ELEMENT."\">";


        for($i=1;$i<=count($fontsList)-1;$i++) {
	//	$FONTINFO = pathinfo($FONTS_INSTALLED[$i]);
       //         $FONTS_INSTALLED_TEMP = basename($FONTS_INSTALLED[$i],'.'.$FONTINFO['extension']);

			if($fontsList[$i] == $FONT) {
			
                       		 echo "<option selected value=\"" . $FONT . "\">" . $FONT . "</option>";
                       } else { 
			echo "<option value=\"" . $fontsList[$i] . "\">" . $fontsList[$i] . "</option>";
			}
        }
        echo "</select>";
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
function logEntry($data,$logLevel=1,$sourceFile, $sourceLine) {

	global $logFile,$myPid, $LOG_LEVEL;

	
	if($logLevel <= $LOG_LEVEL) 
		//return
		
		if($sourceFile == "") {
			$sourceFile = $_SERVER['PHP_SELF'];
		}
		$data = $sourceFile." : [".$myPid."] ".$data;
		
		if($sourceLine !="") {
			$data .= " (Line: ".$sourceLine.")";
		}
		
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
