<?php
namespace Promoziti\Lib\Core\Mail;

use \Promoziti\Lib\Core\DiHandler as DiHandler;

class MailHandler
{
    //TODO: implement $params['attachments'] 
    public static function send($params)
    {
        $output = array('success' => false,
                            'status' => '',
                            'id' => '',
                            'error_message' => '');

        $config = DiHandler::getConfig();
        if($config->application->mailer->default == 'mandrill')
        {
            $mandrill_config = $config->application->mailer->mandrill;

            $send_params = array('from' => $params['from'],
                                    'to' => $params['to'],
                                    'subject' => $params['subject'],
                                    'message' => $params['message']);
            
            if(isset($params['from_name']))
            {
                $send_params['fromname'] = $params['from_name'];
            }
            if(isset($params['to_name']))
            {
                $send_params['toname'] = $params['to_name'];          
            }            
            if(isset($params['important']))
            {
                $send_params['important'] = $params['important'];
            }
            if(isset($params['wildcards']) && is_array($params['wildcards']))
            {
                foreach ($params['wildcards'] as $wildcard => $value) {
                    $send_params['subject'] = str_ireplace('%'.$wildcard.'%', $value, $send_params['subject']);
                    $send_params['message'] = str_ireplace('%'.$wildcard.'%', $value, $send_params['message']);                                
                }
            }            

            $mandrill_obj = new MandrillWrapper($mandrill_config);
            $response = $mandrill_obj->send($send_params);
            $response_arr = json_decode($response, true);

            if(is_array($response_arr) && isset($response_arr[0]))
            {
                $response_arr = $response_arr[0];
                if(is_null($response_arr['reject_reason']))
                {
                    $output['success'] = true;
                    $output['id'] = $response_arr['_id'];
                }
                else
                {
                    $output['error_message'] = $response_arr['reject_reason'];    
                }

                $output['status'] = $response_arr['status'];
            }

            return $output;
        }
        else if($config->application->mailer->default == 'sendgrid') //TODO: implemented wrapper with SendGrid
        {

        }

        return $output;
    }

}   