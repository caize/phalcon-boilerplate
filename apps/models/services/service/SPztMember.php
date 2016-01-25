<?php
namespace Promoziti\Models\Services\Service;

use \Promoziti\Models\Repositories\Repositories,
    \Promoziti\Models\Services\Services as Services,
    \Promoziti\Lib\Core\DiHandler as DiHandler,
    \Promoziti\Lib\Core\CacheHandler as CacheHandler,
    \Promoziti\Lib\Core\Mail\MailHandler as MailHandler,
    \Promoziti\Lib\Core\AwsS3Handler as AwsS3Handler,
    \Promoziti\Lib\Core\Sms\SmsHandler as SmsHandler;

class SPztMember extends ServiceBase
{    
    private function _getMemberToken($pzt_member_id, $app_token, $create_if_not_exists = true)
    {
        $token_key = null;
        if($create_if_not_exists)
        {
            $config = DiHandler::getConfig();
            $token_key = $config->application->security->member->token_key;            
        }

        return Repositories::getRepository('RPztMemberToken')->getMemberToken($pzt_member_id, $app_token, $token_key);
    }

    public function login($params, $lang)
    {
        $config = DiHandler::getConfig();

        if(isset($params['password']))
        {
            $salt = $config->application->security->member->salt;
            $params['password'] .= $salt;
        }

        $member_obj = Repositories::getRepository('RPztMember')->login($params);
        if(is_object($member_obj))
        {
            Repositories::getRepository('RPztMember')->recordLoginAction($member_obj->pzt_member_id);
            $member_token_obj = $this->_getMemberToken($member_obj->pzt_member_id, $params['app_token'], true);
            if(is_object($member_token_obj) && property_exists($member_token_obj, 'member_token'))
            {
                return $member_token_obj;
            }
            else
            {
                return Services::getService('SPztSysResCode')->getSysResData(true, 'E002-R007', $lang);
            }
        }
        else
        {
            return Services::getService('SPztSysResCode')->getSysResData(true, 'E002-R006', $lang);
        }
    }

    public function me($params, $lang)
    {
        $member_obj = Repositories::getRepository('RPztMember')->me($params);
        if(is_object($member_obj))
        {
            return $member_obj;
        }
        else
        {
            return Services::getService('SPztSysResCode')->getSysResData(true, 'E002-R016', $lang);
        }
    }

    public function getLast()
    {
        return Repositories::getRepository('RPztMember')->getLast();
    }

    public function emailExists($email, $lang)
    {
        $errors = array();
        if(Repositories::getRepository('RPztMember')->emailExists($email))
        {
            $errors['email'] = $email;
            //return Services::getService('SPztSysResCode')->getSysResData($errors, 'E002-R002', $lang);
        }
        else
        {
            return false;
        }
    }

    public function emailOwnedByAnother($email, $pzt_member_id, $lang)
    {
        $errors = array();
        if(Repositories::getRepository('RPztMember')->emailOwnedByAnother($email, $pzt_member_id))
        {
            $errors['email'] = $email;
            return Services::getService('SPztSysResCode')->getSysResData($errors, 'E002-R009', $lang);
        }
        else
        {
            return false;
        }
    }

    public function mobileExists($mobile, $lang)
    {
        $errors = array();
        if(Repositories::getRepository('RPztMember')->mobileExists($mobile))
        {
            $errors['mobile'] = $mobile;
            return Services::getService('SPztSysResCode')->getSysResData($errors, 'E002-R004', $lang);
        }
        else
        {
            return false;
        }
    }

    public function mobileOwnedByAnother($mobile, $pzt_member_id, $lang)
    {
        $errors = array();
        if(Repositories::getRepository('RPztMember')->mobileOwnedByAnother($mobile, $pzt_member_id))
        {
            $errors['mobile'] = $mobile;
            return Services::getService('SPztSysResCode')->getSysResData($errors, 'E002-R011', $lang);
        }
        else
        {
            return false;
        }        
    }

