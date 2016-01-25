<?php

namespace Promoziti\Models\Entities;

class EPztMember extends ModelBase
{
    //config
    const TBL = "pzt_member";
    const TBL_PK = "pzt_member_id";

    //mapping
    public $pzt_member_id;
    public $pzt_sys_country_id;
    public $current_country_id;
    public $creation_dt;    
    public $creation_method;
    public $first_name;
    public $last_name;
    public $email;
    public $password;
    public $mobile;
    public $mobile_full; //unique across the system
    public $pubid;
    public $avatar_path;
    public $dob;
    public $gender;
    public $city;
    public $state;    
    public $zip_code;
    public $lang;
    public $tz;
    public $dt_format;
    public $registration_ip;
    public $phone_os;
    public $phone_brand;
    public $phone_model;
    public $email_verified;
    public $mobile_verified;
    public $mobile_verification_token;
    public $status;

    public function initialize()
    {
        $this->setSource(self::TBL);
        $this->reset();

        $this->hasMany('pzt_member_id', '\Promoziti\Models\Entities\EPztMemberPromo', 'pzt_member_id', array('alias' => 'PztMemberPromo'));
        $this->hasMany('pzt_member_id', '\Promoziti\Models\Entities\EPztSale', 'pzt_member_id', array('alias' => 'PztSale'));
        $this->hasMany('pzt_member_id', '\Promoziti\Models\Entities\EPztVisit', 'pzt_member_id', array('alias' => 'PztVisit'));
        $this->hasMany('pzt_member_id', '\Promoziti\Models\Entities\EPztLocationTrack', 'requester_id', array('alias' => 'PztLocationTrack'));
        $this->hasMany('pzt_member_id', '\Promoziti\Models\Entities\EPztMemberVip', 'receiver_id', array('alias' => 'PztMemberVip'));
        $this->hasMany('pzt_member_id', '\Promoziti\Models\Entities\EPztMemberToken', 'pzt_member_id', array('alias' => 'PztMemberToken'));
        $this->hasMany('pzt_member_id', '\Promoziti\Models\Entities\EPztMemberSocial', 'pzt_member_id', array('alias' => 'PztMemberSocial'));
        $this->hasMany('pzt_member_id', '\Promoziti\Models\Entities\EPztMemberLogin', 'pzt_member_id', array('alias' => 'PztMemberLogin'));
        $this->hasMany('pzt_member_id', '\Promoziti\Models\Entities\EPztMemberActivity', 'pzt_member_id', array('alias' => 'PztMemberActivity'));
        $this->hasMany('pzt_member_id', '\Promoziti\Models\Entities\EPztMemberForgot', 'pzt_member_id', array('alias' => 'PztMemberForgot'));

        $this->hasOne('pzt_member_id', '\Promoziti\Models\Entities\EPztMemberPoint', 'pzt_member_id', array('alias' => 'PztMemberPoint'));        

        $this->belongsTo('pzt_sys_country_id', '\Promoziti\Models\Entities\EPztSysCountry', 'pzt_sys_country_id', array('alias' => 'PztSysCountry'));
        $this->belongsTo('current_country_id', '\Promoziti\Models\Entities\EPztSysCountry', 'pzt_sys_country_id', array('alias' => 'CurrentCountry'));
    }

    public function serviceOut($key = 'basic')
    {
        $fields = array('first_name', 'last_name', 'email', 'status');
        if($key == 'basic+id')
        {
            array_unshift($fields, 'pzt_member_id');
        }
        else if($key == 'complete'
                || $key == 'complete+id')
        {
            $fields = array('first_name', 'last_name', 'email', 'mobile', 'mobile_full',
                            'dob', 'gender', 'city', 'state', 'zip_code',
                            'lang', 'tz', 'email_verified', 'mobile_verified',
                            'status', 
                            '|PztSysCountry:country|country|',
                            '|PztSysCountry:country|call_code|',
                            '|PztSysCountry:country|native_name_html|',
                            '|CurrentCountry:current_country|country|',
                            '|CurrentCountry:current_country|call_code|',
                            '|CurrentCountry:current_country|native_name_html|');
            if($key == 'complete+id')
            {
                array_unshift($fields, 'pzt_member_id');
            }
        }

        $out = array();
        foreach ($fields as $key => $field) {
            if(strpos($field, '|') === 0) //handling child class
            {
                $field = substr($field, 1, -1);
                $tokens = explode('|', $field);
                $class_info = explode(':', $tokens[0]);

                if(!isset($out[$class_info[1]]) || !is_array($out[$class_info[1]]))
                {
                    $out[$class_info[1]] = array();
                }

                $out[$class_info[1]][$tokens[1]] = $this->$class_info[0]->$tokens[1];
            }
            else
            {
                if(property_exists($this, $field))
                {
                    $out[$field] = $this->$field;    
                }                
            }
        }

        return $out;
    }
}
