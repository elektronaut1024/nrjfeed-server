<?php

//file_put_contents('nrjfeed.logson',"\n".trim($_POST['data']),FILE_APPEND);

set_include_path(join(PATH_SEPARATOR,array(
	"firebase/php-jwt/Authentication",
	"firebase/firebase-token-generator-php",
)));

include_once "FirebaseToken.php";

$secret = "";
$tokenGen = new Services_FirebaseTokenGenerator($secret);
$token = $tokenGen->createToken(array("id" => "nrjfeed"));

// Get data only readable by auth.id = "example".
$ch = curl_init("https://nrjfeed.firebaseio.com/.json?auth=$token");

curl_exec($ch);