    /*
     * After verifications called from the controller, the data is saved on the database
     */
    public function signup($params, $lang)
    {
        $config = DiHandler::getConfig();
        $config_email = $config->application->member->communications->welcome->email;
        
        //if registration is from social network then email verification is not necessary
        if(isset($params['network']) && strlen(trim($params['network'])) > 0)
        {
            $config_email = $config->application->member->communications->welcome_social->email;
        }

        if(isset($params['password']))
        {
            $salt = $config->application->security->member->salt;
            $params['password'] = password_hash($params['password'].$salt, PASSWORD_BCRYPT);
        }
      
        $member_obj = Repositories::getRepository('RPztMember')->signup($params, $config->application->lang, $config->application->security);
        if(is_object($member_obj))
        {
            $token_key = $config->application->security->member->token_key;

            //sending welcome email to new member with the link to activate their account
            $exp_hours = $config->application->member->communications->validate->expiration_hours;
            $everification_config = array('token_key' => $config->application->security->member->token_key,
                                        'exp_hours' => $exp_hours);
            $member_obj = Repositories::getRepository('RPztMember')->generateEmailVerificationToken($member_obj, $everification_config);

            //verification token generated, proceed sending by email
            if(strlen($member_obj->email_verification_token) > 0)
            {
                $front_uri = $config->application->uri->front;

                $message    = $config_email->message;
                $message = str_replace('%expiration_hours%', $exp_hours, $message);
                $message = str_replace('%action_link_from%', '<a href="'.$front_uri.'member/validate?t='.$member_obj->email_verification_token.'" target="_blank">', $message);
                $message = str_replace('%action_link_to%', '</a>', $message);

                $send_params = array('from' => $config_email->from,
                                    'from_name' => $config_email->from_name,
                                    'to' => $member_obj->email,
                                    'to_name' => $member_obj->first_name,
                                    'subject' => $config_email->subject,
                                    'message' => $message);

                //MailHandler::send($send_params);
            }

            Repositories::getRepository('RPztMember')->recordLoginAction($member_obj->pzt_member_id);
            $member_token_obj = $this->_getMemberToken($member_obj->pzt_member_id, $params['app_token'], true);
            if(is_object($member_token_obj) && property_exists($member_token_obj, 'member_token'))
            {
                return $member_token_obj;
            }
            else
            {
                return Services::getService('SPztSysResCode')->getSysResData(true, 'E002-R007', $lang);
            }
        }
        else
        {
            return Services::getService('SPztSysResCode')->getSysResData(true, 'E002-R005', $lang);
        }
    }

    public function sendMobileVerificationCode($member_obj, $mobile, $lang)
    {
        $config = DiHandler::getConfig();
        $message = $config->application->member->communications->validate_mobile->sms->message;

        $code = mt_rand(10000, 99999);
        $message = str_replace('%first_name%', $member_obj->first_name, $message);
        $message = str_replace('%code%', $code, $message);

        $full_mobile = '+'.$member_obj->PztSysCountry->call_code.$mobile;

        //$params = array('to' => '+51954047376',
        $params = array('to' => $full_mobile,
                            'message' => $message);
        $response = SmsHandler::send($params);
        if(is_array($response) && isset($response['success']) && $response['success'] === true)
        {
            Repositories::getRepository('RPztMember')->sendMobileVerificationCode($member_obj->pzt_member_id, $mobile, $code);
            return $response;
        }
        else
        {
            return Services::getService('SPztSysResCode')->getSysResData(true, 'E002-R012', $lang);
        }
    }

    public function mobileVerifyCode($member_obj, $code, $lang)
    {
        $is_valid = Repositories::getRepository('RPztMember')->mobileVerifyCode($member_obj->pzt_member_id, $code);
        if($is_valid === true)
        {
            return true;
        }
        else
        {
            return Services::getService('SPztSysResCode')->getSysResData(true, 'E002-R014', $lang);
        }
    }

    public function getPointAccount($member_obj, $lang)
    {
        Repositories::getRepository('RPztMember')->checkPointAccount($member_obj);

        $member_point_obj = Repositories::getRepository('RPztMember')->getPointAccount($member_obj->pzt_member_id);
        if(is_object($member_point_obj) && property_exists($member_point_obj, 'pzt_member_point_id'))
        {
            return $member_point_obj;
        }
        else
        {
            return Services::getService('SPztSysResCode')->getSysResData(true, 'E002-R017', $lang);
        }
    }

