var browser = 'Non-DRM browser';
var drmType = 'No DRM';

// Replace the DEMO site ID with yours when you test your own FPS content.
var fairplayCertUri = 'https://fps.ezdrm.com/demo/video/eleisure.cer';

// Detect the browser and set proper DRM type
function checkBrowser() {
	var agent = navigator.userAgent.toLowerCase(),
		name = navigator.appName,
		browser;

	if (name === 'Microsoft Internet Explorer' || agent.indexOf('trident') > -1 || agent.indexOf('Edg/') > -1) {
		browser = 'ie';
		if (name === 'Microsoft Internet Explorer') { // IE old version (IE 10 or Lower)
			agent = /msie ([0-9]{1,}[\.0-9]{0,})/.exec(agent);
		} else if (agent.indexOf('Edg/') > -1) { // Edge
			browser = 'Edge';
			drmType = "PlayReady";
		}
	} else if (agent.indexOf('safari') > -1) { // Chrome or Safari
		if (agent.indexOf('opr') > -1) { // Opera
			browser = 'Opera';
			drmType = 'Widevine';
		} else if (agent.indexOf('whale') > -1) { // Whale
			browser = 'Whale';
			drmType = 'Widevine';
		} else if (agent.indexOf('edg/') > -1 && agent.indexOf('chrome/') > -1 && agent.indexOf('mac') > -1) { // Edge on Mac
			browser = 'Edge';
			drmType = "Widevine";
		} else if (agent.indexOf('edg/') > -1 || agent.indexOf('Edg/') > -1) { // Edge
			browser = 'Edge';
			drmType = "PlayReady";
		} else if (agent.indexOf('chrome') > -1) { // Chrome
			browser = 'Chrome';
			drmType = 'Widevine';
		} else { // Safari
			browser = 'Safari';
			drmType = "FairPlay";
		}
	} else if (agent.indexOf('firefox') > -1) { // Firefox
		browser = 'firefox';
		drmType = 'Widevine';
	}

	// The below three lines are for the sample code only. May need to be removed.
	var result = "Running in " + browser + ". " + drmType + " supported.";
	document.getElementById("browserCheckResult").innerHTML = result;
	console.log(result);

	if ('Widevine' === drmType) {
		$(document).ready(function() {
			setTimeout(function() { gettime("Loading MPEG-DASH stream"); }, 500);
			setTimeout(function() { gettime("Detecting CENC"); }, 1000);
			setTimeout(function() { gettime("Calling Widevine License URL"); }, 2000);
			setTimeout(function() { gettime("CENC License Acquired with v: " + videoR + " and a: " + audioR); }, 4000);
			setTimeout(function() { gettime("<font color='red'>Press Play to Begin Playback</font>"); }, 6000);
		});
	}
	if ('PlayReady' === drmType) {
		$(document).ready(function() {
			setTimeout(function() { gettime("Loading MPEG-DASH stream"); }, 500);
			setTimeout(function() { gettime("Detecting CENC"); }, 1000);
			setTimeout(function() { gettime("Calling PlayReady License URL"); }, 2000);
			setTimeout(function() { gettime("CENC License Acquired"); }, 4000);
			setTimeout(function() { gettime("<font color='red'>Press Play to Begin Playback</font>"); }, 6000);
		});
	}

	return browser;
}

// Convert array to string
function arrayToString(array) {
	var uint16array = new Uint16Array(array.buffer);
	return String.fromCharCode.apply(null, uint16array);
}

// Convert array buffer to string
function arrayBufferToString(buffer) {
	var arr = new Uint8Array(buffer);
	var str = String.fromCharCode.apply(String, arr);
	return str;
}

// Decode base64 to Uint8Array
function base64DecodeUint8Array(input) {
	var raw = window.atob(input);
	var rawLength = raw.length;
	var array = new Uint8Array(new ArrayBuffer(rawLength));

	for (i = 0; i < rawLength; i++)
		array[i] = raw.charCodeAt(i);

	return array;
}

// Encode Uint8Array to base64
function base64EncodeUint8Array(input) {
	var keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
	var output = "";
	var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
	var i = 0;

	while (i < input.length) {
		chr1 = input[i++];
		chr2 = i < input.length ? input[i++] : Number.NaN;
		chr3 = i < input.length ? input[i++] : Number.NaN;

		enc1 = chr1 >> 2;
		enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
		enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
		enc4 = chr3 & 63;

		if (isNaN(chr2)) {
			enc3 = enc4 = 64;
		} else if (isNaN(chr3)) {
			enc4 = 64;
		}
		output += keyStr.charAt(enc1) + keyStr.charAt(enc2) +
			keyStr.charAt(enc3) + keyStr.charAt(enc4);
	}
	return output;
}

// Get Fairplay certificate
function getFairplayCert() {
	var xmlhttp;
	if (window.XMLHttpRequest) {
		xmlhttp = new XMLHttpRequest();
	} else {
		xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	}
	xmlhttp.open("GET", fairplayCertUri, false);
	xmlhttp.send();
	console.log('fpsCert : ', xmlhttp.responseText);
	var fpsCert = shaka.util.Uint8ArrayUtils.fromBase64(xmlhttp.responseText);
	console.log('fpsCert decrypt : ', fpsCert);
	return fpsCert;
}

// Global variable to store the name of detected DRM
let supportedDRM = "no support";

// Checks which DRM is supported by the browser
function checkSupportedDRM() {
	console.log("checkSupportedDRM start");

	var configCENC = [{
		"initDataTypes": ["cenc"],
		"audioCapabilities": [{
			"contentType": "audio/mp4;codecs=\"mp4a.40.2\""
		}],
		"videoCapabilities": [{
			"contentType": "video/mp4;codecs=\"avc1.42E01E\""
		}]
	}];

	var configFPS = [{
		"audioCapabilities": [{
			"contentType": "audio/mp4;codecs=\"mp4a.40.2\""
		}],
		"videoCapabilities": [{
			"contentType": "video/mp4;codecs=\"avc1.42E01E\""
		}]
	}];

	// Checks if the browser support PlayReady DRM
	try {
		navigator.requestMediaKeySystemAccess("com.microsoft.playready", configCENC)
			.then(function(mediaKeySystemAccess) {
				console.log('playready support ok');
				supportedDRM = "PlayReady";
				return; // Stops the checking here because we found PlayReady DRM
			}).catch(function(e) {
				console.log('no playready support');
				console.log(e);
			});
	} catch (e) {
		console.log('no playready support');
		console.log(e);
	}

	// If no PlayReady, checks if there's Widevine DRM
	try {
		navigator.requestMediaKeySystemAccess("com.widevine.alpha", configCENC)
			.then(function(mediaKeySystemAccess) {
				console.log('widevine support ok');
				supportedDRM = "Widevine";
				return; // Stops when Widevine DRM is found
			}).catch(function(e) {
				console.log('no widevine support');
				console.log(e);
			});
	} catch (e) {
		console.log('no widevine support');
		console.log(e);
	}

	console.log('seems the browser is safari (fairplay supported)');
	supportedDRM = "FairPlay";
}

// Add zero in front of numbers < 10
function checkTime(i) {
	if (i < 10) { i = "0" + i };
	return i;
}

// Log the time and message
function gettime(msg) {
	var today = new Date();
	var h = today.getHours();
	var m = today.getMinutes();
	var s = today.getSeconds();
	m = checkTime(m);
	s = checkTime(s);
	$('#log').append(h + ":" + m + ":" + s + " " + msg + "<br>");
	return true;
}
