<?php
require_once 'alphaOnly.php';

function fetch_checklists()
{
	unset($_SESSION[APPNAME]);

	$checklists = array();
	$errormsg = array();
	$infomsg = array();
	$nullmsg = "Please enter one or more checklists or trip reports and try again.";

	if (empty($_POST['checklists']))
	{
		$errormsg[] = $nullmsg;
		return $errormsg;
	}

	$submissionIDs = array();
	$rawInput = $_POST['checklists'];
	$rawExcludes = $_POST['excludes'];

	$_SESSION[APPNAME]['rawInput'] = $rawInput;
	$_SESSION[APPNAME]['rawExcludes'] = $rawExcludes;
	$_SESSION[APPNAME]['excluded'] = array();

	$rawInput = preg_replace("/[,\r\n]/"," ",$rawInput);	// Change all commas or newlines to space
	$rawInput = preg_replace("/ +/"," ",$rawInput);			// Change multiple spaces to single space
	$rawInput = explode(' ',$rawInput);
	foreach (array_map('trim',$rawInput) as $input)
	{
		if ($input == '')
			continue;
		$input = explode('?',$input)[0];
		$pathElement = explode('/',$input);
		if (count($pathElement) == 1)
		{
			$pathTypes[] = is_numeric($input) ? 'tripreport' : 'checklist';
			$submissionIDs[] = $input;
		}
		else
		{
			$pcount = 0;
			$valid = false;
			foreach($pathElement as $element)
			{
				if ($element == 'checklist' || $element == 'tripreport')
				{
					$pathTypes[] = $element;
					$submissionIDs[] = $pathElement[$pcount+1];
					$valid = true;
					break;
				}
				$pcount++;
			}
			if (!$valid)
			{
				$errormsg[] = "$input is not valid.";
			}
		}
	}

	if (!empty($errormsg))
		return $errormsg;

//	Make the list of species to be excluded from checklists.
	$excludes = preg_replace("/[\r\n]/","~",$_POST['excludes']);
	$excludes = explode('~',$excludes);
	if (count($excludes))
		for ($i=count($excludes)-1; $i>=0; $i--)
		{
			$excludes[$i] = trim($excludes[$i]);
			if (!$excludes[$i])
				unset($excludes[$i]);
			else
			{
				$excludes[$i] = alphaOnly($excludes[$i]);
			}
		}

	$viewURL = 'https://ebird.org/ws2.0/product/checklist/view/';
	$tripURL = 'https://ebird.org/tripreport-internal/v1/checklists/';
	$i=0;	
	foreach ($submissionIDs as $submissionID)
	{
		$isTripReport = $pathTypes[$i++] == 'tripreport';

		if ($isTripReport)
		{
			$json = curlCall($tripURL . $submissionID,false);

			$trip_info = json_decode($json);
			foreach ($trip_info as $checklist_info)
			{
				$checklistObject = getChecklistObject($viewURL, $checklist_info->subId, $errormsg);
				appendChecklist($checklistObject,$checklists,$excludes);
			}
		}
		else
		{
			$checklistObject = getChecklistObject($viewURL, $submissionID, $errormsg);
			appendChecklist($checklistObject,$checklists,$excludes);
		}
	}

	if (empty($checklists))
	{
		if (empty($errormsg))
			$errormsg[] = $nullmsg;
		return $errormsg;
	}

	// Sort checklists by date
	usort($checklists,'dateCompare');

	if (isset($_POST['merged']))
	{
		$merged = $_POST['merged'];
		$_SESSION[APPNAME]['merged'] = $merged ? 1 : 0;
	}
	else if (isset($_SESSION[APPNAME]['merged']))
		$merged = $_SESSION[APPNAME]['merged'];
	else
		$merged = true;

	$locations = array();

	foreach($checklists as $checklist)
	{
		$location = validUTF8($checklist->location);

		$Avplace = $location;
		$Avlevel = " ";

		$locationIndex = $merged ? $location : $location . $checklist->effort;
		if (!isset($locations[$locationIndex]))
		{
			$locations[$locationIndex] = new eBirdLocation($location,$Avplace,$Avlevel,$checklist->country,$checklist->state);
			if (!$merged)
				$locations[$locationIndex]->addEffort($checklist->effort);
		}
	}
	if (!empty($errormsg))
		return $errormsg;

	$_SESSION[APPNAME]['infomsg'] = $infomsg;
	$_SESSION[APPNAME]['locations'] = $locations;

	$_SESSION[APPNAME]['checklists'] = $checklists;
	return $errormsg;
}

function dateCompare($a,$b)
{
   return strcmp($a->obsDt, $b->obsDt);
}
   
function getChecklistObject($URL,$submissionID, &$errormsg)
{
	$obj = curlCall($URL . $submissionID);

	$checklistObject = new CheckList($obj,$submissionID);
	if ($checklistObject->error)
	{
		$errormsg[] = $checklistObject->errorText;
		return;
	}

//	if (empty($checklistObject->obs))
//	{
//		$errormsg[] = "Note: No observations were found for checklist $submissionID.";
//	}

	return $checklistObject;

}
function appendChecklist($checklistObject,&$checklists,$excludes)
{
	if (is_object($checklistObject))
	{
		//		Remove excluded species from this checklist
		$checklistObject->exclude($excludes);
		$checklists[] = $checklistObject;
	}
}
?>