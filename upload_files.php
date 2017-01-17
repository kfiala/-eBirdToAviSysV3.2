<?php
function upload_files()
{
	global $myself;

	unset($_SESSION['eBird']['file']);
	unset($_SESSION['eBird']['streamfile']);

	$numfiles = count($_FILES['fileupload']['name']);
	$uploadcount = 0;

	if ($numfiles == 0)
	{
		echo "<p>You did not upload any file!</p>",PHP_EOL;
		return(false);
	}

	$incoming = dirname(__FILE__) . "/incoming";
	$_SESSION['eBird']['file'] = array();

	$anyError = FALSE;
	$totalbytes = 0;
	$locations = array();

	ini_set("auto_detect_line_endings", "1"); // mac compatibility

	for ($i=0; $i<$numfiles; $i++)
	{
		$filename = $_FILES['fileupload']['name'][$i];
		$filesize = $_FILES['fileupload']['size'][$i];
		$filetype = $_FILES['fileupload']['type'][$i];
		$filetemp = $_FILES['fileupload']['tmp_name'][$i];
		$fileerror= $_FILES['fileupload']['error'][$i];

		if ($fileerror != UPLOAD_ERR_OK)
		{
			switch ($fileerror)
			{
				case UPLOAD_ERR_INI_SIZE:
				case UPLOAD_ERR_FORM_SIZE:		$emsg = "The file is too large."; break;
				case UPLOAD_ERR_PARTIAL:		$emsg = "The file was only partially uploaded."; break;
				case UPLOAD_ERR_NO_FILE:		$emsg = "No file was uploaded."; break;
				case UPLOAD_ERR_NO_TMP_DIR:	$emsg = "Missing a temporary folder."; break;
				case UPLOAD_ERR_CANT_WRITE:	$emsg = "Failed to write file to disk."; break;
				case UPLOAD_ERR_EXTENSION:		$emsg = "A PHP extension stopped the file upload"; break;
				default: $emsg = "Unknown error $fileerror";
			}
			if ($fileerror != UPLOAD_ERR_NO_FILE)	
			{
				printError("Sorry, file $filename could not be received. An error occurred: $emsg");
				$anyError = TRUE;
			}
			continue;
		}

		$full_filename = $filename;
		$path_parts = pathinfo($filename);
		$filename = $path_parts['filename'];
		$extension = $path_parts['extension'];
//		if ($filetype != "text/csv" && $filetype != "application/csv" && $filetype != "application/vnd.ms-excel" )
		if (strcasecmp("csv",$extension))
		{
			printError("Sorry, $filetype file $full_filename could not be received. Only csv files are supported.");
			$anyError = TRUE;
			continue;
		}

		$path_parts = pathinfo($filename);
		$workname = $path_parts['filename'];
		$workfile = "$incoming/$workname.csv";
		$_SESSION['eBird']['file'][] = $workname;
		
		/* If uploading one file, use same filename for stream file. If multiple uploads,
		   name the stream file AviSys. */
		if ($i==0)
			$_SESSION['eBird']['streamfile'] = $filename;
		else
			$_SESSION['eBird']['streamfile'] = 'AviSys';

		if (move_uploaded_file($filetemp,$workfile))
		{
//			echo "<p>Received {$_FILES['fileupload']['name'][$i]} as $workfile, size $filesize, from tempfile $filetemp.</p>",PHP_EOL;
			$uploadcount++;
		}
		else
		{
			printError("Error: Upload failed--$full_filename could not be copied!");
			$anyError = TRUE;
			continue;
		}


		/* Process the column headings, and get the locations. */
		$fh = fopen($workfile,"r") or die("<p>Could not open work file</p>");
		if ($fh)
		{
			unset($species_column);
			unset($count_column);
			unset($location_column);
			unset($date_column);
			unset($comments_column);
			unset($country_column);
			
			$headings = fgetcsv ( $fh );
// echo "<ol>";
			for ($h=0; $h<count($headings); $h++)
			{
//				echo "<li>$headings[$h]</li>";
				$head = strtolower($headings[$h]);
				switch ($head)
				{
					case "common name":
					case "species":	$species_column = $h; break;
					case "count":		$count_column = $h; break;
					case "location":	$location_column = $h; break;
					case "date":
					case "observation date":	$date_column = $h; break;
					case "species comments":
					case "comments":	$comments_column = $h; break;
					case "country":
					case "state/province":
					case "s/p":			$country_column = $h; break;
					default:
				}
			}
// echo "</ol>";
			if (!isset($species_column) || !isset($count_column) || !isset($location_column)
				|| !isset($date_column) /* || !isset($comments_column) */ )
			{
				printError("Error: Your csv file $full_filename is not in the expected format. Make sure you download from My eBird->Manage My Observations->View or Edit->Download.");
				fclose($fh);
				$anyError = TRUE;
				continue;
			}		

			$nsightings = 0;
			while (($sighting = fgetcsv($fh)) !== FALSE)
			{
				$date = $sighting[$date_column];
				$location = $sighting[$location_column];
				$location = validUTF8($location);
				if (isset($country_column))
					$country = $sighting[$country_column];
				else
					$country = "US";
				if (isset($_SESSION['eBird']['place'][$location]))
				{	// $_SESSION['eBird']['place'] would only have been set by a previous stream generation.
					// Here we pick up any settings of AviSys location, level, or country that the user made then.
					$Avplace = $_SESSION['eBird']['place'][$location]->AviSys;
					$Avlevel = $_SESSION['eBird']['place'][$location]->level;
					$Avcountry = $_SESSION['eBird']['place'][$location]->country;
				}
				else
				{
					$Avplace = $location;
					$Avlevel = " ";
					$Avcountry = $country;
				}
				if (!isset($locations[$location]))
					$locations[$location] = new eBirdLocation($location,$Avplace,$Avlevel,$Avcountry);
				$nsightings++;
			}
			$empty_file = ($nsightings==0);

			fclose($fh);
			if ($empty_file)
			{
				printError("Error: There are no sightings in file $full_filename!");
				$anyError = TRUE;
				continue;
			}
		}
	}
	
	if ($uploadcount == 0)
	{
		printError("Error: No files were successfully uploaded. Be sure to click the Browse... button and select a csv file.");
		return(false);
	}
	if ($anyError)
	{
		cleanWork();
		printError("Error: A file was not successfully uploaded. Please correct errors and try again.");
		return(false);
	}

?>
<p>The following eBird locations have been found in your upload.
As necessary, change each location name to the corresponding AviSys place name.</p>
<p><strong>Important: The AviSys place names are case-sensitive!</strong></p>
<?php
	$i = 0;
	$heredoc = <<<HEREDOC
<form method="POST" action="$myself" style="width:55em">
HEREDOC;
	echo $heredoc;
	foreach ($locations as $ebirdloc)
	{
		$locnum = $i+1;
		$eBird = $ebirdloc->eBird;
		$AviSys = $ebirdloc->AviSys;
		$levtype = $ebirdloc->level;
		$country = substr($ebirdloc->country,0,2);
//		if ($levtype == 0) $levtype = " ";
		if ($levtype == "Site")		$siteselected = "selected"; else 	$siteselected = "";
		if ($levtype == "City")		$cityselected = "selected"; else		$cityselected = "";
		if ($levtype == "County")	$countyselected = "selected"; else	$countyselected = "";
		if ($levtype == "State")	$stateselected = "selected"; else	$stateselected = "";
		if ($levtype == "Nation")	$nationselected = "selected"; else	$nationselected = "";

		$heredoc = <<<HEREDOC
<fieldset>
<legend>Location $locnum</legend>
<label style="width: 15em">eBird location: $eBird<br>AviSys place:
<input name="place[$i]" type="text" value="$AviSys" style="width:26em" maxlength="36" autofocus /></label>
<input name="location[$i]" type="hidden" value="$eBird" >

<label style="margin-left:1em">Type:
<select name="place_level[$i]" id="place_level[$i]" style="width:6em" onchange="place_sel($i)">
<option value="">Select:</option>
<option value="Site" $siteselected>Site</option>
<option value="City" $cityselected>City</option>
<option value="County" $countyselected>County</option>
<option value="State" $stateselected>State</option>
<option value="Nation" $nationselected>Nation</option>
</select></label>

<label style="margin-left:1em">Country code:<input name="ccode[$i]" id="ccode[$i]" type="text" value="$country" style="width:2em" maxlength="2" oninput="country_fill($i)"></label>
<span id=placewarn[$i] class="error" style="display:none;margin-left:30em">Please select the location type</span>
<span id=cntrywarn[$i] class="error" style="display:none;margin-left:28em">Please fill in the country code (e.g., US)</span>
<br>
<label>Global comment:
<input name="glocom[$i]" type="text" value="" style="margin-top:1em;width:44em" maxlength=80
placeholder="Optional: info to insert in each comment for this location"></label>

</fieldset>
HEREDOC;
		echo $heredoc;
		$i++;
	}
?>
<br style="clear:both" >
<input type="submit" style="width:7em" value="Do it!" id="subbut" name="locButton" onclick="return checkType();">
<input type="submit" style="width:7em" value="Cancel" id="canbut" name="cancelButton" />
<span id=donemsg style="display:none">Processing complete, click Reset if you'd like to do another.</span>
</form>
<div id=advice>
<h2>How to use this form</h2>
<p>On this screen you see each eBird location that is in your input.
<?php include "download.php"; ?>
</div>
<?php
	return(True);
}
?>
