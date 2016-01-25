<?php 
namespace Promoziti\Models\Services;

use Promoziti\Models\Services\Exceptions;

abstract class Services
{
    public static function getService($name)
    {
        $className = "Promoziti\\Models\\Services\\Service\\{$name}";
        
        if ( ! class_exists($className)) {
            print_r(debug_backtrace());
            throw new Exceptions\InvalidServiceException("Class {$className} doesn't exists.");
        }
        
        return new $className();
    }
}
