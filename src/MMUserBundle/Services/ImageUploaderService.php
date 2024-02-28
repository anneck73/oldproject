<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMUserBundle\Services;

use Aws\S3\S3Client;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageUploaderService
{
    /** @var string s3 bucket name */
    protected $bucket;

    /** @var Logger $logger the logger to use */
    protected $logger;

    /** @var S3Client */
    protected $s3Client;

    /**
     * ImageUploaderService constructor.
     *
     * @param S3Client $pS3Client
     * @param Logger   $pLog
     * @param string   $pBucket
     */
    public function __construct(S3Client $pS3Client, Logger $pLog, $pBucket)
    {
        $this->s3Client = $pS3Client;
        $this->logger = $pLog;
        $this->bucket = $pBucket;
    }

    /**
     * @param string $url
     * @param string $fileNameWithoutExtension
     *
     * @return string
     */
    public function uploadImageByUrl($url, $fileNameWithoutExtension = '')
    {
        $imageContent = file_get_contents($url);
        $temporaryFileName = tempnam(sys_get_temp_dir(), 'mealmatch');
        file_put_contents($temporaryFileName, $imageContent);

        $image = new UploadedFile($temporaryFileName, 'photo.jpg');

        $fileName = $this->upload($image, $fileNameWithoutExtension);
        unlink($temporaryFileName);

        return $fileName;
    }

    /**
     * Uploads a file to the s3 bucket.
     *
     * @param UploadedFile $file
     * @param string       $fileNameWithoutExtension
     *
     * @return string
     */
    public function upload(UploadedFile $file, $fileNameWithoutExtension = '')
    {
        if (!$fileNameWithoutExtension) {
            $fileNameWithoutExtension = uniqid('MealMatch', true);
        }

        $fileName = $fileNameWithoutExtension.'.'.$file->guessExtension();

        // Extract the mime type
        $fileMime = $file->getMimeType() ?: 'image/png';

        $this->remove($fileName);

        try {
            $result = $this->s3Client->putObject(
                array(
                    'Bucket' => $this->bucket,
                    'Key' => $fileName,
                    'SourceFile' => $file,
                    'StorageClass' => 'REDUCED_REDUNDANCY',
                    'ACL' => 'public-read',
                    'ContentType' => $fileMime,
                    'ContentLength' => filesize($file),
                )
            );

            $this->logger->addInfo(
                'AWSWrite',
                array('Result' => $result)
            );
        } catch (\Exception $exception) {
            $this->logger->addCritical($exception->getMessage());
        }

        return $fileName;
    }

    /**
     * Remove the file from the s3 bucket.
     *
     * @param string $fileName
     */
    public function remove($fileName)
    {
        // Remove the image if it is already there
        if (!$this->s3Client->doesObjectExist($this->bucket, $fileName)) {
            return;
        }

        try {
            $result = $this->s3Client->deleteObject(
                array(
                    'Bucket' => $this->bucket,
                    'Key' => $fileName,
                )
            );

            $this->logger->addInfo(
                'AWSDelete',
                array('Result' => $result)
            );
        } catch (\Exception $exception) {
            $this->logger->addCritical($exception->getMessage());
        }
    }
}
