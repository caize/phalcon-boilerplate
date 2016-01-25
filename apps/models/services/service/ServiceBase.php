<?php

namespace Promoziti\Models\Services\Service;

use Promoziti\Lib\Core\DiHandler as DiHandler;

class ServiceBase
{
    public function __construct()
    {
        
    }

    public function wrapOutputCols($dta, $mode = null) //encode|decode
    {
		$obj_rflx = new \ReflectionClass($this);

        $class = "Promoziti\\Models\\Entities\\".$obj_rflx->getShortName();
        if(class_exists($class))
        {
            $obj = new $class();
            if(is_object($obj) && method_exists($obj,'wrapOutputCols'))
            {
            	$dta = $obj->wrapOutputCols($dta, $mode);
            }        	
        }
        
        return $dta;
    }
}
