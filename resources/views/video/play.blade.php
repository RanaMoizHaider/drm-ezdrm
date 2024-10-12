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
            <video id="my-player" width="640" controls></video>

            <span id="browserCheckResult"></span>

            <!-- Dropdown for video robustness levels -->
            <div class="mt-4">
                <h4>Select Video Robustness:</h4>
                <select id="videoR" onchange="updateSettings()">
                    <option value="SW_SECURE_CRYPTO">SW_SECURE_CRYPTO</option>
                    <option value="SW_SECURE_DECODE">SW_SECURE_DECODE</option>
                    <option value="HW_SECURE_CRYPTO">HW_SECURE_CRYPTO</option>
                    <option value="HW_SECURE_DECODE">HW_SECURE_DECODE</option>
                    <option value="HW_SECURE_ALL" selected>HW_SECURE_ALL</option>
                </select>
            </div>

            <!-- Dropdown for audio robustness levels -->
            <div class="mt-4">
                <h4>Select Audio Robustness:</h4>
                <select id="audioR" onchange="updateSettings()">
                    <option value="SW_SECURE_CRYPTO">SW_SECURE_CRYPTO</option>
                    <option value="SW_SECURE_DECODE">SW_SECURE_DECODE</option>
                    <option value="HW_SECURE_CRYPTO" selected>HW_SECURE_CRYPTO</option>
                    <option value="HW_SECURE_DECODE">HW_SECURE_DECODE</option>
                    <option value="HW_SECURE_ALL">HW_SECURE_ALL</option>
                </select>
            </div>

            <div style="min-height:120px;">
                <span id="log"></span>
            </div>

        </div>
    </div>

    <x-slot:scripts>
        <script src="{{ asset('js/shaka-compiled.min.js') }}"></script>
        <script>
            let dashUri = '{{ $videoUrl }}';
            let hlsUri = '{{ $videoUrl }}';
            let widevineLicenseUrl = 'https://widevine-dash.ezdrm.com/proxy?pX=D6A082';
            let playreadyLicenseUrl = 'https://playready.ezdrm.com/cency/preauth.aspx?pX=2AFB63';

            const video = document.getElementById('my-player');

            let videoR = 'HW_SECURE_ALL';
            let audioR = 'HW_SECURE_CRYPTO';

            function updateSettings() {
                videoR = document.getElementById('videoR').value;
                audioR = document.getElementById('audioR').value;

                // Reinitialize the player
                initApp();
            }
        </script>
        <script src="{{ asset('js/helper.js') }}"></script>
        <script src="{{ asset('js/shaka.js') }}"></script>
    </x-slot:scripts>

</x-layouts.app>
