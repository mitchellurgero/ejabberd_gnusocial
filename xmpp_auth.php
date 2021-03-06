<?php
error_reporting(0);
$server = "https://p2px.me"; //Full URI to your instance (No trailing slash)
$xmpp_server = "p2px.me"; //Purely so we can replace this string in the XMPP's jabberID for processing later in script.
$ja_user;
$ja_pass;
$js_host;
$data;
$stout;
$stin;
$limit = 100;
play();
function play(){
	global $data, $stin, $stout;
	openstd();
	do {
		$limit = $limit - 1;
		readstdin(); // get data
		$ret = command(); // play with data !
		//syslog(LOG_INFO, $data);
		//syslog(LOG_INFO, $ret);
		error_log($data, 3, "/var/logs/ejabberd/error.log");
		error_log($ret, 3, "/var/logs/ejabberd/error.log");
		out($ret); // send what we reply.
		$data = NULL; // more clean. ...
		usleep(500000); //sleep half second
		if($limit <= 0){ exit(0); }
	} while (true);
}
function command(){
	global $data, $stin, $stout, $server, $xmpp_server;
	$data = splitcomm();
	switch($data[0]){
		case "isuser":
			$uname = $data[1];
			$uname = str_replace("@$xmpp_server","",$uname);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,"$server/api/statuses/friends_timeline/$uname.json");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HEADER  , true);
			//curl_setopt($ch, CURLOPT_NOBODY  , true);
			$server_output = curl_exec ($ch);
			$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close ($ch);
			error_log($httpcode."--".$server_output, 3, "/var/logs/ejabberd/error.log");
			$return = false;
			if($httpcode == 200){
				$return = true;
			} else {
				$return = false;
			}
			$return = ($return) ? 1 : 0;
			return @pack("nn",2,$return);
			break;
		case "auth":
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,"$server/api/qvitter/checklogin.json"); //Change this to match your server
			curl_setopt($ch, CURLOPT_POST, 2);
			curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query(array('password' => $data[3], 'username' => $data[1])));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HEADER  , true);
			//curl_setopt($ch, CURLOPT_NOBODY  , true);
			$server_output = curl_exec ($ch);
			$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close ($ch);
			error_log($httpcode."--".$server_output, 3, "/var/logs/ejabberd/error.log");
			$return = false;
			if($httpcode == 200){
				$return = true;
			} else {
				$return = false;
			}
			$return = ($return) ? 1 : 0;
			return @pack("nn",2,$return);
			break;
		default:
			//Do nothing.
			break;
	}
}
function out($message){
	global $data, $stin, $stout;
	@fwrite($stout, $message); // We reply ...
	$dump = @unpack("nn", $message);
	$dump = $dump["n"];
}
function splitcomm(){
	global $data, $stin, $stout;
	return explode(":", $data);
}
function openstd(){
	global $data, $stin, $stout;
	$stout = @fopen("php://stdout", "w"); // We open STDOUT so we can read
	$stin  = @fopen("php://stdin", "r"); // and STDIN so we can talk !
}
function readstdin(){
	global $data, $stin, $stout;
	$l      = @fgets($stin, 3); // We take the length of string
	$length = @unpack("n", $l); // ejabberd give us something to play with ...
	$len    = $length["1"]; // and we now know how long to read.
	if($len > 0) { // if not, we'll fill logfile ... and disk full is just funny once
		$data   = @fgets($stin, $len+1);
		// $data = iconv("UTF-8", "ISO-8859-15", $data); // To be tested, not sure if still needed.
	}
}

function closestd(){
	global $data, $stin, $stout;
	@fclose($stin); // We close everything ...
	@fclose($stout);
}
?>
