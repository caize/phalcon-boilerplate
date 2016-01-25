<?php
namespace Promoziti\Models\Repositories\Repository;

use Phalcon\Verification\Validator;
use \Promoziti\Models\Entities\EPztMember as EntityMember,
    \Promoziti\Models\Entities\EPztMemberLogin as EntityMemberLogin,
    \Promoziti\Models\Entities\EPztMemberToken as EntityMemberToken,
    \Promoziti\Models\Entities\EPztMemberPoint as EntityMemberPoint,
    \Promoziti\Models\Entities\EPztMemberActivity as EntityMemberActivity,
    \Promoziti\Models\Entities\EPztSysAppToken as EntitySysAppToken;

use \Promoziti\Lib\Core\IpHandler as IpHandler,
    \Promoziti\Lib\Core\DiHandler as DiHandler,
    \Promoziti\Lib\Core\CacheHandler as CacheHandler,
    \Promoziti\Lib\Core\DateHandler as DateHandler,
    \Promoziti\Lib\Core\Crypt\Hashids as Hashids;

class RPztMember extends RepositoryBase
{
    public $country_id_peru = 173;

    public function signup($params, $lang_config, $security_config)
    {
        //removing white spaces if any
        $params = array_map('trim', $params);
        $network = isset($params['network']) ? $params['network']:null;

        $member_obj = new EntityMember();
        $member_obj->pzt_sys_country_id = $this->country_id_peru;
        $member_obj->current_country_id = $this->country_id_peru;

        $member_obj->creation_dt = date('Y-m-d H:i:s');
        $member_obj->creation_method = 'app-registration';
        $member_obj->first_name = $params['first_name'];
        $member_obj->last_name = $params['last_name'];
        $member_obj->email = $params['email'];
        $member_obj->password = isset($params['password']) ? $params['password']:null;

        $member_obj->pubid = null;
        $member_obj->avatar_path = null; //check if we can get it from FB
        $member_obj->dob = null; //check if we can get it from FB
        $member_obj->gender = null; //check if we can get it from FB
        $member_obj->city = null;
        $member_obj->state = null;
        $member_obj->zip_code = null;
        $member_obj->lang = null;
        $member_obj->tz = null;
        $member_obj->dt_format = null;

        $member_obj->registration_ip = $params['registration_ip'];

        $member_obj->phone_os = null;
        $member_obj->phone_brand = null;
        $member_obj->phone_model = null;

        $member_obj->email_verified = 0;
        $member_obj->mobile_verified = 0;
        $member_obj->status = 1;

        if(!is_null($network) && strlen($network))
        {
            $member_obj->creation_method = 'app-'.$network.'-connect';

            $member_social_obj = new EntityMemberSocial();
            $member_social_obj->network = $network;
            $member_social_obj->n_user_id = $params['n_user_id'];
            $member_social_obj->n_user_token = $params['n_user_token'];
            $member_social_obj->creation_dt = $params['creation_dt'];

            $member_obj->PztMemberSocial = $member_social_obj;
            $member_obj->email_verified = 1; //if signup is from a social source we consider email as valid
        }

        if($member_obj->save())
        {
            $country_lang = $member_obj->PztSysCountry->languages;
            $country_lang = explode(',', $country_lang);
            $country_lang = strtolower(trim($country_lang[0])); //first language for this country
            $lang_supported = (array) $lang_config->supported;

            if(!in_array($country_lang, $lang_supported))
            {
                $country_lang = $lang_config->default;
            }

            $member_obj->lang = $country_lang;
            $member_obj->dt_format = $member_obj->PztSysCountry->dt_format;

            /*
            //TODO: Implement info based in location later
            $ip_based_info = IpHandler::ipFullInfo($params['registration_ip']);
            if($ip_based_info) //info was able to be pulled in based on IP
            {
                $member_obj->city = $ip_based_info['city'];
                $member_obj->zip_code = $ip_based_info['zip_code'];
                $member_obj->tz = $ip_based_info['tz'];
            }
            */

            $hashids = new Hashids($security_config->member->pubid_key,
                                    $security_config->member->pubid_length,
                                    $security_config->member->pubid_alpha);
            $member_obj->pubid = $hashids->encode($member_obj->pzt_member_id);
            $member_obj->save();

            $this->_createPointAccount($member_obj->pzt_member_id);

            return $member_obj;
        }
        else
        {
            $this->showDbMessages($member_obj);
            return false;
        }
    }

