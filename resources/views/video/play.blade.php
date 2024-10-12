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

            <span id="browserCheckResult"></span>

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
        </script>
        <script src="{{ asset('js/helper.js') }}"></script>
        <script src="{{ asset('js/shaka.js') }}"></script>
    </x-slot:scripts>

</x-layouts.app>
