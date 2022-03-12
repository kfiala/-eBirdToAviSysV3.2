<?php
require_once 'alphaOnly.php';

function fetch_checklists()
{
	global $myself;
	unset($_SESSION[APPNAME]);

	$success = true;

	$checklists = array();
	$errormsg = array();
	$infomsg = array();

	if (empty($_POST['checklists']))
	{
		$errormsg[] = "Please enter one or more checklist names in &ldquo;Checklist input&rdquo; and try again.";
		return $errormsg;
	}

	$rawInput = $_POST['checklists'];
	$rawExcludes = $_POST['excludes'];

	$_SESSION[APPNAME]['rawInput'] = $rawInput;
	$_SESSION[APPNAME]['rawExcludes'] = $rawExcludes;
	$_SESSION[APPNAME]['excluded'] = array();

	$rawInput = preg_replace("/[,\r\n]/"," ",$rawInput);	// Change all commas or newlines to space
	$rawInput = preg_replace("/ +/"," ",$rawInput);			// Change multiple spaces to single space
	$rawInput = explode(' ',$rawInput);
	$_SESSION[APPNAME]['nChecklists'] = count($rawInput);

	$submissionIDs = array_map('trim',$rawInput);

	$urlPath = 'https://ebird.org/view/checklist';
	for ($i=0; $i<count($submissionIDs); $i++)
	{
		$input = $submissionIDs[$i];
		$lastslash = strrpos($input,'/');
		if ($lastslash === false)
			$submissionIDs[$i] = $input;
		else
			$submissionIDs[$i] = substr($input,$lastslash+1);
	}
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
	foreach ($submissionIDs as $submissionID)
	{
		if (!$submissionID)
			continue;

		$URL = $viewURL . $submissionID;
		$obj = curlCall($URL);

		if (isset($obj->errors))
		{
			if ($obj->errors[0]->title == 'Field subId of checklistBySubIdCmd: subId is invalid.')
				$errormsg[] = "&ldquo;$submissionID&rdquo; does not appear to be a valid eBird checklist. Please correct and retry.";
			else
				$errormsg[] = "An error occurred while attempting to fetch checklist $submissionID. Please correct and retry.";
			continue;
		}

		if (empty($obj) || empty($obj->obs))
		{
			$infomsg[] = "NOTE: No observations were found in checklist $submissionID.";
		}

		$checklistObject = new CheckList($obj,$submissionID);
		if (isset($checklistObject->error))
		{
			$errormsg[] = $checklistObject->errorText;
			continue;
		}
//		Remove excluded species from this checklist
		$checklistObject->exclude($excludes);

		$checklists[] = $checklistObject;
	}

	$_SESSION[APPNAME]['checklists'] = $checklists;

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
	$_SESSION[APPNAME]['results'] = true;
	header("Location: $myself");

	exit;
}
?>