<?php
require_once './avisys.php';
session_start();
require_once './upload_form.php';
require_once './upload_files.php';
require_once './generate_stream.php';
require_once './utilities.php';

// Global variables.
$myself = $_SERVER['REQUEST_URI'];
if (!isset($_SESSION['eBird']['REFERER']))
	$_SESSION['eBird']['REFERER'] = (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : "");
$posted = ($_SERVER["REQUEST_METHOD"]=="POST");
if ($posted && isset($_POST['cancelButton']))
	$posted = false;
$errormsg = array();
/* Quick exit with download */
if (isset($_POST['locButton']))
{
	$place_level = isset($_POST['place_level']) ? $_POST['place_level'] : "";
	$country = isset($_POST['ccode']) ? $_POST['ccode'] : "";

	$noplace = false;
	foreach ($place_level as $pl)
		if (!$pl) $noplace = true;
	if ($noplace)
		$errormsg[] = "Error: You must set the AviSys place type.";

	$nocountry = false;
	foreach ($country as $cc)
		if (!$cc) $nocountry = true;
	if ($nocountry)
		$errormsg[] = "Error: You must set the country code.";

	if (!empty($errormsg))
		$posted = false;
	else
	{	// echo "POST: "; print_r($_POST); die;
		$success = generate_stream();
		logger();
		if ($success)
			exit;
		else 
			$posted = false;
	}
}

?>
<!DOCTYPE HTML>
<html>

<head>
<title>eBird to AviSys checklist import</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<script src='functions.js'></script>
<link rel="stylesheet" type="text/css" href="eBird.css">
<meta name="description" content="eBird to AviSys checklist import will convert eBird checklist files (in csv format) into an AviSys stream file with which you can import the data into AviSys." />
<meta property="og:image" content="http://www.faintlake.com/images/ebirdtoavisys.png"/>
<meta property="og:title" content="eBird to AviSys checklist import"/>
<meta property="og:url" content="http://www.faintlake.com/ebirdtoavisys/"/>
<meta property="og:site_name" content="eBird to AviSys checklist import"/>
<meta property="og:type" content="website"/>
<meta property="og:description" content="This site provides an easy way to import checklists from eBird into AviSys."/>

   </head>

<body>
<h1>eBird to AviSys checklist import</h1>

<noscript>
<p><span class=error>
Notice: javascript is disabled in your browser.
This page is minimally usable without javascript, but some features require that you
enable javascript, or use a different browser that has javascript enabled.
See <a href="http://enable-javascript.com/" target="_blank">How to enable JavaScript</a>.</span>
</p>
</noscript>

<?php
/*
echo "<pre>\n";
echo "SESSION: "; print_r($_SESSION);
echo "POST: "; print_r($_POST);
echo "FILES: "; print_r($_FILES);
//print_r($_SERVER);
echo "</pre>\n";
*/

include dirname(dirname($_SERVER["SCRIPT_FILENAME"])) . '/txt/fbjdk.php'; 

if (empty($_POST) && empty($_FILES) && isset($_SERVER['CONTENT_LENGTH']))
{
	$length = $_SERVER['CONTENT_LENGTH'];
	$maxup = ini_get('post_max_size');
   echo "<p>Sorry, your upload is too large. It is $length bytes and the limit is $maxup.</p>";
	$posted = false;
}

if (!$posted)
{
	if (!empty($errormsg) || isset($_POST['cancelButton']))
	{
		foreach($errormsg as $emsg)
			printError($emsg);
		cleanWork();
	}
	upload_form();
}
else 
{
	$success = upload_files();
	if (!$success)
		upload_form();
}

likebutton();
?>

</body>
</html>
