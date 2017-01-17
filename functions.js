function clearPage()
{
document.getElementById('subbut').style.display='none';
document.getElementById('canbut').value='Reset';
document.getElementById('donemsg').style.display='inline';
document.getElementById('advice').style.display='none';
}

function filebutton(i,limit)
{	/* Add another upload button. */
	var ip1=i+1;
	var dv = document.createElement("div");
	var im1 = i-1;
	/* Only allow onclick to fire once; then remove it. */
	var myButton = document.getElementById("file"+im1);
	myButton.removeAttribute("onclick");

	dv.setAttribute("id", "d"+i);
	dv.style.display = "block";
	if (!limit || ip1 < limit)
		dv.innerHTML='<label class="input" for="file'+i+'">Checklist '+ip1+':</label><br><input class="upload" id="file'+i+'" name="fileupload['+i+']" type="file" style="width:35em" onclick="filebutton('+ip1+','+limit+');return true;"/><br>';
	else
		dv.innerHTML='<label class="input" for="file'+i+'">Checklist '+ip1+':</label><br><input class="upload" id="file'+i+'" name="fileupload['+i+']" type="file" style="width:35em"/><br>';
	var element = document.getElementById("buttons");
	element.appendChild(dv);
	return;
}

function checkType()
{
	return validated();
}

function validated()
{
	/* Validate some inputs. Return true if all ok, else false. */
	var i=0;
	var id;
	var allok = true;
	do
	{	/* Check that a place type has been selected. If not, turn on the warning message. */
		id = "place_level[" + i + "]";
		pl = document.getElementById(id);
		if (!pl) break;
		id = "placewarn[" + i + "]";
		if (pl.value == "")
		{
			document.getElementById(id).style.display='inline';
			allok = false;
		}
		else
			document.getElementById(id).style.display='none';
		i++;
	} while (i < 1000);

	i = 0;
	do
	{	/* Check that all country codes have a value. */
		id = "ccode[" + i + "]";
		cy = document.getElementById(id);		
		if (!cy) break;
		id = "cntrywarn[" + i + "]";
		if (cy.value.trim() == "")
		{
			document.getElementById(id).style.display='inline';
			allok = false;
		}
		else
			document.getElementById(id).style.display='none';
		i++;
	} while (i < 1000);

	if (allok)
	{
		clearPage();
		return true;
	}
	else return false;
}

function place_sel(i)
{	/* Clear the warning message when a place type is selected. */
	id = "placewarn[" + i + "]";
	document.getElementById(id).style.display='none';
	return;
}


function country_fill(i)
{	/* Clear the warning message when a country code is entered. */
	id = "cntrywarn[" + i + "]";
	document.getElementById(id).style.display='none';
	return;
}

function qc()
{
hst='gmail';
dmn='com';
usr='Kent.Fiala';
nm='Kent Fiala';
sj = '?' + 'sub' + 'jec' + 't=' + 'Question or comment on eBird to AviSys checklist import';
return('m' + 'ailt' + 'o:' + nm + '<' + usr + '@' + hst + '.' + dmn + '>' + sj )
}
