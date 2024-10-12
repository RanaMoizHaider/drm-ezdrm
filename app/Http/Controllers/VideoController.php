<?php
/*!
 * File: app/Http/Controllers/VideoController.php
 * Description: This controller handles video-related operations such as listing, uploading, and playing videos stored in an S3 bucket.
 * Author: Moiz Haider
 * Date: 12 October 2024
 */

namespace App\Http\Controllers;

use App\Jobs\SubmitMediaConvertJob;
use Illuminate\Http\Request;
use App\Services\MediaConvertService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class VideoController extends Controller
{
    protected $mediaConvertService;

    // Constructor to initialize MediaConvertService
    public function __construct(MediaConvertService $mediaConvertService)
    {
        $this->mediaConvertService = $mediaConvertService;
    }

    // List all videos in the "encryptedvideos" directory in S3
    public function index()
    {
        $directories = Storage::disk('s3')->directories('ezdrm/encryptedvideos');

        // Generate random tokens for each folder and store them in the session
        $videos = [];
        foreach ($directories as $directory) {
            $folderName = basename($directory);
            $token = Str::random(40);
            session()->put($token, $folderName);
            $videos[] = [
                'title' => $folderName,
                'token' => $token,
            ];
        }

        return view('video.index', compact('videos'));
    }

    // Show the video upload form
    public function showUploadForm()
    {
        return view('video.upload');
    }

    // Handle video upload and dispatch a job to convert the video
    public function uploadVideo(Request $request)
    {
        $request->validate([
            'video' => 'required|mimes:mp4|max:200000',
        ]);

        $file = $request->file('video');
        $filename = $file->getClientOriginalName();
        $filenameWithoutExtension = pathinfo($filename, PATHINFO_FILENAME);
        $filePath = Storage::disk('s3')->putFileAs('ezdrm/videos', $file, $filename);

        $accessToken = Str::random(40);
        session()->put($accessToken, $filename);

        $inputPath = 's3://' . config('filesystems.disks.s3.bucket') . '/' . $filePath;
        $outputPath = 's3://' . config('filesystems.disks.s3.bucket') . '/ezdrm/encryptedvideos/' . $filenameWithoutExtension . '/';

        SubmitMediaConvertJob::dispatch($filePath, $inputPath, $outputPath);

        return redirect()->route('video.index');
    }

    // Play the video by generating a signed URL for the .mpd manifest
    public function playVideo($token)
    {
        $folderName = session()->get($token);
        if (!$folderName) {
            abort(403, "Unauthorized access");
        }

        $videoUrl = Storage::disk('s3')->url("ezdrm/encryptedvideos/{$folderName}/{$folderName}.mpd");

        return view('video.play', compact('videoUrl'));
    }
}
