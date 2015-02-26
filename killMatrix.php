#!/usr/bin/php
<?
//error_reporting(0);

$pluginName ="MatrixMessage";
$myPid = getmypid();

$DEBUG=false;

$skipJSsettings = 1;
include_once("/opt/fpp/www/config.php");
include_once("/opt/fpp/www/common.php");
include_once("functions.inc.php");

require ("lock.helper.php");
define('LOCK_DIR', '/tmp/');
define('LOCK_SUFFIX', '.lock');

$logFile = $settings['logDirectory']."/".$pluginName.".log";


$fpp_matrixtools_Plugin = "fpp-matrixtools";
$fpp_matrixtools_Plugin_Script = "scripts/matrixtools";
$P10Matrix = urldecode(ReadSettingFromFile("P10Matrix",$pluginName));


logEntry("unlocking matrix.php");

//unlock the pid
//lockHelper::unlock();
//clearMatrix();

if(file_exists("/tmp/matrix.php.lock")) {
	$matrix_pid = file_get_contents("/tmp/matrix.php.lock");
	
	$cmdKill = "sudo kill -9 ".$matrix_pid;
	
	exec($cmdKill);
	
}
//$cmdKillPHPMatrix = "sudo killall -9 matrix.php";

//logEntry("Killing php matrix: ".$cmdKillPHPMatrix);

//exec($cmdKillPHPMatrix);

//sleep(1);


$cmdKill = "sudo killall -9 matrixtools";

logEntry("Killing Matrix pid:");

exec($cmdKill,$result);

clearMatrix();




//print_r($result);


?>
