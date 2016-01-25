<?php
namespace Promoziti\Lib\Core;

use Promoziti\Lib\Core\DiHandler as DiHandler;

class CacheHandler
{
    public static function get($key)
    {
        $cache = DiHandler::getModelCache();
        return $cache->get($key);
    }

    public static function create($key, $obj)
    {
        $cache = DiHandler::getModelCache();
        $cache->save($key, $obj);
    }

    public static function delete($key)
    {
        $cache = DiHandler::getModelCache();
        $cache->delete($key);
    }
}