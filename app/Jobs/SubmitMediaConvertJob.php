<?php
/*!
 * File: app/Jobs/SubmitMediaConvertJob.php
 * Description: This job handles the submission of video conversion tasks to AWS MediaConvert. It manages the creation of MediaConvert jobs and handles any exceptions that occur during the process.
 * Author: Moiz Haider
 * Date: 12 October 2024
 */

namespace App\Jobs;

use App\Services\MediaConvertService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class SubmitMediaConvertJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $inputPath;
    protected $outputPath;

    /**
     * Constructor to initialize job with file paths.
     *
     * @param string $filePath Path to the uploaded video file.
     * @param string $inputPath S3 input path for the MediaConvert job.
     * @param string $outputPath S3 output path for the MediaConvert job.
     */
    public function __construct($filePath, $inputPath, $outputPath)
    {
        $this->filePath = $filePath;
        $this->inputPath = $inputPath;
        $this->outputPath = $outputPath;
    }

    /**
     * Handle the job to create a MediaConvert job.
     *
     * @param MediaConvertService $mediaConvertService Service to interact with AWS MediaConvert.
     * @return void
     * @throws \Exception If the MediaConvert job creation fails.
     */
    public function handle(MediaConvertService $mediaConvertService)
    {
        try {
            // Create a MediaConvert job with the provided paths
            $mediaConvertService->createMediaConvertJob($this->filePath, $this->inputPath, $this->outputPath);
        } catch (\Exception $e) {
            // Delete the uploaded file from S3 if job creation fails
            Storage::disk('s3')->delete($this->filePath);
            throw $e;
        }
    }
}
