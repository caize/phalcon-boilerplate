<?php

namespace Promoziti\Models\Repositories\Repository;

use Promoziti\Lib\Core\DiHandler as DiHandler;

class RepositoryBase
{
    protected $dbconn;
    protected $dbmmgr;
    protected $config;

    public function __construct()
    {
        $this->dbconn = DiHandler::getDb();        
        $this->dbmmgr = DiHandler::getModelManager();
        $this->config = DiHandler::getConfig();        
    }
    
    public function showDbMessages($obj)
    {        
        foreach ($obj->getMessages() as $message)
        {
            echo "Message: ", $message->getMessage();
            echo "Field: ", $message->getField();
            echo "Type: ", $message->getType();
        }
    } 
}
