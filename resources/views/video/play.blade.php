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
            let widevineLicenseUrl = 'https://widevine-dash.ezdrm.com/proxy?pX=D6A082'; // Replace with your Widevine license server URL
            let playreadyLicenseUrl = 'https://playready.ezdrm.com/cency/preauth.aspx?pX=2AFB63'; // Replace with your PlayReady license server URL

            function initApp() {
                shaka.Player.probeSupport().then(function(support) {
                    console.log(support);
                    if (support.drm['com.widevine.alpha']) {
                        console.log('Widevine is supported!');
                        drmType = 'Widevine';

                        // Install built-in polyfills to patch browser incompatibilities.
                        shaka.polyfill.installAll();

                    } else if (support.drm['com.microsoft.playready']) {
                        console.log('PlayReady is supported!');
                        drmType = 'Playready';

                    } else {
                        console.log('No available DRM Supported.');
                    }

                    // Check if the browser supports the basic APIs Shaka needs.
                    if (shaka.Player.isBrowserSupported()) {
                        // Everything looks good!
                        initPlayer();
                    } else {
                        console.error('Browser not supported!');
                    }
                }).catch(function(error) {
                    console.error('Error probing DRM support:', error);
                });
            }

            function isAndroid() {
                return /Android/i.test(navigator.userAgent);
            }

            function initPlayer() {
                let contentUri, playerConfig;
                // Create a Player instance.
                const video = document.getElementById('video-player');
                console.log('Initializing player');
                let player = new shaka.Player(video);

                // Attach player to the window to make it easy to access in the JS console.
                window.player = player;

                // Listen for error events.
                player.addEventListener('error', onErrorEvent);

                contentUri = dashUri;

                console.log('DRM type: ', drmType);

                if (drmType === 'Widevine') {
                    playerConfig = {
                        drm: {
                            servers: {
                                'com.widevine.alpha': widevineLicenseUrl
                            },
                            advanced: {
                                'com.widevine.alpha': {
                                    'videoRobustness': 'HW_SECURE_DECODE',
                                    'audioRobustness': 'HW_SECURE_CRYPTO'
                                }
                            }
                        }
                    };
                } else if (drmType === 'Playready') {
                    // PlayReady configuration if needed
                    playerConfig = {
                        drm: {
                            servers: {
                                'com.microsoft.playready': playreadyLicenseUrl
                            }
                        }
                    };
                } else {
                    alert('No DRM configuration found.');
                }

                // Configure the player
                player.configure(playerConfig);

                // Load the content
                player.load(contentUri).then(function () {
                    console.log('The video has now been loaded!');
                }).catch(onError); // Handle load errors
            }

            function onErrorEvent(event) {
                // Extract the shaka.util.Error object from the event.
                onError(event.detail);
            }

            function onError(error) {
                // Log the error.
                console.error('Error code', error.code, 'object', error);
                alert('An error occurred: ' + error.code);
            }

            // Start the application
            document.addEventListener('DOMContentLoaded', initApp);
        </script>
    </x-slot:scripts>

</x-layouts.app>
