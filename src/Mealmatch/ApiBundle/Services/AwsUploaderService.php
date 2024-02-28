<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Services;

use Aws\S3\S3Client;
use Doctrine\Common\Collections\ArrayCollection;
use Mealmatch\ApiBundle\Model\LegalFileServiceData;
use Mealmatch\ApiBundle\Model\MealticketPDFServiceData;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @todo: Finish PHPDoc!
 * A summary informing the user what the class S3UploaderService does.
 *
 * A *description*, that can span multiple lines, to go _in-depth_ into the details of this element
 * and to provide some background information or textual references.
 */
class AwsUploaderService
{
    /** @var string s3 bucket name */
    protected $bucket;

    /** @var Logger $logger the logger to use */
    protected $logger;

    /** @var S3Client */
    protected $s3Client;

    /** @var string $kernelEnv */
    protected $kernelEnv;

    /**
     * AwsUploaderService constructor.
     *
     * @param S3Client $pS3Client
     * @param Logger   $pLog
     * @param string   $pBucket
     */
    public function __construct(S3Client $pS3Client, Logger $pLog, string $pBucket, string $kernelEnv = 'prod')
    {
        $this->s3Client = $pS3Client;
        $this->logger = $pLog;
        $this->bucket = $pBucket;
        $this->kernelEnv = $kernelEnv;
    }

    public function uploadByMealticketPDF(MealticketPDFServiceData $serviceData): MealticketPDFServiceData
    {
        $file = $serviceData->getFile();
        $fileName = $serviceData->getMealticketPDFEntity()->getFileName().'.'.$file->guessExtension();
        $fileMime = $file->getMimeType();

        $this->remove($fileName);
        $awsRequest = array(
            'Bucket' => $this->bucket,
            'Key' => $fileName,
            'SourceFile' => $file,
            'StorageClass' => 'REDUCED_REDUNDANCY',
            'ACL' => 'public-read',
            'ContentType' => $fileMime,
            'ContentLength' => filesize($file),
        );
        try {
            $result = $this->s3Client->putObject($awsRequest);
            $metaData = new ArrayCollection(
                array(
                    'AWSRequest' => $awsRequest,
                    'AWSResult' => $result->toArray(),
                ));
            $serviceData->getLegalFileEntity()->setFileMeta($metaData);

            $this->logger->addInfo(
                'AWSWrite',
                array('Result' => $result)
            );
        } catch (\Exception $exception) {
            $this->logger->addCritical($exception->getMessage());
            $serviceData->addError($exception->getMessage());
            $serviceData->setValidity(false);
        }

        return $serviceData;
    }

    public function uploadByLegalFile(LegalFileServiceData $fileServiceData): LegalFileServiceData
    {
        $file = $fileServiceData->getFile();
        $fileName = $fileServiceData->getLegalFileEntity()->getFileName().'.'.$file->guessExtension();
        $fileMime = $file->getMimeType();

        $this->remove($fileName);
        $awsRequest = array(
            'Bucket' => $this->bucket,
            'Key' => $fileName,
            'SourceFile' => $file,
            'StorageClass' => 'REDUCED_REDUNDANCY',
            'ACL' => 'public-read',
            'ContentType' => $fileMime,
            'ContentLength' => filesize($file),
        );
        try {
            $result = $this->s3Client->putObject($awsRequest);
            $metaData = new ArrayCollection(
                array(
                    'AWSRequest' => $awsRequest,
                    'AWSResult' => $result->toArray(),
                ));
            $fileServiceData->getLegalFileEntity()->setFileMeta($metaData);

            $this->logger->addInfo(
                'AWSWrite',
                array('Result' => $result)
            );
        } catch (\Exception $exception) {
            $this->logger->addCritical($exception->getMessage());
            $fileServiceData->addError($exception->getMessage());
            $fileServiceData->setValidity(false);
        }

        return $fileServiceData;
    }

    /**
     * Uploads a file by URL.
     *
     * @param string $url                      the url to be used
     * @param string $fileNameWithoutExtension the filename
     *
     * @return string the file name uploaded
     */
    public function uploadFileByUrl(string $url, string $fileNameWithoutExtension = ''): string
    {
        $fileContent = file_get_contents($url);

        $temporaryFileName = tempnam(sys_get_temp_dir(), 'mealmatch');
        file_put_contents($temporaryFileName, $fileContent);

        $uploadedFile = new UploadedFile($temporaryFileName, 'upload.tmp');

        $fileName = $this->upload($uploadedFile, $fileNameWithoutExtension);

        unlink($temporaryFileName);

        return $fileName;
    }

    /**
     * Uploads a file to the s3 bucket. Ignoring all Exceptions!!!
     *
     *
     * @param string $fileData
     * @param string $fileNameWithoutExtension
     * @param mixed  $fileName
     *
     * @return string
     */
    public function uploadByFileData(string $fileData, $fileName = 'Mealmatch/defaultByFileData.tmp'): string
    {
        $file = file_put_contents($fileName, $fileData);
        $fileMime = $file->getMimeType();

        if ('dev' === $this->kernelEnv) {
            return $fileName;
        }

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
     * Uploads a file to the s3 bucket. Ignoring all Exceptions!!!
     *
     * @param UploadedFile $file
     * @param string       $fileNameWithoutExtension
     *
     * @return string
     */
    public function upload(UploadedFile $file, $fileNameWithoutExtension = ''): string
    {
        if (!$fileNameWithoutExtension) {
            $fileNameWithoutExtension = uniqid('MealMatch', true);
        }

        $fileName = $fileNameWithoutExtension.'.'.$file->guessExtension();
        $fileMime = $file->getMimeType();

        if ('dev' === $this->kernelEnv) {
            return $fileName;
        }

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
