<?php
// curlCall sends server-side error messages by email to the address specified here:
$error_email = $_SERVER['SERVER_ADMIN'];

$downMessage = 'eBird may be down.';

/*
File apiKey.txt contains one line like:
<?php $apiKey = 'Your eBird api key here';?>
*/
require 'apiKey.txt';
?>