    public function emailExists($email)
    {
        $member = EntityMember::findFirstByEmail(trim($email));
        return $member != null;
    }

    public function emailOwnedByAnother($email, $pzt_member_id)
    {
        $member = EntityMember::findFirst(array(
                                            'columns'    => 'pzt_member_id',
                                            'conditions' => 'email = ?1 AND pzt_member_id <> ?2',
                                            'bind'       => array(1 => $email, 2 => $pzt_member_id)));
        return $member != null;
    }

    public function mobileExists($mobile)
    {
        $mobile = trim(str_replace(array(' ','-','.','_'), '', $mobile));
        $member = EntityMember::findFirstByMobile($mobile);
        return $member != null;
    }

    public function mobileOwnedByAnother($mobile, $pzt_member_id)
    {
        $mobile = trim(str_replace(array(' ','-','.','_'), '', $mobile));
        $member = EntityMember::findFirst(array(
                                            'columns'    => 'pzt_member_id',
                                            'conditions' => 'mobile = ?1 AND pzt_member_id <> ?2',
                                            'bind'       => array(1 => $mobile, 2 => $pzt_member_id)));
        return $member != null;
    }    

    public function generateEmailVerificationToken($member_obj, $config)
    {
        $token_key = isset($config['token_key']) ? $config['token_key']:null;
        $exp_hours = isset($config['exp_hours']) ? $config['exp_hours']:null;

        if(is_null($token_key) || is_null($exp_hours))
        {
            //operation not completed
            return $member_obj;
        }
        else
        {
            $member_obj->email_verification_token = hash_hmac('sha256', $member_obj->pzt_member_id.":".time().":".uniqid(), $token_key);
            $member_obj->email_verification_expires = date('Y-m-d H:i:s', strtotime('+'.$exp_hours.' hours', time()));
            $member_obj->save();
            return $member_obj;
        }
    }

    public function generateEmailVerificationTokenById($pzt_member_id, $config)
    {
        return $this->generateEmailVerificationToken(EntityMember::findFirst($pzt_member_id), $config);
    }

    public function getMemberByEmailVerificationToken($email_verification_token)
    {
        $member = EntityMember::findFirstByEmailVerificationToken(trim($email_verification_token));
        if(is_object($member) && property_exists($member, 'status') && $member->status != 0)
        {
            return $member;
        }
        else
        {
            null;
        }
    }

    public function me($params)
    {
        //removing white spaces if any
        $params = array_map('trim', $params);

        if(isset($params['pzt_member_id']))
        {
            $member_obj = EntityMember::findFirst($params['pzt_member_id']);
            if(is_object($member_obj) && property_exists($member_obj, 'pzt_member_id'))
            {
                return $member_obj;
            }
        }
        else if(isset($params['app_token']) && strlen($params['app_token']) > 0
            && isset($params['member_token']) && strlen($params['member_token']) > 0)
        {
            $cache_key = $params['app_token'].":".$params['member_token'];

            $cached_result = CacheHandler::get($cache_key);
            if($cached_result != null)
            {
                return $cached_result;
            }
            else
            {
                $app_token_obj = EntitySysAppToken::findFirst(array(
                                                                'columns'    => '*',
                                                                'conditions' => 'app_token = ?1 AND status = 1',
                                                                'bind'       => array(1 => $params['app_token'])));
                if(is_object($app_token_obj))
                {
                    $pzt_sys_app_id = $app_token_obj->PztSysApp->pzt_sys_app_id;
                    $member_token_obj = EntityMemberToken::findFirst(array(
                                                                    'columns'    => '*',
                                                                    'conditions' => 'member_token = ?1 AND pzt_sys_app_id = ?2 AND status = 1',
                                                                    'bind'       => array(1 => $params['member_token'],
                                                                                          2 => $pzt_sys_app_id)));
                    if(is_object($member_token_obj))
                    {
                        CacheHandler::create($cache_key, $member_token_obj->PztMember);
                        return $member_token_obj->PztMember;
                    }
                }
            }
        }

        return null;
    }