    public function getActivityList($member_obj, $lang)
    {
        $list = Repositories::getRepository('RPztMember')->getActivityList($member_obj->pzt_member_id);
        if(is_object($list))
        {
            $out = array();
            foreach ($list as $key => $obj) {
                $tmp = $obj->toArray();
                unset($tmp['pzt_member_activity_id']);
                unset($tmp['pzt_member_id']);
                unset($tmp['pzt_sale_id']);
                array_push($out, $tmp);
            }

            return $out;
        }
        else
        {
            return Services::getService('SPztSysResCode')->getSysResData(true, 'E002-R017', $lang);
        }
    }

    public function getMenu($member_obj, $lat, $lon, $lang)
    {
        $config = DiHandler::getConfig();
        $metrics = $config->application->metrics;

        $radius_promos = $config->application->location_radius->promos;
        $radius_branches = $config->application->location_radius->branches;

        $for_you_count = Repositories::getRepository('RPztPromo')->countForYouMenu($member_obj->pzt_member_id);

        //TODO: use cache for country based count
        $promos_count = Repositories::getRepository('RPztPromo')->countForMenu($member_obj->pzt_member_id, 'branch');
        $promos_near_count = Repositories::getRepository('RPztPromo')->countForMenuByLocation($member_obj->pzt_member_id, $lat, $lon, $radius_promos, $metrics);

        $branches_count = Repositories::getRepository('RPztBranch')->countForMenu($member_obj->pzt_member_id);
        $branches_near_count = Repositories::getRepository('RPztBranch')->countForMenuByLocation($member_obj->pzt_member_id, $lat, $lon, $radius_branches, $metrics);

        $delivery_count = Repositories::getRepository('RPztPromo')->countForMenu($member_obj->pzt_member_id, 'delivery');

        $all_near_count = 0; //TODO: Develop this section

        $points_star = false; //TODO: Develo this later

        $response = array('for_you' => array('show' => ($for_you_count*1 > 0),
                                            'star' => false,
                                            'num' => $for_you_count*1,
                                            'num_near' => null),
                            'promos' => array('show' => true,
                                            'star' => false,
                                            'num' => $promos_count*1,
                                            'num_near' => $promos_near_count),
                            'branches' => array('show' => true,
                                            'star' => false,
                                            'num' => $branches_count*1,
                                            'num_near' => $branches_near_count),
                            'delivery' => array('show' => true,
                                            'star' => false,
                                            'num' => $delivery_count*1,
                                            'num_near' => null),
                            'near' => array('show' => false,
                                            'star' => false,
                                            'num' => $all_near_count,
                                            'num_near' => null),
                            'points' => array('show' => true,
                                            'star' => $points_star,
                                            'num' => null,
                                            'num_near' => null));

        return $response;
    }    


    /*
     * After verifications called from the controller, the data is saved on the database
     */
    public function OLDactivate($pzt_member_id, $params, $lang)
    {
        $config = DiHandler::getConfig();

        $member_obj = Repositories::getRepository('RPztMember')->activate($pzt_member_id, $params, $config->application->lang);
        if(is_object($member_obj))
        {
            if(is_array($params['avatar']) && count($params['avatar']))
            {
                //uploading to S3
                $source = $params['avatar']['file_obj']->getTempName();
                $target = $pzt_member_id.'/avatar/'.$params['avatar']['file_obj']->getName();

                $options = array('acl' => 'public-read');
                $public_endpoint = AwsS3Handler::upload('member', $source, $target, $options);

                if($public_endpoint)
                {
                    unlink($source);

                    $member_obj->avatar = $public_endpoint;
                    $member_obj->save();
                }
            }

            return $member_obj;
        }
        else
        {
            return Services::getService('SPztSysResCode')->getSysResData(true, 'MISSING!', $lang);
        }
    }

    public function getMemberByField($params, $lang)
    {
        $config = DiHandler::getConfig();

        $member_obj = Repositories::getRepository('RPztMember')->getMemberByField($params);
        if(is_object($member_obj))
        {
            return $member_obj;
        }
        else
        {
            return Services::getService('SPztSysResCode')->getSysResData(true, 'MISSING', $lang);
        }
    }

