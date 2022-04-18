<?php
function error_handler($ch)
{
	$ce = curl_error($ch);
	$errno = curl_errno($ch);
	$httpCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE );
	$remoteIP = $_SERVER["REMOTE_ADDR"];
	if ($errno == CURLE_HTTP_RETURNED_ERROR)
		if ($httpCode < 500)
		{
			echo "<p class=outage>There is an error in your URL. Did you hack it?</p>";
//			error_log(__FILE__.' line '. __LINE__." ce $ce, errno $errno\nURL $URL\n\nHTTP code: $httpCode\n$remoteIP",1,ERROR_EMAIL);
		}
		else
		{
			echo "<p class=outage>Sorry, an error occurred on the eBird server. eBird may be down.</p>";
//			error_log(__FILE__.' line '. __LINE__." ce $ce, errno $errno\nURL $URL\n\nHTTP code: $httpCode\n$remoteIP",1,ERROR_EMAIL);
		}
	else
	{
		echo "<p class=outage>Sorry, an error occurred while connecting to eBird: $ce (Error $errno). eBird may be down.</p>";
//		error_log(__FILE__.' line '. __LINE__." ce $ce, errno $errno\nURL $URL\n\nHTTP code: $httpCode\n$remoteIP",1,ERROR_EMAIL);
		/* Errors that have been observed here, with HTTP code 0, include:
			TCP connection reset by peer, errno 35
			Failed connect to ebird.org:443; Connection timed out, errno 7
		*/
	}
}
?>