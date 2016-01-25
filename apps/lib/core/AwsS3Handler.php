<?php
namespace Promoziti\Lib\Core;

use \Promoziti\Lib\Core\DiHandler as DiHandler;
use \Aws\Common\Exception\MultipartUploadException;
use \Aws\S3\Model\MultipartUpload\UploadBuilder;

class AwsS3Handler
{
    public static function upload($bucket, $source, $target, $options = array())
    {       
        $config = DiHandler::getConfig();
        $config_s3_buckets = $config->application->aws->services->s3->buckets;
        $bucket_info = null;

        if(property_exists($config_s3_buckets, $bucket))
        {
            $bucket_info = $config_s3_buckets->$bucket;
        }
        else
        {
            $bucket_info = $config_s3_buckets->tmp;
        }

        $s3 = DiHandler::getAwsS3();

        $uploader = UploadBuilder::newInstance()
            ->setClient($s3)
            ->setSource($source)
            ->setBucket($bucket_info->name)
            ->setKey($target)
            ->setOption('ACL', 'public-read')
            ->build();

        try
        {
            $result = $uploader->upload();
            if(isset($result['Location']))
            {
                return str_replace('%2F', '/', $result['Location']);
            }
            else
            {
                return false;
            }
        }
        catch (MultipartUploadException $e)
        {
            $uploader->abort();
            return false;
        }
    }
}