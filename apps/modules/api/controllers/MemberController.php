<?php

namespace Promoziti\Modules\Api\Controllers;

use \Promoziti\Models\Services\Services as Services,
    \Promoziti\Lib\Core\DiHandler as DiHandler,
    \Promoziti\Lib\Core\IpHandler as IpHandler,
    \Promoziti\Lib\Core\Mail\MandrillWrapper as MandrillWrapper;

/**
 * @todo In general we need to monitor IP access and delay if bad attempts are made (e.g. registration, login, etc)
 * @todo Implement a method that allows to track any actions (log of member actions)
 */
class MemberController extends ControllerBase
{

    public function indexAction()
    {
        //TODO: handle error message here, use forward invalid request
        $this->setJsonResponse();

        $meta = array('response_type' => 'success');
        $response = array('Member Index');

        echo $this->responseWrapper($meta, $response);
    }

    /**
     * POST method to register a member on the database, by default the new member is assigned status 2 (pending)
     * This method does not require a member access token, only an app access token
     * @param string app_token HTTP HEADER
     * @param string lang HTTP HEADER optional parameter used for the response message
     * @param string first_name
     * @param string last_name
     * @param string email
     *
     * @return string response String representation of json object with pzt_member_id value or error
     */
    public function signupAction()
    {
        //this is a protected API service
        if($this->isAccessTokenValid())
        {
            //loading dispatcher vars
            $version = $this->dispatcher->getParam("version");
            $id = $this->dispatcher->getParam(0);

            $this->setJsonResponse();

            $meta = array('response_type' => 'error');
            $response = array();

            $lang = $this->getRequestLang();

            if($this->request->isPost())
            {
                $post_vars = $this->request->getPost();

                $error_data = Services::getService('InputValidator')->vMemberSignup($post_vars, $lang);
                if($error_data === false)
                {

                    $error_data = Services::getService('SPztMember')->emailExists($post_vars['email'], $lang);
                    if($error_data === false)
                    {
                        $tokens = $this->getAuthTokens();
                        $post_vars['app_token'] = $tokens['app_token'];

                        $post_vars['creation_dt'] = date('Y-m-d H:i:s');
                        $post_vars['registration_ip'] = $this->request->getClientAddress();
/*
ALEX HERE:
Falta implementar member token!!
*/
                        $member_obj = Services::getService('SPztMember')->signup($post_vars, $lang);
                        if(is_object($member_obj) && property_exists($member_obj, 'member_token'))
                        {
                            $meta = array('response_type' => 'success');
                            $response = array(
                                'member_token' => $member_obj->member_token,
                                'member' => $member_obj->PztMember->serviceOut('complete+id')
                            );
                        }
                        else
                        {
                            $response = $member_obj; //error
                        }
                    }
                }

                if($error_data !== false)
                {
                    $response = $error_data;
                }
            }
            else
            {
                $response = Services::getService('SPztSysApp')->getUnsupportedRequest($lang);
            }

            echo $this->responseWrapper($meta, $response);
        }
        else
        {
            $this->handleInvalidToken();
        }
    }

    public function verifymobileAction()
    {
        //this is a protected API service
        if($this->isAccessTokenValid())
        {
            //loading dispatcher vars
            $version = $this->dispatcher->getParam("version");
            $id = $this->dispatcher->getParam(0);

            $this->setJsonResponse();

            $meta = array('response_type' => 'error');
            $response = array();

            $lang = $this->getRequestLang();

            $tokens = $this->getAuthTokens();
            $app_token = $tokens['app_token'];
            $member_token = $tokens['member_token'];

            $member_obj = Services::getService('SPztMember')->me(array(
                                                                'app_token' => $app_token,
                                                                'member_token' => $member_token), $lang);
            if(is_object($member_obj) && property_exists($member_obj, 'pzt_member_id'))
            {
                if($this->request->isGet())
                {
                    $mobile = $this->request->getQuery("mobile");
                    $mobile = str_replace(array(' ','-','.','_'), '', $mobile);
                    if(is_numeric($mobile) && strlen($mobile) >= 8 && strlen($mobile) <= 12)
                    {
                        $error_data = Services::getService('SPztMember')->mobileOwnedByAnother($mobile, $member_obj->pzt_member_id, $lang);

                        if($error_data === false) //no error, proceed
                        {
                            $send_response = Services::getService('SPztMember')->sendMobileVerificationCode($member_obj, $mobile, $lang);
                            if(isset($send_response['success']) && $send_response['success'] === true)
                            {
                                $meta = array('response_type' => 'success');
                                $response = array(
                                    'sent_to' => '+'.$member_obj->PztSysCountry->call_code.$mobile,
                                    'mobile' => $mobile
                                );
                            }
                            else
                            {
                                $response = $send_response; //error
                            }
                        }
                        else
                        {
                            $response = $error_data; //error
                        }
                    }
                    else
                    {
                        $response = Services::getService('SPztSysResCode')->getSysResData(false, 'E002-R010', $lang);
                    }
                }
                else if($this->request->isPost())
                {
                    $post_vars = $this->request->getPost();

                    if(isset($post_vars['code']) && strlen(trim($post_vars['code'])) > 0 && is_numeric($post_vars['code']))
                    {
                        $code = trim($post_vars['code']);
                        $verify_response = Services::getService('SPztMember')->mobileVerifyCode($member_obj, $code, $lang);
                        if($verify_response === true)
                        {
                            //getting fresh data
                            $member_obj = Services::getService('SPztMember')->me(array('pzt_member_id' => $member_obj->pzt_member_id), $lang);
                            if(is_object($member_obj) && property_exists($member_obj, 'pzt_member_id'))
                            {
                                $meta = array('response_type' => 'success');
                                $response = array(
                                    'member' => $member_obj->serviceOut('complete+id')
                                );
                            }
                            else
                            {
                                $response = Services::getService('SPztSysResCode')->getSysResData(false, 'E002-R015', $lang);
                            }
                        }
                        else
                        {
                            $response = $verify_response; //error
                        }                        
                    }
                    else
                    {
                        $response = Services::getService('SPztSysResCode')->getSysResData(false, 'E002-R013', $lang);
                    }
                }
                else
                {
                    $response = Services::getService('SPztSysApp')->getUnsupportedRequest($lang);
                }
            }
            else
            {
                $response = $member_obj; //error
            }            

            echo $this->responseWrapper($meta, $response);
        }
        else
        {
            $this->handleInvalidToken();
        }
    }

    public function pointsAction()
    {
        //this is a protected API service
        if($this->isAccessTokenValid())
        {
            //loading dispatcher vars
            $version = $this->dispatcher->getParam("version");
            $id = $this->dispatcher->getParam(0);

            $this->setJsonResponse();

            $meta = array('response_type' => 'error');
            $response = array();

            $lang = $this->getRequestLang();

            $tokens = $this->getAuthTokens();
            $app_token = $tokens['app_token'];
            $member_token = $tokens['member_token'];

            $member_obj = Services::getService('SPztMember')->me(array(
                                                                'app_token' => $app_token,
                                                                'member_token' => $member_token), $lang);
            if(is_object($member_obj) && property_exists($member_obj, 'pzt_member_id'))
            {
                if($this->request->isGet())
                {
                    $member_point_obj = Services::getService('SPztMember')->getPointAccount($member_obj, $lang);
                    if(is_object($member_point_obj) && property_exists($member_point_obj, 'pzt_member_point_id'))
                    {
                        $meta = array('response_type' => 'success');
                        $response = array(
                            'points' => $member_point_obj->points
                        );
                    }
                    else
                    {
                        $response = $points_response; //error
                    }
                }
                else
                {
                    $response = Services::getService('SPztSysApp')->getUnsupportedRequest($lang);
                }
            }
            else
            {
                $response = $member_obj; //error
            }            

            echo $this->responseWrapper($meta, $response);
        }
        else
        {
            $this->handleInvalidToken();
        }
    } 

