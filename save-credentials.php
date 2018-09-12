<?php
	require_once("functions.php");
	session_start();

	header('Content-Type: text/html; charset=utf-8');

	global $CLIENT_ID, $CLIENT_SECRET, $REDIRECT_URI;

	$client = new Google_Client();
	$client->setClientId($CLIENT_ID);
	$client->setClientSecret($CLIENT_SECRET);
	$client->setRedirectUri($REDIRECT_URI);
	$client->setScopes('email');

	$authUrl = $client->createAuthUrl();	

	if(isset($_GET['code'])){
		getCredentials($_GET['code'], $authUrl);
		header("Location:".WEB."display-albums.php");
	}
	else{
		header('Content-Type: text/html; charset=utf-8');
		$url = getAuthorizationUrl("", "");
		echo "<script> window.location= '".$url."'; </script>";
	}
?>