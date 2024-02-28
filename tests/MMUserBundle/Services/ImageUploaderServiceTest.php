<?php
/**
 * Copyright (c) 2017. Mealmatch GmbH
 * Author: Krish Damani <k.damani@easternenterprise.com>
 */

namespace MMUserBundle\Services;

use Aws\S3\S3Client;
use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageUploaderServiceTest extends WebTestCase
{
    /**
     * Client used to interact with Amazon S3
     *
     * @var S3Client
     */
    protected $s3ClientMock;

    /**
     * Logger
     *
     * @var Logger 
     */
    protected $logMock;

    /**
     * bucket
     *
     * @var string
     */
    protected $bucket;

    /**
     * Basic setup
     */
    public function setUp()
    {
        $this->s3ClientMock = $this->getMockBuilder(S3Client::class)
            ->setMethods([
                'putObject',
                'doesObjectExist',
                'deleteObject'
            ])
            ->disableOriginalConstructor()
            ->getMock();

        $this->s3ClientMock->expects($this->any())
            ->method('putObject')
            ->willReturn('testFile.jpeg');

        $this->s3ClientMock->expects($this->any())
            ->method('doesObjectExist')
            ->willReturn(true);

        $this->s3ClientMock->expects($this->any())
            ->method('deleteObject')
            ->willReturn('testFile');

        $this->logMock = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->bucket = 'bucketName';
    }

    /**
     * ServiceDataManagerTest upload image
     */
    public function testImageCanBeUploadedByUrl()
    {
        $expected = 'testFile.jpeg';
        $url = 'http://test.meal-match.com/bundles/mmwebfront/images/mm/Logo_mm_48px_verbunden.jpg?version=v0.2.0';

        $service = new ImageUploaderService($this->s3ClientMock, $this->logMock, $this->bucket);
        $result = $service->uploadImageByUrl($url, "testFile");

        $this->assertEquals($result, $expected);
    }

    /**
     * ServiceDataManagerTest upload image without file name
     */
    public function testImageCanBeUploadedWithoutFileName()
    {
        $uploadMock = $this->getMockBuilder(UploadedFile::class)
            ->setMethods(['guessExtension', 'getMimeType'])
            ->disableOriginalConstructor()
            ->getMock();

        $uploadMock->expects($this->any())
            ->method('guessExtension')
            ->willReturn('jpeg');

        $uploadMock->expects($this->any())
            ->method('getMimeType')
            ->willReturn('image/jpeg');

        $service = new ImageUploaderService($this->s3ClientMock, $this->logMock, $this->bucket);
        $result = $service->upload($uploadMock);

        $this->assertNotEmpty($result);
    }

    /**
     * ServiceDataManagerTest upload image with file name
     */
    public function testImageCanBeUploadedWithFileName()
    {
        $expected = 'testFile.jpeg';
        $uploadMock = $this->getMockBuilder(UploadedFile::class)
            ->setMethods(['guessExtension', 'getMimeType'])
            ->disableOriginalConstructor()
            ->getMock();

        $uploadMock->expects($this->any())
            ->method('guessExtension')
            ->willReturn('jpeg');

        $uploadMock->expects($this->any())
            ->method('getMimeType')
            ->willReturn('image/jpeg');

        $service = new ImageUploaderService($this->s3ClientMock, $this->logMock, $this->bucket);
        $result = $service->upload($uploadMock, 'testFile');

        $this->assertEquals($result, $expected);
    }

    /**
     * ServiceDataManagerTest upload file with exception
     */
    public function testImageCanBeUploadedWithException()
    {
        $expected = 'testFile.jpeg';
        $uploadMock = $this->getMockBuilder(UploadedFile::class)
            ->setMethods(['guessExtension', 'getMimeType'])
            ->disableOriginalConstructor()
            ->getMock();

        $uploadMock->expects($this->any())
            ->method('guessExtension')
            ->willReturn('jpeg');

        $uploadMock->expects($this->any())
            ->method('getMimeType')
            ->willReturn('image/jpeg');

        $s3ClientMock = $this->getMockBuilder(S3Client::class)
            ->setMethods([
                'putObject',
                'doesObjectExist'
            ])
            ->disableOriginalConstructor()
            ->getMock();

        $s3ClientMock->expects($this->once())
            ->method('doesObjectExist')
            ->willReturn(true);

        $logMock = $this->getMockBuilder('Monolog\Logger')
            ->disableOriginalConstructor()
            ->setMethods(['addInfo', 'addCritical'])
            ->getMock();

        $service = new ImageUploaderService($s3ClientMock, $logMock, $this->bucket);
        $result = $service->upload($uploadMock, 'testFile');

        $this->assertEquals($result, $expected);
    }

    /**
     * ServiceDataManagerTest remove method with object exist at S3Client
     */
    public function testImageCanBeRemovedWithObjectExists()
    {
        $s3ClientMock = $this->getMockBuilder(S3Client::class)
            ->setMethods([
                'putObject',
                'doesObjectExist',
                'deleteObject'
            ])
            ->disableOriginalConstructor()
            ->getMock();

        $s3ClientMock->expects($this->once())
            ->method('doesObjectExist')
            ->willReturn(true);

        $s3ClientMock->expects($this->once())
            ->method('deleteObject')
            ->willReturn('testFile');

        $logMock = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $service = new ImageUploaderService($s3ClientMock, $logMock, $this->bucket);
        $result = $service->remove('testFile');

        $this->assertNull($result);
    }

    /**
     * ServiceDataManagerTest remove method with object does not exist
     */
    public function testImageCanBeRemovedWithNoObjectExists()
    {
        $s3ClientMock = $this->getMockBuilder(S3Client::class)
            ->setMethods([
                'putObject',
                'doesObjectExist',
                'deleteObject'
            ])
            ->disableOriginalConstructor()
            ->getMock();

        $s3ClientMock->expects($this->once())
            ->method('doesObjectExist')
            ->willReturn(false);

        $logMock = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $service = new ImageUploaderService($s3ClientMock, $logMock, $this->bucket);
        $result = $service->remove('testFile');

        $this->assertNull($result);
    }

    /**
     * ServiceDataManagerTest remove image method with exception
     */
    public function testImageCanBeRemovedWithException()
    {
        $s3ClientMock = $this->getMockBuilder(S3Client::class)
            ->setMethods([
                'putObject',
                'doesObjectExist'
            ])
            ->disableOriginalConstructor()
            ->getMock();

        $s3ClientMock->expects($this->once())
            ->method('doesObjectExist')
            ->willReturn(true);

        $logMock = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->setMethods(['addInfo', 'addCritical'])
            ->getMock();

        $service = new ImageUploaderService($s3ClientMock, $logMock, $this->bucket);
        $result = $service->remove('testFile');

        $this->assertNull($result);
    }
}
