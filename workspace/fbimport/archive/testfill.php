#!/usr/local/php5/bin/php
<?php 

date_default_timezone_set('America/Los_Angeles');

$cmdoptions = getopt("t:d:s::");
$days=0;
if($cmdoptions){
	
	$starttime = strtotime($cmdoptions['t']);
	$days =  $cmdoptions['d'];
	$brandset = false;
	if(isset($cmdoptions['s']))
		$brandset = $cmdoptions['s'];
	}
else{
		echo "\n Help\n";
		echo "-t	- time flag (optional)\n";
		echo "-d	- # of days to process (only if -t is used)\n";
		echo "-s	- Only process a specific competitive set.\n";
		echo "Example\n";
		echo "/testfill.php -t'Jan 01 00:00:00 UTC 2010' -d1 -s'Yokohama'\n";
		exit;
}


$filltime= $starttime;	
for($d =1; $d<=$days;$d++){

if($brandset)
	$sflag = " -s\"".$brandset."\"";
else {
	$sflag = "";

}

$ex = "./pollfb.php -t'".date("M d Y H:i:s O",$filltime )." '".$sflag."\n";
echo $ex."\n";

$out = shell_exec($ex);
echo $out;

$filltime += 86400;
}


?>