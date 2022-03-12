<?php
session_start();
// From https://www.sitepoint.com/tracking-upload-progress-with-php-and-javascript/

const APPNAME = 'ebirdtoavisys';

if (isset($_SESSION[APPNAME]['nChecklists'])) 
{
	$current = count($_SESSION[APPNAME]['checklists']);
	$total = $_SESSION[APPNAME]['nChecklists'];
	$output = $current < $total ? ceil($current / $total * 100) : 100;
	echo "$current of $total"; // $output;
}
else
{
	echo 100;
}
?>