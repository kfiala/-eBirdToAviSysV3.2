/* exported lookupPlace, filebutton, placeEdit, checkType, place_sel, getExcludes, saveExcludes, glocomSave */

function lookupPlace(i) {
	'use strict';
	let eBirdLocation = document.getElementById('eBirdLocation'+i).innerText;

	let AviSysLookup = localStorage.getItem(eBirdLocation);
	if (AviSysLookup) {	// If there is a saved AviSys place name for this eBird location
		document.getElementById('place'+i).value = AviSysLookup;	// Fill in the AviSys place name
		if (AviSysLookup !== eBirdLocation) {	// If the names are different
			if (localStorage.getItem(eBirdLocation+'.autofill')) {		// If there is a saved autofill for this location
				document.getElementById('autofill'+i).checked = false;	// Turn off the autofill checkmark and 
				// put the saved autofill in the global comment
				document.getElementById('glocom'+i).value = localStorage.getItem(eBirdLocation+'.autofill').substring(1);
			} else {	// But if no saved autofill
				document.getElementById('autofill'+i).checked = true;			// Turn on the autofill checkmark
				document.getElementById('glocom'+i).value = eBirdLocation;	// Put the eBird location name in the global comment
			}
		}
		let placeInfo = localStorage.getItem('Place/'+AviSysLookup);
		if (placeInfo) {
			let info = placeInfo.split('/');	// backwards compatibility
			document.getElementById('place_level'+i).value = info[0];
		}
	}
	document.getElementById('autofill'+i).addEventListener('change',() => {autoFillToggle(i)});	// change listener for checkbox
}

function placeToolong(i) {
	'use strict';
	var AviSysPlace = document.getElementById('place'+i).value;
	var toolong =  document.getElementById('toolong'+i);
	if (AviSysPlace.length > 30)
	{
		toolong.style.display='block';
		toolong.innerText = AviSysPlace + ' is too long to be an AviSys place name.';
	}
	else
	{
		toolong.style.display='none';
		toolong.innerText = '';
	}
}

function autoFillToggle(i) {	// When autofill checkbox is changed
	let eBirdLocation = document.getElementById('eBirdLocation'+i).innerText;
	if (document.getElementById('autofill'+i).checked) {	// If it is checked
		// Fill the global comment with the eBird location name
		document.getElementById('glocom'+i).value = document.getElementById('eBirdLocation'+i).innerText;
		localStorage.removeItem(eBirdLocation+'.autofill');	// And remove any saved autofill 
	} else {	// If it is unchecked
		document.getElementById('glocom'+i).value = '';			// Blank out the global comment
		localStorage.setItem(eBirdLocation+'.autofill','/');	// Store a blank autofill
	}
}

function placeEdit(i)
{ // When the AviSys place name is edited
	'use strict';
	placeToolong(i);  // Check if it is too long
  // Copy the eBird location name to the global comment
	document.getElementById('glocom'+i).value = document.getElementById('eBirdLocation'+i).innerText;
  // Check the autofill checkbox
	document.getElementById('autofill'+i).checked = true;
}

function savePlace(i) {
	'use strict';
	var eBirdLocation = document.getElementById('eBirdLocation'+i).innerText;
	var AviSysPlace = document.getElementById('place'+i).value;
	localStorage.setItem(eBirdLocation,AviSysPlace);
	var placeType = document.getElementById('place_level'+i).value;
	localStorage.setItem('Place/'+AviSysPlace,placeType);
}

function glocomSave(i) { // When global comment is updated
	'use strict';
	let eBirdLocation = document.getElementById('eBirdLocation'+i).innerText;
	let glocom = document.getElementById('glocom'+i).value;

	if (eBirdLocation != glocom && glocom != '') {	// If there is a global comment and it does not match the eBird location
		document.getElementById('autofill'+i).checked = false;		// Uncheck the autofill checkbox
		localStorage.setItem(eBirdLocation+'.autofill','/'+glocom);	// Save the global comment as autofill
	}
}

function getExcludes() {
	'use strict';
	var excludes = localStorage.getItem('excludes');
	if (excludes) {
		document.getElementById('excludes').value = excludes;
	}
}

function saveExcludes() {
	'use strict';
	var excludes = document.getElementById('excludes').value;
	localStorage.setItem('excludes',excludes);
}

function clearPage()
{ 
'use strict';
document.getElementById('subbut').style.display='none';
document.getElementById('canbut').value='Reset';
document.getElementById('donemsg').style.display='inline';
document.getElementById('advice').style.display='none';
}

function checkType()
{
	'use strict';
	return validated();
}

function validated()
{
	'use strict';
	/* Validate some inputs. Return true if all ok, else false. */
	var i=0;
	var id;
	var allok = true;
	do
	{	/* Check that a place type has been selected. If not, turn on the warning message. */
		id = "place_level" + i;
		var pl = document.getElementById(id);
		if (!pl) {break;}
		id = "placewarn[" + i + "]";
		if (pl.value === "")
		{
			document.getElementById(id).style.display='inline';
			allok = false;
		}
		else {
			document.getElementById(id).style.display='none';
		}
		i++;
	} while (i < 1000);

	if (allok)
	{
		clearPage();
		return true;
	}
	else {return false;}
}

function place_sel(i)
{	/* Clear the warning message when a place type is selected. */
	'use strict';
	var id = "placewarn[" + i + "]";
	document.getElementById(id).style.display='none';
	savePlace(i);
	return;
}
