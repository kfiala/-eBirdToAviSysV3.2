<?php
function upload_form()
{
	global $myself;

	$max_file_uploads = ini_get('max_file_uploads');
	if ($max_file_uploads)
		$upload_max = "(up to $max_file_uploads)";
	else
	{
		$upload_max = "";
		$max_file_uploads = 0;	// no limit
	}

	$heredoc = <<<HEREDOC
<p style="color:red;"><strong>Note:</strong>
To be current with eBird, install the <a href="http://avisys.info/update/">2017 Taxonomy Update for AviSys</a>.
</p>
<form enctype="multipart/form-data" method="POST" action="$myself" name="upform" style="max-width:40em;float:left;">
<fieldset>
<legend>File upload</legend>
<div id=buttons>
<label class="input" for="file0">Select one or more $upload_max eBird csv files to be converted for AviSys, then click "Upload".</label>
<input class="upload" id="file0" name="fileupload[0]" type="file"  style="width:35em" onclick="document.getElementById('uplbutn').style.display = 'block';filebutton(1,$max_file_uploads);return true;"/>
</div>
HEREDOC;
	echo $heredoc,PHP_EOL;
?>
<div class="conspicuous">
<p id="patience" style="display:none;text-align:center">Uploading... be patient</p>
</div>

<div id=uplbutn style="display:block">
<hr style="width:75%" />
<input type="submit" style="width:5.5em;" value="Upload" name="uploadButton"
	onclick="document.getElementById('patience').style.display = 'block'; return true;">
Click to upload the selected checklist(s).
</div>
<script>
document.getElementById('uplbutn').style.display = 'none';
</script>
</fieldset>
</form>
<!--
<fieldset style="float:left;max-width:20em;margin-left: 2em">
	<legend>Bug alert</legend>
	<p>Until 8 March 2017 this application had a minor bug that affected sightings from outside of the Lower 48 states
		of the US. Please read the <a href="bug.html" target="_blank">bug report</a> for full details, and an explanation of how your data can be
		easily and automatically corrected.</p>
</fieldset>-->
<br style="clear: both">
<h2>What it is</h2>
<?php
	$heredoc = <<<HEREDOC
<p>eBird to AviSys checklist import will convert one or more $upload_max eBird checklist files (in csv format) into an AviSys stream file 
with which you can import the data into AviSys (Version 6).</p>
HEREDOC;
	echo $heredoc,PHP_EOL;
?>
<h2>What you do</h2>
<ol>
<li>Download an eBird checklist (by going to My eBird->Manage My Observations->View or edit->Download).
Save the csv file on your computer.</li>
<li>In the &ldquo;File upload&rdquo; form above, select the downloaded checklist file and click &ldquo;Upload&rdquo;.
You can upload multiple eBird checklists in one batch. 
Each time you select a file, a new button for selecting another file is added.
When you've selected your last file, click &ldquo;Upload&rdquo;.
</li>
<li>On the next screen that you see, each eBird location that is in your input will be displayed.
If the corresponding AviSys place has a different name, you can enter the correct AviSys place name.
Then click the &ldquo;Do it!&rdquo; button that you will see.
<a href="howtodownload.html" target="_blank">More details on this screen</a> will be shown when you get there.
</li>
<li>Your AviSys stream file will be downloaded. Save it on your computer, then run Avisys to import it.</li>
<li>That's it!</li>
</ol>

<h2>About species names</h2>
<p>
If AviSys does not recognize a species name while importing a stream file, 
it will skip that sighting record. (It will tell you!)
You might want to edit the csv file before uploading it, to make sure that all species names 
that will go into the stream file
are ones that will be recognized by AviSys.
Important: if you edit the file in Excel, be sure to save it back in csv format, not as an xls or xlsx file!
</p>
<p>One prominent difference between eBird names and AviSys names is that eBird accepts certain names
with parenthetic qualifiers, e.g., &ldquo;Northern Flicker (Yellow-shafted)&rdquo;.
You don't need to worry about these cases!
eBird to AviSys checklist import will remove the parenthetic part of the name and insert it at the beginning of the AviSys comment.</p>
<p>Another difference is that eBird allows &ldquo;sp&rdquo; entries.
You can either delete these before importing, or just let AviSys ignore them.
</p>
<p>Finally, there might be some real differences between the nomenclature of eBird and AviSys, depending on timing of updates, etc.
If you know about any such differences in advance, you can alter names in your csv file before uploading.
Otherwise, AviSys will skip the unrecognized name during the import,
and you will need to enter the record manually, using the name that AviSys recognizes.</p>
<h2>Can I import from other sources?</h2>
<p>You should be able to import any validly formatted csv file that contains at least four columns headed
"species", "count", "location", and "date" or "observation date", and optionally a "comments" column and/or a "country" column. 
The order of columns and presence of other columns does not matter.
The date can be in most any recognizable format.</p>
<h3>What about eBird's &ldquo;Download My Data&rdquo;?</h3>
eBird provides a feature to <a href="http://ebird.org/ebird/downloadMyData" target="_blank">download all of your eBird data</a> in one file.
eBird to AviSys checklist import will process that file too!
However, if you are going to import a large amount of data all at once, you really should set up a test data set in AviSys 
and do a dry run of importing the data there before you commit to importing it to your real AviSys database.
<h3>What's a stream file?</h3>
<p>Read the tutorial on <a href="import.html" target="_blank">using AviSys stream files to import data</a> if you are not familiar with the process.</p>

<h2>eBird locations vs. AviSys places</h2>
<p>In many cases, your eBird location names may not match up exactly with your AviSys place names,
and you will need to manually enter your corresponding AviSys place name for each eBird location.
As a convenience, eBird to AviSys checklist import remembers AviSys places from one session to the next.
Once you "train" it by entering the AviSys place name for an eBird location, the name pairing will be saved for future use.
The Places data is stored locally in your browser. This has a couple of consequences.</p>
<ul>
<li>If you use more than one browser, you have to "train" each one separately.</li>
<li>
If you decide to clear private data from your browser, the Places data
is subject to deletion. If it gets deleted, you will have to repeat the training.
<ul>
<li>In the Firefox "Clear Recent History" dialog, 
selecting "Offline Website Data" will clear your Places data and you will have to recreate it.</li>
<li>In the Chrome "Clear browsing data" dialog, 
selecting "Cookies and other site data" will clear your Places.</li>
<li>
In Microsoft browsers (Edge and IE) you can't clear the data
under "Clear browsing data", you have to get down into developer
tools, which you probably are not going to do.
</li>
</ul>
</li>
</ul>	


<div style="float:right">
<p>
<a href="http://www.faintlake.com/ebirdtools/">More eBird tools</a>
</p>
<p>

<a href="/txt/email.html" 
	onblur='this.href="/txt/email.html"' 
	onmouseout='this.href="/txt/email.html"' 
	onfocus='this.href=qc()' 
	onmouseover='this.href=qc()'>Questions or comments?</a>
</p>
</div>
<?php
}
 ?>