    public function activityAction()
    {
        //this is a protected API service
        if($this->isAccessTokenValid())
        {
            //loading dispatcher vars
            $version = $this->dispatcher->getParam("version");
            $id = $this->dispatcher->getParam(0);

            $this->setJsonResponse();

            $meta = array('response_type' => 'error');
            $response = array();

            $lang = $this->getRequestLang();

            $tokens = $this->getAuthTokens();
            $app_token = $tokens['app_token'];
            $member_token = $tokens['member_token'];

            $member_obj = Services::getService('SPztMember')->me(array(
                                                                'app_token' => $app_token,
                                                                'member_token' => $member_token), $lang);
            if(is_object($member_obj) && property_exists($member_obj, 'pzt_member_id'))
            {
                if($this->request->isGet())
                {
                    $activity_list = Services::getService('SPztMember')->getActivityList($member_obj, $lang);
                    //response is an array but not an error
                    if(is_array($activity_list) && !isset($activity_list['res_type']))
                    {
                        $points = 0;
                        $member_point_obj = Services::getService('SPztMember')->getPointAccount($member_obj, $lang);
                        if(is_object($member_point_obj) && property_exists($member_point_obj, 'pzt_member_point_id'))
                        {
                            $points = $member_point_obj->points;
                        }

                        $meta = array('response_type' => 'success');
                        $response = array(
                            'points' => $points,
                            'items' => $activity_list
                        );
                    }
                    else
                    {
                        $response = $activity_list; //error
                    }
                }
                else
                {
                    $response = Services::getService('SPztSysApp')->getUnsupportedRequest($lang);
                }
            }
            else
            {
                $response = $member_obj; //error
            }            

            echo $this->responseWrapper($meta, $response);
        }
        else
        {
            $this->handleInvalidToken();
        }
    }

    public function menuAction()
    {
        //this is a protected API service
        if($this->isAccessTokenValid())
        {
            //loading dispatcher vars
            $version = $this->dispatcher->getParam("version");
            $id = $this->dispatcher->getParam(0);

            $this->setJsonResponse();

            $meta = array('response_type' => 'error');
            $response = array();

            $lang = $this->getRequestLang();

            $tokens = $this->getAuthTokens();
            $app_token = $tokens['app_token'];
            $member_token = $tokens['member_token'];

            $member_obj = Services::getService('SPztMember')->me(array(
                                                                'app_token' => $app_token,
                                                                'member_token' => $member_token), $lang);
            if(is_object($member_obj) && property_exists($member_obj, 'pzt_member_id'))
            {
                if($this->request->isPost())
                {
                    $post_vars = $this->request->getPost();
                    $lat = isset($post_vars['lat']) && is_numeric($post_vars['lat']) ? $post_vars['lat']:0;
                    $lon = isset($post_vars['lon']) && is_numeric($post_vars['lon']) ? $post_vars['lon']:0;


                    $menu_list = Services::getService('SPztMember')->getMenu($member_obj, $lat, $lon, $lang);

                    $meta = array('response_type' => 'success');
                    $response = array(
                        'items' => $menu_list
                    );
                }
                else
                {
                    $response = Services::getService('SPztSysApp')->getUnsupportedRequest($lang);
                }
            }
            else
            {
                $response = $member_obj; //error
            }            

            echo $this->responseWrapper($meta, $response);
        }
        else
        {
            $this->handleInvalidToken();
        }
    }

    /**
     * POST method to activate a member account and completing their info
     * @param string app_token HTTP HEADER
     * @param string lang HTTP HEADER optional parameter used for the response message
     * @param int pzt_member_id
     * @param string dob
     * @param string gender
     * @param string address
     * @param string city
     * @param string zip_code
     * @param string tz timezone in php format (http://php.net/manual/en/timezones.php)
     * @param string lang
     * @param string dt_format as per allowed in pzt_sys_dt_format
     * @param string avatar profile image
     * @param string mobile_os
     * @param string activation_code
     *
     * @return string response String representation of json object with pzt_member_id value or error
     */
    public function activateAction()
    {
        //this is a protected API service
        if($this->isAccessTokenValid())
        {
            if($this->isMemberTokenValid())
            {
                //loading dispatcher vars
                $version = $this->dispatcher->getParam("version");
                $id = $this->dispatcher->getParam(0);

                $this->setJsonResponse();

                $meta = array('response_type' => 'error');
                $response = array();

                $lang = $this->getRequestLang();

                if($this->request->isPost())
                {
                    $post_vars = $this->request->getPost();
                    $post_vars = Services::getService('SPztMember')->wrapOutputCols($post_vars,'decode');

                    $error_data = Services::getService('InputValidator')->vMemberActivate($post_vars, $lang);

                    if($error_data === false)
                    {
                        $tokens = $this->getAuthTokens();
                        $app_token = $tokens['app_token'];
                        $member_token = $tokens['member_token'];

                        $member_obj = Services::getService('SPztMember')->me(array(
                                                                            'app_token' => $app_token,
                                                                            'member_token' => $member_token), $lang);
                        //this app has access to this member
                        if(is_object($member_obj) && property_exists($member_obj, 'pzt_member_id'))
                        {
                            // $error_data = Services::getService('SPztMember')->emailOwnedByAnother($post_vars['email'], $member_obj->pzt_member_id, $lang);
                            // if($error_data === false)
                            // {
                                $error_data = Services::getService('SPztMember')->aliasOwnedByAnother($post_vars['alias'], $member_obj->pzt_member_id, $lang);
                                if($error_data === false)
                                {
                                    if(strlen(trim($post_vars['mobile'])) > 0)
                                    {
                                        $post_vars['mobile'] = preg_replace('/[^0-9]/s', '', $post_vars['mobile']);
                                        $error_data = Services::getService('SPztMember')->mobileOwnedByAnother($post_vars['mobile'], $member_obj->pzt_member_id, $lang);
                                    }

                                    //mobile number is blank or valid, continue
                                    if($error_data === false)
                                    {
                                        ///// create avatar /////
                                        $avatar_post_backendd = isset($post_vars['avatar']) ? $post_vars['avatar'] : array();
                                        $avatar_post_frontend = isset($post_vars['avatar_frontend']) ? $post_vars['avatar_frontend'] : array();
                                        $avatar_post = array();
                                        $avatar_data = array();

                                        if(is_array($avatar_post_frontend) && count($avatar_post_frontend))
                                        {   
                                            foreach ($avatar_post_frontend as $key_avatar => $record_avatar)
                                            {
                                                foreach ($record_avatar as $k_attch => $r_attch)
                                                {
                                                    $avatar_post[$k_attch][$key_avatar] = $r_attch;
                                                }
                                            }
                                        }
                                        else if(is_array($avatar_post_backendd) && count($avatar_post_backendd))
                                        {
                                            $avatar_post = $avatar_post_backendd;
                                        }

                                        $avatar_obj = array();

                                        //now save the avatar
                                        if(is_array($avatar_post) && count($avatar_post))
                                        {
                                            $avatar_count = 0;
                                            foreach ($avatar_post as $key => $element)
                                            {
                                                $avatar_count++;

                                                $sanitized_fn = preg_replace('@[^0-9a-z\.]+@i', '-', $element['name']);
                                                
                                                $path_parts = pathinfo($sanitized_fn);

                                                $size = getimagesize($element['tmp_name']);

                                                $file_obj =  new \Phalcon\Http\Request\File (
                                                                array(
                                                                    'name'      => $sanitized_fn,
                                                                    'tmp_name'  => $element['tmp_name'],
                                                                    'size'      => ceil(filesize($element['tmp_name'])/1024),
                                                                    'type'      => $element['type'],
                                                                    'real_type' => $element['type'],
                                                                    'error'     => "",
                                                                    'extension' => $path_parts['extension']
                                                                ), microtime());

                                                $avatar_obj = array('file_obj'     => $file_obj,
                                                                    'name'      => $element['name'],
                                                                    'position'  => $avatar_count);
                                            }
                                        }

                                        $post_vars['avatar'] = $avatar_obj;
                                        
                                        $post_vars['creation_dt'] = date('Y-m-d H:i:s');
                                        $post_vars['registration_ip'] = $this->request->getClientAddress();

                                        $response_obj = Services::getService('SPztMember')->activate($member_obj->pzt_member_id, $post_vars, $lang);
                                        if(is_object($response_obj) && property_exists($response_obj, 'pzt_member_id'))
                                        {
                                            $meta = array('response_type' => 'success');
                                            $response = array('member' => $response_obj->fillOutputCols($response_obj, 'basic','encode'));
                                        }
                                        else
                                        {
                                            //handling error
                                            $error_data = $response_obj;
                                        }
                                    }
                                }
                            //}
                        }
                        else
                        {
                            //handling error
                            $error_data = $member_obj;
                        }
                    }

                    if($error_data !== false)
                    {
                        $response = $error_data;
                    }
                }
                else
                {
                    $response = Services::getService('SPztSysApp')->getUnsupportedRequest($lang);
                }

                echo $this->responseWrapper($meta, $response);
            }
            else
            {
                $this->handleInvalidMemberToken();
            }
        }
        else
        {
            $this->handleInvalidToken();
        }
    }


