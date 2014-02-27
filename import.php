<?php
set_include_path(join(PATH_SEPARATOR,array(
"firebase/php-jwt/Authentication",
"firebase/firebase-token-generator-php",
".",
)));

include_once "FirebaseToken.php";
include_once "RESTFirebase.php";

$start = (int)file_get_contents('start');
if ( !$start ) die('failed to determine start');

$offset = file_get_contents('offset');
$offsetCountdown = $offset;

$limit = (int)($argv[1]?$argv[1]:10);
$counter = 0;
$remainder = null;

$base = new RESTFirebase('25Epyg9MkxzZynFDWSakAnb4npYy4CqWk0KsL044', 'nrjfeed');

$lastData = '';
$line = '';
$f = fopen('nrjfeed.archive.logson','r');
while ( true ) {
	$line = trim(fgets($f));
	if ( $line === false ) break;
	
	$data = json_decode($remainder.$line);
	if ( !$data ) {
		echo '~';
		if ( $remainder != '' ) {
			echo "\n'$line'\n";
			echo "\nfailed";
			break;
		}
		$remainder = $line;
		continue;
	}
	
	$remainder = '';
	
	if ( $offsetCountdown-- > 0 ) {
		$line = '';
		echo '.';
		continue; //skip those
	}
	
	if ( --$limit < 0 ) {
		echo "\nlimit reached";
		break;
	}
	
	file_put_contents('offset', ++$offset);
	
	if ( $lastData && $lastData[0] != ($data[0] - 1) ) {
		echo "\ngap on line $counter: {$lastData[0]} => $data[0]";
	}
	
	list($idx,$milli,$times) = $data;
	
	$ts = $start+($milli/1000);
	
	echo "$idx,";

	$base->post('feed/'
			.date('Y',$ts).'/'
			.date('m',$ts).'/'
			.date('d',$ts).'/'
			.date('H',$ts).'/'
			.date('i',$ts).'/'
		,array(
		'.priority'=>$ts*1000+$milli,
		'idx'=>$idx,
		'ts'=>$ts,
		'milli'=>$milli,
		'times'=>$times
	));
	
	$line = '';
	$lastData = $data;
}