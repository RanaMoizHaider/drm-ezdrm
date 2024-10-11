{{--
    File: index.blade.php
    Description: View to list all available videos with options to play them.
    Author: Moiz Haider
    Date: 11 October 2024
--}}

<x-layouts.app>

    <x-slot:title>All Videos</x-slot:title>

    <main class="mt-6">

        <!-- Display success message if video is uploaded successfully -->
        @if(session('success'))
            <div class="mx-auto mt-8">
                <p class="text-green-600 mb-4">{{ session('success') }}</p>
            </div>
        @endif

        <!-- Check if there are videos -->
        @if(count($videos) > 0)
            <!-- List all videos -->
            <div class="grid gap-6 lg:grid-cols-2 lg:gap-8">
                @foreach ($videos as $video)
                    <a
                        href="{{ $video['status'] == 'ready' ? route('video.play', ['videoID' => $video['id']]) : '#'}}"
                        id="docs-card"
                        class="flex flex-col items-start gap-6 overflow-hidden rounded-lg bg-white p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] transition duration-300 hover:text-black/70 hover:ring-black/20 focus:outline-none focus-visible:ring-[#FF2D20] md:row-span-3 lg:p-10 lg:pb-10 dark:bg-zinc-900 dark:ring-zinc-800 dark:hover:text-white/70 dark:hover:ring-zinc-700 dark:focus-visible:ring-[#FF2D20]"
                    >
                        <div id="screenshot-container" class="relative flex w-full flex-1 items-stretch">
                            <img
                                src="{{ $video['poster'] }}"
                                alt="{{ $video['title'] }}"
                                class="aspect-video h-full w-full flex-1 rounded-[10px] object-top object-cover drop-shadow-[0px_4px_34px_rgba(0,0,0,0.25)]"
                            />
                        </div>

                        <div class="relative flex items-center gap-6 lg:items-end">
                            <div id="docs-card-content" class="flex items-start gap-6 lg:flex-col">
                                <div class="pt-3 sm:pt-5 lg:pt-0">
                                    <h2 class="text-xl font-semibold text-black dark:text-white">{{ $video['title'] }}</h2>
                                </div>
                            </div>

                            <svg class="size-6 shrink-0 stroke-[#FF2D20]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12h15m0 0l-6.75-6.75M19.5 12l-6.75 6.75"/></svg>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <!-- Message when no videos are available -->
            <p class="text-xl text-gray-500">No videos available.</p>
        @endif
    </main>

</x-layouts.app>
