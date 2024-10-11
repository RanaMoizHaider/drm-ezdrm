{{--
    File: play.blade.php
    Description: View to play a video using VdoCipher player.
    Author: Moiz Haider
    Date: 11 October 2024
--}}

<x-layouts.app>

    <x-slot:title>Play Video</x-slot:title>

    <div class="flex justify-center items-center">
        <div class="relative w-full md:w-2/3 lg:w-1/2">
            {{-- Video container with aspect ratio --}}
            <div style="padding-top:52.73%;position:relative;">
                {{-- VdoCipher player iframe --}}
                <iframe
                    src="https://player.vdocipher.com/v2/?otp={{ $cred['otp'] }}&playbackInfo={{ $cred['playbackInfo'] }}"
                    style="border:0;max-width:100%;position:absolute;top:0;left:0;height:100%;width:100%;"
                    allow="encrypted-media"
                    allowfullscreen
                >
                </iframe>
            </div>
        </div>
    </div>

</x-layouts.app>
