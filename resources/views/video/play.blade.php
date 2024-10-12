{{--
    File: play.blade.php
    Description: View to play a video using Shaka Player.
    Author: Moiz Haider
    Date: 12 October 2024
--}}

<x-layouts.app>

    <x-slot:title>Play Video</x-slot:title>

    <x-slot:styles>
        <!-- Shaka Player UI CSS -->
        <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/shaka-player/4.11.7/controls.min.css">
    </x-slot:styles>

    <div class="flex justify-center items-center">
        <div id="video-container" class="relative w-full md:w-2/3 lg:w-1/2">
            <!-- Shaka Player video element -->
            <video id="video-player" width="640" controls></video>
        </div>
    </div>

    <x-slot:scripts>
        <!-- Shaka Player UI JS -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/shaka-player/4.11.7/shaka-player.ui.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', initApp);

            async function initApp() {
                // Install built-in polyfills to patch browser incompatibilities.
                shaka.polyfill.installAll();

                // Check if the browser supports the basic APIs Shaka needs.
                if (shaka.Player.isBrowserSupported()) {
                    console.log('The browser is supported!');
                    // Everything looks good!
                    await initPlayer();
                } else {
                    // This browser does not have the minimum set of APIs we need.
                    console.error('Browser not supported!');
                }
            }

            async function initPlayer() {
                // Create a Player instance without arguments.
                var video = document.getElementById('video-player');
                var videoContainer = document.getElementById('video-container');

                var player = new shaka.Player();
                var ui = new shaka.ui.Overlay(player, videoContainer, video);

                // Attach player and UI to the window for easy access in the JS console.
                window.player = player;
                window.ui = ui;

                // Listen for error events.
                player.addEventListener('error', onErrorEvent);

                // Configure DRM servers and robustness.
                player.configure({
                    drm: {
                        servers: {
                            'com.widevine.alpha': 'https://widevine-dash.ezdrm.com/proxy?pX=D6A082',
                            'com.microsoft.playready': 'https://playready.ezdrm.com/cency/preauth.aspx?pX=2AFB63'
                        },
                        advanced: {
                            'com.widevine.alpha': {
                                'videoRobustness': 'SW_SECURE_DECODE',
                                'audioRobustness': 'SW_SECURE_DECODE'
                            }
                        }
                    }
                });

                try {
                    // Attach the player to the video element.
                    await player.attach(video);

                    // Load the manifest URI.
                    await player.load('{{ $videoUrl }}');
                    console.log('The video has now been loaded!');
                } catch (error) {
                    onError(error);
                }
            }

            function onErrorEvent(event) {
                // Extract the shaka.util.Error object from the event.
                onError(event.detail);
            }

            function onError(error) {
                // Log the error.
                console.error('Error code', error.code, 'object', error);
            }
        </script>
    </x-slot:scripts>

</x-layouts.app>