    /**
     * POST method to retrieve member details
     * @param string app_token & member_token HTTP HEADER
     * @param string lang HTTP HEADER optional parameter used for the response message
     * @param string detail_level basic or full (if none specified then basic)
     *
     * @return string response String representation of json object with pzt_member_id value, basic info or error
     */
    public function meAction()
    {
        //this is a protected API service
        if($this->isAccessTokenValid())
        {
            if($this->isMemberTokenValid())
            {
                //loading dispatcher vars
                $version = $this->dispatcher->getParam("version");
                $id = $this->dispatcher->getParam(0);

                $this->setJsonResponse();

                $meta = array('response_type' => 'error');
                $response = array();

                $lang = $this->getRequestLang();

                if($this->request->isPost())
                {
                    $post_vars = $this->request->getPost();
                    
                    //TODO: validate this input
                    //allowed values are basic, basic+email, full
                    $detail_level = isset($post_vars['detail_level']) && strlen(trim($post_vars['detail_level'])) > 0 ?
                                                    $post_vars['detail_level']:'basic'; //defaults to basic

                    $tokens = $this->getAuthTokens();
                    $app_token = $tokens['app_token'];
                    $member_token = $tokens['member_token'];

                    $member_obj = Services::getService('SPztMember')->me(array(
                                                                        'app_token' => $app_token,
                                                                        'member_token' => $member_token), $lang);
                    //this app has access to this member
                    if(is_object($member_obj) && property_exists($member_obj, 'pzt_member_id'))
                    {
                        $meta = array('response_type' => 'success');
                        $response = array('member' => $member_obj->fillOutputCols($member_obj, $detail_level, 'encode'));
                    }
                    else
                    {
                        //handling error
                        $response = $member_obj;
                    }
                }
                else
                {
                    $response = Services::getService('SPztSysApp')->getUnsupportedRequest($lang);
                }

                echo $this->responseWrapper($meta, $response);
            }
            else
            {
                $this->handleInvalidMemberToken();
            }
        }
        else
        {
            $this->handleInvalidToken();
        }
    }

    /**
     * POST method to login a member
     * @param string app_token HTTP HEADER
     * @param string lang HTTP HEADER optional parameter used for the response message
     * @param string login can be email or alias
     * @param string password plain text
     *
     * @return string response String representation of json object with pzt_member_id value, basic info or error
     */
    public function loginAction()
    {
        //this is a protected API service
        if($this->isAccessTokenValid())
        {
            //loading dispatcher vars
            $version = $this->dispatcher->getParam("version");
            $id = $this->dispatcher->getParam(0);

            $this->setJsonResponse();

            $meta = array('response_type' => 'error');
            $response = array();

            $lang = $this->getRequestLang();

            if($this->request->isPost())
            {
                $post_vars = $this->request->getPost();
                $error_data = Services::getService('InputValidator')->vMemberLogin($post_vars, $lang);
                if($error_data === false)
                {
                    $tokens = $this->getAuthTokens();
                    $post_vars['app_token'] = $tokens['app_token'];

                    $member_obj = Services::getService('SPztMember')->login($post_vars, $lang);
                    if(is_object($member_obj) && property_exists($member_obj, 'member_token'))
                    {
                        $meta = array('response_type' => 'success');
                        $response = array(
                            'member_token' => $member_obj->member_token,
                            'member' => $member_obj->PztMember->serviceOut('complete+id')
                        );
                    }
                    else
                    {
                        //handling error
                        $error_data = $member_obj;
                    }
                }

                if($error_data !== false)
                {
                    $response = $error_data;
                }
            }
            else
            {
                $response = Services::getService('SPztSysApp')->getUnsupportedRequest($lang);
            }

            echo $this->responseWrapper($meta, $response);
        }
        else
        {
            $this->handleInvalidToken();
        }
    }

    /**
     * POST method to setup member reminders
     * @param string app_token HTTP HEADER
     * @param string lang HTTP HEADER optional parameter used for the response message
     * @param int pzt_member_id
     * @param string label
     * @param string description
     * @param string frequency_t
     * @param string frequency_v
     *
     * @return string response String representation of json object with pzt_member_id value, basic info or error
     */
    public function remindersAction()
    {
        //this is a protected API service
        if($this->isAccessTokenValid())
        {
            //loading dispatcher vars
            $version = $this->dispatcher->getParam("version");
            $id = $this->dispatcher->getParam(0);

            $this->setJsonResponse();

            $meta = array('response_type' => 'error');
            $response = array();

            $lang = $this->getRequestLang();

            $tokens = $this->getAuthTokens();
            $app_token = $tokens['app_token'];
            $member_token = $tokens['member_token'];

            $member_obj = Services::getService('SPztMember')->me(array(
                                                                'app_token' => $app_token,
                                                                'member_token' => $member_token), $lang);
            //valid app and member tokens provided
            if(is_object($member_obj) && property_exists($member_obj, 'pzt_member_id'))
            {
                if($this->request->isGet())
                {
                    //individual record
                    if($id && is_numeric($id))
                    {
                        $reminder_obj = Services::getService('SPztReminder')->getReminder($member_obj->pzt_member_id, $id, $lang);
                        if(is_array($reminder_obj))
                        {
                            $meta['response_type'] = 'success';
                            $response = array('reminders' => array($reminder_obj));
                        }
                    }
                    else //all reminders for member
                    {
                        $filters = array('filters' => $this->request->getQuery("filters")); //e.g. ?filters=some-data
                        $response = Services::getService('SPztReminder')->getAll($member_obj->pzt_member_id, $filters, $lang);
                        if(is_array($response))
                        {
                            $meta['response_type'] = 'success';
                            $response = array('reminders' => $response);
                        }
                        else
                        {
                            //TODO: handler error
                        }
                    }
                }
                else if($this->request->isPost())
                {
                    $post_vars = $this->request->getPost();
                    $error_data = Services::getService('InputValidator')->vMemberReminders($post_vars, $lang);
                    if($error_data === false)
                    {
                        $post_vars['pzt_member_id'] = $member_obj->pzt_member_id;
                        $post_vars['status'] = 1; //by default activate the newly created reminder

                        $reminder_obj = Services::getService('SPztReminder')->create($post_vars, $lang);
                        if(is_object($reminder_obj))
                        {
                            $meta = array('response_type' => 'success');
                            $response = array('reminder' => $reminder_obj->fillOutputCols($reminder_obj, 'basic', 'encode'));
                        }
                        else
                        {
                            //handling error
                            $error_data = $response_obj;
                        }

                    }

                    //passing error to service output
                    if($error_data)
                    {
                        $response = $error_data;
                    }
                }
                else if($this->request->isPut())
                {
                    $put_vars = $this->request->getPut();
                    $error_data = Services::getService('InputValidator')->vMemberReminders($put_vars, $lang, 'reminders_u'); 
                    if($error_data === false)
                    {
                        $put_vars['pzt_member_id'] = $member_obj->pzt_member_id;

                        $pzt_reminder_id = $put_vars['re_id'];
                        $response_obj = Services::getService('SPztReminder')->update($put_vars, $pzt_reminder_id, $lang);
                        if(is_array($response_obj))
                        {
                            $meta = array('response_type' => 'success');
                            $response = array('reminder' => $response_obj);
                        }
                        else
                        {
                            //handling error
                            $error_data = $response_obj;
                        }

                    }

                    //passing error to service output
                    if($error_data)
                    {
                        $response = $error_data;
                    }
                }
                else if($this->request->isDelete())
                {
                    //individual record
                    if($id && is_numeric($id))
                    {
                        $response = Services::getService('SPztReminder')->delete($member_obj->pzt_member_id, $id, $lang);
                        if(is_object($response))
                        {
                            $meta['response_type'] = 'success';
                            $response = array('reminder' => $response->toArray());
                        }
                    }
                    else if($id == "all") //all reminders for member
                    {
                        $res = $this->request->getQuery("re_id");
                        if($res) //delete multiple reminders
                        {
                            $response = Services::getService('SPztReminder')->delete($member_obj->pzt_member_id, $res, $lang);
                            if(is_object($response))
                            {
                                $meta['response_type'] = 'success';
                                $response = array('reminder' => $response->toArray());
                            }
                        }
                        else //delete all reminders
                        {
                            $response = Services::getService('SPztReminder')->delete($member_obj->pzt_member_id, null, $lang);
                            if(is_object($response))
                            {
                                $meta['response_type'] = 'success';
                                $response = array('reminder' => $response->toArray());
                            }
                        }
                    }
                }
            }
            else
            {
                //handling error
                $response = $member_obj;
            }

            echo $this->responseWrapper($meta, $response);
        }
        else
        {
            $this->handleInvalidToken();
        }
    }

