<?php

namespace Promoziti\Models\Entities;

use \Promoziti\Lib\Core\CacheHandler as CacheHandler;

class ModelBase extends \Phalcon\Mvc\Model
{
   /******************************************
    * PUBLIC METHODS
    ******************************************/
    final public function wrapOutputCols($dta, $mode = null) //encode|decode
    {
        $keys = defined('static::TBL_WKEYS') && is_array(static::TBL_WKEYS) ? (static::TBL_WKEYS):array();
        $keys = ($mode=="encode") ? $keys :($mode=="decode" ? array_flip($keys):null);
        if(is_array($keys))
        {
            $dtakeys = array_keys($dta);

            foreach($keys as $k => $v)
            {
                $dki = array_search($k,$dtakeys);
                if($dki !== false)                
                {
                    $dtakeys[$dki] = $v;
                }
            }

            $dta = array_combine($dtakeys, $dta);
        }

        return $dta;
    }

    final public function getOutputCols($level = "full")
    {
        $dbx_cols = array();

        $obj_rflx = new \ReflectionClass($this);
        $obj_props   = $obj_rflx->getProperties(\ReflectionProperty::IS_PUBLIC);

        //fill dbcols
        foreach($obj_props as $prop)
        {
            array_push($dbx_cols,$prop->name);
        }

        //filter columns by level
        $out_options = defined('static::OUT') && is_array(static::OUT) ? (static::OUT):array();
        if(array_key_exists($level, $out_options))
        {
            $lvl_cols = $out_options[$level];
            $dbx_cols = array_intersect($dbx_cols, $lvl_cols);
        }

        return $dbx_cols;
    }

    final public function fillOutputCols($obj, $level = "full", $wrap_mode = null)
    {
        $dta = array();
        $dbx_cols = $this->getOutputCols($level);

        foreach($dbx_cols as $value)
        {
            $dta[$value] = is_object($obj) && property_exists($obj, $value) ? $obj->$value:null;
        }

        //wrap keys if it's valid mode
        $dta = $this->wrapOutputCols($dta,$wrap_mode);

        return $dta;
    }

    /******************************************
    * PUBLIC STATIC METHODS
    ******************************************/

    final public static function getCachedFields($tbl_pkval, $fields = '*')
    {
        $tbl = static::TBL;
        $tbl_pk  = static::TBL_PK;
        $cache_rs = null;

        if(strlen($tbl) && strlen($tbl_pk))
        {
            $field = $fields == '*' ? 'all':$fields;
            $cache_ky = "{$tbl}_{$tbl_pkval}_{$field}";

            $cache_rs = CacheHandler::get($cache_ky);
            if($cache_rs == null)
            {
                $cache_rs = self::findFirst(array(
                    'columns'    => $fields,
                    'conditions' => "{$tbl_pk} = ?1",
                    'bind'       => array(1 => $tbl_pkval)));
                CacheHandler::create($cache_ky, $cache_rs);
            }
        }

        return $cache_rs;
    }



}