    public function login($params)
    {
        //removing white spaces if any
        $params = array_map('trim', $params);
        $response = false;

        if(isset($params['network']))
        {
            if($params['network'] == "facebook") //-RBN-return to here: 2015.07.02
            {
                if(isset($params['n_user_id']) && strlen($params['n_user_id']) > 0
                    && isset($params['n_user_token']) && strlen($params['n_user_token']) > 0)
                {
                    $member_social_obj = EntityMemberSocial::findFirst(array(
                        'conditions' => 'network = ?1 AND n_user_id = ?2',
                        'bind'       => array(1 => $params['network'], 2 => $params['n_user_id'])));

                    if(is_object($member_social_obj) && property_exists($member_social_obj, 'pzt_member_social_id'))
                    {
                        $member_obj = $member_social_obj->PztMember;

                        if(is_object($member_obj) && property_exists($member_obj, 'pzt_member_id'))
                        {
                            $response = $member_obj;
                        }
                    }
                }
            }
        }
        else
        {
            if(isset($params['login']) && strlen($params['login']) > 0
                && isset($params['password']) && strlen($params['password']) > 0)
            {
                $login = $params['login'];
                $conditions = 'email = ?1';

                $member_obj = EntityMember::findFirst(array(
                    'columns'    => 'pzt_member_id, first_name, last_name, email, password',
                    'conditions' => $conditions,
                    'bind'       => array(1 => $login)
                ));

                if(is_object($member_obj) && property_exists($member_obj, 'pzt_member_id'))
                {
                    if(password_verify($params['password'], $member_obj->password))
                    {
                        $response = $member_obj;
                    }
                }
            }
        }

        return $response;
    }

    public function recordLoginAction($pzt_member_id)
    {
        $session = DiHandler::getSession();
        //TODO: review how this method will work, should it record login source, do we need logout dt?
        $member_login_obj = new EntityMemberLogin();
        $member_login_obj->pzt_member_id = $pzt_member_id;
        $member_login_obj->session_id = $session->getid();
        $member_login_obj->ip = IpHandler::clientIp();
        $member_login_obj->login_dt = date('Y-m-d H:i:s');
        $member_login_obj->expires_dt = date('Y-m-d H:i:s', strtotime('+2 hours', time()));
        $member_login_obj->logout_dt = null;

        if($member_login_obj->save())
        {
            return true;
        }
        else
        {
            $this->showDbMessages($member_login_obj);
            return false;
        }
    }

    public function sendMobileVerificationCode($pzt_member_id, $mobile, $verification_code)
    {
        if(strlen($mobile))
        {
            $mobile = str_replace(array(' ','-','.','_'), '', $mobile);
            $member_obj = EntityMember::findFirst($pzt_member_id);
            if(is_object($member_obj) && property_exists($member_obj, 'pzt_member_id'))
            {
                $member_obj->mobile = $mobile;
                $member_obj->mobile_verification_token = $verification_code;
                $member_obj->save();
            }
        }
    }

    public function mobileVerifyCode($pzt_member_id, $verification_code)
    {
        if(strlen($verification_code))
        {
            $member_obj = EntityMember::findFirst($pzt_member_id);
            if(is_object($member_obj) && property_exists($member_obj, 'pzt_member_id'))
            {
                if($member_obj->mobile_verification_token == $verification_code)
                {
                    $member_obj->mobile_verified = 1;
                    $member_obj->save();

                    return true;
                }
            }
        }

        return false;
    }