    /**
     * POST method to send member a link to recover a password
     * @param string app_token HTTP HEADER
     * @param string lang HTTP HEADER optional parameter used for the response message
     * @param string login can be email or alias
     *
     * @return string response String representation of json object with success or error
     */
    public function forgotAction()
    {
        if($this->isAccessTokenValid())
        {
            $version = $this->dispatcher->getParam("version");
            $id = $this->dispatcher->getParam(0);

            $this->setJsonResponse();

            $meta = array('response_type' => 'error');
            $response = array();

            $lang = $this->getRequestLang();

            if($this->request->isGet())
            {
                $recover_by = $this->request->getQuery("recover_by");

                $validation_error = false;
                $params = array('recover_by' => $recover_by);
                if($recover_by == 'email')
                {
                    $params['email'] = $this->request->getQuery("email");
                    if(strlen(trim($params['email'])) == 0)
                    {
                        $validation_error = true;
                        $response = Services::getService('SPztSysResCode')->getSysResData(false, 'E002-R020', $lang);
                    }
                }
                else //mobile
                {
                    $params['country_id'] = $this->request->getQuery("country_id");
                    $params['mobile'] = $this->request->getQuery("mobile");
                    if(strlen(trim($params['mobile'])) == 0)
                    {
                        $validation_error = true;
                        $response = Services::getService('SPztSysResCode')->getSysResData(false, 'E002-R021', $lang);
                    }
                }

                if(!$validation_error) //no errors
                {
                    /*Alex here, you need to create that method*/
                    $forgot_obj = Services::getService('SPztMember')->startForgotProcess($params, $lang);

                    if(is_object($forgot_obj) && property_exists($forgot_obj, 'pzt_member_forgot_id')) //request successfully created
                    {
                        /*Alex here, you need to processresponse*/
                    }
                    else
                    {
                        $response = $forgot_obj; //error
                    }
                }
            }
            else if($this->request->isPost())
            {            
                $post_vars = $this->request->getPost();

                //$error_data = Services::getService('InputValidator')->vMemberValidEmail($post_vars, $lang);
                //if($error_data === false)
                if(true)
                {
                    $status = 1;

                    $params_email = array("status" => $status,
                                          "email" => $post_vars['email']);

                    $params_alias = array("status" => $status,
                                          "alias" => $post_vars['email']);
                    
                    $member_obj = Services::getService('SPztMember')->getMemberByField($params_email, $lang);
                    
                    if(is_object($member_obj))
                    {
                        $member_obj = $member_obj;
                    }
                    else
                    {
                        $member_obj = Services::getService('SPztMember')->getMemberByField($params_alias, $lang);
                    }

                    if(is_object($member_obj))
                    {
                        $fmember_obj = Services::getService('SPztMember')->forgot($member_obj->pzt_member_id, $post_vars['type'], $lang);                        
                        if(is_object($fmember_obj) && property_exists($fmember_obj, 'pzt_member_id'))
                        {                            
                            $meta     = array('response_type' => 'success');
                            $response = array(
                                'email_send' => true,
                                'member' => $fmember_obj->fillOutputCols($fmember_obj, 'basic', 'encode')
                            );
                        }
                        else
                        {
                            //handling error
                            $error_data = $member_obj;
                        }
                    }
                    else
                    {
                        $response = $member_obj;
                    }
                }
                else
                {
                    $response = $error_data;
                }
            }
            else
            {
                $response = Services::getService('SPztSysApp')->getUnsupportedRequest($lang);
            }

            echo $this->responseWrapper($meta, $response);
        }
        else
        {
            $this->handleInvalidToken();
        }
    }

    /**
     * POST method to send an invite
     * @param string app_token HTTP HEADER (app and member tokens)
     * @param string lang HTTP HEADER optional parameter used for the response message
     * @param int pzt_member_id
     * @param string email receiver email address
     * @param string content custom content for the person receiving the invite
     *
     * @return string response String representation of json object with success or error
     */
    public function invitesAction()
    {
        //this is a protected API service
        if($this->isAccessTokenValid())
        {
            if($this->isMemberTokenValid())
            {
                //loading dispatcher vars
                $version = $this->dispatcher->getParam("version");
                $id = $this->dispatcher->getParam(0);

                $this->setJsonResponse();

                $meta = array('response_type' => 'error');
                $response = array();

                $lang = $this->getRequestLang();

                if($this->request->isPost())
                {
                    $post_vars = $this->request->getPost();
                    $error_data = Services::getService('InputValidator')->vMemberInvites($post_vars, $lang);
                    if($error_data === false)
                    {
                        $tokens = $this->getAuthTokens();
                        $app_token = $tokens['app_token'];
                        $member_token = $tokens['member_token'];

                        $member_obj = Services::getService('SPztMember')->me(array(
                                                                            'app_token' => $app_token,
                                                                            'member_token' => $member_token), $lang);
                        //this app has access to this member
                        if(is_object($member_obj) && property_exists($member_obj, 'pzt_member_id'))
                        {
                            $error_data = Services::getService('SPztMember')->emailExists($post_vars['invitee_email'], $lang);
                            if($error_data === false)
                            {
                                $can_invite = Services::getService('SPztMember')->canInvite($member_obj->pzt_member_id, $post_vars['invitee_email'], $lang);
                                if($can_invite === true)
                                {
                                    $post_vars['sent_dt'] = date('Y-m-d H:i:s');
                                    $post_vars['action_status'] = 'none';
                                    $post_vars['send_status'] = 'none';
                                    $post_vars['became_member'] = 0;

                                    $response_obj = Services::getService('SPztMember')->invite($member_obj->pzt_member_id, $post_vars, $lang);
                                    if(is_object($response_obj) && property_exists($response_obj, 'pzt_invitation_id'))
                                    {
                                        $meta = array('response_type' => 'success');
                                        $response = array('invites' => $response_obj->fillOutputCols($response_obj, 'basic','encode'));
                                    }
                                    else
                                    {
                                        $error_data = $response_obj;
                                    }
                                }
                                else
                                {
                                    $error_data = $can_invite;
                                }
                            }
                        }
                        else
                        {
                            //handling error
                            $error_data = $member_obj;
                        }
                    }

                    if($error_data !== false)
                    {
                        $response = $error_data;
                    }
                }
                else
                {
                    $response = Services::getService('SPztSysApp')->getUnsupportedRequest($lang);
                }

                echo $this->responseWrapper($meta, $response);
            }
            else
            {
                $this->handleInvalidMemberToken();
            }
        }
        else
        {
            $this->handleInvalidToken();
        }
    }

