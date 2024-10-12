/*!
 * File: shaka.js
 * Description: JavaScript file to initialize and configure Shaka Player for DRM-protected video playback.
 * Author: Moiz Haider
 * Date: 12 October 2024
 */

function initApp() {
    if ('FairPlay' === drmType) {
        loadCertificate();
        $(document).ready(function() {
            setTimeout(function() { gettime("Loading HLS stream"); }, 500);
            setTimeout(function() { gettime("Detecting Apple FairPlay"); }, 1000);
            setTimeout(function() { gettime("Calling FairPlay License URL"); }, 2000);
            setTimeout(function() { gettime("License Acquired"); }, 4000);
            setTimeout(function() { gettime("<font color='red'>Press Play to Begin Playback</font>"); }, 6000);
        });
    } else {
        // Install polyfills to patch browser incompatibilities.
        shaka.polyfill.installAll();

        // Check if the browser supports the necessary APIs for Shaka Player.
        if (shaka.Player.isBrowserSupported()) {
            initPlayer();
        } else {
            console.error('Browser not supported!');
        }
    }
}

// Check if the user agent is Android.
function isAndroid() {
    return /Android/i.test(navigator.userAgent);
}

function initPlayer() {
    let contentUri, playerConfig;
    let player = new shaka.Player();

    // Attach the player to the video element.
    player.attach(video);

    // Attach player to the window for easy access in the JS console.
    window.player = player;

    // Listen for error events.
    player.addEventListener('error', onErrorEvent);

    if ('FairPlay' === drmType) {
        contentUri = hlsUri;
        const fairplayCert = getFairplayCert();

        alert('Fairplay not supported yet.');

        playerConfig = {
            drm: {
                servers: {
                    'com.apple.fps.1_0': 'https://fps.ezdrm.com/api/licenses/fd537439-74e2-4aad-8adb-b9f3e6417c59'
                },
                advanced: {
                    'com.apple.fps.1_0': {
                        serverCertificate: fairplayCert
                    }
                },
                initDataTransform: function(initData) {
                    const skdUri = shaka.util.StringUtils.fromBytesAutoDetect(initData);
                    const contentId = skdUri.substring(skdUri.indexOf('skd://') + 6);
                    const cert = player.drmInfo().serverCertificate;
                    return shaka.util.FairPlayUtils.initDataTransform(initData, contentId, cert);
                }
            }
        };

        player.getNetworkingEngine().registerRequestFilter(function(type, request) {
            if (type == shaka.net.NetworkingEngine.RequestType.LICENSE) {
                const originalPayload = new Uint8Array(request.body);
                const base64Payload = shaka.util.Uint8ArrayUtils.toBase64(originalPayload);
                const params = 'spc=' + encodeURIComponent(base64Payload);

                request.body = shaka.util.StringUtils.toUTF8(params);
            }
        });

        player.getNetworkingEngine().registerResponseFilter(function(type, response) {
            if (type == shaka.net.NetworkingEngine.RequestType.LICENSE) {
                const responseText = shaka.util.StringUtils.fromUTF8(response.data).trim();
                response.data = shaka.util.Uint8ArrayUtils.fromBase64(responseText).buffer;
                parsingResponse(response);
            }
        });
    } else {
        contentUri = dashUri;
        if ('Widevine' === drmType) {
            $(document).ready(function() {
                setTimeout(function() { gettime("Loading MPEG-DASH stream"); }, 500);
                setTimeout(function() { gettime("Detecting CENC"); }, 1000);
                setTimeout(function() { gettime("Calling Widevine License URL"); }, 2000);
                setTimeout(function() { gettime("CENC License Acquired with v: " + videoR + " and a: " + audioR); }, 4000);
                setTimeout(function() { gettime("<font color='red'>Press Play to Begin Playback</font>"); }, 6000);
            });
            playerConfig = {
                drm: {
                    servers: {
                        'com.widevine.alpha': widevineLicenseUrl
                    },
                    advanced: {
                        'com.widevine.alpha': {
                            'videoRobustness': videoR,
                            'audioRobustness': audioR
                        }
                    }
                }
            };

            player.getNetworkingEngine().registerRequestFilter(function(type, request) {
                if (type == shaka.net.NetworkingEngine.RequestType.LICENSE) {
                    console.log("request :" + request.body);
                }
            });
        } else {
            $(document).ready(function() {
                setTimeout(function() { gettime("Loading MPEG-DASH stream"); }, 500);
                setTimeout(function() { gettime("Detecting CENC"); }, 1000);
                setTimeout(function() { gettime("Calling PlayReady License URL"); }, 2000);
                setTimeout(function() { gettime("CENC License Acquired"); }, 4000);
                setTimeout(function() { gettime("<font color='red'>Press Play to Begin Playback</font>"); }, 6000);
            });
            playerConfig = {
                drm: {
                    servers: {
                        'com.microsoft.playready': playreadyLicenseUrl
                    }
                }
            };

            player.getNetworkingEngine().registerRequestFilter(function(type, request) {
                if (type == shaka.net.NetworkingEngine.RequestType.LICENSE) {
                    console.log("request :" + request.body);
                }
            });
        }

        player.getNetworkingEngine().registerResponseFilter(function(type, response) {
            if (type == shaka.net.NetworkingEngine.RequestType.LICENSE) {
                parsingResponse(response);
            }
        });
    }

    // Load the video content.
    player.load(contentUri).then(function() {
        console.log('The video has now been loaded!');
    }).catch(onError);

    player.configure(playerConfig);
}

