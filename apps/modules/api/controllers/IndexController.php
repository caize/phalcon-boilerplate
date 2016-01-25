<?php

namespace Promoziti\Modules\Api\Controllers;

use \Promoziti\Lib\Core\Sms\SmsHandler as SmsHandler;

class IndexController extends ControllerBase
{

    public function indexAction()
    {
        $this->setJsonResponse();

        $endpoint_list = array('business_url' => '/v1/business',
                                'promo_url' => '/v1/promo',
                                'member_url' => '/v1/member');
        echo json_encode($endpoint_list);
    }

    public function twiliotestAction()
    {
        $this->setJsonResponse();

        //$params = array('to' => '+51954047376',
        $params = array('to' => '+51975061142',
                            'message' => 'Estimada Schonita, el premio se va, se va, todavia hay tiempo para el viajecito a las bahamas!');
        $response = SmsHandler::send($params);

        echo json_encode($response);
    }    
}