    /**
     * POST connect 2 members (networking), allows people to become "mates"
     * @param string app_token HTTP HEADER
     * @param string lang HTTP HEADER optional parameter used for the response message
     * @param int pzt_member_id
     * @param string alias alias or email of the person you want to connect with
     *
     * @return string response String representation of json object with success or error
     */
    public function matesAction()
    {
        //this is a protected API service
        if($this->isAccessTokenValid())
        {
            if($this->isMemberTokenValid())
            {
                //loading dispatcher vars
                $version = $this->dispatcher->getParam("version");
                $id = $this->dispatcher->getParam(0);

                $this->setJsonResponse();

                $meta = array('response_type' => 'error');
                $response = array();

                $lang = $this->getRequestLang();

                $tokens = $this->getAuthTokens();
                $app_token = $tokens['app_token'];
                $member_token = $tokens['member_token'];

                $member_obj = Services::getService('SPztMember')->me(array(
                                                                    'app_token' => $app_token,
                                                                    'member_token' => $member_token), $lang);

                if($this->request->isGet()) //list my mates
                {
                    //individual record
                    if($id && trim(strlen($id)) > 0)
                    {
                        $response = Services::getService('SPztMate')->getMate($member_obj->pzt_member_id, $id, $lang);
                        if(is_object($response))
                        {
                            $meta['response_type'] = 'success';

                            $mate_info = ($member_obj->pzt_member_id == $response->member_low_id) ?
                                $response->SbMemberHigh:$response->SbMemberLow;

                            $tmp_arr = $response->fillOutputCols($response, 'basic','encode');
                            $tmp_arr['member'] = $mate_info->fillOutputCols($mate_info, 'basic','encode');

                            $response = array('mate' => $tmp_arr);
                        }
                    }
                    else //all mates for member
                    {
                        //Status options are: 0 = pending, 1 = accepted, 2 = rejected, 3 = cancelled, 4 = blocked
                        $filters = array('request_status' => $this->request->getQuery("request_status"),
                                            'request_token' => $this->request->getQuery("request_token")); //e.g. ?filters=some-data

                        $response = Services::getService('SPztMate')->getAll($member_obj->pzt_member_id, $filters, $lang);
                        if(is_object($response))
                        {
                            $meta['response_type'] = 'success';
                            $mates_arr = array();
                            foreach ($response as $key => $record)
                            {
                                $mate_info = ($member_obj->pzt_member_id == $record->member_low_id) ?
                                    $record->SbMemberHigh:$record->SbMemberLow;

                                $tmp_arr = $record->fillOutputCols($record, 'basic','encode');
                                $tmp_arr['member'] = $mate_info->fillOutputCols($mate_info, 'basic','encode');

                                array_push($mates_arr, $tmp_arr);
                            }
                            $response = array('mates' => $mates_arr);
                        }
                    }
                }
                else if($this->request->isPost()) //new mates request
                {
                    $post_vars = $this->request->getPost();
                    $error_data = Services::getService('InputValidator')->vMemberMates($post_vars, $lang);
                    if($error_data === false)
                    {
                        //this app has access to this member
                        if(is_object($member_obj) && property_exists($member_obj, 'pzt_member_id'))
                        {
                            $pubid_exists = Services::getService('SPztMember')->pubidExists($post_vars['pubid'], $lang);
                            if($pubid_exists === true)
                            {
                                //request already sent, previous request rejected*, already mates
                                $can_send = Services::getService('SPztMate')->canSendMatesRequest($member_obj->pzt_member_id, $post_vars['pubid'], $lang);
                                if($can_send === true)
                                {
                                    $response_obj = Services::getService('SPztMate')->sendMatesRequest($member_obj->pzt_member_id, $post_vars['pubid'], $lang);
                                    if(is_object($response_obj) && property_exists($response_obj, 'pzt_mate_id'))
                                    {
                                        $meta = array('response_type' => 'success');
                                        $response = array('mates_request' => $response_obj->fillOutputCols($response_obj, 'basic','encode'));
                                    }
                                    else
                                    {
                                        $error_data = $response_obj;
                                    }
                                }
                                else
                                {
                                    $error_data = $can_send;
                                }
                            }
                            else
                            {
                                $error_data = $pubid_exists;
                            }
                        }
                        else
                        {
                            //handling error
                            $error_data = $member_obj;
                        }
                    }

                    if($error_data !== false)
                    {
                        $response = $error_data;
                    }
                }
                else if($this->request->isPut()) //accept/reject/cancel/block a mate request
                {
                    $put_vars = $this->request->getPut();
                    $error_data = Services::getService('InputValidator')->vMemberMates($put_vars, $lang, 'mates_action');

                    if($error_data === false)
                    {
                        //this app has access to this member
                        if(is_object($member_obj) && property_exists($member_obj, 'pzt_member_id'))
                        {
                            $can_respond = Services::getService('SPztMate')->canRespondMatesRequest($member_obj->pzt_member_id, $put_vars, $lang);                            
                            if($can_respond === true)
                            {
                                $response_obj = Services::getService('SPztMate')->respondMatesRequest($member_obj->pzt_member_id, $put_vars, $lang);
                                if(is_object($response_obj) && property_exists($response_obj, 'pzt_mate_id'))
                                {
                                    $meta = array('response_type' => 'success');
                                    $response = array('mates_request' => $response_obj->fillOutputCols($response_obj, 'basic','encode'));
                                }
                                else
                                {
                                    $error_data = $response_obj;
                                }
                            }
                            else
                            {
                                $error_data = $can_respond;
                            }
                        }
                        else
                        {
                            //handling error
                            $error_data = $member_obj;
                        }
                    }

                    if($error_data !== false)
                    {
                        $response = $error_data;
                    }
                }
                else if($this->request->isDelete())
                {
                    //individual record
                    if($id && strlen(trim($id)))
                    {
                        $response = Services::getService('SPztMate')->delete($member_obj->pzt_member_id, $id, $lang);
                        if(is_object($response))
                        {
                            $meta['response_type'] = 'success';
                            $response = array('reminder' => $response->toArray());
                        }
                    }
                    else if($id = "all")
                    {
                        $pubid = $this->request->getQuery("pubid");
                        if($pubid) //delete multiple mates
                        {
                            $response = Services::getService('SPztMate')->delete($member_obj->pzt_member_id, $pubid, $lang);
                            if(is_object($response))
                            {
                                $meta['response_type'] = 'success';
                                $response = array('reminder' => $response->toArray());
                            }
                        }
                        else //delete all mates is NOT permitted
                        {
                            $response = Services::getService('SPztSysApp')->getUnsupportedRequest($lang);
                        }
                    }
                }
                else
                {
                    $response = Services::getService('SPztSysApp')->getUnsupportedRequest($lang);
                }

                echo $this->responseWrapper($meta, $response);
            }
            else
            {
                $this->handleInvalidMemberToken();
            }
        }
        else
        {
            $this->handleInvalidToken();
        }
    }

    /**
     * POST method to perform facebook login
     * @todo create this functionality
     */
    public function socialconnectAction()
    {
        //this is a protected API service
        if($this->isAccessTokenValid())
        {
            //loading dispatcher vars
            $version = $this->dispatcher->getParam("version");
            $id = $this->dispatcher->getParam(0);

            $this->setJsonResponse();

            $meta = array('response_type' => 'error');
            $response = array();

            $lang = $this->getRequestLang();
            
            if($this->request->isPost()) //new mates request
            {
                $post_vars = $this->request->getPost();

                $error_data = Services::getService('InputValidator')->vMemberSocialConnect($post_vars, $lang);
                if($error_data === false)
                {
                    $token_debug = Services::getService('InputValidator')->vSocialTokenDebug($post_vars, $lang);
                    if(false && !$token_debug['success'])
                    {
                        $error_data = $token_debug['error_data'];
                    }
                    
                    if($error_data === false)
                    {
                        $sc_exists = Services::getService('SPztMember')->socialConnectExists($post_vars, $lang);
                        $sc_action = ($sc_exists === false) ? 'signup':'login';

                        // Forward to specific controller ////////////////////////
                        $this->dispatcher->forward(array('action'=>$sc_action));
                        return false;
                        //////////////////////////////////////////////////////////
                    }
                }

                if($error_data !== false)
                {
                    $response = $error_data;
                }
            }
            else
            {
                $response = Services::getService('SPztSysApp')->getUnsupportedRequest($lang);
            }

            echo $this->responseWrapper($meta, $response);
        }
        else
        {
            $this->handleInvalidToken();
        }
    }