    public function OLDgetMemberById($pzt_member_id, $lang, $status)
    {
        $member_obj = Repositories::getRepository('RPztMember')->getMemberById($pzt_member_id, $status);
        if(is_object($member_obj))
        {
            return $member_obj;
        }
        else
        {
            return Services::getService('SPztSysResCode')->getSysResData(true, 'E002-R071', $lang);
        }
    }

    public function OLDgetMemberByIds($pzt_member_ids, $lang, $status)
    {
        $member_obj = Repositories::getRepository('RPztMember')->getMemberByIds($pzt_member_ids, $status);
        if(is_object($member_obj) && count($member_obj))
        {
            return $member_obj;
        }
        else
        {
            return Services::getService('SPztSysResCode')->getSysResData(true, 'E002-R071', $lang);
        }
    }

    // Member Social Methods ////////////////
    public function socialConnectExists($params, $lang)
    {
        $errors = array();
        if(Repositories::getRepository('RPztMember')->socialConnectExists($params))
        {
            $errors['network'] = $params['network'];
        }
        return Services::getService('SPztSysResCode')->getSysResData($errors, 'E002-R025', $lang);

    }

    public function OLDforgot($pzt_member_id, $type, $lang)
    {
        $config = DiHandler::getConfig();
        $config_forgot          = $config->application->member->communications->forgot;
        //$frequency_hours        = $config_forgot->frequency_hours;
        $forgot_email           = $config_forgot->email->from;
        $forgot_email_name      = $config_forgot->email->from_name;
        $forgot_email_subject   = $config_forgot->email->subject;

        $member_obj = Repositories::getRepository('RPztMember')->getMemberById($pzt_member_id);
        if(is_object($member_obj) && property_exists($member_obj, 'pzt_member_id'))
        {
            $exp_hours = $config->application->member->communications->forgot->expiration_hours;
            $everification_config = array('token_key' => $config->application->security->member->token_key,
                                        'exp_hours' => $exp_hours);
            $member_obj = Repositories::getRepository('RPztMember')->generatePasswordResetToken($member_obj, $everification_config);

            //verification token generated, proceed sending by email
            if(strlen($member_obj->password_reset_token) > 0)
            {
                $front_uri = $config->application->uri->front;

                $message = "";
                if($type == 'password')
                {
                    $message = $config_forgot->email->message;
                    $message = str_replace('%expiration_hours%', $exp_hours, $message);
                    $message = str_replace('%action_link_from%', '<a href="'.$front_uri.'member/reset?t='.$member_obj->password_reset_token.'" target="_blank">', $message);
                    $message = str_replace('%action_link_to%', '</a>', $message);
                }
                else if($type == 'alias')
                {
                    $message = $config_forgot->email->message_alias;
                    $message = str_replace('%alias%', $member_obj->alias, $message);
                }

                $send_params = array('from' => $forgot_email,
                                    'from_name' => $forgot_email_name,
                                    'to' => $member_obj->email,
                                    'to_name' => $member_obj->first_name,
                                    'subject' => $forgot_email_subject,
                                    'message' => $message);

                $response = MailHandler::send($send_params);
                if(is_array($response) && isset($response['id']) && strlen($response['id']) > 0)
                {
                    return $member_obj;
                }
                else
                {
                    //TODO: what happens if email fails to send, should we remove the mate request so they can try again?
                    $reason = is_array($response) && isset($response['error_message']) ? $response['error_message']:'';
                    $errors = array('reason'=>$reason);

                    return Services::getService('SPztSysResCode')->getSysResData($errors, 'E002-R027', $lang);
                }                
            }
            else
            {
                return Services::getService('SPztSysResCode')->getSysResData($errors, 'E002-R055', $lang);
            }
        }
        else
        {
            return Services::getService('SPztSysResCode')->getSysResData(true, 'E002-R032', $lang);
        }
    }

