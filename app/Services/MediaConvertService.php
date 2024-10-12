<?php
/*!
 * File: app/Services/MediaConvertService.php
 * Description: This service handles interactions with AWS MediaConvert, including creating and monitoring video conversion jobs.
 * Author: Moiz Haider
 * Date: 12 October 2024
 */

namespace App\Services;

use Aws\MediaConvert\MediaConvertClient;
use Aws\Exception\AwsException;

class MediaConvertService
{
    protected MediaConvertClient $mediaConvertClient;

    // Constructor to initialize AWS MediaConvert client with configuration values
    public function __construct()
    {
        $this->mediaConvertClient = new MediaConvertClient([
            'region' => config('services.aws.region'),
            'version' => '2017-08-29',
            'credentials' => [
                'key' => config('services.aws.key'),
                'secret' => config('services.aws.secret'),
            ],
            'endpoint' => config('services.aws.endpoint'),
        ]);
    }

    // Create a MediaConvert job with specified input and output paths
    public function createMediaConvertJob($filePath, $inputPath, $outputPath)
    {
        $jobSettings = [
            'TimecodeConfig' => [
                'Source' => 'ZEROBASED',
            ],
            'OutputGroups' => [
                [
                    'CustomName' => 'encryptedVids',
                    'Name' => 'DASH ISO',
                    'Outputs' => [
                        [
                            'ContainerSettings' => [
                                'Container' => 'MPD',
                            ],
                            'VideoDescription' => [
                                'CodecSettings' => [
                                    'Codec' => 'H_264',
                                    'H264Settings' => [
                                        'MaxBitrate' => 50000000,
                                        'RateControlMode' => 'QVBR',
                                        'SceneChangeDetect' => 'TRANSITION_DETECTION',
                                    ],
                                ],
                            ],
                            'NameModifier' => '_vid',
                        ],
                        [
                            'ContainerSettings' => [
                                'Container' => 'MPD',
                            ],
                            'AudioDescriptions' => [
                                [
                                    'CodecSettings' => [
                                        'Codec' => 'AAC',
                                        'AacSettings' => [
                                            'Bitrate' => 256000,
                                            'CodingMode' => 'CODING_MODE_2_0',
                                            'SampleRate' => 48000,
                                        ],
                                    ],
                                ],
                            ],
                            'NameModifier' => '_aud',
                        ],
                    ],
                    'OutputGroupSettings' => [
                        'Type' => 'DASH_ISO_GROUP_SETTINGS',
                        'DashIsoGroupSettings' => [
                            'SegmentLength' => 30,
                            'Destination' => $outputPath,
                            'DestinationSettings' => [
                                'S3Settings' => [
                                    'AccessControl' => [
                                        'CannedAcl' => 'PUBLIC_READ',
                                    ],
                                ],
                            ],
                            'Encryption' => [
                                'SpekeKeyProvider' => [
                                    'ResourceId' => config('services.resource_id'),
                                    'SystemIds' => [
                                        config('services.system_ids.widevine'),
                                        config('services.system_ids.playready')
                                    ],
                                    'Url' => config('services.aws.speke'),
                                ],
                            ],
                            'FragmentLength' => 2,
                        ],
                    ],
                ],
            ],
            'FollowSource' => 1,
            'Inputs' => [
                [
                    'AudioSelectors' => [
                        'Audio Selector 1' => [
                            'DefaultSelection' => 'DEFAULT',
                        ],
                    ],
                    'VideoSelector' => [],
                    'TimecodeSource' => 'ZEROBASED',
                    'FileInput' => $inputPath,
                ],
            ],
        ];

        try {
            $result = $this->mediaConvertClient->createJob([
                'Queue' => config('services.aws.queue'),
                'Role' => config('services.aws.role'),
                'UserMetadata' => [],
                'Settings' => $jobSettings,
                'BillingTagsSource' => 'JOB',
                'AccelerationSettings' => [
                    'Mode' => 'DISABLED',
                ],
                'StatusUpdateInterval' => 'SECONDS_60',
                'Priority' => 0,
            ]);
            return $result['Job']['Id'];
        } catch (AwsException $e) {
            \Log::error('Error creating MediaConvert job: ' . $e->getMessage());
            throw new \Exception('Error creating MediaConvert job: ' . $e->getMessage());
        }
    }

    // Retrieve the status of a MediaConvert job by its ID
    public function getJobStatus($jobId)
    {
        try {
            $result = $this->mediaConvertClient->getJob(['Id' => $jobId]);
            return $result['Job']['Status'];
        } catch (AwsException $e) {
            throw new \Exception('Error fetching job status: ' . $e->getMessage());
        }
    }
}
