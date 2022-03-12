// Originally from https://www.sitepoint.com/tracking-upload-progress-with-php-and-javascript/
/* exported startUpload, sendRequest, resubmit */
/* globals ActiveXObject */
/* I couldn't get the progress bar to work. */
function toggleBarVisibility() {
	var e = document.getElementById("bar_blank");
	e.style.display = (e.style.display == "block") ? "none" : "block";
}

function createRequestObject() {
	var http;
	if (navigator.appName == "Microsoft Internet Explorer") {
		http = new ActiveXObject("Microsoft.XMLHTTP");
	}
	else {
		http = new XMLHttpRequest();
	}
	return http;
}

function sendRequest() {
	var http = createRequestObject();
	http.open("POST", "progress.php");
	http.onreadystatechange = function () { handleResponse(http); };
	http.send(null);
}

function handleResponse(http) {
	var response;
	if (http.readyState == 4) {
		response = http.responseText;
//		document.getElementById("bar_color").style.width = response + "%";
//		document.getElementById("status").innerHTML = response + "%";
		if (response < 100) {
			setTimeout("sendRequest()", 1000);
		}
		else {
			toggleBarVisibility();
			document.getElementById("status").innerHTML = '...';
			setTimeout("resubmit()",3000);
		}
	}
}

function resubmit() {
	// This is to handle a perplexing hang that occurs randomly, possibly only with Firefox
	window.location.assign(window.location.href);
}

function startUpload() {
	toggleBarVisibility();
	setTimeout("sendRequest()", 500);
}