    public function OLDvalidate($email_verification_token, $app_token, $lang)
    {
        $config = DiHandler::getConfig();
        $config_email = $config->application->member->communications->welcome->email;

        $member_obj = Repositories::getRepository('RPztMember')->getMemberByEmailVerificationToken($email_verification_token);
        if(is_object($member_obj) && property_exists($member_obj, 'pzt_member_id'))
        {
            if(!$member_obj->email_validated)
            {
                if($member_obj->email_verification_expires >= date('Y-m-d H:i:s'))
                {
                    $member_token_obj = $this->_getMemberToken($member_obj->pzt_member_id, $app_token, true);
                    if(is_object($member_token_obj) && property_exists($member_token_obj, 'member_token'))
                    {
                        Repositories::getRepository('RPztMember')->validate($member_obj);
                        return $member_token_obj;
                    }
                    else
                    {
                        return Services::getService('SPztSysResCode')->getSysResData(true, 'E002-R054', $lang);
                    }
                }
                else //token expired
                {
                    $token_key = $config->application->security->member->token_key;

                    //sending welcome email to new member with the link to activate their account
                    $exp_hours = $config->application->member->communications->validate->expiration_hours;
                    $everification_config = array('token_key' => $config->application->security->member->token_key,
                                                'exp_hours' => $exp_hours);
                    $member_obj = Repositories::getRepository('RPztMember')->generateEmailVerificationToken($member_obj, $everification_config);

                    //verification token generated, proceed sending by email
                    if(strlen($member_obj->email_verification_token) > 0)
                    {
                        $front_uri = $config->application->uri->front;

                        $message = $config_email->message;
                        $message = str_replace('%expiration_hours%', $exp_hours, $message);
                        $message = str_replace('%action_link_from%', '<a href="'.$front_uri.'member/validate?t='.$member_obj->email_verification_token.'" target="_blank">', $message);
                        $message = str_replace('%action_link_to%', '</a>', $message);

                        $send_params = array('from' => $config_email->from,
                                            'from_name' => $config_email->from_name,
                                            'to' => $member_obj->email,
                                            'to_name' => $member_obj->first_name,
                                            'subject' => $config_email->subject,
                                            'message' => $message);

                        MailHandler::send($send_params);
                        return $member_obj;
                    }
                    else
                    {
                        return Services::getService('SPztSysResCode')->getSysResData(true, 'E002-R053', $lang);    
                    }
                }                
            }
            else //already validated
            {
                return Services::getService('SPztSysResCode')->getSysResData(true, 'E002-R052', $lang);
            }
        }
        else //can't find email verification token
        {
            return Services::getService('SPztSysResCode')->getSysResData(true, 'E002-R049', $lang);
        }
    }

    public function OLDreset($password_reset_token, $password, $app_token, $lang)
    {
        $config = DiHandler::getConfig();
        $salt = $config->application->security->member->salt;
        $password_enc = password_hash($password.$salt, PASSWORD_BCRYPT);

        $member_obj = Repositories::getRepository('RPztMember')->getMemberByPasswordResetToken($password_reset_token);
        if(is_object($member_obj) && property_exists($member_obj, 'pzt_member_id'))
        {
            if($member_obj->password_reset_expires >= date('Y-m-d H:i:s'))
            {
                $member_token_obj = $this->_getMemberToken($member_obj->pzt_member_id, $app_token, true);
                if(is_object($member_token_obj) && property_exists($member_token_obj, 'member_token'))
                {
                    Repositories::getRepository('RPztMember')->resetPassword($member_obj, $password_enc);
                    return $member_token_obj;
                }
                else
                {
                    return Services::getService('SPztSysResCode')->getSysResData(true, 'E002-R054', $lang);
                }
            }
            else //token expired
            {
                return Services::getService('SPztSysResCode')->getSysResData(true, 'E002-R056', $lang);    
            }                
        }
        else //can't find email verification token
        {
            return Services::getService('SPztSysResCode')->getSysResData(true, 'E002-R057', $lang);
        }
    }