// Parse the DRM license response.
function parsingResponse(response) {
    let responseText = arrayBufferToString(response.data);
    responseText = responseText.trim();

    try {
        const drmconObj = JSON.parse(responseText);
        if (drmconObj && drmconObj.errorCode && drmconObj.message) {
            if ("8002" != errorCode) {
                alert("DRM Error : " + drmconObj.message + "(" + drmconObj.errorCode + ")");
            } else {
                var errorObj = JSON.parse(drmconObj.message);
                alert("Error : " + errorObj.MESSAGE + "(" + errorObj.ERROR + ")");
            }
        }
    } catch (e) {}
}

// Handle Shaka Player error events.
function onErrorEvent(event) {
    console.error('Error code', event.detail.code, 'object', event.detail);
    onError(event.detail);
}

// Log Shaka Player errors.
function onError(error) {
    console.error('Error code', error.code, 'object', error);
    alert('Error code ' + error.code + ' object ' + error);
}

// Check the browser and set the DRM type.
checkBrowser();
document.addEventListener('DOMContentLoaded', initApp);

var keySystem;
var certificate;

// Path to the FairPlay certificate.
var serverCertificatePath = 'https://fps.ezdrm.com/demo/video/eleisure.cer';
// Path to the keyserver module that processes the SPC and returns a CKC.
var serverProcessSPCPath  = 'https://fps.ezdrm.com/api/licenses/b99ed9e5-c641-49d1-bfa8-43692b686ddb';
// Path to the media source.
var serverMediaSourcePath = 'https://na-fps.ezdrm.com/demo/ezdrm/master.m3u8';

// Convert a string to a Uint16Array.
function stringToArray(string) {
    var buffer = new ArrayBuffer(string.length * 2);
    var array = new Uint16Array(buffer);
    for (var i = 0, strLen = string.length; i < strLen; i++) {
        array[i] = string.charCodeAt(i);
    }
    return array;
}

// Convert a Uint16Array to a string.
function arrayToString(array) {
    var uint16array = new Uint16Array(array.buffer);
    return String.fromCharCode.apply(null, uint16array);
}

// Wait for a specific event and execute an action.
function waitForEvent(name, action, target) {
    target.addEventListener(name, function() {
        action(arguments[0]);
    }, false);
}

// Load the FairPlay certificate.
function loadCertificate() {
    var request = new XMLHttpRequest();
    request.responseType = 'arraybuffer';
    request.addEventListener('load', onCertificateLoaded, false);
    request.addEventListener('error', onCertificateError, false);
    request.open('GET', serverCertificatePath, true);
    request.send();
}

// Handle the loaded FairPlay certificate.
function onCertificateLoaded(event) {
    var request = event.target;
    certificate = new Uint8Array(request.response);
    startVideo();
}