    /**
     * POST method to validate an email address
     * @param string app_token HTTP HEADER
     * @param string lang HTTP HEADER optional parameter used for the response message
     * @param string first_name
     * @param string last_name
     * @param string email
     *
     * @return string response String representation of json object with pzt_member_id value or error
     */
    public function validateAction()
    {
        //this is a protected API service
        if($this->isAccessTokenValid())
        {
            //loading dispatcher vars
            $version = $this->dispatcher->getParam("version");
            $id = $this->dispatcher->getParam(0);

            $this->setJsonResponse();

            $meta = array('response_type' => 'error');
            $response = array();

            $lang = $this->getRequestLang();

            if($this->request->isPost())
            {
                $post_vars = $this->request->getPost();

                $tokens = $this->getAuthTokens();

                $error_data = Services::getService('InputValidator')->vMemberValidate($post_vars, $lang);
                if($error_data === false)
                {
                    $member_obj = Services::getService('SPztMember')->validate($post_vars['email_token'], $tokens['app_token'], $lang);
                    if(is_object($member_obj) && property_exists($member_obj, 'member_token'))
                    {
                        $meta = array('response_type' => 'success');
                        $response = array(
                            'member_token' => $member_obj->member_token,
                            'member' => $member_obj->SbMember->fillOutputCols($member_obj->SbMember, 'basic', 'encode')
                        );
                    }
                    else
                    {
                        if(is_object($member_obj) && property_exists($member_obj, 'email_validation_token'))
                        {
                            $meta = array('response_type' => 'success');
                            $response = array(
                                'email_validation_token' => $member_obj->email_validation_token
                            );
                        }
                        else
                        {
                            $response = $member_obj; //error
                        }
                    }
                }

                if($error_data !== false)
                {
                    $response = $error_data;
                }
            }
            else
            {
                $response = Services::getService('SPztSysApp')->getUnsupportedRequest($lang);
            }

            echo $this->responseWrapper($meta, $response);
        }
        else
        {
            $this->handleInvalidToken();
        }
    }

    /**
     * POST method to reset a password
     * @param string app_token HTTP HEADER
     * @param string lang HTTP HEADER optional parameter used for the response message
     * @param string first_name
     * @param string last_name
     * @param string email
     *
     * @return string response String representation of json object with pzt_member_id value or error
     */
    public function resetAction()
    {
        //this is a protected API service
        if($this->isAccessTokenValid())
        {
            //loading dispatcher vars
            $version = $this->dispatcher->getParam("version");
            $id = $this->dispatcher->getParam(0);

            $this->setJsonResponse();

            $meta = array('response_type' => 'error');
            $response = array();

            $lang = $this->getRequestLang();

            if($this->request->isPost())
            {
                $post_vars = $this->request->getPost();

                $tokens = $this->getAuthTokens();

                $error_data = Services::getService('InputValidator')->vMemberReset($post_vars, $lang);
                if($error_data === false)
                {
                    $member_obj = Services::getService('SPztMember')->reset($post_vars['reset_token'], $post_vars['password'], $tokens['app_token'], $lang);
                    if(is_object($member_obj) && property_exists($member_obj, 'member_token'))
                    {
                        $meta = array('response_type' => 'success');
                        //don't allow to have the member token shown because the UI has to force the person to login
                        $response = array(
                            /*'member_token' => $member_obj->member_token,*/
                            'member' => $member_obj->SbMember->fillOutputCols($member_obj->SbMember, 'basic', 'encode')
                        );
                    }
                    else
                    {
                        $response = $member_obj; //error
                    }
                }

                if($error_data !== false)
                {
                    $response = $error_data;
                }
            }
            else
            {
                $response = Services::getService('SPztSysApp')->getUnsupportedRequest($lang);
            }

            echo $this->responseWrapper($meta, $response);
        }
        else
        {
            $this->handleInvalidToken();
        }
    }

    /**
     * POST method to handle children
     * @param string app_token HTTP HEADER
     * @param string lang HTTP HEADER optional parameter used for the response message
     * @param string first_name
     * @param string last_name
     * @param string email
     *
     * @return string response String representation of json object with pzt_member_id value or error
     */
    public function childrenAction()
    {
        //this is a protected API service
        if($this->isAccessTokenValid())
        {
            if($this->isMemberTokenValid())
            {            
                //loading dispatcher vars
                $version = $this->dispatcher->getParam("version");
                $id = $this->dispatcher->getParam(0);

                $this->setJsonResponse();

                $meta = array('response_type' => 'error');
                $response = array();

                $lang = $this->getRequestLang();

                $tokens = $this->getAuthTokens();
                $app_token = $tokens['app_token'];
                $member_token = $tokens['member_token'];

                $member_obj = Services::getService('SPztMember')->me(array(
                                                                    'app_token' => $app_token,
                                                                    'member_token' => $member_token), $lang);            
                if($this->request->isPost())
                {
                    $post_vars = $this->request->getPost();
                    $object_detail = isset($post_vars['odetail']) ? $post_vars['odetail']:'basic';

                    $error_data = Services::getService('InputValidator')->vMemberChildren($post_vars, $lang, 'children_create');

                    if($error_data === false)
                    {
                        $post_vars['creation_dt'] = date('Y-m-d H:i:s');
                        $post_vars['registration_ip'] = $this->request->getClientAddress();

                        $child_obj = Services::getService('SPztMember')->createChild($member_obj, $post_vars, $tokens['app_token'], $lang);
                        if(is_object($child_obj) && property_exists($child_obj, 'pzt_member_id'))
                        {
                            $meta = array('response_type' => 'success');
                            $response = array(
                                'child' => $child_obj->fillOutputCols($child_obj, $object_detail, 'encode')
                            );
                        }
                        else
                        {
                            $response = $child_obj; //error
                        }
                    }

                    if($error_data !== false)
                    {
                        $response = $error_data;
                    }
                }
                else if($this->request->isPut())
                {
                    $put_vars = $this->request->getPut();
                    $object_detail = isset($put_vars['odetail']) ? $put_vars['odetail']:'basic';

                    $error_data = Services::getService('InputValidator')->vMemberChildren($put_vars, $lang, 'children_update');

                    if($error_data === false)
                    {
                        $child_obj = Services::getService('SPztMember')->updateChild($member_obj, $put_vars, $lang);
                        if(is_object($child_obj) && property_exists($child_obj, 'pzt_member_id'))
                        {
                            $meta = array('response_type' => 'success');
                            $response = array(
                                'child' => $child_obj->fillOutputCols($child_obj, $object_detail, 'encode')
                            );
                        }
                        else
                        {
                            $response = $child_obj; //error
                        }
                    }

                    if($error_data !== false)
                    {
                        $response = $error_data;
                    }
                }
                else if($this->request->isGet())
                {
                    //id = pubid
                    if($id && strlen(trim($id)) > 0)
                    {
                        $response = Services::getService('SPztMember')->getChild($member_obj->pzt_member_id, $id, $lang);
                        if(is_object($response))
                        {
                            $meta['response_type'] = 'success';

                            $tmp_arr = $response->fillOutputCols($response, 'basic','encode');
                            $response = array('children' => $tmp_arr);
                        }                        
                    }
                    else //all children for member
                    {
                        $filters = array('filters' => $this->request->getQuery("filters")); //e.g. ?filters=some-data
                        $response = Services::getService('SPztMember')->getChildren($member_obj->pzt_member_id, $filters, $lang);
                        if(is_object($response))
                        {
                            $meta['response_type'] = 'success';
                            $mates_arr = array();
                            foreach ($response as $key => $record)
                            {
                                $tmp_arr = $record->fillOutputCols($record, 'basic','encode');
                                array_push($mates_arr, $tmp_arr);
                            }
                            $response = array('children' => $mates_arr);
                        }
                    }                    
                }                
                else
                {
                    $response = Services::getService('SPztSysApp')->getUnsupportedRequest($lang);
                }

                echo $this->responseWrapper($meta, $response);
            }
            else
            {
                $this->handleInvalidMemberToken();
            }            
        }
        else
        {
            $this->handleInvalidToken();
        }
    }

