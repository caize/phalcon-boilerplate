<?php
namespace Promoziti\Lib\Core;

use Phalcon\DI as DI;

class DiHandler
{
    public static function getModelManager()
    {
        return DI::getDefault()->getShared('modelsManager');
    }

    public static function getModelCache()
    {
        return DI::getDefault()->getShared('modelsCache');
    }

    public static function getConfig()
    {
        return DI::getDefault()->get('config');
    }

    public static function getDb()
    {
        return DI::getDefault()->getShared('db');
    }  

    public static function getSession()
    {
        return DI::getDefault()->getShared('session');
    }

    public static function getRequest()
    {
        return DI::getDefault()->get('request');
    }

    public static function getAwsS3()
    {
        return DI::getDefault()->get('aws_s3');
    }

    public static function getCookies()
    {
        return DI::getDefault()->getShared('cookies');
    }       

    public static function getLangEs()
    {
        return DI::getDefault()->get('lang_es');
    }    
}