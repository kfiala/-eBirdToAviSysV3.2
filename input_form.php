<?php
function input_form()
{
	global $myself;
?>
<p style="color:red;"><strong>Note:</strong>
To be current with eBird, install the <a href="http://avisys.info/update/">current Taxonomy Update for AviSys</a>.
</p>

<form method="POST" action="<?php echo $myself;?>" name="upform">
<fieldset style="max-width:40em;float:left;">
<legend>Checklist input</legend>


<div id=buttons>
<textarea id="maininput" name="checklists" cols="65" rows="6">
<?php
	if (isset($_SESSION[APPNAME]['rawInput']))
		echo $_SESSION[APPNAME]['rawInput'];

	$merged = isset($_SESSION[APPNAME]['merged']) ? $_SESSION[APPNAME]['merged'] : true;
	$mergedON = $merged ? 'checked' : '';
	$mergedOFF = $merged ? '' : 'checked';
?>
</textarea>
</div>







<div class="conspicuous">
<p id="patience" style="display:none;text-align:center">Fetching checklists from eBird... be patient</p>
</div>
<div id="bar_blank"><div id="bar_color"></div></div><div id="status"></div>

<input id="submitButton" type="submit" style="width:5.5em;" value="Go!" name="fetchButton">
Click to fetch the specified checklist(s) from eBird.
<script>
	var button = document.getElementById('submitButton');
	button.addEventListener('click',setPatience,true);
	button.addEventListener('click',clearErrMsgs,true);
	function setPatience() {
		document.getElementById('patience').style.display='block';
	};
	function clearErrMsgs() {
		let emsg = document.getElementsByClassName('error');
		for (const i=0; i<emsg.length; i++) {
			emsg[i].style.display = 'none';	
		}
	}
</script>

</fieldset>

<fieldset style="float: left">
	<legend>Options</legend>
	Summarize by<br>

<label><input type="radio" name="merged" value="1" <?php echo $mergedON;?>/>Location</label>
<br>
<label><input type="radio" name="merged" value="0" <?php echo $mergedOFF;?> />Checklist</label>
<br><a href="#summarize"><span onmouseover="sumHI();">What's this?</span></a>
<script>function sumHI(){document.getElementById('summarize').style.color='red';}</script>
</fieldset>
<fieldset style="float:left">
	<legend>Excludes</legend>
	<textarea id="excludes" name="excludes" cols=40 rows=4 onblur="saveExcludes()">
<?php
	if (isset($_SESSION[APPNAME]['rawExcludes']))
		echo $_SESSION[APPNAME]['rawExcludes'];
	else
		echo "See below for explanation.\n";
?>
	</textarea>
	<script>getExcludes();</script>
</fieldset>
</form>
<br style="clear: both">
<div style="max-width:53em">
	<p>Do you need to upload CSV files? If so, you will need use
		eBird to AviSys checklist import (Version 1).
		<a href="../ebirdtoavisysV1/">Click here</a></p>
</div>
<h2>What it is</h2>
<?php
	$heredoc = <<<HEREDOC
<p>eBird to AviSys checklist import will convert one or more eBird checklists into an AviSys stream file 
with which you can import the data into AviSys (Version 6).</p>
HEREDOC;
	echo $heredoc,PHP_EOL;
?>
<h2>What you do</h2>
<ol>
<li>In the &ldquo;Checklist input&rdquo; form above, list one or more checklist names and click &ldquo;Go!&rdquo;.
	eBird to AviSys checklist import (Version 3) will fetch the checklists directly from eBird.
	If you have a large number of checklists, an easy way to handle them is to
	create an <a href="https://ebird.org/mytripreports">eBird Trip Report</a> for the checklists that you want to import to AviSys.
	Then just enter the URL of the trip report in this form.
	After downloading, you can delete the Trip Report if you want.
<p>	
Let's say you have a checklist <a href="https://ebird.org/checklist/S46116491" target="_blank">https://ebird.org/checklist/S46116491</a>.
	You can copy-and-paste just the "S46116491",
or you can copy-and-paste the whole "https://ebird.org/checklist/S46116491".
If you enter multiple checklists, enter them on separate lines, or else just separate them with spaces or commas.
For example you could enter</p>
<pre>https://ebird.org/checklist/S60513355
https://ebird.org/checklist/S60533760
https://ebird.org/checklist/S60593564
https://ebird.org/checklist/S60652303</pre>or
	<pre>S60513355, S60533760, S60593564, S60652303</pre>or
	<pre>S60513355 S60533760 S60593564 S60652303</pre>
<p>Likewise for a trip report you could enter</p>
<pre>https://ebird.org/tripreport/3380</pre>or just
<pre>3380</pre>
<p>You can enter more than one trip report.</p>
</li>
<li>On the next screen that you see, each eBird location that is in your input will be displayed.
If the corresponding AviSys place has a different name, you can enter the correct AviSys place name.
Then click the &ldquo;Download&rdquo; button that you will see.
<a href="howtodownload.php" target="_blank">More details on this screen</a> will be shown when you get there.
</li>
<li>Your AviSys stream file will be downloaded. Save it on your computer, then run Avisys to import it.</li>
<li>That's it!</li>
</ol>

<h3>What's a stream file?</h3>
<p>Read the tutorial on <a href="import.html" target="_blank">using AviSys stream files to import data</a> if you are not familiar with the process.</p>

<h3>What are excludes?</h3>
<p>You might have some species recorded in eBird that you don't want recorded in AviSys.
These might include domestic forms like Muscovy Duck (Domestic type),
non-countable exotics like Red-masked Parakeet, or others.
Maybe you don't record these species yourself, but they show up on lists someone shares with you.
You can exclude these species from the import from eBird by listing them,
one per line,
in the "Excludes" input.</p>
<p>"Slashes", "Spuhs", and Hybrids are automatically excluded, because AviSys does not support these names.
<h2>About species names</h2>
<p>
If AviSys does not recognize a species name while importing a stream file, 
it will skip that sighting record. (It will tell you!)
You will need to be sure that you have AviSys up-to-date with current taxonomy.
</p>
<p>One prominent difference between eBird names and AviSys names is that eBird accepts certain names
with parenthetic qualifiers, e.g., &ldquo;Northern Flicker (Yellow-shafted)&rdquo;.
You don't need to worry about these cases!
eBird to AviSys checklist import will remove the parenthetic part of the name and insert it at the beginning of the AviSys comment.</p>
<p>Another difference is that eBird allows &ldquo;sp&rdquo; or &ldquo;slash&rdquo; entries (e.g. Downy/Hairy Woodpecker).
AviSys does not allow such names.
</p>

<h2>Warning about system-hidden checklists or sensitive species.</h2>
<p>A checklist that is flagged for some reason with a comment similar to 
&ldquo;This checklist and its observations do not appear in public eBird outputs&rdquo;
cannot be imported with this version. You will need to use
<a href="../ebirdtoavisysV1/">Version 1</a></p>
<p>Any species designated sensitive will be omitted from checklists, so if you have any records of sensitive species,
you will likewise need to use <a href="../ebirdtoavisysV1/">Version 1</a>, or else enter the sensitive species manually.</p>

<h2>Can I import from other sources?</h2>
<p>Yes, but not with this version of eBird to AviSys checklist import. You will need to use
<a href="../ebirdtoavisysV1/">Version 1</a> which supports csv files.</p>
<h3>What about eBird's &ldquo;Download My Data&rdquo;?</h3>
eBird provides a feature to <a href="http://ebird.org/ebird/downloadMyData" target="_blank">download all of your eBird data</a> in one file.
eBird to AviSys checklist import will process that file too!
But again, you will need to use <a href="../ebirdtoavisysV1/">Version 1</a>.

<h2 id="summarize">Summarize by location or checklist</h2>
<p>
Before you generate the AviSys stream file, 
eBird to AviSys checklist import gives you a summary of what is in the checklists that you specified.
In the summary, you can correct the AviSys place name, and you have the option to
enter a global comment that will be added to all observations for that location.
</p><p>
By default, the summary is by location. 
In other words, no matter how many checklists you select for a location, the
summary will only have a single entry for the location. This is
"Summarize by location".
</p><p>
Sometimes you might prefer to be able to enter a custom comment for each
checklist rather than for each location. In that case, select "Summarize
by checklist", and the summary will contain an entry for each checklist.
Each entry will display the time and effort data for the checklist, so
that you can recognize it.
</p><p>
Whichever type of summary you choose, the generated stream file will be
the same, except for anything that you enter in the Global comment field.
</p>


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

<h2>Date</h2>
<p>AviSys can only record <a href="/2030.html">dates between Jan 1, 1930 and Dec 31, 2029</a>. 
Any dates older than 1930 will be recorded with a year of 1930.
Dates after 2030 will be recorded with a year 100 years earlier.
</p>

<?php
	if (file_exists('local_code.html'))
	{
		include 'local_code.html';
	}
?>
<?php
}
 ?>
