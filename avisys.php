<?php
class eBirdLocation
{
	var $eBird, $AviSys, $level, $country, $comment;

	function __construct($eBirdName, $AviSysName, $level, $country, $comment="")
	{
		$this->eBird = $eBirdName;
		$this->AviSys = $AviSysName;
		$this->level = $level;
		$this->country = $country;
		$this->comment = $comment;
	}	
}

class StreamEntry
{
	var $species_code, $field_note, $dec_date, $place_level, $country_code;
	var $comment, $species_name, $place, $count;

	function __construct($species_name, $date, $place, $place_level, $count=1, $country_code="US", $comment="", $field_note=0)
	{
		if ( mb_detect_encoding($place, "UTF-8", true) ) // Encoding cannot be UTF-8 for AviSys
			$place = mb_convert_encoding($place, "Windows-1252", "UTF-8");

		$this->species_name=trim($species_name);
		$this->place=$place;	// was: trim($place);
		$this->place_level = trim($place_level);
		$this->count = trim($count);
		$this->country_code = trim($country_code);
		$this->comment = trim($comment);
		$this->field_note = trim($field_note);

		$this->species_code = 1000;
/*	From the strtotime documentation:
Dates in the m/d/y or d-m-y formats are disambiguated by looking at the separator between the various components: if the separator is a slash (/), then the American m/d/y is assumed; whereas if the separator is a dash (-) or a dot (.), then the European d-m-y format is assumed. 

Unfortunately eBird uses the American style with dash separators sometimes, so I have to change dashes to slashes.
*/
		if (strlen($date) == 10)
		{
			$newdate = preg_replace("/(\d{2})-(\d{2})-(\d{4})/","$1/$2/$3",$date);
			if ($newdate != $date)
				$date = $newdate;
		}

		/* I've seen a case where a user had dates in the format yyyy.mm.dd, so fix that too,
			by changing to yyyy-mm-dd. */
		if (strlen($date) == 10)
		{
			$newdate = preg_replace("/(\d{4})\.(\d{2})\.(\d{2})/","$1-$2-$3",$date);
			if ($newdate != $date)
				$date = $newdate;
		}		

		$date = strtotime($date);
		$year = date("Y",$date) - 1930;
		$daystring = date("md",$date);
		$date = $year . $daystring;
		$this->dec_date = $date;

		if ($this->species_name) $this->species_name = substr($this->species_name,0,36);
		if ($this->place) $this->place = substr($this->place,0,30);
		if ($this->comment) $this->comment = substr($this->comment,0,80);
		if ($this->country_code) $this->country_code = substr($this->country_code,0,2);

		switch($this->place_level)
		{
			case "Site":	$this->place_level = 0; break;
			case "City":	$this->place_level = 1; break;
			case "County":	$this->place_level = 3; break;
			case "State":	$this->place_level = 5; break;
			case "Nation":	$this->place_level = 7; break;
			default:	$this->place_level = 0;
		}

	}

/*
unsigned little-endian long:	"V":	0
unsigned little-endian short:	"v":	species code
unsigned little-endian short:	"v":	field note id
unsigned little-endian short:	"v":	0
										"V":	date
unsigned char						"C":	15
unsigned char						"C":	place level
unsigned char						"C":	2
SPACE-padded string				"A2":	country code
hex string, high nibble first	"H10":	0x000d200800
unsigned little-endian long:	"V":	0

unsigned char						"C":	comment length		
SPACE-padded string				"A80":	comment (80 chars)	

unsigned little-endian short:	"v":	count					
unsigned char						"C":	species name length	
SPACE-padded string				"A36":	species name (36 chars)
unsigned char						"C":	place name length	
SPACE-padded string				"A30":	place name (30 chars)
SPACE-padded string				"A4":	"END!"
*/
	function toStream()
	{
		$stream = pack("VvvvVCCCA2H10VCA80vCA36CA30A4",
			0,$this->species_code,$this->field_note,0,$this->dec_date,255,$this->place_level,
			2, $this->country_code, "000d200800", 0, strlen($this->comment), str_pad($this->comment,80), $this->count,
			strlen($this->species_name), str_pad($this->species_name,36), strlen($this->place), str_pad($this->place,30),
			"END!");
		return $stream;
	}
		
}
class FieldNote
{
	var $id, $comment;

	function __construct($comment)
	{
	   $file = dirname(__FILE__).'/incoming/fncounter.txt';
   	$fp = fopen($file,"r");
	   if (flock($fp, LOCK_EX))
   	{
      	$counter = (int)fgets($fp);
	      fclose($fp);
			if ($counter < 2147483647)
				$counter++;
			else
				$counter = 0;
	      $fp = fopen($file,"w");
   	   fwrite($fp,"$counter\n");
      	fflush($fp);            // flush output before releasing the lock
	      flock($fp, LOCK_UN);    // release the lock
   	}
	   fclose($fp);

		$this->id = $counter;
		$this->comment = $comment;
	}

	function toStream()
	{
		$comment = wordwrap($this->comment,72);
		$line = explode("\n",$comment);
		for ($i=0; $i<count($line); $i++)
		{
			$line[$i] = trim($line[$i]);
			$line[$i] = pack("CA124",strlen($line[$i]),$line[$i]);
		}
		$stream = pack("VV",0,$this->id);
		$lines = min(count($line),60);
		for ($i=0; $i<$lines; $i++)
			$stream .= $line[$i];
		$nullline = pack("CA124",0," ");
		for ($i=$lines; $i < 60; $i++)
			$stream .= $nullline;
		return $stream;
	}
}

?>
