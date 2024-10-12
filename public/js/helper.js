/*!
 * File: helper.js
 * Description: This file contains helper functions for detecting browser types and supported DRM systems, as well as utility functions for encoding/decoding data and logging.
 * Author: Moiz Haider
 * Date: 12 October 2024
 */

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

    // Display the detected browser and DRM type
    var result = "Running in " + browser + ". " + drmType + " supported.";
    document.getElementById("browserCheckResult").innerHTML = result;
    console.log(result);

    return browser;
}

// Convert a Uint8Array to a string
function arrayToString(array) {
    var uint16array = new Uint16Array(array.buffer);
    return String.fromCharCode.apply(null, uint16array);
}

// Convert an ArrayBuffer to a string
function arrayBufferToString(buffer) {
    var arr = new Uint8Array(buffer);
    var str = String.fromCharCode.apply(String, arr);
    return str;
}

// Decode a base64 string to a Uint8Array
function base64DecodeUint8Array(input) {
    var raw = window.atob(input);
    var rawLength = raw.length;
    var array = new Uint8Array(new ArrayBuffer(rawLength));

    for (i = 0; i < rawLength; i++)
        array[i] = raw.charCodeAt(i);

    return array;
}

// Encode a Uint8Array to a base64 string
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

// Retrieve the FairPlay certificate from the server
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

// Check which DRM is supported by the browser
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

    // Check if the browser supports PlayReady DRM
    try {
        navigator.requestMediaKeySystemAccess("com.microsoft.playready", configCENC)
            .then(function(mediaKeySystemAccess) {
                console.log('playready support ok');
                supportedDRM = "PlayReady";
                return; // Stop checking if PlayReady DRM is found
            }).catch(function(e) {
                console.log('no playready support');
                console.log(e);
            });
    } catch (e) {
        console.log('no playready support');
        console.log(e);
    }

    // If no PlayReady, check if there's Widevine DRM
    try {
        navigator.requestMediaKeySystemAccess("com.widevine.alpha", configCENC)
            .then(function(mediaKeySystemAccess) {
                console.log('widevine support ok');
                supportedDRM = "Widevine";
                return; // Stop checking if Widevine DRM is found
            }).catch(function(e) {
                console.log('no widevine support');
                console.log(e);
            });
    } catch (e) {
        console.log('no widevine support');
        console.log(e);
    }

    // Assume FairPlay support if no other DRM is found
    console.log('seems the browser is safari (fairplay supported)');
    supportedDRM = "FairPlay";
}

// Add a leading zero to numbers less than 10
function checkTime(i) {
    if (i < 10) { i = "0" + i };
    return i;
}

// Log the current time and a message
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