    public function OLDsearch($term, $params, $lang)
    {
        //making sure params have valid data limits
        $default_offset = 0;
        $default_limit = 20;
        $max_limit = 50;

        if(isset($params['offset']))
        {
            $params['offset'] = !$params['offset'] || $params['offset'] < 0 ? $default_offset:$params['offset'];
        }
        else
        {
            $params['offset'] = $default_offset;
        }

        if(isset($params['limit']))
        {
            if(is_numeric($params['limit']) && $params['limit'] > $max_limit)
            {
                $params['limit'] = $max_limit;
            }
            else
            {
                $params['limit'] = !$params['limit'] || $params['limit'] < 0 ? $default_limit:$params['limit'];
            }
        }
        else
        {
            $params['limit'] = $default_limit;
        }

        $members = Repositories::getRepository('RPztMember')->search($term, $params);
        if($members !== false)
        {
            return $members;
        }        
        else
        {
            return Services::getService('SPztSysResCode')->getSysResData(true, 'E002-R079', $lang);
        }
    }
    public function getMembersByBusiness($pzt_business_id,$idsString='')
    {
        $members_obj= Repositories::getRepository('RPztMember')->findMembersByBusiness($pzt_business_id,$idsString);

        return $members_obj;
    }
    public function findMemberById($pzt_member_id)
    {

        $member_obj = Repositories::getRepository('RPztMember')->findMemberById($pzt_member_id);
        if(is_object($member_obj))
        {
            return $member_obj;
        }
        else
        {
            return false;
        }
    }

    public function update($pzt_member_id, $params, $lang)
    {
        $config = DiHandler::getConfig();

        $member_obj = Repositories::getRepository('RPztMember')->update($pzt_member_id, $params);
        if(is_object($member_obj) && property_exists($member_obj, 'pzt_member_id'))
        {   
            return $member_obj;
        }
        else
        {
           return false;
        }
    }

    public function uploadAvatar($pzt_member_id, $avatar, $lang)
    {
        $config = DiHandler::getConfig();

        $member_obj = Repositories::getRepository('RPztMember')->findMemberById($pzt_member_id);
        if(is_object($member_obj) && property_exists($member_obj, 'pzt_member_id'))
        {   
            if(isset($avatar) && strlen($avatar))
            {

                /*
                $filename = $attachment->filename;
                // write the file to the directory you want to save it in
                if ($fp = fopen($attachment_path.$filename, 'w'))
                {
                    while($bytes = $attachment->read())
                    {
                        fwrite($fp, $bytes);
                    }
                    fclose($fp);

                    echo "\n\n".$attachment_path.$filename."\n";
                }
                */






                $avatar = str_replace('data:image/jpeg;base64,', '', $avatar);
                $avatar = str_replace(' ', '+', $avatar);
                $avatar_decode = base64_decode($avatar);
                $avatar_image = file_put_contents('/tmp/avatar.jpeg', $avatar_decode);
                //$avatar_image = file_get_contents('/tmp/image.jpeg');
                $avatar_data = @getimagesize('/tmp/avatar.jpeg'); 

                $tes_member = array(); 
                $tes_member['BIT'] = $avatar_image;
                //$tes_member['IMG'] = $avatar_data;
                $tes_member['member'] = $pzt_member_id;

                //uploading to S3 
                //jamet
/*
                if(is_array($params['avatar']) && count($params['avatar']))
                {
                    //uploading to S3
                    $source = $params['avatar']['file_obj']->getTempName();
                    $target = $pzt_member_id.'/avatar/'.$params['avatar']['file_obj']->getName();

                    $options = array('acl' => 'public-read');
                    $public_endpoint = AwsS3Handler::upload('member', $source, $target, $options);

                    if($public_endpoint)
                    {
                        unlink($source);

                        $member_obj->avatar = $public_endpoint;
                        $member_obj->save();
                    }
                }
*/

                $source = "/tmp/avatar.jpeg";
                $target = $pzt_member_id.'/avatar/avatar.jpeg';

                $options = array('acl' => 'public-read');
                $public_endpoint = AwsS3Handler::upload('pztrepo', $source, $target, $options);

                if($public_endpoint)
                {
                    unlink($source);

                    $member_obj->avatar_path = $public_endpoint;
                    $member_obj->save();

                    $tes_member["upload"] = "success";
                }
                else
                {
                    $tes_member["upload"] = "error - ".$public_endpoint;
                }
                
                //return $member_obj;
                return $tes_member;
            }
            else 
            {
                return false;
            }
        }
        else
        {
           return false;
        }
    }
}


/*
txt file cache
// Query all records from model parts
$parts = Parts::find();

// Store the resultset into a file
file_put_contents("cache.txt", serialize($parts));

// Get parts from file
$parts = unserialize(file_get_contents("cache.txt"));

// Traverse the parts
foreach ($parts as $part) {
    echo $part->id;
}
*/