    public function checkPointAccount($member_obj)
    {
        $member_point_obj = EntityMemberPoint::findFirst(array(
                                                            'conditions' => 'pzt_member_id = ?1',
                                                            'bind'       => array(1 => $member_obj->pzt_member_id)));
        //if record does not exist
        if(!is_object($member_point_obj) || !property_exists($member_point_obj, 'pzt_member_point_id'))
        {
            $this->_createPointAccount($member_obj->pzt_member_id);
        }
    }

    public function getPointAccount($pzt_member_id)
    {
        $member_point_obj = EntityMemberPoint::findFirst(array(
                                                            'conditions' => 'pzt_member_id = ?1',
                                                            'bind'       => array(1 => $pzt_member_id)));
        //if record does not exist
        if(is_object($member_point_obj) && property_exists($member_point_obj, 'pzt_member_point_id'))
        {
            return $member_point_obj;
        }

        return null;
    }

    public function getActivityList($pzt_member_id)
    {
        $activity_list = EntityMemberActivity::find(array(
                                                            'conditions' => 'pzt_member_id = ?1',
                                                            'bind'       => array(1 => $pzt_member_id),
                                                            'order'      => 'creation_dt DESC'));
        return  $activity_list;
    }

    private function _createPointAccount($pzt_member_id)    
    {
        $new_member_point_obj = new EntityMemberPoint();
        $new_member_point_obj->pzt_member_id = $pzt_member_id;
        $new_member_point_obj->last_update_dt = date('Y-m-d H:i:s');
        $new_member_point_obj->points = 0;
        $new_member_point_obj->save();        
    }
    public function findMembersByBusiness($pzt_business_id, $idsString='')
    {
        if(isset($pzt_business_id))
        {

            $sql=   'SELECT mp.*,
                            m.*,
                          (SELECT COUNT(mp.pzt_member_id)
                           FROM pzt_member_promo mp
                           WHERE mp.pzt_promo_id = p.pzt_promo_id
                             AND mp.pzt_member_id=m.pzt_member_id
                             AND mp.withdrawn_dt IS NULL
                             AND mp.collected_dt IS NULL
                             AND mp.collected_dt IS NULL
                             AND mp.expired_dt IS NULL) AS reserved,
                          (SELECT COUNT(mp.pzt_member_id)
                           FROM pzt_member_promo mp
                           WHERE mp.pzt_promo_id = p.pzt_promo_id
                             AND mp.pzt_member_id=m.pzt_member_id
                             AND mp.collected_dt IS NOT NULL) AS interchanged,
                          (SELECT SUM(amount)
                           FROM pzt_sale s
                           WHERE s.pzt_business_id = p.pzt_business_id
                             AND s.pzt_member_id = m.pzt_member_id) AS total_spent
                        FROM pzt_member_promo mp,
                             pzt_member m,
                             pzt_promo p
                        WHERE m.pzt_member_id = mp.pzt_member_id
                          AND mp.pzt_promo_id = p.pzt_promo_id
                          AND p.pzt_business_id =:pzt_business_id '.$idsString;


            $members_obj = $this->dbconn->fetchAll($sql, \Phalcon\Db::FETCH_ASSOC, 
                                                        array('pzt_business_id' => $pzt_business_id));
            if(count($members_obj))
            {
                return $members_obj;
            }
        }

        return false;
    }
    public function findMemberById($pzt_member_id)
    {
        $response = null;
        
        $member_obj = EntityMember::findFirst($pzt_member_id);

        if(is_object($member_obj))
        {
            $response = $member_obj;
        }

        return $response;
    }
    
    public function update($pzt_member_id, $params)
    {   
        $response = null;

        $member_obj = EntityMember::findFirst($pzt_member_id);
        if(is_object($member_obj))
        {  
            //removing white spaces if any
            $params = array_map('trim', $params);

            $member_obj->mobile = $params['mobile'];
            $member_obj->dob    = $params['dob'];
            $member_obj->gender = 'M';//$params['gender'];
            $member_obj->city   = $params['city'];
            $member_obj->state  = $params['state'];
            $member_obj->zip_code = $params['zip_code'];
            $member_obj->country  = $params['country'];

            if($member_obj->save())
            {
                $response = $member_obj;
            }
        }   
        return $response;
    }

}
