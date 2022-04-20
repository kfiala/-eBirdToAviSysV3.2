<?php
require_once 'error_handler.php';
function curlCall($URL,$useAPI=true)
{
	require 'curlConfig.php';

	$ch = curl_init($URL);
	if (!$ch)
	{
		$e = new Exception;
		$traceback = var_export($e->getTraceAsString(), true);

		$IP = $_SERVER["REMOTE_ADDR"];
		$URL = urlencode($URL);
		error_log ( "curl failure:\n$traceback\n From $IP: URL=$URL", 1, $error_email );
		die ("<p>Sorry, unable to fetch data. $downMessage</p>");
	}

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	if ($useAPI)
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-eBirdApiToken: $apiKey"));
	else
	{	// --cookie '' -L
		curl_setopt($ch, CURLOPT_COOKIEFILE,'');
		curl_setopt($ch,CURLOPT_FOLLOWLOCATION,true);
	}
	curl_setopt($ch, CURLOPT_HEADER, 0);

//	echo "<p>Calling curl on $URL</p>";

	$json = curl_exec($ch);

	if ($json === false)
	{
		error_handler($ch);
		$json = '';
		exit;
	}
	curl_close($ch);
	loginfo("Retrieved $URL");
	if ($useAPI)
		return json_decode($json);
	else
		return $json;
}
?>