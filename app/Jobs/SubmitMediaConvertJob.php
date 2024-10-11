<?php

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

    public function __construct($filePath, $inputPath, $outputPath)
    {
        $this->filePath = $filePath;
        $this->inputPath = $inputPath;
        $this->outputPath = $outputPath;
    }

    public function handle(MediaConvertService $mediaConvertService)
    {
        try {
            $mediaConvertService->createMediaConvertJob($this->filePath, $this->inputPath, $this->outputPath);
        } catch (\Exception $e) {
            Storage::disk('s3')->delete($this->filePath);
            throw $e;
        }
    }
}
