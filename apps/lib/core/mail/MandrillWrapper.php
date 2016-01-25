<?php
namespace Promoziti\Lib\Core\Mail;

class MandrillWrapper
{

    private $config = null;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function send($params)
    {
        $host = 'https://mandrillapp.com/api/1.0';
        $endpoint = '/messages/send.json';

        if($this->config
            && property_exists($this->config, 'key'))
        {
            if(isset($params['from']) && strlen(trim($params['from'])) > 0
                && isset($params['to']) && strlen(trim($params['to'])) > 0
                && isset($params['subject']) && strlen(trim($params['subject'])) > 0
                && isset($params['message']) && strlen(trim($params['message'])) > 0)
            {
                $important          = isset($params['important']) && $params['from'] == true;
                $fromname           = isset($params['fromname']) && strlen(trim($params['fromname'])) > 0 
                                        ?   $params['fromname']:"";
                $toname             = isset($params['toname']) && strlen(trim($params['toname'])) > 0 
                                        ?   $params['toname']:"";

                $reply_to = isset($params['reply_to']) && strlen(trim($params['reply_to'])) > 0 ? $params['reply_to']:$params['from'];

                $fields = array('key' => $this->config->key,
                                'message' => array('html' => $params['message'],
                                                    'text' => strip_tags(str_replace(array("<br>", "<br />", "<br/>"), "\n", $params['message'])),
                                                    'subject' => $params['subject'],
                                                    'from_email' => $params['from'],
                                                    'to' => array(
                                                                array('email' => $params['to'],
                                                                        'type' => 'to')),
                                                    'headers' => array('Reply-To' => $reply_to),
                                                    'important' => $important),
                                'async' => false,

                                );

                if($fromname != '')
                {
                    $fields['message']['from_name'] = $fromname;
                }

                if($toname != '')
                {
                    $fields['message']['to'][0]['name'] = $toname;
                }
                
                $ch = curl_init();

                curl_setopt($ch, CURLOPT_URL, $host.$endpoint);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HEADER, 1);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));       
                               
                $response = curl_exec ($ch);

                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                $header = substr($response, 0, $header_size);
                $body = substr($response, $header_size);

                curl_close ($ch);
                
                return $body; //already in json format
            }
            else
            {
                echo "\n====\n\n";
                print_r($params);
                echo "\n====\n\n";

                throw new \Exception("Class '".get_class($this)."': missing from/to/subject/message params (".__LINE__.")");            
            }                
        }
        else
        {
            throw new \Exception("Class '".get_class($this)."': config is not properly set (".__LINE__.")");            
        }        
    }
}