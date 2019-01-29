<?php
class CheckList
{
	function __construct($cornell,$submissionID)
	{
		if (isset($cornell->errors))
		{
			$this->error = true;
			if ($cornell->errors[0]->title == 'Field subId of checklistBySubIdCmd: subId is invalid.')
				$this->errorText = "$submissionID does not appear to be a valid eBird checklist.";
			else
				$this->errorText = "An error occurred while attempting to fetch checklist $submissionID.";
			return;
		}

		foreach(get_object_vars($cornell) as $property => $value)
		{
			$this->$property = $value;
		}
		$this->location = getLocation($this->locId);


		if (isset($this->effortDistanceKm) && $this->effortDistanceKm != '')
		{
			$km = $this->effortDistanceKm;
			if ($this->effortDistanceEnteredUnit == 'mi')
				$distance = sprintf('%.2f',$km * 0.62137119224) . ' miles';
			else
				$distance = "$km km";	
		}
		$this->startTime = $this->obsDt . ' - ' . $this->durationHrs . ' hours';
		$this->effort = $this->startTime;
		if (isset($distance))
			$this->effort .=  " - $distance";
		//Jan 27, 2019 – 8:51 AM – 2 hour(s), 2 minute(s) – 1.5 mile(s)
	}

	function __toString()
	{
		global $speciesLookup;
		$heading = "Checklist for $this->location (".explode('-',$this->subnational1Code)[0].") on $this->effort";
		foreach($this->obs as $observationObject)
		{
			$observation = get_object_vars($observationObject);
			$comName = $speciesLookup[$observation['speciesCode']];
			$line = $comName . ', ' . $observation['howManyStr'];
			if (isset($observation['comments']))
				$line .= ', ' . $observation['comments'];
			$checklist[] = $line;
		}
		return "$heading<br>" . implode('<br>',$checklist).'<br>';
	}
}

?>