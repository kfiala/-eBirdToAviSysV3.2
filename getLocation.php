<?php
require_once 'curlCall.php';
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME']))	// Direct http entry, not via include
{
	$loc = $_GET['loc'];

	echo '<p>'.getLocation($loc).'</p>';
}
/*
curl --location --request GET "https://ebird.org/ws2.0/ref/L2508836"   --header 'X-eBirdApiToken: qurt0fg3admo'
{"result":"1714 Borland Road","bounds":{"minX":-79.18809999999999,"maxX":-79.0881,"minY":35.95445,"maxY":36.054449999999996}}[
*/
function getLocation($location)
{
	$locInfo = curlCall("https://ebird.org/ws2.0/ref/region/info/$location");
	$name = $locInfo->result;
	return $name;
}
?>