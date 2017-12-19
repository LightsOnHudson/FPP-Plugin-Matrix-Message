<?php
//display the various overlay modes for matrix tools

function PrintOverlayMode($overlayMode) {
	
	global $DEBUG;
	echo " 1 = FULL OVERLAY, 2 = TRANSPARENT, 3 = Transparent RGB \n";

	echo "<select name=\"OVERLAY_MODE\"> \n";
	
	for($i=1;$i<=3;$i++) {
		
		if($overlayMode == $i) {
			echo "<option selected value=\"".$i."\">".$i."</option> \n";
		} else {
			echo "<option value=\"".$i."\">".$i."</option> \n";
		}	
	}
	echo "</select> \n";
}

function clearMatrix($matrix="") {

	global $pluginDirectory, $fpp_matrixtools_Plugin, $fpp_matrixtools_Plugin_Script,$Matrix,$settings;;
	
	if($matrix == "") {
		$matrix = $Matrix;
	}
	//	$cmdClear = $pluginDirectory."/".$fpp_matrixtools_Plugin."/".$fpp_matrixtools_Plugin_Script." --blockname \"".$Matrix."\" --clearblock";

	$cmdClear = $settings['fppBinDir']."/fppmm -m \"".$matrix."\" -s 0";


	logEntry("Matrix Clear cmd: ".$cmdClear);

	exec($cmdClear,$clearOutput);
}

function enableMatrixToolOutput($matrix="") {
	global $DEBUG, $fpp_version, $settings, $pluginDirectory,$fpp_matrixtools_Plugin, $fpp_matrixtools_Plugin_Script,$Matrix, $overlayMode;
	
	if($overlayMode == "") {
		$overlayMode = "1";
	}

	if($matrix =="" ) {
		$matrix = $Matrix;
	}

	switch ($overlayMode) {
		
		case "0":
			$overlayModeCMD = "off";
			break;
			
		case "1":
			$overlayModeCMD = "on";
			break;
			
		case "2":
			
			$overlayModeCMD = "transparent";
			break;
				
		case "3":
					
			$overlayModeCMD = "transparentrgb";
			break;
					
		default:
			
			$overlayModeCMD = "on";
			
	}
	
	$cmdEnable = $pluginDirectory."/".$fpp_matrixtools_Plugin."/".$fpp_matrixtools_Plugin_Script. " --blockname \"".$matrix."\" --enable ".$overlayMode;//1";
	
	switch($fpp_version) {
		
		case "1.9":
			$cmdEnable = $settings['fppBinDir']."/fppmm -m \"".$matrix."\" -o ".$overlayModeCMD;
		//	$cmdEnable = $settings['fppBinDir']."/fppmm -m \"".$matrix."\" -t ".$overlayModeCMD;
			break;
			
		case "1.10":
			$cmdEnable = $settings['fppBinDir']."/fppmm -m \"".$matrix."\" -o ".$overlayModeCMD;
			//	$cmdEnable = $settings['fppBinDir']."/fppmm -m \"".$matrix."\" -t ".$overlayModeCMD;
			break;
			
		case "1.8":
			$cmdEnable = $settings['fppBinDir']."/fppmm -m \"".$matrix."\" -t ".$overlayModeCMD;
			
			break;
			
		default:
			$cmdEnable = $settings['fppBinDir']."/fppmm -m \"".$matrix."\" -e";// ".$overlayModeCMD;
			break;
	}
	
	
	
	
	logEntry("Matrix Enable cmd: ".$cmdEnable);
	//echo "p10 enable: ".$cmdEnable."\n";

	exec($cmdEnable,$enableOutput);
	//echo "Enabled \n";

	//print_r($enableOutput);

}

function disableMatrixToolOutput($matrix="") {
	global $DEBUG, $fpp_version, $settings,$pluginDirectory,$fpp_matrixtools_Plugin, $fpp_matrixtools_Plugin_Script,$Matrix;

	if($matrix =="" ) {
		$matrix = $Matrix;
	}

	switch($fpp_version) {
		
		case "1.9":
			$cmdDisable = $settings['fppBinDir']."/fppmm -m \"".$matrix."\" -o off";
			//$cmdDisable = $settings['fppBinDir']."/fppmm -m \"".$matrix."\" -t off";
			break;

		case "1.10":
			$cmdDisable = $settings['fppBinDir']."/fppmm -m \"".$matrix."\" -o off";
			//$cmdDisable = $settings['fppBinDir']."/fppmm -m \"".$matrix."\" -t off";
			break;
			
		case "1.8":
			$cmdDisable = $settings['fppBinDir']."/fppmm -m \"".$matrix."\" -t off";
			
			break;
		default:
			$cmdDisable = $settings['fppBinDir']."/fppmm -m \"".$matrix."\" -d";
			break;
	}
	
	//$cmdDisable = $pluginDirectory."/".$fpp_matrixtools_Plugin."/".$fpp_matrixtools_Plugin_Script. " --blockname \"".$matrix."\" --enable 0";
	
	
	
	
	logEntry("Matrix disable cmd: ".$cmdDisable);
	//echo "p10 enable: ".$cmdEnable."\n";

	exec($cmdDisable,$disableOutput);
	//echo "Enabled \n";

	//print_r($enableOutput);

}
function PrintMatrixList($SELECT_NAME="MATRIX",$MATRIX_READ)
{
	global $pluginDirectory,$fpp_matrixtools_Plugin,$fpp_matrixtools_Plugin_Script;//,$blockOutput;

	$blockOutput = getBlockOutputs();
	//print_r($blockOutput);


	//$matrixBlocks=array();
	echo "<select name=\"".$SELECT_NAME."\">";

	for($i=0;$i<=count($blockOutput)-1;$i++) {

		//$blockParts = explode(":",$blockOutput[$i]);

		//echo "blockPart 0: ".$blockParts[0]. " : ".$blockParts[1]."<br/> \n";

		
			if(trim($blockOutput[$i])==$MATRIX_READ) {
				echo "<option selected value=\"".trim($MATRIX_READ)."\">".trim($MATRIX_READ)."</option>\n";
			} else {
				echo "<option value=\"".trim($blockOutput[$i])."\">".trim($blockOutput[$i])."</option>\n";
			}
		
	}

	echo "</select>";
}


//chase around the border of a matrix with colors :)

function matrixBorderChase($MATRIX,$COLOR=255) {
	
	global $pluginDirectory,$fppMM,$settings,$pluginSettings,$blockOutput;
	
	//	['channelOutputsFile'] = $mediaDirectory . "/channeloutputs";
	//$settings['channelMemoryMapsFile']
	
	
	
}

//get the block outputs
function getBlockOutputs() {
	global $settings;
	//echo "getting blocks";
	
	$blockOutput = array();
	
	$blocksTmp = file_get_contents($settings['channelMemoryMapsFile']);
	
	//print_r($blocksTmp);
	
	$blocks = explode("\n",$blocksTmp);
	
	//print_r($blocks);
	$blockIndex=0;
	
	for($blockIndex =0; $blockIndex<=count($blocks)-1;$blockIndex++) {
		$blockParts = explode(",",$blocks[$blockIndex]);
		$blockOutput[] = $blockParts[0];
		//$blockIndex++;
		//$blockOutput [] =
	}
	//print_r($blockOutput);
	
	return $blockOutput;
}

?>
