<?php
$start = (int)file_get_contents('start');
if ( !$start ) die('failed to determine start');

$counter = 0;
$remainder = null;

$lastPath = '';
$lastData = '';
$line = '';
$fIn = fopen('nrjfeed.archive.logson','r');
$fOut = null;
while ( true ) {
	$counter++;
	$line = fgets($fIn);
	if ( $line === false ) break;
	
	$line = trim($line);
	
	$data = json_decode($remainder.$line);
	if ( !$data ) {
		if ( $remainder != '' ) {
			echo "\nfailed on line $counter";
			break;
		}
		$remainder = $line;
		continue;
	}
	
	$remainder = '';
	
	if ( $lastData && $lastData[0] != ($data[0] - 1) ) {
		echo "\ngap on line $counter: {$lastData[0]} => $data[0]";
	}
	
	list($idx,$milli,$times) = $data;
	
	$ts = $start+($milli/1000);
	
	$path = 'feed/'
		.date('Y',$ts).'.'
		.date('m',$ts).'.'
		.date('d',$ts).'.json'
	;
	
	$post = array(
		'idx'=>$idx,
		'ts'=>$ts,
		'milli'=>$milli,
		'times'=>$times
	);
	
	if ( $fOut && $path != $lastPath ){
		fwrite($fOut, "\n".']');
		fclose($fOut);
	}
	
	if ( !$lastPath || $path != $lastPath ){
		echo "\n".$path;
		$fOut = fopen($path, 'w');
		fwrite($fOut, '['."\n".json_encode($post));
	} else {
		fwrite($fOut, ','."\n".json_encode($post));
	}
	
	$line = '';
	$lastPath = $path;
	$lastData = $data;
}


if ( $fOut ) {
	fwrite($fOut, "\n".']');
	fclose($fOut);
}

fclose($fIn);