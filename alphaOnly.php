<?php
function alphaOnly($string)
{ // Convert to uppercase and remove spaces, hyphens, quotes, ampersand
	$string = strtoupper($string);
	$string = str_replace(array(' ','-',"'",'&',"\t"),'',$string);
	return($string);
}
?>