<?php
function write($file,$content,$flags=0){
	file_put_contents($file,$content,$flags);
	chmod($file,0777);
}

function read(){
	$feeds = $_REQUEST['feed'];
	if ( $feeds == '' ) throw new Exception('empty');
	
	write('log/feed.logson',$feeds,FILE_APPEND);
	
	if ( !preg_match_all('/\[([0-9]+),([0-9]+),\[([0-9,]+)\]\]/', $feeds,$matches) ) throw new Exception('feed does not match pattern');
	
	return $matches[0];
}

function decode($feed){
	$data = json_decode($feed);
	if ( $data ) return $data;
	
	throw new Exception('failed to parse: '. $feed);
}

function prepare($data){
	list($idx,$milli,$times) = $data;
	
	if ( $idx > 1 ) {
		$start = (int)file_get_contents('data/start');
		if ( !$start ) throw new Exception('no start');
	} else {
		$start = time()-$milli/1000;
		write('data/start',$start);
	}
	
	$ts = $start+($milli/1000);
	
	return array(
		'.priority'=>$ts,
		'ts'=>$ts,
		'idx'=>$idx,
		'times'=>$times
	);
}

if ( $argc > 1  ) {
	$_POST = array(
		'feed'=>$argv[1],
	);
}

try {
	set_include_path(join(PATH_SEPARATOR,array(
		"firebase/php-jwt/Authentication",
		"firebase/firebase-token-generator-php",
		".",
	)));
	
	include_once "FirebaseToken.php";
	include_once "RESTFirebase.php";
	
	if ( !isset($_REQUEST['feed']) ) throw new Exception('no feed');
	
	$base = new RESTFirebase('25Epyg9MkxzZynFDWSakAnb4npYy4CqWk0KsL044', 'nrjfeed');
	
	foreach( read() as $feed ) {
		$feed = decode($feed);
		$data = prepare($feed);
		
		$base->post('feed/',$data);
		
		$dataLog = 'data/history/'.date('Y-m-d').'.logson';
		if ( file_exists($dataLog) ) write($dataLog,','."\n".json_encode($data));
		else write($dataLog,'['."\n".json_encode($data));
	}
	
} catch( Exception $e ){
	echo 'Exception: '. $e->getMessage();
	write('log/exceptions.log', "\n".date('r')."\t".$e->getMessage(),FILE_APPEND);
}