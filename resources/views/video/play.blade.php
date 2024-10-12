{{--
    File: play.blade.php
    Description: View to play a video using Shaka Player.
    Author: Moiz Haider
    Date: 12 October 2024
--}}

<x-layouts.app>

    <x-slot:title>Play Video</x-slot:title>

    <div class="flex justify-center items-center">
        <div id="video-container" class="relative w-full md:w-2/3 lg:w-1/2">
            <!-- Shaka Player video element -->
            <video id="video-player" width="640" controls></video>
        </div>
    </div>

    <x-slot:scripts>
        <script src="{{ asset('js/shaka-compiled.min.js') }}"></script>
        <script>
            // Define your variables and URLs
            let drmType = ''; // Will be set based on device/browser
            let certificate; // For FairPlay
            let dashUri = '{{ $videoUrl }}'; // Replace with your DASH manifest URL
            let hlsUri = '{{ $videoUrl }}'; // Replace with your HLS manifest URL (for FairPlay)
            let fairplayCertUrl = 'YOUR_FAIRPLAY_CERTIFICATE_URL'; // Replace with your FairPlay certificate URL
            let fairplayLicenseUrl = 'YOUR_FAIRPLAY_LICENSE_URL'; // Replace with your FairPlay license server URL
            let widevineLicenseUrl = 'https://widevine-dash.ezdrm.com/proxy?pX=D6A082'; // Replace with your Widevine license server URL
            let playreadyLicenseUrl = 'https://playready.ezdrm.com/cency/preauth.aspx?pX=2AFB63'; // Replace with your PlayReady license server URL

            function initApp() {
                // Detect DRM type based on device/browser
                // if (isSafari()) {
                //     drmType = 'FairPlay';
                //     loadCertificate();
                // } else {

                shaka.Player.probeSupport().then(function(support) {
                    if (support.drm['com.widevine.alpha']) {
                        console.log('Widevine is supported!');
                        drmType = 'Widevine';

                        // Install built-in polyfills to patch browser incompatibilities.
                        shaka.polyfill.installAll();

                    } else if (support.drm['com.microsoft.playready']) {
                        console.log('PlayReady is supported!');
                        drmType = 'PlayReady';

                    } else {
                        console.log('No available DRM Supported.');
                    }
                }).catch(function(error) {
                    console.error('Error probing DRM support:', error);
                });

                    // drmType = 'Widevine';
                    // Install built-in polyfills to patch browser incompatibilities.
                    // shaka.polyfill.installAll();

                    // Check if the browser supports the basic APIs Shaka needs.
                    if (shaka.Player.isBrowserSupported()) {
                        // Everything looks good!
                        initPlayer();
                    } else {
                        console.error('Browser not supported!');
                    }
                // }
            }

            function isSafari() {
                return /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
            }

            function isAndroid() {
                return /Android/i.test(navigator.userAgent);
            }

            function initPlayer() {
                let contentUri, playerConfig;
                // Create a Player instance.
                const video = document.getElementById('video-player');
                let player = new shaka.Player(video);

                // Attach player to the window to make it easy to access in the JS console.
                window.player = player;

                // Listen for error events.
                player.addEventListener('error', onErrorEvent);

                // if ('FairPlay' === drmType) {
                //     contentUri = hlsUri;
                //
                //     playerConfig = {
                //         drm: {
                //             servers: {
                //                 'com.apple.fps.1_0': fairplayLicenseUrl
                //             },
                //             advanced: {
                //                 'com.apple.fps.1_0': {
                //                     serverCertificate: certificate
                //                 }
                //             },
                //             initDataTransform: function (initData) {
                //                 const skdUri = shaka.util.StringUtils.fromBytesAutoDetect(initData);
                //                 const contentId = skdUri.substring(skdUri.indexOf('skd://') + 6);
                //                 const cert = player.drmInfo().serverCertificate;
                //                 return shaka.util.FairPlayUtils.initDataTransform(initData, contentId, cert);
                //             }
                //         }
                //     };
                //
                //     player.getNetworkingEngine().registerRequestFilter(function (type, request) {
                //         if (type == shaka.net.NetworkingEngine.RequestType.LICENSE) {
                //             const originalPayload = new Uint8Array(request.body);
                //             const base64Payload = shaka.util.Uint8ArrayUtils.toBase64(originalPayload);
                //             const params = 'spc=' + encodeURIComponent(base64Payload);
                //             request.body = shaka.util.StringUtils.toUTF8(params);
                //             request.headers['Content-Type'] = 'application/x-www-form-urlencoded';
                //         }
                //     });
                //
                //     player.getNetworkingEngine().registerResponseFilter(function (type, response) {
                //         if (type == shaka.net.NetworkingEngine.RequestType.LICENSE) {
                //             const responseText = shaka.util.StringUtils.fromUTF8(response.data).trim();
                //             response.data = shaka.util.Uint8ArrayUtils.fromBase64(responseText).buffer;
                //         }
                //     });
                // } else {
                    contentUri = dashUri;

                    if ('Widevine' === drmType) {
                        if (isAndroid()) {
                            playerConfig = {
                                drm: {
                                    servers: {
                                        'com.widevine.alpha': widevineLicenseUrl
                                    },
                                    advanced: {
                                        'com.widevine.alpha': {
                                            'videoRobustness': 'HW_SECURE_ALL',
                                            'audioRobustness': 'HW_SECURE_CRYPTO'
                                        }
                                    }
                                }
                            };
                        } else {
                            playerConfig = {
                                drm: {
                                    servers: {
                                        'com.widevine.alpha': widevineLicenseUrl
                                    }
                                }
                            };
                        }

                        player.getNetworkingEngine().registerRequestFilter(function (type, request) {
                            if (type == shaka.net.NetworkingEngine.RequestType.LICENSE) {
                                // Optional: Add headers or modify the request
                            }
                        });
                    } else if ('Playready' === drmType) {
                        // PlayReady configuration if needed
                        playerConfig = {
                            drm: {
                                servers: {
                                    'com.microsoft.playready': playreadyLicenseUrl
                                }
                            }
                        };

                        player.getNetworkingEngine().registerRequestFilter(function (type, request) {
                            if (type == shaka.net.NetworkingEngine.RequestType.LICENSE) {
                                // Optional: Add headers or modify the request
                            }
                        });
                    } else {
                        console.error('No DRM configuration found.');
                    }

                    player.getNetworkingEngine().registerResponseFilter(function (type, response) {
                        if (type == shaka.net.NetworkingEngine.RequestType.LICENSE) {
                            // Optional: Parse the response if needed
                        }
                    });
                }

                // Configure the player
                player.configure(playerConfig);

                // Load the content
                player.load(contentUri).then(function () {
                    console.log('The video has now been loaded!');
                }).catch(onError); // Handle load errors
            // }

            function onErrorEvent(event) {
                // Extract the shaka.util.Error object from the event.
                onError(event.detail);
            }

            function onError(error) {
                // Log the error.
                console.error('Error code', error.code, 'object', error);
            }

            // FairPlay-specific functions
            function loadCertificate() {
                var request = new XMLHttpRequest();
                request.responseType = 'arraybuffer';
                request.addEventListener('load', onCertificateLoaded, false);
                request.addEventListener('error', onCertificateError, false);
                request.open('GET', fairplayCertUrl, true);
                request.send();
            }

            function onCertificateLoaded(event) {
                var request = event.target;
                certificate = new Uint8Array(request.response);
                initPlayer(); // Start the player after the certificate is loaded
            }

            function onCertificateError(event) {
                console.error('Failed to retrieve the server certificate.');
            }

            // Start the application
            document.addEventListener('DOMContentLoaded', initApp);
        </script>
    </x-slot:scripts>

</x-layouts.app>
