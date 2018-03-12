<?php
function generate_stream()
{
	global $myself;
/*
echo '<pre>';
echo "SESSION: "; print_r($_SESSION);
// echo "POST: "; print_r($_POST);
echo "</pre>\n";
*/

	$notes = array();

	$locationData = array();

	$incoming = dirname(__FILE__) . "/incoming";

	$place = $_POST['place'];
	$location = $_POST['location'];
	$place_level = $_POST['place_level'];
	$ccode = $_POST['ccode'];
	$glocom = $_POST['glocom'];
	$nsites = min(count($place),count($location),count($place_level),count($ccode),count($glocom));
	$merged = $_POST['merged'];

	$stream = array();
	ini_set("auto_detect_line_endings", "1"); // mac compatibility

	for ($i=0; $i<$nsites; $i++)
	{
		$locationData[$location[$i]] = 
			new eBirdLocation($location[$i],$place[$i],$place_level[$i],strtoupper($ccode[$i]),$glocom[$i]);
	}

	for ($w=0; $w<count($_SESSION['eBird']['file']); $w++)
	{
		$workname = $_SESSION['eBird']['file'][$w];
		$workfile = "$incoming/$workname.csv";
		$fh = @fopen($workfile,"r");
		if ($fh)
		{
			$headings = fgetcsv ( $fh );
			for ($h=0; $h<count($headings); $h++)
			{
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

					case "time":
					case "start time":	$time_column = $h; break;
					default:
				}
			}
			if (!isset($species_column) || !isset($count_column) || !isset($location_column)
				|| !isset($date_column) /* || !isset($comments_column) */ )
				die("File is not in expected format");
			while (($sighting = fgetcsv($fh)) !== FALSE)
			{
				$location = $sighting[$location_column];
				$location = validUTF8($location);
				if ($merged)
					$locationIndex = $location;
				else
					$locationIndex = $location . $sighting[$date_column].$sighting[$time_column];
				$location = $locationData[$locationIndex];

				if	(isset($comments_column) && isset($sighting[$comments_column]))
					$comments = $sighting[$comments_column];
				else $comments = "";
				if ($location->comment)
					$comments = "$location->comment $comments";
				if (strlen($comments) > 80)
				{
					$fn = new FieldNote($comments);
					$notes[] = $fn;
					$fnid = $fn->id;
				}
				else $fnid = 0;

				$species = $sighting[$species_column];
				$lparen = strpos($species,'(',0);
				if ($lparen)
				{
					$qualifier = trim(substr($species,$lparen));
					$species = trim(substr($species,0,$lparen));
					$comments = "$qualifier $comments";
				}

				$number = $sighting[$count_column];
				if ($number == "X")
					$number = 1;

				$stream[] = new StreamEntry($species,
					$sighting[$date_column],
					$location->AviSys,
					$location->level,
					$number,
					$location->country,
					$comments,
					$fnid
					);
			}

			fclose($fh);
		}
		else
		{
			return false;
		}
	}

/*
echo "<pre>locationData:\n";
//echo "SESSION: "; print_r($_SESSION);
print_r($locationData);
echo "</pre>\n";
*/

	$str_file = "$incoming/$workname.str";
	$handle = fopen($str_file,"w");
	foreach(	$stream as $data )
		fwrite($handle,$data->toStream());
	fclose($handle);

// If uploading one file, use same filename for stream file. If multiple uploads, name the stream file AviSys.
	if (count($_SESSION['eBird']['file']) == 1)
		$streamfile = $_SESSION['eBird']['file'][0];
	else
		$streamfile = 'AviSys';

	if (count($notes))
	{
		$notes_file = "$incoming/$workname.fnr";
		$handle = fopen($notes_file,"wb");
		foreach( $notes as $data )
			fwrite($handle,$data->toStream());
		fclose($handle);
		$zip = new ZipArchive();
		$zipfile = "$incoming/$streamfile.zip";
		if ($zip->open($zipfile,ZipArchive::CREATE) !== TRUE)
			die("cannot open $zipfile");
		$zip->addFile($str_file,"$streamfile.str");
		$zip->addFile($notes_file,"$streamfile.fnr");
		$zip->close();

		$filesize = filesize($zipfile);
		header("Content-type: application/zip");
		header("Content-Length: $filesize");
		header('Content-Transfer-Encoding: binary');
		header('Content-Disposition: attachment; filename="'.$streamfile.'.zip"');
		readfile($zipfile);
		unlink($zipfile);
		unlink($notes_file);
	}
	else
	{
		$filesize = filesize($str_file);
		header("Content-type: application/octet-stream");
		header("Content-Length: $filesize");
		header('Content-Transfer-Encoding: binary');
		header('Content-Disposition: attachment; filename="'.$streamfile.'.str"');
		readfile($str_file);
	}
	cleanWork();
	unlink($str_file);
	return true;
}
?>