// Handle errors when loading the FairPlay certificate.
function onCertificateError(event) {
    window.console.error('Failed to retrieve the server certificate.')
}

// Extract the content ID from the init data.
function extractContentId(initData) {
    var uri = arrayToString(initData);
    var uriParts = uri.split('://', 1);
    var protocol = uriParts[0].slice(-3);

    uriParts = uri.split(';', 2);
    var contentId = uriParts.length > 1 ? uriParts[1] : '';

    return protocol.toLowerCase() == 'skd' ? contentId : '';
}

// Concatenate init data, content ID, and certificate.
function concatInitDataIdAndCertificate(initData, id, cert) {
    if (typeof id == "string")
        id = stringToArray(id);
    var offset = 0;
    var buffer = new ArrayBuffer(initData.byteLength + 4 + id.byteLength + 4 + cert.byteLength);
    var dataView = new DataView(buffer);

    var initDataArray = new Uint8Array(buffer, offset, initData.byteLength);
    initDataArray.set(initData);
    offset += initData.byteLength;

    dataView.setUint32(offset, id.byteLength, true);
    offset += 4;

    var idArray = new Uint16Array(buffer, offset, id.length);
    idArray.set(id);
    offset += idArray.byteLength;

    dataView.setUint32(offset, cert.byteLength, true);
    offset += 4;

    var certArray = new Uint8Array(buffer, offset, cert.byteLength);
    certArray.set(cert);

    return new Uint8Array(buffer, 0, buffer.byteLength);
}

// Select the appropriate key system for FairPlay.
function selectKeySystem() {
    if (WebKitMediaKeys.isTypeSupported("com.apple.fps.1_0", "video/mp4")) {
        keySystem = "com.apple.fps.1_0";
    } else {
        throw "Key System not supported";
    }
}

// Start video playback.
function startVideo() {
    var video = document.getElementsByTagName('video')[0];
    video.addEventListener('webkitneedkey', onneedkey, false);
    video.addEventListener('error', onerror, false);

    video.src = serverMediaSourcePath;
}

// Handle video playback errors.
function onerror(event) {
    window.console.error('A video playback error occurred')
}

// Handle the needkey event for FairPlay.
function onneedkey(event) {
    var video = event.target;
    var initData = event.initData;
    var contentId = extractContentId(initData);

    initData = concatInitDataIdAndCertificate(initData, contentId, certificate);

    if (!video.webkitKeys) {
        selectKeySystem();
        video.webkitSetMediaKeys(new WebKitMediaKeys(keySystem));
    }

    if (!video.webkitKeys)
        throw "Could not create MediaKeys";

    var keySession = video.webkitKeys.createSession("video/mp4", initData);
    if (!keySession)
        throw "Could not create key session";

    keySession.contentId = contentId;
    waitForEvent('webkitkeymessage', licenseRequestReady, keySession);
    waitForEvent('webkitkeyadded', onkeyadded, keySession);
    waitForEvent('webkitkeyerror', onkeyerror, keySession);
}

// Handle the license request ready event.
function licenseRequestReady(event) {
    var session = event.target;
    var message = event.message;
    var sessionId = session.sessionId;
    var blob = new Blob([message], { type: 'application/octet-binary' });
    var request = new XMLHttpRequest();
    request.session = session;
    request.open('POST', serverProcessSPCPath + '?p1=' + Date.now(), true);
    request.setRequestHeader('Content-type', 'application/octet-stream');
    request.responseType = 'blob';
    request.addEventListener('load', licenseRequestLoaded, false);

    request.send(blob);
}

// Handle the loaded license request.
function licenseRequestLoaded(event) {
    var request = event.target;
    if (request.status == 200) {
        var blob = request.response;

        var reader = new FileReader();
        reader.addEventListener('loadend', function() {
            var array = new Uint8Array(reader.result);
            request.session.update(array);
        });
        reader.readAsArrayBuffer(blob);
    }
}

// Handle failed license requests.
function licenseRequestFailed(event) {
    window.console.error('The license request failed.');
}

// Handle decryption key errors.
function onkeyerror(event) {
    window.console.error('A decryption key error was encountered');
}

// Handle successful decryption key addition.
function onkeyadded(event) {
    window.console.log('Decryption key was added to session.');
}
