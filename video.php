#!/usr/bin/php
<?php


$myPid = getmypid();

include_once "/opt/fpp/www/common.php";
include_once "functions.inc.php";
$pluginName = "MatrixMessage";
$fpp_matrixtools_Plugin = "fpp-matrixtools";
$fpp_matrixtools_Plugin_Script = "scripts/matrixtools";
$FPP_MATRIX_PLUGIN_ENABLED=false;
$logFile = $settings['logDirectory']."/".$pluginName.".log";
$P10Matrix = urldecode(ReadSettingFromFile("P10Matrix",$pluginName));

echo "outputing video!!! \n";

$VIDEO_PATH = "/home/pi/media/effects/";

$IMAGE_BASE = "image-";
$IMAGE_EXT = ".jpg";

$FPPMM = "/opt/fpp/bin/fppmm";


$NUM_OF_FRAMES = 1200;

$CONVERT_BINARY = "/usr/bin/convert";
$frame =0;

for($frame=0;$frame<=$NUM_OF_FRAMES;$frame++) {

	$cmd = $CONVERT_BINARY. " -scale '32x16!' -depth 8 ".$VIDEO_PATH.$IMAGE_BASE.$frame.$IMAGE_EXT." rgb:".$VIDEO_PATH."RGB".$IMAGE_BASE.$frame.$IMAGE_EXT;
	//convert the image and then output it to the fppmm

	//$cmd = "cat ".$VIDEO_PATH.$IMAGE_BASE.$frame.$IMAGE_EXT. " > /home/pi/matrix";
	//exec($cmd);
	echo "CMD: ".$cmd."\n";
}

$frame=0;
for($frame=0;$frame<=$NUM_OF_FRAMES;$frame++) {
	
	$cmd = $FPPMM. " -m ".$P10Matrix." -f ".$VIDEO_PATH.$IMAGE_BASE.$frame.$IMAGE_EXT;
	//convert the image and then output it to the fppmm
	
	//$cmd = "cat ".$VIDEO_PATH.$IMAGE_BASE.$frame.$IMAGE_EXT. " > /home/pi/matrix";
	exec($cmd);
//	echo "CMD: ".$cmd."\n";
}

clearMatrix();



///Users/bshaver/ffmpeg/ffmpeg -i /Users/bshaver/Movies/Halloween.mp4 -r 20 -vframes 120 -vf scale=32:16 -f image2 /Volumes/FPP/FPP3/effects/image-%1d.jpg

?>