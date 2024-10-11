{{--
    File: upload.blade.php
    Description: View to upload a new video file.
    Author: Moiz Haider
    Date: 11 October 2024
--}}

<x-layouts.app>

    <x-slot:title>Upload Video</x-slot:title>

    <h1 class="text-3xl font-bold mb-6">Upload Video</h1>

    <div class="mx-auto mt-8">
        <!-- Form to upload video -->
        <form action="{{ route('video.upload') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-4">
                <!-- Input field for video file -->
                <input type="file" name="video" id="video" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" accept="video/*" required>
                <!-- Display error message if validation fails -->
                @error('video')
                    <p class="text-red-500 mt-2">{{ $message }}</p>
                @enderror
            </div>
            <!-- Submit button to upload video -->
            <button type="submit" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">Upload Video</button>
        </form>
    </div>

</x-layouts.app>
