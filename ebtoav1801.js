/* exported lookupPlace, filebutton, placeEdit, checkType, place_sel, qc */

function lookupPlace(i) {
	'use strict';
	var eBirdLocation = document.getElementById('eBirdLocation'+i).innerHTML;

	var AviSysLookup = localStorage.getItem(eBirdLocation);
	if (AviSysLookup) {
		document.getElementById('place'+i).value = AviSysLookup;
		if (AviSysLookup !== eBirdLocation) {
			document.getElementById('glocom'+i).value = eBirdLocation;
		}
		var placeInfo = localStorage.getItem('Place/'+AviSysLookup);
		if (placeInfo) {
			var info = placeInfo.split('/');
			document.getElementById('place_level'+i).value = info[0];
			document.getElementById('ccode'+i).value = info[1];
		}
	}
}

function placeToolong(i) {
	'use strict';
	var AviSysPlace = document.getElementById('place'+i).value;
	var toolong =  document.getElementById('toolong'+i);
	if (AviSysPlace.length > 30)
	{
		toolong.innerHTML = AviSysPlace + ' is too long to be an AviSys place name.';
	}
	else
	{
		toolong.innerHTML = '';
	}
}

function placeEdit(i)
{
	'use strict';
	placeToolong(i);
	document.getElementById('glocom'+i).value = document.getElementById('eBirdLocation'+i).innerHTML;
}

function savePlace(i) {
	'use strict';
	var eBirdLocation = document.getElementById('eBirdLocation'+i).innerHTML;
	var AviSysPlace = document.getElementById('place'+i).value;
	localStorage.setItem(eBirdLocation,AviSysPlace);
	var placeType = document.getElementById('place_level'+i).value;
	var country = document.getElementById('ccode'+i).value;
	localStorage.setItem('Place/'+AviSysPlace,placeType+'/'+country);
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

function qc()
{
	'use strict';
var hst='gmail';
var dmn='com';
var usr='Kent.Fiala';
var nm='Kent Fiala';
var sj = '?' + 'sub' + 'jec' + 't=' + 'Question or comment on eBird to AviSys checklist import';
return('m' + 'ailt' + 'o:' + nm + '<' + usr + '@' + hst + '.' + dmn + '>' + sj );
}