    /**
     * POST method to handle tags
     * @param string app_token HTTP HEADER
     * @param string member_token HTTP HEADER
     * @param string lang HTTP HEADER optional parameter used for the response message
     *
     * @return string response String representation of json object with pzt_member_id value or error
     */
    public function tagsAction()
    {
        //this is a protected API service
        if($this->isAccessTokenValid())
        {
            if($this->isMemberTokenValid())
            {            
                //loading dispatcher vars
                $version = $this->dispatcher->getParam("version");
                $id = $this->dispatcher->getParam(0);

                $this->setJsonResponse();

                $meta = array('response_type' => 'error');
                $response = array();

                $lang = $this->getRequestLang();

                $tokens = $this->getAuthTokens();
                $app_token = $tokens['app_token'];
                $member_token = $tokens['member_token'];

                $member_obj = Services::getService('SPztMember')->me(array(
                                                                    'app_token' => $app_token,
                                                                    'member_token' => $member_token), $lang);     

                if(is_object($member_obj) && property_exists($member_obj, 'pzt_member_id'))
                {
                    if($this->request->isPost())
                    {
                        $post_vars = $this->request->getPost();
                        $post_vars['pzt_member_id'] = $member_obj->pzt_member_id;
                        $post_vars = Services::getService('SPztTag')->wrapOutputCols($post_vars,'decode');

                        $error_data = Services::getService('InputValidator')->vTag($post_vars, $lang);
                        if($error_data === false)
                        {
                            $error_data = Services::getService('SPztTag')->exists($post_vars['tag'], $member_obj->pzt_member_id, $lang);
                            if($error_data === false)
                            {
                                $tag_obj = Services::getService('SPztTag')->create($post_vars, $lang);
                                if(is_object($tag_obj) && property_exists($tag_obj, 'pzt_tag_id'))
                                {
                                    $meta = array('response_type' => 'success');
                                    $response = array(
                                        'tag' => $tag_obj->fillOutputCols($tag_obj, 'basic', 'encode')
                                    );
                                }
                                else
                                {
                                    $response = $tag_obj; //error
                                }
                            }
                        }

                        if($error_data !== false)
                        {
                            $response = $error_data;
                        }
                    }
                    else if($this->request->isDelete())
                    {
                        if($id == "all" || ($id && is_numeric($id)))
                        {
                            $response = Services::getService('SPztTag')->delete($member_obj->pzt_member_id, $id, $lang);
                            if($response === true)
                            {
                                $meta['response_type'] = 'success';
                                $response = array('deleted' => $response);
                            }
                        }                        
                    }
                    else if($this->request->isPut())
                    {
                        $put_vars = $this->request->getPut();
                        $put_vars['pzt_member_id'] = $member_obj->pzt_member_id;
                        $put_vars = Services::getService('SPztTag')->wrapOutputCols($put_vars,'decode');

                        $error_data = Services::getService('InputValidator')->vTag($put_vars, $lang);
                        if($error_data === false)
                        {
                            $error_data = Services::getService('SPztTag')->existsByAnother($put_vars['tag'], $member_obj->pzt_member_id, $put_vars['pzt_tag_id'], $lang);
                            if($error_data === false)
                            {
                                $tag_obj = Services::getService('SPztTag')->update($put_vars, $put_vars['pzt_tag_id'], $lang);
                                if(is_object($tag_obj) && property_exists($tag_obj, 'pzt_tag_id'))
                                {
                                    $meta = array('response_type' => 'success');
                                    $response = array(
                                        'tag' => $tag_obj->fillOutputCols($tag_obj, 'basic', 'encode')
                                    );
                                }
                                else
                                {
                                    $response = $tag_obj; //error
                                }
                            }
                        }

                        if($error_data !== false)
                        {
                            $response = $error_data;
                        }
                    }
                    else if($this->request->isGet())
                    {
                        //id = pzt_tag_id
                        if($id && strlen(trim($id)) > 0)
                        {
                            $response = Services::getService('SPztTag')->getById($member_obj->pzt_member_id, $id, $lang);
                            if(is_object($response))
                            {
                                $meta['response_type'] = 'success';

                                $tmp_arr = $response->fillOutputCols($response, 'basic','encode');
                                $response = array('tags' => $tmp_arr);
                            }                        
                        }
                        else //all tags for member
                        {
                            $filters = array('filters' => $this->request->getQuery("filters")); //e.g. ?filters=some-data
                            $response = Services::getService('SPztTag')->getByMember($member_obj->pzt_member_id, $filters, $lang);
                            if(is_object($response))
                            {
                                $meta['response_type'] = 'success';
                                $tags_arr = array();
                                foreach ($response as $key => $record)
                                {
                                    $tmp_arr = $record->fillOutputCols($record, 'basic','encode');
                                    array_push($tags_arr, $tmp_arr);
                                }
                                $response = array('tags' => $tags_arr);
                            }
                        }                    
                    }                
                    else
                    {
                        $response = Services::getService('SPztSysApp')->getUnsupportedRequest($lang);
                    }
                }
                else
                {
                    $response = $member_obj;
                }

                echo $this->responseWrapper($meta, $response);
            }
            else
            {
                $this->handleInvalidMemberToken();
            }            
        }
        else
        {
            $this->handleInvalidToken();
        }
    }    

    //##########################################
    //##########################################
    //TODO: Re-think about the following methods
    //##########################################
    //##########################################

    /**
     * POST method to resend mobile activation code
     * @param string app_token HTTP HEADER
     * @param string lang HTTP HEADER optional parameter used for the response message
     * @param int pzt_member_id
     *
     * @return string response String representation of json object with success or error
     */
    public function resendAction()
    {
        //this is a protected API service
        if($this->isAccessTokenValid())
        {
            //loading dispatcher vars
            $version = $this->dispatcher->getParam("version");
            $id = $this->dispatcher->getParam(0);

            $this->setJsonResponse();

            $meta = array('response_type' => 'error');
            $response = array();

            echo $this->responseWrapper($meta, $response);
        }
        else
        {
            $this->handleInvalidToken();
        }
    }

    /**
     * POST method to change language, timezone, date format
     * @param string app_token HTTP HEADER
     * @param string lang HTTP HEADER optional parameter used for the response message
     * @param int pzt_member_id
     * @param string lang
     * @param string tz
     * @param string dt_format
     *
     * @return string response String representation of json object with success or error
     */
    public function settingsAction()
    {
        //this is a protected API service
        if($this->isAccessTokenValid())
        {
            //loading dispatcher vars
            $version = $this->dispatcher->getParam("version");
            $id = $this->dispatcher->getParam(0);

            $this->setJsonResponse();

            $meta = array('response_type' => 'error');
            $response = array();

            echo $this->responseWrapper($meta, $response);
        }
        else
        {
            $this->handleInvalidToken();
        }
    }

    /**
     * PUT method to update member details
     * @param int pzt_member_id
     * @param string mobile
     * @param string dob
     * @param string gender
     * @param string city
     * @param string state
     * @param string zip_code
     * @param string country
     *
     * @return string response String representation of json object with pzt_member_id value, basic info or error
     */
    public function detailsAction()
    {
        //this is a protected API service
        if($this->isAccessTokenValid())
        {
            if($this->isMemberTokenValid())
            {  
                //loading dispatcher vars
                $version = $this->dispatcher->getParam("version");
                $id = $this->dispatcher->getParam(0);

                $this->setJsonResponse();

                $meta = array('response_type' => 'error');
                $response = array();

                $lang = $this->getRequestLang();

                $tokens = $this->getAuthTokens();
                $app_token = $tokens['app_token'];
                $member_token = $tokens['member_token'];

                $member_obj = Services::getService('SPztMember')->me(array(
                                                                    'app_token' => $app_token,
                                                                'member_token' => $member_token), $lang);     

                if(is_object($member_obj) && property_exists($member_obj, 'pzt_member_id'))
                {
                    if($this->request->isPut())
                    {
                        $put_vars = $this->request->getPut();

                        //echo "<pre>put_vars";
                        //print_r($put_vars);
                        //echo "</pre>";
                    
                        $member_obj = Services::getService('SPztMember')->update($member_obj->pzt_member_id, $put_vars, $lang);
                        if(is_object($member_obj) && property_exists($member_obj, 'pzt_member_id'))
                        {
                            $meta = array('response_type' => 'success');
                            $response = array(
                                'member' => $member_obj->fillOutputCols($member_obj, 'basic', 'encode')
                            );
                        }
                        else
                        {
                            $response = $member_obj; //error
                        }
                    }
                    else
                    {
                        $response = Services::getService('SPztSysApp')->getUnsupportedRequest($lang);
                    }
                }
                else
                {
                    $response = $member_obj;
                }

                echo $this->responseWrapper($meta, $response);
            }
            else
            {
                $this->handleInvalidMemberToken();
            }   
        }
        else
        {
            $this->handleInvalidToken();
        }
    }

    /**
     * PUT method to upload photo 
     * @param string app_token HTTP HEADER
     * @param int pzt_member_id
     * @param string lang HTTP HEADER optional parameter used for the response message
     * @param string avatar
     *
     * @return string response String representation of json object with pzt_member_id value or error
     */

