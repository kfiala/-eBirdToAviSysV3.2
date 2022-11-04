<?php
function list_results()
{
	global $myself;

	$_SESSION[APPNAME]['maxline'] = __FILE__ . ' ' . __LINE__;
?>
<!DOCTYPE HTML>
<html lang="en">

<head>
<title>eBird to AviSys checklist import</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<link rel="stylesheet" type="text/css" href="eBird.css">
<script src='ebtoav.js'></script>
</head>

<body>
<p>The following eBird locations have been found in your upload.
As necessary, change each location name to the corresponding AviSys place name.</p>
<p><strong>Important: The AviSys place names are case-sensitive!</strong></p>

<div class='leftdiv'>Version 3.2</div>
<div class='rightdiv'>
Initially, the AviSys place name is filled in with the eBird location name.
You can change this initial name to the correct AviSys place name if it is different from the eBird name.
When you do this, the eBird location name is automatically filled into the global comment.
If you don't want this to happen, uncheck the "Autofill" checkbox.
</div>

<?php
	if (!empty($_SESSION[APPNAME]['infomsg']))
	{
		foreach($_SESSION[APPNAME]['infomsg'] as $info)
			printError($info);
	}
	$i = 0;
	$heredoc = <<<HEREDOC
<form method="POST" action="$myself" style="width:55em">
HEREDOC;
	echo $heredoc;
	$merged = $_SESSION[APPNAME]['merged'];
	if ($merged)
	{
		$legendLabel = 'Location';
		$eachComment = "each comment for this location";
	}
	else
	{
		$legendLabel = 'Checklist';
		$eachComment = "each comment for this checklist";
	}

	$_SESSION[APPNAME]['maxline'] = __FILE__ . ' ' . __LINE__;

	foreach ($_SESSION[APPNAME]['locations'] as $locationIndex => $ebirdloc)
	{
		$locnum = $i+1;
		$eBird = htmlspecialchars($ebirdloc->eBird);
		$AviSys = htmlspecialchars($ebirdloc->AviSys);
		$levtype = htmlspecialchars($ebirdloc->level);
		$country = htmlspecialchars($ebirdloc->country);
		$state = htmlspecialchars($ebirdloc->state);

		if ($merged)
			$effort = '';
		else
		{
			$effort = htmlspecialchars($ebirdloc->effort);
		}


		if (trim($levtype) == '') $levtype = "Site";	// Default
		if ($levtype == "Site")		$siteselected = "selected"; else 	$siteselected = "";
		if ($levtype == "City")		$cityselected = "selected"; else		$cityselected = "";
		if ($levtype == "County")	$countyselected = "selected"; else	$countyselected = "";
		if ($levtype == "State")	$stateselected = "selected"; else	$stateselected = "";
		if ($levtype == "Nation")	$nationselected = "selected"; else	$nationselected = "";

		$heredoc = <<<HEREDOC
<fieldset>
<legend>$legendLabel $locnum</legend>
<label style="width: 15em">eBird location: <span id="eBirdLocation$i">$eBird</span><br>AviSys place:
<input oninput="placeEdit('$i')" onblur="savePlace('$i')" name="place[$i]" id="place$i" type="text" value="$AviSys" style="width:26em" autofocus /></label>
<input name="location[$i]" type="hidden" value="$locationIndex" >
<label style="margin-left:1em">Type:
<select name="place_level[$i]" id="place_level$i" style="width:6em" onchange="place_sel($i)">
<option value="">Select:</option>
<option value="Site" $siteselected>Site</option>
<option value="City" $cityselected>City</option>
<option value="County" $countyselected>County</option>
<option value="State" $stateselected>State</option>
<option value="Nation" $nationselected>Nation</option>
</select></label>
<input type="hidden" name="ccode[$i]" value="$country">
<input type="hidden" name="scode[$i]" value="$state">
<span id=placewarn[$i] class="error" style="display:none;margin-left:30em">Please select the location type</span>
<span class="error" id="toolong$i" style="display:none;"></span><br>
$effort
<br><label>Global comment:
<input name="glocom[$i]" id="glocom$i" type="text" value="" style="margin-top:1em;width:44em" maxlength=80
placeholder="Optional: info to insert in $eachComment" onblur="glocomSave('$i')"></label>
<label><input id="autofill$i" type="checkbox" name="autofill$i">Autofill</label>
<script>lookupPlace($i);placeToolong($i);</script>
</fieldset>
HEREDOC;
		echo $heredoc;
		$i++;
	}
?>
<br style="clear:both" >
<?php
echo '<input type="submit" style="width:7em" value="Download" id="subbut" name="locButton" onclick="return checkType();">';
echo "<input type=hidden name='merged' value='$merged'>";
?>
<input type="submit" style="width:7em" value="Cancel" id="canbut" name="cancelButton" />
<span id=donemsg style="display:none">
Processing complete, click Reset if you'd like a blank slate to do another.
<br><input type="submit" style="width:7em" value="Retry" name="retryButton">
Click Retry if you found errors that you need to correct.
</span>
</form>
<?php
if (!empty($_SESSION[APPNAME]['excluded']))
{
	echo "<h2>These species will be excluded from these checklists:</h2><ul>";
	foreach ($_SESSION[APPNAME]['excluded'] as $pair)
	{
		echo "<li><a href=https://ebird.org/checklist/{$pair['subId']} target=_blank>{$pair['subId']}</a>: {$pair['species']}</li>";
	}
	echo "</ul>";
}
$_SESSION[APPNAME]['maxline'] = __FILE__ . ' ' . __LINE__;
?>
<div id=advice>
<h2>How to use this form</h2>
<p>On this screen you see each eBird location that is in your input.
<?php include "download.php"; ?>
</div>
<?php
	return;
?>
</body> // list_results
</html>
<?php } ?>
