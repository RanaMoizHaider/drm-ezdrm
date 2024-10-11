{{--
    File: play.blade.php
    Description: View to play a video using VdoCipher player.
    Author: Moiz Haider
    Date: 11 October 2024
--}}

<x-layouts.app>

    <x-slot:title>Play Video</x-slot:title>

    <x-slot:styles>
        <!-- Video.js CSS -->
        <link href="https://unpkg.com/video.js/dist/video-js.css" rel="stylesheet" />
    </x-slot:styles>

    <div class="flex justify-center items-center">
        <div class="relative w-full md:w-2/3 lg:w-1/2">
            <!-- Video.js player -->
            <video id="video-player" class="video-js vjs-default-skin vjs-16-9" controls preload="auto" width="640" height="360">
                <!-- Placeholder for poster image -->
                <p class="vjs-no-js">
                    To view this video please enable JavaScript, and consider upgrading to a
                    web browser that
                    <a href="https://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a>
                </p>
            </video>
        </div>
    </div>

    <x-slot:scripts>
        <!-- Video.js and DASH.js -->
        <script src="https://unpkg.com/video.js/dist/video.js"></script>
        <!-- Include Dash.js library -->
        <script src="https://cdn.dashjs.org/latest/dash.all.min.js"></script>
        <!-- Include videojs-contrib-dash -->
        <script src="https://unpkg.com/videojs-contrib-dash/dist/videojs-dash.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var player = videojs('video-player');

                player.ready(function () {
                    player.src({
                        src: '{{ $videoUrl }}',
                        type: 'application/dash+xml',
                        keySystemOptions: [
                            {
                                name: 'com.widevine.alpha',
                                options:{
                                    serverURL : 'https://widevine-dash.ezdrm.com/proxy?pX=D6A082'
                                }
                            },
                            {
                                name: 'com.microsoft.playready',
                                options:{
                                    serverURL : 'https://playready.ezdrm.com/cency/preauth.aspx?pX=2AFB63'

                                }
                            }
                        ]
                    });
                });
            });
        </script>
    </x-slot:scripts>

</x-layouts.app>