    public function avatarAction()
    {
        //this is a protected API service
        if($this->isAccessTokenValid())
        {
            if($this->isMemberTokenValid())
            {  
                //loading dispatcher vars
                $version = $this->dispatcher->getParam("version");
                $id = $this->dispatcher->getParam(0);

                $this->setJsonResponse();

                $meta = array('response_type' => 'error');
                $response = array();

                $lang = $this->getRequestLang();

                $tokens = $this->getAuthTokens();
                $app_token = $tokens['app_token'];
                $member_token = $tokens['member_token'];

                $member_obj = Services::getService('SPztMember')->me(array(
                                                                    'app_token' => $app_token,
                                                                    'member_token' => $member_token), $lang);     

                if(is_object($member_obj) && property_exists($member_obj, 'pzt_member_id'))
                {
                    if($this->request->isPut())
                    {
                        $put_vars = $this->request->getPut();

                        $avatar = isset($put_vars['avatar']) ? $put_vars['avatar'] : ""; // Your data 'data:image/png;base64,AAAFBfj42Pj4';
                        $avatar_obj = Services::getService('SPztMember')->uploadAvatar($member_obj->pzt_member_id, $avatar, $lang);
                        //if(is_object($avatar_obj) && property_exists($avatar_obj, 'pzt_member_id'))
                        if($avatar_obj !== false)
                        {
                            $meta = array('response_type' => 'success');
                           
                            $response = $avatar_obj; 
                            /*$response = array(
                                'member' => $member_obj->fillOutputCols($member_obj, 'basic', 'encode')
                            );*/
                        }
                        else
                        {
                            $response = $member_obj; //error
                        }
                    }
                    else
                    {
                        $response = Services::getService('SPztSysApp')->getUnsupportedRequest($lang);
                    }
                }
                else
                {
                    $response = $member_obj;
                }
        
                echo $this->responseWrapper($meta, $response);
            }
            else
            {
                $this->handleInvalidMemberToken();
            }   
        }
        else
        {
            $this->handleInvalidToken();
        }
    }

    /**
     * GET method to search for members
     * @param string app_token HTTP HEADER
     * @param string lang HTTP HEADER optional parameter used for the response message
     * @param string first_name
     * @param string last_name
     * @param string email
     *
     * @return string response String representation of json object with pzt_member_id value or error
     */
    public function searchAction()
    {
        //this is a protected API service
        if($this->isAccessTokenValid())
        {
            if($this->isMemberTokenValid())
            {            
                //loading dispatcher vars
                $version = $this->dispatcher->getParam("version");
                $term = $this->dispatcher->getParam(0);

                $this->setJsonResponse();

                $meta = array('response_type' => 'error');
                $response = array();

                $lang = $this->getRequestLang();

                $tokens = $this->getAuthTokens();
                $app_token = $tokens['app_token'];
                $member_token = $tokens['member_token'];

                $member_obj = Services::getService('SPztMember')->me(array(
                                                                    'app_token' => $app_token,
                                                                    'member_token' => $member_token), $lang);            
                if($this->request->isGet())
                {
                    //searching for a term
                    if($term && strlen(trim($term)) > 0)
                    {
                        $params = array('offset' => $this->request->getQuery("offset"),
                                        'limit' => $this->request->getQuery("limit"));

                        $members = Services::getService('SPztMember')->search($term, $params, $lang);
                        if(is_array($members) && !isset($members['res_type'])) //not an error
                        {
                            $meta['response_type'] = 'success';
                            $response_arr = array();
                            foreach ($members as $key => $member_arr)
                            {
                                //TODO: review using objects instead of array stdclass!  https://forum.phalconphp.com/discussion/1846/decoding-json-to-a-model-class-object-instead-of-stdclass
                                //$member_arr = $member_obj->toArray();
                                $response_arr[] = array('pubid' => $member_arr['pubid'],
                                                            'alias' => $member_arr['alias'],
                                                            'first_name' => $member_arr['first_name'],
                                                            'last_name' => $member_arr['last_name'],                                                            
                                                            'email_validated' => $member_arr['email_validated'],
                                                            'status' => $member_arr['status']);
                            }

                            $response = array('members' => $response_arr);
                        }                        
                        else
                        {
                            $response = $members;
                        }
                    }
                    else //invalid search
                    {
                        $response = Services::getService('SPztSysApp')->getUnsupportedRequest($lang);
                    }                    
                }                
                else
                {
                    $response = Services::getService('SPztSysApp')->getUnsupportedRequest($lang);
                }

                echo $this->responseWrapper($meta, $response);
            }
            else
            {
                $this->handleInvalidMemberToken();
            }            
        }
        else
        {
            $this->handleInvalidToken();
        }
    }    

    /**
     * POST method to change password
     * @param string app_token HTTP HEADER
     * @param string lang HTTP HEADER optional parameter used for the response message
     * @param int pzt_member_id
     * @param string password plain current password
     * @param string password_new plain new password
     *
     * @return string response String representation of json object with success or error
     */
    public function passwordAction()
    {
        //this is a protected API service
        if($this->isAccessTokenValid())
        {
            //loading dispatcher vars
            $version = $this->dispatcher->getParam("version");
            $id = $this->dispatcher->getParam(0);

            $this->setJsonResponse();

            //use phalcon security class for hashing password, like this: $member->password = $this->security->hash($password);

            $meta = array('response_type' => 'error');
            $response = array();

            echo $this->responseWrapper($meta, $response);
        }
        else
        {
            $this->handleInvalidToken();
        }
    }

    /**
     * GET method to setup member timeline
     * @param string app_token HTTP HEADER
     * @param string lang HTTP HEADER optional parameter used for the response message
     * @param int pzt_member_id
     * @return string response String representation of json object with pzt_member_id value, basic info or error
     */
    public function timelineAction()
    {
        //this is a protected API service
        if($this->isAccessTokenValid())
        {
            //loading dispatcher vars
            $version = $this->dispatcher->getParam("version");
            $id = $this->dispatcher->getParam(0);

            $this->setJsonResponse();

            $meta = array('response_type' => 'error');
            $response = array();

            $lang = $this->getRequestLang();

            $tokens = $this->getAuthTokens();
            $app_token = $tokens['app_token'];
            $member_token = $tokens['member_token'];

            $member_obj = Services::getService('SPztMember')->me(array(
                                                                'app_token' => $app_token,
                                                                'member_token' => $member_token), $lang);
            
            


            //valid app and member tokens provided
            if(is_object($member_obj) && property_exists($member_obj, 'pzt_member_id'))
            {
                if($this->request->isGet())
                {
                    $type = $this->dispatcher->getParam(0);
                    $date = $this->dispatcher->getParam(1);
                    $limt = $this->dispatcher->getParam(2);
                    $dirn = $this->dispatcher->getParam(3);
                    
                    $params = array();
                    if(strlen($date) && is_numeric($limt))
                    {
                        $params = array("date"=> $date,
                                        "limit"=> $limt,
                                        "direction"=> $dirn);
                    }

                    $message_obj = Services::getService('SPztMessage')->getTimeline($member_obj->pzt_member_id, $type, $params, $lang);
                    if(is_object($message_obj) && count($message_obj))
                    {
                        $msg_data = array();
                        
                        if($type == 'menu')
                        {
                            foreach ($message_obj as $msg_key => $msg_record)
                            {
                                $msg_tmp = array('label' => '',
                                                'number' => $msg_record->total,
                                                'date'=> $msg_record->date
                                                );

                                $msg_data[] = array(
                                    'type' => 'message',
                                    'body' => $msg_tmp
                                );
                            }
                        }
                        else
                        {
                            foreach ($message_obj as $msg_key => $msg_record)
                            {
                                $msg_tmp = array('subject' => '',
                                                'message' => '',
                                                'delivery_dt'=>'',
                                                'media' => array('main'=>'','others'=>''),
                                                'documents' => array(),
                                                'hearts' => 0,
                                                'comments' => array()
                                                );

                                $msg_data[] = array(
                                    'type' => 'message',
                                    'body' => $msg_tmp
                                );
                            }
                        }

                        $meta = array('response_type' => 'success');
                        $response = $msg_data;
                    
                    }   
                }
            }
            else
            {
                //handling error
                $response = $member_obj;
            }

            echo $this->responseWrapper($meta, $response);
        }
        else
        {
            $this->handleInvalidToken();
        }
    }
}
